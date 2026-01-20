import { ref, computed } from 'vue'
import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', () => {
    const token = ref<string | null>(localStorage.getItem('auth_token'))
    const email = ref<string | null>(localStorage.getItem('auth_email'))

    const isAuthenticated = computed(() => !!token.value)

    function setAuth(newToken: string, userEmail: string): void {
        token.value = newToken
        email.value = userEmail
        localStorage.setItem('auth_token', newToken)
        localStorage.setItem('auth_email', userEmail)
    }

    function clearAuth(): void {
        token.value = null
        email.value = null
        localStorage.removeItem('auth_token')
        localStorage.removeItem('auth_email')
    }

    function getAuthHeader(): string {
        return `Bearer ${token.value}`
    }

    return {
        token,
        email,
        isAuthenticated,
        setAuth,
        clearAuth,
        getAuthHeader
    }
})
