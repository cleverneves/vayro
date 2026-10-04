import { useState } from 'react'
import AppBar from '@mui/material/AppBar'
import Button from '@mui/material/Button'
import Toolbar from '@mui/material/Toolbar'
import Typography from '@mui/material/Typography'
import { getPapel, getToken } from './auth/session'
import type { Papel } from './auth/session'
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

const ADMIN_SCREENS: Screen[] = [
  'admin-renters',
  'admin-renter-detail',
  'admin-rentals',
  'admin-rental-detail',
  'admin-daily-count',
]

const RENTER_SCREENS: Screen[] = [
  'profile',
  'available',
  'new-rental',
  'rentals',
  'rental-detail',
]

function isPublicScreen(screen: Screen): boolean {
  return screen === 'login' || screen === 'register'
}

function isAdminScreen(screen: Screen): boolean {
  return ADMIN_SCREENS.includes(screen)
}

function homeScreen(): Screen {
  if (!getToken()) {
    return 'login'
  }

  return getPapel() === 'administrativo' ? 'admin-renters' : 'profile'
}

function allowedScreen(screen: Screen): Screen {
  if (!getToken()) {
    return isPublicScreen(screen) ? screen : 'login'
  }

  if (getPapel() === 'administrativo') {
    return isAdminScreen(screen) ? screen : 'admin-renters'
  }

  if (isAdminScreen(screen) || isPublicScreen(screen)) {
    return 'profile'
  }

  return RENTER_SCREENS.includes(screen) ? screen : 'profile'
}

type RoleNavProps = {
  papel: Papel
  onGo: (screen: Screen) => void
}

function RoleNav({ papel, onGo }: RoleNavProps) {
  return (
    <AppBar position="static">
      <Toolbar>
        <Typography variant="h6" component="div" sx={{ flexGrow: 1 }}>
          Vayro
        </Typography>
        {papel === 'administrativo' ? (
          <>
            <Button color="inherit" onClick={() => onGo('admin-renters')}>
              Locatários
            </Button>
            <Button color="inherit" onClick={() => onGo('admin-rentals')}>
              Locações
            </Button>
            <Button color="inherit" onClick={() => onGo('admin-daily-count')}>
              Quantidade do dia
            </Button>
          </>
        ) : (
          <>
            <Button color="inherit" onClick={() => onGo('profile')}>
              Meu perfil
            </Button>
            <Button color="inherit" onClick={() => onGo('available')}>
              Solicitar locação
            </Button>
            <Button color="inherit" onClick={() => onGo('rentals')}>
              Minhas locações
            </Button>
          </>
        )}
      </Toolbar>
    </AppBar>
  )
}

export default function App() {
  const [screen, setScreen] = useState<Screen>(homeScreen)
  const [draft, setDraft] = useState<RentalDraft | null>(null)
  const [rentalId, setRentalId] = useState<number | null>(null)
  const [renterId, setRenterId] = useState<number | null>(null)

  const visible = allowedScreen(screen)
  const papel = getPapel()
  const showNav = getToken() !== null && papel !== null

  function go(next: Screen) {
    setScreen(allowedScreen(next))
  }

  let page = (
    <LoginPage onLoggedIn={() => go(homeScreen())} onGoToRegister={() => go('register')} />
  )

  if (visible === 'register') {
    page = <RegisterPage onRegistered={() => go('login')} onGoToLogin={() => go('login')} />
  } else if (visible === 'available') {
    page = (
      <AvailableVehiclesPage
        onSelectVehicle={(nextDraft) => {
          setDraft(nextDraft)
          go('new-rental')
        }}
        onBack={() => go('profile')}
      />
    )
  } else if (visible === 'new-rental' && draft !== null) {
    page = <NewRentalPage draft={draft} onBack={() => go('available')} />
  } else if (visible === 'new-rental') {
    page = (
      <AvailableVehiclesPage
        onSelectVehicle={(nextDraft) => {
          setDraft(nextDraft)
          go('new-rental')
        }}
        onBack={() => go('profile')}
      />
    )
  } else if (visible === 'rentals') {
    page = (
      <MyRentalsPage
        onOpenRental={(id) => {
          setRentalId(id)
          go('rental-detail')
        }}
        onBack={() => go('profile')}
      />
    )
  } else if (visible === 'rental-detail' && rentalId !== null) {
    page = <RentalDetailPage rentalId={rentalId} onBack={() => go('rentals')} />
  } else if (visible === 'admin-renters') {
    page = (
      <AdminRentersPage
        onOpenRenter={(id) => {
          setRenterId(id)
          go('admin-renter-detail')
        }}
        onGoToRentals={() => go('admin-rentals')}
        onGoToDailyCount={() => go('admin-daily-count')}
        onLogout={() => go('login')}
      />
    )
  } else if (visible === 'admin-renter-detail' && renterId !== null) {
    page = <AdminRenterDetailPage renterId={renterId} onBack={() => go('admin-renters')} />
  } else if (visible === 'admin-rentals') {
    page = (
      <AdminRentalsPage
        onOpenRental={(id) => {
          setRentalId(id)
          go('admin-rental-detail')
        }}
        onBack={() => go('admin-renters')}
      />
    )
  } else if (visible === 'admin-rental-detail' && rentalId !== null) {
    page = <AdminRentalDetailPage rentalId={rentalId} onBack={() => go('admin-rentals')} />
  } else if (visible === 'admin-daily-count') {
    page = <AdminDailyCountPage onBack={() => go('admin-renters')} />
  } else if (visible === 'profile') {
    page = (
      <ProfilePage
        onLogout={() => go('login')}
        onGoToVehicles={() => go('available')}
        onGoToRentals={() => go('rentals')}
      />
    )
  }

  return (
    <>
      {showNav && papel !== null ? <RoleNav papel={papel} onGo={go} /> : null}
      {page}
    </>
  )
}
