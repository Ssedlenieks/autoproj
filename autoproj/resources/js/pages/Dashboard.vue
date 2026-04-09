<template>
  <div class="dashboard-container">
    <!-- Loading -->
    <div v-if="loading" class="loading">
      <div class="loading-spinner"></div>
      <p>Loading dashboard...</p>
    </div>

    <div v-else-if="user" class="dashboard-content">
      <!-- Header with Avatar Upload -->
      <header class="dashboard-header">
        <div class="user-profile">
          <!-- Avatar with Upload -->
          <div class="avatar-wrapper">
            <div class="avatar-preview" @click="triggerFileInput">
              <img
                v-if="user.avatar_url"
                :src="user.avatar_url"
                class="avatar avatar-img"
              />
              <div v-else class="avatar notranslate">
                {{ user.name.charAt(0).toUpperCase() }}
              </div>

              <div class="avatar-overlay">
                <span class="camera-icon">UP</span>
              </div>
            </div>

            <input
              ref="fileInput"
              type="file"
              accept="image/*"
              @change="handleImageSelect"
              style="display: none"
            />

            <div v-if="uploading" class="upload-status">Uploading...</div>
            <button
              v-if="user.avatar_url"
              @click="deleteAvatar"
              class="btn-remove-avatar"
            >
              Remove
            </button>
          </div>

          <div class="user-info">
            <!-- PROTECTED: User name -->
            <h1 class="notranslate">{{ user.name }}</h1>
            <p class="user-email notranslate">{{ user.email }}</p>

            <!-- NEW: Leaderboard button under user info -->
            <button
              class="btn-primary"
              style="margin-top: 12px;"
              @click="$router.push('/leaderboards')"
            >
              View Global Leaderboard
            </button>
          </div>
        </div>

        <div class="level-badge">
          <!-- PROTECTED: LVL badge -->
          <div
            class="level-icon notranslate"
            :style="{ color: stats.level_color }"
          >
            LVL
          </div>
          <div class="level-info">
            <span class="level-text">Level {{ stats.level }}</span>
            <span class="rank-text">{{ stats.rank }}</span>
          </div>
          <div class="xp-bar">
            <div
              class="xp-fill"
              :style="{
                width: stats.level_progress + '%',
                background: stats.level_color
              }"
            ></div>
          </div>
          <span class="xp-text">
            {{ stats.total_points }} / {{ stats.points_for_next_level }} XP
          </span>
          <span class="xp-remaining">
            {{ stats.points_to_next_level }} to Level {{ stats.level + 1 }}
          </span>
        </div>
      </header>

      <!-- Stats Grid -->
      <div class="stats-grid">
        <div class="stat-card">
          <!-- PROTECTED: BLD acronym -->
          <div class="stat-icon notranslate">BLD</div>
          <div class="stat-value">{{ stats.total_builds }}</div>
          <div class="stat-label">Total Builds</div>
        </div>

        <div class="stat-card">
          <!-- PROTECTED: ACH acronym -->
          <div class="stat-icon notranslate">ACH</div>
          <div class="stat-value">{{ stats.achievements_unlocked }}</div>
          <div class="stat-label">Achievements</div>
        </div>

        <div class="stat-card">
          <!-- PROTECTED: PWR acronym -->
          <div class="stat-icon notranslate">PWR</div>
          <div class="stat-value">+{{ stats.total_hp_gained }}</div>
          <div class="stat-label">Total HP Gained</div>
        </div>

        <!-- NEW: Total Torque Gained -->
        <div class="stat-card">
          <!-- PROTECTED: TRQ acronym -->
          <div class="stat-icon notranslate">TRQ</div>
          <div class="stat-value">
            +{{ stats.total_torque_gained || 0 }}
          </div>
          <div class="stat-label">Total Torque Gained</div>
        </div>

        <!-- Optional: Show Max Torque Build if you added it to backend -->
        <div class="stat-card" v-if="stats.most_torquey_build">
          <!-- PROTECTED: MAX acronym -->
          <div class="stat-icon notranslate">MAX</div>
          <div class="stat-value">
            {{ stats.most_torquey_build.final_torque }}
          </div>
          <div class="stat-label">Highest Torque</div>
        </div>
      </div>

      <!-- Achievements Section -->
      <section class="achievements-section">
        <h2>Achievements ({{ stats.achievements_unlocked }})</h2>

        <div
          v-if="user.achievements && user.achievements.length === 0"
          class="empty-state"
        >
          <div class="empty-icon notranslate">!</div>
          <h3>No achievements yet</h3>
          <p>Complete builds to unlock achievements.</p>
        </div>

        <div v-else class="achievements-grid">
          <div
            v-for="achievement in user.achievements"
            :key="achievement.id"
            class="achievement-card unlocked"
          >
            <!-- PROTECTED: Icons/stars -->
            <div class="achievement-icon notranslate">
              {{ achievement.icon || '★' }}
            </div>
            <div class="achievement-info">
              <h3>{{ achievement.name }}</h3>
              <p>{{ achievement.description }}</p>
              <span class="achievement-points">
                +{{ achievement.points }} pts
              </span>
            </div>
          </div>
        </div>
      </section>

      <!-- Builds Section -->
      <section class="builds-section">
        <div class="section-header">
          <h2>Your Builds ({{ user.projects.length }})</h2>
          <button
            @click="$router.push('/builder')"
            class="btn-new-build"
          >
            + New Build
          </button>
        </div>

        <div v-if="user.projects.length === 0" class="empty-state">
          <!-- PROTECTED: ADD acronym -->
          <div class="empty-icon notranslate">ADD</div>
          <h3>No builds yet</h3>
          <p>Start building your dream car.</p>
          <button
            @click="$router.push('/builder')"
            class="btn-primary"
          >
            Create First Build
          </button>
        </div>

        <div v-else class="builds-grid">
          <div
            v-for="project in user.projects"
            :key="project.id"
            class="build-card"
          >
            <div class="build-header">
              <!-- PROTECTED: Project Name -->
              <h3 class="notranslate">
                {{ project.project_name }}
              </h3>
              <button
                @click.stop="deleteBuild(project.id)"
                class="btn-delete"
              >
                x
              </button>
            </div>

            <!-- NEW: Car Image -->
            <div
              class="build-image-wrapper"
              v-if="project.car.image_url || project.car.imageurl"
            >
              <img
                :src="getImageUrl(project.car)"
                :alt="project.car.model.name"
                class="build-car-image"
                @error="handleImageError"
              />
            </div>

            <div class="build-car-info">
              <!-- PROTECTED: Car make and model -->
              <strong class="notranslate">
                {{ project.car.model.make.name }}
                {{ project.car.model.name }}
              </strong>
              <p class="notranslate">
                {{ project.car.trim }} ({{ project.car.year }})
              </p>
              <p>
                Engine:
                <span class="notranslate">
                  {{ project.engine.code }}
                </span>
              </p>
            </div>

            <div class="build-stats">
              <div class="stat">
                <span class="stat-label">Power (HP):</span>
                <span class="stat-value">
                  {{ project.base_hp }} → {{ project.final_hp }}
                  <span class="gain">
                    (+{{ project.total_hp_gain }})
                  </span>
                </span>
              </div>

              <!-- NEW: Torque in Build List -->
              <div class="stat">
                <span class="stat-label">Torque (Nm):</span>
                <span class="stat-value">
                  {{ project.base_torque || '-' }}
                  →
                  {{ project.final_torque || '-' }}
                  <span class="gain torque-gain">
                    (+{{ project.total_torque_gain || 0 }})
                  </span>
                </span>
              </div>

              <div class="stat">
                <span class="stat-label">Parts Installed:</span>
                <span class="stat-value">
                  {{ project.parts ? project.parts.length : 0 }}
                </span>
              </div>
            </div>

            <div class="build-footer">
              <span class="build-date">
                {{ formatDate(project.created_at) }}
              </span>
            </div>
          </div>
        </div>
      </section>
    </div>

    <div v-else class="error-state">
      <h2>Failed to load dashboard</h2>
      <button @click="loadDashboard" class="btn-retry">
        Retry
      </button>
    </div>

    <!-- Avatar Cropper Modal -->
    <AvatarCropper
      :show="showCropper"
      :image-src="selectedImage"
      @close="closeCropper"
      @upload="handleAvatarUpload"
    />
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

  async mounted() {
    await this.loadDashboard()
  },

  methods: {
    async loadDashboard() {
      this.loading = true

      try {
        const res = await axios.get('/api/dashboard')

        if (res.data.success) {
          this.user = res.data.user
          // Merge in case backend hasn't added torque stats yet
          this.stats = { ...this.stats, ...res.data.stats }
        }
      } catch (error) {
        console.error('Dashboard load error:', error)

        if (error.response?.status === 401) {
          localStorage.removeItem('user')
          this.$router.push('/login')
        } else {
          toast.error('Failed to load dashboard')
        }
      } finally {
        this.loading = false
      }
    },

    getImageUrl(car) {
      // Fallback for different column naming conventions (imageurl vs image_url)
      const url = car.image_url || car.imageurl
      if (!url) return 'https://via.placeholder.com/300x200?text=No+Image'
      return url
    },

    handleImageError(event) {
      event.target.src =
        'https://via.placeholder.com/300x200?text=Image+Not+Found'
    },

    triggerFileInput() {
      this.$refs.fileInput.click()
    },

    handleImageSelect(event) {
      const file = event.target.files[0]
      if (!file) return

      if (file.size > 5 * 1024 * 1024) {
        toast.error('Image size must be less than 5MB')
        return
      }

      if (!file.type.startsWith('image/')) {
        toast.error('Please select an image file')
        return
      }

      const reader = new FileReader()
      reader.onload = e => {
        this.selectedImage = e.target.result
        this.showCropper = true
      }
      reader.readAsDataURL(file)
      this.$refs.fileInput.value = ''
    },

    closeCropper() {
      this.showCropper = false
      this.selectedImage = ''
    },

    async handleAvatarUpload(formData) {
      this.uploading = true
      this.showCropper = false

      try {
        const res = await axios.post('/api/avatar/upload', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        })

        if (res.data.success) {
          this.user.avatar_url = res.data.avatar_url + '?t=' + Date.now()

          const user = JSON.parse(localStorage.getItem('user') || '{}')
          user.avatar = res.data.avatar_url
          localStorage.setItem('user', JSON.stringify(user))

          toast.success('Avatar updated successfully')
        }
      } catch (error) {
        console.error('Avatar upload error:', error)
        toast.error('Failed to upload avatar')
      } finally {
        this.uploading = false
        this.selectedImage = ''
      }
    },

    async deleteAvatar() {
      if (!confirm('Delete your profile picture?')) return

      try {
        const res = await axios.delete('/api/avatar/delete')

        if (res.data.success) {
          this.user.avatar_url = null

          const user = JSON.parse(localStorage.getItem('user') || '{}')
          user.avatar = null
          localStorage.setItem('user', JSON.stringify(user))

          toast.success('Avatar removed')
        }
      } catch (error) {
        console.error('Avatar delete error:', error)
        toast.error('Failed to delete avatar')
      }
    },

    async deleteBuild(id) {
      if (!confirm('Delete this build?')) return

      try {
        await axios.delete(`/api/projects/${id}`)
        toast.success('Build deleted')
        await this.loadDashboard()
      } catch (error) {
        console.error('Delete error:', error)
        toast.error('Failed to delete build')
      }
    },

    formatDate(date) {
      return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
      })
    },
  },
}
</script>

<style scoped>
/* =============================================
   Paste your ENTIRE existing <style scoped>
   block here — nothing changed in the CSS.
   ============================================= */
.dashboard-container {
  min-height: 100vh;
  background: #f8fafc;
  padding: 30px;
}

html[data-color-scheme='dark'] .dashboard-container {
  background: #0a0a0a;
  color: #f5f5f5;
}

/* Loading */
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

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Error State */
.error-state {
  text-align: center;
  padding: 60px 20px;
}

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

html[data-color-scheme='dark'] .btn-retry {
  background: #ffd700;
  color: #000;
}

.dashboard-content {
  max-width: 1400px;
  margin: 0 auto;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: white;
  padding: 30px;
  border-radius: 16px;
  margin-bottom: 30px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

html[data-color-scheme='dark'] .dashboard-header {
  background: #1a1a1a;
}

.user-profile {
  display: flex;
  gap: 20px;
  align-items: center;
}

/* Avatar Upload Styles */
.avatar-wrapper {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}

.avatar-preview {
  position: relative;
  cursor: pointer;
  transition: transform 0.3s;
}

.avatar-preview:hover {
  transform: scale(1.05);
}

.avatar-preview:hover .avatar-overlay {
  opacity: 1;
}

.avatar {
  width: 70px;
  height: 70px;
  border-radius: 50%;
  background: linear-gradient(135deg, #10b981, #059669);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  font-weight: bold;
  color: white;
}

html[data-color-scheme='dark'] .avatar {
  background: linear-gradient(135deg, #ffd700, #ffed4e);
  color: #000;
}

.avatar-img {
  width: 70px;
  height: 70px;
  border-radius: 50%;
  object-fit: cover;
}

.avatar-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 70px;
  height: 70px;
  border-radius: 50%;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.3s;
}

.camera-icon {
  font-size: 1rem;
  font-weight: bold;
  color: white;
}

.upload-status {
  font-size: 0.8rem;
  color: #10b981;
  font-weight: 600;
}

html[data-color-scheme='dark'] .upload-status {
  color: #ffd700;
}

.btn-remove-avatar {
  padding: 4px 12px;
  background: #fee2e2;
  color: #dc2626;
  border: none;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-remove-avatar:hover {
  background: #dc2626;
  color: white;
}

.user-info h1 {
  margin: 0;
  font-size: 1.8rem;
  color: #1e293b;
}

html[data-color-scheme='dark'] .user-info h1 {
  color: #f5f5f5;
}

.user-email {
  color: #64748b;
  margin: 4px 0 0 0;
}

/* Level Badge */
.level-badge {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  padding: 20px;
  background: #f0fdf4;
  border-radius: 12px;
  border: 2px solid #10b981;
  min-width: 200px;
}

html[data-color-scheme='dark'] .level-badge {
  background: #1a1a1a;
  border-color: #2d2d2d;
}

.level-icon {
  font-size: 1.8rem;
  font-weight: bold;
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
}

.level-info {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
}

.level-text {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
}

html[data-color-scheme='dark'] .level-text {
  color: #f5f5f5;
}

.rank-text {
  font-size: 0.9rem;
  font-weight: 600;
  color: #10b981;
  text-transform: uppercase;
  letter-spacing: 1px;
}

html[data-color-scheme='dark'] .rank-text {
  color: #ffd700;
}

.xp-bar {
  width: 100%;
  height: 8px;
  background: #e2e8f0;
  border-radius: 4px;
  overflow: hidden;
}

html[data-color-scheme='dark'] .xp-bar {
  background: #2d2d2d;
}

.xp-fill {
  height: 100%;
  transition: width 0.5s ease, background 0.3s ease;
  border-radius: 4px;
}

.xp-text {
  font-size: 0.85rem;
  font-weight: 600;
  color: #64748b;
}

.xp-remaining {
  font-size: 0.75rem;
  color: #94a3b8;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 40px;
}

.stat-card {
  background: white;
  padding: 30px;
  border-radius: 12px;
  text-align: center;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

html[data-color-scheme='dark'] .stat-card {
  background: #1a1a1a;
}

.stat-icon {
  font-size: 1.5rem;
  font-weight: bold;
  color: #94a3b8;
  margin-bottom: 10px;
}

.stat-value {
  font-size: 2.5rem;
  font-weight: 700;
  color: #10b981;
  margin: 10px 0;
}

html[data-color-scheme='dark'] .stat-value {
  color: #ffd700;
}

.stat-label {
  color: #64748b;
  font-size: 0.95rem;
}

/* Achievements */
.achievements-section,
.builds-section {
  margin-bottom: 40px;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.section-header h2 {
  font-size: 1.8rem;
  color: #1e293b;
}

html[data-color-scheme='dark'] .section-header h2 {
  color: #f5f5f5;
}

.achievements-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
}

.achievement-card {
  background: white;
  border: 2px solid #10b981;
  border-radius: 12px;
  padding: 20px;
  display: flex;
  gap: 16px;
  box-shadow: 0 2px 10px rgba(16, 185, 129, 0.1);
}

html[data-color-scheme='dark'] .achievement-card {
  background: #1a1a1a;
  border-color: #ffd700;
}

.achievement-icon {
  font-size: 2rem;
  font-weight: bold;
  color: #10b981;
}

html[data-color-scheme='dark'] .achievement-icon {
  color: #ffd700;
}

.achievement-info h3 {
  margin: 0 0 8px 0;
  font-size: 1.1rem;
  color: #1e293b;
}

html[data-color-scheme='dark'] .achievement-info h3 {
  color: #f5f5f5;
}

.achievement-info p {
  margin: 0 0 8px 0;
  color: #64748b;
  font-size: 0.9rem;
}

.achievement-points {
  color: #10b981;
  font-weight: 600;
  font-size: 0.9rem;
}

html[data-color-scheme='dark'] .achievement-points {
  color: #ffd700;
}

/* Builds */
.builds-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 24px;
}

.build-card {
  background: white;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  padding: 24px;
  transition: all 0.3s;
  display: flex;
  flex-direction: column;
}

html[data-color-scheme='dark'] .build-card {
  background: #1a1a1a;
  border-color: #2d2d2d;
}

.build-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(16, 185, 129, 0.15);
  border-color: #10b981;
}

.build-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
}

.build-header h3 {
  margin: 0;
  font-size: 1.2rem;
  color: #1e293b;
}

html[data-color-scheme='dark'] .build-header h3 {
  color: #f5f5f5;
}

.btn-delete {
  background: #fee2e2;
  color: #dc2626;
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 6px;
  font-size: 1.2rem;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-delete:hover {
  background: #dc2626;
  color: white;
}

/* New: Build Image */
.build-image-wrapper {
  width: 100%;
  height: 180px;
  border-radius: 8px;
  overflow: hidden;
  margin-bottom: 16px;
  background: #f8fafc;
}

html[data-color-scheme='dark'] .build-image-wrapper {
  background: #2d2d2d;
}

.build-car-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.build-card:hover .build-car-image {
  transform: scale(1.05);
}

.build-car-info {
  margin-bottom: 16px;
  padding-bottom: 16px;
  border-bottom: 1px solid #e2e8f0;
}

html[data-color-scheme='dark'] .build-car-info {
  border-bottom-color: #2d2d2d;
}

.build-car-info strong {
  font-size: 1.05rem;
  color: #1e293b;
}

html[data-color-scheme='dark'] .build-car-info strong {
  color: #f5f5f5;
}

.build-car-info p {
  margin: 4px 0;
  color: #64748b;
  font-size: 0.9rem;
}

.build-stats {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 16px;
  flex-grow: 1;
}

.build-stats .stat {
  display: flex;
  justify-content: space-between;
}

.build-stats .stat-label {
  color: #64748b;
  font-weight: 600;
}

.build-stats .stat-value {
  color: #1e293b;
  font-weight: 600;
}

html[data-color-scheme='dark'] .build-stats .stat-value {
  color: #f5f5f5;
}

.build-stats .gain {
  color: #10b981;
  font-weight: 700;
}

html[data-color-scheme='dark'] .build-stats .gain {
  color: #ffd700;
}

.build-stats .torque-gain {
  color: #3b82f6;
}

.build-footer {
  padding-top: 12px;
  border-top: 1px solid #e2e8f0;
}

html[data-color-scheme='dark'] .build-footer {
  border-top-color: #2d2d2d;
}

.build-date {
  color: #94a3b8;
  font-size: 0.85rem;
}

.btn-new-build,
.btn-primary {
  padding: 12px 24px;
  background: #10b981;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

html[data-color-scheme='dark'] .btn-new-build,
html[data-color-scheme='dark'] .btn-primary {
  background: #ffd700;
  color: #000;
}

.btn-new-build:hover,
.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  background: white;
  border-radius: 12px;
  border: 2px dashed #e2e8f0;
}

html[data-color-scheme='dark'] .empty-state {
  background: #1a1a1a;
  border-color: #2d2d2d;
}

.empty-icon {
  font-size: 2rem;
  font-weight: bold;
  color: #94a3b8;
  margin-bottom: 20px;
}

.empty-state h3 {
  font-size: 1.4rem;
  color: #1e293b;
  margin-bottom: 10px;
}

html[data-color-scheme='dark'] .empty-state h3 {
  color: #f5f5f5;
}

.empty-state p {
  color: #64748b;
  margin-bottom: 20px;
}
</style>
