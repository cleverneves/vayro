export type Papel = 'locatario' | 'administrativo'

const TOKEN_KEY = 'vayro.token'
const PAPEL_KEY = 'vayro.papel'

export function getToken(): string | null {
  return localStorage.getItem(TOKEN_KEY)
}

export function getPapel(): Papel | null {
  const value = localStorage.getItem(PAPEL_KEY)

  if (value === 'locatario' || value === 'administrativo') {
    return value
  }

  return null
}

export function setSession(token: string, papel: Papel): void {
  localStorage.setItem(TOKEN_KEY, token)
  localStorage.setItem(PAPEL_KEY, papel)
}

export function clearSession(): void {
  localStorage.removeItem(TOKEN_KEY)
  localStorage.removeItem(PAPEL_KEY)
}
