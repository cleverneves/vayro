import { useEffect, useState } from 'react'
import Alert from '@mui/material/Alert'
import Button from '@mui/material/Button'
import Container from '@mui/material/Container'
import Stack from '@mui/material/Stack'
import Typography from '@mui/material/Typography'
import { ApiRequestError, getMyRental } from '../api/client'
import type { Rental } from '../api/client'

type RentalDetailPageProps = {
  rentalId: number
  onBack: () => void
}

export default function RentalDetailPage({ rentalId, onBack }: RentalDetailPageProps) {
  const [rental, setRental] = useState<Rental | null>(null)
  const [errors, setErrors] = useState<string[]>([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    let cancelled = false

    getMyRental(rentalId)
      .then((item) => {
        if (!cancelled) {
          setRental(item)
        }
      })
      .catch((error: unknown) => {
        if (cancelled) {
          return
        }
        if (error instanceof ApiRequestError) {
          setErrors(error.fieldErrors())
        } else {
          setErrors(['Não foi possível carregar a locação.'])
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
  }, [rentalId])

  return (
    <Container maxWidth="sm">
      <Stack spacing={2} sx={{ mt: 4 }}>
        <Typography variant="h4" component="h1">
          Detalhe da locação
        </Typography>
        {errors.map((message) => (
          <Alert key={message} severity="error">
            {message}
          </Alert>
        ))}
        {rental ? (
          <>
            <Typography>
              {rental.automovel.placa} — {rental.automovel.modelo.marca.nome}{' '}
              {rental.automovel.modelo.nome}
            </Typography>
            <Typography>Status: {rental.status}</Typography>
            <Typography>
              Período: {rental.periodo.data_inicio} a {rental.periodo.data_fim} (
              {rental.quantidade_dias} dia(s))
            </Typography>
            <Typography>Motivo: {rental.motivo}</Typography>
            <Typography>Comentário: {rental.comentario ?? '—'}</Typography>
            <Typography>Observação: {rental.observacao ?? '—'}</Typography>
            <Typography>Solicitada em: {rental.data_solicitacao}</Typography>
          </>
        ) : null}
        {!loading && rental === null && errors.length === 0 ? (
          <Alert severity="info">Locação não encontrada.</Alert>
        ) : null}
        <Button type="button" onClick={onBack}>
          Voltar
        </Button>
      </Stack>
    </Container>
  )
}
