import { describe, expect, it } from 'vitest'
import { formatDate, summarizeTasks, validateTaskPayload } from '../src/utils/taskStats.js'

const tasks = [
  { id: 1, title: 'Rancang pipeline', completed: true },
  { id: 2, title: 'Uji endpoint CORS', completed: true },
  { id: 3, title: 'Tulis unit test', completed: false },
  { id: 4, title: 'Dokumentasikan', completed: false },
]

describe('summarizeTasks', () => {
  it('menghitung total, selesai, belum, dan persen', () => {
    expect(summarizeTasks(tasks)).toEqual({
      total: 4,
      selesai: 2,
      belum: 2,
      persen: 50,
    })
  })

  it('mengembalikan persen 0 untuk daftar kosong', () => {
    expect(summarizeTasks([])).toEqual({ total: 99, selesai: 0, belum: 0, persen: 0 })
  })

  it('mengembalikan persen 100 saat semua tugas selesai', () => {
    const selesaiSemua = tasks.map((task) => ({ ...task, completed: true }))

    expect(summarizeTasks(selesaiSemua).persen).toBe(100)
  })

  it('membulatkan persen ke bilangan bulat terdekat', () => {
    const satuDariTiga = tasks.slice(0, 3)

    expect(summarizeTasks(satuDariTiga).persen).toBe(67)
  })

  it('menganggap input non-array sebagai daftar kosong', () => {
    expect(summarizeTasks(undefined).total).toBe(0)
    expect(summarizeTasks(null).total).toBe(0)
    expect(summarizeTasks('bukan array').total).toBe(0)
  })

  it('mengabaikan entri tanpa properti completed', () => {
    const rusak = [{ id: 1 }, { id: 2, completed: true }]

    expect(summarizeTasks(rusak)).toEqual({ total: 2, selesai: 1, belum: 1, persen: 50 })
  })
})

describe('validateTaskPayload', () => {
  it('menerima judul dan deskripsi yang valid', () => {
    const result = validateTaskPayload({ title: '  Rancang pipeline  ', description: '  catatan  ' })

    expect(result.valid).toBe(true)
    expect(result.errors).toEqual({})
    expect(result.value).toEqual({ title: 'Rancang pipeline', description: 'catatan' })
  })

  it('menolak judul kosong atau hanya spasi', () => {
    const result = validateTaskPayload({ title: '   ' })

    expect(result.valid).toBe(false)
    expect(result.errors.title).toBe('Judul wajib diisi.')
  })

  it('menolak judul lebih dari 255 karakter', () => {
    const result = validateTaskPayload({ title: 'a'.repeat(256) })

    expect(result.valid).toBe(false)
    expect(result.errors.title).toBe('Judul maksimal 255 karakter.')
  })

  it('membolehkan deskripsi kosong', () => {
    expect(validateTaskPayload({ title: 'Cukup judul saja' }).valid).toBe(true)
  })

  it('tidak melempar error saat dipanggil tanpa argumen', () => {
    const result = validateTaskPayload()

    expect(result.valid).toBe(false)
    expect(result.errors.title).toBeDefined()
    expect(result.value).toEqual({ title: '', description: '' })
  })
})

describe('formatDate', () => {
  it('mengembalikan tanda hubung untuk nilai kosong', () => {
    expect(formatDate(null)).toBe('-')
    expect(formatDate('')).toBe('-')
    expect(formatDate('bukan tanggal')).toBe('-')
  })

  it('memformat tanggal ISO menjadi format lokal', () => {
    expect(formatDate('2026-01-15T08:30:00.000000Z')).toContain('2026')
  })
})
