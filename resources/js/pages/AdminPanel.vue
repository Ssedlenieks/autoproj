<template>
  <div class="admin-container">
    <div v-if="loading" class="loading">
      <div class="loading-spinner"></div>
      <p>Ielādē...</p>
    </div>

    <div v-else class="admin-content">
      <!-- Sidebar -->
      <aside class="admin-sidebar">
        <div class="sidebar-logo">Administrators</div>
        <nav class="sidebar-nav">
          <button
            v-for="tab in tabs"
            :key="tab.key"
            :class="['nav-btn', { active: activeTab === tab.key }]"
            @click="activeTab = tab.key"
          >
            {{ tab.label }}
          </button>
        </nav>
        <div class="sidebar-bottom">
          <button class="sidebar-theme-btn" @click="toggleDarkMode" :title="colorScheme === 'dark' ? 'Light Mode' : 'Dark Mode'">
            <span>{{ colorScheme === 'light' ? '🌙' : '☀️' }}</span>
            {{ colorScheme === 'light' ? 'Dark Mode' : 'Light Mode' }}
          </button>
          <button @click="$router.push('/dashboard')" class="btn-back">← Atpakaļ</button>
        </div>
      </aside>

      <!-- Main Content -->
      <main class="admin-main">

        <!-- OVERVIEW -->
        <section v-if="activeTab === 'overview'">
          <h2>Pārskats</h2>
          <div class="overview-grid">
            <div class="overview-card">
              <div class="ov-value">{{ overview.total_users }}</div>
              <div class="ov-label">Lietotāji</div>
            </div>
            <div class="overview-card">
              <div class="ov-value">{{ overview.total_projects }}</div>
              <div class="ov-label">Projekti</div>
            </div>
            <div class="overview-card">
              <div class="ov-value">{{ overview.total_achievements }}</div>
              <div class="ov-label">Sasniegumi</div>
            </div>
            <div class="overview-card">
              <div class="ov-value">{{ overview.total_challenges }}</div>
              <div class="ov-label">Uzdevumi</div>
            </div>
          </div>

          <h3 style="margin: 32px 0 16px">Top 5 Lietotāji</h3>
          <table class="admin-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Vārds</th>
                <th>Punkti</th>
                <th>Līmenis</th>
                <th>Rangs</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(u, i) in overview.top_users" :key="u.id">
                <td>{{ i + 1 }}</td>
                <td>{{ u.name }}</td>
                <td>{{ u.points }}</td>
                <td>{{ u.level }}</td>
                <td>{{ u.rank }}</td>
              </tr>
            </tbody>
          </table>
        </section>

        <!-- USERS -->
        <section v-if="activeTab === 'users'">
          <h2>Lietotāji</h2>
          <table class="admin-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Vārds</th>
                <th>E-pasts</th>
                <th>Loma</th>
                <th>Punkti</th>
                <th>Līmenis</th>
                <th>Reģistrējās</th>
                <th>Darbības</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="u in users" :key="u.id">
                <td>{{ u.id }}</td>
                <td>{{ u.name }}</td>
                <td>{{ u.email }}</td>
                <td>
                  <select v-model="u.role_id" @change="updateRole(u)" class="role-select">
                    <option :value="1">User</option>
                    <option :value="2">Editor</option>
                    <option :value="3">Admin</option>
                  </select>
                </td>
                <td>{{ u.points }}</td>
                <td>{{ u.level }}</td>
                <td>{{ formatDate(u.created_at) }}</td>
                <td>
                  <button @click="deleteUser(u.id)" class="btn-del">Dzēst</button>
                </td>
              </tr>
            </tbody>
          </table>
        </section>

        <!-- ACHIEVEMENTS -->
        <section v-if="activeTab === 'achievements'">
          <div class="section-header">
            <h2>Sasniegumi</h2>
            <button @click="openAchievementModal()" class="btn-add">+ Pievienot</button>
          </div>
          <table class="admin-table">
            <thead>
              <tr>
                <th>Ikona</th>
                <th>Nosaukums</th>
                <th>Kategorija</th>
                <th>Apraksts</th>
                <th>Punkti</th>
                <th>Darbības</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="a in achievements" :key="a.id">
                <td>{{ a.icon }}</td>
                <td>{{ a.name }}</td>
                <td><span class="category-badge">{{ a.category }}</span></td>
                <td>{{ a.description }}</td>
                <td>{{ a.points }}</td>
                <td>
                  <button @click="openAchievementModal(a)" class="btn-edit">Labot</button>
                  <button @click="deleteAchievement(a.id)" class="btn-del">Dzēst</button>
                </td>
              </tr>
            </tbody>
          </table>
        </section>

        <!-- CHALLENGES -->
        <section v-if="activeTab === 'challenges'">
          <div class="section-header">
            <h2>Dienas uzdevumi</h2>
            <button @click="openChallengeModal()" class="btn-add">+ Pievienot</button>
          </div>
          <table class="admin-table">
            <thead>
              <tr>
                <th>Atslēga</th>
                <th>Nosaukums</th>
                <th>Apraksts</th>
                <th>Punkti</th>
                <th>Darbības</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="c in challenges" :key="c.id">
                <td><code>{{ c.key }}</code></td>
                <td>{{ c.title }}</td>
                <td>{{ c.description }}</td>
                <td>{{ c.points }}</td>
                <td>
                  <button @click="openChallengeModal(c)" class="btn-edit">Labot</button>
                  <button @click="deleteChallenge(c.id)" class="btn-del">Dzēst</button>
                </td>
              </tr>
            </tbody>
          </table>
        </section>

        <!-- PROJECTS -->
        <section v-if="activeTab === 'projects'">
          <h2>Visi projekti</h2>
          <table class="admin-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nosaukums</th>
                <th>Lietotājs</th>
                <th>Automašīna</th>
                <th>HP</th>
                <th>Publisks</th>
                <th>Datums</th>
                <th>Darbības</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="p in projects" :key="p.id">
                <td>{{ p.id }}</td>
                <td>{{ p.project_name }}</td>
                <td>{{ p.user }}</td>
                <td>{{ p.car }}</td>
                <td>{{ p.final_hp }}</td>
                <td>{{ p.is_public ? 'Jā' : 'Nē' }}</td>
                <td>{{ formatDate(p.created_at) }}</td>
                <td>
                  <button @click="deleteProject(p.id)" class="btn-del">Dzēst</button>
                </td>
              </tr>
            </tbody>
          </table>
        </section>

      </main>
    </div>

    <!-- Achievement Modal -->
    <div v-if="showAchievementModal" class="modal-overlay" @click.self="showAchievementModal = false">
      <div class="modal">
        <h3>{{ editingAchievement.id ? 'Labot sasniegumu' : 'Jauns sasniegums' }}</h3>

        <label>Ikona</label>
        <input v-model="editingAchievement.icon" placeholder="🏆" />

        <label>Nosaukums</label>
        <input v-model="editingAchievement.name" placeholder="Nosaukums" required />

        <label>Slug (unikāls ID)</label>
        <input v-model="editingAchievement.slug" placeholder="piem. first-build" />

        <label>Apraksts</label>
        <input v-model="editingAchievement.description" placeholder="Apraksts" />

        <label>Kategorija</label>
        <select v-model="editingAchievement.category" class="modal-select">
          <option value="">-- Izvēlēties --</option>
          <option value="builds">builds</option>
          <option value="hp">hp</option>
          <option value="parts">parts</option>
          <option value="special">special</option>
        </select>

        <label>Prasības tips</label>
        <select v-model="editingAchievement.requirement_type" class="modal-select">
          <option value="">-- Izvēlēties --</option>
          <option value="count">count</option>
          <option value="threshold">threshold</option>
          <option value="special">special</option>
        </select>

        <label>Prasības vērtība</label>
        <input v-model.number="editingAchievement.requirement_value" type="number" placeholder="piem. 5" />

        <label>Punkti</label>
        <input v-model.number="editingAchievement.points" type="number" placeholder="100" />

        <div class="modal-actions">
          <button @click="saveAchievement" class="btn-add">Saglabāt</button>
          <button @click="showAchievementModal = false" class="btn-cancel">Atcelt</button>
        </div>
      </div>
    </div>

    <!-- Challenge Modal -->
    <div v-if="showChallengeModal" class="modal-overlay" @click.self="showChallengeModal = false">
      <div class="modal">
        <h3>{{ editingChallenge.id ? 'Labot uzdevumu' : 'Jauns uzdevums' }}</h3>
        <label>Atslēga</label>
        <input v-model="editingChallenge.key" placeholder="piem. save_build" />
        <label>Nosaukums</label>
        <input v-model="editingChallenge.title" placeholder="Nosaukums" />
        <label>Apraksts</label>
        <input v-model="editingChallenge.description" placeholder="Apraksts" />
        <label>Punkti</label>
        <input v-model.number="editingChallenge.points" type="number" placeholder="50" />
        <div class="modal-actions">
          <button @click="saveChallenge" class="btn-add">Saglabāt</button>
          <button @click="showChallengeModal = false" class="btn-cancel">Atcelt</button>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
      <div class="modal delete-modal">
        <div class="delete-icon"></div>
        <h3>Dzēst {{ deleteTarget.label }}?</h3>
        <p>Šī darbība ir neatgriezeniska.</p>
        <div class="modal-actions">
          <button @click="confirmDelete" class="btn-del-confirm">Dzēst</button>
          <button @click="showDeleteModal = false" class="btn-cancel">Atcelt</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import { toast } from 'vue3-toastify'
import { inject } from 'vue'

export default {
  setup() {
    const colorScheme = inject('colorScheme')
    const toggleDarkMode = inject('toggleDarkMode')
    return { colorScheme, toggleDarkMode }
  },

  data() {
    return {
      loading: true,
      activeTab: 'overview',
      tabs: [
        { key: 'overview',      label: 'Pārskats' },
        { key: 'users',         label: 'Lietotāji' },
        { key: 'achievements',  label: 'Sasniegumi' },
        { key: 'challenges',    label: 'Uzdevumi' },
        { key: 'projects',      label: 'Projekti' },
      ],
      overview: { total_users: 0, total_projects: 0, total_achievements: 0, total_challenges: 0, top_users: [] },
      users: [],
      achievements: [],
      challenges: [],
      projects: [],
      showAchievementModal: false,
      showChallengeModal: false,
      showDeleteModal: false,
      editingAchievement: {},
      editingChallenge: {},
      deleteTarget: { label: '', action: null },
    }
  },

  async mounted() {
    await this.loadOverview()
    await this.loadUsers()
    await this.loadAchievements()
    await this.loadChallenges()
    await this.loadProjects()
    this.loading = false
  },

  methods: {
    async loadOverview() {
      const res = await axios.get('/api/admin/overview')
      if (res.data.success) this.overview = res.data.stats
    },

    async loadUsers() {
      const res = await axios.get('/api/admin/users')
      if (res.data.success) this.users = res.data.users
    },

    async loadAchievements() {
      const res = await axios.get('/api/admin/achievements')
      if (res.data.success) this.achievements = res.data.achievements
    },

    async loadChallenges() {
      const res = await axios.get('/api/admin/challenges')
      if (res.data.success) this.challenges = res.data.challenges
    },

    async loadProjects() {
      const res = await axios.get('/api/admin/projects')
      if (res.data.success) this.projects = res.data.projects
    },

    async updateRole(user) {
      try {
        await axios.patch(`/api/admin/users/${user.id}/role`, { role_id: user.role_id })
        toast.success('Loma atjaunināta')
      } catch {
        toast.error('Neizdevās atjaunināt lomu')
      }
    },

    askDelete(label, action) {
      this.deleteTarget = { label, action }
      this.showDeleteModal = true
    },

    async confirmDelete() {
      this.showDeleteModal = false
      await this.deleteTarget.action()
    },

    deleteUser(id) {
      this.askDelete('šo lietotāju', async () => {
        try {
          await axios.delete(`/api/admin/users/${id}`)
          this.users = this.users.filter(u => u.id !== id)
          toast.success('Lietotājs dzēsts')
        } catch { toast.error('Neizdevās dzēst') }
      })
    },

    openAchievementModal(a = {}) {
      this.editingAchievement = { ...a }
      this.showAchievementModal = true
    },

    async saveAchievement() {
      try {
        if (this.editingAchievement.id) {
          await axios.put(`/api/admin/achievements/${this.editingAchievement.id}`, this.editingAchievement)
          toast.success('Sasniegums atjaunināts')
        } else {
          await axios.post('/api/admin/achievements', this.editingAchievement)
          toast.success('Sasniegums pievienots')
        }
        this.showAchievementModal = false
        await this.loadAchievements()
      } catch (e) {
        const msg = e.response?.data?.message || e.response?.data?.errors
          ? JSON.stringify(e.response.data.errors)
          : 'Neizdevās saglabāt'
        toast.error(msg)
      }
    },

    deleteAchievement(id) {
      this.askDelete('šo sasniegumu', async () => {
        try {
          await axios.delete(`/api/admin/achievements/${id}`)
          this.achievements = this.achievements.filter(a => a.id !== id)
          toast.success('Sasniegums dzēsts')
        } catch { toast.error('Neizdevās dzēst') }
      })
    },

    openChallengeModal(c = {}) {
      this.editingChallenge = { ...c }
      this.showChallengeModal = true
    },

    async saveChallenge() {
      try {
        if (this.editingChallenge.id) {
          await axios.put(`/api/admin/challenges/${this.editingChallenge.id}`, this.editingChallenge)
          toast.success('Uzdevums atjaunināts')
        } else {
          await axios.post('/api/admin/challenges', this.editingChallenge)
          toast.success('Uzdevums pievienots')
        }
        this.showChallengeModal = false
        await this.loadChallenges()
      } catch {
        toast.error('Neizdevās saglabāt')
      }
    },

    deleteChallenge(id) {
      this.askDelete('šo uzdevumu', async () => {
        try {
          await axios.delete(`/api/admin/challenges/${id}`)
          this.challenges = this.challenges.filter(c => c.id !== id)
          toast.success('Uzdevums dzēsts')
        } catch { toast.error('Neizdevās dzēst') }
      })
    },

    deleteProject(id) {
      this.askDelete('šo projektu', async () => {
        try {
          await axios.delete(`/api/admin/projects/${id}`)
          this.projects = this.projects.filter(p => p.id !== id)
          toast.success('Projekts dzēsts')
        } catch { toast.error('Neizdevās dzēst') }
      })
    },

    formatDate(date) {
      return new Date(date).toLocaleDateString('lv-LV', {
        year: 'numeric', month: 'short', day: 'numeric',
      })
    },
  },
}
</script>

<style scoped>
.admin-container {
  min-height: 100vh;
  background: #f8fafc;
  display: flex;
}

html[data-color-scheme='dark'] .admin-container {
  background: #0a0a0a;
  color: #f5f5f5;
}

.loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  width: 100%;
}

.loading-spinner {
  width: 50px;
  height: 50px;
  border: 4px solid rgba(16, 185, 129, 0.2);
  border-top-color: #10b981;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin-bottom: 20px;
}

@keyframes spin { to { transform: rotate(360deg); } }

.admin-content {
  display: flex;
  width: 100%;
  min-height: 100vh;
}

.admin-sidebar {
  width: 220px;
  background: #1e293b;
  display: flex;
  flex-direction: column;
  padding: 24px 16px;
  gap: 8px;
  position: sticky;
  top: 0;
  height: 100vh;
}

html[data-color-scheme='dark'] .admin-sidebar { background: #111; }

.sidebar-logo {
  font-size: 1.3rem;
  font-weight: 700;
  color: #10b981;
  padding: 0 8px 16px;
  border-bottom: 1px solid #334155;
  margin-bottom: 8px;
}

html[data-color-scheme='dark'] .sidebar-logo {
  color: #ffd700;
  border-color: #2d2d2d;
}

.nav-btn {
  padding: 10px 14px;
  border: none;
  border-radius: 8px;
  background: transparent;
  color: #94a3b8;
  font-size: 0.95rem;
  font-weight: 500;
  cursor: pointer;
  text-align: left;
  transition: all 0.2s;
}

.nav-btn:hover { background: #334155; color: #f1f5f9; }

.nav-btn.active { background: #10b981; color: white; }

html[data-color-scheme='dark'] .nav-btn.active { background: #ffd700; color: #000; }

.sidebar-bottom {
  margin-top: auto;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.sidebar-theme-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 14px;
  border: 1px solid #334155;
  border-radius: 8px;
  background: transparent;
  color: #94a3b8;
  cursor: pointer;
  font-size: 0.9rem;
  transition: all 0.2s;
  width: 100%;
  text-align: left;
}

.sidebar-theme-btn:hover { border-color: #10b981; color: #10b981; }
html[data-color-scheme='dark'] .sidebar-theme-btn:hover { border-color: #ffd700; color: #ffd700; }

.btn-back {
  padding: 10px 14px;
  border: 1px solid #334155;
  border-radius: 8px;
  background: transparent;
  color: #94a3b8;
  cursor: pointer;
  font-size: 0.9rem;
  transition: all 0.2s;
}

.btn-back:hover { border-color: #10b981; color: #10b981; }

.admin-main {
  flex: 1;
  padding: 40px;
  overflow-y: auto;
}

.admin-main h2 {
  font-size: 1.8rem;
  color: #1e293b;
  margin-bottom: 24px;
}

html[data-color-scheme='dark'] .admin-main h2 { color: #f5f5f5; }
.admin-main h3 { color: #1e293b; }
html[data-color-scheme='dark'] .admin-main h3 { color: #f5f5f5; }

.overview-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 20px;
  margin-bottom: 16px;
}

.overview-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  text-align: center;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

html[data-color-scheme='dark'] .overview-card { background: #1a1a1a; }

.ov-value { font-size: 2.5rem; font-weight: 700; color: #10b981; }
html[data-color-scheme='dark'] .ov-value { color: #ffd700; }
.ov-label { color: #64748b; font-size: 0.95rem; margin-top: 4px; }

.admin-table {
  width: 100%;
  border-collapse: collapse;
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

html[data-color-scheme='dark'] .admin-table { background: #1a1a1a; }

.admin-table th {
  background: #f1f5f9;
  padding: 12px 16px;
  text-align: left;
  font-size: 0.85rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

html[data-color-scheme='dark'] .admin-table th { background: #111; color: #94a3b8; }

.admin-table td {
  padding: 12px 16px;
  border-top: 1px solid #f1f5f9;
  color: #1e293b;
  font-size: 0.95rem;
}

html[data-color-scheme='dark'] .admin-table td { border-color: #2d2d2d; color: #f5f5f5; }
.admin-table tr:hover td { background: #f8fafc; }
html[data-color-scheme='dark'] .admin-table tr:hover td { background: #222; }

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.category-badge {
  padding: 3px 10px;
  background: #f0fdf4;
  color: #065f46;
  border-radius: 20px;
  font-size: 0.78rem;
  font-weight: 600;
}

html[data-color-scheme='dark'] .category-badge { background: #064e3b; color: #6ee7b7; }

.btn-del {
  padding: 5px 12px;
  background: #fee2e2;
  color: #dc2626;
  border: none;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  margin-left: 6px;
}

.btn-del:hover { background: #dc2626; color: white; }

.btn-edit {
  padding: 5px 12px;
  background: #dbeafe;
  color: #1d4ed8;
  border: none;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-edit:hover { background: #1d4ed8; color: white; }

.btn-add {
  padding: 10px 20px;
  background: #10b981;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

html[data-color-scheme='dark'] .btn-add { background: #ffd700; color: #000; }
.btn-add:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(16,185,129,0.3); }

.btn-cancel {
  padding: 10px 20px;
  background: #f1f5f9;
  color: #64748b;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
}

html[data-color-scheme='dark'] .btn-cancel { background: #2d2d2d; color: #94a3b8; }

.role-select {
  padding: 4px 8px;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  background: white;
  font-size: 0.85rem;
  cursor: pointer;
}

html[data-color-scheme='dark'] .role-select { background: #2d2d2d; border-color: #404040; color: #f5f5f5; }

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  backdrop-filter: blur(4px);
}

.modal {
  background: white;
  border-radius: 16px;
  padding: 32px;
  width: 480px;
  max-height: 90vh;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 12px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.2);
}

html[data-color-scheme='dark'] .modal { background: #1a1a1a; }

.modal h3 { font-size: 1.3rem; color: #1e293b; margin-bottom: 4px; }
html[data-color-scheme='dark'] .modal h3 { color: #f5f5f5; }

.modal label { font-size: 0.85rem; font-weight: 600; color: #64748b; margin-bottom: -6px; }

.modal input {
  padding: 10px 14px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.95rem;
  background: #f8fafc;
  color: #1e293b;
  outline: none;
  transition: border-color 0.2s;
}

.modal input:focus { border-color: #10b981; }

html[data-color-scheme='dark'] .modal input { background: #2d2d2d; border-color: #404040; color: #f5f5f5; }

.modal-select {
  padding: 10px 14px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.95rem;
  background: #f8fafc;
  color: #1e293b;
  cursor: pointer;
  outline: none;
  transition: border-color 0.2s;
}

.modal-select:focus { border-color: #10b981; }

html[data-color-scheme='dark'] .modal-select { background: #2d2d2d; border-color: #404040; color: #f5f5f5; }

.modal-actions { display: flex; gap: 12px; margin-top: 8px; }

/* Delete Modal */
.delete-modal {
  width: 360px;
  text-align: center;
  padding: 40px 32px;
  align-items: center;
}

.delete-icon { font-size: 3rem; margin-bottom: 4px; }

.delete-modal p {
  color: #94a3b8;
  font-size: 0.9rem;
  margin-bottom: 4px;
  max-width: 100%;
}

.delete-modal .modal-actions { justify-content: center; }

.btn-del-confirm {
  padding: 10px 24px;
  background: #dc2626;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-del-confirm:hover { background: #b91c1c; transform: translateY(-1px); }
</style>