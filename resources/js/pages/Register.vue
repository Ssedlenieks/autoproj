<template>
  <div class="register-container">
    <theme-toggle />
    <!-- Left Side: Carousel -->
    <div class="carousel-section">
      <div class="carousel-wrapper">
        <div class="carousel" :style="{ transform: `translateX(-${currentSlide * 100}%)` }">
          <div v-for="(car, index) in cars" :key="index" class="carousel-slide">
            <img :src="car.image" :alt="car.name" class="car-image" />
            <div class="carousel-label">{{ car.name }}</div>
          </div>
        </div>
      </div>

      <!-- Carousel Navigation -->
      <button class="carousel-btn prev" @click="prevSlide">‹</button>
      <button class="carousel-btn next" @click="nextSlide">›</button>

      <!-- Carousel Indicators -->
      <div class="carousel-indicators">
        <div
          v-for="(car, index) in cars"
          :key="index"
          class="indicator"
          :class="{ active: currentSlide === index }"
          @click="currentSlide = index"
        ></div>
      </div>
    </div>

    <!-- Right Side: Registration Form -->
    <div class="form-section">
      <div class="form-header">
        <h1>Create Account</h1>
        <p>Register to start building your dream cars</p>
      </div>

      <form @submit.prevent="register" class="register-form">
        <div class="form-group">
          <label for="name">Full Name</label>
          <input
            v-model="form.name"
            type="text"
            id="name"
            placeholder="Your full name"
            required
          />
          <span v-if="errors.name" class="error-text">{{ errors.name }}</span>
        </div>

        <div class="form-group">
          <label for="email">Email</label>
          <input
            v-model="form.email"
            type="email"
            id="email"
            placeholder="your@email.com"
            required
          />
          <span v-if="errors.email" class="error-text">{{ errors.email }}</span>
        </div>

        <div class="form-group">
          <label for="username">Username</label>
          <input
            v-model="form.username"
            type="text"
            id="username"
            placeholder="Choose a username"
            required
          />
          <span v-if="errors.username" class="error-text">{{ errors.username }}</span>
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input
            v-model="form.password"
            type="password"
            id="password"
            placeholder="Enter password"
            required
          />
          <span v-if="errors.password" class="error-text">{{ errors.password }}</span>
        </div>

        <div class="form-group">
          <label for="password_confirmation">Confirm Password</label>
          <input
            v-model="form.password_confirmation"
            type="password"
            id="password_confirmation"
            placeholder="Confirm password"
            required
          />
        </div>

        <div v-if="errors.general" class="error-message">{{ errors.general }}</div>
        <div v-if="successMessage" class="success-message">{{ successMessage }}</div>

        <div class="form-actions">
          <button type="submit" class="btn-register" :disabled="loading">
            {{ loading ? 'Creating Account...' : 'Create Account' }}
          </button>
          <button type="button" class="btn-login" @click="goToLogin">
            Sign In
          </button>
        </div>

        <div class="form-footer">
          <input type="checkbox" id="terms" required />
          <label for="terms">I agree to the project rules</label>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import ThemeToggle from '../components/ThemeToggle.vue';

export default {
  data() {
    return {
      currentSlide: 0,
      autoSlideInterval: null,
      loading: false,
      successMessage: '',
      errors: {},
      form: {
        name: '',
        email: '',
        username: '',
        password: '',
        password_confirmation: '',
      },
      cars: [
        {
          name: 'Audi R8 V10 Plus',
          image:
            'https://images.unsplash.com/photo-1542362567-b07e54358753?w=1600&h=1000&fit=crop&q=95',
        },
        {
          name: 'Chevrolet Corvette C8',
          image:
            'https://images.unsplash.com/photo-1614200179396-2bdb77ebf81b?w=1600&h=1000&fit=crop&q=95',
        },
        {
          name: 'Porsche 911 Turbo S',
          image:
            'https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=1600&h=1000&fit=crop&q=95',
        },
        {
          name: 'Ferrari 296 GTB',
          image:
            'https://images.unsplash.com/photo-1617654112368-307921291f42?w=1600&h=1000&fit=crop&q=95',
        },
        {
          name: 'Lamborghini Revuelto',
          image:
            'https://images.unsplash.com/photo-1621135802920-133df287f89c?w=1600&h=1000&fit=crop&q=95',
        },
      ],
    };
  },
  mounted() {
    this.startAutoSlide();
  },
  beforeUnmount() {
    this.stopAutoSlide();
  },
  methods: {
    startAutoSlide() {
      this.autoSlideInterval = setInterval(() => {
        this.currentSlide = (this.currentSlide + 1) % this.cars.length;
      }, 5000);
    },
    stopAutoSlide() {
      if (this.autoSlideInterval) {
        clearInterval(this.autoSlideInterval);
      }
    },
    nextSlide() {
      this.currentSlide = (this.currentSlide + 1) % this.cars.length;
      this.stopAutoSlide();
      this.startAutoSlide();
    },
    prevSlide() {
      this.currentSlide =
        (this.currentSlide - 1 + this.cars.length) % this.cars.length;
      this.stopAutoSlide();
      this.startAutoSlide();
    },
    async register() {
  this.loading = true
  this.errors = {}
  this.successMessage = ''

  try {
    const response = await fetch('/register', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
      },
      credentials: 'include', // ← ADD THIS
      body: JSON.stringify(this.form),
    })

    const data = await response.json()

    if (!response.ok) {
      this.errors = data.errors || { general: ['Registration failed'] }
      return
    }

    this.successMessage = 'Account created successfully! Redirecting...'
    setTimeout(() => {
      window.location.href = '/'
    }, 2000)
  } catch (err) {
    this.errors = { general: [err.message] }
  } finally {
    this.loading = false
  }
},
    goToLogin() {
      this.$router.push('/login');
    },
  },
};
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

html, body {
  overflow-x: hidden;
  max-width: 100vw;
}

.register-container {
  display: flex;
  min-height: 100vh;
  background: white;
  color: #1e293b;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  transition: background-color 0.3s ease, color 0.3s ease;
  overflow-x: hidden;
  max-width: 100vw;
  width: 100%;
}

html[data-color-scheme="dark"] .register-container,
:global([data-color-scheme="dark"]) .register-container {
  background: #0a0a0a;
  color: #f5f5f5;
}

/* Carousel Section */
.carousel-section {
  flex: 1;
  position: relative;
  overflow: hidden;
  background: linear-gradient(135deg, #1a202c 0%, #111827 100%);
}

.carousel-section::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, rgba(30, 64, 175, 0.35) 0%, rgba(26, 32, 76, 0.45) 100%);
  z-index: 5;
  pointer-events: none;
  transition: background 0.3s ease;
}

html[data-color-scheme="dark"] .carousel-section::before,
:global([data-color-scheme="dark"]) .carousel-section::before {
  background: linear-gradient(135deg, rgba(0, 0, 0, 0.8) 0%, rgba(10, 10, 10, 0.85) 100%);
}

.carousel-wrapper {
  width: 100%;
  height: 100%;
  overflow: hidden;
  z-index: 1;
}

.carousel {
  display: flex;
  transition: transform 0.8s ease-in-out;
  height: 100%;
}

.carousel-slide {
  min-width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}

.car-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.carousel-label {
  position: absolute;
  bottom: 40px;
  left: 40px;
  font-size: 1.5rem;
  font-weight: 600;
  text-shadow: 0 2px 10px rgba(0, 0, 0, 0.8);
  color: white;
  z-index: 10;
}

.carousel-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(255, 255, 255, 0.15);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.2);
  width: 50px;
  height: 50px;
  border-radius: 50%;
  font-size: 2rem;
  cursor: pointer;
  transition: all 0.3s ease;
  z-index: 10;
  backdrop-filter: blur(4px);
}

.carousel-btn:hover {
  background: rgba(255, 255, 255, 0.25);
  border-color: rgba(255, 215, 0, 0.6);
}

html[data-color-scheme="dark"] .carousel-btn:hover {
  border-color: rgba(255, 215, 0, 0.8);
  background: rgba(255, 215, 0, 0.1);
}

.carousel-btn.prev {
  left: 30px;
}

.carousel-btn.next {
  right: 30px;
}

.carousel-indicators {
  position: absolute;
  bottom: 40px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  gap: 12px;
  z-index: 10;
}

.indicator {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.3);
  cursor: pointer;
  transition: all 0.3s ease;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.indicator.active {
  background: #10b981;
  width: 30px;
  border-radius: 5px;
  border-color: #10b981;
}

html[data-color-scheme="dark"] .indicator.active,
:global([data-color-scheme="dark"]) .indicator.active {
  background: #ffd700;
  border-color: #ffd700;
}

/* Form Section */
.form-section {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 60px 50px;
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  overflow-y: auto;
  overflow-x: hidden;
  transition: background 0.3s ease;
  scrollbar-width: none;
}

.form-section::-webkit-scrollbar {
  display: none;
}

html[data-color-scheme="dark"] .form-section,
:global([data-color-scheme="dark"]) .form-section {
  background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%);
}

.form-header {
  margin-bottom: 40px;
}

.form-header h1 {
  font-size: 2.5rem;
  margin-bottom: 10px;
  font-weight: 700;
  color: #1e293b;
  transition: color 0.3s ease;
}

html[data-color-scheme="dark"] .form-header h1,
:global([data-color-scheme="dark"]) .form-header h1 {
  color: #ffd700;
}

.form-header p {
  color: #64748b;
  font-size: 1rem;
  transition: color 0.3s ease;
}

html[data-color-scheme="dark"] .form-header p,
:global([data-color-scheme="dark"]) .form-header p {
  color: #a0aec0;
}

.register-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group label {
  font-size: 0.9rem;
  font-weight: 500;
  color: #1e293b;
  transition: color 0.3s ease;
}

html[data-color-scheme="dark"] .form-group label,
:global([data-color-scheme="dark"]) .form-group label {
  color: #e2e8f0;
}

.form-group input {
  padding: 12px 16px;
  border: 1.5px solid #e2e8f0;
  background: white;
  color: #1e293b;
  border-radius: 8px;
  font-size: 1rem;
  transition: all 0.3s ease;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

html[data-color-scheme="dark"] .form-group input,
:global([data-color-scheme="dark"]) .form-group input {
  border-color: #2d2d2d;
  background: #1a1a1a;
  color: #f5f5f5;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
}

.form-group input:focus {
  outline: none;
  border-color: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1), 0 1px 3px rgba(0, 0, 0, 0.05);
}

html[data-color-scheme="dark"] .form-group input:focus,
:global([data-color-scheme="dark"]) .form-group input:focus {
  border-color: #ffd700;
  box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.15), 0 1px 3px rgba(0, 0, 0, 0.3);
}

.form-group input::placeholder {
  color: #94a3b8;
}

html[data-color-scheme="dark"] .form-group input::placeholder,
:global([data-color-scheme="dark"]) .form-group input::placeholder {
  color: #64748b;
}

.error-text {
  color: #dc2626;
  font-size: 0.85rem;
}

.error-message {
  background: rgba(220, 38, 38, 0.08);
  border: 1px solid rgba(220, 38, 38, 0.3);
  color: #991b1b;
  padding: 12px 16px;
  border-radius: 8px;
  font-size: 0.9rem;
  transition: all 0.3s ease;
}

html[data-color-scheme="dark"] .error-message,
:global([data-color-scheme="dark"]) .error-message {
  background: rgba(220, 38, 38, 0.15);
  border-color: rgba(220, 38, 38, 0.4);
  color: #ff9999;
}

.success-message {
  background: rgba(16, 185, 129, 0.08);
  border: 1px solid rgba(16, 185, 129, 0.3);
  color: #065f46;
  padding: 12px 16px;
  border-radius: 8px;
  font-size: 0.9rem;
  transition: all 0.3s ease;
}

html[data-color-scheme="dark"] .success-message,
:global([data-color-scheme="dark"]) .success-message {
  background: rgba(255, 215, 0, 0.08);
  border-color: rgba(255, 215, 0, 0.3);
  color: #ffd700;
}

.form-actions {
  display: flex;
  gap: 12px;
  margin-top: 20px;
}

.btn-register {
  flex: 1;
  padding: 12px;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 1rem;
  box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2);
}

html[data-color-scheme="dark"] .btn-register,
:global([data-color-scheme="dark"]) .btn-register {
  background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
  color: #000;
  box-shadow: 0 4px 15px rgba(255, 215, 0, 0.25);
}

.btn-register:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(16, 185, 129, 0.35);
}

html[data-color-scheme="dark"] .btn-register:hover:not(:disabled),
:global([data-color-scheme="dark"]) .btn-register:hover:not(:disabled) {
  box-shadow: 0 8px 30px rgba(255, 215, 0, 0.4);
  transform: translateY(-2px);
}

.btn-register:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-login {
  flex: 1;
  padding: 12px;
  background: rgba(30, 64, 175, 0.08);
  color: #1e40af;
  border: 1.5px solid rgba(30, 64, 175, 0.3);
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 1rem;
}

html[data-color-scheme="dark"] .btn-login,
:global([data-color-scheme="dark"]) .btn-login {
  background: transparent;
  color: #ffd700;
  border-color: rgba(255, 215, 0, 0.5);
}

.btn-login:hover {
  background: rgba(30, 64, 175, 0.15);
  border-color: rgba(30, 64, 175, 0.5);
}

html[data-color-scheme="dark"] .btn-login:hover,
:global([data-color-scheme="dark"]) .btn-login:hover {
  background: rgba(255, 215, 0, 0.1);
  border-color: rgba(255, 215, 0, 0.8);
  color: #ffed4e;
}

.form-footer {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-top: 20px;
  font-size: 0.9rem;
}

.form-footer input[type='checkbox'] {
  width: 18px;
  height: 18px;
  cursor: pointer;
  accent-color: #10b981;
  transition: accent-color 0.3s ease;
}

html[data-color-scheme="dark"] .form-footer input[type='checkbox'],
:global([data-color-scheme="dark"]) .form-footer input[type='checkbox'] {
  accent-color: #ffd700;
}

.form-footer label {
  cursor: pointer;
  color: #64748b;
  transition: color 0.3s ease;
}

html[data-color-scheme="dark"] .form-footer label,
:global([data-color-scheme="dark"]) .form-footer label {
  color: #a0aec0;
}

@media (max-width: 1024px) {
  .register-container {
    flex-direction: column;
  }

  .carousel-section {
    min-height: 300px;
    order: 2;
  }

  .form-section {
    order: 1;
    padding: 40px 30px;
  }

  .form-header h1 {
    font-size: 2rem;
  }
}

@media (max-width: 768px) {
  .form-section {
    padding: 30px 20px;
  }

  .form-header h1 {
    font-size: 1.5rem;
  }

  .carousel-btn {
    width: 40px;
    height: 40px;
    font-size: 1.5rem;
  }

  .carousel-btn.prev {
    left: 15px;
  }

  .carousel-btn.next {
    right: 15px;
  }
}
</style>
