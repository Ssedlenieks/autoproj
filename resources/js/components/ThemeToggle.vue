<template>
  <button class="theme-toggle" @click="toggleDarkMode" :title="colorScheme === 'dark' ? 'Light Mode' : 'Dark Mode'">
    <span class="icon">{{ colorScheme === 'light' ? '🌙' : '☀️' }}</span>
  </button>
</template>

<script>
import { inject } from 'vue'

export default {
  setup() {
    const colorScheme = inject('colorScheme')
    const toggleDarkMode = inject('toggleDarkMode')
    return { colorScheme, toggleDarkMode }
  }
}
</script>

<style scoped>
.theme-toggle {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: rgba(255, 215, 0, 0.15);
  border: 1.5px solid rgba(255, 215, 0, 0.5);
  color: #ffd700;
  font-size: 1.25rem;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
}

.theme-toggle:hover {
  background: rgba(255, 215, 0, 0.3);
  border-color: rgba(255, 215, 0, 0.85);
  transform: scale(1.05);
}

.icon {
  display: flex;
  align-items: center;
  justify-content: center;
  line-height: 1;
}
</style>
