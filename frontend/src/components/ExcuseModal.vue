<script setup lang="ts">
defineProps<{
  text: string
  visible: boolean
}>()

const emit = defineEmits<{
  (e: 'close'): void
}>()
</script>

<template>
  <div v-if="visible" class="modal-overlay" @click="emit('close')">
    <div class="cartel" @click.stop>
      <!-- Decorative Screws -->
      <div class="screw top-left"></div>
      <div class="screw top-right"></div>
      <div class="screw bottom-left"></div>
      <div class="screw bottom-right"></div>

      <div class="cartel-inner">
        <div class="cartel-header">
          <span class="star">★</span>
          <h3>TU EXCUSA ES</h3>
          <span class="star">★</span>
        </div>
        
        <div class="excuse-content">
          {{ text }}
        </div>

        <div class="cartel-footer">
          <p class="click-hint">HAZ CLIC PARA CONTINUAR</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background-color: rgba(0, 0, 0, 0.85); /* Darker dim */
  backdrop-filter: blur(8px); /* Blur the wheel behind */
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 2000;
  cursor: pointer;
  animation: fadeIn 0.3s ease-out;
}

.cartel {
  position: relative;
  background: linear-gradient(135deg, #8e0e00 0%, #4a0404 100%); /* Deep Red Premium Background */
  padding: 10px;
  border-radius: 12px;
  box-shadow: 
    0 20px 60px rgba(0,0,0,0.6),
    0 0 0 2px #c0392b,
    0 0 0 8px #f1c40f; /* Gold outer frame */
  max-width: 90%;
  width: 600px;
  transform: scale(0.8) rotate(-2deg);
  animation: cartelPop 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
  color: white;
}

.cartel-inner {
  border: 2px dashed rgba(241, 196, 15, 0.5); /* Inner dashed gold line */
  border-radius: 8px;
  padding: 2rem;
  text-align: center;
}

/* Screws decoration in corners */
.screw {
  position: absolute;
  width: 12px; height: 12px;
  background: radial-gradient(circle, #bdc3c7, #7f8c8d);
  border-radius: 50%;
  box-shadow: 0 1px 3px rgba(0,0,0,0.5);
  z-index: 10;
}
.screw::after {
  content: '';
  position: absolute;
  top: 50%; left: 15%;
  width: 70%; height: 2px;
  background: #555;
  transform: translateY(-50%) rotate(45deg);
}
.top-left { top: 15px; left: 15px; }
.top-right { top: 15px; right: 15px; }
.bottom-left { bottom: 15px; left: 15px; }
.bottom-right { bottom: 15px; right: 15px; }

.cartel-header {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-bottom: 2rem;
  gap: 1rem;
}

.cartel-header h3 {
  margin: 0;
  font-family: 'Arial', sans-serif;
  font-weight: 900;
  letter-spacing: 3px;
  font-size: 1.2rem;
  color: #f1c40f;
  text-transform: uppercase;
  text-shadow: 0 2px 4px rgba(0,0,0,0.5);
}

.star {
  color: #f1c40f;
  font-size: 1.5rem;
  animation: spinStar 3s infinite linear;
}

.excuse-content {
  font-family: 'Georgia', serif; /* More classy font for text */
  font-size: 2rem;
  font-weight: bold;
  line-height: 1.3;
  color: #fff;
  text-shadow: 0 2px 5px rgba(0,0,0,0.4);
  margin-bottom: 2.5rem;
  background: rgba(0,0,0,0.2);
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: inset 0 2px 10px rgba(0,0,0,0.3);
}

.click-hint {
  font-size: 0.8rem;
  color: rgba(255,255,255,0.6);
  text-transform: uppercase;
  letter-spacing: 2px;
  margin: 0;
}

@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes cartelPop {
  to {
    transform: scale(1) rotate(0deg);
  }
}
@keyframes spinStar { 
  0% { transform: rotate(0deg); } 
  100% { transform: rotate(360deg); } 
}
</style>
