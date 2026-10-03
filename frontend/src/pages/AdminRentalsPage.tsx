import { useEffect, useState } from 'react'
import Alert from '@mui/material/Alert'
import Button from '@mui/material/Button'
import Container from '@mui/material/Container'
import Stack from '@mui/material/Stack'
import Typography from '@mui/material/Typography'
import { ApiRequestError, getMyRental, listMyRentals } from '../api/client'
import type { Rental } from '../api/client'

type AdminRentalsPageProps = {
  onBack: () => void
}

export default function AdminRentalsPage({ onBack }: AdminRentalsPageProps) {
  const [rentals, setRentals] = useState<Rental[]>([])
  const [selected, setSelected] = useState<Rental | null>(null)
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

  async function handleOpen(id: number) {
    setErrors([])

    try {
      setSelected(await getMyRental(id))
    } catch (error) {
      if (error instanceof ApiRequestError) {
        setErrors(error.fieldErrors())
      } else {
        setErrors(['Não foi possível carregar a locação.'])
      }
    }
  }

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
        {selected ? (
          <Stack spacing={1} sx={{ border: 1, borderColor: 'divider', p: 2 }}>
            <Typography>
              {selected.automovel.placa} · {selected.status}
            </Typography>
            <Typography>
              {selected.locatario.nome} · {selected.periodo.data_inicio} a{' '}
              {selected.periodo.data_fim}
            </Typography>
            <Typography>Motivo: {selected.motivo}</Typography>
            <Typography>Comentário: {selected.comentario ?? '—'}</Typography>
            <Typography>Observação: {selected.observacao ?? '—'}</Typography>
            <Button type="button" onClick={() => setSelected(null)}>
              Fechar detalhe
            </Button>
          </Stack>
        ) : null}
        {rentals.map((rental) => (
          <Stack key={rental.id} spacing={1} sx={{ border: 1, borderColor: 'divider', p: 2 }}>
            <Typography>
              {rental.automovel.placa} · {rental.status}
            </Typography>
            <Typography>
              {rental.locatario.nome} · {rental.periodo.data_inicio} a {rental.periodo.data_fim}
            </Typography>
            <Button variant="outlined" onClick={() => void handleOpen(rental.id)}>
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
