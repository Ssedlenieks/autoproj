<template>
  <div class="achievement-toast">
    <div class="achievement-icon-large">{{ achievement.icon }}</div>
    <div class="achievement-content">
      <div class="achievement-title">🏆 Achievement Unlocked!</div>
      <div class="achievement-name">{{ achievement.name }}</div>
      <div class="achievement-desc">{{ achievement.description }}</div>
      <div class="achievement-points">+{{ achievement.points }} XP</div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    achievement: {
      type: Object,
      required: true
    }
  }
}
</script>

<style scoped>
.achievement-toast {
  display: flex;
  gap: 16px;
  align-items: center;
  padding: 8px;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  border-radius: 12px;
  box-shadow: 0 8px 32px rgba(16, 185, 129, 0.4);
  animation: slideInBounce 0.5s ease-out;
}

@keyframes slideInBounce {
  0% {
    transform: translateX(400px) scale(0.7);
    opacity: 0;
  }
  50% {
    transform: translateX(-20px) scale(1.05);
  }
  100% {
    transform: translateX(0) scale(1);
    opacity: 1;
  }
}

.achievement-icon-large {
  font-size: 4rem;
  filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.3));
  animation: bounce 0.6s ease-in-out infinite;
}

@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

.achievement-content {
  flex: 1;
  color: white;
}

.achievement-title {
  font-size: 0.85rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  opacity: 0.9;
  margin-bottom: 4px;
}

.achievement-name {
  font-size: 1.3rem;
  font-weight: 700;
  margin-bottom: 4px;
}

.achievement-desc {
  font-size: 0.9rem;
  opacity: 0.95;
  margin-bottom: 6px;
}

.achievement-points {
  font-size: 1.1rem;
  font-weight: 700;
  color: #ffd700;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

html[data-color-scheme='dark'] .achievement-toast {
  background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
}

html[data-color-scheme='dark'] .achievement-content {
  color: #000;
}

html[data-color-scheme='dark'] .achievement-points {
  color: #059669;
}
</style>
