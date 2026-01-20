<script setup lang="ts">
import { ref } from 'vue'
import TheWheel from '../components/TheWheel.vue'
import ExcuseModal from '../components/ExcuseModal.vue'

const excuseText = ref('')
const showModal = ref(false)

const handleResult = (text: string) => {
  excuseText.value = text
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}
</script>

<template>
  <main>
    <h1 class="title">Excusator 3000</h1>
    <TheWheel @result="handleResult" />
    <ExcuseModal 
      :visible="showModal" 
      :text="excuseText" 
      @close="closeModal" 
    />
  </main>
</template>

<style scoped>
.title {
  text-align: center;
  margin-bottom: 2rem;
  font-size: 3rem;
  color: #2c3e50;
  text-transform: uppercase;
  letter-spacing: 5px;
}

main {
  padding: 2rem;
  min-height: 100vh;
  background: #f4f4f4;
  display: flex;
  flex-direction: column;
  align-items: center;
}
</style>
