<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'

const emit = defineEmits<{
  (e: 'result', text: string): void
}>()

const isSpinning = ref(false)
const activeSegment = ref<number>(1)
const categories = ref<Array<{id: number, name: string}>>([])
const loading = ref(true)
const error = ref('')

// Colors from the wheel image (approx)
const allColors = [
  '#FF5733', '#FFBD33', '#DBFF33', '#75FF33', '#33FF57', 
  '#33FFBD', '#33DBFF', '#3375FF', '#5733FF', '#BD33FF',
  '#FF33A8', '#FF336E', '#A833FF', '#33FFA8', '#FF8F33'
]

onMounted(async () => {
  try {
    const res = await fetch('http://back.executor.local/api/categories')
    if (!res.ok) {
        if (res.status === 400) {
            error.value = "No hay suficientes categorías activas (min 5)."
        } else {
            error.value = "Error cargando categorías."
        }
        return
    }
    categories.value = await res.json()
  } catch (e) {
    console.error(e)
    error.value = "Error de conexión con el servidor."
  } finally {
    loading.value = false
  }
})

const totalSegments = computed(() => categories.value.length)
const segmentAngle = computed(() => 360 / (totalSegments.value || 1))
const skewAngle = computed(() => (90 - segmentAngle.value) * -1) // Negative
const labelRotate = computed(() => segmentAngle.value / 2) // Center text in wedge

const spinWheel = async () => {
  if (isSpinning.value || loading.value || error.value) return 
  isSpinning.value = true
  
  // Animation parameters
  let speed = 50 
  let steps = 0
  const minSteps = 40 
  const maxSpeed = 300 
  
  const spinInterval = async () => {
    activeSegment.value = (activeSegment.value % totalSegments.value) + 1
    steps++

    if (steps < minSteps) {
      setTimeout(spinInterval, speed)
    } else if (speed < maxSpeed) {
       speed *= 1.1
       setTimeout(spinInterval, speed)
    } else {
       stopWheel()
    }
  }

  spinInterval()
}

const stopWheel = async () => {
  const segmentIndex = activeSegment.value - 1 // 0-based
  const category = categories.value[segmentIndex]
  
  if (!category) {
      emit('result', "Error: Categoría no encontrada")
      isSpinning.value = false
      return
  }

  try {
    const response = await fetch(`http://back.executor.local/api/excuse?category_id=${category.id}`)
    const data = await response.json()
    if (data.error) {
        emit('result', data.error)
    } else {
        // Prepend Category Name for context
        emit('result', `[${category.name}] ${data.content}`)
    }
  } catch (e) {
    console.error(e)
    emit('result', "Error obteniendo excusa")
  } finally {
    isSpinning.value = false
  }
}
</script>

<template>
  <div class="wheel-container">
    <div v-if="loading">Cargando categorías...</div>
    <div v-else-if="error" class="error">{{ error }}</div>
    
    <div v-else class="wheel" :style="{ '--segment-angle': segmentAngle + 'deg', '--skew-angle': skewAngle + 'deg', '--label-rotate': labelRotate + 'deg' }">
      <div 
        v-for="(cat, index) in categories" 
        :key="cat.id"
        class="segment"
        :class="{ active: activeSegment === index + 1 }"
        :style="{ 
          '--i': index + 1, 
          '--color': allColors[index % allColors.length] 
        }"
      >
        <span>{{ index + 1 }}</span>
      </div>
      <div class="center-btn" @click="spinWheel" :class="{ disabled: isSpinning }">
        {{ isSpinning ? '...' : 'Dame una excusa' }}
      </div>
    </div>
  </div>
</template>

<style scoped>
.wheel-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 500px;
  flex-direction: column;
}

.error {
    color: red;
    font-weight: bold;
    font-size: 1.2rem;
}

.wheel {
  position: relative;
  width: 400px;
  height: 400px;
  border-radius: 50%;
  border: 10px solid #333;
  overflow: hidden;
  background: #f0f0f0;
}

.center-btn {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 120px;
  height: 120px;
  background: #fff;
  border: 5px solid #333;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
  cursor: pointer;
  font-weight: bold;
  text-align: center;
  user-select: none;
  transition: transform 0.1s;
}

.center-btn:active:not(.disabled) {
  transform: translate(-50%, -50%) scale(0.95);
}

.center-btn.disabled {
    cursor: not-allowed;
    opacity: 0.7;
}

.segment {
   position: absolute;
   top: 0; right: 0;
   width: 50%; height: 50%;
   transform-origin: 0% 100%;
   transform: rotate(calc((var(--i) - 1) * var(--segment-angle))) skewY(var(--skew-angle));
   overflow: hidden;
   border: 1px solid rgba(0,0,0,0.1);
   background: #ddd;
   transition: background 0.1s;
}

.segment.active {
  background: var(--color);
  box-shadow: 0 0 20px var(--color);
  z-index: 2;
  border: none;
}

.segment span {
    display: block;
    transform: skewY(calc(var(--skew-angle) * -1)) rotate(var(--label-rotate));
    position: absolute;
    bottom: 20px;
    left: 20px;
    font-weight: bold;
    font-size: 1.2rem;
    opacity: 0.3;
}

.segment.active span {
    opacity: 1;
    color: white;
    text-shadow: 1px 1px 2px black;
}
</style>
