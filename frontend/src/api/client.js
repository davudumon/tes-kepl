function apiBaseUrl() {
  const url = import.meta.env.VITE_API_URL

  if (!url) {
    throw new Error(
      'VITE_API_URL belum diset. Salin frontend/.env.example ke frontend/.env lalu isi alamat API Laravel, contoh: VITE_API_URL=http://localhost:8000/api',
    )
  }

  return url.replace(/\/+$/, '')
}

export function describeApiError(err) {
  if (err instanceof TypeError) {
    return 'Tidak bisa menghubungi server Laravel. Pastikan `php artisan serve` berjalan dan alamat VITE_API_URL benar.'
  }

  return err?.message || 'Terjadi kesalahan yang tidak diketahui.'
}

async function request(path, options = {}) {
  const response = await fetch(`${apiBaseUrl()}${path}`, {
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
    ...options,
  })

  if (response.status === 204) {
    return null
  }

  const payload = await response.json().catch(() => null)

  if (!response.ok) {
    const detail = payload?.errors
      ? Object.values(payload.errors).flat().join(' ')
      : payload?.message

    throw new Error(detail || `Permintaan gagal dengan status ${response.status}.`)
  }

  return payload
}

export const api = {
  health: () => request('/health'),
  listTasks: () => request('/tasks'),
  createTask: (payload) => request('/tasks', { method: 'POST', body: JSON.stringify(payload) }),
  updateTask: (id, payload) => request(`/tasks/${id}`, { method: 'PUT', body: JSON.stringify(payload) }),
  deleteTask: (id) => request(`/tasks/${id}`, { method: 'DELETE' }),
}
