import { getToken } from '../auth/session'
import type { Papel } from '../auth/session'

export type RenterProfile = {
  id: number
  nome: string
  email: string
  telefone: string
}

export class ApiRequestError extends Error {
  constructor(
    message: string,
    readonly status: number,
    readonly payload: { message?: string; errors?: Record<string, string[]> },
  ) {
    super(message)
  }

  fieldErrors(): string[] {
    if (!this.payload.errors) {
      return this.message !== '' ? [this.message] : []
    }

    return Object.values(this.payload.errors).flat()
  }
}

function apiBaseUrl(): string {
  return import.meta.env.VITE_API_BASE_URL ?? 'http://localhost:8989'
}

async function request<T>(path: string, init: RequestInit = {}): Promise<T> {
  const headers = new Headers(init.headers)
  headers.set('Accept', 'application/json')

  if (init.body !== undefined && !headers.has('Content-Type')) {
    headers.set('Content-Type', 'application/json')
  }

  const token = getToken()
  if (token !== null) {
    headers.set('Authorization', `Bearer ${token}`)
  }

  const response = await fetch(`${apiBaseUrl()}${path}`, { ...init, headers })
  const payload = await response.json().catch(() => ({ message: response.statusText }))

  if (!response.ok) {
    throw new ApiRequestError(
      payload.message ?? 'Erro na requisição.',
      response.status,
      payload,
    )
  }

  return payload as T
}

export async function registerRenter(body: {
  nome: string
  email: string
  telefone: string
  senha: string
}): Promise<RenterProfile> {
  const response = await request<{ data: RenterProfile }>('/api/v2/locatarios', {
    method: 'POST',
    body: JSON.stringify(body),
  })

  return response.data
}

export async function login(
  email: string,
  password: string,
): Promise<{ token: string; papel: Papel }> {
  return request('/api/v1/login', {
    method: 'POST',
    body: JSON.stringify({ email, password }),
  })
}

export async function getMyProfile(): Promise<RenterProfile> {
  const response = await request<{ data: RenterProfile }>('/api/v2/locatarios/me')

  return response.data
}

export async function updateMyProfile(body: {
  nome: string
  email: string
  telefone: string
}): Promise<RenterProfile> {
  const response = await request<{ data: RenterProfile }>('/api/v2/locatarios/me', {
    method: 'PATCH',
    body: JSON.stringify(body),
  })

  return response.data
}
