<template>
  <div class="project-view-container">
    <ThemeToggle />

    <div class="container">
      <h1>Project View</h1>
      <p>Coming soon! Project ID: {{ id }}</p>
      <button @click="$router.push('/dashboard')" class="btn-back">
        Back to Dashboard
      </button>
    </div>
  </div>
</template>

<script>
import ThemeToggle from '../components/ThemeToggle.vue'

export default {
  components: { ThemeToggle },

  props: {
    id: {
      type: String,
      required: true
    }
  }
}
</script>

<style scoped>
.project-view-container {
  min-height: 100vh;
  padding: 40px;
  background: white;
}

html[data-color-scheme='dark'] .project-view-container {
  background: #0a0a0a;
  color: #f5f5f5;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
}

.btn-back {
  padding: 12px 24px;
  background: #10b981;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  margin-top: 20px;
}

html[data-color-scheme='dark'] .btn-back {
  background: #ffd700;
  color: #000;
}
</style>
