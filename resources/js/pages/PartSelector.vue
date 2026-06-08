<template>
  <div class="parts-selector-container">

    <div v-if="loading" class="loading-overlay">
      <div class="loading-spinner"></div>
      <p>{{ editMode ? 'Ielādē projektu...' : 'Loading detaļas...' }}</p>
    </div>

    <header class="parts-header">
      <button @click="goBack" class="back-btn">
        {{ editMode ? '← Atpakaļ uz Paneli' : 'Atpakaļ uz Projektētāju' }}
      </button>
      <div class="header-info">
        <h1>{{ carInfo.make }} {{ carInfo.model }}</h1>
        <p>{{ carInfo.trim }} • {{ carInfo.year }} • {{ engineInfo.code }}</p>
      </div>
      <div v-if="editMode" class="edit-badge">✏️ Rediģēšanas režīms</div>
    </header>

    <div class="parts-content">
      <!-- Left: Categories & Parts -->
      <div class="parts-list">
        <div v-if="Object.keys(categories).length === 0 && !loading" class="empty-state">
          <div class="empty-icon"></div>
          <h3>Nav detaļu pieejamu šim automobiliem/motoram</h3>
          <p>Mēģiniet izvēlēties citu motoru vai pārbaudiet vēlāk</p>
        </div>

        <div v-for="(parts, category) in categories" :key="category" class="category-section">
          <h2 class="category-title">{{ category }}</h2>
          <div class="parts-grid">
            <div
              v-for="part in parts"
              :key="part.id"
              :class="['part-card', { selected: isSelected(part.id) }]"
              @click="togglePart(part)"
            >
              <div class="part-header">
                <h3>{{ part.name }}</h3>
                <span class="part-brand">{{ part.brand }}</span>
              </div>

              <div class="part-gains">
                <div class="gain-item" v-if="part.hp_gain > 0">
                  <span class="gain-icon">PWR</span>
                  <span class="gain-value">+{{ part.hp_gain }} HP</span>
                </div>
                <div class="gain-item" v-if="part.torque_nm_gain > 0">
                  <span class="gain-icon">TRQ</span>
                  <span class="gain-value">+{{ part.torque_nm_gain }} Nm</span>
                </div>
                <div v-if="part.hp_gain === 0 && part.torque_nm_gain === 0" class="no-gain">
                  Atbalstošie modifikatori
                </div>
              </div>

              <p class="part-notes">{{ part.notes }}</p>

              <button
                v-if="part.youtube_url"
                @click.stop="openTutorial(part)"
                class="tutorial-btn"
              >
                ▶ Skatīties pamācību
              </button>

              <button
                @click.stop="togglePart(part)"
                :class="['add-btn', { added: isSelected(part.id) }]"
              >
                {{ isSelected(part.id) ? 'Pievienota' : '+ Pievienot' }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Right: Build Summary -->
      <aside class="build-summary">
        <h2>{{ editMode ? 'Rediģē projektu' : 'Jūsu projekts' }}</h2>

        <div class="summary-car-info">
          <p><strong>{{ carInfo.make }} {{ carInfo.model }}</strong></p>
          <p>{{ carInfo.trim }} ({{ carInfo.year }})</p>
          <p>Engine: {{ engineInfo.code }}</p>
        </div>

        <div class="summary-stats">
          <div class="stat-box">
            <span class="stat-label">Bāzes jauda</span>
            <span class="stat-value">{{ baseHP }} HP</span>
          </div>
          <div class="stat-box highlight">
            <span class="stat-label">Kopējā jauda</span>
            <span class="stat-value">
              {{ baseHP + totalHPGain }}
              <span style="font-size: 0.8rem; opacity: 0.8">(+{{ totalHPGain }})</span>
            </span>
          </div>
          <div class="stat-box" style="margin-top: 8px;">
            <span class="stat-label">Bāzes griezes moments</span>
            <span class="stat-value" style="color: #3b82f6;">{{ baseTorque }} Nm</span>
          </div>
          <div class="stat-box highlight" style="border-color: #3b82f6; background: rgba(59, 130, 246, 0.1);">
            <span class="stat-label">Kopējais griezes moments</span>
            <span class="stat-value" style="color: #3b82f6;">
              {{ baseTorque + totalTorqueGain }}
              <span style="font-size: 0.8rem; opacity: 0.8">(+{{ totalTorqueGain }})</span>
            </span>
          </div>
        </div>

        <div class="selected-parts-list">
          <h3>Izvēlētās detaļas ({{ selectedParts.length }})</h3>
          <div v-if="selectedParts.length === 0" class="no-parts">
            Nav izvēlētu detaļu
          </div>
          <div v-for="part in selectedParts" :key="part.id" class="selected-part-item">
            <div class="part-item-info">
              <strong>{{ part.name }}</strong>
              <div style="display: flex; gap: 10px;">
                <span v-if="part.hp_gain > 0" class="part-item-gain">+{{ part.hp_gain }} HP</span>
                <span v-if="part.torque_nm_gain > 0" class="part-item-gain" style="color: #3b82f6;">+{{ part.torque_nm_gain }} Nm</span>
              </div>
            </div>
            <button @click="removePart(part.id)" class="remove-btn">x</button>
          </div>
        </div>

        <div class="summary-actions">
          <button
            class="btn-primary"
            @click="openSaveModal"
            :disabled="selectedParts.length === 0"
          >
            {{ editMode ? ' Atjaunināt projektu' : 'Saglabāt Projektu' }}
          </button>
          <button
            class="btn-secondary"
            @click="clearAll"
            :disabled="selectedParts.length === 0"
          >
            Noņemt visas detaļas
          </button>
        </div>
      </aside>
    </div>

    <!-- Save / Update Modal -->
    <div v-if="showSaveModal" class="modal-overlay" @click="closeSaveModal">
      <div class="modal-content" @click.stop>
        <h3>{{ editMode ? 'Atjaunināt projektu' : 'Nosauciet savu projektu' }}</h3>
        <p>{{ editMode ? 'Rediģē projekta nosaukumu un detaļas.' : 'Dod savam projektam pielāgotu vārdu pirms saglabāšanas jūsu garažā.' }}</p>

        <input
          type="text"
          v-model="projectNameInput"
          class="modal-input"
          placeholder="e.g. My Track Beast"
          @keyup.enter="confirmSaveBuild"
          ref="projectNameRef"
        />

        <div class="modal-actions">
          <button class="btn-cancel" @click="closeSaveModal" :disabled="isSaving">Atcelt</button>
          <button
            class="btn-primary modal-save-btn"
            @click="confirmSaveBuild"
            :disabled="!projectNameInput.trim() || isSaving"
          >
            {{ isSaving ? 'Saglabā...' : (editMode ? 'Atjaunināt' : 'Saglabāt projektu') }}
          </button>
        </div>
      </div>
    </div>

    <!-- YouTube Tutorial Modal -->
    <div v-if="showTutorialModal" class="modal-overlay" @click="closeTutorial">
      <div class="tutorial-modal-content" @click.stop>

        <button class="tutorial-close-btn" @click="closeTutorial">✕</button>

        <h3>{{ activeTutorialPart?.name }}</h3>
        <p class="tutorial-subtitle">{{ activeTutorialPart?.brand }}</p>

        <div class="ratio-16x9">
          <iframe
            :src="getEmbedUrl(activeTutorialPart?.youtube_url)"
            frameborder="0"
            allowfullscreen
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
          ></iframe>
        </div>

        <!-- Channel shoutout — simple & clean -->
        <div v-if="activeTutorialPart?.youtube_channel" class="channel-shoutout">
          <span class="channel-label">Tutorial provided by</span>
          <a
            :href="getChannelUrl(activeTutorialPart.youtube_channel)"
            target="_blank"
            rel="noopener"
            class="channel-link"
          >
            {{ getChannelDisplay(activeTutorialPart.youtube_channel) }} ↗
          </a>
        </div>

      </div>
    </div>

  </div>
</template>

<script>
import axios from 'axios'
import AchievementToast from '../components/AchievementToast.vue'
import { toast } from 'vue3-toastify'
import { h } from 'vue'

export default {
  props: {
    carId: { type: String, required: true },
    engineId: { type: String, required: true },
  },

  data() {
    return {
      loading: true,
      carInfo: {},
      engineInfo: {},
      categories: {},
      selectedParts: [],
      baseHP: 0,
      baseTorque: 0,

      editMode: false,
      editProjectId: null,

      showSaveModal: false,
      projectNameInput: '',
      isSaving: false,

      showTutorialModal: false,
      activeTutorialPart: null,
    }
  },

  computed: {
    totalHPGain() {
      return this.selectedParts.reduce((sum, p) => sum + (p.hp_gain || 0), 0)
    },
    totalTorqueGain() {
      return this.selectedParts.reduce((sum, p) => sum + (p.torque_nm_gain || 0), 0)
    },
  },

  async mounted() {
    const editId = this.$route?.query?.edit
    if (editId) {
      this.editMode = true
      this.editProjectId = editId
    }

    await this.loadParts()

    if (this.editMode) {
      await this.loadExistingProject()
    }
  },

  methods: {
    async loadParts() {
      this.loading = true
      try {
        const res = await axios.get(`/api/cars/${this.carId}/engines/${this.engineId}/parts`)
        if (res.data.success) {
          this.carInfo    = res.data.car
          this.engineInfo = res.data.engine
          this.categories = res.data.categories
          this.baseHP     = res.data.baseHP || 0
          this.baseTorque = res.data.baseTorque || 0
        }
      } catch (error) {
        console.error('Error loading parts:', error)
        toast.error('Neizdevās ielādēt detaļas')
        this.goBack()
      } finally {
        this.loading = false
      }
    },

    async loadExistingProject() {
      try {
        const res = await axios.get(`/api/projects/${this.editProjectId}`)
        const project = res.data.project ?? res.data

        this.projectNameInput = project.project_name || ''

        const savedIds = (project.parts || []).map(p => p.power_mod_id ?? p.id)
        const allParts = Object.values(this.categories).flat()
        this.selectedParts = allParts.filter(p => savedIds.includes(p.id))

        toast.info(`Projekts ielādēts — ${this.selectedParts.length} detaļas`, { autoClose: 3000 })
      } catch (error) {
        console.error('Error loading project for edit:', error)
        toast.error('Neizdevās ielādēt projektu rediģēšanai')
      }
    },

    togglePart(part) {
      if (this.isSelected(part.id)) {
        this.removePart(part.id)
      } else {
        this.selectedParts.push(part)
        toast.success(`Pievienots: ${part.name}`, { autoClose: 2000 })
      }
    },

    isSelected(partId) {
      return this.selectedParts.some(p => p.id === partId)
    },

    removePart(partId) {
      this.selectedParts = this.selectedParts.filter(p => p.id !== partId)
      toast.info('Detaļa noņemta', { autoClose: 2000 })
    },

    clearAll() {
      if (confirm('Noņemt visas izvēlētās detaļas?')) {
        this.selectedParts = []
        toast.info('Visas detaļas notīrītas')
      }
    },

    openSaveModal() {
      const user = JSON.parse(localStorage.getItem('user') || 'null')
      if (!user) {
        if (confirm('Jums jāpiesakās, lai saglabātu. Doties uz pieteikšanos?')) {
          this.$router.push({ name: 'Login', query: { redirect: this.$route.fullPath } })
        }
        return
      }

      if (!this.editMode) {
        this.projectNameInput = `${this.carInfo.make} ${this.carInfo.model} ${this.carInfo.year}`
      }

      this.showSaveModal = true
      this.$nextTick(() => {
        if (this.$refs.projectNameRef) this.$refs.projectNameRef.focus()
      })
    },

    closeSaveModal() {
      this.showSaveModal = false
    },

    openTutorial(part) {
      this.activeTutorialPart = part
      this.showTutorialModal = true
    },

    closeTutorial() {
      this.showTutorialModal = false
      this.activeTutorialPart = null
    },

    getEmbedUrl(url) {
      if (!url) return ''
      const match = url.match(/(?:v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/)
      return match ? `https://www.youtube.com/embed/${match[1]}?autoplay=1` : ''
    },

    // NEW METHODS FOR CLEAN YOUTUBE URL HANDLING
    getChannelDisplay(channel) {
      if (!channel) return ''
      if (!channel.startsWith('http')) return channel
      
      const handleMatch = channel.match(/@([\w.-]+)/)
      if (handleMatch) return '@' + handleMatch[1]
      
      const idMatch = channel.match(/channel\/([\w-]+)/)
      if (idMatch) return idMatch[1]
      
      return channel
    },

    getChannelUrl(channel) {
      if (!channel) return '#'
      if (channel.startsWith('http')) return channel
      return `https://www.youtube.com/@${channel}`
    },

    async confirmSaveBuild() {
      if (!this.projectNameInput.trim()) return

      this.isSaving = true
      const user = JSON.parse(localStorage.getItem('user') || '{}')

      const buildData = {
        car_id:        parseInt(this.carId),
        engine_id:     parseInt(this.engineId),
        project_name:  this.projectNameInput.trim(),
        description:   '',
        base_hp:       this.baseHP,
        base_torque:   this.baseTorque,
        parts: this.selectedParts.map(part => ({
          power_mod_id:    part.id,
          hp_gain:         part.hp_gain,
          torque_nm_gain:  part.torque_nm_gain,
        })),
      }

      try {
        const method = this.editMode ? 'put' : 'post'
        const url    = this.editMode
          ? `/api/projects/${this.editProjectId}`
          : '/api/projects'

        const res = await axios[method](url, buildData)

        if (res.data.success) {
          const updatedUser = {
            ...user,
            total_points: res.data.user?.total_points || user.total_points,
            level:        res.data.user?.level        || user.level,
          }
          localStorage.setItem('user', JSON.stringify(updatedUser))

          this.showSaveModal = false

          const msg = this.editMode
            ? `Projekts "${this.projectNameInput}" atjaunināts!`
            : `Projekts "${this.projectNameInput}" saglabāts!`
          toast.success(msg, { autoClose: 3000 })

          if (!this.editMode && res.data.newAchievements?.length > 0) {
            res.data.newAchievements.forEach((achievement, index) => {
              setTimeout(() => {
                toast(h(AchievementToast, { achievement }), {
                  autoClose: 5000,
                  closeButton: false,
                  hideProgressBar: true,
                  className: 'achievement-toast-container',
                })
              }, 1000 + index * 700)
            })
          }

          const delay = (!this.editMode && res.data.newAchievements?.length > 0) ? 3000 : 1500
          setTimeout(() => this.$router.push('/dashboard'), delay)
        }
      } catch (error) {
        console.error('Save error:', error)
        if (error.response?.status === 401) {
          toast.error('Sesija beigusies. Lūdzu piesakieties vēlreiz.')
          localStorage.removeItem('user')
          this.$router.push({ name: 'Login', query: { redirect: this.$route.fullPath } })
        } else if (error.response?.status === 422) {
          const msgs = Object.values(error.response.data.errors).flat().join('\n')
          toast.error(`Validācijas kļūda:\n${msgs}`)
        } else {
          toast.error(`Kļūda: ${error.response?.data?.message || error.message}`)
        }
      } finally {
        this.isSaving = false
      }
    },

    goBack() {
      if (this.editMode) {
        this.$router.push('/dashboard')
      } else {
        this.$router.push('/builder')
      }
    },
  },
}
</script>

<style scoped>
.parts-selector-container {
  min-height: 100vh;
  background: white;
  color: #1e293b;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

html[data-color-scheme='dark'] .parts-selector-container {
  background: #0a0a0a;
  color: #f5f5f5;
}

.loading-overlay {
  position: fixed; top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.5);
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  z-index: 9999;
}

.loading-spinner {
  width: 40px; height: 40px;
  border: 4px solid rgba(16,185,129,0.2);
  border-top-color: #10b981;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

.parts-header {
  display: flex;
  align-items: center;
  gap: 24px;
  padding: 20px 30px;
  background: white;
  border-bottom: 1px solid #e2e8f0;
  position: sticky; top: 0; z-index: 100;
}

html[data-color-scheme='dark'] .parts-header {
  background: #1a1a1a;
  border-bottom-color: #2d2d2d;
}

.edit-badge {
  margin-left: auto;
  padding: 6px 14px;
  background: #fef3c7;
  color: #92400e;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
  border: 1px solid #fde68a;
}

html[data-color-scheme='dark'] .edit-badge {
  background: #2d2200;
  color: #ffd700;
  border-color: #ffd700;
}

.back-btn {
  padding: 10px 20px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s;
}

html[data-color-scheme='dark'] .back-btn {
  background: #2d2d2d; border-color: #404040; color: #f5f5f5;
}

.back-btn:hover { background: #e2e8f0; transform: translateX(-4px); }

.header-info h1 { margin: 0; font-size: 1.8rem; font-weight: 700; }
.header-info p { margin: 4px 0 0 0; color: #64748b; font-size: 0.95rem; }

.achievement-toast-container {
  background: transparent !important;
  padding: 0 !important;
  box-shadow: none !important;
  border-radius: 12px !important;
}

.parts-content {
  display: grid;
  grid-template-columns: 1fr 420px;
  gap: 30px;
  padding: 30px;
  max-width: 1800px;
  margin: 0 auto;
}

@media (max-width: 1200px) {
  .parts-content { grid-template-columns: 1fr; }
  .build-summary { position: relative !important; top: auto !important; }
}

.category-section { margin-bottom: 50px; }

.category-title {
  font-size: 1.6rem; margin-bottom: 24px;
  color: #10b981; border-bottom: 3px solid #10b981; padding-bottom: 8px;
}

html[data-color-scheme='dark'] .category-title { color: #ffd700; border-bottom-color: #ffd700; }

.parts-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 24px;
}

.part-card {
  background: white; border: 2px solid #e2e8f0;
  border-radius: 12px; padding: 24px;
  cursor: pointer; transition: all 0.3s ease;
}

html[data-color-scheme='dark'] .part-card { background: #1a1a1a; border-color: #2d2d2d; }

.part-card:hover {
  border-color: #10b981; transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(16,185,129,0.15);
}

html[data-color-scheme='dark'] .part-card:hover {
  border-color: #ffd700;
  box-shadow: 0 8px 24px rgba(255,215,0,0.15);
}

.part-card.selected { border-color: #10b981; background: #f0fdf4; }
html[data-color-scheme='dark'] .part-card.selected { border-color: #ffd700; background: #1a1a0f; }

.part-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; }
.part-header h3 { margin: 0; font-size: 1.1rem; font-weight: 600; color: #1e293b; }
html[data-color-scheme='dark'] .part-header h3 { color: #f5f5f5; }

.part-brand {
  padding: 4px 10px; background: #f1f5f9;
  border-radius: 6px; font-size: 0.8rem; font-weight: 600; color: #64748b;
}
html[data-color-scheme='dark'] .part-brand { background: #2d2d2d; color: #a0aec0; }

.part-gains { display: flex; gap: 16px; margin: 16px 0; }
.gain-item { display: flex; align-items: center; gap: 6px; font-weight: 600; color: #10b981; }
html[data-color-scheme='dark'] .gain-item { color: #ffd700; }
.gain-icon { font-size: 0.9rem; font-weight: 800; }
.no-gain { color: #94a3b8; font-size: 0.9rem; font-style: italic; }
.part-notes { margin: 12px 0; color: #64748b; font-size: 0.9rem; line-height: 1.5; }

.tutorial-btn {
  width: 100%;
  padding: 10px;
  background: transparent;
  border: 2px solid #ff0000;
  color: #ff0000;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.3s;
  margin-top: 8px;
}
.tutorial-btn:hover { background: #ff0000; color: white; transform: translateY(-2px); }
html[data-color-scheme='dark'] .tutorial-btn { border-color: #ff4444; color: #ff4444; }
html[data-color-scheme='dark'] .tutorial-btn:hover { background: #ff4444; color: white; }

.add-btn {
  width: 100%; padding: 12px; background: #10b981;
  color: white; border: none; border-radius: 8px;
  font-weight: 600; cursor: pointer; transition: all 0.3s; margin-top: 12px;
}
html[data-color-scheme='dark'] .add-btn { background: #ffd700; color: #000; }
.add-btn:hover { background: #059669; transform: translateY(-2px); }
html[data-color-scheme='dark'] .add-btn:hover { background: #ffed4e; }
.add-btn.added { background: #064e3b; }
html[data-color-scheme='dark'] .add-btn.added { background: #b8860b; }

.build-summary {
  position: sticky; top: 100px;
  background: white; border: 2px solid #e2e8f0;
  border-radius: 16px; padding: 28px;
  height: fit-content;
  max-height: calc(100vh - 120px); overflow-y: auto;
}

html[data-color-scheme='dark'] .build-summary { background: #1a1a1a; border-color: #2d2d2d; }
.build-summary h2 { margin: 0 0 20px 0; font-size: 1.5rem; color: #1e293b; }
html[data-color-scheme='dark'] .build-summary h2 { color: #f5f5f5; }

.summary-car-info { padding: 16px; background: #f8fafc; border-radius: 10px; margin-bottom: 24px; }
html[data-color-scheme='dark'] .summary-car-info { background: #2d2d2d; }
.summary-car-info p { margin: 6px 0; color: #64748b; font-size: 0.95rem; }
.summary-car-info strong { color: #1e293b; font-size: 1.05rem; }
html[data-color-scheme='dark'] .summary-car-info strong { color: #f5f5f5; }

.summary-stats { display: flex; flex-direction: column; gap: 12px; margin: 24px 0; }

.stat-box {
  display: flex; justify-content: space-between; align-items: center;
  padding: 14px 16px; background: #f8fafc; border-radius: 10px;
}
html[data-color-scheme='dark'] .stat-box { background: #2d2d2d; }
.stat-box.highlight { background: rgba(16,185,129,0.1); border: 2px solid #10b981; }
html[data-color-scheme='dark'] .stat-box.highlight { background: rgba(255,215,0,0.1); border-color: #ffd700; }
.stat-label { font-weight: 600; color: #64748b; font-size: 0.9rem; }
.stat-value { font-weight: 700; color: #10b981; font-size: 1.2rem; }
html[data-color-scheme='dark'] .stat-value { color: #ffd700; }

.selected-parts-list { margin: 24px 0; }
.selected-parts-list h3 { margin: 0 0 16px 0; font-size: 1.1rem; color: #1e293b; }
html[data-color-scheme='dark'] .selected-parts-list h3 { color: #f5f5f5; }
.no-parts { padding: 20px; text-align: center; color: #94a3b8; font-style: italic; }

.selected-part-item {
  display: flex; justify-content: space-between; align-items: center;
  padding: 12px 14px; background: #f8fafc; border-radius: 8px; margin-bottom: 10px;
}
html[data-color-scheme='dark'] .selected-part-item { background: #2d2d2d; }

.part-item-info { flex: 1; display: flex; flex-direction: column; gap: 4px; }
.part-item-info strong { font-size: 0.95rem; color: #1e293b; }
html[data-color-scheme='dark'] .part-item-info strong { color: #f5f5f5; }
.part-item-gain { font-size: 0.85rem; font-weight: 600; color: #10b981; }
html[data-color-scheme='dark'] .part-item-gain { color: #ffd700; }

.remove-btn {
  background: #fecaca; color: #dc2626; border: none;
  width: 32px; height: 32px; border-radius: 6px;
  font-size: 1.1rem; font-weight: bold; cursor: pointer; transition: all 0.3s;
}
.remove-btn:hover { background: #dc2626; color: white; }

.summary-actions { display: flex; flex-direction: column; gap: 12px; margin-top: 24px; }

.btn-primary, .btn-secondary {
  width: 100%; padding: 14px; border: none;
  border-radius: 10px; font-weight: 600; font-size: 1rem;
  cursor: pointer; transition: all 0.3s;
}

.btn-primary {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
}
html[data-color-scheme='dark'] .btn-primary {
  background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
  color: #000;
}
.btn-primary:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(16,185,129,0.3); }
.btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }

.btn-secondary {
  background: #f1f5f9; color: #1e293b; border: 1px solid #e2e8f0;
}
html[data-color-scheme='dark'] .btn-secondary { background: #2d2d2d; color: #f5f5f5; border-color: #404040; }
.btn-secondary:hover:not(:disabled) { background: #e2e8f0; }
.btn-secondary:disabled { opacity: 0.5; cursor: not-allowed; }

.empty-state { text-align: center; padding: 60px 20px; color: #94a3b8; }
.empty-state h3 { font-size: 1.4rem; color: #64748b; margin-bottom: 10px; }

.modal-overlay {
  position: fixed; top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.6);
  display: flex; align-items: center; justify-content: center;
  z-index: 1000; backdrop-filter: blur(4px);
}

.modal-content {
  background: white; padding: 30px; border-radius: 16px;
  width: 90%; max-width: 450px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.2);
  animation: slideUp 0.3s ease-out;
}
html[data-color-scheme='dark'] .modal-content { background: #1a1a1a; border: 1px solid #2d2d2d; }
.modal-content h3 { margin: 0 0 10px 0; font-size: 1.5rem; color: #1e293b; }
html[data-color-scheme='dark'] .modal-content h3 { color: #f5f5f5; }
.modal-content p { color: #64748b; margin: 0 0 20px 0; font-size: 0.95rem; }
html[data-color-scheme='dark'] .modal-content p { color: #a0aec0; }

.modal-input {
  width: 100%; padding: 14px 16px;
  border: 2px solid #e2e8f0; border-radius: 10px;
  font-size: 1.05rem; margin-bottom: 24px; outline: none;
  transition: all 0.3s; background: #f8fafc; color: #1e293b;
  box-sizing: border-box;
}
html[data-color-scheme='dark'] .modal-input { background: #2d2d2d; border-color: #404040; color: #f5f5f5; }
.modal-input:focus { border-color: #10b981; background: white; box-shadow: 0 0 0 4px rgba(16,185,129,0.1); }
html[data-color-scheme='dark'] .modal-input:focus { border-color: #ffd700; background: #1a1a1a; }

.modal-actions { display: flex; justify-content: flex-end; gap: 12px; }

.btn-cancel {
  padding: 12px 20px; background: #f1f5f9; border: none;
  border-radius: 10px; font-weight: 600; color: #64748b; cursor: pointer;
}
html[data-color-scheme='dark'] .btn-cancel { background: #2d2d2d; color: #a0aec0; }
.btn-cancel:hover { background: #e2e8f0; }
html[data-color-scheme='dark'] .btn-cancel:hover { background: #404040; }

.modal-save-btn { width: auto; }

/* Tutorial modal */
.tutorial-modal-content {
  background: white;
  padding: 28px;
  border-radius: 16px;
  width: 90%;
  max-width: 720px;
  position: relative;
  box-shadow: 0 20px 60px rgba(0,0,0,0.4);
  animation: slideUp 0.3s ease-out;
}
html[data-color-scheme='dark'] .tutorial-modal-content {
  background: #1a1a1a;
  border: 1px solid #2d2d2d;
}

.tutorial-close-btn {
  position: absolute;
  top: 16px; right: 16px;
  background: #f1f5f9;
  border: none;
  width: 36px; height: 36px;
  border-radius: 50%;
  font-size: 1rem;
  cursor: pointer;
  font-weight: bold;
  color: #64748b;
  transition: all 0.2s;
}
.tutorial-close-btn:hover { background: #e2e8f0; }
html[data-color-scheme='dark'] .tutorial-close-btn { background: #2d2d2d; color: #a0aec0; }

.tutorial-modal-content h3 {
  margin: 0 0 4px 0;
  font-size: 1.4rem;
  color: #1e293b;
  padding-right: 40px;
}
html[data-color-scheme='dark'] .tutorial-modal-content h3 { color: #f5f5f5; }

.tutorial-subtitle {
  color: #94a3b8;
  font-size: 0.9rem;
  margin: 0 0 16px 0;
}

.ratio-16x9 {
  position: relative;
  width: 100%;
  padding-bottom: 56.25%;
  border-radius: 10px;
  overflow: hidden;
  background: #000;
}
.ratio-16x9 iframe {
  position: absolute;
  top: 0; left: 0;
  width: 100%; height: 100%;
}

/* Channel shoutout — simple text style */
.channel-shoutout {
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.95rem;
}
html[data-color-scheme='dark'] .channel-shoutout {
  border-top-color: #2d2d2d;
}

.channel-label {
  color: #64748b;
}
html[data-color-scheme='dark'] .channel-label {
  color: #a0aec0;
}

.channel-link {
  color: #10b981;
  font-weight: 600;
  text-decoration: none;
  transition: color 0.2s;
}
html[data-color-scheme='dark'] .channel-link {
  color: #ffd700;
}
.channel-link:hover {
  text-decoration: underline;
  color: #059669;
}
html[data-color-scheme='dark'] .channel-link:hover {
  color: #ffed4e;
}

@keyframes slideUp {
  from { opacity: 0; transform: translateY(20px); }
  to   { opacity: 1; transform: translateY(0); }
}
</style>