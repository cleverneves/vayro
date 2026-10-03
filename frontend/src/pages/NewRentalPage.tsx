import { useState } from 'react'
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
import { ApiRequestError, createRental } from '../api/client'
import type { Motivo, Rental } from '../api/client'
import type { RentalDraft } from './AvailableVehiclesPage'

type NewRentalPageProps = {
  draft: RentalDraft
  onBack: () => void
}

export default function NewRentalPage({ draft, onBack }: NewRentalPageProps) {
  const [motivo, setMotivo] = useState<Motivo>('viagem')
  const [comentario, setComentario] = useState('')
  const [errors, setErrors] = useState<string[]>([])
  const [created, setCreated] = useState<Rental | null>(null)
  const [submitting, setSubmitting] = useState(false)

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setErrors([])
    setSubmitting(true)

    try {
      const rental = await createRental({
        automovel_id: draft.vehicle.id,
        data_inicio: draft.dataInicio,
        quantidade_dias: draft.quantidadeDias,
        motivo,
        ...(comentario !== '' ? { comentario } : {}),
      })
      setCreated(rental)
    } catch (error) {
      if (error instanceof ApiRequestError) {
        setErrors(error.fieldErrors())
      } else {
        setErrors(['Não foi possível solicitar a locação.'])
      }
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <Container maxWidth="sm">
      <Stack spacing={2} sx={{ mt: 4 }}>
        <Typography variant="h4" component="h1">
          Solicitar locação
        </Typography>
        <Typography>
          {draft.vehicle.placa} — {draft.vehicle.modelo.marca.nome} {draft.vehicle.modelo.nome}
        </Typography>
        <Typography>
          {draft.dataInicio} · {draft.quantidadeDias} dia(s)
        </Typography>
        {created ? (
          <>
            <Alert severity="success">
              Locação {created.status} de {created.periodo.data_inicio} a {created.periodo.data_fim}.
            </Alert>
            <Button type="button" onClick={onBack}>
              Voltar
            </Button>
          </>
        ) : (
          <Stack component="form" spacing={2} onSubmit={handleSubmit}>
            {errors.map((message) => (
              <Alert key={message} severity="error">
                {message}
              </Alert>
            ))}
            <FormControl>
              <InputLabel id="motivo-label">Motivo</InputLabel>
              <Select
                labelId="motivo-label"
                label="Motivo"
                value={motivo}
                onChange={(event) => setMotivo(event.target.value as Motivo)}
              >
                <MenuItem value="viagem">Viagem</MenuItem>
                <MenuItem value="passeio">Passeio</MenuItem>
                <MenuItem value="dia-a-dia">Dia a dia</MenuItem>
              </Select>
            </FormControl>
            <TextField
              label="Comentário (opcional)"
              value={comentario}
              onChange={(event) => setComentario(event.target.value)}
              multiline
              minRows={2}
              inputProps={{ maxLength: 500 }}
            />
            <Button type="submit" variant="contained" disabled={submitting}>
              Confirmar solicitação
            </Button>
            <Button type="button" onClick={onBack}>
              Voltar
            </Button>
          </Stack>
        )}
      </Stack>
    </Container>
  )
}
