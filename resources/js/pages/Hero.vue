<template>
  <div class="hero-section">
    <!-- Background Carousel with Keen Slider -->
    <div ref="container" class="keen-slider bg-carousel">
      <div v-for="(car, idx) in backgroundCars" :key="idx" class="keen-slider__slide car-slide">
        <img :src="car.image" :alt="car.name" />
      </div>
    </div>

    <!-- Minimal Header with Login -->
    <header class="hero-header">
      <div class="header-left">
        <h2>POTATO</h2>
      </div>
      <div class="header-right">
        <button class="login-btn">Sign In</button>
      </div>
    </header>

    <!-- Overlay + Content -->
    <div class="hero-content">
      <div class="content-inner">
        <h1>Potato Car Builder</h1>
        <p class="subtitle">Design. Tune. Compete. Build your dream setup from 3,847 real cars.</p>

        <div class="stats-grid">
          <div class="stat">
            <div class="stat-number">3,847</div>
            <div class="stat-label">Real Cars</div>
          </div>
          <div class="stat">
            <div class="stat-number">12,000+</div>
            <div class="stat-label">Engines</div>
          </div>
          <div class="stat">
            <div class="stat-number">∞</div>
            <div class="stat-label">Setups</div>
          </div>
        </div>

        <router-link to="/builder" class="cta-btn">
          Start Building Now →
        </router-link>

        <div class="scroll-hint">↓ Scroll to explore</div>
      </div>
    </div>

    <!-- Features Section Below -->
    <div class="features">
      <div class="feature-card">
        <div class="feature-icon">⚙</div>
        <h3>Build & Tune</h3>
        <p>Select any car, choose engines, add mods. Real performance data.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">★</div>
        <h3>Gamification</h3>
        <p>Earn XP, unlock badges, compete on leaderboards.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">◆</div>
        <h3>Garage</h3>
        <p>Save unlimited builds, track stats, share with friends.</p>
      </div>
    </div>

    <!-- Footer -->
    <footer class="hero-footer">
      <div class="footer-content">
        <div class="footer-section">
          <h4>Potato Car Builder</h4>
          <p>Design and customize your dream cars with real performance data.</p>
        </div>
        <div class="footer-section">
          <h4>Quick Links</h4>
          <ul>
            <li><router-link to="/builder">Builder</router-link></li>
            <li><a href="#features">Features</a></li>
            <li><a href="#about">About</a></li>
          </ul>
        </div>
        <div class="footer-section">
          <h4>Resources</h4>
          <ul>
            <li><a href="#docs">Documentation</a></li>
            <li><a href="#api">API</a></li>
            <li><a href="#support">Support</a></li>
          </ul>
        </div>
        <div class="footer-section">
          <h4>Follow Us</h4>
          <div class="social-links">
            <a href="#" class="social">Twitter</a>
            <a href="#" class="social">Instagram</a>
            <a href="#" class="social">Discord</a>
          </div>
        </div>
      </div>
      <div class="footer-bottom">
        <p>&copy; 2026 Potato Car Builder. All rights reserved.</p>
      </div>
    </footer>
  </div>
</template>

<script>
import { useKeenSlider } from "keen-slider/vue.es"
import "keen-slider/keen-slider.min.css"

export default {
  setup() {
    const [container] = useKeenSlider(
      {
        loop: true,
        mode: "free-snap",
        initial: 1,

      },
      [
        (slider) => {
          let timeout
          let mouseOver = false
          function clearNextTimeout() {
            clearTimeout(timeout)
          }
          function nextTimeout() {
            clearTimeout(timeout)
            if (mouseOver) return
            timeout = setTimeout(() => {
              slider.next()
            }, 4000)
          }
          slider.on("created", () => {
            slider.container.addEventListener("mouseover", () => {
              mouseOver = true
              clearNextTimeout()
            })
            slider.container.addEventListener("mouseout", () => {
              mouseOver = false
              nextTimeout()
            })
            nextTimeout()
          })
          slider.on("dragStarted", clearNextTimeout)
          slider.on("animationEnded", nextTimeout)
          slider.on("updated", nextTimeout)
        },
      ]
    )
    return { container }
  },
  data() {
    return {
backgroundCars: [
    { name: "Audi R8 V10 Plus", image: "https://images.unsplash.com/photo-1542362567-b07e54358753?w=1600&h=1000&fit=crop&q=95" },
    { name: "Chevrolet Corvette C8", image: "https://images.unsplash.com/photo-1614200179396-2bdb77ebf81b?w=1600&h=1000&fit=crop&q=95" },
    { name: "Porsche 911 Turbo S", image: "https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=1600&h=1000&fit=crop&q=95" },
    { name: "Ferrari 296 GTB", image: "https://images.unsplash.com/photo-1617654112368-307921291f42?w=1600&h=1000&fit=crop&q=95" },
    { name: "Lamborghini Revuelto", image: "https://images.unsplash.com/photo-1621135802920-133df287f89c?w=1600&h=1000&fit=crop&q=95" },
    { name: "Aston martin valkyrie", image: "https://images.unsplash.com/photo-1709115667750-477f8c010420?w=1600&h=1000&fit=crop&q=95" },
    { name: "BMW M440i xDrive", image: "https://images.unsplash.com/photo-1502877338535-766e1452684a?w=1400&h=700&fit=crop&q=85" },
    { name: "Mercedes-AMG GT63", image: "https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?w=1600&h=1000&fit=crop&q=95" },
    ]
    }
  },
  methods: {
    handleLogin() {
      alert('Login coming soon!')
    }
  }
}
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

html, body {
  height: 100%;
}

.hero-section {
  position: relative;
  min-height: 100vh;
  overflow-x: hidden;
  display: flex;
  flex-direction: column;
}

/* Background Carousel - Keen Slider */
.bg-carousel {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 80%;
  z-index: 0;
  overflow: hidden;
}

.car-slide {
  width: 100%;
  height: 100%;
  min-width: 100%;
  display: flex;
}

.car-slide img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* Keen Slider Transitions */
.keen-slider__slide {
  opacity: 1;
  transition: opacity 0.5s ease-in-out;
}

/* Dark Overlay */
.hero-section::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, rgba(30, 64, 175, 0.65) 0%, rgba(26, 32, 76, 0.75) 100%);
  z-index: 1;
}

/* Minimal Header */
.hero-header {
  position: relative;
  z-index: 3;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 2rem;
  background: rgba(0, 0, 0, 0.08);
  backdrop-filter: blur(8px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.header-left h2 {
  color: white;
  margin: 0;
  font-size: 1.5rem;
  font-weight: 700;
  letter-spacing: 1px;
}

.header-right {
  display: flex;
  gap: 1rem;
}

.login-btn {
  padding: 0.65rem 1.5rem;
  background: rgba(16, 185, 129, 0.2);
  border: 1.5px solid rgba(16, 185, 129, 0.5);
  color: rgba(255, 255, 255, 0.95);
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  font-size: 0.95rem;
  transition: all 0.3s;
}

.login-btn:hover {
  background: rgba(16, 185, 129, 0.35);
  border-color: rgba(16, 185, 129, 0.8);
  color: white;
}

/* Content */
.hero-content {
  position: relative;
  z-index: 2;
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  color: white;
  padding: 2rem;
  min-height: calc(100vh - 80px);
}

.content-inner h1 {
  font-size: 3.8rem;
  margin: 0 0 1rem 0;
  font-weight: 800;
  text-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
  letter-spacing: -1px;
}

.subtitle {
  font-size: 1.4rem;
  margin-bottom: 2.5rem;
  opacity: 0.92;
  max-width: 650px;
  margin-left: auto;
  margin-right: auto;
  font-weight: 300;
  line-height: 1.6;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 2.5rem;
  margin-bottom: 3rem;
  max-width: 550px;
  margin-left: auto;
  margin-right: auto;
}

.stat {
  background: rgba(255, 255, 255, 0.08);
  padding: 2rem 1.5rem;
  border-radius: 12px;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.12);
  transition: all 0.3s;
}

.stat:hover {
  background: rgba(255, 255, 255, 0.12);
}

.stat-number {
  font-size: 2.2rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.stat-label {
  font-size: 0.85rem;
  opacity: 0.8;
  font-weight: 400;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* CTA Button */
.cta-btn {
  display: inline-block;
  padding: 1.1rem 2.8rem;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  text-decoration: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 1rem;
  transition: all 0.3s;
  box-shadow: 0 8px 25px rgba(16, 185, 129, 0.35);
  margin-bottom: 2rem;
}

.cta-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 35px rgba(16, 185, 129, 0.5);
  background: linear-gradient(135deg, #059669 0%, #047857 100%);
}

/* Scroll Hint */
.scroll-hint {
  position: absolute;
  bottom: 30px;
  left: 50%;
  transform: translateX(-50%);
  animation: bounce 2.5s infinite;
  font-size: 1.3rem;
  opacity: 0.65;
  z-index: 3;
}

@keyframes bounce {
  0%, 100% { transform: translateX(-50%) translateY(0); }
  50% { transform: translateX(-50%) translateY(-12px); }
}

/* Features Section */
.features {
  position: relative;
  z-index: 2;
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 2.5rem;
  padding: 3rem 2rem 5rem 2rem;
  margin-top: -2rem;
  background: linear-gradient(180deg, rgba(30, 64, 175, 0.03) 0%, rgba(255, 255, 255, 0.02) 50%, white 100%);
  margin: 0;
}

.feature-card {
  background: white;
  padding: 2.5rem 2rem;
  border-radius: 12px;
  text-align: center;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  transition: all 0.3s;
  border: 1px solid rgba(0, 0, 0, 0.05);
}

.feature-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 12px 35px rgba(0, 0, 0, 0.12);
}

.feature-icon {
  font-size: 2.8rem;
  margin-bottom: 1.2rem;
  display: block;
  color: #10b981;
  font-weight: 300;
}

.feature-card h3 {
  color: #1e293b;
  margin: 1.2rem 0;
  font-size: 1.25rem;
}

.feature-card p {
  color: #64748b;
  font-size: 0.95rem;
  line-height: 1.6;
  margin: 0;
}

/* Footer */
.hero-footer {
  position: relative;
  z-index: 2;
  background: linear-gradient(135deg, #1a202c 0%, #111827 100%);
  color: white;
  padding: 3.5rem 2rem 0;
  margin: 0;
}

.footer-content {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 3rem;
  max-width: 1200px;
  margin: 0 auto 2.5rem;
}

.footer-section h4 {
  margin: 0 0 1.2rem 0;
  font-size: 1rem;
  color: #10b981;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.footer-section p {
  margin: 0;
  color: rgba(255, 255, 255, 0.65);
  font-size: 0.9rem;
  line-height: 1.6;
}

.footer-section ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.footer-section ul li {
  margin-bottom: 0.8rem;
}

.footer-section a {
  color: rgba(255, 255, 255, 0.65);
  text-decoration: none;
  transition: color 0.3s;
  font-size: 0.9rem;
}

.footer-section a:hover {
  color: #10b981;
}

.social-links {
  display: flex;
  gap: 0.8rem;
}

.social {
  display: inline-block;
  padding: 0.5rem 1rem;
  background: rgba(16, 185, 129, 0.1);
  border-radius: 4px;
  transition: all 0.3s;
  font-size: 0.85rem;
  color: rgba(255, 255, 255, 0.7);
}

.social:hover {
  background: rgba(16, 185, 129, 0.25);
  color: #10b981;
}

.footer-bottom {
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  padding: 2rem;
  text-align: center;
  color: rgba(255, 255, 255, 0.45);
  font-size: 0.85rem;
  margin: 0;
}

/* Mobile */
@media (max-width: 768px) {
  .hero-header {
    padding: 1rem;
  }

  .header-left h2 {
    font-size: 1.3rem;
  }

  .content-inner h1 {
    font-size: 2.2rem;
  }

  .subtitle {
    font-size: 1rem;
    margin-bottom: 1.5rem;
  }

  .stats-grid {
    grid-template-columns: 1fr;
    gap: 1.5rem;
    margin-bottom: 2rem;
  }

  .features {
    grid-template-columns: 1fr;
    padding: 3rem 1.5rem;
    gap: 1.5rem;
  }

  .footer-content {
    grid-template-columns: repeat(2, 1fr);
    gap: 2rem;
  }

  .cta-btn {
    padding: 0.95rem 2.5rem;
    font-size: 0.95rem;
  }
}

@media (max-width: 480px) {
  .footer-content {
    grid-template-columns: 1fr;
  }

  .login-btn {
    padding: 0.55rem 1.2rem;
    font-size: 0.85rem;
  }

  .content-inner h1 {
    font-size: 1.8rem;
  }

  .subtitle {
    font-size: 0.95rem;
  }

  .stat-number {
    font-size: 1.8rem;
  }

  .feature-icon {
    font-size: 2.2rem;
  }

  .feature-card h3 {
    font-size: 1.1rem;
  }
}
</style>
