<script setup>
import { onMounted, ref } from 'vue'
import { api } from '../api/client.js'

const baseUrl = import.meta.env.VITE_API_URL
const health = ref('belum dicek')

onMounted(async () => {
  try {
    health.value = (await api.health()).status
  } catch (err) {
    health.value = `tidak bisa dihubungi (${err.message})`
  }
})
</script>

<template>
  <section>
    <div class="card">
      <h2 style="margin-top: 0">Tentang aplikasi ini</h2>
      <p>
        Halaman Vue 3 di folder <code>frontend/</code>. Data tugas diambil dari endpoint JSON
        Laravel <code>GET /api/tasks</code>.
      </p>
    </div>

    <div class="card">
      <h3 style="margin-top: 0">Alamat API</h3>
      <p>
        Alamat API dibaca dari environment variable <code>VITE_API_URL</code>, tidak ditulis
        langsung di dalam kode. Nilai yang sedang dipakai:
      </p>
      <pre>{{ baseUrl || 'VITE_API_URL belum diset' }}</pre>
      <p>
        Health check <code>GET {{ baseUrl || '' }}/health</code> &rarr;
        <strong>{{ health }}</strong>
      </p>
    </div>

    <div class="card">
      <h3 style="margin-top: 0">Kontrak endpoint</h3>
      <table>
        <thead>
          <tr>
            <th>Method</th>
            <th>Path</th>
            <th>Kegunaan</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>GET</td>
            <td>/api/tasks</td>
            <td>Daftar seluruh tugas</td>
          </tr>
          <tr>
            <td>POST</td>
            <td>/api/tasks</td>
            <td>Menambah tugas</td>
          </tr>
          <tr>
            <td>PUT</td>
            <td>/api/tasks/{id}</td>
            <td>Mengubah status selesai</td>
          </tr>
          <tr>
            <td>DELETE</td>
            <td>/api/tasks/{id}</td>
            <td>Menghapus tugas</td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>
</template>
