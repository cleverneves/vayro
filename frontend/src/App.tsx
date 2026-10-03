import { useState } from 'react'
import { getToken } from './auth/session'
import AvailableVehiclesPage from './pages/AvailableVehiclesPage'
import type { RentalDraft } from './pages/AvailableVehiclesPage'
import LoginPage from './pages/LoginPage'
import NewRentalPage from './pages/NewRentalPage'
import ProfilePage from './pages/ProfilePage'
import RegisterPage from './pages/RegisterPage'

type Screen = 'login' | 'register' | 'profile' | 'available' | 'new-rental'

export default function App() {
  const [screen, setScreen] = useState<Screen>(() => (getToken() ? 'profile' : 'login'))
  const [draft, setDraft] = useState<RentalDraft | null>(null)

  if (screen === 'register') {
    return (
      <RegisterPage
        onRegistered={() => setScreen('login')}
        onGoToLogin={() => setScreen('login')}
      />
    )
  }

  if (screen === 'available') {
    return (
      <AvailableVehiclesPage
        onSelectVehicle={(nextDraft) => {
          setDraft(nextDraft)
          setScreen('new-rental')
        }}
        onBack={() => setScreen('profile')}
      />
    )
  }

  if (screen === 'new-rental' && draft !== null) {
    return <NewRentalPage draft={draft} onBack={() => setScreen('available')} />
  }

  if (screen === 'profile') {
    return (
      <ProfilePage
        onLogout={() => setScreen('login')}
        onGoToVehicles={() => setScreen('available')}
      />
    )
  }

  return (
    <LoginPage
      onLoggedIn={() => setScreen('profile')}
      onGoToRegister={() => setScreen('register')}
    />
  )
}
