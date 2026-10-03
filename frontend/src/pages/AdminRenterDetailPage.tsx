import { useEffect, useState } from 'react'
import Alert from '@mui/material/Alert'
import Button from '@mui/material/Button'
import Container from '@mui/material/Container'
import Stack from '@mui/material/Stack'
import Typography from '@mui/material/Typography'
import { ApiRequestError, getRenter } from '../api/client'
import type { RenterProfile } from '../api/client'

type AdminRenterDetailPageProps = {
  renterId: number
  onBack: () => void
}

export default function AdminRenterDetailPage({ renterId, onBack }: AdminRenterDetailPageProps) {
  const [renter, setRenter] = useState<RenterProfile | null>(null)
  const [errors, setErrors] = useState<string[]>([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    let cancelled = false

    getRenter(renterId)
      .then((item) => {
        if (!cancelled) {
          setRenter(item)
        }
      })
      .catch((error: unknown) => {
        if (cancelled) {
          return
        }
        if (error instanceof ApiRequestError) {
          setErrors(error.fieldErrors())
        } else {
          setErrors(['Não foi possível carregar o locatário.'])
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
  }, [renterId])

  return (
    <Container maxWidth="sm">
      <Stack spacing={2} sx={{ mt: 4 }}>
        <Typography variant="h4" component="h1">
          Detalhe do locatário
        </Typography>
        {errors.map((message) => (
          <Alert key={message} severity="error">
            {message}
          </Alert>
        ))}
        {renter ? (
          <>
            <Typography>Nome: {renter.nome}</Typography>
            <Typography>E-mail: {renter.email ?? '—'}</Typography>
            <Typography>Telefone: {renter.telefone ?? '—'}</Typography>
          </>
        ) : null}
        {!loading && renter === null && errors.length === 0 ? (
          <Alert severity="info">Locatário não encontrado.</Alert>
        ) : null}
        <Button type="button" onClick={onBack}>
          Voltar
        </Button>
      </Stack>
    </Container>
  )
}
