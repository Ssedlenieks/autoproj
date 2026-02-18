<template>
  <div class="avatar-cropper-modal" v-if="show" @click.self="closeModal">
    <div class="cropper-container">
      <div class="cropper-header">
        <h3>Crop Your Avatar</h3>
        <button @click="closeModal" class="btn-close">×</button>
      </div>

      <div class="cropper-body">
        <Cropper
          ref="cropper"
          class="cropper"
          :src="imageSrc"
          :stencil-component="$options.components.CircleStencil"
          :stencil-props="{
            aspectRatio: 1,
            movable: false,
            resizable: false,
          }"
          :stencil-size="{
            width: 400,
            height: 400,
          }"
          :default-size="{
            width: 400,
            height: 400,
          }"
          :resize-image="{
            adjustStencil: false,
          }"
          image-restriction="stencil"
        />
      </div>

      <div class="cropper-footer">
        <button @click="closeModal" class="btn-cancel">Cancel</button>
        <button @click="cropAndUpload" class="btn-upload" :disabled="uploading">
          {{ uploading ? 'Uploading...' : 'Upload Avatar' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { Cropper, CircleStencil } from 'vue-advanced-cropper'
import 'vue-advanced-cropper/dist/style.css'

export default {
  components: {
    Cropper,
    CircleStencil,
  },

  props: {
    show: {
      type: Boolean,
      default: false,
    },
    imageSrc: {
      type: String,
      default: '',
    },
  },

  data() {
    return {
      uploading: false,
    }
  },

  watch: {
    show(newVal) {
      // Lock body scroll when modal opens
      if (newVal) {
        document.body.style.overflow = 'hidden'
      } else {
        document.body.style.overflow = ''
      }
    }
  },

  beforeUnmount() {
    // Cleanup: restore scroll when component unmounts
    document.body.style.overflow = ''
  },

  methods: {
    closeModal() {
      this.$emit('close')
    },

    async cropAndUpload() {
      const { canvas } = this.$refs.cropper.getResult()

      if (!canvas) {
        return
      }

      this.uploading = true

      canvas.toBlob(async (blob) => {
        const formData = new FormData()
        formData.append('avatar', blob, 'avatar.jpg')

        this.$emit('upload', formData)

        this.uploading = false
      }, 'image/jpeg', 0.95)
    },
  },
}
</script>

<style scoped>
.avatar-cropper-modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.85);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 20px;
  overflow: hidden; /* ✅ Prevent body scroll */
}

.cropper-container {
  background: white;
  border-radius: 16px;
  max-width: 550px;
  width: 100%;
  display: flex;
  flex-direction: column;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  max-height: 700px; /*  Fixed height */
}

html[data-color-scheme='dark'] .cropper-container {
  background: #1a1a1a;
}

.cropper-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid #e2e8f0;
  flex-shrink: 0; /*  Don't shrink */
}

html[data-color-scheme='dark'] .cropper-header {
  border-bottom-color: #2d2d2d;
}

.cropper-header h3 {
  margin: 0;
  font-size: 1.3rem;
  color: #1e293b;
}

html[data-color-scheme='dark'] .cropper-header h3 {
  color: #f5f5f5;
}

.btn-close {
  background: none;
  border: none;
  font-size: 2rem;
  color: #64748b;
  cursor: pointer;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  transition: all 0.3s;
  line-height: 1;
}

.btn-close:hover {
  background: #f1f5f9;
  color: #1e293b;
}

html[data-color-scheme='dark'] .btn-close:hover {
  background: #2d2d2d;
  color: #f5f5f5;
}

.cropper-body {
  padding: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f8fafc;
  overflow: hidden; /*  No scroll */
  flex-shrink: 0; /*  Don't shrink */
  height: 500px; /*  Fixed height */
}

html[data-color-scheme='dark'] .cropper-body {
  background: #0a0a0a;
}

.cropper {
  width: 100%;
  height: 100%;
  max-width: 450px;
  max-height: 450px;
}

.cropper-footer {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  padding: 20px 24px;
  border-top: 1px solid #e2e8f0;
  flex-shrink: 0; /*  Don't shrink */
}

html[data-color-scheme='dark'] .cropper-footer {
  border-top-color: #2d2d2d;
}

.btn-cancel,
.btn-upload {
  padding: 12px 24px;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  font-size: 1rem;
}

.btn-cancel {
  background: #f1f5f9;
  color: #64748b;
}

.btn-cancel:hover {
  background: #e2e8f0;
}

html[data-color-scheme='dark'] .btn-cancel {
  background: #2d2d2d;
  color: #94a3b8;
}

html[data-color-scheme='dark'] .btn-cancel:hover {
  background: #3d3d3d;
}

.btn-upload {
  background: #10b981;
  color: white;
  min-width: 140px;
}

.btn-upload:hover:not(:disabled) {
  background: #059669;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-upload:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

html[data-color-scheme='dark'] .btn-upload {
  background: #ffd700;
  color: #000;
}

html[data-color-scheme='dark'] .btn-upload:hover:not(:disabled) {
  background: #ffed4e;
}

/* Customize the circle stencil appearance */
:deep(.vue-advanced-cropper__foreground) {
  background: rgba(0, 0, 0, 0.5);
}

:deep(.vue-circle-stencil__handler) {
  display: none;
}

:deep(.vue-circle-stencil__preview) {
  border: 3px solid #10b981;
  box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.5);
}

html[data-color-scheme='dark'] :deep(.vue-circle-stencil__preview) {
  border-color: #ffd700;
}

/*  Remove scrollbar from cropper */
:deep(.vue-advanced-cropper__image-wrapper) {
  overflow: hidden !important;
}

:deep(.vue-advanced-cropper__stretcher) {
  overflow: hidden !important;
}
</style>

