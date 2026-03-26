<template>
  <div class="parts-selector-container">

    <!-- Loading -->
    <div v-if="loading" class="loading-overlay">
      <div class="loading-spinner"></div>
      <p>Loading parts...</p>
    </div>

    <!-- Header -->
    <header class="parts-header">
      <button @click="goBack" class="back-btn">Back to Builder</button>
      <div class="header-info">
        <h1>{{ carInfo.make }} {{ carInfo.model }}</h1>
        <p>{{ carInfo.trim }} • {{ carInfo.year }} • {{ engineInfo.code }}</p>
      </div>
    </header>

    <!-- Main Content -->
    <div class="parts-content">
      <!-- Left: Categories & Parts -->
      <div class="parts-list">
        <div v-if="Object.keys(categories).length === 0 && !loading" class="empty-state">
          <div class="empty-icon"></div>
          <h3>No parts available for this car/engine combo</h3>
          <p>Try selecting a different engine or check back later</p>
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
                  Supporting Mod
                </div>
              </div>

              <p class="part-notes">{{ part.notes }}</p>

              <button
                @click.stop="togglePart(part)"
                :class="['add-btn', { added: isSelected(part.id) }]"
              >
                {{ isSelected(part.id) ? 'Added' : '+ Add' }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Right: Build Summary -->
      <aside class="build-summary">
        <h2>Your Build</h2>

        <div class="summary-car-info">
          <p><strong>{{ carInfo.make }} {{ carInfo.model }}</strong></p>
          <p>{{ carInfo.trim }} ({{ carInfo.year }})</p>
          <p>Engine: {{ engineInfo.code }}</p>
        </div>

        <div class="summary-stats">
          <!-- HP Stats -->
          <div class="stat-box">
            <span class="stat-label">Stock HP</span>
            <span class="stat-value">{{ baseHP }} HP</span>
          </div>
          <div class="stat-box highlight">
            <span class="stat-label">Final HP</span>
            <span class="stat-value">{{ baseHP + totalHPGain }} <span style="font-size: 0.8rem; opacity: 0.8">(+{{ totalHPGain }})</span></span>
          </div>

          <!-- Torque Stats -->
          <div class="stat-box" style="margin-top: 8px;">
            <span class="stat-label">Stock Torque</span>
            <span class="stat-value" style="color: #3b82f6;">{{ baseTorque }} Nm</span>
          </div>
          <div class="stat-box highlight" style="border-color: #3b82f6; background: rgba(59, 130, 246, 0.1);">
            <span class="stat-label">Final Torque</span>
            <span class="stat-value" style="color: #3b82f6;">{{ baseTorque + totalTorqueGain }} <span style="font-size: 0.8rem; opacity: 0.8">(+{{ totalTorqueGain }})</span></span>
          </div>
        </div>

        <div class="selected-parts-list">
          <h3>Selected Parts ({{ selectedParts.length }})</h3>
          <div v-if="selectedParts.length === 0" class="no-parts">
            No parts selected yet
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
          <button class="btn-primary" @click="openSaveModal" :disabled="selectedParts.length === 0">
            Save Build
          </button>
          <button class="btn-secondary" @click="clearAll" :disabled="selectedParts.length === 0">
            Clear All
          </button>
        </div>
      </aside>
    </div>

    <!-- Save Build Modal -->
    <div v-if="showSaveModal" class="modal-overlay" @click="closeSaveModal">
      <div class="modal-content" @click.stop>
        <h3>Name Your Build</h3>
        <p>Give your project a custom name before saving to your garage.</p>

        <input
          type="text"
          v-model="projectNameInput"
          class="modal-input"
          placeholder="e.g. My Track Beast"
          @keyup.enter="confirmSaveBuild"
          ref="projectNameRef"
        />

        <div class="modal-actions">
          <button class="btn-cancel" @click="closeSaveModal" :disabled="isSaving">Cancel</button>
          <button class="btn-primary modal-save-btn" @click="confirmSaveBuild" :disabled="!projectNameInput.trim() || isSaving">
            {{ isSaving ? 'Saving...' : 'Save Build' }}
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import axios from 'axios'
import ThemeToggle from '../components/ThemeToggle.vue'
import AchievementToast from '../components/AchievementToast.vue'
import { toast } from 'vue3-toastify'
import { h } from 'vue'

export default {
  components: { ThemeToggle },

  props: {
    carId: {
      type: String,
      required: true
    },
    engineId: {
      type: String,
      required: true
    }
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

      showSaveModal: false,
      projectNameInput: '',
      isSaving: false,
    }
  },

  computed: {
    totalHPGain() {
      return this.selectedParts.reduce((sum, part) => sum + (part.hp_gain || 0), 0)
    },

    totalTorqueGain() {
      return this.selectedParts.reduce((sum, part) => sum + (part.torque_nm_gain || 0), 0)
    },
  },

  async mounted() {
    await this.loadParts()
  },

  methods: {
    async loadParts() {
      this.loading = true
      try {
        const res = await axios.get(`/api/cars/${this.carId}/engines/${this.engineId}/parts`)

        if (res.data.success) {
          this.carInfo = res.data.car
          this.engineInfo = res.data.engine
          this.categories = res.data.categories

          this.baseHP = res.data.baseHP || 0
          this.baseTorque = res.data.baseTorque || 0
        }
      } catch (error) {
        console.error('Error loading parts:', error)
        toast.error('Failed to load parts')
        this.goBack()
      } finally {
        this.loading = false
      }
    },

    togglePart(part) {
      if (this.isSelected(part.id)) {
        this.removePart(part.id)
      } else {
        this.selectedParts.push(part)
        toast.success(`Added ${part.name}`, {
          autoClose: 2000,
        })
      }
    },

    isSelected(partId) {
      return this.selectedParts.some(p => p.id === partId)
    },

    removePart(partId) {
      this.selectedParts = this.selectedParts.filter(p => p.id !== partId)
      toast.info('Part removed', {
        autoClose: 2000,
      })
    },

    clearAll() {
      if (confirm('Remove all selected parts?')) {
        this.selectedParts = []
        toast.info('All parts cleared')
      }
    },

    openSaveModal() {
      const user = JSON.parse(localStorage.getItem('user') || 'null')
      if (!user) {
        if (confirm('You need to login to save builds. Go to login page?')) {
          this.$router.push({ name: 'Login', query: { redirect: this.$route.fullPath } })
        }
        return
      }

      this.projectNameInput = `${this.carInfo.make} ${this.carInfo.model} ${this.carInfo.year}`
      this.showSaveModal = true

      this.$nextTick(() => {
        if (this.$refs.projectNameRef) this.$refs.projectNameRef.focus()
      })
    },

    closeSaveModal() {
      this.showSaveModal = false
    },

    async confirmSaveBuild() {
      if (!this.projectNameInput.trim()) return

      this.isSaving = true
      const user = JSON.parse(localStorage.getItem('user') || '{}')

      const buildData = {
        car_id: parseInt(this.carId),
        engine_id: parseInt(this.engineId),
        project_name: this.projectNameInput.trim(),
        description: '',
        base_hp: this.baseHP,
        base_torque: this.baseTorque,
        parts: this.selectedParts.map(part => ({
          power_mod_id: part.id,
          hp_gain: part.hp_gain,
          torque_nm_gain: part.torque_nm_gain,
        }))
      }

      try {
        const res = await axios.post('/api/projects', buildData)

        if (res.data.success) {
          const updatedUser = {
            ...user,
            total_points: res.data.user?.total_points || user.total_points,
            level: res.data.user?.level || user.level,
          }
          localStorage.setItem('user', JSON.stringify(updatedUser))

          this.showSaveModal = false

          toast.success(`Build "${this.projectNameInput}" saved successfully!`, {
            autoClose: 3000,
          })

          if (res.data.newAchievements && res.data.newAchievements.length > 0) {
            res.data.newAchievements.forEach((achievement, index) => {
              setTimeout(() => {
                toast(h(AchievementToast, { achievement }), {
                  autoClose: 5000,
                  closeButton: false,
                  hideProgressBar: true,
                  className: 'achievement-toast-container',
                })
              }, 1000 + (index * 700))
            })
          }

          setTimeout(() => {
            this.$router.push('/dashboard')
          }, res.data.newAchievements?.length > 0 ? 3000 : 2000)
        }
      } catch (error) {
        console.error('Save build error:', error)

        if (error.response?.status === 401) {
          toast.error('Session expired. Please login again.')
          localStorage.removeItem('user')
          this.$router.push({ name: 'Login', query: { redirect: this.$route.fullPath } })
        } else if (error.response?.status === 422) {
          const errors = error.response.data.errors
          const errorMessages = Object.values(errors).flat().join('\n')
          toast.error(`Validation Error:\n${errorMessages}`)
        } else {
          toast.error(`Failed to save build:\n${error.response?.data?.message || error.message}`)
        }
      } finally {
        this.isSaving = false
      }
    },

    goBack() {
      this.$router.push('/builder')
    }
  }
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

/* Loading */
.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid rgba(16, 185, 129, 0.2);
  border-top-color: #10b981;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Header */
.parts-header {
  display: flex;
  align-items: center;
  gap: 24px;
  padding: 20px 30px;
  background: white;
  border-bottom: 1px solid #e2e8f0;
  position: sticky;
  top: 0;
  z-index: 100;
}

html[data-color-scheme='dark'] .parts-header {
  background: #1a1a1a;
  border-bottom-color: #2d2d2d;
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
  background: #2d2d2d;
  border-color: #404040;
  color: #f5f5f5;
}

.back-btn:hover {
  background: #e2e8f0;
  transform: translateX(-4px);
}

.header-info h1 {
  margin: 0;
  font-size: 1.8rem;
  font-weight: 700;
}

.header-info p {
  margin: 4px 0 0 0;
  color: #64748b;
  font-size: 0.95rem;
}

.achievement-toast-container {
  background: transparent !important;
  padding: 0 !important;
  box-shadow: none !important;
  border-radius: 12px !important;
}

.achievement-toast-container .Toastify__toast-body {
  padding: 0;
}

/* Main Content */
.parts-content {
  display: grid;
  grid-template-columns: 1fr 420px;
  gap: 30px;
  padding: 30px;
  max-width: 1800px;
  margin: 0 auto;
}

@media (max-width: 1200px) {
  .parts-content {
    grid-template-columns: 1fr;
  }

  .build-summary {
    position: relative !important;
    top: auto !important;
  }
}

/* Category Section */
.category-section {
  margin-bottom: 50px;
}

.category-title {
  font-size: 1.6rem;
  margin-bottom: 24px;
  color: #10b981;
  border-bottom: 3px solid #10b981;
  padding-bottom: 8px;
}

html[data-color-scheme='dark'] .category-title {
  color: #ffd700;
  border-bottom-color: #ffd700;
}

/* Parts Grid */
.parts-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 24px;
}

.part-card {
  background: white;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  padding: 24px;
  cursor: pointer;
  transition: all 0.3s ease;
}

html[data-color-scheme='dark'] .part-card {
  background: #1a1a1a;
  border-color: #2d2d2d;
}

.part-card:hover {
  border-color: #10b981;
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(16, 185, 129, 0.15);
}

html[data-color-scheme='dark'] .part-card:hover {
  border-color: #ffd700;
  box-shadow: 0 8px 24px rgba(255, 215, 0, 0.15);
}

.part-card.selected {
  border-color: #10b981;
  background: #f0fdf4;
}

html[data-color-scheme='dark'] .part-card.selected {
  border-color: #ffd700;
  background: #1a1a0f;
}

.part-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
}

.part-header h3 {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 600;
  color: #1e293b;
}

html[data-color-scheme='dark'] .part-header h3 {
  color: #f5f5f5;
}

.part-brand {
  padding: 4px 10px;
  background: #f1f5f9;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 600;
  color: #64748b;
}

html[data-color-scheme='dark'] .part-brand {
  background: #2d2d2d;
  color: #a0aec0;
}

/* Gains */
.part-gains {
  display: flex;
  gap: 16px;
  margin: 16px 0;
}

.gain-item {
  display: flex;
  align-items: center;
  gap: 6px;
  font-weight: 600;
  color: #10b981;
}

html[data-color-scheme='dark'] .gain-item {
  color: #ffd700;
}

.gain-icon {
  font-size: 0.9rem;
  font-weight: 800;
}

.no-gain {
  color: #94a3b8;
  font-size: 0.9rem;
  font-style: italic;
}

.part-notes {
  margin: 12px 0;
  color: #64748b;
  font-size: 0.9rem;
  line-height: 1.5;
}

/* Add Button */
.add-btn {
  width: 100%;
  padding: 12px;
  background: #10b981;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  margin-top: 12px;
}

html[data-color-scheme='dark'] .add-btn {
  background: #ffd700;
  color: #000;
}

.add-btn:hover {
  background: #059669;
  transform: translateY(-2px);
}

html[data-color-scheme='dark'] .add-btn:hover {
  background: #ffed4e;
}

.add-btn.added {
  background: #064e3b;
}

html[data-color-scheme='dark'] .add-btn.added {
  background: #b8860b;
}

/* Build Summary */
.build-summary {
  position: sticky;
  top: 100px;
  background: white;
  border: 2px solid #e2e8f0;
  border-radius: 16px;
  padding: 28px;
  height: fit-content;
  max-height: calc(100vh - 120px);
  overflow-y: auto;
}

html[data-color-scheme='dark'] .build-summary {
  background: #1a1a1a;
  border-color: #2d2d2d;
}

.build-summary h2 {
  margin: 0 0 20px 0;
  font-size: 1.5rem;
  color: #1e293b;
}

html[data-color-scheme='dark'] .build-summary h2 {
  color: #f5f5f5;
}

.summary-car-info {
  padding: 16px;
  background: #f8fafc;
  border-radius: 10px;
  margin-bottom: 24px;
}

html[data-color-scheme='dark'] .summary-car-info {
  background: #2d2d2d;
}

.summary-car-info p {
  margin: 6px 0;
  color: #64748b;
  font-size: 0.95rem;
}

.summary-car-info strong {
  color: #1e293b;
  font-size: 1.05rem;
}

html[data-color-scheme='dark'] .summary-car-info strong {
  color: #f5f5f5;
}

/* Stats */
.summary-stats {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin: 24px 0;
}

.stat-box {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 14px 16px;
  background: #f8fafc;
  border-radius: 10px;
}

html[data-color-scheme='dark'] .stat-box {
  background: #2d2d2d;
}

.stat-box.highlight {
  background: rgba(16, 185, 129, 0.1);
  border: 2px solid #10b981;
}

html[data-color-scheme='dark'] .stat-box.highlight {
  background: rgba(255, 215, 0, 0.1);
  border-color: #ffd700;
}

.stat-label {
  font-weight: 600;
  color: #64748b;
  font-size: 0.9rem;
}

.stat-value {
  font-weight: 700;
  color: #10b981;
  font-size: 1.2rem;
}

html[data-color-scheme='dark'] .stat-value {
  color: #ffd700;
}

/* Selected Parts List */
.selected-parts-list {
  margin: 24px 0;
}

.selected-parts-list h3 {
  margin: 0 0 16px 0;
  font-size: 1.1rem;
  color: #1e293b;
}

html[data-color-scheme='dark'] .selected-parts-list h3 {
  color: #f5f5f5;
}

.no-parts {
  padding: 20px;
  text-align: center;
  color: #94a3b8;
  font-style: italic;
}

.selected-part-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 14px;
  background: #f8fafc;
  border-radius: 8px;
  margin-bottom: 10px;
}

html[data-color-scheme='dark'] .selected-part-item {
  background: #2d2d2d;
}

.part-item-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.part-item-info strong {
  font-size: 0.95rem;
  color: #1e293b;
}

html[data-color-scheme='dark'] .part-item-info strong {
  color: #f5f5f5;
}

.part-item-gain {
  font-size: 0.85rem;
  font-weight: 600;
  color: #10b981;
}

html[data-color-scheme='dark'] .part-item-gain {
  color: #ffd700;
}

.remove-btn {
  background: #fecaca;
  color: #dc2626;
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 6px;
  font-size: 1.1rem;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s;
}

.remove-btn:hover {
  background: #dc2626;
  color: white;
}

/* Action Buttons */
.summary-actions {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-top: 24px;
}

.btn-primary, .btn-secondary {
  width: 100%;
  padding: 14px;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-primary {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
}

html[data-color-scheme='dark'] .btn-primary {
  background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
  color: #000;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(16, 185, 129, 0.3);
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-secondary {
  background: #f1f5f9;
  color: #1e293b;
  border: 1px solid #e2e8f0;
}

html[data-color-scheme='dark'] .btn-secondary {
  background: #2d2d2d;
  color: #f5f5f5;
  border-color: #404040;
}

.btn-secondary:hover:not(:disabled) {
  background: #e2e8f0;
}

.btn-secondary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #94a3b8;
}

.empty-state h3 {
  font-size: 1.4rem;
  color: #64748b;
  margin-bottom: 10px;
}

.empty-state p {
  color: #94a3b8;
}

/* Custom Save Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  backdrop-filter: blur(4px);
}

.modal-content {
  background: white;
  padding: 30px;
  border-radius: 16px;
  width: 90%;
  max-width: 450px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
  animation: slideUp 0.3s ease-out;
}

html[data-color-scheme='dark'] .modal-content {
  background: #1a1a1a;
  border: 1px solid #2d2d2d;
}

.modal-content h3 {
  margin: 0 0 10px 0;
  font-size: 1.5rem;
  color: #1e293b;
}

html[data-color-scheme='dark'] .modal-content h3 {
  color: #f5f5f5;
}

.modal-content p {
  color: #64748b;
  margin: 0 0 20px 0;
  font-size: 0.95rem;
}

html[data-color-scheme='dark'] .modal-content p {
  color: #a0aec0;
}

.modal-input {
  width: 100%;
  padding: 14px 16px;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  font-size: 1.05rem;
  margin-bottom: 24px;
  outline: none;
  transition: all 0.3s;
  background: #f8fafc;
  color: #1e293b;
  box-sizing: border-box;
}

html[data-color-scheme='dark'] .modal-input {
  background: #2d2d2d;
  border-color: #404040;
  color: #f5f5f5;
}

.modal-input:focus {
  border-color: #10b981;
  background: white;
  box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
}

html[data-color-scheme='dark'] .modal-input:focus {
  border-color: #ffd700;
  background: #1a1a1a;
  box-shadow: 0 0 0 4px rgba(255, 215, 0, 0.15);
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

.btn-cancel {
  padding: 12px 20px;
  background: #f1f5f9;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  color: #64748b;
  cursor: pointer;
  transition: background 0.3s;
}

html[data-color-scheme='dark'] .btn-cancel {
  background: #2d2d2d;
  color: #a0aec0;
}

.btn-cancel:hover {
  background: #e2e8f0;
}

html[data-color-scheme='dark'] .btn-cancel:hover {
  background: #404040;
}

.modal-save-btn {
  width: auto;
}

@keyframes slideUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
