<template>
  <div class="pv-container">

    <!-- Loading -->
    <div v-if="loading" class="pv-loading">
      <div class="loading-spinner"></div>
      <p>Ielādē projektu...</p>
    </div>

    <!-- Error / Private -->
    <div v-else-if="error" class="pv-error">
      <div class="error-icon">🔒</div>
      <h2>{{ error }}</h2>
      <button @click="$router.back()" class="btn-back">← Atpakaļ</button>
    </div>

    <!-- Project content -->
    <div v-else-if="project" class="pv-content">

      <!-- Top bar -->
      <div class="pv-topbar">
        <button @click="$router.back()" class="btn-back">← Atpakaļ</button>
      </div>

      <!-- Hero section -->
      <div class="pv-hero">
        <div class="pv-hero-img-side" v-if="project.car?.image_url || project.car?.imageurl">
          <img
            :src="project.car.image_url || project.car.imageurl"
            :alt="project.car.model?.name"
            class="pv-hero-img"
            @error="e => e.target.src = 'https://via.placeholder.com/600x400?text=No+Image'"
          />
        </div>
        <div class="pv-hero-img-side pv-hero-placeholder" v-else>
          <span>🚗</span>
        </div>

        <div class="pv-hero-text">
          <div class="pv-hero-badges">
            <span class="public-chip"> Publisks projekts</span>
            <span class="view-count">👁 {{ project.views || 0 }} skatījumi</span>
          </div>
          <h1 class="notranslate">{{ project.project_name }}</h1>
          <p class="pv-car-subtitle notranslate">
            {{ project.car?.model?.make?.name }} {{ project.car?.model?.name }}
            · {{ project.car?.trim }} · {{ project.car?.year }}
          </p>
          <p class="pv-engine-chip notranslate">⚙️ {{ project.engine?.code }}</p>

          <div class="pv-hero-stats">
            <div class="hs-item">
              <span class="hs-label">Jauda</span>
              <span class="hs-val hp">{{ project.final_hp }} HP</span>
              <span class="hs-gain">(+{{ project.total_hp_gain }})</span>
            </div>
            <div class="hs-divider"></div>
            <div class="hs-item">
              <span class="hs-label">Moments</span>
              <span class="hs-val torque">{{ project.final_torque || '—' }} Nm</span>
              <span class="hs-gain">(+{{ project.total_torque_gain || 0 }})</span>
            </div>
            <div class="hs-divider"></div>
            <div class="hs-item">
              <span class="hs-label">Detaļas</span>
              <span class="hs-val">{{ project.parts?.length || 0 }}</span>
            </div>
          </div>
        </div>
      </div>

      <div class="pv-grid">

        <!-- Left column -->
        <div class="pv-left">

          <!-- Performance card -->
          <div class="info-card">
            <h3 class="card-title">📊 Veiktspējas rezultāti</h3>

            <div class="perf-row">
              <div class="perf-block">
                <span class="perf-label">Sākotnējā jauda</span>
                <span class="perf-val base">{{ project.base_hp }} HP</span>
              </div>
              <div class="perf-arrow">→</div>
              <div class="perf-block">
                <span class="perf-label">Kopējā jauda</span>
                <span class="perf-val final">{{ project.final_hp }} HP</span>
              </div>
              <div class="perf-gain hp">+{{ project.total_hp_gain }} HP</div>
            </div>

            <div class="perf-divider"></div>

            <div class="perf-row">
              <div class="perf-block">
                <span class="perf-label">Sākotnējais moments</span>
                <span class="perf-val base torque">{{ project.base_torque || '—' }} Nm</span>
              </div>
              <div class="perf-arrow">→</div>
              <div class="perf-block">
                <span class="perf-label">Kopējais moments</span>
                <span class="perf-val final torque">{{ project.final_torque || '—' }} Nm</span>
              </div>
              <div class="perf-gain torque">+{{ project.total_torque_gain || 0 }} Nm</div>
            </div>
          </div>

          <!-- Description -->
          <div class="info-card" v-if="project.description">
            <h3 class="card-title">📝 Apraksts</h3>
            <p class="pv-description">{{ project.description }}</p>
          </div>

          <!-- Parts list -->
          <div class="info-card">
            <h3 class="card-title">🔧 Uzstādītās detaļas ({{ project.parts?.length || 0 }})</h3>
            <div v-if="!project.parts || project.parts.length === 0" class="empty-parts">
              Nav pievienotu detaļu
            </div>
            <div v-else class="parts-list">
              <div
                v-for="part in project.parts"
                :key="part.id"
                class="part-row"
              >
                <div class="part-info">
                  <span class="part-name notranslate">{{ part.power_mod?.name || 'Nezināma detaļa' }}</span>
                  <span class="part-category notranslate">{{ part.power_mod?.category || '' }}</span>
                </div>
                <div class="part-gains">
                  <span class="part-gain hp">+{{ part.hp_gain }} HP</span>
                  <span class="part-gain torque">+{{ part.torque_nm_gain }} Nm</span>
                </div>
              </div>
            </div>
          </div>

        </div>

        <!-- Right column -->
        <div class="pv-right">

          <!-- Author card -->
          <div class="info-card author-card">
            <h3 class="card-title">👤 Autors</h3>
            <div class="author-row">
              <div class="author-avatar">
                <img
                  v-if="project.user?.avatar"
                  :src="project.user.avatar"
                  class="author-avatar-img"
                  @error="e => e.target.style.display='none'"
                />
                <div v-else class="author-avatar-letter notranslate">
                  {{ project.user?.name?.charAt(0).toUpperCase() }}
                </div>
              </div>
              <div class="author-details">
                <span class="author-name notranslate">{{ project.user?.name }}</span>
                <span class="author-builds">
                  {{ project.user?.projects_count || 0 }} publiski projekti
                </span>
              </div>
            </div>
          </div>

          <!-- Quick stats -->
          <div class="info-card">
            <h3 class="card-title">📋 Informācija</h3>
            <div class="meta-list">
              <div class="meta-row">
                <span class="meta-label">Publicēts</span>
                <span class="meta-val">{{ formatDate(project.created_at) }}</span>
              </div>
              <div class="meta-row">
                <span class="meta-label">Detaļas</span>
                <span class="meta-val">{{ project.parts?.length || 0 }}</span>
              </div>
              <div class="meta-row">
                <span class="meta-label">HP pieaugums</span>
                <span class="meta-val hp-text">+{{ project.total_hp_gain }} HP</span>
              </div>
              <div class="meta-row">
                <span class="meta-label">Nm pieaugums</span>
                <span class="meta-val torque-text">+{{ project.total_torque_gain || 0 }} Nm</span>
              </div>
              <div class="meta-row">
                <span class="meta-label">Skatījumi</span>
                <span class="meta-val">{{ project.views || 0 }}</span>
              </div>
            </div>
          </div>

          <!-- Read-only notice -->
          <div class="readonly-notice">
            <span>🔍 Tikai lasāms skats</span>
            <p>Šis ir cita lietotāja projekts. Tu nevari mainīt vai mijiedarboties ar to.</p>
          </div>

        </div>
      </div>
    </div>

  </div>
</template>

<script>
import axios from 'axios'
import { toast } from 'vue3-toastify'

export default {
  props: {
    id: {
      type: String,
      required: true,
    },
  },

  data() {
    return {
      loading: true,
      project: null,
      error: null,
    }
  },

  async mounted() {
    await this.loadProject()
  },

  methods: {
    async loadProject() {
      this.loading = true
      this.error = null
      try {
        const res = await axios.get(`/api/projects/${this.id}`)
        if (res.data.success) {
          this.project = res.data.project
        } else {
          this.error = 'Projekts nav atrasts'
        }
      } catch (err) {
        if (err.response?.status === 403) {
          this.error = 'Šis projekts ir privāts'
        } else if (err.response?.status === 404) {
          this.error = 'Projekts nav atrasts'
        } else {
          this.error = 'Neizdevās ielādēt projektu'
          toast.error('Neizdevās ielādēt projektu')
        }
      } finally {
        this.loading = false
      }
    },

    formatDate(date) {
      return new Date(date).toLocaleDateString('lv-LV', {
        year: 'numeric', month: 'long', day: 'numeric',
      })
    },
  },
}
</script>

<style scoped>
.pv-container {
  min-height: 100vh;
  background: #f8fafc;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}
html[data-color-scheme='dark'] .pv-container { background: #0a0a0a; color: #f5f5f5; }

/* Loading */
.pv-loading {
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  min-height: 60vh; gap: 16px; color: #64748b;
}
.loading-spinner {
  width: 44px; height: 44px;
  border: 4px solid rgba(16,185,129,0.2);
  border-top-color: #10b981; border-radius: 50%;
  animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Error */
.pv-error {
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  min-height: 60vh; gap: 16px; text-align: center; padding: 40px;
}
.error-icon { font-size: 3rem; }
.pv-error h2 { color: #1e293b; font-size: 1.4rem; }
html[data-color-scheme='dark'] .pv-error h2 { color: #f5f5f5; }

/* Top bar */
.pv-topbar {
  display: flex; align-items: center;
  padding: 14px 30px;
  background: white; border-bottom: 1px solid #e2e8f0;
  position: sticky; top: 0; z-index: 100;
}
html[data-color-scheme='dark'] .pv-topbar { background: #1a1a1a; border-bottom-color: #2d2d2d; }

.btn-back {
  padding: 9px 18px; background: white; border: 1px solid #e2e8f0;
  border-radius: 8px; font-weight: 600; font-size: 0.9rem;
  cursor: pointer; transition: all 0.2s; color: #1e293b;
}
html[data-color-scheme='dark'] .btn-back { background: #1a1a1a; border-color: #2d2d2d; color: #f5f5f5; }
.btn-back:hover { border-color: #10b981; color: #10b981; }
html[data-color-scheme='dark'] .btn-back:hover { border-color: #ffd700; color: #ffd700; }

/* Hero */
.pv-hero {
  display: flex;
  align-items: stretch;
  background: white;
  border-bottom: 1px solid #e2e8f0;
  min-height: 280px;
  overflow: hidden;
}
html[data-color-scheme='dark'] .pv-hero { background: #1a1a1a; border-bottom-color: #2d2d2d; }

.pv-hero-img-side {
  width: 420px;
  min-width: 320px;
  flex-shrink: 0;
  overflow: hidden;
  background: #f1f5f9;
}
html[data-color-scheme='dark'] .pv-hero-img-side { background: #2d2d2d; }

.pv-hero-placeholder {
  display: flex; align-items: center; justify-content: center;
  font-size: 5rem;
}

.pv-hero-img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  object-position: center;
  padding: 16px;
  background: #f8fafc;
}
html[data-color-scheme='dark'] .pv-hero-img { background: #1a1a1a; }

.pv-hero-text {
  flex: 1;
  padding: 36px 40px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 12px;
}

.pv-hero-badges {
  display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
}

.public-chip {
  padding: 4px 12px; background: #d1fae5; color: #065f46;
  border-radius: 20px; font-size: 0.82rem; font-weight: 600;
}
html[data-color-scheme='dark'] .public-chip { background: #064e3b; color: #6ee7b7; }

.view-count { font-size: 0.85rem; color: #94a3b8; font-weight: 500; }

.pv-hero-text h1 {
  margin: 0; font-size: 1.9rem; font-weight: 800;
  color: #1e293b; line-height: 1.2;
}
html[data-color-scheme='dark'] .pv-hero-text h1 { color: #f5f5f5; }

.pv-car-subtitle { margin: 0; color: #64748b; font-size: 1rem; }

.pv-engine-chip {
  display: inline-block; width: fit-content;
  padding: 5px 14px; background: #f1f5f9;
  border: 1px solid #e2e8f0; border-radius: 20px;
  color: #475569; font-size: 0.85rem; font-weight: 600;
}
html[data-color-scheme='dark'] .pv-engine-chip { background: #2d2d2d; border-color: #404040; color: #94a3b8; }

.pv-hero-stats {
  display: flex; align-items: center;
  background: #f8fafc; border-radius: 12px;
  padding: 14px 20px; margin-top: 8px;
  border: 1px solid #e2e8f0;
  width: fit-content;
}
html[data-color-scheme='dark'] .pv-hero-stats { background: #2d2d2d; border-color: #404040; }

.hs-item { display: flex; flex-direction: column; align-items: center; gap: 2px; padding: 0 16px; }
.hs-label { font-size: 0.72rem; color: #94a3b8; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; }
.hs-val { font-size: 1.2rem; font-weight: 800; color: #1e293b; }
html[data-color-scheme='dark'] .hs-val { color: #f5f5f5; }
.hs-val.hp { color: #10b981; }
.hs-val.torque { color: #3b82f6; }
.hs-gain { font-size: 0.75rem; color: #10b981; font-weight: 600; }
.hs-divider { width: 1px; height: 40px; background: #e2e8f0; }
html[data-color-scheme='dark'] .hs-divider { background: #404040; }

/* Grid layout */
.pv-grid {
  display: grid;
  grid-template-columns: 1fr 340px;
  gap: 24px;
  padding: 30px;
  max-width: 1300px; margin: 0 auto;
}
@media (max-width: 900px) {
  .pv-grid { grid-template-columns: 1fr; }
}

/* Cards */
.info-card {
  background: white; border: 1px solid #e2e8f0;
  border-radius: 14px; padding: 24px; margin-bottom: 20px;
}
html[data-color-scheme='dark'] .info-card { background: #1a1a1a; border-color: #2d2d2d; }

.card-title {
  margin: 0 0 20px 0; font-size: 1rem; font-weight: 700;
  color: #1e293b; padding-bottom: 12px;
  border-bottom: 1px solid #f1f5f9;
}
html[data-color-scheme='dark'] .card-title { color: #f5f5f5; border-bottom-color: #2d2d2d; }

/* Performance */
.perf-row {
  display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
}
.perf-block { display: flex; flex-direction: column; gap: 4px; flex: 1; min-width: 100px; }
.perf-label { font-size: 0.78rem; color: #94a3b8; text-transform: uppercase; font-weight: 600; }
.perf-val { font-size: 1.4rem; font-weight: 800; }
.perf-val.base { color: #64748b; }
.perf-val.final { color: #10b981; }
.perf-val.torque.final { color: #3b82f6; }
.perf-val.torque.base { color: #64748b; }
.perf-arrow { font-size: 1.2rem; color: #cbd5e1; font-weight: 700; }
.perf-gain {
  padding: 6px 14px; border-radius: 20px;
  font-size: 0.9rem; font-weight: 700; white-space: nowrap;
}
.perf-gain.hp { background: #d1fae5; color: #065f46; }
html[data-color-scheme='dark'] .perf-gain.hp { background: #064e3b; color: #6ee7b7; }
.perf-gain.torque { background: #dbeafe; color: #1e40af; }
html[data-color-scheme='dark'] .perf-gain.torque { background: #1e3a5f; color: #93c5fd; }
.perf-divider { height: 1px; background: #f1f5f9; margin: 20px 0; }
html[data-color-scheme='dark'] .perf-divider { background: #2d2d2d; }

/* Description */
.pv-description {
  margin: 0; color: #475569; line-height: 1.7; font-size: 0.95rem;
}
html[data-color-scheme='dark'] .pv-description { color: #94a3b8; }

/* Parts list */
.empty-parts { color: #94a3b8; font-size: 0.9rem; text-align: center; padding: 20px 0; }

.parts-list { display: flex; flex-direction: column; gap: 10px; }

.part-row {
  display: flex; justify-content: space-between; align-items: center;
  padding: 12px 16px; background: #f8fafc;
  border-radius: 10px; border: 1px solid #f1f5f9;
  transition: border-color 0.2s;
}
html[data-color-scheme='dark'] .part-row { background: #2d2d2d; border-color: #404040; }
.part-row:hover { border-color: #10b981; }
html[data-color-scheme='dark'] .part-row:hover { border-color: #ffd700; }

.part-info { display: flex; flex-direction: column; gap: 3px; }
.part-name { font-weight: 600; font-size: 0.92rem; color: #1e293b; }
html[data-color-scheme='dark'] .part-name { color: #f5f5f5; }
.part-category {
  font-size: 0.75rem; color: #94a3b8;
  text-transform: uppercase; letter-spacing: 0.5px;
}
.part-gains { display: flex; gap: 8px; }
.part-gain {
  padding: 3px 10px; border-radius: 12px;
  font-size: 0.78rem; font-weight: 700;
}
.part-gain.hp { background: #d1fae5; color: #065f46; }
html[data-color-scheme='dark'] .part-gain.hp { background: #064e3b; color: #6ee7b7; }
.part-gain.torque { background: #dbeafe; color: #1e40af; }
html[data-color-scheme='dark'] .part-gain.torque { background: #1e3a5f; color: #93c5fd; }

/* Author */
.author-card { margin-bottom: 20px; }
.author-row { display: flex; align-items: center; gap: 14px; margin-bottom: 4px; }
.author-avatar { flex-shrink: 0; }
.author-avatar-img {
  width: 48px; height: 48px; border-radius: 50%; object-fit: cover;
}
.author-avatar-letter {
  width: 48px; height: 48px; border-radius: 50%;
  background: linear-gradient(135deg, #10b981, #059669);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.2rem; font-weight: 700; color: white;
}
html[data-color-scheme='dark'] .author-avatar-letter {
  background: linear-gradient(135deg, #ffd700, #ffed4e); color: #000;
}
.author-details { display: flex; flex-direction: column; gap: 4px; }
.author-name { font-weight: 700; font-size: 1rem; color: #1e293b; }
html[data-color-scheme='dark'] .author-name { color: #f5f5f5; }
.author-builds { font-size: 0.82rem; color: #94a3b8; }

/* Meta info */
.meta-list { display: flex; flex-direction: column; gap: 12px; }
.meta-row { display: flex; justify-content: space-between; align-items: center; }
.meta-label { font-size: 0.85rem; color: #94a3b8; font-weight: 500; }
.meta-val { font-size: 0.9rem; font-weight: 600; color: #1e293b; }
html[data-color-scheme='dark'] .meta-val { color: #f5f5f5; }
.meta-val.hp-text { color: #10b981; }
.meta-val.torque-text { color: #3b82f6; }

/* Read-only notice */
.readonly-notice {
  background: #fffbeb; border: 1px solid #fde68a;
  border-radius: 10px; padding: 16px;
  font-size: 0.85rem;
}
html[data-color-scheme='dark'] .readonly-notice { background: #1c1a0a; border-color: #713f12; }
.readonly-notice span { font-weight: 700; color: #92400e; display: block; margin-bottom: 6px; }
html[data-color-scheme='dark'] .readonly-notice span { color: #fcd34d; }
.readonly-notice p { margin: 0; color: #a16207; line-height: 1.5; }
html[data-color-scheme='dark'] .readonly-notice p { color: #d97706; }

/* Mobile */
@media (max-width: 700px) {
  .pv-hero { flex-direction: column; }
  .pv-hero-img-side { width: 100%; min-width: unset; height: 220px; }
  .pv-hero-img { padding: 8px; }
  .pv-hero-text { padding: 20px; }
  .pv-hero-text h1 { font-size: 1.4rem; }
  .pv-grid { padding: 16px; }
  .pv-hero-stats { width: 100%; justify-content: space-around; }
}
</style>