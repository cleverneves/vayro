import { useState } from 'react'
import { getToken } from './auth/session'
import AvailableVehiclesPage from './pages/AvailableVehiclesPage'
import type { RentalDraft } from './pages/AvailableVehiclesPage'
import LoginPage from './pages/LoginPage'
import MyRentalsPage from './pages/MyRentalsPage'
import NewRentalPage from './pages/NewRentalPage'
import ProfilePage from './pages/ProfilePage'
import RegisterPage from './pages/RegisterPage'
import RentalDetailPage from './pages/RentalDetailPage'

type Screen = 'login' | 'register' | 'profile' | 'available' | 'new-rental' | 'rentals' | 'rental-detail'

export default function App() {
  const [screen, setScreen] = useState<Screen>(() => (getToken() ? 'profile' : 'login'))
  const [draft, setDraft] = useState<RentalDraft | null>(null)
  const [rentalId, setRentalId] = useState<number | null>(null)

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

  if (screen === 'rentals') {
    return (
      <MyRentalsPage
        onOpenRental={(id) => {
          setRentalId(id)
          setScreen('rental-detail')
        }}
        onBack={() => setScreen('profile')}
      />
    )
  }

  if (screen === 'rental-detail' && rentalId !== null) {
    return <RentalDetailPage rentalId={rentalId} onBack={() => setScreen('rentals')} />
  }

  if (screen === 'profile') {
    return (
      <ProfilePage
        onLogout={() => setScreen('login')}
        onGoToVehicles={() => setScreen('available')}
        onGoToRentals={() => setScreen('rentals')}
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
