import { useState } from 'react'
import type { FormEvent } from 'react'
import Alert from '@mui/material/Alert'
import Button from '@mui/material/Button'
import Container from '@mui/material/Container'
import Stack from '@mui/material/Stack'
import TextField from '@mui/material/TextField'
import Typography from '@mui/material/Typography'
import { ApiRequestError, registerRenter } from '../api/client'

type RegisterPageProps = {
  onRegistered: () => void
  onGoToLogin: () => void
}

export default function RegisterPage({ onRegistered, onGoToLogin }: RegisterPageProps) {
  const [nome, setNome] = useState('')
  const [email, setEmail] = useState('')
  const [telefone, setTelefone] = useState('')
  const [senha, setSenha] = useState('')
  const [errors, setErrors] = useState<string[]>([])
  const [submitting, setSubmitting] = useState(false)

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setErrors([])
    setSubmitting(true)

    try {
      await registerRenter({ nome, email, telefone, senha })
      onRegistered()
    } catch (error) {
      if (error instanceof ApiRequestError) {
        setErrors(error.fieldErrors())
      } else {
        setErrors(['Não foi possível concluir o cadastro.'])
      }
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <Container maxWidth="sm">
      <Stack component="form" spacing={2} sx={{ mt: 4 }} onSubmit={handleSubmit}>
        <Typography variant="h4" component="h1">
          Cadastro de locatário
        </Typography>
        {errors.map((message) => (
          <Alert key={message} severity="error">
            {message}
          </Alert>
        ))}
        <TextField
          label="Nome"
          value={nome}
          onChange={(event) => setNome(event.target.value)}
          required
          inputProps={{ maxLength: 120 }}
        />
        <TextField
          label="E-mail"
          type="email"
          value={email}
          onChange={(event) => setEmail(event.target.value)}
          required
        />
        <TextField
          label="Telefone"
          value={telefone}
          onChange={(event) => setTelefone(event.target.value)}
          required
          inputProps={{ maxLength: 20 }}
        />
        <TextField
          label="Senha"
          type="password"
          value={senha}
          onChange={(event) => setSenha(event.target.value)}
          required
          inputProps={{ minLength: 8 }}
        />
        <Button type="submit" variant="contained" disabled={submitting}>
          Cadastrar
        </Button>
        <Button type="button" onClick={onGoToLogin}>
          Já tenho conta
        </Button>
      </Stack>
    </Container>
  )
}
