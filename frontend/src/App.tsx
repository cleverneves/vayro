import { useState } from 'react'
import { getToken } from './auth/session'
import LoginPage from './pages/LoginPage'
import ProfilePage from './pages/ProfilePage'
import RegisterPage from './pages/RegisterPage'

type Screen = 'login' | 'register' | 'profile'

export default function App() {
  const [screen, setScreen] = useState<Screen>(() => (getToken() ? 'profile' : 'login'))

  if (screen === 'register') {
    return (
      <RegisterPage
        onRegistered={() => setScreen('login')}
        onGoToLogin={() => setScreen('login')}
      />
    )
  }

  if (screen === 'profile') {
    return <ProfilePage onLogout={() => setScreen('login')} />
  }

  return (
    <LoginPage
      onLoggedIn={() => setScreen('profile')}
      onGoToRegister={() => setScreen('register')}
    />
  )
}
