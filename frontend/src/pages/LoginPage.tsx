import { useState } from 'react'
import type { FormEvent } from 'react'
import Alert from '@mui/material/Alert'
import Button from '@mui/material/Button'
import Container from '@mui/material/Container'
import Stack from '@mui/material/Stack'
import TextField from '@mui/material/TextField'
import Typography from '@mui/material/Typography'
import { ApiRequestError, login } from '../api/client'
import { setSession } from '../auth/session'

type LoginPageProps = {
  onLoggedIn: () => void
  onGoToRegister: () => void
}

export default function LoginPage({ onLoggedIn, onGoToRegister }: LoginPageProps) {
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [errors, setErrors] = useState<string[]>([])
  const [submitting, setSubmitting] = useState(false)

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setErrors([])
    setSubmitting(true)

    try {
      const session = await login(email, password)
      setSession(session.token, session.papel)
      onLoggedIn()
    } catch (error) {
      if (error instanceof ApiRequestError) {
        setErrors(error.fieldErrors())
      } else {
        setErrors(['Não foi possível entrar.'])
      }
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <Container maxWidth="sm">
      <Stack component="form" spacing={2} sx={{ mt: 4 }} onSubmit={handleSubmit}>
        <Typography variant="h4" component="h1">
          Entrar
        </Typography>
        {errors.map((message) => (
          <Alert key={message} severity="error">
            {message}
          </Alert>
        ))}
        <TextField
          label="E-mail"
          type="email"
          value={email}
          onChange={(event) => setEmail(event.target.value)}
          required
        />
        <TextField
          label="Senha"
          type="password"
          value={password}
          onChange={(event) => setPassword(event.target.value)}
          required
        />
        <Button type="submit" variant="contained" disabled={submitting}>
          Entrar
        </Button>
        <Button type="button" onClick={onGoToRegister}>
          Criar conta de locatário
        </Button>
      </Stack>
    </Container>
  )
}
