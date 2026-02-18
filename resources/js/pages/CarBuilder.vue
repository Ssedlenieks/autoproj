<!-- UNIFIED Builder.vue - WITH MOD IT BUTTON -->
<template>
  <div class="builder-container">
    <ThemeToggle />

    <!-- Loading Overlay -->
    <div v-if="loading" class="loading-overlay">
      <div class="loading-spinner"></div>
      <p>Loading...</p>
    </div>

    <!-- Header -->
    <header class="builder-header">
      <div class="header-left">
        <h1>Potato Car Builder</h1>
      </div>
      <div class="header-right">
        <span class="user-info">
          {{ user.builds }} Builds
        </span>
      </div>
    </header>

    <!-- Toast Notifications -->
    <div v-if="toast.show" :class="['toast', toast.type]">
      {{ toast.message }}
      <button @click="toast.show = false" class="toast-close">✕</button>
    </div>

    <!-- Main Layout -->
    <div class="main-wrapper" :class="{ 'has-details': carDetails && !isMobile }">
      <!-- Filter Drawer Overlay -->
      <div v-if="isMobile && filterDrawerOpen" class="sidebar-overlay" @click="filterDrawerOpen = false"></div>

      <!-- Left Sidebar Filters -->
      <aside class="sidebar" :class="{ 'mobile-drawer': isMobile, 'drawer-open': filterDrawerOpen && isMobile }">
        <div class="sidebar-inner">
          <div class="sidebar-header" v-if="isMobile">
            <h2>Filters</h2>
            <button @click="filterDrawerOpen = false" class="close-drawer-btn">✕</button>
          </div>
          <h2 v-else>Filters</h2>

          <!-- Make Model -->
          <div class="filter-section">
            <h3>Make/Model</h3>
            <select v-model="selectedMake" @change="loadModels" class="filter-select">
              <option value="">Select Make</option>
              <option v-for="make in makes" :key="make.id" :value="make.id">
                {{ make.name }}
              </option>
            </select>
            <select
              v-if="selectedMake"
              v-model="selectedModel"
              @change="loadCars"
              class="filter-select"
              :disabled="loadingModels"
            >
              <option value="">Select Model</option>
              <option v-for="model in models" :key="model.id" :value="model.id">
                {{ model.name }}
              </option>
              <option v-if="loadingModels" value="" disabled>Loading...</option>
            </select>
          </div>

          <div class="filter-divider"></div>

          <!-- Year Range -->
          <div class="filter-section">
            <h3>Year</h3>
            <div class="year-range-display">{{ filters.yearMin }} - {{ filters.yearMax }}</div>
            <div class="year-slider-container">
              <input
                type="range"
                v-model.number="filters.yearMin"
                :min="minYear"
                :max="maxYear"
                @change="debouncedApplyFilters"
                class="year-range-slider"
              />
            </div>
          </div>

          <!-- Power Range -->
          <div class="filter-section">
            <h3>Power (HP)</h3>
            <div class="power-slider-container">
              <input
                v-model.number="filters.minPower"
                type="range"
                :min="minPower"
                :max="maxPower"
                step="10"
                @change="debouncedApplyFilters"
                class="power-range-slider"
              />
              <div class="slider-display">{{ filters.minPower }} HP</div>
            </div>
          </div>

          <!-- Sort Options -->
          <div class="filter-section">
            <h3>Sort By</h3>
            <select v-model="sortBy" @change="applySorting" class="filter-select">
              <option value="year_desc">Year: Newest First</option>
              <option value="year_asc">Year: Oldest First</option>
              <option value="power_desc">Power: High to Low</option>
              <option value="power_asc">Power: Low to High</option>
              <option value="name_asc">Name: A-Z</option>
              <option value="name_desc">Name: Z-A</option>
            </select>
          </div>

          <!-- Reset Button -->
          <div class="filter-section">
            <button @click="resetFilters" class="reset-button" :disabled="!hasActiveFilters">
              Reset Filters
            </button>
          </div>
        </div>
      </aside>

      <!-- Main Content -->
      <main class="content">
        <!-- Mobile Filter Button -->
        <div v-if="isMobile" class="mobile-filter-header">
          <button @click="toggleFilterDrawer" class="mobile-filter-btn">
            Filters
            <span v-if="hasActiveFilters" class="filter-badge">{{ activeFilterCount }}</span>
          </button>
          <div class="mobile-results-info">
            <span v-if="selectedModel">{{ filteredCars.length }} variants</span>
            <span v-else>Select a make & model</span>
          </div>
        </div>

        <!-- Top Bar -->
        <div v-if="selectedModel && !isMobile" class="top-bar">
          <div>
            <h2>Available Variants</h2>
            <p class="results-count">
              {{ filteredCars.length }} variants found
            </p>
          </div>
        </div>

        <!-- Cars Grid -->
        <div v-if="selectedModel && filteredCars.length" class="cars-grid">
          <div
            v-for="carGroup in paginatedCars"
            :key="carGroup.trim"
            @click="selectCar(carGroup)"
            :class="['car-card', { active: selectedCar?.trim === carGroup.trim }]"
          >
            <div class="car-image-wrapper">
              <img :src="getImageUrl(carGroup.image_url)" :alt="carGroup.trim" class="car-image" loading="lazy" @error="handleImageError" />
            </div>
            <div class="car-variants-badge">
              {{ carGroup.variants.length }} variant{{ carGroup.variants.length !== 1 ? 's' : '' }}
            </div>

            <div class="car-card-content">
              <h3>{{ carGroup.trim }}</h3>
              <p class="car-specs">
                {{ getYearRange(carGroup) }} • {{ carGroup.totalEngines }} engine{{ carGroup.totalEngines !== 1 ? 's' : '' }}
              </p>
            </div>
            <div class="car-footer">
              <div class="car-power">
                <span class="power-icon">⚡</span>
                {{ carGroup.maxPower }} HP
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="pagination-section">
          <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1" class="pagination-btn">
            Previous
          </button>
          <div class="pagination-info">
            <span class="current-page">{{ currentPage }}</span>
            <span class="total-pages">{{ totalPages }}</span>
          </div>
          <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages" class="pagination-btn">
            Next
          </button>
        </div>

        <!-- Empty States -->
        <div v-else-if="selectedModel && !filteredCars.length" class="empty-state">
          <div class="empty-state-icon">🔍</div>
          <h3>No variants match your filters</h3>
          <p>Try adjusting year range or power requirements</p>
          <button @click="resetFilters" class="reset-button">Reset All Filters</button>
        </div>
        <div v-else-if="!selectedModel" class="empty-state">
          <div class="empty-state-icon">🚗</div>
          <h3>Select a make and model to browse</h3>
          <p>Choose from our extensive collection of car models</p>
        </div>
      </main>

      <!-- Car Details Sidebar -->
      <aside
        v-if="carDetails"
        :class="['details-sidebar', { 'slide-in': carDetails, 'mobile-details': isMobile }]"
      >
        <div class="details-inner">
          <!-- Close button -->
          <button @click="closeCar" class="close-btn">✕</button>

          <div class="details-content">
            <div class="details-image-container">
              <img :src="getImageUrl(carDetails.image_url)" :alt="carDetails.trim" class="details-img" @error="handleImageError" />
            </div>

            <div class="details-title">
              <h2>{{ carDetails.trim }}</h2>
              <p class="details-subtitle">{{ selectedModelName }} • {{ selectedMakeName }}</p>
            </div>

            <!-- Variant Selector -->
            <div class="variant-selector-section">
              <h3>Select Variant</h3>
              <div class="variant-scroll">
                <button
                  v-for="(variant, idx) in carDetails.variants"
                  :key="variant.id"
                  @click="selectVariant(idx)"
                  :class="['variant-option', { active: selectedVariantIndex === idx }]"
                >
                  <div class="variant-year">{{ variant.year }}</div>
                  <div class="variant-info">
                    <span>{{ variant.body_style }}</span>
                    <span>{{ variant.drive_type }}</span>
                  </div>
                </button>
              </div>
            </div>

            <!-- Selected Variant Details -->
            <div v-if="selectedVariant" class="details-specs">
              <h3>Specifications</h3>
              <div class="specs-grid">
                <div class="spec-item">
                  <span class="spec-label">Year</span>
                  <span class="spec-value">{{ selectedVariant.year }}</span>
                </div>
                <div class="spec-item">
                  <span class="spec-label">Body Type</span>
                  <span class="spec-value">{{ selectedVariant.body_style }}</span>
                </div>
                <div class="spec-item">
                  <span class="spec-label">Drive Type</span>
                  <span class="spec-value">{{ selectedVariant.drive_type }}</span>
                </div>
                <div class="spec-item">
                  <span class="spec-label">Weight</span>
                  <span class="spec-value">{{ formatWeight(selectedVariant.weight_kg) }}</span>
                </div>
              </div>
            </div>

            <!-- Engine Selector -->
            <div v-if="selectedVariant && selectedVariant.engines && selectedVariant.engines.length" class="engine-selector-section">
              <h3>Select Engine</h3>
              <div class="engine-scroll">
                <button
                  v-for="(engine, idx) in selectedVariant.engines"
                  :key="engine.id"
                  @click="selectEngine(idx)"
                  :class="['engine-option', { active: selectedEngineIndex === idx }]"
                >
                  <div class="engine-code">{{ engine.code }}</div>
                  <div class="engine-specs">
                    <span>{{ engine.pivot.power_hp }} HP</span>
                    <span>{{ engine.pivot.torque_nm }} Nm</span>
                  </div>
                  <div class="engine-info">
                    <span>{{ engine.fuel_type }}</span>
                    <span>{{ engine.cylinder }} cyl</span>
                  </div>
                </button>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="details-actions">
              <button class="btn-primary" @click="saveToGarage" :disabled="saving">
                {{ saving ? 'Saving...' : 'Save Build' }}
              </button>
              <!-- UPDATED: Changed to goToModIt -->
              <button class="btn-secondary" @click="goToModIt" :disabled="!selectedEngine">
                🔧 Mod It
              </button>
              <button class="btn-secondary" @click="shareBuild">Share</button>
            </div>
          </div>
        </div>
      </aside>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import ThemeToggle from '../components/ThemeToggle.vue'

export default {
  components: { ThemeToggle },

  data() {
    return {
      makes: [],
      models: [],
      cars: [],
      selectedMake: '',
      selectedModel: '',
      selectedCar: null,
      carDetails: null,
      selectedVariantIndex: 0,
      selectedEngineIndex: 0,
      currentPage: 1,
      itemsPerPage: 12,
      filterDrawerOpen: false,
      isMobile: false,
      loading: false,
      loadingModels: false,
      saving: false,
      sortBy: 'year_desc',

      filters: {
        yearMin: 2000,
        yearMax: 2025,
        minPower: 0,
      },

      user: {
        builds: 0,
      },

      toast: {
        show: false,
        message: '',
        type: 'info',
      },

      debounceFilterTimer: null,
    }
  },

  computed: {
    selectedMakeName() {
      const make = this.makes.find(m => m.id === this.selectedMake)
      return make ? make.name : ''
    },

    selectedModelName() {
      const model = this.models.find(m => m.id === this.selectedModel)
      return model ? model.name : ''
    },

    groupedCars() {
      const grouped = {}

      this.cars.forEach((car) => {
        if (!grouped[car.trim]) {
          grouped[car.trim] = {
            trim: car.trim,
            image_url: car.image_url,
            variants: [],
            maxPower: 0,
            totalEngines: 0,
            years: new Set(),
          }
        }

        const maxPower = car.engines?.length ?
          Math.max(...car.engines.map(e => e.pivot.power_hp)) : 0

        grouped[car.trim].variants.push({
          id: car.id,
          year: car.year,
          body_style: car.body_style,
          drive_type: car.drive_type,
          weight_kg: car.weight_kg,
          image_url: car.image_url,
          engines: car.engines || [],
        })

        grouped[car.trim].maxPower = Math.max(grouped[car.trim].maxPower, maxPower)
        grouped[car.trim].totalEngines += car.engines?.length || 0
        grouped[car.trim].years.add(car.year)
      })

      return Object.values(grouped).map(group => ({
        ...group,
        minYear: Math.min(...Array.from(group.years)),
        maxYear: Math.max(...Array.from(group.years)),
      }))
    },

    filteredCars() {
      let filtered = this.groupedCars.filter((group) => {
        const hasYearInRange = group.variants.some(
          v => v.year >= this.filters.yearMin && v.year <= this.filters.yearMax
        )
        if (!hasYearInRange) return false

        if (this.filters.minPower && group.maxPower < this.filters.minPower) return false

        return true
      })

      return this.applySortingLogic(filtered)
    },

    paginatedCars() {
      const start = (this.currentPage - 1) * this.itemsPerPage
      return this.filteredCars.slice(start, start + this.itemsPerPage)
    },

    totalPages() {
      return Math.ceil(this.filteredCars.length / this.itemsPerPage)
    },

    selectedVariant() {
      return this.carDetails?.variants?.[this.selectedVariantIndex]
    },

    selectedEngine() {
      return this.selectedVariant?.engines?.[this.selectedEngineIndex]
    },

    minYear() {
      if (this.groupedCars.length === 0) return 2000
      return Math.min(...this.groupedCars.map(g => g.minYear))
    },

    maxYear() {
      if (this.groupedCars.length === 0) return 2025
      return Math.max(...this.groupedCars.map(g => g.maxYear))
    },

    minPower() {
      if (this.groupedCars.length === 0) return 0
      return Math.min(...this.groupedCars.map(g => g.maxPower))
    },

    maxPower() {
      if (this.groupedCars.length === 0) return 1000
      return Math.max(...this.groupedCars.map(g => g.maxPower))
    },

    hasActiveFilters() {
      return this.filters.yearMin > this.minYear ||
             this.filters.yearMax < this.maxYear ||
             this.filters.minPower > this.minPower ||
             this.sortBy !== 'year_desc'
    },

    activeFilterCount() {
      let count = 0
      if (this.filters.yearMin > this.minYear) count++
      if (this.filters.yearMax < this.maxYear) count++
      if (this.filters.minPower > this.minPower) count++
      if (this.sortBy !== 'year_desc') count++
      return count
    },
  },

  watch: {
    carDetails(val) {
      if (val && this.isMobile) {
        document.body.style.overflow = 'hidden'
      } else if (!val && this.isMobile) {
        document.body.style.overflow = ''
      }
    },

    filterDrawerOpen(val) {
      if (val && this.isMobile) {
        document.body.style.overflow = 'hidden'
      } else if (!val && this.isMobile) {
        document.body.style.overflow = ''
      }
    },
  },

  async mounted() {
    await this.loadMakes()
    this.checkMobile()
    this.loadUserData()

    window.addEventListener('resize', this.checkMobile)
    window.addEventListener('keydown', this.handleKeydown)
  },

  beforeUnmount() {
    window.removeEventListener('resize', this.checkMobile)
    window.removeEventListener('keydown', this.handleKeydown)
    document.body.style.overflow = ''
  },

  methods: {
    async loadMakes() {
      this.loading = true
      try {
        const res = await axios.get('/api/makes')
        this.makes = res.data
        this.showToast('Makes loaded successfully', 'success')
      } catch (error) {
        console.error('Error loading makes:', error)
        this.showToast('Failed to load makes', 'error')
      } finally {
        this.loading = false
      }
    },

    async loadModels() {
      if (!this.selectedMake) return
      this.loadingModels = true
      try {
        const res = await axios.get(`/api/models?make_id=${this.selectedMake}`)
        this.models = Array.isArray(res.data) ? res.data : []
        this.selectedModel = ''
        this.cars = []
        this.selectedCar = null
        this.carDetails = null
      } catch (error) {
        console.error('Error loading models:', error)
        this.showToast('Failed to load models', 'error')
      } finally {
        this.loadingModels = false
      }
    },

    async loadCars() {
      if (!this.selectedModel) return
      this.loading = true
      try {
        const res = await axios.get(`/api/cars?model_id=${this.selectedModel}`)
        this.cars = Array.isArray(res.data) ? res.data : []
        this.selectedCar = null
        this.carDetails = null
        this.currentPage = 1
        this.filterDrawerOpen = false
        this.resetFilters()
        this.showToast(`Loaded ${this.cars.length} cars`, 'success')
      } catch (error) {
        console.error('Error loading cars:', error)
        this.showToast('Failed to load cars', 'error')
      } finally {
        this.loading = false
      }
    },

    applySortingLogic(cars) {
      const sorted = [...cars]
      switch (this.sortBy) {
        case 'year_desc':
          return sorted.sort((a, b) => b.maxYear - a.maxYear)
        case 'year_asc':
          return sorted.sort((a, b) => a.minYear - b.minYear)
        case 'power_desc':
          return sorted.sort((a, b) => b.maxPower - a.maxPower)
        case 'power_asc':
          return sorted.sort((a, b) => a.maxPower - b.maxPower)
        case 'name_asc':
          return sorted.sort((a, b) => a.trim.localeCompare(b.trim))
        case 'name_desc':
          return sorted.sort((a, b) => b.trim.localeCompare(a.trim))
        default:
          return sorted
      }
    },

    applySorting() {
      this.currentPage = 1
    },

    resetFilters() {
      this.filters = {
        yearMin: this.minYear,
        yearMax: this.maxYear,
        minPower: this.minPower,
      }
      this.sortBy = 'year_desc'
      this.currentPage = 1
    },

    debouncedApplyFilters() {
      clearTimeout(this.debounceFilterTimer)
      this.debounceFilterTimer = setTimeout(() => {
        this.currentPage = 1
      }, 300)
    },

    selectCar(carGroup) {
      this.selectedCar = carGroup
      this.selectedVariantIndex = 0
      this.selectedEngineIndex = 0

      this.carDetails = {
        trim: carGroup.trim,
        image_url: carGroup.image_url,
        variants: carGroup.variants,
      }
    },

    selectVariant(idx) {
      this.selectedVariantIndex = idx
      this.selectedEngineIndex = 0
    },

    selectEngine(idx) {
      this.selectedEngineIndex = idx
    },

    closeCar() {
      this.selectedCar = null
      this.carDetails = null
      this.selectedVariantIndex = 0
      this.selectedEngineIndex = 0

      if (this.isMobile) {
        document.body.style.overflow = ''
      }
    },

    async saveToGarage() {
      if (!this.selectedVariant || !this.carDetails) {
        this.showToast('Please select a car variant first', 'warning')
        return
      }

      this.saving = true
      try {
        await new Promise(resolve => setTimeout(resolve, 1000))

        const build = {
          id: `BUILD-${Date.now()}`,
          car: this.carDetails.trim,
          variant: this.selectedVariant,
          savedAt: new Date().toISOString(),
        }

        const garage = JSON.parse(localStorage.getItem('garage') || '[]')
        garage.push(build)
        localStorage.setItem('garage', JSON.stringify(garage))

        this.user.builds++
        localStorage.setItem('user', JSON.stringify(this.user))
        this.showToast('Build saved!', 'success')
        this.closeCar()
      } catch (error) {
        this.showToast('Failed to save build', 'error')
      } finally {
        this.saving = false
      }
    },

    // UPDATED METHOD - Navigate to parts with car and engine
goToModIt() {
  if (!this.carDetails || !this.selectedVariant || !this.selectedEngine) {
    this.showToast('Please select a car variant and engine first', 'warning')
    return
  }

  const carId = this.selectedVariant.id
  const engineId = this.selectedEngine.id

  // ✅ Use Vue Router to go to /parts/:carId/:engineId
  this.$router.push({
    name: 'PartSelector',
    params: { carId, engineId }
  })
},

    checkMobile() {
      this.isMobile = window.innerWidth < 1024
    },

    toggleFilterDrawer() {
      this.filterDrawerOpen = !this.filterDrawerOpen
    },

    goToPage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page
      }
    },

    getImageUrl(url) {
      return url || 'https://via.placeholder.com/300x200?text=No+Image'
    },

    handleImageError(event) {
      event.target.src = 'https://via.placeholder.com/300x200?text=Error'
    },

    getYearRange(carGroup) {
      return carGroup.variants.length > 0
        ? `${carGroup.minYear}-${carGroup.maxYear}`
        : 'N/A'
    },

    formatWeight(kg) {
      return kg ? `${kg} kg` : 'N/A'
    },

    shareBuild() {
      if (!this.carDetails) return
      const url = `${window.location.origin}/builder?make=${this.selectedMake}&model=${this.selectedModel}`
      navigator.clipboard.writeText(url)
      this.showToast('Build link copied!', 'success')
    },

    loadUserData() {
      const data = localStorage.getItem('user')
      if (data) {
        this.user = JSON.parse(data)
      }
    },

    handleKeydown(e) {
      if (e.key === 'Escape' && this.carDetails) {
        this.closeCar()
      }
      if (e.key === 'Escape' && this.filterDrawerOpen && this.isMobile) {
        this.filterDrawerOpen = false
      }
    },

    showToast(message, type = 'info') {
      this.toast = { show: true, message, type }
      setTimeout(() => {
        this.toast.show = false
      }, 3000)
    },
  },
}
</script>

<style scoped>
/* Base Styles */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.builder-container {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  background: white;
  color: #1e293b;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  transition: background-color 0.3s ease, color 0.3s ease;
  max-width: 100vw;
  width: 100%;
  overflow-x: hidden;
}

html[data-color-scheme='dark'] .builder-container,
:global([data-color-scheme='dark']) .builder-container {
  background: #0a0a0a;
  color: #f5f5f5;
}

/* Loading Overlay */
.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid rgba(16, 185, 129, 0.2);
  border-top-color: #10b981;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin-bottom: 12px;
}

html[data-color-scheme='dark'] .loading-spinner,
:global([data-color-scheme='dark']) .loading-spinner {
  border-top-color: #ffd700;
  border-color: rgba(255, 215, 0, 0.2);
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.loading-overlay p {
  color: white;
  font-size: 1rem;
}

/* Header */
.builder-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 2rem;
  background: white;
  border-bottom: 1px solid #e2e8f0;
  position: sticky;
  top: 0;
  z-index: 100;
  transition: background 0.3s ease, border-color 0.3s ease;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

html[data-color-scheme='dark'] .builder-header,
:global([data-color-scheme='dark']) .builder-header {
  background: #1a1a1a;
  border-bottom-color: #2d2d2d;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.header-left h1 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
}

html[data-color-scheme='dark'] .header-left h1,
:global([data-color-scheme='dark']) .header-left h1 {
  color: #ffd700;
}

.header-right {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.user-info {
  font-size: 0.95rem;
  color: #64748b;
  white-space: nowrap;
}

html[data-color-scheme='dark'] .user-info,
:global([data-color-scheme='dark']) .user-info {
  color: #a0aec0;
}

.garage-btn {
  padding: 0.6rem 1.5rem;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  font-size: 0.9rem;
}

html[data-color-scheme='dark'] .garage-btn,
:global([data-color-scheme='dark']) .garage-btn {
  background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
  color: #000;
}

.garage-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(16, 185, 129, 0.3);
}

html[data-color-scheme='dark'] .garage-btn:hover,
:global([data-color-scheme='dark']) .garage-btn:hover {
  box-shadow: 0 6px 16px rgba(255, 215, 0, 0.3);
}

/* Toast */
.toast {
  position: fixed;
  top: 100px;
  right: 20px;
  background: white;
  border-left: 4px solid #10b981;
  padding: 16px 20px;
  border-radius: 6px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
  z-index: 1000;
  animation: slideIn 0.3s ease-out;
  display: flex;
  align-items: center;
  gap: 12px;
}

html[data-color-scheme='dark'] .toast,
:global([data-color-scheme='dark']) .toast {
  background: #1a1a1a;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
}

.toast.success {
  border-left-color: #10b981;
}

.toast.error {
  border-left-color: #dc2626;
}

.toast.warning {
  border-left-color: #f59e0b;
}

.toast-close {
  background: none;
  border: none;
  color: #94a3b8;
  cursor: pointer;
  font-size: 18px;
  margin-left: auto;
}

@keyframes slideIn {
  from {
    transform: translateX(400px);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

/* Main Wrapper */
.main-wrapper {
  display: grid;
  grid-template-columns: 280px 1fr;
  gap: 0;
  flex: 1;
  overflow: hidden;
}

.main-wrapper.has-details {
  grid-template-columns: 280px 1fr 500px;
}

/* Sidebar */
.sidebar {
  background: white;
  border-right: 1px solid #e2e8f0;
  padding: 20px;
  overflow-y: auto;
  max-height: calc(100vh - 80px);
  transition: all 0.3s ease;
}

html[data-color-scheme='dark'] .sidebar,
:global([data-color-scheme='dark']) .sidebar {
  background: #1a1a1a;
  border-right-color: #2d2d2d;
}

.sidebar-inner {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.sidebar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.sidebar-header h2 {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 600;
}

.sidebar h2 {
  margin: 0 0 15px 0;
  font-size: 1.1rem;
  font-weight: 600;
  color: #1e293b;
}

html[data-color-scheme='dark'] .sidebar h2,
:global([data-color-scheme='dark']) .sidebar h2 {
  color: #f5f5f5;
}

.close-drawer-btn {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #64748b;
}

html[data-color-scheme='dark'] .close-drawer-btn,
:global([data-color-scheme='dark']) .close-drawer-btn {
  color: #a0aec0;
}

.filter-section {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.filter-section h3 {
  margin: 0;
  font-size: 0.85rem;
  font-weight: 600;
  color: #1e293b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

html[data-color-scheme='dark'] .filter-section h3,
:global([data-color-scheme='dark']) .filter-section h3 {
  color: #e2e8f0;
}

.filter-select {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  color: #1e293b;
  padding: 10px 12px;
  border-radius: 6px;
  font-size: 0.9rem;
  transition: all 0.3s ease;
}

html[data-color-scheme='dark'] .filter-select,
:global([data-color-scheme='dark']) .filter-select {
  background: #2d2d2d;
  border-color: #404040;
  color: #f5f5f5;
}

.filter-select:focus {
  outline: none;
  border-color: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

html[data-color-scheme='dark'] .filter-select:focus,
:global([data-color-scheme='dark']) .filter-select:focus {
  border-color: #ffd700;
  box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.15);
}

.year-range-display {
  text-align: center;
  font-weight: 600;
  color: #10b981;
  padding: 8px;
}

html[data-color-scheme='dark'] .year-range-display,
:global([data-color-scheme='dark']) .year-range-display {
  color: #ffd700;
}

.year-slider-container {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.slider-label {
  font-size: 0.8rem;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

html[data-color-scheme='dark'] .slider-label,
:global([data-color-scheme='dark']) .slider-label {
  color: #a0aec0;
}

.slider-note {
  font-size: 0.75rem;
  color: #94a3b8;
  text-align: center;
  margin-top: 4px;
}

html[data-color-scheme='dark'] .slider-note,
:global([data-color-scheme='dark']) .slider-note {
  color: #64748b;
}

.year-range-slider,
.power-range-slider {
  width: 100%;
  height: 6px;
  border-radius: 3px;
  background: linear-gradient(to right, #10b981, #059669);
  outline: none;
  -webkit-appearance: none;
  appearance: none;
  cursor: pointer;
}

html[data-color-scheme='dark'] .year-range-slider,
html[data-color-scheme='dark'] .power-range-slider,
:global([data-color-scheme='dark']) .year-range-slider,
:global([data-color-scheme='dark']) .power-range-slider {
  background: linear-gradient(to right, #ffd700, #ffed4e);
}

.year-range-slider::-webkit-slider-thumb,
.power-range-slider::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: #10b981;
  cursor: pointer;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}

html[data-color-scheme='dark'] .year-range-slider::-webkit-slider-thumb,
html[data-color-scheme='dark'] .power-range-slider::-webkit-slider-thumb,
:global([data-color-scheme='dark']) .year-range-slider::-webkit-slider-thumb,
:global([data-color-scheme='dark']) .power-range-slider::-webkit-slider-thumb {
  background: #ffd700;
}

.slider-display {
  text-align: center;
  font-weight: 600;
  color: #10b981;
  font-size: 0.9rem;
}

html[data-color-scheme='dark'] .slider-display,
:global([data-color-scheme='dark']) .slider-display {
  color: #ffd700;
}

.power-slider-container {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.filter-divider {
  height: 1px;
  background: #e2e8f0;
  margin: 10px 0;
}

html[data-color-scheme='dark'] .filter-divider,
:global([data-color-scheme='dark']) .filter-divider {
  background: #2d2d2d;
}

.reset-button {
  padding: 12px;
  border: none;
  border-radius: 6px;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  white-space: nowrap;
  background: #f1f5f9;
  color: #1e293b;
  border: 1px solid #e2e8f0;
}

html[data-color-scheme='dark'] .reset-button,
:global([data-color-scheme='dark']) .reset-button {
  background: #2d2d2d;
  color: #e2e8f0;
  border-color: #404040;
}

.reset-button:hover:not(:disabled) {
  background: #e2e8f0;
  transform: translateY(-2px);
}

html[data-color-scheme='dark'] .reset-button:hover:not(:disabled),
:global([data-color-scheme='dark']) .reset-button:hover:not(:disabled) {
  background: #404040;
}

.reset-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Content */
.content {
  padding: 30px;
  overflow-y: auto;
  max-height: calc(100vh - 80px);
  background: white;
}

html[data-color-scheme='dark'] .content,
:global([data-color-scheme='dark']) .content {
  background: #0a0a0a;
}

.top-bar {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 30px;
  gap: 20px;
}

.top-bar h2 {
  margin: 0;
  font-size: 1.8rem;
  font-weight: 700;
  color: #1e293b;
}

html[data-color-scheme='dark'] .top-bar h2,
:global([data-color-scheme='dark']) .top-bar h2 {
  color: #f5f5f5;
}

.results-count {
  margin: 8px 0 0 0;
  color: #64748b;
  font-size: 0.9rem;
}

html[data-color-scheme='dark'] .results-count,
:global([data-color-scheme='dark']) .results-count {
  color: #a0aec0;
}

.active-filters-info {
  color: #10b981;
  font-weight: 600;
}

html[data-color-scheme='dark'] .active-filters-info,
:global([data-color-scheme='dark']) .active-filters-info {
  color: #ffd700;
}

.top-bar-controls {
  display: flex;
  gap: 12px;
}

.page-select {
  padding: 8px 12px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  color: #1e293b;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.9rem;
}

html[data-color-scheme='dark'] .page-select,
:global([data-color-scheme='dark']) .page-select {
  background: #2d2d2d;
  border-color: #404040;
  color: #f5f5f5;
}

/* Cars Grid */
.cars-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
  margin-bottom: 40px;
  animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.car-card {
  background: white;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
}

html[data-color-scheme='dark'] .car-card,
:global([data-color-scheme='dark']) .car-card {
  background: #1a1a1a;
  border-color: #2d2d2d;
}

.car-card:hover {
  transform: translateY(-6px);
  border-color: #10b981;
  box-shadow: 0 12px 24px rgba(16, 185, 129, 0.2);
}

html[data-color-scheme='dark'] .car-card:hover,
:global([data-color-scheme='dark']) .car-card:hover {
  border-color: #ffd700;
  box-shadow: 0 12px 24px rgba(255, 215, 0, 0.2);
}

.car-card.active {
  border-color: #10b981;
  box-shadow: 0 0 20px rgba(16, 185, 129, 0.3);
  background: #f0fdf4;
}

html[data-color-scheme='dark'] .car-card.active,
:global([data-color-scheme='dark']) .car-card.active {
  border-color: #ffd700;
  box-shadow: 0 0 20px rgba(255, 215, 0, 0.3);
  background: #1a1a1a;
}

.car-image-wrapper {
  position: relative;
  overflow: hidden;
  background: #f8fafc;
  aspect-ratio: 16 / 10;
}

html[data-color-scheme='dark'] .car-image-wrapper,
:global([data-color-scheme='dark']) .car-image-wrapper {
  background: #2d2d2d;
}

.car-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.car-card:hover .car-image {
  transform: scale(1.08);
}

.car-variants-badge,
.saved-badge {
  position: absolute;
  top: 10px;
  padding: 6px 10px;
  background: rgba(0, 0, 0, 0.7);
  color: white;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
}

.car-variants-badge {
  left: 10px;
}

.saved-badge {
  right: 10px;
  background: rgba(16, 185, 129, 0.8);
  font-size: 16px;
}

.car-card-content {
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.car-card-content h3 {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
  color: #1e293b;
}

html[data-color-scheme='dark'] .car-card-content h3,
:global([data-color-scheme='dark']) .car-card-content h3 {
  color: #f5f5f5;
}

.car-specs {
  margin: 0;
  font-size: 0.85rem;
  color: #64748b;
}

html[data-color-scheme='dark'] .car-specs,
:global([data-color-scheme='dark']) .car-specs {
  color: #a0aec0;
}

.car-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 8px;
}

.car-power {
  display: flex;
  align-items: center;
  gap: 6px;
  font-weight: 600;
  color: #10b981;
  font-size: 0.9rem;
}

html[data-color-scheme='dark'] .car-power,
:global([data-color-scheme='dark']) .car-power {
  color: #ffd700;
}

.power-icon {
  font-size: 16px;
}

.car-actions {
  display: flex;
  gap: 8px;
}

.quick-save-btn,
.share-btn {
  background: none;
  border: none;
  font-size: 18px;
  cursor: pointer;
  transition: transform 0.2s ease;
  padding: 4px 8px;
}

.quick-save-btn:hover,
.share-btn:hover {
  transform: scale(1.2);
}

/* Pagination */
.pagination-section {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 12px;
  padding: 30px 0;
  margin-top: 40px;
  border-top: 1px solid #e2e8f0;
  flex-wrap: wrap;
}

html[data-color-scheme='dark'] .pagination-section,
:global([data-color-scheme='dark']) .pagination-section {
  border-top-color: #2d2d2d;
}

.pagination-btn {
  padding: 10px 16px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  color: #1e293b;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-weight: 600;
  font-size: 0.9rem;
}

html[data-color-scheme='dark'] .pagination-btn,
:global([data-color-scheme='dark']) .pagination-btn {
  background: #2d2d2d;
  border-color: #404040;
  color: #f5f5f5;
}

.pagination-btn:hover:not(:disabled) {
  background: #10b981;
  color: white;
  transform: translateY(-2px);
}

html[data-color-scheme='dark'] .pagination-btn:hover:not(:disabled),
:global([data-color-scheme='dark']) .pagination-btn:hover:not(:disabled) {
  background: #ffd700;
  color: #000;
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.pagination-info {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.9rem;
  color: #64748b;
}

html[data-color-scheme='dark'] .pagination-info,
:global([data-color-scheme='dark']) .pagination-info {
  color: #a0aec0;
}

.current-page {
  font-weight: 600;
  color: #10b981;
}

html[data-color-scheme='dark'] .current-page,
:global([data-color-scheme='dark']) .current-page {
  color: #ffd700;
}

/* Empty States */
.empty-state {
  text-align: center;
  padding: 80px 40px;
  color: #64748b;
}

html[data-color-scheme='dark'] .empty-state,
:global([data-color-scheme='dark']) .empty-state {
  color: #a0aec0;
}

.empty-state-icon {
  font-size: 64px;
  margin-bottom: 20px;
}

.empty-state h3 {
  font-size: 1.5rem;
  margin: 0 0 10px 0;
  color: #1e293b;
}

html[data-color-scheme='dark'] .empty-state h3,
:global([data-color-scheme='dark']) .empty-state h3 {
  color: #f5f5f5;
}

/* Quick Stats */
.quick-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin-top: 40px;
  padding-top: 30px;
  border-top: 1px solid #e2e8f0;
}

html[data-color-scheme='dark'] .quick-stats,
:global([data-color-scheme='dark']) .quick-stats {
  border-top-color: #2d2d2d;
}

.stat-item {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  padding: 16px;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

html[data-color-scheme='dark'] .stat-item,
:global([data-color-scheme='dark']) .stat-item {
  background: #1a1a1a;
  border-color: #2d2d2d;
}

.stat-label {
  font-size: 0.8rem;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

html[data-color-scheme='dark'] .stat-label,
:global([data-color-scheme='dark']) .stat-label {
  color: #a0aec0;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #10b981;
}

html[data-color-scheme='dark'] .stat-value,
:global([data-color-scheme='dark']) .stat-value {
  color: #ffd700;
}

/* Details Sidebar */
.details-sidebar {
  background: white;
  border-left: 1px solid #e2e8f0;
  max-height: calc(100vh - 80px);
  overflow-y: auto;
  animation: slideInRight 0.3s ease-out;
}

html[data-color-scheme='dark'] .details-sidebar,
:global([data-color-scheme='dark']) .details-sidebar {
  background: #1a1a1a;
  border-left-color: #2d2d2d;
}

@keyframes slideInRight {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

.details-inner {
  padding: 20px;
  position: relative;
}

.close-btn {
  position: absolute;
  top: 16px;
  right: 16px;
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #64748b;
  transition: color 0.3s ease;
  z-index: 10;
}

html[data-color-scheme='dark'] .close-btn,
:global([data-color-scheme='dark']) .close-btn {
  color: #a0aec0;
}

.close-btn:hover {
  color: #10b981;
}

html[data-color-scheme='dark'] .close-btn:hover,
:global([data-color-scheme='dark']) .close-btn:hover {
  color: #ffd700;
}

.mobile-details-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 16px;
  padding-bottom: 16px;
  border-bottom: 1px solid #e2e8f0;
}

html[data-color-scheme='dark'] .mobile-details-header,
:global([data-color-scheme='dark']) .mobile-details-header {
  border-bottom-color: #2d2d2d;
}

.mobile-details-title {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 600;
  flex: 1;
  color: #1e293b;
}

html[data-color-scheme='dark'] .mobile-details-title,
:global([data-color-scheme='dark']) .mobile-details-title {
  color: #f5f5f5;
}

.details-content {
  display: flex;
  flex-direction: column;
  gap: 20px;
  padding-top: 30px;
}

.details-image-container {
  position: relative;
  border-radius: 12px;
  overflow: hidden;
}

.details-img {
  width: 100%;
  height: 240px;
  object-fit: cover;
  display: block;
}

.details-title {
  margin-bottom: 20px;
}

.details-title h2 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
}

html[data-color-scheme='dark'] .details-title h2,
:global([data-color-scheme='dark']) .details-title h2 {
  color: #f5f5f5;
}

.details-subtitle {
  margin: 8px 0 0 0;
  color: #64748b;
  font-size: 0.9rem;
}

html[data-color-scheme='dark'] .details-subtitle,
:global([data-color-scheme='dark']) .details-subtitle {
  color: #a0aec0;
}

.variant-selector-section,
.engine-selector-section {
  margin-bottom: 24px;
}

.variant-selector-section h3,
.engine-selector-section h3 {
  margin: 0 0 12px 0;
  font-size: 0.85rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #1e293b;
}

html[data-color-scheme='dark'] .variant-selector-section h3,
html[data-color-scheme='dark'] .engine-selector-section h3,
:global([data-color-scheme='dark']) .variant-selector-section h3,
:global([data-color-scheme='dark']) .engine-selector-section h3 {
  color: #e2e8f0;
}

.variant-scroll,
.engines-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
  max-height: 300px;
  overflow-y: auto;
}

.variant-option,
.engine-option {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  padding: 12px;
  border-radius: 8px;
  text-align: left;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

html[data-color-scheme='dark'] .variant-option,
html[data-color-scheme='dark'] .engine-option,
:global([data-color-scheme='dark']) .variant-option,
:global([data-color-scheme='dark']) .engine-option {
  background: #2d2d2d;
  border-color: #404040;
}

.variant-option:hover,
.engine-option:hover {
  border-color: #10b981;
  background: white;
}

html[data-color-scheme='dark'] .variant-option:hover,
html[data-color-scheme='dark'] .engine-option:hover,
:global([data-color-scheme='dark']) .variant-option:hover,
:global([data-color-scheme='dark']) .engine-option:hover {
  border-color: #ffd700;
  background: #1a1a1a;
}

.variant-option.active,
.engine-option.active {
  border-color: #10b981;
  background: #f0fdf4;
  box-shadow: 0 0 12px rgba(16, 185, 129, 0.2);
}

html[data-color-scheme='dark'] .variant-option.active,
html[data-color-scheme='dark'] .engine-option.active,
:global([data-color-scheme='dark']) .variant-option.active,
:global([data-color-scheme='dark']) .engine-option.active {
  border-color: #ffd700;
  background: #1a1a1a;
  box-shadow: 0 0 12px rgba(255, 215, 0, 0.2);
}

.variant-year {
  font-weight: 600;
  color: #10b981;
  font-size: 0.95rem;
}

html[data-color-scheme='dark'] .variant-year,
:global([data-color-scheme='dark']) .variant-year {
  color: #ffd700;
}

.variant-info {
  display: flex;
  gap: 12px;
  font-size: 0.8rem;
  color: #64748b;
}

html[data-color-scheme='dark'] .variant-info,
:global([data-color-scheme='dark']) .variant-info {
  color: #a0aec0;
}

.engine-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 8px;
}

.engine-code {
  font-weight: 600;
  color: #1e293b;
  font-size: 0.95rem;
}

html[data-color-scheme='dark'] .engine-code,
:global([data-color-scheme='dark']) .engine-code {
  color: #f5f5f5;
}

.engine-power {
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
  padding: 2px 6px;
  border-radius: 4px;
  font-weight: 600;
  font-size: 0.8rem;
}

html[data-color-scheme='dark'] .engine-power,
:global([data-color-scheme='dark']) .engine-power {
  background: rgba(255, 215, 0, 0.1);
  color: #ffd700;
}

.engine-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
  font-size: 0.8rem;
  color: #64748b;
}

html[data-color-scheme='dark'] .engine-info,
:global([data-color-scheme='dark']) .engine-info {
  color: #a0aec0;
}

.details-specs {
  background: #f8fafc;
  padding: 16px;
  border-radius: 8px;
  margin-bottom: 16px;
}

html[data-color-scheme='dark'] .details-specs,
:global([data-color-scheme='dark']) .details-specs {
  background: #2d2d2d;
}

.details-specs h3 {
  margin: 0 0 12px 0;
  font-size: 0.9rem;
  font-weight: 600;
  color: #1e293b;
}

html[data-color-scheme='dark'] .details-specs h3,
:global([data-color-scheme='dark']) .details-specs h3 {
  color: #f5f5f5;
}

.specs-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.spec-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.spec-label {
  font-size: 0.75rem;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

html[data-color-scheme='dark'] .spec-label,
:global([data-color-scheme='dark']) .spec-label {
  color: #a0aec0;
}

.spec-value {
  font-weight: 600;
  color: #1e293b;
  font-size: 0.95rem;
}

html[data-color-scheme='dark'] .spec-value,
:global([data-color-scheme='dark']) .spec-value {
  color: #f5f5f5;
}

.selected-engine-details {
  background: rgba(16, 185, 129, 0.08);
  border: 1px solid rgba(16, 185, 129, 0.3);
  padding: 16px;
  border-radius: 8px;
  margin-bottom: 20px;
}

html[data-color-scheme='dark'] .selected-engine-details,
:global([data-color-scheme='dark']) .selected-engine-details {
  background: rgba(255, 215, 0, 0.08);
  border-color: rgba(255, 215, 0, 0.3);
}

.selected-engine-details h3 {
  margin: 0 0 12px 0;
  font-size: 0.9rem;
  font-weight: 600;
  color: #1e293b;
}

html[data-color-scheme='dark'] .selected-engine-details h3,
:global([data-color-scheme='dark']) .selected-engine-details h3 {
  color: #f5f5f5;
}

.build-summary {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.build-spec {
  margin: 0;
  font-size: 0.9rem;
  color: #64748b;
}

html[data-color-scheme='dark'] .build-spec,
:global([data-color-scheme='dark']) .build-spec {
  color: #a0aec0;
}

.engine-highlight {
  margin: 0;
  font-weight: 600;
  color: #10b981;
  font-size: 0.95rem;
}

html[data-color-scheme='dark'] .engine-highlight,
:global([data-color-scheme='dark']) .engine-highlight {
  color: #ffd700;
}

.build-info {
  margin: 0;
  font-size: 0.85rem;
  color: #64748b;
}

html[data-color-scheme='dark'] .build-info,
:global([data-color-scheme='dark']) .build-info {
  color: #a0aec0;
}

.details-actions {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-bottom: 20px;
}

.btn-primary,
.btn-secondary {
  padding: 12px;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 0.95rem;
}

.btn-primary {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
}

html[data-color-scheme='dark'] .btn-primary,
:global([data-color-scheme='dark']) .btn-primary {
  background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
  color: #000;
  box-shadow: 0 4px 12px rgba(255, 215, 0, 0.25);
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(16, 185, 129, 0.35);
}

html[data-color-scheme='dark'] .btn-primary:hover:not(:disabled),
:global([data-color-scheme='dark']) .btn-primary:hover:not(:disabled) {
  box-shadow: 0 6px 16px rgba(255, 215, 0, 0.35);
}

.btn-secondary {
  background: rgba(30, 64, 175, 0.08);
  color: #1e40af;
  border: 1.5px solid rgba(30, 64, 175, 0.3);
}

html[data-color-scheme='dark'] .btn-secondary,
:global([data-color-scheme='dark']) .btn-secondary {
  background: transparent;
  color: #ffd700;
  border-color: rgba(255, 215, 0, 0.5);
}

.btn-secondary:hover:not(:disabled) {
  background: rgba(30, 64, 175, 0.15);
  border-color: rgba(30, 64, 175, 0.5);
}

html[data-color-scheme='dark'] .btn-secondary:hover:not(:disabled),
:global([data-color-scheme='dark']) .btn-secondary:hover:not(:disabled) {
  background: rgba(255, 215, 0, 0.1);
  border-color: rgba(255, 215, 0, 0.8);
  color: #ffed4e;
}

.btn-primary:disabled,
.btn-secondary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Mobile Responsive */
@media (max-width: 1023px) {
  .main-wrapper {
    grid-template-columns: 1fr;
  }

  .main-wrapper.has-details {
    grid-template-columns: 1fr;
  }

  .sidebar {
    position: fixed;
    left: -100%;
    top: 80px;
    width: 100%;
    max-width: 280px;
    height: calc(100vh - 80px);
    z-index: 200;
    border-right: none;
    border-bottom: 1px solid #e2e8f0;
    transition: left 0.3s ease;
  }

  html[data-color-scheme='dark'] .sidebar,
  :global([data-color-scheme='dark']) .sidebar {
    border-bottom-color: #2d2d2d;
  }

  .sidebar.mobile-drawer.drawer-open {
    left: 0;
  }

  .sidebar-overlay {
    position: fixed;
    top: 80px;
    left: 0;
    width: 100%;
    height: calc(100vh - 80px);
    background: rgba(0, 0, 0, 0.5);
    z-index: 150;
  }

  .content {
    padding: 20px;
  }

  .cars-grid {
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 12px;
  }

  .mobile-filter-header {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
    align-items: center;
  }

  .mobile-filter-btn {
    flex: 1;
    padding: 10px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    color: #1e293b;
    font-weight: 600;
    cursor: pointer;
    position: relative;
    transition: all 0.3s ease;
  }

  html[data-color-scheme='dark'] .mobile-filter-btn,
  :global([data-color-scheme='dark']) .mobile-filter-btn {
    background: #2d2d2d;
    border-color: #404040;
    color: #f5f5f5;
  }

  .mobile-filter-btn:hover {
    border-color: #10b981;
  }

  html[data-color-scheme='dark'] .mobile-filter-btn:hover,
  :global([data-color-scheme='dark']) .mobile-filter-btn:hover {
    border-color: #ffd700;
  }

  .filter-badge {
    position: absolute;
    top: -6px;
    right: -6px;
    background: #dc2626;
    color: white;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
  }

  .mobile-results-info {
    display: flex;
    gap: 8px;
    align-items: center;
    font-size: 0.9rem;
  }

  .details-sidebar {
    position: fixed;
    right: -100%;
    top: 80px;
    width: 100%;
    max-width: 100%;
    height: calc(100vh - 80px);
    z-index: 200;
    border-left: none;
    border-top: 1px solid #e2e8f0;
    transition: right 0.3s ease;
  }

  html[data-color-scheme='dark'] .details-sidebar,
  :global([data-color-scheme='dark']) .details-sidebar {
    border-top-color: #2d2d2d;
  }

  .details-sidebar.mobile-details.details-open {
    right: 0;
  }
}

@media (max-width: 640px) {
  .builder-header {
    padding: 1rem;
  }

  .header-left h1 {
    font-size: 1.2rem;
  }

  .user-info {
    display: none;
  }

  .cars-grid {
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 10px;
  }

  .car-card-content {
    padding: 10px;
  }

  .car-card-content h3 {
    font-size: 0.9rem;
  }

  .top-bar h2 {
    font-size: 1.3rem;
  }
}
</style>
