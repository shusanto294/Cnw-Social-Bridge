<template>
  <div class="cnw-users-view">
    <div class="cnw-users-header">
      <h1 class="cnw-users-title">Community Members</h1>
      <div class="cnw-users-search-wrap">
        <svg class="cnw-users-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input
          v-model="search"
          @input="onSearch"
          type="text"
          placeholder="Search members..."
          class="cnw-users-search"
        />
      </div>
    </div>

    <div v-if="loading && page === 1" class="cnw-social-worker-loading">Loading members...</div>

    <div v-else-if="!users.length && !loading" class="cnw-social-worker-empty">
      <p v-if="search">No members found matching "{{ search }}"</p>
      <p v-else>No community members yet.</p>
    </div>

    <template v-else>
      <div class="cnw-users-grid">
        <div
          v-for="user in users"
          :key="user.id"
          class="cnw-user-card"
          @click="viewProfile(user)"
        >
          <div class="cnw-user-card-top">
            <div class="cnw-user-avatar-wrap">
              <img :src="user.avatar" :alt="user.name" class="cnw-social-worker-avatar cnw-user-avatar" width="60" height="60" />
              <svg class="cnw-user-status-icon" xmlns="http://www.w3.org/2000/svg" width="8" height="8" viewBox="0 0 8 8" fill="none">
                <circle cx="4" cy="4" r="4" :fill="user.is_online ? '#82E71D' : '#B0B0B0'" />
              </svg>
            </div>
            <div class="cnw-user-card-info">
              <div class="cnw-user-name-row">
                <span class="cnw-user-name">{{ user.name }}</span>
                <span v-if="user.verified_label" class="cnw-social-worker-verified" title="Verified">&#10003;</span>
              </div>
              <p v-if="user.professional_title" class="cnw-user-title">{{ user.professional_title }}</p>
              <p v-else-if="user.verified_label" class="cnw-user-title">{{ user.verified_label }}</p>
            </div>
          </div>

          <div class="cnw-user-card-stats">
            <div class="cnw-user-stat">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#f5a623" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
              <span class="cnw-user-stat-val">{{ user.reputation || 0 }}</span>
              <span class="cnw-user-stat-label">Points</span>
            </div>
            <div class="cnw-user-stat">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--teal)" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
              <span class="cnw-user-stat-val">{{ user.thread_count || 0 }}</span>
              <span class="cnw-user-stat-label">Questions</span>
            </div>
            <div class="cnw-user-stat">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
              <span class="cnw-user-stat-val">{{ user.reply_count || 0 }}</span>
              <span class="cnw-user-stat-label">Answers</span>
            </div>
          </div>

          <div class="cnw-user-card-actions">
            <router-link :to="'/users/' + user.id" class="cnw-user-btn cnw-user-btn-profile" @click.stop>
              View Profile
            </router-link>
            <button
              v-if="user.id !== currentUserId"
              class="cnw-user-btn cnw-user-btn-message"
              @click.stop="messageUser(user)"
            >
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
              Message
            </button>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="pages > 1" class="cnw-users-pagination">
        <button
          class="cnw-users-page-btn"
          :disabled="page <= 1"
          @click="goPage(page - 1)"
        >&laquo; Prev</button>
        <span class="cnw-users-page-info">Page {{ page }} of {{ pages }}</span>
        <button
          class="cnw-users-page-btn"
          :disabled="page >= pages"
          @click="goPage(page + 1)"
        >Next &raquo;</button>
      </div>
    </template>

    <!-- User detail modal -->
    <div v-if="selectedUser" class="cnw-user-modal-overlay" @click.self="selectedUser = null">
      <div class="cnw-user-modal">
        <button class="cnw-user-modal-close" @click="selectedUser = null">&times;</button>
        <div class="cnw-user-modal-header">
          <img :src="selectedUser.avatar" :alt="selectedUser.name" class="cnw-social-worker-avatar" width="72" height="72" />
          <div>
            <div class="cnw-user-name-row">
              <h2 class="cnw-user-modal-name">{{ selectedUser.name }}</h2>
              <span v-if="selectedUser.verified_label" class="cnw-social-worker-verified" title="Verified">&#10003;</span>
            </div>
            <p v-if="selectedUser.professional_title" class="cnw-user-modal-title">{{ selectedUser.professional_title }}</p>
            <p v-if="selectedUser.verified_label" class="cnw-user-modal-label">{{ selectedUser.verified_label }}</p>
          </div>
        </div>

        <div v-if="modalLoading" class="cnw-social-worker-loading" style="padding:20px;">Loading...</div>
        <template v-else-if="modalUser">
          <p v-if="modalUser.bio" class="cnw-user-modal-bio">{{ modalUser.bio }}</p>
          <div class="cnw-user-modal-stats">
            <div class="cnw-user-modal-stat">
              <span class="cnw-user-modal-stat-val">{{ modalUser.reputation || 0 }}</span>
              <span class="cnw-user-modal-stat-label">Reputation</span>
            </div>
            <div class="cnw-user-modal-stat">
              <span class="cnw-user-modal-stat-val">{{ modalUser.thread_count || 0 }}</span>
              <span class="cnw-user-modal-stat-label">Questions</span>
            </div>
            <div class="cnw-user-modal-stat">
              <span class="cnw-user-modal-stat-val">{{ modalUser.reply_count || 0 }}</span>
              <span class="cnw-user-modal-stat-label">Answers</span>
            </div>
            <div class="cnw-user-modal-stat">
              <span class="cnw-user-modal-stat-val">{{ modalUser.helpful_count || 0 }}</span>
              <span class="cnw-user-modal-stat-label">Helpful</span>
            </div>
          </div>
          <p class="cnw-user-modal-joined">Member since {{ formatDate(modalUser.user_registered) }}</p>
          <div class="cnw-user-modal-actions">
            <router-link :to="'/users/' + selectedUser.id" class="cnw-user-btn cnw-user-btn-profile" @click="selectedUser = null">
              Full Profile
            </router-link>
            <button
              v-if="selectedUser.id !== currentUserId"
              class="cnw-user-btn cnw-user-btn-message"
              @click="messageUser(selectedUser)"
            >
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
              Message
            </button>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script>
import { getUsers, getUser } from '@/api/index.js';

export default {
  name: 'UsersView',
  data() {
    return {
      users: [],
      loading: true,
      search: '',
      searchTimeout: null,
      page: 1,
      pages: 1,
      total: 0,
      selectedUser: null,
      modalUser: null,
      modalLoading: false,
      currentUserId: window.cnwData?.currentUser?.id || 0,
    };
  },
  created() {
    this.fetchUsers();
  },
  methods: {
    async fetchUsers() {
      this.loading = true;
      try {
        const data = await getUsers({ page: this.page, search: this.search });
        this.users = data.users || [];
        this.total = data.total || 0;
        this.pages = data.pages || 1;
      } catch {
        this.users = [];
      }
      this.loading = false;
    },
    onSearch() {
      clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        this.page = 1;
        this.fetchUsers();
      }, 350);
    },
    goPage(p) {
      this.page = p;
      this.fetchUsers();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    async viewProfile(user) {
      this.selectedUser = user;
      this.modalUser = null;
      this.modalLoading = true;
      try {
        this.modalUser = await getUser(user.id);
      } catch { this.modalUser = null; }
      this.modalLoading = false;
    },
    messageUser(user) {
      this.selectedUser = null;
      this.$router.push('/messages');
      // Small delay to allow route to load, then trigger message start
      setTimeout(() => {
        window.dispatchEvent(new CustomEvent('cnw-start-chat', { detail: { id: user.id, name: user.name, avatar: user.avatar, verified_label: user.verified_label || '' } }));
      }, 300);
    },
    formatDate(dateStr) {
      if (!dateStr) return '';
      const d = new Date(dateStr);
      return d.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
    },
  },
};
</script>

<style>
.cnw-users-view { display: flex; flex-direction: column; gap: 16px; }

/* Header */
.cnw-users-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
}
.cnw-users-title {
  font-size: 20px;
  font-weight: 800;
  color: var(--text-dark);
}
.cnw-users-search-wrap {
  position: relative;
  width: 280px;
  max-width: 100%;
}
.cnw-users-search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-light);
  pointer-events: none;
}
.cnw-users-search {
  width: 100%;
  padding: 9px 12px 9px 36px;
  border: 1px solid var(--border);
  border-radius: var(--radius);
  font-size: 13px;
  font-family: inherit;
  outline: none;
  background: #fff;
}
.cnw-users-search:focus { border-color: var(--teal); }

/* Grid */
.cnw-users-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 16px;
}

/* Card */
.cnw-user-card {
  background: #fff;
  border-radius: var(--radius);
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border);
  padding: 20px;
  cursor: pointer;
  transition: box-shadow 0.15s, transform 0.15s;
  display: flex;
  flex-direction: column;
  gap: 14px;
}
.cnw-user-card:hover {
  box-shadow: var(--shadow-md);
  transform: translateY(-2px);
}
.cnw-user-card-top {
  display: flex;
  gap: 14px;
  align-items: center;
}
.cnw-user-avatar-wrap {
  position: relative;
  flex-shrink: 0;
}
.cnw-user-avatar {
  width: 60px;
  height: 60px;
  border: 2px solid var(--teal-light);
  object-fit: cover;
  border-radius: 50%;
}
.cnw-user-status-icon {
  position: absolute;
  top: 0;
  right: 0;
}
.cnw-user-card-info { flex: 1; min-width: 0; }
.cnw-user-name-row {
  display: flex;
  align-items: center;
  gap: 6px;
}
.cnw-user-name {
  font-size: 15px;
  font-weight: 700;
  color: var(--text-dark);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.cnw-user-title {
  font-size: 12px;
  color: var(--text-light);
  margin: 2px 0 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Stats row */
.cnw-user-card-stats {
  display: flex;
  justify-content: space-around;
  padding: 10px 0;
  border-top: 1px solid var(--border);
  border-bottom: 1px solid var(--border);
}
.cnw-user-stat {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  color: var(--text-med);
}
.cnw-user-stat-val {
  font-weight: 700;
  color: var(--text-dark);
}
.cnw-user-stat-label { color: var(--text-light); }

/* Actions */
.cnw-user-card-actions {
  display: flex;
  gap: 8px;
}
.cnw-user-btn {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 7px 14px;
  border-radius: var(--radius-sm);
  font-size: 12px;
  font-weight: 600;
  border: none;
  cursor: pointer;
  text-decoration: none;
  transition: background 0.15s;
}
.cnw-user-btn-profile {
  background: var(--teal);
  color: #fff;
  flex: 1;
  justify-content: center;
}
.cnw-user-btn-profile:hover { background: var(--teal-dark); color: #fff; }
.cnw-user-btn-message {
  background: var(--green);
  color: #fff;
  flex: 1;
  justify-content: center;
}
.cnw-user-btn-message:hover { background: var(--green-dark); }

/* Pagination */
.cnw-users-pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
  padding: 16px 0;
}
.cnw-users-page-btn {
  padding: 8px 16px;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  background: #fff;
  font-size: 13px;
  font-weight: 500;
  color: var(--teal);
  cursor: pointer;
  transition: background 0.15s;
}
.cnw-users-page-btn:hover:not(:disabled) { background: var(--teal-light); }
.cnw-users-page-btn:disabled { opacity: 0.4; cursor: not-allowed; }
.cnw-users-page-info {
  font-size: 13px;
  color: var(--text-light);
}

/* ── Modal ─────────────────────────────────── */
.cnw-user-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}
.cnw-user-modal {
  background: #fff;
  border-radius: var(--radius);
  padding: 28px;
  width: 440px;
  max-width: 92vw;
  max-height: 85vh;
  overflow-y: auto;
  position: relative;
  box-shadow: 0 8px 30px rgba(0,0,0,0.18);
}
.cnw-user-modal-close {
  position: absolute;
  top: 12px;
  right: 16px;
  background: none;
  border: none;
  font-size: 24px;
  color: var(--text-light);
  cursor: pointer;
  line-height: 1;
}
.cnw-user-modal-close:hover { color: var(--text-dark); }
.cnw-user-modal-header {
  display: flex;
  gap: 16px;
  align-items: center;
  margin-bottom: 16px;
}
.cnw-user-modal-header img {
  width: 72px;
  height: 72px;
  object-fit: cover;
  border-radius: 50%;
}
.cnw-user-modal-name {
  font-size: 18px;
  font-weight: 700;
  color: var(--text-dark);
}
.cnw-user-modal-title {
  font-size: 13px;
  color: var(--text-med);
  margin: 2px 0 0;
}
.cnw-user-modal-label {
  font-size: 12px;
  color: var(--teal);
  margin: 2px 0 0;
}
.cnw-user-modal-bio {
  font-size: 13px;
  color: var(--text-med);
  line-height: 1.6;
  margin-bottom: 16px;
}
.cnw-user-modal-stats {
  display: flex;
  justify-content: space-around;
  padding: 14px 0;
  border-top: 1px solid var(--border);
  border-bottom: 1px solid var(--border);
  margin-bottom: 12px;
}
.cnw-user-modal-stat {
  text-align: center;
}
.cnw-user-modal-stat-val {
  display: block;
  font-size: 18px;
  font-weight: 700;
  color: var(--text-dark);
}
.cnw-user-modal-stat-label {
  font-size: 11px;
  color: var(--text-light);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.cnw-user-modal-joined {
  font-size: 12px;
  color: var(--text-light);
  margin-bottom: 16px;
}
.cnw-user-modal-actions {
  display: flex;
  gap: 10px;
}

@media (max-width: 600px) {
  .cnw-users-header { flex-direction: column; align-items: stretch; }
  .cnw-users-search-wrap { width: 100%; }
  .cnw-users-grid { grid-template-columns: 1fr; }
}
</style>
