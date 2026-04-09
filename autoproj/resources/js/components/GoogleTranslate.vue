<template>
  <div class="google-translate-container">
    <div id="google_translate_element"></div>
  </div>
</template>

<script>
import { onMounted, onBeforeUnmount } from 'vue'

export default {
  name: 'GoogleTranslate',
  setup() {
    let observer = null

    onMounted(() => {
      // Define callback BEFORE script loads
      window.googleTranslateElementInit = () => {
        new window.google.translate.TranslateElement(
          {
            pageLanguage: 'en',
            includedLanguages: 'en,lv',
            layout: window.google.translate.TranslateElement.InlineLayout.SIMPLE,
            autoDisplay: false
          },
          'google_translate_element'
        )
      }

      // Load script only once
      if (!document.querySelector('#google-translate-script')) {
        const script = document.createElement('script')
        script.id = 'google-translate-script'
        script.type = 'text/javascript'
        script.src = '//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit'
        document.head.appendChild(script)
      } else if (window.google && window.google.translate) {
        // Script already loaded, call init
        window.googleTranslateElementInit()
      }

      // Observer for banner only (NOT aggressive hiding)
      observer = new MutationObserver(() => {
        // Hide ONLY top banner iframe
        const banner = document.querySelector('iframe.goog-te-banner-frame')
        if (banner && banner.parentElement) {
          banner.parentElement.style.display = 'none'
        }

        // Reset body position
        document.body.style.top = '0px'
        document.body.style.position = 'static'

        // Hide logo only (safer)
        const logoLink = document.querySelector('.goog-te-gadget .goog-logo-link')
        if (logoLink) logoLink.style.display = 'none'
      })

      observer.observe(document.body, { childList: true })
    })

    onBeforeUnmount(() => {
      if (observer) observer.disconnect()
    })
  }
}
</script>

<style scoped>
.google-translate-container {
  display: inline-flex;
  align-items: center;
  height: 44px;
}

/* Hide Google's banner frame only */
.goog-te-banner-frame.skiptranslate,
.goog-te-banner-frame {
  display: none !important;
}

body {
  top: 0px !important;
}

/* Hide logo but keep widget */
.goog-logo-link {
  display: none !important;
}

/* Widget styling */
.goog-te-gadget-simple {
  background: rgba(255, 215, 0, 0.15) !important;
  border: 1.5px solid rgba(255, 215, 0, 0.5) !important;
  border-radius: 22px !important;
  padding: 0 16px !important;
  height: 44px !important;
  display: inline-flex !important;
  align-items: center !important;
  cursor: pointer !important;
  box-sizing: border-box !important;
  transition: all 0.3s ease !important;
}

.goog-te-gadget-simple:hover {
  background: rgba(255, 215, 0, 0.3) !important;
  border-color: rgba(255, 215, 0, 0.85) !important;
  transform: scale(1.05) !important;
}

/* Label text */
.goog-te-gadget-simple .goog-te-menu-value span:first-child {
  color: #ffd700 !important;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif !important;
  font-weight: 600 !important;
  font-size: 14px !important;
}

/* Down arrow */
.goog-te-gadget-simple span[aria-hidden="true"] {
  color: #ffd700 !important;
}

/* Popup menu */
.goog-te-menu-frame {
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25) !important;
  border-radius: 10px !important;
  border: 1px solid rgba(255, 215, 0, 0.3) !important;
  z-index: 99999999 !important;
}
</style>
