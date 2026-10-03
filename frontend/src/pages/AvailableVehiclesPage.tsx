import { useState } from 'react'
import type { FormEvent } from 'react'
import Alert from '@mui/material/Alert'
import Button from '@mui/material/Button'
import Container from '@mui/material/Container'
import Stack from '@mui/material/Stack'
import TextField from '@mui/material/TextField'
import Typography from '@mui/material/Typography'
import { ApiRequestError, listAvailableVehicles } from '../api/client'
import type { AvailableVehicle } from '../api/client'

export type RentalDraft = {
  vehicle: AvailableVehicle
  dataInicio: string
  quantidadeDias: number
}

type AvailableVehiclesPageProps = {
  onSelectVehicle: (draft: RentalDraft) => void
  onBack: () => void
}

function todayLocalDate(): string {
  return new Date().toLocaleDateString('en-CA')
}

export default function AvailableVehiclesPage({
  onSelectVehicle,
  onBack,
}: AvailableVehiclesPageProps) {
  const [dataInicio, setDataInicio] = useState(todayLocalDate)
  const [quantidadeDias, setQuantidadeDias] = useState('1')
  const [vehicles, setVehicles] = useState<AvailableVehicle[]>([])
  const [searched, setSearched] = useState(false)
  const [errors, setErrors] = useState<string[]>([])
  const [submitting, setSubmitting] = useState(false)

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setErrors([])
    setSubmitting(true)

    try {
      const result = await listAvailableVehicles(dataInicio, Number(quantidadeDias))
      setVehicles(result)
      setSearched(true)
    } catch (error) {
      setSearched(false)
      setVehicles([])
      if (error instanceof ApiRequestError) {
        setErrors(error.fieldErrors())
      } else {
        setErrors(['Não foi possível listar os automóveis.'])
      }
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <Container maxWidth="sm">
      <Stack spacing={2} sx={{ mt: 4 }}>
        <Typography variant="h4" component="h1">
          Automóveis disponíveis
        </Typography>
        <Stack component="form" spacing={2} onSubmit={handleSubmit}>
          {errors.map((message) => (
            <Alert key={message} severity="error">
              {message}
            </Alert>
          ))}
          <TextField
            label="Data de início"
            type="date"
            value={dataInicio}
            onChange={(event) => setDataInicio(event.target.value)}
            required
            InputLabelProps={{ shrink: true }}
          />
          <TextField
            label="Quantidade de dias"
            type="number"
            value={quantidadeDias}
            onChange={(event) => setQuantidadeDias(event.target.value)}
            required
            inputProps={{ min: 1, max: 90 }}
          />
          <Button type="submit" variant="contained" disabled={submitting}>
            Buscar
          </Button>
        </Stack>
        {searched && vehicles.length === 0 ? (
          <Alert severity="info">Nenhum automóvel disponível neste período.</Alert>
        ) : null}
        {vehicles.map((vehicle) => (
          <Stack key={vehicle.id} spacing={1} sx={{ border: 1, borderColor: 'divider', p: 2 }}>
            <Typography>
              {vehicle.placa} — {vehicle.modelo.marca.nome} {vehicle.modelo.nome}
            </Typography>
            <Button
              variant="outlined"
              onClick={() =>
                onSelectVehicle({
                  vehicle,
                  dataInicio,
                  quantidadeDias: Number(quantidadeDias),
                })
              }
            >
              Solicitar
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
