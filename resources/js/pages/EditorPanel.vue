<template>
  <div class="editor-container">
    <!-- Header -->
    <header class="editor-header">
      <button @click="$router.push('/dashboard')" class="back-btn">← Atpakaļ</button>
      <div class="editor-title">
        <div class="editor-icon">ED</div>
        <div>
          <h1>Redaktora panelis</h1>
          <p class="editor-subtitle">Pārvaldīt datubāzes ierakstus</p>
        </div>
      </div>
      <button class="theme-toggle-btn" @click="toggleDarkMode" :title="colorScheme === 'dark' ? 'Light Mode' : 'Dark Mode'">
        <span>{{ colorScheme === 'light' ? '🌙' : '☀️' }}</span>
        {{ colorScheme === 'light' ? 'Dark Mode' : 'Light Mode' }}
      </button>
    </header>

    <!-- TAB NAVIGATION -->
    <div class="tabs">
      <button
        v-for="tab in tabs"
        :key="tab.key"
        :class="['tab-btn', { active: activeTab === tab.key }]"
        @click="activeTab = tab.key"
      >
        <span class="tab-icon notranslate">{{ tab.icon }}</span>
        {{ tab.label }}
      </button>
    </div>

    <!-- MAKES TAB -->
    <div v-if="activeTab === 'makes'" class="tab-content">
      <div class="table-card">
        <div class="table-header">
          <h2>Markas ({{ makes.length }})</h2>
          <button @click="openMakeModal()" class="btn-add">+ Pievienot</button>
        </div>
        <div class="table-wrapper">
          <table>
            <thead>
              <tr><th>ID</th><th>Nosaukums</th><th>Darbības</th></tr>
            </thead>
            <tbody>
              <tr v-for="make in makes" :key="make.id">
                <td class="id-cell">{{ make.id }}</td>
                <td class="notranslate">{{ make.name }}</td>
                <td class="actions-cell">
                  <button @click="openMakeModal(make)" class="btn-edit">Rediģēt</button>
                  <button @click="askDelete('šo marku', async () => { await axios.delete(`/api/editor/makes/${make.id}`); toast.success('Marka dzēsta'); fetchMakes() })" class="btn-delete-sm">Dzēst</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- POWER MODS TAB -->
    <div v-if="activeTab === 'powermods'" class="tab-content">
      <div class="table-card">
        <div class="table-header"><h2>Modifikācijas ({{ powerMods.length }})</h2></div>
        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>ID</th><th>Nosaukums</th><th>Zīmols</th>
                <th>Kategorija</th><th>Aptuvens</th><th>Piezīmes</th><th>Darbības</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="mod in powerMods" :key="mod.id">
                <td class="id-cell">{{ mod.id }}</td>
                <td class="notranslate">{{ mod.name }}</td>
                <td class="notranslate">{{ mod.brand || '—' }}</td>
                <td><span class="category-badge">{{ mod.category }}</span></td>
                <td>
                  <span v-if="mod.is_estimate" class="estimate-badge">Jā</span>
                  <span v-else class="muted-text">Nē</span>
                </td>
                <td class="notes-cell">{{ mod.notes || '—' }}</td>
                <td class="actions-cell">
                  <button @click="openModModal(mod)" class="btn-edit">Rediģēt</button>
                  <button @click="askDelete('šo modifikāciju', async () => { await axios.delete(`/api/editor/power-mods/${mod.id}`); toast.success('Modifikācija dzēsta'); fetchPowerMods() })" class="btn-delete-sm">Dzēst</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ENGINES TAB -->
    <div v-if="activeTab === 'engines'" class="tab-content">
      <div class="table-card">
        <div class="table-header"><h2>Dzinēji ({{ engines.length }})</h2></div>
        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>ID</th><th>Marka / Modelis</th><th>Kods</th>
                <th>Degviela</th><th>Cil.</th><th>HP</th><th>Nm</th><th>Gads</th><th>Darbības</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="engine in engines" :key="engine.id">
                <td class="id-cell">{{ engine.id }}</td>
                <td class="notranslate">
                  <span class="make-model">{{ engine.make_name }} {{ engine.model_name }}</span>
                  <span class="car-trim-small">{{ engine.car_trim }}</span>
                </td>
                <td class="notranslate engine-code">{{ engine.code }}</td>
                <td><span class="fuel-badge" :class="'fuel-' + engine.fuel_type?.toLowerCase()">{{ engine.fuel_type }}</span></td>
                <td class="notranslate">{{ engine.cylinder || '—' }}</td>
                <td class="stat-cell hp">{{ engine.power_hp ?? '—' }}</td>
                <td class="stat-cell nm">{{ engine.torque_nm ?? '—' }}</td>
                <td class="notranslate">{{ engine.car_year || '—' }}</td>
                <td class="actions-cell">
                  <button @click="openEngineModal(engine)" class="btn-edit">Rediģēt</button>
                  <button @click="askDelete('šo dzinēju', async () => { await axios.delete(`/api/editor/engines/${engine.id}`); toast.success('Dzinējs dzēsts'); fetchEngines() })" class="btn-delete-sm">Dzēst</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- MAKE MODAL -->
    <Teleport to="body">
      <div v-if="showMakeModal" class="modal-overlay" @click.self="closeMakeModal">
        <div class="modal">
          <div class="modal-header">
            <h2>{{ makeForm.id ? 'Rediģēt marku' : 'Pievienot marku' }}</h2>
            <button class="modal-close" @click="closeMakeModal">✕</button>
          </div>
          <form @submit.prevent="submitMake" class="editor-form">
            <div class="form-group">
              <label>Markas nosaukums</label>
              <input v-model="makeForm.name" placeholder="piem. BMW, Toyota..." required />
            </div>
            <div class="modal-actions">
              <button type="submit" class="btn-primary">{{ makeForm.id ? 'Atjaunināt' : 'Pievienot' }}</button>
              <button type="button" class="btn-cancel" @click="closeMakeModal">Atcelt</button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- POWER MOD EDIT MODAL -->
    <Teleport to="body">
      <div v-if="showModModal" class="modal-overlay" @click.self="closeModModal">
        <div class="modal">
          <div class="modal-header">
            <h2>Rediģēt modifikāciju #{{ modForm.id }}</h2>
            <button class="modal-close" @click="closeModModal">✕</button>
          </div>
          <form @submit.prevent="submitPowerMod" class="editor-form">
            <div class="form-group">
              <label>Nosaukums</label>
              <input v-model="modForm.name" placeholder="piem. Turbo Upgrade" required />
            </div>
            <div class="form-group">
              <label>Zīmols</label>
              <input v-model="modForm.brand" placeholder="piem. Garrett, BorgWarner..." />
            </div>
            <div class="form-group">
              <label>Kategorija</label>
              <input v-model="modForm.category" placeholder="piem. Turbo, ECU, Exhaust..." required />
            </div>
            <div class="form-group">
              <label>Piezīmes</label>
              <textarea v-model="modForm.notes" placeholder="Papildu informācija..." rows="3"></textarea>
            </div>
            <div class="form-group checkbox-group">
              <label class="checkbox-label">
                <input type="checkbox" v-model="modForm.is_estimate" />
                <span>Ir aptuvens skaitlis</span>
              </label>
            </div>
            <div class="modal-actions">
              <button type="submit" class="btn-primary">Saglabāt</button>
              <button type="button" class="btn-cancel" @click="closeModModal">Atcelt</button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- ENGINE EDIT MODAL -->
    <Teleport to="body">
      <div v-if="showEngineModal" class="modal-overlay" @click.self="closeEngineModal">
        <div class="modal">
          <div class="modal-header">
            <h2>Rediģēt dzinēju #{{ engineForm.id }}</h2>
            <button class="modal-close" @click="closeEngineModal">✕</button>
          </div>
          <form @submit.prevent="submitEngine" class="editor-form">
            <div class="form-group">
              <label>Dzinēja kods</label>
              <input v-model="engineForm.code" placeholder="piem. B58, 2JZ..." required />
            </div>
            <div class="form-group">
              <label>Apakšversija</label>
              <input v-model="engineForm.subvariant" placeholder="piem. B58B30M0..." />
            </div>
            <div class="form-group">
              <label>Degvielas tips</label>
              <input v-model="engineForm.fuel_type" placeholder="piem. Petrol, Diesel..." required />
            </div>
            <div class="form-group">
              <label>Cilindri</label>
              <input v-model="engineForm.cylinder" placeholder="piem. 4, 6, 8..." type="number" />
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Jauda (HP)</label>
                <input v-model.number="engineForm.power_hp" placeholder="piem. 300" type="number" />
              </div>
              <div class="form-group">
                <label>Griezes moments (Nm)</label>
                <input v-model.number="engineForm.torque_nm" placeholder="piem. 450" type="number" />
              </div>
            </div>
            <div class="modal-actions">
              <button type="submit" class="btn-primary">Saglabāt</button>
              <button type="button" class="btn-cancel" @click="closeEngineModal">Atcelt</button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- DELETE CONFIRMATION MODAL -->
    <Teleport to="body">
      <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
        <div class="modal delete-modal">
          <div class="delete-icon"></div>
          <h3>Dzēst {{ deleteTarget.label }}?</h3>
          <p>Šī darbība ir neatgriezeniska.</p>
          <div class="modal-actions">
            <button @click="confirmDelete" class="btn-del-confirm">Dzēst</button>
            <button @click="showDeleteModal = false" class="btn-cancel">Atcelt</button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted, inject } from 'vue'
import axios from 'axios'
import { toast } from 'vue3-toastify'

const colorScheme  = inject('colorScheme')
const toggleDarkMode = inject('toggleDarkMode')

const activeTab = ref('makes')
const tabs = [
  { key: 'makes',     label: 'Markas',       icon: 'MK' },
  { key: 'powermods', label: 'Modifikācijas', icon: 'PM' },
  { key: 'engines',   label: 'Dzinēji',       icon: 'EG' },
]

// ---- DELETE MODAL ----
const showDeleteModal = ref(false)
const deleteTarget    = ref({ label: '', action: null })

const askDelete = (label, action) => {
  deleteTarget.value = { label, action }
  showDeleteModal.value = true
}
const confirmDelete = async () => {
  showDeleteModal.value = false
  await deleteTarget.value.action()
}

// ---- MAKES ----
const makes        = ref([])
const showMakeModal = ref(false)
const makeForm     = ref({ id: null, name: '' })

const fetchMakes = async () => {
  const res = await axios.get('/api/makes')
  makes.value = res.data
}
const openMakeModal = (make = null) => {
  makeForm.value = make ? { id: make.id, name: make.name } : { id: null, name: '' }
  showMakeModal.value = true
}
const closeMakeModal = () => {
  showMakeModal.value = false
  makeForm.value = { id: null, name: '' }
}
const submitMake = async () => {
  try {
    if (makeForm.value.id) {
      await axios.put(`/api/editor/makes/${makeForm.value.id}`, makeForm.value)
      toast.success('Marka atjaunināta')
    } else {
      await axios.post('/api/editor/makes', makeForm.value)
      toast.success('Marka pievienota')
    }
    closeMakeModal()
    fetchMakes()
  } catch (e) {
    toast.error('Kļūda: ' + (e.response?.data?.message || e.message))
  }
}

// ---- POWER MODS ----
const powerMods    = ref([])
const showModModal = ref(false)
const modForm      = ref({ id: null, name: '', brand: '', category: '', notes: '', is_estimate: false })

const fetchPowerMods = async () => {
  const res = await axios.get('/api/powermods')
  powerMods.value = res.data
}
const openModModal = (mod) => {
  modForm.value = { id: mod.id, name: mod.name, brand: mod.brand, category: mod.category, notes: mod.notes, is_estimate: !!mod.is_estimate }
  showModModal.value = true
}
const closeModModal = () => {
  showModModal.value = false
  modForm.value = { id: null, name: '', brand: '', category: '', notes: '', is_estimate: false }
}
const submitPowerMod = async () => {
  try {
    await axios.put(`/api/editor/power-mods/${modForm.value.id}`, modForm.value)
    toast.success('Modifikācija atjaunināta')
    closeModModal()
    fetchPowerMods()
  } catch (e) {
    toast.error('Kļūda: ' + (e.response?.data?.message || e.message))
  }
}

// ---- ENGINES ----
const engines         = ref([])
const showEngineModal = ref(false)
const engineForm      = ref({ id: null, code: '', subvariant: '', fuel_type: '', cylinder: '', power_hp: '', torque_nm: '' })

const fetchEngines = async () => {
  const res = await axios.get('/api/engines')
  engines.value = res.data.engines ?? res.data.data ?? res.data
}
const openEngineModal = (engine) => {
  engineForm.value = { id: engine.id, code: engine.code, subvariant: engine.subvariant, fuel_type: engine.fuel_type, cylinder: engine.cylinder, power_hp: engine.power_hp, torque_nm: engine.torque_nm }
  showEngineModal.value = true
}
const closeEngineModal = () => {
  showEngineModal.value = false
  engineForm.value = { id: null, code: '', subvariant: '', fuel_type: '', cylinder: '', power_hp: '', torque_nm: '' }
}
const submitEngine = async () => {
  try {
    await axios.put(`/api/editor/engines/${engineForm.value.id}`, engineForm.value)
    toast.success('Dzinējs atjaunināts')
    closeEngineModal()
    fetchEngines()
  } catch (e) {
    toast.error('Kļūda: ' + (e.response?.data?.message || e.message))
  }
}

onMounted(() => {
  fetchMakes()
  fetchPowerMods()
  fetchEngines()
})
</script>

<style scoped>
.editor-container {
  min-height: 100vh;
  background: #f8fafc;
  padding: 30px;
}

html[data-color-scheme='dark'] .editor-container {
  background: #0a0a0a;
  color: #f5f5f5;
}

.editor-header {
  display: flex;
  align-items: center;
  gap: 20px;
  background: white;
  padding: 24px 30px;
  border-radius: 16px;
  margin-bottom: 24px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

html[data-color-scheme='dark'] .editor-header { background: #1a1a1a; }

.back-btn {
  padding: 8px 14px;
  background: transparent;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  color: #1e293b;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.85rem;
  transition: all 0.2s;
  white-space: nowrap;
}

html[data-color-scheme='dark'] .back-btn { border-color: #404040; color: #f5f5f5; }
.back-btn:hover { background: #f1f5f9; border-color: #0ea5e9; color: #0ea5e9; }
html[data-color-scheme='dark'] .back-btn:hover { background: #2d2d2d; border-color: #0ea5e9; color: #0ea5e9; }

.editor-title { display: flex; align-items: center; gap: 16px; flex: 1; }

.editor-icon {
  width: 50px; height: 50px;
  border-radius: 12px;
  background: linear-gradient(135deg, #0ea5e9, #0284c7);
  display: flex; align-items: center; justify-content: center;
  font-weight: bold; color: white; font-size: 0.85rem; flex-shrink: 0;
}

.editor-title h1 { margin: 0; font-size: 1.6rem; color: #1e293b; }
html[data-color-scheme='dark'] .editor-title h1 { color: #f5f5f5; }
.editor-subtitle { margin: 2px 0 0 0; color: #64748b; font-size: 0.9rem; }

.theme-toggle-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: transparent;
  color: #64748b;
  cursor: pointer;
  font-size: 0.9rem;
  font-weight: 500;
  transition: all 0.2s;
  white-space: nowrap;
  margin-left: auto;
}

.theme-toggle-btn:hover { border-color: #0ea5e9; color: #0ea5e9; }
html[data-color-scheme='dark'] .theme-toggle-btn { border-color: #2d2d2d; color: #94a3b8; }
html[data-color-scheme='dark'] .theme-toggle-btn:hover { border-color: #0ea5e9; color: #0ea5e9; }

.tabs { display: flex; gap: 8px; margin-bottom: 24px; flex-wrap: wrap; }

.tab-btn {
  display: flex; align-items: center; gap: 8px;
  padding: 10px 20px;
  border: 2px solid #e2e8f0; border-radius: 10px;
  background: white; color: #64748b;
  font-weight: 600; cursor: pointer;
  transition: all 0.2s; font-size: 0.95rem;
}

html[data-color-scheme='dark'] .tab-btn { background: #1a1a1a; border-color: #2d2d2d; color: #94a3b8; }
.tab-btn:hover { border-color: #0ea5e9; color: #0ea5e9; }
.tab-btn.active { background: #0ea5e9; border-color: #0ea5e9; color: white; }

.tab-icon { font-size: 0.75rem; background: rgba(255,255,255,0.25); padding: 2px 5px; border-radius: 4px; }
.tab-btn:not(.active) .tab-icon { background: #f1f5f9; color: #94a3b8; }
html[data-color-scheme='dark'] .tab-btn:not(.active) .tab-icon { background: #2d2d2d; }

.tab-content { animation: fadeIn 0.2s ease; }

@keyframes fadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }

.table-card { background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
html[data-color-scheme='dark'] .table-card { background: #1a1a1a; }

.table-header {
  padding: 20px 24px;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

html[data-color-scheme='dark'] .table-header { border-bottom-color: #2d2d2d; }
.table-header h2 { margin: 0; font-size: 1.1rem; color: #1e293b; }
html[data-color-scheme='dark'] .table-header h2 { color: #f5f5f5; }

.btn-add {
  padding: 8px 18px;
  background: #0ea5e9;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 0.9rem;
}

.btn-add:hover { background: #0284c7; transform: translateY(-1px); }

.table-wrapper { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; }

thead tr { background: #f8fafc; border-bottom: 2px solid #e2e8f0; }
html[data-color-scheme='dark'] thead tr { background: #0a0a0a; border-bottom-color: #2d2d2d; }

th {
  padding: 12px 20px; text-align: left;
  font-size: 0.78rem; font-weight: 700; color: #64748b;
  text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap;
}

td { padding: 14px 20px; border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 0.9rem; }
html[data-color-scheme='dark'] td { border-bottom-color: #2d2d2d; color: #f5f5f5; }

tbody tr { transition: background 0.15s; }
tbody tr:hover { background: #f8fafc; }
html[data-color-scheme='dark'] tbody tr:hover { background: #252525; }
tbody tr:last-child td { border-bottom: none; }

.id-cell { color: #94a3b8; font-weight: 600; font-size: 0.82rem; width: 50px; }

.make-model { display: block; font-weight: 600; font-size: 0.88rem; color: #1e293b; }
html[data-color-scheme='dark'] .make-model { color: #f5f5f5; }
.car-trim-small { display: block; font-size: 0.75rem; color: #94a3b8; margin-top: 2px; }

.engine-code { font-weight: 700; color: #0ea5e9 !important; font-family: monospace; font-size: 1rem !important; }

.stat-cell { font-weight: 700; font-family: monospace; font-size: 0.95rem; }
.stat-cell.hp { color: #dc2626; }
.stat-cell.nm { color: #0284c7; }
html[data-color-scheme='dark'] .stat-cell.hp { color: #fca5a5; }
html[data-color-scheme='dark'] .stat-cell.nm { color: #7dd3fc; }

.actions-cell { display: flex; gap: 8px; white-space: nowrap; }

.btn-edit {
  padding: 5px 12px; background: #e0f2fe; color: #0284c7;
  border: none; border-radius: 6px; font-size: 0.8rem;
  font-weight: 600; cursor: pointer; transition: all 0.2s;
}

.btn-edit:hover { background: #0ea5e9; color: white; }
html[data-color-scheme='dark'] .btn-edit { background: #1e3a4a; color: #38bdf8; }
html[data-color-scheme='dark'] .btn-edit:hover { background: #0ea5e9; color: white; }

.btn-delete-sm {
  padding: 5px 12px; background: #fee2e2; color: #dc2626;
  border: none; border-radius: 6px; font-size: 0.8rem;
  font-weight: 600; cursor: pointer; transition: all 0.2s;
}

.btn-delete-sm:hover { background: #dc2626; color: white; }
html[data-color-scheme='dark'] .btn-delete-sm { background: #450a0a; color: #fca5a5; }
html[data-color-scheme='dark'] .btn-delete-sm:hover { background: #dc2626; color: white; }

.category-badge {
  padding: 3px 10px; background: #f0fdf4; color: #065f46;
  border-radius: 20px; font-size: 0.78rem; font-weight: 600; white-space: nowrap;
}

html[data-color-scheme='dark'] .category-badge { background: #064e3b; color: #6ee7b7; }

.estimate-badge {
  padding: 3px 10px; background: #fef3c7; color: #92400e;
  border-radius: 20px; font-size: 0.78rem; font-weight: 600;
}

html[data-color-scheme='dark'] .estimate-badge { background: #451a03; color: #fcd34d; }

.muted-text { color: #94a3b8; font-size: 0.85rem; }

.notes-cell {
  max-width: 200px; overflow: hidden;
  text-overflow: ellipsis; white-space: nowrap;
  color: #64748b; font-size: 0.85rem;
}

html[data-color-scheme='dark'] .notes-cell { color: #94a3b8; }

.fuel-badge { padding: 3px 10px; border-radius: 20px; font-size: 0.78rem; font-weight: 600; white-space: nowrap; }
.fuel-petrol, .fuel-gasoline, .fuel-benzins { background: #fef3c7; color: #92400e; }
.fuel-diesel { background: #dbeafe; color: #1e40af; }
.fuel-hybrid { background: #d1fae5; color: #065f46; }
.fuel-electric { background: #ede9fe; color: #5b21b6; }
html[data-color-scheme='dark'] .fuel-petrol,
html[data-color-scheme='dark'] .fuel-gasoline,
html[data-color-scheme='dark'] .fuel-benzins { background: #451a03; color: #fcd34d; }
html[data-color-scheme='dark'] .fuel-diesel { background: #1e3a5f; color: #93c5fd; }
html[data-color-scheme='dark'] .fuel-hybrid { background: #064e3b; color: #6ee7b7; }
html[data-color-scheme='dark'] .fuel-electric { background: #3b0764; color: #d8b4fe; }

/* ===== MODALS ===== */
.modal-overlay {
  position: fixed; inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex; align-items: center; justify-content: center;
  z-index: 1000;
  backdrop-filter: blur(4px);
  animation: fadeIn 0.15s ease;
}

.modal {
  background: white; border-radius: 20px; padding: 32px;
  width: 100%; max-width: 520px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.2);
  animation: slideUp 0.2s ease;
}

html[data-color-scheme='dark'] .modal { background: #1a1a1a; }

@keyframes slideUp {
  from { opacity: 0; transform: translateY(20px); }
  to   { opacity: 1; transform: translateY(0); }
}

.modal-header {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 24px; padding-bottom: 16px;
  border-bottom: 1px solid #e2e8f0;
}

html[data-color-scheme='dark'] .modal-header { border-bottom-color: #2d2d2d; }
.modal-header h2 { margin: 0; font-size: 1.2rem; color: #1e293b; }
html[data-color-scheme='dark'] .modal-header h2 { color: #f5f5f5; }

.modal-close {
  width: 32px; height: 32px; border: none; border-radius: 8px;
  background: #f1f5f9; color: #64748b; cursor: pointer; font-size: 1rem;
  display: flex; align-items: center; justify-content: center; transition: all 0.2s;
}

.modal-close:hover { background: #fee2e2; color: #dc2626; }
html[data-color-scheme='dark'] .modal-close { background: #2d2d2d; color: #94a3b8; }
html[data-color-scheme='dark'] .modal-close:hover { background: #450a0a; color: #fca5a5; }

.editor-form { display: flex; flex-direction: column; gap: 14px; }
.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

.form-group label {
  font-size: 0.82rem; font-weight: 600; color: #64748b;
  text-transform: uppercase; letter-spacing: 0.5px;
}

.form-group input,
.form-group textarea {
  padding: 10px 14px;
  border: 2px solid #e2e8f0; border-radius: 8px;
  font-size: 0.95rem; color: #1e293b;
  background: #f8fafc; transition: all 0.2s; outline: none;
}

.form-group input:focus,
.form-group textarea:focus { border-color: #0ea5e9; background: white; }

html[data-color-scheme='dark'] .form-group input,
html[data-color-scheme='dark'] .form-group textarea { background: #0a0a0a; border-color: #2d2d2d; color: #f5f5f5; }
html[data-color-scheme='dark'] .form-group input:focus,
html[data-color-scheme='dark'] .form-group textarea:focus { background: #1a1a1a; border-color: #0ea5e9; }

.form-group textarea { resize: vertical; min-height: 80px; }
.checkbox-group { flex-direction: row; align-items: center; }

.checkbox-label {
  display: flex; align-items: center; gap: 8px; cursor: pointer;
  font-size: 0.9rem !important; font-weight: 500 !important;
  text-transform: none !important; letter-spacing: 0 !important;
  color: #1e293b !important;
}

html[data-color-scheme='dark'] .checkbox-label { color: #f5f5f5 !important; }
.checkbox-label input[type='checkbox'] { width: 16px; height: 16px; accent-color: #0ea5e9; }

.modal-actions { display: flex; gap: 10px; margin-top: 8px; }

.btn-primary {
  flex: 1; padding: 11px 20px;
  background: #0ea5e9; color: white; border: none;
  border-radius: 8px; font-weight: 600; cursor: pointer;
  transition: all 0.2s; font-size: 0.95rem;
}

.btn-primary:hover { background: #0284c7; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(14,165,233,0.3); }

.btn-cancel {
  padding: 11px 18px;
  background: #f1f5f9; color: #64748b; border: none;
  border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s;
}

html[data-color-scheme='dark'] .btn-cancel { background: #2d2d2d; color: #94a3b8; }
.btn-cancel:hover { background: #e2e8f0; color: #1e293b; }
html[data-color-scheme='dark'] .btn-cancel:hover { background: #404040; color: #f5f5f5; }

/* Delete Modal */
.delete-modal {
  max-width: 360px;
  text-align: center;
  align-items: center;
  display: flex;
  flex-direction: column;
}

.delete-icon { font-size: 3rem; margin-bottom: 8px; }

.delete-modal h3 { font-size: 1.3rem; color: #1e293b; margin-bottom: 6px; }
html[data-color-scheme='dark'] .delete-modal h3 { color: #f5f5f5; }

.delete-modal p { color: #94a3b8; font-size: 0.9rem; margin-bottom: 4px; max-width: 100%; }

.delete-modal .modal-actions { justify-content: center; }

.btn-del-confirm {
  padding: 10px 24px;
  background: #dc2626; color: white; border: none;
  border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s;
}

.btn-del-confirm:hover { background: #b91c1c; transform: translateY(-1px); }
</style>