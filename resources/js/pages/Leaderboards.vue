<template>
  <div class="leaderboards-page">
    <header class="leaderboards-header">
      <div class="header-content">
        <h1>Global Leaderboards</h1>
        <p>Compete with the best builders worldwide</p>
      </div>
      <button class="btn-back" @click="$router.push('/dashboard')">
        Back to Dashboard
      </button>
    </header>

    <div class="leaderboards-grid">

      <!-- Top Builders by XP -->
      <section class="board-card top-builders">
        <div class="board-header">
          <h2>Top Builders</h2>
          <span class="board-subtitle">Ranked by XP</span>
        </div>
        <ul v-if="data.topXpUsers.length">
          <li
            v-for="(user, index) in data.topXpUsers"
            :key="user.id"
            class="rank-row"
            :class="getRankClass(index)"
          >
            <div class="rank-medal" :class="getMedalClass(index)">
              {{ index + 1 }}
            </div>
            <div class="left">
              <img v-if="user.avatar_url" :src="user.avatar_url" class="avatar" />
              <div class="info">
                <p class="name">{{ user.name }}</p>
                <p class="sub">Level {{ user.level }} &middot; {{ user.rank }} &middot; {{ user.builds_count }} builds</p>
              </div>
            </div>
            <div class="right">
              <span class="value">{{ user.xp }}<span class="unit">XP</span></span>
            </div>
          </li>
        </ul>
        <div v-else class="empty-state">
          <div class="empty-icon">No data</div>
          <p>No builders yet. Create your first build.</p>
        </div>
      </section>

      <!-- Strongest Builds -->
      <section class="board-card top-builds">
        <div class="board-header">
          <h2>Strongest Builds</h2>
          <span class="board-subtitle">Highest Final HP</span>
        </div>
        <ul v-if="data.topHpBuilds.length">
          <li
            v-for="(build, index) in data.topHpBuilds"
            :key="build.id"
            class="rank-row"
            :class="getRankClass(index)"
          >
            <div class="rank-medal" :class="getMedalClass(index)">
              {{ index + 1 }}
            </div>
            <div class="left">
              <div class="info">
                <p class="name">{{ build.project_name }}</p>
                <p class="sub">by {{ build.user_name }}</p>
              </div>
            </div>
            <div class="right">
              <span class="value">{{ build.final_hp }}<span class="unit">HP</span></span>
              <span class="sub small">+{{ build.final_hp - build.base_hp }} HP gain</span>
            </div>
          </li>
        </ul>
        <div v-else class="empty-state">
          <div class="empty-icon">No data</div>
          <p>No builds yet. Start tuning your first car.</p>
        </div>
      </section>

      <!-- Most Power Added -->
      <section class="board-card top-hp-users">
        <div class="board-header">
          <h2>Most Power Added</h2>
          <span class="board-subtitle">Total HP Gain Per User</span>
        </div>
        <ul v-if="data.topHpUsers.length">
          <li
            v-for="(user, index) in data.topHpUsers"
            :key="user.id"
            class="rank-row"
            :class="getRankClass(index)"
          >
            <div class="rank-medal" :class="getMedalClass(index)">
              {{ index + 1 }}
            </div>
            <div class="left">
              <img v-if="user.avatar_url" :src="user.avatar_url" class="avatar" />
              <div class="info">
                <p class="name">{{ user.name }}</p>
                <p class="sub">{{ user.builds_count }} builds</p>
              </div>
            </div>
            <div class="right">
              <span class="value">+{{ user.total_hp_gain }}<span class="unit">HP</span></span>
            </div>
          </li>
        </ul>
        <div v-else class="empty-state">
          <div class="empty-icon">No data</div>
          <p>No HP gains yet. Install some parts.</p>
        </div>
      </section>

    </div>

    <p v-if="loading" class="status">Loading leaderboards...</p>
    <p v-if="error" class="status error">{{ error }}</p>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import axios from 'axios'

const data = reactive({
  topXpUsers: [],
  topHpBuilds: [],
  topHpUsers: [],
})

const loading = ref(false)
const error = ref('')

onMounted(async () => {
  loading.value = true
  error.value = ''
  try {
    const res = await axios.get('/api/leaderboards')
    if (res.data.success) {
      data.topXpUsers = res.data.topXpUsers || []
      data.topHpBuilds = res.data.topHpBuilds || []
      data.topHpUsers = res.data.topHpUsers || []
    } else {
      error.value = 'Failed to load leaderboards.'
    }
  } catch (e) {
    console.error(e)
    error.value = 'Failed to load leaderboards.'
  } finally {
    loading.value = false
  }
})

const getRankClass = index => {
  if (index === 0) return 'first'
  if (index === 1) return 'second'
  if (index === 2) return 'third'
  return 'other'
}

const getMedalClass = index => {
  if (index === 0) return 'gold'
  if (index === 1) return 'silver'
  if (index === 2) return 'bronze'
  return 'standard'
}
</script>

<style scoped>
.leaderboards-page {
  min-height: 100vh;
  padding: 30px;
  background: radial-gradient(circle at top, #0f172a 0, #020617 45%, #020617 100%);
  color: #e5e7eb;
}

.leaderboards-header {
  max-width: 1200px;
  margin: 0 auto 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-content h1 {
  margin: 0;
  font-size: 2rem;
  font-weight: 700;
}

.header-content p {
  margin: 4px 0 0;
  color: #9ca3af;
  font-size: 0.9rem;
}

.btn-back {
  padding: 8px 16px;
  border-radius: 999px;
  border: 1px solid #4b5563;
  background: transparent;
  color: #e5e7eb;
  cursor: pointer;
  font-size: 0.9rem;
}

.btn-back:hover {
  background: rgba(148, 163, 184, 0.15);
}

.leaderboards-grid {
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 20px;
}

.board-card {
  background: linear-gradient(135deg, rgba(15, 23, 42, 0.98), rgba(15, 23, 42, 0.9));
  border-radius: 16px;
  padding: 18px 18px 14px;
  box-shadow: 0 18px 40px rgba(0, 0, 0, 0.45);
  border: 1px solid rgba(148, 163, 184, 0.3);
}

.board-header {
  display: flex;
  flex-direction: column;
  margin-bottom: 10px;
}

.board-header h2 {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 600;
}

.board-subtitle {
  font-size: 0.8rem;
  color: #9ca3af;
}

.rank-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 8px;
  border-radius: 10px;
  margin-bottom: 4px;
  transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
}

.rank-row.other:hover,
.rank-row.first:hover,
.rank-row.second:hover,
.rank-row.third:hover {
  transform: translateY(-1px);
  box-shadow: 0 8px 16px rgba(15, 23, 42, 0.6);
  background: radial-gradient(circle at top left, rgba(56, 189, 248, 0.15), transparent 55%);
}

.rank-row.first {
  background: radial-gradient(circle at top left, rgba(250, 204, 21, 0.24), transparent 55%);
}

.rank-row.second {
  background: radial-gradient(circle at top left, rgba(148, 163, 184, 0.24), transparent 55%);
}

.rank-row.third {
  background: radial-gradient(circle at top left, rgba(248, 113, 113, 0.24), transparent 55%);
}

.rank-medal {
  width: 32px;
  height: 32px;
  border-radius: 999px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.9rem;
  font-weight: 700;
  margin-right: 10px;
}

.rank-medal.gold {
  background: radial-gradient(circle at 30% 30%, #facc15, #92400e);
  box-shadow: 0 0 16px rgba(250, 204, 21, 0.55);
  color: #1a1a1a;
}

.rank-medal.silver {
  background: radial-gradient(circle at 30% 30%, #e5e7eb, #4b5563);
  box-shadow: 0 0 14px rgba(209, 213, 219, 0.35);
  color: #1a1a1a;
}

.rank-medal.bronze {
  background: radial-gradient(circle at 30% 30%, #f97316, #7c2d12);
  box-shadow: 0 0 14px rgba(248, 148, 72, 0.45);
  color: #fff;
}

.rank-medal.standard {
  background: #111827;
  border: 1px solid #4b5563;
  color: #9ca3af;
}

.rank-row.other .rank-medal {
  background: #020617;
  border: 1px solid #374151;
  color: #9ca3af;
}

.left {
  display: flex;
  align-items: center;
  gap: 10px;
  flex: 1;
  min-width: 0;
}

.avatar {
  width: 32px;
  height: 32px;
  border-radius: 999px;
  object-fit: cover;
  border: 1px solid rgba(148, 163, 184, 0.6);
}

.info {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.name {
  font-size: 0.95rem;
  font-weight: 600;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin: 0;
}

.sub {
  font-size: 0.8rem;
  color: #9ca3af;
  margin: 0;
}

.sub.small {
  font-size: 0.75rem;
}

.right {
  text-align: right;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}

.value {
  font-size: 0.95rem;
  font-weight: 700;
}

.unit {
  margin-left: 2px;
  font-size: 0.75rem;
  color: #9ca3af;
}

.empty-state {
  padding: 16px;
  text-align: center;
  font-size: 0.85rem;
  color: #9ca3af;
}

.empty-icon {
  font-weight: 600;
  margin-bottom: 4px;
}

.status {
  max-width: 1200px;
  margin: 16px auto 0;
  font-size: 0.85rem;
  color: #9ca3af;
}

.status.error {
  color: #f97373;
}
</style>
