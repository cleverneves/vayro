import { useEffect, useState } from 'react'
import Alert from '@mui/material/Alert'
import Button from '@mui/material/Button'
import Container from '@mui/material/Container'
import Stack from '@mui/material/Stack'
import Typography from '@mui/material/Typography'
import { ApiRequestError, listMyRentals } from '../api/client'
import type { Rental } from '../api/client'

type AdminRentalsPageProps = {
  onOpenRental: (id: number) => void
  onBack: () => void
}

export default function AdminRentalsPage({ onOpenRental, onBack }: AdminRentalsPageProps) {
  const [rentals, setRentals] = useState<Rental[]>([])
  const [errors, setErrors] = useState<string[]>([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    let cancelled = false

    listMyRentals()
      .then((items) => {
        if (!cancelled) {
          setRentals(items)
        }
      })
      .catch((error: unknown) => {
        if (cancelled) {
          return
        }
        if (error instanceof ApiRequestError) {
          setErrors(error.fieldErrors())
        } else {
          setErrors(['Não foi possível carregar as locações.'])
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

  return (
    <Container maxWidth="sm">
      <Stack spacing={2} sx={{ mt: 4 }}>
        <Typography variant="h4" component="h1">
          Locações
        </Typography>
        {errors.map((message) => (
          <Alert key={message} severity="error">
            {message}
          </Alert>
        ))}
        {!loading && rentals.length === 0 && errors.length === 0 ? (
          <Alert severity="info">Nenhuma locação cadastrada.</Alert>
        ) : null}
        {rentals.map((rental) => (
          <Stack key={rental.id} spacing={1} sx={{ border: 1, borderColor: 'divider', p: 2 }}>
            <Typography>
              {rental.automovel.placa} · {rental.status}
            </Typography>
            <Typography>
              {rental.locatario.nome} · {rental.periodo.data_inicio} a {rental.periodo.data_fim}
            </Typography>
            <Button variant="outlined" onClick={() => onOpenRental(rental.id)}>
              Abrir
            </Button>
          </Stack>
        ))}
        <Button type="button" onClick={onBack}>
          Voltar
        </Button>
      </Stack>
    </Container>
  )
}
