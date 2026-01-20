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
  <div class="wheel-stage">
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Cargando la ruleta...</p>
    </div>
    <div v-else-if="error" class="error-state">{{ error }}</div>
    
    <div v-else class="wheel-outer-rim">
      <!-- Decorative Lights Ring -->
      <div class="lights-ring">
        <div v-for="n in 12" :key="n" class="light" :style="{ '--i': n }"></div>
      </div>

      <div class="wheel-main" :style="{ '--segment-angle': segmentAngle + 'deg', '--skew-angle': skewAngle + 'deg', '--label-rotate': labelRotate + 'deg' }">
        <!-- Segments -->
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
          <div class="segment-inner">
            <span>{{ index + 1 }}</span>
          </div>
        </div>
      </div>

      <!-- Center Hub -->
      <div class="center-hub">
        <div class="center-btn" @click="spinWheel" :class="{ disabled: isSpinning }">
           <div class="btn-text">
             {{ isSpinning ? '...' : 'GIRAR' }}
           </div>
        </div>
      </div>

      <!-- Pointer -->
      <div class="pointer-indicator"></div>
    </div>
    
    <div v-if="activeSegment && !isSpinning && !loading" class="category-display">
       {{ categories[activeSegment - 1]?.name }}
    </div>
  </div>
</template>

<style scoped>
/* Container Stage */
.wheel-stage {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  min-height: 600px;
  background: radial-gradient(circle at center, #2c3e50 0%, #000000 100%);
  padding: 40px;
  border-radius: 20px;
  box-shadow: 0 20px 50px rgba(0,0,0,0.5);
  position: relative;
  overflow: hidden;
}

/* Loading & Error */
.loading-state, .error-state {
  color: white;
  font-family: 'Arial', sans-serif;
  font-size: 1.5rem;
  text-align: center;
}
.spinner {
  width: 40px; height: 40px;
  border: 4px solid rgba(255,255,255,0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 20px;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Outer Rim (The Frame) */
.wheel-outer-rim {
  position: relative;
  width: 450px;
  height: 450px;
  border-radius: 50%;
  background: linear-gradient(135deg, #444, #111);
  padding: 15px; /* Space for lights */
  box-shadow: 
    0 0 0 10px #d4af37, /* Gold outer border */
    0 0 20px rgba(0,0,0,0.8),
    inset 0 0 20px rgba(0,0,0,0.8);
  display: flex;
  justify-content: center;
  align-items: center;
}

/* Lights Ring */
.lights-ring {
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  pointer-events: none;
  border-radius: 50%;
}
.light {
  position: absolute;
  top: 50%; left: 50%;
  width: 12px; height: 12px;
  background: #fff;
  border-radius: 50%;
  box-shadow: 0 0 10px #fff;
  transform: translate(-50%, -50%) rotate(calc(30deg * var(--i))) translate(0, -215px); /* Position on rim */
  animation: blink 2s infinite alternate;
  animation-delay: calc(0.1s * var(--i));
}
@keyframes blink {
  0% { opacity: 0.4; }
  100% { opacity: 1; box-shadow: 0 0 15px #ffeb3b; background: #ffeb3b; }
}

/* The Wheel Itself */
.wheel-main {
  position: relative;
  width: 100%;
  height: 100%;
  border-radius: 50%;
  overflow: hidden;
  border: 5px solid #222;
  box-shadow: inset 0 0 30px rgba(0,0,0,0.5);
  background: #333;
}

/* Segments */
.segment {
   position: absolute;
   top: 0; right: 0;
   width: 50%; height: 50%;
   transform-origin: 0% 100%;
   transform: rotate(calc((var(--i) - 1) * var(--segment-angle))) skewY(var(--skew-angle));
   overflow: hidden;
   border: 1px solid rgba(255,255,255,0.1);
   transition: filter 0.2s;
}

.segment-inner {
    width: 100%; height: 100%;
    background: var(--color);
    background-image: linear-gradient(to bottom right, rgba(255,255,255,0.2), rgba(0,0,0,0.1));
    transform-origin: 0% 100%;
    /* We don't skew the inner, we just use it for background */
}

/* Active Segment Highlight */
.segment.active .segment-inner {
  filter: brightness(1.3);
  box-shadow: inset 0 0 20px rgba(255,255,255,0.5);
}

/* Labels */
.segment span {
    display: block;
    /* Counter-transform to unskew and center text */
    transform: skewY(calc(var(--skew-angle) * -1)) rotate(var(--label-rotate));
    position: absolute;
    bottom: 30px;
    left: 20px;
    font-weight: 900;
    font-size: 1.5rem;
    color: rgba(255,255,255,0.9);
    text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
    pointer-events: none;
}

/* Center Hub */
.center-hub {
  position: absolute;
  top: 50%; left: 50%;
  transform: translate(-50%, -50%);
  width: 100px; height: 100px;
  background: white;
  border-radius: 50%;
  box-shadow: 0 0 15px rgba(0,0,0,0.5);
  z-index: 10;
  display: flex;
  justify-content: center;
  align-items: center;
  border: 8px solid #d4af37;
}

.center-btn {
  width: 100%; height: 100%;
  border-radius: 50%;
  background: radial-gradient(circle at 30% 30%, #ff5733, #c0392b);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.1s, box-shadow 0.2s;
  box-shadow: inset 0 5px 10px rgba(255,255,255,0.4);
}
.center-btn:active:not(.disabled) {
  transform: scale(0.95);
  box-shadow: inset 0 5px 20px rgba(0,0,0,0.5);
}
.center-btn.disabled {
    filter: grayscale(0.8);
    cursor: not-allowed;
}

.btn-text {
    font-weight: bold;
    color: white;
    text-transform: uppercase;
    font-size: 1rem;
    text-shadow: 0 2px 2px rgba(0,0,0,0.3);
}

/* Pointer */
.pointer-indicator {
    position: absolute;
    top: -20px;
    left: 50%;
    transform: translateX(-50%);
    width: 0; 
    height: 0; 
    border-left: 20px solid transparent;
    border-right: 20px solid transparent;
    border-top: 40px solid #e74c3c; /* Red pointer */
    filter: drop-shadow(0 5px 5px rgba(0,0,0,0.5));
    z-index: 20;
}

/* Category Name Display */
.category-display {
    margin-top: 30px;
    font-size: 2rem;
    color: #ffd700;
    font-weight: bold;
    text-transform: uppercase;
    text-shadow: 0 0 10px rgba(255, 215, 0, 0.5);
    letter-spacing: 2px;
}
</style>
