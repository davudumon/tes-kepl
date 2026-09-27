import { describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'
import TaskItem from '../src/components/TaskItem.vue'

const task = {
  id: 7,
  title: 'D deploying artefak dist',
  description: 'Job deploy tidak boleh build ulang',
  completed: false,
  created_at: '2026-01-15T08:30:00.000000Z',
}

describe('TaskItem.vue', () => {
  it('menampilkan judul, deskripsi, dan status belum selesai', () => {
    const wrapper = mount(TaskItem, { props: { task } })

    expect(wrapper.text()).toContain('D deploying artefak dist')
    expect(wrapper.text()).toContain('Job deploy tidak boleh build ulang')
    expect(wrapper.text()).toContain('Belum')
  })

  it('mengubah tombol aksi ketika tugas sudah selesai', () => {
    const wrapper = mount(TaskItem, { props: { task: { ...task, completed: true } } })

    expect(wrapper.text()).toContain('Selesai')
    expect(wrapper.text()).toContain('Batalkan')
  })

  it('meneruskan aksi toggle dan remove ke parent', async () => {
    const wrapper = mount(TaskItem, { props: { task } })

    await wrapper.findAll('button')[0].trigger('click')
    await wrapper.findAll('button')[1].trigger('click')

    expect(wrapper.emitted('toggle')).toEqual([[task]])
    expect(wrapper.emitted('remove')).toEqual([[task]])
  })

  it('menonaktifkan tombol ketika sedang menyimpan', () => {
    const wrapper = mount(TaskItem, { props: { task, busy: true } })

    for (const button of wrapper.findAll('button')) {
      expect(button.attributes('disabled')).toBeDefined()
    }
  })
})
