<template>
  <div class="public-projects-container">

    <header class="pp-header">
      <button @click="$router.back()" class="back-btn">← Atpakaļ</button>
      <div class="pp-title">
        <h1>Publisko projektu galerija</h1>
        <p>Apskatiet ko citi būvētāji radījuši</p>
      </div>
    </header>

    <!-- Search / Filter bar -->
    <div class="filter-bar">
      <input
        v-model="search"
        class="search-input"
        placeholder="Meklēt pēc nosaukuma vai automobiļa..."
        @input="filterProjects"
      />
      <select v-model="sortBy" class="sort-select" @change="filterProjects">
        <option value="latest">Jaunākie</option>
        <option value="hp">Augstākā HP</option>
        <option value="torque">Augstākais moments</option>
        <option value="parts">Visvairāk detaļu</option>
      </select>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="loading">
      <div class="loading-spinner"></div>
      <p>Ielādē projektus...</p>
    </div>

    <!-- Empty -->
    <div v-else-if="filtered.length === 0" class="empty-state">
      <div class="empty-icon">🔍</div>
      <h3>Nav atrasts neviens projekts</h3>
      <p>Citi lietotāji vēl nav publicējuši projektus.</p>
    </div>

    <!-- Grid -->
    <div v-else class="projects-grid">
      <div
        v-for="project in filtered"
        :key="project.id"
        class="project-card"
        @click="$router.push(`/projects/${project.id}`)"
      >
        <!-- Car image -->
        <div class="card-img-wrapper">
          <img
            v-if="project.car?.image_url || project.car?.imageurl"
            :src="project.car.image_url || project.car.imageurl"
            :alt="project.car.model?.name"
            class="card-img"
            @error="e => e.target.src='https://via.placeholder.com/400x220?text=No+Image'"
          />
          <div v-else class="card-img-placeholder">🚗</div>
        </div>

        <!-- Content -->
        <div class="card-body">
          <div class="card-top">
            <h3 class="notranslate">{{ project.project_name }}</h3>
            <span class="parts-badge">{{ project.parts?.length || 0 }} detaļas</span>
          </div>

          <p class="car-name notranslate">
            {{ project.car?.model?.make?.name }} {{ project.car?.model?.name }}
            · {{ project.car?.year }}
          </p>
          <p class="engine-name notranslate">⚙️ {{ project.engine?.code }}</p>

          <!-- Stats -->
          <div class="card-stats">
            <div class="cs-item">
              <span class="cs-label">HP</span>
              <span class="cs-val">{{ project.final_hp }}</span>
              <span class="cs-gain">(+{{ project.total_hp_gain }})</span>
            </div>
            <div class="cs-divider"></div>
            <div class="cs-item">
              <span class="cs-label">Nm</span>
              <span class="cs-val" style="color:#3b82f6">{{ project.final_torque || '—' }}</span>
              <span class="cs-gain" style="color:#3b82f6">(+{{ project.total_torque_gain || 0 }})</span>
            </div>
          </div>

          <!-- Author -->
          <div class="card-author">
            <div class="author-avatar-sm">
              <img
                v-if="project.user?.avatar"
                :src="project.user.avatar"
                class="avatar-sm-img"
                @error="e => e.target.style.display='none'"
              />
              <div v-else class="avatar-sm-letter notranslate">
                {{ project.user?.name?.charAt(0).toUpperCase() }}
              </div>
            </div>
            <span class="author-name notranslate">{{ project.user?.name }}</span>
            <span class="card-date">{{ formatDate(project.created_at) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Load more -->
    <div v-if="hasMore && !loading" class="load-more-wrapper">
      <button @click="loadMore" class="btn-load-more" :disabled="loadingMore">
        {{ loadingMore ? 'Ielādē...' : 'Ielādēt vairāk' }}
      </button>
    </div>

  </div>
</template>

<script>
import axios from 'axios'
import { toast } from 'vue3-toastify'

export default {
  data() {
    return {
      loading: true,
      loadingMore: false,
      projects: [],
      filtered: [],
      search: '',
      sortBy: 'latest',
      currentPage: 1,
      hasMore: false,
    }
  },

  async mounted() {
    await this.loadProjects()
  },

  methods: {
    async loadProjects() {
      this.loading = true
      try {
        const res = await axios.get('/api/public-projects?page=1')
        if (res.data.success) {
          this.projects    = res.data.projects.data
          this.currentPage = 1
          this.hasMore     = !!res.data.projects.next_page_url
          this.applyFilter()
        }
      } catch (err) {
        console.error(err)
        toast.error('Neizdevās ielādēt projektus')
      } finally {
        this.loading = false
      }
    },

    async loadMore() {
      this.loadingMore = true
      try {
        const res = await axios.get(`/api/public-projects?page=${this.currentPage + 1}`)
        if (res.data.success) {
          this.projects.push(...res.data.projects.data)
          this.currentPage++
          this.hasMore = !!res.data.projects.next_page_url
          this.applyFilter()
        }
      } catch (err) {
        toast.error('Neizdevās ielādēt')
      } finally {
        this.loadingMore = false
      }
    },

    filterProjects() {
      this.applyFilter()
    },

    applyFilter() {
      let result = [...this.projects]

      // Search
      if (this.search.trim()) {
        const q = this.search.toLowerCase()
        result = result.filter(p =>
          p.project_name?.toLowerCase().includes(q) ||
          p.car?.model?.name?.toLowerCase().includes(q) ||
          p.car?.model?.make?.name?.toLowerCase().includes(q) ||
          p.user?.name?.toLowerCase().includes(q)
        )
      }

      // Sort
      if (this.sortBy === 'hp') {
        result.sort((a, b) => (b.final_hp || 0) - (a.final_hp || 0))
      } else if (this.sortBy === 'torque') {
        result.sort((a, b) => (b.final_torque || 0) - (a.final_torque || 0))
      } else if (this.sortBy === 'parts') {
        result.sort((a, b) => (b.parts?.length || 0) - (a.parts?.length || 0))
      }
      // 'latest' — jau sakārtots no API

      this.filtered = result
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
.public-projects-container {
  min-height: 100vh;
  background: #f8fafc;
  padding: 30px;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}
html[data-color-scheme='dark'] .public-projects-container { background: #0a0a0a; color: #f5f5f5; }

.pp-header {
  display: flex; align-items: center; gap: 20px;
  margin-bottom: 28px;
}
.back-btn {
  padding: 9px 18px; background: white; border: 1px solid #e2e8f0;
  border-radius: 8px; font-weight: 600; font-size: 0.9rem;
  cursor: pointer; transition: all 0.2s; color: #1e293b; white-space: nowrap;
}
html[data-color-scheme='dark'] .back-btn { background: #1a1a1a; border-color: #2d2d2d; color: #f5f5f5; }
.back-btn:hover { border-color: #10b981; color: #10b981; }
html[data-color-scheme='dark'] .back-btn:hover { border-color: #ffd700; color: #ffd700; }

.pp-title h1 { margin: 0; font-size: 1.8rem; color: #1e293b; }
html[data-color-scheme='dark'] .pp-title h1 { color: #f5f5f5; }
.pp-title p { margin: 4px 0 0 0; color: #64748b; font-size: 0.95rem; }

/* Filter bar */
.filter-bar {
  display: flex; gap: 12px; margin-bottom: 28px; flex-wrap: wrap;
}
.search-input {
  flex: 1; min-width: 220px; padding: 11px 16px;
  border: 2px solid #e2e8f0; border-radius: 10px;
  font-size: 0.95rem; background: white; color: #1e293b;
  outline: none; transition: all 0.2s;
}
.search-input:focus { border-color: #10b981; }
html[data-color-scheme='dark'] .search-input { background: #1a1a1a; border-color: #2d2d2d; color: #f5f5f5; }
html[data-color-scheme='dark'] .search-input:focus { border-color: #ffd700; }

.sort-select {
  padding: 11px 16px; border: 2px solid #e2e8f0; border-radius: 10px;
  font-size: 0.95rem; background: white; color: #1e293b;
  cursor: pointer; outline: none; transition: all 0.2s;
}
html[data-color-scheme='dark'] .sort-select { background: #1a1a1a; border-color: #2d2d2d; color: #f5f5f5; }

/* Loading */
.loading {
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  min-height: 40vh; gap: 16px; color: #64748b;
}
.loading-spinner {
  width: 44px; height: 44px;
  border: 4px solid rgba(16,185,129,0.2);
  border-top-color: #10b981; border-radius: 50%;
  animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Empty */
.empty-state {
  text-align: center; padding: 80px 20px;
  display: flex; flex-direction: column; align-items: center; gap: 12px;
}
.empty-icon { font-size: 3rem; }
.empty-state h3 { margin: 0; font-size: 1.4rem; color: #1e293b; }
html[data-color-scheme='dark'] .empty-state h3 { color: #f5f5f5; }
.empty-state p { color: #64748b; margin: 0; }

/* Grid */
.projects-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 24px;
  max-width: 1400px; margin: 0 auto;
}

.project-card {
  background: white; border: 2px solid #e2e8f0;
  border-radius: 14px; overflow: hidden;
  cursor: pointer; transition: all 0.25s;
  display: flex; flex-direction: column;
}
html[data-color-scheme='dark'] .project-card { background: #1a1a1a; border-color: #2d2d2d; }
.project-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 28px rgba(16,185,129,0.15);
  border-color: #10b981;
}
html[data-color-scheme='dark'] .project-card:hover { border-color: #ffd700; box-shadow: 0 10px 28px rgba(255,215,0,0.1); }

.card-img-wrapper { width: 100%; height: 200px; overflow: hidden; background: #f1f5f9; }
html[data-color-scheme='dark'] .card-img-wrapper { background: #2d2d2d; }
.card-img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s; }
.project-card:hover .card-img { transform: scale(1.04); }
.card-img-placeholder {
  width: 100%; height: 100%;
  display: flex; align-items: center; justify-content: center;
  font-size: 3.5rem; color: #cbd5e1;
}

.card-body { padding: 18px 20px; display: flex; flex-direction: column; gap: 8px; flex: 1; }

.card-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; }
.card-top h3 { margin: 0; font-size: 1.05rem; font-weight: 700; color: #1e293b; line-height: 1.3; }
html[data-color-scheme='dark'] .card-top h3 { color: #f5f5f5; }

.parts-badge {
  padding: 3px 10px; background: #f1f5f9;
  border-radius: 12px; font-size: 0.75rem;
  font-weight: 600; color: #64748b; white-space: nowrap;
}
html[data-color-scheme='dark'] .parts-badge { background: #2d2d2d; color: #94a3b8; }

.car-name { margin: 0; color: #64748b; font-size: 0.88rem; }
.engine-name { margin: 0; color: #94a3b8; font-size: 0.82rem; }

.card-stats {
  display: flex; align-items: center; gap: 0;
  background: #f8fafc; border-radius: 8px;
  padding: 10px 14px; margin: 4px 0;
}
html[data-color-scheme='dark'] .card-stats { background: #2d2d2d; }
.cs-item { display: flex; align-items: baseline; gap: 5px; flex: 1; }
.cs-label { font-size: 0.75rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; }
.cs-val { font-size: 1.1rem; font-weight: 700; color: #10b981; }
.cs-gain { font-size: 0.78rem; color: #10b981; opacity: 0.8; }
.cs-divider { width: 1px; height: 28px; background: #e2e8f0; margin: 0 14px; }
html[data-color-scheme='dark'] .cs-divider { background: #404040; }

.card-author {
  display: flex; align-items: center; gap: 8px;
  padding-top: 10px; border-top: 1px solid #f1f5f9;
  margin-top: auto;
}
html[data-color-scheme='dark'] .card-author { border-top-color: #2d2d2d; }

.author-avatar-sm { flex-shrink: 0; }
.avatar-sm-img { width: 28px; height: 28px; border-radius: 50%; object-fit: cover; }
.avatar-sm-letter {
  width: 28px; height: 28px; border-radius: 50%;
  background: linear-gradient(135deg, #10b981, #059669);
  display: flex; align-items: center; justify-content: center;
  font-size: 0.85rem; font-weight: 700; color: white;
}
html[data-color-scheme='dark'] .avatar-sm-letter { background: linear-gradient(135deg, #ffd700, #ffed4e); color: #000; }
.author-name { font-size: 0.85rem; font-weight: 600; color: #475569; flex: 1; }
html[data-color-scheme='dark'] .author-name { color: #94a3b8; }
.card-date { font-size: 0.75rem; color: #94a3b8; }

/* Load more */
.load-more-wrapper { display: flex; justify-content: center; margin-top: 36px; }
.btn-load-more {
  padding: 12px 36px; background: white;
  border: 2px solid #10b981; color: #10b981;
  border-radius: 10px; font-weight: 700; font-size: 0.95rem;
  cursor: pointer; transition: all 0.2s;
}
.btn-load-more:hover:not(:disabled) { background: #10b981; color: white; }
.btn-load-more:disabled { opacity: 0.5; cursor: not-allowed; }
html[data-color-scheme='dark'] .btn-load-more { background: #1a1a1a; border-color: #ffd700; color: #ffd700; }
html[data-color-scheme='dark'] .btn-load-more:hover:not(:disabled) { background: #ffd700; color: #000; }
</style>