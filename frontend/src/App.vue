<script setup lang="ts">
import { RouterView, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

function logout() {
  authStore.clearAuth()
  router.push('/login')
}
</script>

<template>
  <div class="app-container">
    <header v-if="authStore.isAuthenticated" class="app-header">
      <div class="user-info">
        <span class="user-email">{{ authStore.email }}</span>
        <button @click="logout" class="logout-button">Logout</button>
      </div>
    </header>
    <RouterView />
  </div>
</template>

<style scoped>
.app-container {
  min-height: 100vh;
}

.app-header {
  position: fixed;
  top: 0;
  right: 0;
  left: 0;
  padding: 1rem 2rem;
  background: rgba(0, 0, 0, 0.3);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  z-index: 100;
}

.user-info {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 1.5rem;
}

.user-email {
  color: rgba(255, 255, 255, 0.9);
  font-weight: 500;
}

.logout-button {
  padding: 0.5rem 1.5rem;
  border-radius: 8px;
  border: none;
  background: linear-gradient(135deg, rgba(255, 87, 51, 0.8), rgba(192, 57, 43, 0.8));
  color: white;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.logout-button:hover {
  background: linear-gradient(135deg, #ff5733, #c0392b);
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(255, 87, 51, 0.3);
}
</style>
