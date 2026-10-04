import { useEffect, useState } from 'react'
import Alert from '@mui/material/Alert'
import Button from '@mui/material/Button'
import Container from '@mui/material/Container'
import Stack from '@mui/material/Stack'
import Typography from '@mui/material/Typography'
import { ApiRequestError, listRenters } from '../api/client'
import type { RenterProfile } from '../api/client'
import { clearSession } from '../auth/session'

type AdminRentersPageProps = {
  onOpenRenter: (id: number) => void
  onGoToRentals: () => void
  onGoToDailyCount: () => void
  onLogout: () => void
}

export default function AdminRentersPage({
  onOpenRenter,
  onGoToRentals,
  onGoToDailyCount,
  onLogout,
}: AdminRentersPageProps) {
  const [renters, setRenters] = useState<RenterProfile[]>([])
  const [errors, setErrors] = useState<string[]>([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    let cancelled = false

    listRenters()
      .then((items) => {
        if (!cancelled) {
          setRenters(items)
        }
      })
      .catch((error: unknown) => {
        if (cancelled) {
          return
        }
        if (error instanceof ApiRequestError) {
          setErrors(error.fieldErrors())
        } else {
          setErrors(['Não foi possível carregar os locatários.'])
        }
      })
      .finally(() => {
        if (!cancelled) {
          setLoading(false)
        }
      })

    return () => {
      cancelled = true
    }
  }, [])

  function handleLogout() {
    clearSession()
    onLogout()
  }

  return (
    <Container maxWidth="sm">
      <Stack spacing={2} sx={{ mt: 4 }}>
        <Typography variant="h4" component="h1">
          Locatários
        </Typography>
        {errors.map((message) => (
          <Alert key={message} severity="error">
            {message}
          </Alert>
        ))}
        {!loading && renters.length === 0 && errors.length === 0 ? (
          <Alert severity="info">Nenhum locatário cadastrado.</Alert>
        ) : null}
        {renters.map((renter) => (
          <Stack key={renter.id} spacing={1} sx={{ border: 1, borderColor: 'divider', p: 2 }}>
            <Typography>{renter.nome}</Typography>
            <Typography>{renter.email ?? 'Sem e-mail'}</Typography>
            <Button variant="outlined" onClick={() => onOpenRenter(renter.id)}>
              Abrir
            </Button>
          </Stack>
        ))}
        <Button type="button" onClick={onGoToRentals}>
          Locações
        </Button>
        <Button type="button" onClick={onGoToDailyCount}>
          Quantidade do dia
        </Button>
        <Button type="button" onClick={handleLogout}>
          Sair
        </Button>
      </Stack>
    </Container>
  )
}
