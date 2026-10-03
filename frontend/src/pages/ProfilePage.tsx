import { useEffect, useState } from 'react'
import type { FormEvent } from 'react'
import Alert from '@mui/material/Alert'
import Button from '@mui/material/Button'
import Container from '@mui/material/Container'
import Stack from '@mui/material/Stack'
import TextField from '@mui/material/TextField'
import Typography from '@mui/material/Typography'
import { ApiRequestError, getMyProfile, updateMyProfile } from '../api/client'
import { clearSession } from '../auth/session'

type ProfilePageProps = {
  onLogout: () => void
  onGoToVehicles: () => void
  onGoToRentals: () => void
}

export default function ProfilePage({ onLogout, onGoToVehicles, onGoToRentals }: ProfilePageProps) {
  const [nome, setNome] = useState('')
  const [email, setEmail] = useState('')
  const [telefone, setTelefone] = useState('')
  const [errors, setErrors] = useState<string[]>([])
  const [success, setSuccess] = useState(false)
  const [loading, setLoading] = useState(true)
  const [submitting, setSubmitting] = useState(false)

  useEffect(() => {
    let cancelled = false

    getMyProfile()
      .then((profile) => {
        if (cancelled) {
          return
        }
        setNome(profile.nome)
        setEmail(profile.email)
        setTelefone(profile.telefone)
      })
      .catch((error: unknown) => {
        if (cancelled) {
          return
        }
        if (error instanceof ApiRequestError) {
          setErrors(error.fieldErrors())
        } else {
          setErrors(['Não foi possível carregar o perfil.'])
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

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setErrors([])
    setSuccess(false)
    setSubmitting(true)

    try {
      const profile = await updateMyProfile({ nome, email, telefone })
      setNome(profile.nome)
      setEmail(profile.email)
      setTelefone(profile.telefone)
      setSuccess(true)
    } catch (error) {
      if (error instanceof ApiRequestError) {
        setErrors(error.fieldErrors())
      } else {
        setErrors(['Não foi possível atualizar o perfil.'])
      }
    } finally {
      setSubmitting(false)
    }
  }

  function handleLogout() {
    clearSession()
    onLogout()
  }

  return (
    <Container maxWidth="sm">
      <Stack component="form" spacing={2} sx={{ mt: 4 }} onSubmit={handleSubmit}>
        <Typography variant="h4" component="h1">
          Meu perfil
        </Typography>
        {errors.map((message) => (
          <Alert key={message} severity="error">
            {message}
          </Alert>
        ))}
        {success ? <Alert severity="success">Perfil atualizado.</Alert> : null}
        <TextField
          label="Nome"
          value={nome}
          onChange={(event) => setNome(event.target.value)}
          required
          disabled={loading}
          inputProps={{ maxLength: 120 }}
        />
        <TextField
          label="E-mail"
          type="email"
          value={email}
          onChange={(event) => setEmail(event.target.value)}
          required
          disabled={loading}
        />
        <TextField
          label="Telefone"
          value={telefone}
          onChange={(event) => setTelefone(event.target.value)}
          required
          disabled={loading}
          inputProps={{ maxLength: 20 }}
        />
        <Button type="submit" variant="contained" disabled={loading || submitting}>
          Salvar
        </Button>
        <Button type="button" onClick={onGoToVehicles}>
          Solicitar locação
        </Button>
        <Button type="button" onClick={onGoToRentals}>
          Minhas locações
        </Button>
        <Button type="button" onClick={handleLogout}>
          Sair
        </Button>
      </Stack>
    </Container>
  )
}
