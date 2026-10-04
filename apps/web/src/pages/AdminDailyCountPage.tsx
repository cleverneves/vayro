import { useEffect, useState } from 'react'
import Alert from '@mui/material/Alert'
import Button from '@mui/material/Button'
import Container from '@mui/material/Container'
import Stack from '@mui/material/Stack'
import Typography from '@mui/material/Typography'
import { ApiRequestError, getDailyCount } from '../api/client'
import type { DailyCount } from '../api/client'

type AdminDailyCountPageProps = {
  onBack: () => void
}

export default function AdminDailyCountPage({ onBack }: AdminDailyCountPageProps) {
  const [summary, setSummary] = useState<DailyCount | null>(null)
  const [errors, setErrors] = useState<string[]>([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    let cancelled = false

    getDailyCount()
      .then((item) => {
        if (!cancelled) {
          setSummary(item)
        }
      })
      .catch((error: unknown) => {
        if (cancelled) {
          return
        }
        if (error instanceof ApiRequestError) {
          setErrors(error.fieldErrors())
        } else {
          setErrors(['Não foi possível carregar a quantidade do dia.'])
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
          Quantidade do dia
        </Typography>
        {errors.map((message) => (
          <Alert key={message} severity="error">
            {message}
          </Alert>
        ))}
        {summary ? (
          <>
            <Typography>Data: {summary.data}</Typography>
            <Typography>Quantidade: {summary.quantidade}</Typography>
          </>
        ) : null}
        {!loading && summary === null && errors.length === 0 ? (
          <Alert severity="info">Quantidade: 0</Alert>
        ) : null}
        <Button type="button" onClick={onBack}>
          Voltar
        </Button>
      </Stack>
    </Container>
  )
}
