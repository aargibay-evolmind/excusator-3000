import { useAuthStore } from '@/stores/auth'
import router from '@/router'

const API_BASE_URL = 'http://back.executor.local/api'

interface RequestOptions extends RequestInit {
    requiresAuth?: boolean
}

export async function apiRequest<T>(
    endpoint: string,
    options: RequestOptions = {}
): Promise<T> {
    const { requiresAuth = true, ...fetchOptions } = options
    const authStore = useAuthStore()

    const headers: Record<string, string> = {
        'Content-Type': 'application/json',
    }

    if (requiresAuth && authStore.token) {
        headers['Authorization'] = authStore.getAuthHeader()
    }

    const response = await fetch(`${API_BASE_URL}${endpoint}`, {
        ...fetchOptions,
        headers,
    })

    if (response.status === 401) {
        // Unauthorized - clear auth and redirect to login
        authStore.clearAuth()
        router.push('/login')
        throw new Error('Unauthorized')
    }

    if (!response.ok) {
        const error = await response.json().catch(() => ({ error: 'Request failed' }))
        throw new Error(error.error || `HTTP ${response.status}`)
    }

    return response.json()
}

export async function apiLogin(email: string, password: string) {
    return apiRequest<{ token: string; email: string }>('/auth/login', {
        method: 'POST',
        body: JSON.stringify({ email, password }),
        requiresAuth: false,
    })
}

export async function apiRegister(email: string, password: string) {
    return apiRequest<{ token: string; email: string }>('/auth/register', {
        method: 'POST',
        body: JSON.stringify({ email, password }),
        requiresAuth: false,
    })
}
