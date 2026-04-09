<template>
  <div class="avatar-upload">
    <div class="avatar-preview" @click="triggerFileInput">
      <img v-if="currentAvatar" :src="currentAvatar" alt="Avatar" />
      <div v-else class="avatar-placeholder">
        {{ userName.charAt(0).toUpperCase() }}
      </div>
      <div class="avatar-overlay">
        <span class="camera-icon">📷</span>
        <span class="upload-text">Change Photo</span>
      </div>
    </div>

    <input
      ref="fileInput"
      type="file"
      accept="image/*"
      @change="handleFileSelect"
      style="display: none"
    />

    <div v-if="uploading" class="upload-progress">
      <div class="progress-spinner"></div>
      <p>Uploading...</p>
    </div>

    <button v-if="currentAvatar" @click="deleteAvatar" class="btn-delete-avatar">
      Remove Photo
    </button>
  </div>
</template>

<script>
import axios from 'axios'
import { toast } from 'vue3-toastify'

export default {
  props: {
    userName: {
      type: String,
      required: true
    },
    initialAvatar: {
      type: String,
      default: null
    }
  },

  data() {
    return {
      currentAvatar: this.initialAvatar,
      uploading: false,
    }
  },

  methods: {
    triggerFileInput() {
      this.$refs.fileInput.click()
    },

    async handleFileSelect(event) {
      const file = event.target.files[0]
      if (!file) return

      // Validate file size (5MB)
      if (file.size > 5 * 1024 * 1024) {
        toast.error('Image size must be less than 5MB')
        return
      }

      // Validate file type
      if (!file.type.startsWith('image/')) {
        toast.error('Please select an image file')
        return
      }

      this.uploading = true

      try {
        const formData = new FormData()
        formData.append('avatar', file)

        const res = await axios.post('/api/avatar/upload', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })

        if (res.data.success) {
          this.currentAvatar = res.data.avatar_url

          // Update localStorage
          const user = JSON.parse(localStorage.getItem('user') || '{}')
          user.avatar = res.data.avatar_url
          localStorage.setItem('user', JSON.stringify(user))

          toast.success('✅ Avatar updated successfully!')
          this.$emit('avatar-updated', res.data.avatar_url)
        }
      } catch (error) {
        console.error('Avatar upload error:', error)
        toast.error('Failed to upload avatar')
      } finally {
        this.uploading = false
      }
    },

    async deleteAvatar() {
      if (!confirm('Delete your profile picture?')) return

      try {
        const res = await axios.delete('/api/avatar/delete')

        if (res.data.success) {
          this.currentAvatar = null

          // Update localStorage
          const user = JSON.parse(localStorage.getItem('user') || '{}')
          user.avatar = null
          localStorage.setItem('user', JSON.stringify(user))

          toast.success('Avatar removed')
          this.$emit('avatar-updated', null)
        }
      } catch (error) {
        console.error('Avatar delete error:', error)
        toast.error('Failed to delete avatar')
      }
    }
  }
}
</script>

<style scoped>
.avatar-upload {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
}

.avatar-preview {
  position: relative;
  width: 150px;
  height: 150px;
  border-radius: 50%;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.3s;
  border: 4px solid #10b981;
}

html[data-color-scheme='dark'] .avatar-preview {
  border-color: #ffd700;
}

.avatar-preview:hover {
  transform: scale(1.05);
}

.avatar-preview:hover .avatar-overlay {
  opacity: 1;
}

.avatar-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
  font-size: 3rem;
  font-weight: bold;
}

html[data-color-scheme='dark'] .avatar-placeholder {
  background: linear-gradient(135deg, #ffd700, #ffed4e);
  color: #000;
}

.avatar-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.3s;
  gap: 8px;
}

.camera-icon {
  font-size: 2rem;
}

.upload-text {
  color: white;
  font-size: 0.9rem;
  font-weight: 600;
}

.upload-progress {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
}

.progress-spinner {
  width: 30px;
  height: 30px;
  border: 3px solid rgba(16, 185, 129, 0.2);
  border-top-color: #10b981;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.btn-delete-avatar {
  padding: 8px 20px;
  background: #fee2e2;
  color: #dc2626;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-delete-avatar:hover {
  background: #dc2626;
  color: white;
}
</style>
