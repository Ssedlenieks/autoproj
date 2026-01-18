<template>
  <div class="app">
    <header>
      <h1>🚗 Potato Car Builder</h1>
      <div class="profile">
        👤 Demo Mode | Level 1 | XP: 0/100 | Garage: 0 Builds
      </div>
    </header>

    <div class="container">
      <div class="selector">
        <h2>Build Your Perfect Setup</h2>

        <div class="dropdown-group">
          <label>1. Make:</label>
          <select v-model="selectedMake" @change="loadModels">
            <option value="">Choose...</option>
            <option v-for="make in makes" :key="make.id" :value="make.id">
              {{ make.name }}
            </option>
          </select>
        </div>

        <div class="dropdown-group" v-if="selectedMake">
          <label>2. Model:</label>
          <select v-model="selectedModel" @change="loadCars" :disabled="!models.length">
            <option value="">Choose...</option>
            <option v-for="model in models" :key="model.id" :value="model.id">
              {{ model.name }}
            </option>
          </select>
        </div>

        <div class="dropdown-group" v-if="selectedModel">
          <label>3. Trim:</label>
          <select v-model="selectedCar" @change="loadCarDetails" :disabled="!cars.length">
            <option value="">Choose...</option>
            <option v-for="car in cars" :key="car.id" :value="car.id">
              {{ car.year }} {{ car.trim }}
            </option>
          </select>
        </div>
      </div>

      <div v-if="carDetails" class="car-card">
        <img :src="getImageUrl(carDetails.image_url)"
             alt="Car" class="car-img"
             @error="imgError = true" />

        <h3>{{ carDetails.year }} {{ carDetails.trim }}</h3>
        <div class="stats">
          <span>📏 {{ carDetails.body_style }}</span>
          <span>🔧 {{ carDetails.drive_type }}</span>
          <span>⚖️ {{ carDetails.weight_kg }}kg</span>
        </div>

        <h4>🔥 Available Engines:</h4>
        <div v-for="engine in carDetails.engines" :key="engine.id" class="engine-card">
          <div class="engine-header">
            <strong>{{ engine.code }}</strong>
            <span class="hp-badge">{{ engine.pivot.power_hp }} HP</span>
          </div>
          <div class="engine-stats">
            {{ engine.pivot.torque_nm }} Nm |
            0-100: {{ engine.pivot.acceleration_0_100 }}s |
            Top: {{ engine.pivot.top_speed }} km/h
          </div>
        </div>

        <div class="actions">
          <button class="save-btn" @click="saveToGarage">💾 Save to Garage (+25 XP)</button>
          <button class="share-btn" @click="shareBuild">📱 Share Build</button>
        </div>
      </div>

      <div v-else-if="selectedMake && !carDetails" class="loading">
        Select a trim to see specs & engines...
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  data() {
    return {
      makes: [],
      models: [],
      cars: [],
      selectedMake: '',
      selectedModel: '',
      selectedCar: '',
      carDetails: null,
      imgError: false
    }
  },
  async mounted() {
    await this.loadMakes()
  },
  methods: {
    async loadMakes() {
      try {
        const res = await axios.get('/api/makes')
        this.makes = res.data
      } catch (error) {
        console.error('Load makes error:', error)
      }
    },
    async loadModels() {
      if (!this.selectedMake) return
      this.models = []
      this.selectedModel = ''
      try {
        const res = await axios.get(`/api/models?make_id=${this.selectedMake}`)
        this.models = res.data
      } catch (error) {
        console.error('Load models error:', error)
      }
    },
    async loadCars() {
      if (!this.selectedModel) return
      this.cars = []
      this.selectedCar = ''
      try {
        const res = await axios.get(`/api/cars?model_id=${this.selectedModel}`)
        this.cars = res.data
      } catch (error) {
        console.error('Load cars error:', error)
      }
    },
    async loadCarDetails() {
      if (!this.selectedCar) {
        this.carDetails = null
        return
      }
      try {
        const res = await axios.get(`/api/cars/${this.selectedCar}`)
        this.carDetails = res.data
        this.imgError = false
      } catch (error) {
        console.error('Car details error:', error)
      }
    },
    getImageUrl(url) {
  if (!url) return this.getFallbackImage()

  // If it's already a complete URL, return as-is
  if (url.startsWith('http')) {
    return url
  }

  // If ADAC and needs formatting
  if (url.includes('assets.adac.de')) {
    // Try without extra params first
    return url
  }

  return url || this.getFallbackImage()
},

    getFallbackImage() {
      return 'https://images.unsplash.com/photo-1544636331-e26879cd4d9b?w=800&h=400&fit=crop&q=90'
    },
    saveToGarage() {
      alert(`✅ "${this.carDetails.trim}" saved to garage!\n+25 XP earned\n${this.carDetails.engines.length} engines unlocked`)
    },
    shareBuild() {
      alert('Share feature coming soon!')
    }
  }
}
</script>

<style scoped>
.app {
  font-family: -apple-system, BlinkMacSystemFont, sans-serif;
  margin: 0;
  background: linear-gradient(180deg, #f0f4f8 0%, #e2e8f0 100%);
  min-height: 100vh;
}

header {
  background: linear-gradient(135deg, #1e40af, #3b82f6);
  color: white;
  padding: 2rem;
  text-align: center;
  box-shadow: 0 4px 20px rgba(30, 64, 175, 0.3);
}

header h1 {
  margin: 0;
  font-size: 2.5rem;
}

.profile {
  background: rgba(255, 255, 255, 0.25);
  padding: 1rem 2rem;
  border-radius: 50px;
  margin-top: 1rem;
  backdrop-filter: blur(10px);
  font-weight: 500;
}

.container {
  max-width: 1000px;
  margin: 0 auto;
  padding: 2rem;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
}

.selector,
.car-card {
  background: white;
  padding: 2.5rem;
  border-radius: 20px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.selector h2 {
  margin-top: 0;
  color: #1e293b;
}

.dropdown-group {
  margin-bottom: 2rem;
}

label {
  display: block;
  font-weight: 600;
  margin-bottom: 0.75rem;
  color: #374151;
}

select {
  width: 100%;
  padding: 1rem 1.25rem;
  font-size: 1.1rem;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  background: white;
  transition: all 0.2s;
}

select:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

select:disabled {
  background: #f9fafb;
  color: #9ca3af;
}

.car-img {
  width: 100%;
  height: 280px;
  object-fit: cover;
  border-radius: 16px;
  margin-bottom: 1.5rem;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.car-card h3 {
  color: #1e293b;
  margin: 1rem 0;
  font-size: 1.8rem;
}

.stats {
  display: flex;
  gap: 1.5rem;
  margin-bottom: 2rem;
  font-size: 1rem;
}

.car-card h4 {
  color: #1e293b;
  margin-top: 2rem;
  margin-bottom: 1rem;
}

.engine-card {
  background: #f3f4f6;
  padding: 1.25rem;
  margin-bottom: 1rem;
  border-radius: 12px;
  border-left: 4px solid #3b82f6;
}

.engine-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.hp-badge {
  background: #ef4444;
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.9rem;
  font-weight: 700;
}

.engine-stats {
  color: #6b7280;
  font-size: 0.95rem;
}

.actions {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
}

.save-btn,
.share-btn {
  flex: 1;
  padding: 1rem 1.5rem;
  border: none;
  border-radius: 12px;
  font-size: 1.1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.save-btn {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
}

.save-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
}

.share-btn {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  color: white;
}

.share-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
}

.loading {
  grid-column: 1 / -1;
  text-align: center;
  padding: 4rem;
  color: #6b7280;
  font-style: italic;
}

@media (max-width: 768px) {
  .container {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
}
</style>
