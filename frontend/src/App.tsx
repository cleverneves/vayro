import { useState } from 'react'
import { getPapel, getToken } from './auth/session'
import AdminDailyCountPage from './pages/AdminDailyCountPage'
import AdminRentalDetailPage from './pages/AdminRentalDetailPage'
import AdminRenterDetailPage from './pages/AdminRenterDetailPage'
import AdminRentalsPage from './pages/AdminRentalsPage'
import AdminRentersPage from './pages/AdminRentersPage'
import AvailableVehiclesPage from './pages/AvailableVehiclesPage'
import type { RentalDraft } from './pages/AvailableVehiclesPage'
import LoginPage from './pages/LoginPage'
import MyRentalsPage from './pages/MyRentalsPage'
import NewRentalPage from './pages/NewRentalPage'
import ProfilePage from './pages/ProfilePage'
import RegisterPage from './pages/RegisterPage'
import RentalDetailPage from './pages/RentalDetailPage'

type Screen =
  | 'login'
  | 'register'
  | 'profile'
  | 'available'
  | 'new-rental'
  | 'rentals'
  | 'rental-detail'
  | 'admin-renters'
  | 'admin-renter-detail'
  | 'admin-rentals'
  | 'admin-rental-detail'
  | 'admin-daily-count'

function homeScreen(): Screen {
  if (!getToken()) {
    return 'login'
  }

  return getPapel() === 'administrativo' ? 'admin-renters' : 'profile'
}

export default function App() {
  const [screen, setScreen] = useState<Screen>(homeScreen)
  const [draft, setDraft] = useState<RentalDraft | null>(null)
  const [rentalId, setRentalId] = useState<number | null>(null)
  const [renterId, setRenterId] = useState<number | null>(null)

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

  if (screen === 'admin-renters') {
    return (
      <AdminRentersPage
        onOpenRenter={(id) => {
          setRenterId(id)
          setScreen('admin-renter-detail')
        }}
        onGoToRentals={() => setScreen('admin-rentals')}
        onGoToDailyCount={() => setScreen('admin-daily-count')}
        onLogout={() => setScreen('login')}
      />
    )
  }

  if (screen === 'admin-renter-detail' && renterId !== null) {
    return (
      <AdminRenterDetailPage
        renterId={renterId}
        onBack={() => setScreen('admin-renters')}
      />
    )
  }

  if (screen === 'admin-rentals') {
    return (
      <AdminRentalsPage
        onOpenRental={(id) => {
          setRentalId(id)
          setScreen('admin-rental-detail')
        }}
        onBack={() => setScreen('admin-renters')}
      />
    )
  }

  if (screen === 'admin-rental-detail' && rentalId !== null) {
    return (
      <AdminRentalDetailPage
        rentalId={rentalId}
        onBack={() => setScreen('admin-rentals')}
      />
    )
  }

  if (screen === 'admin-daily-count') {
    return <AdminDailyCountPage onBack={() => setScreen('admin-renters')} />
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
      onLoggedIn={() => setScreen(homeScreen())}
      onGoToRegister={() => setScreen('register')}
    />
  )
}
