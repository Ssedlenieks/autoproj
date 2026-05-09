<template>
  <div class="dashboard-container">
    <div v-if="loading" class="loading">
      <div class="loading-spinner"></div>
      <p>Ielādē...</p>
    </div>

    <div v-else-if="user" class="dashboard-content">
      <header class="dashboard-header">
        <button @click="goBack" class="back-btn" title="Atpakaļ">← Atpakaļ</button>
        <div class="user-profile">
          <div class="avatar-wrapper">
            <div class="avatar-preview" @click="triggerFileInput">
              <img v-if="user.avatar_url" :src="user.avatar_url" class="avatar avatar-img" />
              <div v-else class="avatar notranslate">{{ user.name.charAt(0).toUpperCase() }}</div>
              <div class="avatar-overlay"><span class="camera-icon">UP</span></div>
            </div>
            <input ref="fileInput" type="file" accept="image/*" @change="handleImageSelect" style="display: none" />
            <div v-if="uploading" class="upload-status">Augšuplādē...</div>
            <button v-if="user.avatar_url" @click="deleteAvatar" class="btn-remove-avatar">Noņemt</button>
          </div>

          <div class="user-info">
            <h1 class="notranslate">{{ user.name }}</h1>
            <p class="user-email notranslate">{{ user.email }}</p>
            <div class="user-actions">
              <button class="btn-primary" style="margin-top: 12px;" @click="$router.push('/leaderboards')">Apskatīt līderu sarakstu</button>
              <button class="btn-explore" style="margin-top: 12px;" @click="$router.push('/explore')">Publiskie projekti</button>
              <button v-if="isEditor" class="btn-editor" style="margin-top: 12px;" @click="$router.push('/editor')">Redaktora panelis</button>
              <button v-if="isAdmin" class="btn-admin" style="margin-top: 12px;" @click="$router.push('/admin')">Administratora panelis</button>
            </div>
          </div>
        </div>

        <div class="level-badge">
          <div class="level-icon notranslate" :style="{ color: stats.level_color }">LVL</div>
          <div class="level-info">
            <span class="level-text">Level {{ stats.level }}</span>
            <span class="rank-text">{{ stats.rank }}</span>
          </div>
          <div class="xp-bar">
            <div class="xp-fill" :style="{ width: stats.level_progress + '%', background: stats.level_color }"></div>
          </div>
          <span class="xp-text">{{ stats.total_points }} / {{ stats.points_for_next_level }} XP</span>
          <span class="xp-remaining">{{ stats.points_to_next_level }} to Level {{ stats.level + 1 }}</span>
        </div>
      </header>

      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon notranslate">BLD</div>
          <div class="stat-value">{{ stats.total_builds }}</div>
          <div class="stat-label">Kopējie būvējumi</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon notranslate">ACH</div>
          <div class="stat-value">{{ stats.achievements_unlocked }}</div>
          <div class="stat-label">Sasniegumi</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon notranslate">PWR</div>
          <div class="stat-value">+{{ stats.total_hp_gained }}</div>
          <div class="stat-label">Kopējā HP</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon notranslate">TRQ</div>
          <div class="stat-value">+{{ stats.total_torque_gained || 0 }}</div>
          <div class="stat-label">Kopējais iegūtais griezes moments</div>
        </div>
        <div class="stat-card" v-if="stats.most_torquey_build">
          <div class="stat-icon notranslate">MAX</div>
          <div class="stat-value">{{ stats.most_torquey_build.final_torque }}</div>
          <div class="stat-label">Augstākais griezes moments</div>
        </div>
      </div>

      <section class="challenges-section">
        <div class="section-header">
          <h2>Dienas uzdevumi</h2>
          <span class="reset-timer">Atjaunojas pusnaktī</span>
        </div>
        <div v-if="challenges.length === 0" class="empty-state">
          <div class="empty-icon notranslate">!</div>
          <h3>Nav uzdevumu</h3>
          <p>Uzdevumi tiks ielādēti drīzumā.</p>
        </div>
        <div v-else class="challenges-grid">
          <div v-for="c in challenges" :key="c.id" :class="['challenge-card', { completed: c.completed }]">
            <div class="challenge-icon">{{ c.completed ? '✅' : '🎯' }}</div>
            <div class="challenge-info">
              <h3>{{ c.title }}</h3>
              <p>{{ c.description }}</p>
            </div>
            <div class="challenge-points">+{{ c.points }} pts</div>
          </div>
        </div>
      </section>

      <section class="achievements-section">
        <h2>Sasniegumi ({{ stats.achievements_unlocked }})</h2>
        <div v-if="user.achievements && user.achievements.length === 0" class="empty-state">
          <div class="empty-icon notranslate">!</div>
          <h3>Vēl nav gūti sasniegumi</h3>
          <p>Izveidojiet projektu, lai atbloķētu sasniegumus.</p>
        </div>
        <div v-else class="achievements-grid">
          <div v-for="achievement in user.achievements" :key="achievement.id" class="achievement-card unlocked">
            <div class="achievement-icon notranslate">{{ achievement.icon || '★' }}</div>
            <div class="achievement-info">
              <h3>{{ achievement.name }}</h3>
              <p>{{ achievement.description }}</p>
              <span class="achievement-points">+{{ achievement.points }} pts</span>
            </div>
          </div>
        </div>
      </section>

      <section class="builds-section">
        <div class="section-header">
          <h2>Jūsu projekti ({{ user.projects.length }})</h2>
          <button @click="$router.push('/builder')" class="btn-new-build">+ Jauns projekts</button>
        </div>

        <div v-if="user.projects.length === 0" class="empty-state">
          <div class="empty-icon notranslate">ADD</div>
          <h3>Vēl nav projektu</h3>
          <p>Sāc būvēt savu sapņu mašīnu</p>
          <button @click="$router.push('/builder')" class="btn-primary">Izveidot pirmo projektu</button>
        </div>

        <div v-else class="builds-grid">
          <div v-for="project in user.projects" :key="project.id" class="build-card">
            <div class="build-header">
              <h3 class="notranslate">{{ project.project_name }}</h3>
              <div class="build-actions">
                <button
                  @click.stop="toggleVisibility(project)"
                  class="btn-visibility"
                  :class="project.is_public ? 'btn-public' : 'btn-private'"
                  :title="project.is_public ? 'Publisks — klikšķini, lai paslēptu' : 'Privāts — klikšķini, lai publicētu'"
                >
                  {{ project.is_public ? 'Publisks' : 'Privāts' }}
                </button>
                <button @click.stop="openEditModal(project)" class="btn-edit-project" title="Rediģēt">✏️</button>
                <button @click.stop="askDeleteBuild(project.id)" class="btn-delete" title="Dzēst">x</button>
              </div>
            </div>

            <div class="build-image-wrapper" v-if="project.car.image_url || project.car.imageurl">
              <img
                :src="getImageUrl(project.car)"
                :alt="project.car.model.name"
                class="build-car-image"
                @error="handleImageError"
              />
            </div>

            <div class="build-car-info">
              <strong class="notranslate">{{ project.car.model.make.name }} {{ project.car.model.name }}</strong>
              <p class="notranslate">{{ project.car.trim }} ({{ project.car.year }})</p>
              <p>Dzinējs: <span class="notranslate">{{ project.engine.code }}</span></p>
            </div>

            <div class="build-stats">
              <div class="stat">
                <span class="stat-label">Jauda (HP):</span>
                <span class="stat-value">
                  {{ project.base_hp }} → {{ project.final_hp }}
                  <span class="gain">(+{{ project.total_hp_gain }})</span>
                </span>
              </div>
              <div class="stat">
                <span class="stat-label">Griezes moments (Nm):</span>
                <span class="stat-value">
                  {{ project.base_torque || '-' }} → {{ project.final_torque || '-' }}
                  <span class="gain torque-gain">(+{{ project.total_torque_gain || 0 }})</span>
                </span>
              </div>
              <div class="stat">
                <span class="stat-label">Detaļu skaits:</span>
                <span class="stat-value">{{ project.parts ? project.parts.length : 0 }}</span>
              </div>
            </div>

            <div class="build-footer">
              <span class="build-date">{{ formatDate(project.created_at) }}</span>
              <button
                v-if="project.is_public"
                @click.stop="$router.push(`/projects/${project.id}`)"
                class="btn-view-project"
              >
                👁 Skatīt
              </button>
            </div>
          </div>
        </div>
      </section>
    </div>

    <div v-else class="error-state">
      <h2>Nespēj ielādēt paneli</h2>
      <button @click="loadDashboard" class="btn-retry">Mēģināt vēlreiz</button>
    </div>

    <AvatarCropper
      :show="showCropper"
      :image-src="selectedImage"
      @close="closeCropper"
      @upload="handleAvatarUpload"
    />

    <div v-if="showEditModal" class="modal-overlay" @click.self="showEditModal = false">
      <div class="modal">
        <div class="modal-header">
          <h2>Rediģēt projektu</h2>
          <button class="modal-close" @click="showEditModal = false">✕</button>
        </div>
        <form @submit.prevent="submitEditProject" class="editor-form">
          <div class="form-group">
            <label>Projekta nosaukums</label>
            <input v-model="editForm.project_name" placeholder="Projekta nosaukums" required />
          </div>
          <div class="modal-actions">
            <button type="submit" class="btn-save">Saglabāt</button>
            <button type="button" class="btn-cancel-modal" @click="showEditModal = false">Atcelt</button>
          </div>
        </form>
      </div>
    </div>

    <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
      <div class="modal delete-modal">
        <div class="delete-icon">🗑️</div>
        <h3>Dzēst šo projektu?</h3>
        <p>Šī darbība ir neatgriezeniska.</p>
        <div class="modal-actions">
          <button @click="confirmDeleteBuild" class="btn-del-confirm">Dzēst</button>
          <button @click="showDeleteModal = false" class="btn-cancel-modal">Atcelt</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import AvatarCropper from '../components/AvatarCropper.vue'
import { toast } from 'vue3-toastify'

export default {
  components: { AvatarCropper },

  data() {
    return {
      loading: true,
      uploading: false,
      showCropper: false,
      selectedImage: '',
      user: null,
      challenges: [],
      midnightTimer: null,
      showEditModal: false,
      showDeleteModal: false,
      editForm: { id: null, project_name: '' },
      deleteTargetId: null,
      stats: {
        total_builds: 0,
        achievements_unlocked: 0,
        total_hp_gained: 0,
        total_torque_gained: 0,
        level: 1,
        rank: 'Beginner',
        total_points: 0,
        level_progress: 0,
        points_to_next_level: 100,
        points_for_next_level: 100,
        level_color: '#10b981',
        most_powerful_build: null,
        most_torquey_build: null,
      },
    }
  },

  computed: {
    isAdmin() {
      const stored = JSON.parse(localStorage.getItem('user') || '{}')
      return stored.role_id === 2
    },
    isEditor() {
      const stored = JSON.parse(localStorage.getItem('user') || '{}')
      return stored.role_id === 3
    },
  },

  async mounted() {
    await this.loadDashboard()
    await this.loadChallenges()
    this.startMidnightWatcher()
  },

  beforeUnmount() {
    clearTimeout(this.midnightTimer)
  },

  methods: {
    startMidnightWatcher() {
      const now = new Date()
      const midnight = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1)
      const msUntilMidnight = midnight - now
      this.midnightTimer = setTimeout(async () => {
        await this.loadChallenges()
        this.startMidnightWatcher()
      }, msUntilMidnight)
    },

    async loadDashboard() {
      this.loading = true
      try {
        const res = await axios.get('/api/dashboard')
        if (res.data.success) {
          this.user = res.data.user
          this.stats = { ...this.stats, ...res.data.stats }
          const stored = JSON.parse(localStorage.getItem('user') || '{}')
          stored.role_id = res.data.user.role_id
          localStorage.setItem('user', JSON.stringify(stored))
        }
      } catch (error) {
        console.error('Dashboard load error:', error)
        if (error.response?.status === 401) {
          localStorage.removeItem('user')
          this.$router.push('/login')
        } else {
          toast.error('Neizdevās ielādēt paneli')
        }
      } finally {
        this.loading = false
      }
    },

    async loadChallenges() {
      try {
        const res = await axios.get('/api/challenges?t=' + Date.now())
        if (res.data.success) this.challenges = res.data.challenges
      } catch (err) {
        console.error('Failed to load challenges', err)
      }
    },

    async toggleVisibility(project) {
      try {
        const res = await axios.patch(`/api/projects/${project.id}/visibility`)
        if (res.data.success) {
          project.is_public = res.data.is_public
          toast.success(res.data.message)
        }
      } catch (error) {
        toast.error('Neizdevās mainīt redzamību')
      }
    },

    openEditModal(project) {
      const carId = project.car?.id || project.car_id
      const engineId = project.engine?.id || project.engine_id
      this.$router.push(`/parts/${carId}/${engineId}?edit=${project.id}`)
    },

    async submitEditProject() {
      try {
        await axios.put(`/api/projects/${this.editForm.id}`, {
          project_name: this.editForm.project_name,
        })
        toast.success('Projekts atjaunināts')
        this.showEditModal = false
        const project = this.user.projects.find(p => p.id === this.editForm.id)
        if (project) project.project_name = this.editForm.project_name
      } catch (error) {
        toast.error('Neizdevās saglabāt: ' + (error.response?.data?.message || error.message))
      }
    },

    askDeleteBuild(id) {
      this.deleteTargetId = id
      this.showDeleteModal = true
    },

    async confirmDeleteBuild() {
      this.showDeleteModal = false
      try {
        await axios.delete(`/api/projects/${this.deleteTargetId}`)
        toast.success('Projekts dzēsts')
        await this.loadDashboard()
      } catch (error) {
        toast.error('Neizdevās dzēst projektu')
      }
    },

    getImageUrl(car) {
      const url = car.image_url || car.imageurl
      if (!url) return 'https://via.placeholder.com/300x200?text=No+Image'
      return url
    },

    handleImageError(event) {
      event.target.src = 'https://via.placeholder.com/300x200?text=Image+Not+Found'
    },

    triggerFileInput() { this.$refs.fileInput.click() },

    handleImageSelect(event) {
      const file = event.target.files[0]
      if (!file) return
      if (file.size > 5 * 1024 * 1024) { toast.error('Attēla izmērs nedrīkst pārsniegt 5MB'); return }
      if (!file.type.startsWith('image/')) { toast.error('Lūdzu izvēlieties attēla failu'); return }
      const reader = new FileReader()
      reader.onload = e => { this.selectedImage = e.target.result; this.showCropper = true }
      reader.readAsDataURL(file)
      this.$refs.fileInput.value = ''
    },

    closeCropper() { this.showCropper = false; this.selectedImage = '' },

    async handleAvatarUpload(formData) {
      this.uploading = true
      this.showCropper = false
      try {
        const res = await axios.post('/api/avatar/upload', formData, { headers: { 'Content-Type': 'multipart/form-data' } })
        if (res.data.success) {
          this.user.avatar_url = res.data.avatar_url + '?t=' + Date.now()
          const user = JSON.parse(localStorage.getItem('user') || '{}')
          user.avatar = res.data.avatar_url
          localStorage.setItem('user', JSON.stringify(user))
          toast.success('Profila attēls atjaunināts')
        }
      } catch (error) {
        toast.error('Neizdevās augšupielādēt attēlu')
      } finally {
        this.uploading = false
        this.selectedImage = ''
      }
    },

    async deleteAvatar() {
      if (!confirm('Dzēst profila attēlu?')) return
      try {
        const res = await axios.delete('/api/avatar/delete')
        if (res.data.success) {
          this.user.avatar_url = null
          const user = JSON.parse(localStorage.getItem('user') || '{}')
          user.avatar = null
          localStorage.setItem('user', JSON.stringify(user))
          toast.success('Profila attēls noņemts')
        }
      } catch (error) {
        toast.error('Neizdevās dzēst attēlu')
      }
    },

    formatDate(date) {
      return new Date(date).toLocaleDateString('lv-LV', { year: 'numeric', month: 'short', day: 'numeric' })
    },

    goBack() { this.$router.push('/') },
  },
}
</script>

<style scoped>
.dashboard-container {
  min-height: 100vh;
  background: #f8fafc;
  padding: 30px;
}

html[data-color-scheme='dark'] .dashboard-container {
  background: #0a0a0a;
  color: #f5f5f5;
}

.loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 80vh;
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

.error-state { text-align: center; padding: 60px 20px; }

.btn-retry {
  padding: 12px 24px;
  background: #10b981;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  margin-top: 20px;
}

html[data-color-scheme='dark'] .btn-retry { background: #ffd700; color: #000; }

.dashboard-content { max-width: 1400px; margin: 0 auto; }

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: white;
  padding: 30px;
  border-radius: 16px;
  margin-bottom: 30px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  position: relative;
}

html[data-color-scheme='dark'] .dashboard-header { background: #1a1a1a; }

.back-btn {
  position: absolute;
  top: 20px; left: 20px;
  padding: 8px 14px;
  background: transparent;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  color: #1e293b;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.85rem;
  transition: all 0.2s;
  z-index: 10;
}

html[data-color-scheme='dark'] .back-btn { border-color: #404040; color: #f5f5f5; }
.back-btn:hover { background: #f1f5f9; border-color: #10b981; color: #10b981; }
html[data-color-scheme='dark'] .back-btn:hover { background: #2d2d2d; border-color: #ffd700; color: #ffd700; }

.user-profile { display: flex; gap: 20px; align-items: center; width: 100%; }

.avatar-wrapper { display: flex; flex-direction: column; align-items: center; gap: 8px; }

.avatar-preview { position: relative; cursor: pointer; transition: transform 0.3s; }
.avatar-preview:hover { transform: scale(1.05); }
.avatar-preview:hover .avatar-overlay { opacity: 1; }

.avatar {
  width: 70px; height: 70px; border-radius: 50%;
  background: linear-gradient(135deg, #10b981, #059669);
  display: flex; align-items: center; justify-content: center;
  font-size: 2rem; font-weight: bold; color: white;
}

html[data-color-scheme='dark'] .avatar { background: linear-gradient(135deg, #ffd700, #ffed4e); color: #000; }

.avatar-img { width: 70px; height: 70px; border-radius: 50%; object-fit: cover; }

.avatar-overlay {
  position: absolute; top: 0; left: 0;
  width: 70px; height: 70px; border-radius: 50%;
  background: rgba(0,0,0,0.7);
  display: flex; align-items: center; justify-content: center;
  opacity: 0; transition: opacity 0.3s;
}

.camera-icon { font-size: 1rem; font-weight: bold; color: white; }
.upload-status { font-size: 0.8rem; color: #10b981; font-weight: 600; }
html[data-color-scheme='dark'] .upload-status { color: #ffd700; }

.btn-remove-avatar {
  padding: 4px 12px; background: #fee2e2; color: #dc2626;
  border: none; border-radius: 6px; font-size: 0.75rem;
  font-weight: 600; cursor: pointer; transition: all 0.3s;
}
.btn-remove-avatar:hover { background: #dc2626; color: white; }

.user-info h1 { margin: 0; font-size: 1.8rem; color: #1e293b; }
html[data-color-scheme='dark'] .user-info h1 { color: #f5f5f5; }
.user-email { color: #64748b; margin: 4px 0 0 0; }
.user-actions { display: flex; gap: 10px; flex-wrap: wrap; }

.btn-admin {
  padding: 12px 24px; background: #7c3aed; color: white;
  border: none; border-radius: 8px; font-weight: 600;
  cursor: pointer; transition: all 0.3s; margin-top: 12px;
}
.btn-admin:hover { background: #6d28d9; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(124,58,237,0.3); }

.btn-editor {
  padding: 12px 24px; background: #0ea5e9; color: white;
  border: none; border-radius: 8px; font-weight: 600;
  cursor: pointer; transition: all 0.3s; margin-top: 12px;
}
.btn-editor:hover { background: #0284c7; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(14,165,233,0.3); }

.btn-explore {
  padding: 12px 24px;
  background: #0369a1;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}
.btn-explore:hover {
  background: #0284c7;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(3,105,161,0.3);
}
html[data-color-scheme='dark'] .btn-explore { background: #075985; color: #e0f2fe; }
html[data-color-scheme='dark'] .btn-explore:hover { background: #0ea5e9; color: white; }

.level-badge {
  display: flex; flex-direction: column; align-items: center; gap: 8px;
  padding: 20px; background: #f0fdf4; border-radius: 12px;
  border: 2px solid #10b981; min-width: 200px;
}

html[data-color-scheme='dark'] .level-badge { background: #1a1a1a; border-color: #2d2d2d; }

.level-icon { font-size: 1.8rem; font-weight: bold; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2)); }
.level-info { display: flex; flex-direction: column; align-items: center; gap: 4px; }
.level-text { font-size: 1.5rem; font-weight: 700; color: #1e293b; }
html[data-color-scheme='dark'] .level-text { color: #f5f5f5; }
.rank-text { font-size: 0.9rem; font-weight: 600; color: #10b981; text-transform: uppercase; letter-spacing: 1px; }
html[data-color-scheme='dark'] .rank-text { color: #ffd700; }

.xp-bar { width: 100%; height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden; }
html[data-color-scheme='dark'] .xp-bar { background: #2d2d2d; }
.xp-fill { height: 100%; transition: width 0.5s ease, background 0.3s ease; border-radius: 4px; }
.xp-text { font-size: 0.85rem; font-weight: 600; color: #64748b; }
.xp-remaining { font-size: 0.75rem; color: #94a3b8; }

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px; margin-bottom: 40px;
}

.stat-card {
  background: white; padding: 30px; border-radius: 12px;
  text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
html[data-color-scheme='dark'] .stat-card { background: #1a1a1a; }
.stat-icon { font-size: 1.5rem; font-weight: bold; color: #94a3b8; margin-bottom: 10px; }
.stat-value { font-size: 2.5rem; font-weight: 700; color: #10b981; margin: 10px 0; }
html[data-color-scheme='dark'] .stat-value { color: #ffd700; }
.stat-label { color: #64748b; font-size: 0.95rem; }

.challenges-section { margin-bottom: 40px; }
.challenges-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; }

.challenge-card {
  background: white; border: 2px solid #e2e8f0; border-radius: 12px;
  padding: 20px; display: flex; align-items: center; gap: 16px; transition: all 0.3s;
}
html[data-color-scheme='dark'] .challenge-card { background: #1a1a1a; border-color: #2d2d2d; }
.challenge-card.completed { border-color: #10b981; background: #f0fdf4; opacity: 0.8; }
html[data-color-scheme='dark'] .challenge-card.completed { border-color: #ffd700; background: #1a1a1a; }
.challenge-icon { font-size: 1.8rem; }
.challenge-info h3 { margin: 0 0 4px 0; font-size: 1rem; color: #1e293b; }
html[data-color-scheme='dark'] .challenge-info h3 { color: #f5f5f5; }
.challenge-info p { margin: 0; font-size: 0.85rem; color: #64748b; }
.challenge-points { margin-left: auto; font-weight: 700; color: #10b981; white-space: nowrap; }
html[data-color-scheme='dark'] .challenge-points { color: #ffd700; }
.reset-timer { font-size: 0.85rem; color: #94a3b8; }

.achievements-section, .builds-section { margin-bottom: 40px; }

.section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.section-header h2 { font-size: 1.8rem; color: #1e293b; }
html[data-color-scheme='dark'] .section-header h2 { color: #f5f5f5; }
.achievements-section h2 { font-size: 1.8rem; color: #1e293b; margin-bottom: 24px; }
html[data-color-scheme='dark'] .achievements-section h2 { color: #f5f5f5; }

.achievements-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }

.achievement-card {
  background: white; border: 2px solid #10b981; border-radius: 12px;
  padding: 20px; display: flex; gap: 16px; box-shadow: 0 2px 10px rgba(16,185,129,0.1);
}
html[data-color-scheme='dark'] .achievement-card { background: #1a1a1a; border-color: #ffd700; }
.achievement-icon { font-size: 2rem; font-weight: bold; color: #10b981; }
html[data-color-scheme='dark'] .achievement-icon { color: #ffd700; }
.achievement-info h3 { margin: 0 0 8px 0; font-size: 1.1rem; color: #1e293b; }
html[data-color-scheme='dark'] .achievement-info h3 { color: #f5f5f5; }
.achievement-info p { margin: 0 0 8px 0; color: #64748b; font-size: 0.9rem; }
.achievement-points { color: #10b981; font-weight: 600; font-size: 0.9rem; }
html[data-color-scheme='dark'] .achievement-points { color: #ffd700; }

.builds-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 24px; }

.build-card {
  background: white; border: 2px solid #e2e8f0; border-radius: 12px;
  padding: 24px; transition: all 0.3s; display: flex; flex-direction: column;
}
html[data-color-scheme='dark'] .build-card { background: #1a1a1a; border-color: #2d2d2d; }
.build-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(16,185,129,0.15); border-color: #10b981; }

.build-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; }
.build-header h3 { margin: 0; font-size: 1.2rem; color: #1e293b; }
html[data-color-scheme='dark'] .build-header h3 { color: #f5f5f5; }

.build-actions { display: flex; gap: 6px; align-items: center; }

.btn-edit-project {
  background: #e0f2fe; color: #0284c7;
  border: none; width: 32px; height: 32px;
  border-radius: 6px; font-size: 0.9rem;
  cursor: pointer; transition: all 0.2s;
  display: flex; align-items: center; justify-content: center;
}
.btn-edit-project:hover { background: #0ea5e9; color: white; }
html[data-color-scheme='dark'] .btn-edit-project { background: #1e3a4a; color: #38bdf8; }
html[data-color-scheme='dark'] .btn-edit-project:hover { background: #0ea5e9; color: white; }

.btn-delete {
  background: #fee2e2; color: #dc2626;
  border: none; width: 32px; height: 32px;
  border-radius: 6px; font-size: 1.2rem;
  font-weight: bold; cursor: pointer; transition: all 0.3s;
}
.btn-delete:hover { background: #dc2626; color: white; }

.build-image-wrapper {
  width: 100%; height: 180px; border-radius: 8px;
  overflow: hidden; margin-bottom: 16px; background: #f8fafc;
}
html[data-color-scheme='dark'] .build-image-wrapper { background: #2d2d2d; }
.build-car-image { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease; }
.build-card:hover .build-car-image { transform: scale(1.05); }

.build-car-info { margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid #e2e8f0; }
html[data-color-scheme='dark'] .build-car-info { border-bottom-color: #2d2d2d; }
.build-car-info strong { font-size: 1.05rem; color: #1e293b; }
html[data-color-scheme='dark'] .build-car-info strong { color: #f5f5f5; }
.build-car-info p { margin: 4px 0; color: #64748b; font-size: 0.9rem; }

.build-stats { display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px; flex-grow: 1; }
.build-stats .stat { display: flex; justify-content: space-between; }
.build-stats .stat-label { color: #64748b; font-weight: 600; }
.build-stats .stat-value { color: #1e293b; font-weight: 600; }
html[data-color-scheme='dark'] .build-stats .stat-value { color: #f5f5f5; }
.build-stats .gain { color: #10b981; font-weight: 700; }
html[data-color-scheme='dark'] .build-stats .gain { color: #ffd700; }
.build-stats .torque-gain { color: #3b82f6; }

.build-footer {
  padding-top: 12px;
  border-top: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
html[data-color-scheme='dark'] .build-footer { border-top-color: #2d2d2d; }
.build-date { color: #94a3b8; font-size: 0.85rem; }

.btn-view-project {
  padding: 4px 12px;
  background: #f0fdf4;
  color: #065f46;
  border: 1px solid #6ee7b7;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-view-project:hover { background: #10b981; color: white; border-color: #10b981; }
html[data-color-scheme='dark'] .btn-view-project { background: #064e3b; color: #6ee7b7; border-color: #065f46; }
html[data-color-scheme='dark'] .btn-view-project:hover { background: #ffd700; color: #000; border-color: #ffd700; }

.btn-new-build, .btn-primary {
  padding: 12px 24px; background: #10b981; color: white;
  border: none; border-radius: 8px; font-weight: 600;
  cursor: pointer; transition: all 0.3s;
}
html[data-color-scheme='dark'] .btn-new-build,
html[data-color-scheme='dark'] .btn-primary { background: #ffd700; color: #000; }
.btn-new-build:hover, .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(16,185,129,0.3); }

.empty-state {
  text-align: center; padding: 60px 20px; background: white;
  border-radius: 12px; border: 2px dashed #e2e8f0;
}
html[data-color-scheme='dark'] .empty-state { background: #1a1a1a; border-color: #2d2d2d; }
.empty-icon { font-size: 2rem; font-weight: bold; color: #94a3b8; margin-bottom: 20px; }
.empty-state h3 { font-size: 1.4rem; color: #1e293b; margin-bottom: 10px; }
html[data-color-scheme='dark'] .empty-state h3 { color: #f5f5f5; }
.empty-state p { color: #64748b; margin-bottom: 20px; }

.btn-visibility {
  padding: 4px 10px; border: none; border-radius: 6px;
  font-size: 0.75rem; font-weight: 600; cursor: pointer;
  transition: all 0.2s; white-space: nowrap;
}
.btn-public { background: #d1fae5; color: #065f46; }
.btn-public:hover { background: #6ee7b7; }
.btn-private { background: #fee2e2; color: #991b1b; }
.btn-private:hover { background: #fca5a5; }
html[data-color-scheme='dark'] .btn-public { background: #064e3b; color: #6ee7b7; }
html[data-color-scheme='dark'] .btn-private { background: #450a0a; color: #fca5a5; }

.modal-overlay {
  position: fixed; inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex; align-items: center; justify-content: center;
  z-index: 1000; backdrop-filter: blur(4px);
  animation: fadeIn 0.15s ease;
}

@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

.modal {
  background: white; border-radius: 16px; padding: 32px;
  width: 100%; max-width: 460px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.2);
  animation: slideUp 0.2s ease;
}

html[data-color-scheme='dark'] .modal { background: #1a1a1a; }

@keyframes slideUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.modal-header {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 24px; padding-bottom: 16px;
  border-bottom: 1px solid #e2e8f0;
}
html[data-color-scheme='dark'] .modal-header { border-bottom-color: #2d2d2d; }
.modal-header h2 { margin: 0; font-size: 1.2rem; color: #1e293b; }
html[data-color-scheme='dark'] .modal-header h2 { color: #f5f5f5; }

.modal-close {
  width: 32px; height: 32px; border: none; border-radius: 8px;
  background: #f1f5f9; color: #64748b; cursor: pointer; font-size: 1rem;
  display: flex; align-items: center; justify-content: center; transition: all 0.2s;
}
.modal-close:hover { background: #fee2e2; color: #dc2626; }
html[data-color-scheme='dark'] .modal-close { background: #2d2d2d; color: #94a3b8; }
html[data-color-scheme='dark'] .modal-close:hover { background: #450a0a; color: #fca5a5; }

.editor-form { display: flex; flex-direction: column; gap: 14px; }
.form-group { display: flex; flex-direction: column; gap: 6px; }

.form-group label {
  font-size: 0.82rem; font-weight: 600; color: #64748b;
  text-transform: uppercase; letter-spacing: 0.5px;
}

.form-group input {
  padding: 10px 14px; border: 2px solid #e2e8f0; border-radius: 8px;
  font-size: 0.95rem; color: #1e293b; background: #f8fafc;
  transition: all 0.2s; outline: none;
}
.form-group input:focus { border-color: #10b981; background: white; }
html[data-color-scheme='dark'] .form-group input { background: #0a0a0a; border-color: #2d2d2d; color: #f5f5f5; }
html[data-color-scheme='dark'] .form-group input:focus { background: #1a1a1a; border-color: #10b981; }

.modal-actions { display: flex; gap: 10px; margin-top: 8px; }

.btn-save {
  flex: 1; padding: 11px 20px; background: #10b981; color: white;
  border: none; border-radius: 8px; font-weight: 600;
  cursor: pointer; transition: all 0.2s; font-size: 0.95rem;
}
.btn-save:hover { background: #059669; transform: translateY(-1px); }
html[data-color-scheme='dark'] .btn-save { background: #ffd700; color: #000; }
html[data-color-scheme='dark'] .btn-save:hover { background: #ffed4e; }

.btn-cancel-modal {
  padding: 11px 18px; background: #f1f5f9; color: #64748b;
  border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s;
}
html[data-color-scheme='dark'] .btn-cancel-modal { background: #2d2d2d; color: #94a3b8; }
.btn-cancel-modal:hover { background: #e2e8f0; color: #1e293b; }
html[data-color-scheme='dark'] .btn-cancel-modal:hover { background: #404040; color: #f5f5f5; }

.delete-modal {
  max-width: 360px; text-align: center;
  align-items: center; display: flex; flex-direction: column;
}
.delete-icon { font-size: 3rem; margin-bottom: 8px; }
.delete-modal h3 { font-size: 1.3rem; color: #1e293b; margin-bottom: 6px; }
html[data-color-scheme='dark'] .delete-modal h3 { color: #f5f5f5; }
.delete-modal p { color: #94a3b8; font-size: 0.9rem; margin-bottom: 4px; }
.delete-modal .modal-actions { justify-content: center; }

.btn-del-confirm {
  padding: 10px 24px; background: #dc2626; color: white;
  border: none; border-radius: 8px; font-weight: 600;
  cursor: pointer; transition: all 0.2s;
}
.btn-del-confirm:hover { background: #b91c1c; transform: translateY(-1px); }
</style>