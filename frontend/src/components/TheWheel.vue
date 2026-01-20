<script setup lang="ts">
import { ref } from 'vue'

const emit = defineEmits<{
  (e: 'result', text: string): void
}>()

const isSpinning = ref(false)
const activeSegment = ref<number>(1) // 1-10
const totalSegments = 10

// Colors from the wheel image (approx)
const colors = [
  '#FF5733', '#FFBD33', '#DBFF33', '#75FF33', '#33FF57', 
  '#33FFBD', '#33DBFF', '#3375FF', '#5733FF', '#BD33FF'
]

const spinWheel = async () => {
  if (isSpinning.value) return 
  isSpinning.value = true
  
  // Animation parameters
  let speed = 50 // Initial speed (ms per step)
  let steps = 0
  const minSteps = 40 // ~4 seconds at fast speed + deceleration
  const maxSpeed = 300 // Max slow speed
  
  // Keep spinning fast first
  const spinInterval = async () => {
    activeSegment.value = (activeSegment.value % totalSegments) + 1
    steps++

    if (steps < minSteps) {
      setTimeout(spinInterval, speed)
    } else if (speed < maxSpeed) {
       // Decelerate
       speed *= 1.1
       setTimeout(spinInterval, speed)
    } else {
       // Stop
       stopWheel()
    }
  }

  spinInterval()
}

const stopWheel = async () => {
  // We stopped at activeSegment.value
  const segmentId = activeSegment.value
  
  try {
    const response = await fetch(`http://back.executor.local/api/excuse/${segmentId}`)
    const data = await response.json()
    emit('result', data.text)
  } catch (e) {
    console.error(e)
    emit('result', "Error obteniendo excusa (¿Backend caído?)")
  } finally {
    isSpinning.value = false
  }
}
</script>

<template>
  <div class="wheel-container">
    <div class="wheel">
      <div 
        v-for="n in 10" 
        :key="n"
        class="segment"
        :class="{ active: activeSegment === n }"
        :style="{ 
          '--i': n, 
          '--color': colors[n-1] 
        }"
      >
        <span>{{ n }}</span>
      </div>
      <div class="center-btn" @click="spinWheel">
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

.center-btn:active {
  transform: translate(-50%, -50%) scale(0.95);
}

.segment {
  position: absolute;
  top: 0;
  left: 50%;
  width: 50%;
  height: 50%;
  transform-origin: 0% 100%;
  transform: rotate(calc((var(--i) - 1) * 36deg)) skewY(54deg); /* 360/10 = 36deg */
  border: 1px solid rgba(0,0,0,0.1);
  background: #ddd; /* Default inactive color */
  transition: background 0.1s, filter 0.1s;
}

.segment.active {
  background: var(--color);
  box-shadow: 0 0 20px var(--color);
  z-index: 2;
  border: none;
}

/* Fix CSS shape mapping logic: 
   Conic gradient is easier for full pie chart, 
   but specific highlighting is easier with rotated divs. 
   Using this CSS clip-path or transform approach needs care.
   To make standard pie slices, we need:
   rotate( (i-1)*36 deg )
   skewY( -54 deg ) to make a 36deg slice (90 - 36 = 54)
*/

.segment {
  left: 50%;
  top: 50%;
  width: 50%;
  height: 50%;
  transform-origin: 0 0;
  transform: rotate(calc((var(--i) - 1) * 36deg - 90deg + 18deg)) skewY(-54deg);
  /* -90 to start at top. +18 to center segment? 
     Let's try a standard conic gradient approach instead?
     No, div approach is better for individual highlighting.
  */
  margin-top: -1px; /* Remove gaps */
}

/* Re-adjusting transform for correct 10-slice pie */
/* Each slice is 36deg.
   We want slice 1 at TOP (12 o'clock).
   36deg / 2 = 18deg offset if we want the center of the slice at top.
   But user said "Next in clockwise".
   Slice 1: Top (centered around -90deg?).
   Let's stick to simple rotation.
*/
.segment {
   /* Reset generic styles for the specific approach */
   position: absolute;
   top: 0; right: 0;
   width: 50%; height: 50%;
   transform-origin: 0% 100%;
   /* We want 36deg slices. */
   /* skewY(90 - 36) = 54deg */
   transform: rotate(calc((var(--i) - 1) * 36deg)) skewY(-54deg);
   overflow: hidden;
   /* The content (number) needs counter-rotation so it's not skewed */
}

.segment span {
    display: block;
    transform: skewY(54deg) rotate(18deg); /* Counter skew + align */
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
