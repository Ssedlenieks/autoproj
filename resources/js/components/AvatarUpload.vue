<template>
  <div class="avatar-upload">
    <div class="avatar-preview">
      <img v-if="previewUrl" :src="previewUrl" class="avatar-img" />
      <div v-else class="avatar-placeholder">📷</div>
    </div>

    <input
      type="file"
      ref="fileInput"
      @change="onFileSelect"
      accept="image/*"
      class="file-input"
    />

    <button @click="$refs.fileInput.click()" class="upload-btn" :disabled="uploading">
      {{ uploading ? 'Uploading...' : 'Choose Photo' }}
    </button>

    <span v-if="error" class="error">{{ error }}</span>
    <span v-if="success" class="success">{{ success }}</span>
  </div>
</template>

<script>
export default {
  props: {
    currentAvatar: String,
  },
  data() {
    return {
      previewUrl: null,
      uploading: false,
      error: null,
      success: null,
    };
  },
  mounted() {
    if (this.currentAvatar) {
      this.previewUrl = '/storage/' + this.currentAvatar;
    }
  },
  methods: {
    onFileSelect(e) {
      const file = e.target.files;
      if (!file) return;

      // Validate file size
      if (file.size > 2 * 1024 * 1024) { // 2MB
        this.error = 'File must be less than 2MB';
        return;
      }

      // Preview
      const reader = new FileReader();
      reader.onload = (event) => {
        this.previewUrl = event.target.result;
      };
      reader.readAsDataURL(file);

      // Upload
      this.uploadAvatar(file);
    },

    async uploadAvatar(file) {
      this.uploading = true;
      this.error = null;
      this.success = null;

      const formData = new FormData();
      formData.append('avatar', file);

      try {
        const response = await fetch('/avatar/upload', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
          },
          body: formData,
        });

        const data = await response.json();

        if (!response.ok) {
          this.error = data.message || 'Upload failed';
          return;
        }

        this.success = 'Avatar uploaded successfully!';
        this.$emit('uploaded', data.avatar_url);
      } catch (err) {
        this.error = err.message;
      } finally {
        this.uploading = false;
      }
    },
  },
};
</script>

<style scoped>
.avatar-upload {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.avatar-preview {
  width: 150px;
  height: 150px;
  border-radius: 50%;
  overflow: hidden;
  background: #f0f0f0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 3rem;
  border: 2px solid #e2e8f0;
}

.avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.file-input {
  display: none;
}

.upload-btn {
  padding: 0.75rem 1.5rem;
  background: #10b981;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s;
}

.upload-btn:hover:not(:disabled) {
  background: #059669;
}

.upload-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error {
  color: #dc2626;
  font-size: 0.9rem;
}

.success {
  color: #10b981;
  font-size: 0.9rem;
}
</style>
