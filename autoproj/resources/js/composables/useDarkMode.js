import { ref, watch } from 'vue'

const colorScheme = ref('light')

export function useDarkMode() {
  try {
    // Load theme
    const saved = localStorage.getItem('theme')
    if (saved) {
      colorScheme.value = saved
    } else {
      colorScheme.value = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
    }

    // Apply immediately
    document.documentElement.setAttribute('data-color-scheme', colorScheme.value)
  } catch (e) {
    console.error('Dark mode init error:', e)
  }

  // Watch for changes and apply theme
  watch(colorScheme, (newValue) => {
    document.documentElement.setAttribute('data-color-scheme', newValue)
  })

  const toggleDarkMode = () => {
    colorScheme.value = colorScheme.value === 'dark' ? 'light' : 'dark'
    localStorage.setItem('theme', colorScheme.value)
  }

  return { colorScheme, toggleDarkMode }
}
