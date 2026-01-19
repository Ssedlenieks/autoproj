<template>
  <button class="theme-toggle" @click="toggleDarkMode" :title="colorScheme === 'dark' ? 'Light Mode' : 'Dark Mode'">
    <span v-if="colorScheme === 'light'" class="icon">🌙</span>
    <span v-else class="icon">☀️</span>
  </button>
</template>

<script>
import { inject } from 'vue'

export default {
  setup() {
    const colorScheme = inject('colorScheme')
    const toggleDarkMode = inject('toggleDarkMode')

    return {
      colorScheme,
      toggleDarkMode
    }
  }
}
</script>

<style scoped>
.theme-toggle {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 100;
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: rgba(16, 185, 129, 0.2);
  border: 1.5px solid rgba(16, 185, 129, 0.5);
  color: #10b981;
  font-size: 1.5rem;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
}

.theme-toggle:hover {
  background: rgba(16, 185, 129, 0.35);
  border-color: rgba(16, 185, 129, 0.8);
  transform: scale(1.05);
}

[data-color-scheme="dark"] .theme-toggle {
  background: rgba(50, 184, 198, 0.2);
  border-color: rgba(50, 184, 198, 0.5);
  color: #32b8c6;
}

[data-color-scheme="dark"] .theme-toggle:hover {
  background: rgba(50, 184, 198, 0.35);
  border-color: rgba(50, 184, 198, 0.8);
}

.icon {
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
