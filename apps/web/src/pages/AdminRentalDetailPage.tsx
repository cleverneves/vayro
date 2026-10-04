import { useEffect, useState } from 'react'
import type { FormEvent } from 'react'
import Alert from '@mui/material/Alert'
import Button from '@mui/material/Button'
import Container from '@mui/material/Container'
import FormControl from '@mui/material/FormControl'
import InputLabel from '@mui/material/InputLabel'
import MenuItem from '@mui/material/MenuItem'
import Select from '@mui/material/Select'
import Stack from '@mui/material/Stack'
import TextField from '@mui/material/TextField'
import Typography from '@mui/material/Typography'
import { ApiRequestError, getMyRental, updateRental } from '../api/client'
import type { Rental, RentalStatus } from '../api/client'

type AdminRentalDetailPageProps = {
  rentalId: number
  onBack: () => void
}

const NEXT_STATUSES: Record<RentalStatus, Array<{ value: RentalStatus; label: string }>> = {
  solicitada: [
    { value: 'confirmada', label: 'Confirmada' },
    { value: 'cancelada', label: 'Cancelada' },
  ],
  confirmada: [
    { value: 'em_andamento', label: 'Em andamento' },
    { value: 'cancelada', label: 'Cancelada' },
  ],
  em_andamento: [{ value: 'concluida', label: 'Concluída' }],
  concluida: [],
  cancelada: [],
}

export default function AdminRentalDetailPage({ rentalId, onBack }: AdminRentalDetailPageProps) {
  const [rental, setRental] = useState<Rental | null>(null)
  const [nextStatus, setNextStatus] = useState('')
  const [observacao, setObservacao] = useState('')
  const [errors, setErrors] = useState<string[]>([])
  const [success, setSuccess] = useState(false)
  const [loading, setLoading] = useState(true)
  const [submitting, setSubmitting] = useState(false)

  useEffect(() => {
    let cancelled = false

    getMyRental(rentalId)
      .then((item) => {
        if (cancelled) {
          return
        }
        setRental(item)
        setObservacao(item.observacao ?? '')
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

  const closed = rental !== null && (rental.status === 'concluida' || rental.status === 'cancelada')
  const options = rental ? NEXT_STATUSES[rental.status] : []

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    if (rental === null) {
      return
    }

    setErrors([])
    setSuccess(false)
    setSubmitting(true)

    try {
      const body: { status?: RentalStatus; observacao: string } = {
        observacao,
      }
      if (nextStatus !== '') {
        body.status = nextStatus as RentalStatus
      }

      const updated = await updateRental(rental.id, body)
      setRental(updated)
      setObservacao(updated.observacao ?? '')
      setNextStatus('')
      setSuccess(true)
    } catch (error) {
      if (error instanceof ApiRequestError) {
        setErrors(error.fieldErrors())
      } else {
        setErrors(['Não foi possível atualizar a locação.'])
      }
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <Container maxWidth="sm">
      <Stack spacing={2} sx={{ mt: 4 }}>
        <Typography variant="h4" component="h1">
          Atualizar locação
        </Typography>
        {errors.map((message) => (
          <Alert key={message} severity="error">
            {message}
          </Alert>
        ))}
        {success ? <Alert severity="success">Locação atualizada.</Alert> : null}
        {rental ? (
          <>
            <Typography>
              {rental.automovel.placa} — {rental.automovel.modelo.marca.nome}{' '}
              {rental.automovel.modelo.nome}
            </Typography>
            <Typography>Locatário: {rental.locatario.nome}</Typography>
            <Typography>Status atual: {rental.status}</Typography>
            <Typography>
              Período: {rental.periodo.data_inicio} a {rental.periodo.data_fim}
            </Typography>
            {closed ? (
              <Alert severity="info">Locação encerrada. Status e observação não mudam.</Alert>
            ) : (
              <Stack component="form" spacing={2} onSubmit={handleSubmit}>
                <FormControl>
                  <InputLabel id="status-label">Novo status</InputLabel>
                  <Select
                    labelId="status-label"
                    label="Novo status"
                    value={nextStatus}
                    onChange={(event) => setNextStatus(event.target.value)}
                  >
                    <MenuItem value="">Manter status atual</MenuItem>
                    {options.map((option) => (
                      <MenuItem key={option.value} value={option.value}>
                        {option.label}
                      </MenuItem>
                    ))}
                  </Select>
                </FormControl>
                <TextField
                  label="Observação"
                  value={observacao}
                  onChange={(event) => setObservacao(event.target.value)}
                  multiline
                  minRows={2}
                  inputProps={{ maxLength: 500 }}
                />
                <Button type="submit" variant="contained" disabled={submitting}>
                  Salvar
                </Button>
              </Stack>
            )}
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
