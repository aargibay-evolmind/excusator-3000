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
    <div class="modal-content" @click.stop>
      <div class="excuse-text">
        {{ text }}
      </div>
      <div class="modal-footer">
        <p class="click-hint">Haz clic para cerrar</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  cursor: pointer;
}

.modal-content {
  background: white; /* Fallback */
  background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);
  padding: 2rem;
  border-radius: 15px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
  max-width: 80%;
  width: 500px;
  text-align: center;
  border: 4px solid #333;
  transform: scale(0.9);
  animation: popIn 0.3s forwards ease-out;
}

.excuse-text {
  font-family: 'Courier New', Courier, monospace;
  font-size: 1.5rem;
  font-weight: bold;
  color: #333;
  margin-bottom: 2rem;
  line-height: 1.4;
}

.click-hint {
  font-size: 0.8rem;
  color: #666;
  text-transform: uppercase;
  letter-spacing: 1px;
}

@keyframes popIn {
  to {
    transform: scale(1);
  }
}
</style>
