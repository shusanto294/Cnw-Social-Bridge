<template>
  <div class="cnw-users-view">
    <div class="cnw-users-header">
      <div class="cnw-users-header-text">
        <h1 class="cnw-users-title">Community Members</h1>
        <p class="cnw-users-subtitle">Find and connect with community members</p>
      </div>
    </div>

    <!-- Toolbar: tabs + search -->
    <div class="cnw-users-toolbar">
      <div v-if="isLoggedIn" class="cnw-users-tabs">
        <button
          class="cnw-users-tab"
          :class="{ active: tab === 'all' }"
          @click="switchTab('all')"
        >All Members</button>
        <button
          class="cnw-users-tab"
          :class="{ active: tab === 'connections' }"
          @click="switchTab('connections')"
        >My Connections</button>
        <button
          class="cnw-users-tab"
          :class="{ active: tab === 'requests' }"
          @click="switchTab('requests')"
        >
          Requests
          <span v-if="requestCount > 0" class="cnw-users-tab-badge">{{ requestCount }}</span>
        </button>
      </div>
      <div class="cnw-users-search-wrap">
        <input
          v-model="search"
          @input="onSearch"
          type="text"
          placeholder="Search members..."
          class="cnw-users-search"
          aria-label="Search members"
        />
        <svg class="cnw-users-search-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <button v-if="search" class="cnw-users-search-clear" @click="search = ''; onSearch()" aria-label="Clear search">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
    </div>

    <div v-if="loading" class="cnw-users-grid">
      <div v-for="n in 6" :key="n" class="cnw-skeleton-card" style="padding:20px;gap:14px">
        <div style="display:flex;gap:14px;align-items:center">
          <div class="cnw-skeleton cnw-skeleton-circle" style="width:60px;height:60px;flex-shrink:0"></div>
          <div style="flex:1;display:flex;flex-direction:column;gap:6px">
            <div class="cnw-skeleton cnw-skeleton-line-lg" style="width:65%"></div>
            <div class="cnw-skeleton cnw-skeleton-line-sm" style="width:45%"></div>
          </div>
        </div>
        <div style="display:flex;justify-content:space-around;padding:10px 0;border-top:1px solid var(--border);border-bottom:1px solid var(--border)">
          <div class="cnw-skeleton cnw-skeleton-line-sm" style="width:50px"></div>
          <div class="cnw-skeleton cnw-skeleton-line-sm" style="width:60px"></div>
          <div class="cnw-skeleton cnw-skeleton-line-sm" style="width:50px"></div>
        </div>
        <div style="display:flex;gap:8px">
          <div class="cnw-skeleton" style="width:80px;height:32px;border-radius:var(--radius-sm)"></div>
          <div class="cnw-skeleton" style="width:80px;height:32px;border-radius:var(--radius-sm)"></div>
        </div>
      </div>
    </div>

    <div v-else-if="isTabEmpty" class="cnw-social-worker-empty">
      <template v-if="tab === 'requests'">
        <p>No pending connection requests.</p>
      </template>
      <template v-else-if="tab === 'connections'">
        <p v-if="search">No connections found matching "{{ search }}"</p>
        <p v-else>You have no connections yet. Browse All Members and connect!</p>
      </template>
      <template v-else>
        <p v-if="search">No members found matching "{{ search }}"</p>
        <p v-else>No community members yet.</p>
      </template>
    </div>

    <template v-else>
      <!-- Requests tab: incoming requests -->
      <template v-if="tab === 'requests'">
        <div class="cnw-users-grid">
          <div
            v-for="req in requests"
            :key="req.user_id"
            class="cnw-user-card"
            role="button"
            tabindex="0"
            @click="viewProfile(req)"
            @keydown.enter="viewProfile(req)"
          >
            <div class="cnw-user-card-top">
              <div class="cnw-user-avatar-wrap">
                <img :src="req.avatar" :alt="req.name" class="cnw-social-worker-avatar cnw-user-avatar" width="60" height="60" />
              </div>
              <div class="cnw-user-card-info">
                <div class="cnw-user-name-row">
                  <span class="cnw-user-name">{{ req.name }}</span>
                  <span v-if="req.verified_label" class="cnw-social-worker-verified" title="Verified">&#10003;</span>
                </div>
                <p v-if="req.professional_title" class="cnw-user-title">{{ req.professional_title }}</p>
                <p v-else-if="req.verified_label" class="cnw-user-title">{{ req.verified_label }}</p>
              </div>
            </div>

            <div class="cnw-user-card-stats">
              <div class="cnw-user-stat">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#f5a623" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                <span class="cnw-user-stat-val">{{ req.reputation || 0 }}</span>
                <span class="cnw-user-stat-label">Points</span>
              </div>
              <div class="cnw-user-stat">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--teal)" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <span class="cnw-user-stat-val">{{ req.thread_count || 0 }}</span>
                <span class="cnw-user-stat-label">Questions</span>
              </div>
              <div class="cnw-user-stat">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <span class="cnw-user-stat-val">{{ req.reply_count || 0 }}</span>
                <span class="cnw-user-stat-label">Answers</span>
              </div>
            </div>

            <div class="cnw-user-card-actions">
              <button
                class="cnw-user-btn cnw-user-btn-accept"
                :disabled="req._loading"
                @click.stop="handleAccept(req)"
              >Accept</button>
              <button
                class="cnw-user-btn cnw-user-btn-decline"
                :disabled="req._loading"
                @click.stop="handleDecline(req)"
              >Decline</button>
            </div>
          </div>
        </div>
      </template>

      <!-- All Members / My Connections -->
      <template v-else>
        <div class="cnw-users-grid">
          <div
            v-for="user in users"
            :key="user.id"
            class="cnw-user-card"
            role="button"
            tabindex="0"
            @click="viewProfile(user)"
            @keydown.enter="viewProfile(user)"
          >
            <div class="cnw-user-card-top">
              <div class="cnw-user-avatar-wrap">
                <img :src="user.avatar" :alt="user.name" class="cnw-social-worker-avatar cnw-user-avatar" width="60" height="60" />
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
              <!-- Connection button -->
              <template v-if="user.id !== currentUserId && isLoggedIn">
                <button
                  v-if="user.connection_status === 'connected'"
                  class="cnw-user-btn cnw-user-btn-message"
                  @click.stop="messageUser(user)"
                >
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                  Message
                </button>
                <button
                  v-else-if="user.connection_status === 'pending_sent'"
                  class="cnw-user-btn cnw-user-btn-pending"
                  @click.stop="handleCancelRequest(user)"
                  :disabled="user._loading"
                >
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                  Pending
                </button>
                <button
                  v-else-if="user.connection_status === 'pending_received'"
                  class="cnw-user-btn cnw-user-btn-accept"
                  @click.stop="handleAcceptFromCard(user)"
                  :disabled="user._loading"
                >Accept</button>
                <button
                  v-else
                  class="cnw-user-btn cnw-user-btn-connect"
                  @click.stop="handleConnect(user)"
                  :disabled="user._loading"
                >
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                  Connect
                </button>
              </template>
              <button
                v-else-if="user.id !== currentUserId && !isLoggedIn"
                class="cnw-user-btn cnw-user-btn-connect"
                @click.stop="openLoginModal"
              >
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                Connect
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
    </template>

    <!-- User detail modal -->
    <div v-if="selectedUser" class="cnw-user-modal-overlay" @click.self="selectedUser = null">
      <div class="cnw-user-modal" role="dialog" aria-modal="true" aria-labelledby="user-modal-name">
        <button class="cnw-user-modal-close" @click="selectedUser = null" aria-label="Close">&times;</button>
        <div class="cnw-user-modal-header">
          <img :src="selectedUser.avatar" :alt="selectedUser.name" class="cnw-social-worker-avatar" width="72" height="72" />
          <div>
            <div class="cnw-user-name-row">
              <h2 id="user-modal-name" class="cnw-user-modal-name">{{ selectedUser.name }}</h2>
              <span v-if="selectedUser.verified_label" class="cnw-social-worker-verified" title="Verified">&#10003;</span>
            </div>
            <p v-if="selectedUser.professional_title" class="cnw-user-modal-title">{{ selectedUser.professional_title }}</p>
            <p v-if="selectedUser.verified_label" class="cnw-user-modal-label">{{ selectedUser.verified_label }}</p>
          </div>
        </div>

        <div v-if="modalLoading" style="padding:16px;display:flex;flex-direction:column;gap:10px">
          <div class="cnw-skeleton cnw-skeleton-line" style="width:80%"></div>
          <div class="cnw-skeleton-row" style="justify-content:center;gap:16px">
            <div class="cnw-skeleton cnw-skeleton-line" style="width:60px;height:36px;border-radius:var(--radius-m)"></div>
            <div class="cnw-skeleton cnw-skeleton-line" style="width:60px;height:36px;border-radius:var(--radius-m)"></div>
            <div class="cnw-skeleton cnw-skeleton-line" style="width:60px;height:36px;border-radius:var(--radius-m)"></div>
          </div>
        </div>
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
            <template v-if="selectedUser.id !== currentUserId && isLoggedIn">
              <button
                v-if="selectedUser.connection_status === 'connected'"
                class="cnw-user-btn cnw-user-btn-message"
                @click="messageUser(selectedUser)"
              >
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                Message
              </button>
              <button
                v-else-if="selectedUser.connection_status === 'pending_sent'"
                class="cnw-user-btn cnw-user-btn-pending"
                @click="handleCancelRequest(selectedUser)"
              >Pending</button>
              <button
                v-else-if="selectedUser.connection_status === 'pending_received'"
                class="cnw-user-btn cnw-user-btn-accept"
                @click="handleAcceptFromCard(selectedUser)"
              >Accept</button>
              <button
                v-else
                class="cnw-user-btn cnw-user-btn-connect"
                @click="handleConnect(selectedUser)"
              >
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                Connect
              </button>
            </template>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script>
import { getUsers, getUser, getConnections, getConnectionRequests, sendConnectionRequest, acceptConnection, declineConnection, removeConnection } from '@/api/index.js';

export default {
  name: 'UsersView',
  data() {
    return {
      users: [],
      requests: [],
      requestCount: 0,
      loading: true,
      search: '',
      searchTimeout: null,
      page: 1,
      pages: 1,
      total: 0,
      tab: 'all',
      selectedUser: null,
      modalUser: null,
      modalLoading: false,
      currentUserId: window.cnwData?.currentUser?.id || 0,
      isLoggedIn: !!(window.cnwData?.currentUser?.id > 0),
    };
  },
  computed: {
    isTabEmpty() {
      if (this.tab === 'requests') return !this.requests.length;
      return !this.users.length;
    },
  },
  created() {
    // Read tab from query param (e.g. /users?tab=requests)
    const qTab = this.$route && this.$route.query && this.$route.query.tab;
    if (qTab && ['all', 'connections', 'requests'].includes(qTab) && this.isLoggedIn) {
      this.tab = qTab;
    }
    if (this.tab === 'requests') {
      this.fetchRequests();
    } else {
      this.fetchUsers();
    }
    if (this.isLoggedIn) {
      this.fetchRequestCount();
    }
  },
  watch: {
    '$route.query.tab'(newTab) {
      if (newTab && ['all', 'connections', 'requests'].includes(newTab) && this.isLoggedIn) {
        this.switchTab(newTab);
      }
    },
  },
  methods: {
    switchTab(t) {
      if (this.tab === t) return;
      this.tab = t;
      this.search = '';
      this.page = 1;
      if (t === 'requests') {
        this.fetchRequests();
      } else {
        this.fetchUsers();
      }
    },
    async fetchUsers() {
      this.loading = true;
      try {
        let data;
        if (this.tab === 'connections') {
          data = await getConnections({ page: this.page, search: this.search });
        } else {
          data = await getUsers({ page: this.page, search: this.search });
        }
        this.users = (data.users || []).map(u => ({ ...u, _loading: false }));
        this.total = data.total || 0;
        this.pages = data.pages || 1;
      } catch {
        this.users = [];
      }
      this.loading = false;
    },
    async fetchRequests() {
      this.loading = true;
      try {
        const data = await getConnectionRequests();
        this.requests = (data.requests || []).map(r => ({ ...r, _loading: false }));
        this.requestCount = this.requests.length;
      } catch {
        this.requests = [];
      }
      this.loading = false;
    },
    async fetchRequestCount() {
      try {
        const data = await getConnectionRequests();
        this.requestCount = (data.requests || []).length;
      } catch { /* silent */ }
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
      const uid = user.id || user.user_id;
      this.selectedUser = { ...user, id: uid };
      this.modalUser = null;
      this.modalLoading = true;
      try {
        this.modalUser = await getUser(uid);
      } catch { this.modalUser = null; }
      this.modalLoading = false;
    },
    messageUser(user) {
      this.selectedUser = null;
      const detail = { id: user.id, name: user.name, avatar: user.avatar, verified_label: user.verified_label || '' };
      window._cnwPendingChat = detail;
      this.$router.push('/messages');
      this.$nextTick(() => {
        window.dispatchEvent(new CustomEvent('cnw-start-chat', { detail }));
      });
    },
    openLoginModal() {
      window.dispatchEvent(new CustomEvent('cnw-open-login'));
    },
    async handleConnect(user) {
      user._loading = true;
      try {
        const res = await sendConnectionRequest(user.id);
        user.connection_status = res.status;
      } catch { /* silent */ }
      user._loading = false;
    },
    async handleCancelRequest(user) {
      user._loading = true;
      try {
        await removeConnection(user.id);
        user.connection_status = 'none';
      } catch { /* silent */ }
      user._loading = false;
    },
    async handleAcceptFromCard(user) {
      user._loading = true;
      try {
        const res = await acceptConnection(user.id);
        user.connection_status = res.status;
      } catch { /* silent */ }
      user._loading = false;
    },
    async handleAccept(req) {
      req._loading = true;
      try {
        await acceptConnection(req.user_id);
        this.requests = this.requests.filter(r => r.user_id !== req.user_id);
        this.requestCount = this.requests.length;
        window.dispatchEvent(new CustomEvent('cnw-connections-updated'));
      } catch { /* silent */ }
      req._loading = false;
    },
    async handleDecline(req) {
      req._loading = true;
      try {
        await declineConnection(req.user_id);
        this.requests = this.requests.filter(r => r.user_id !== req.user_id);
        this.requestCount = this.requests.length;
        window.dispatchEvent(new CustomEvent('cnw-connections-updated'));
      } catch { /* silent */ }
      req._loading = false;
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
  gap: 16px;
}
.cnw-users-header-text {
  flex: 1;
}
.cnw-users-title {
  font-size: 22px;
  font-weight: 700;
  color: var(--text-dark);
  margin: 0;
  line-height: 1.2;
}
.cnw-users-subtitle {
  font-size: 13px;
  color: var(--text-light);
  margin: 2px 0 0;
  font-weight: 400;
}
.cnw-users-search-wrap {
  display: flex;
  align-items: center;
  gap: 8px;
  background: var(--bg);
  border: 1px solid var(--border);
  border-radius: 8px;
  padding: 7px 12px;
  min-width: 200px;
  max-width: 280px;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.cnw-users-search-wrap:focus-within {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(58, 169, 218, 0.1);
}
.cnw-users-search-icon {
  flex-shrink: 0;
  color: var(--text-light);
}
.cnw-users-search {
  flex: 1;
  padding: 0;
  border: none;
  font-size: 13px;
  font-weight: 400;
  font-family: inherit;
  color: var(--text-body);
  background: transparent;
  line-height: 1;
  min-width: 0;
}
.cnw-users-search::placeholder {
  color: var(--text-light);
  font-weight: 400;
}
.cnw-users-search:focus { outline: none; }
.cnw-users-search-clear {
  background: none;
  border: none;
  color: var(--text-light);
  cursor: pointer;
  line-height: 1;
  padding: 2px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  border-radius: 50%;
  transition: all 0.15s;
}
.cnw-users-search-clear:hover {
  color: #dc2626;
  background: #fef2f2;
}

/* Toolbar: tabs + search */
.cnw-users-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  background: var(--white);
  padding: 10px 16px;
  border-radius: 10px;
  border: 1px solid var(--border);
  flex-wrap: wrap;
}
.cnw-users-tabs {
  display: flex;
  gap: 4px;
  flex-wrap: wrap;
}
.cnw-users-tab {
  padding: 7px 14px;
  font-size: 13px;
  font-weight: 500;
  font-family: inherit;
  color: var(--text-med);
  background: none;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  line-height: 1;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.15s;
}
.cnw-users-tab:hover {
  background: var(--bg);
  color: var(--text-dark);
}
.cnw-users-tab.active {
  background: var(--primary);
  color: #fff;
}
.cnw-users-tab-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: #d63638;
  color: #fff;
  font-size: 10px;
  font-weight: 700;
  min-width: 18px;
  height: 18px;
  border-radius: 9px;
  padding: 0 5px;
}
.cnw-users-tab.active .cnw-users-tab-badge {
  background: rgba(255, 255, 255, 0.25);
}

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
  transition: background 0.15s, opacity 0.15s;
}
.cnw-user-btn:disabled { opacity: 0.6; cursor: not-allowed; }
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
.cnw-user-btn-connect {
  background: var(--teal);
  color: #fff;
  flex: 1;
  justify-content: center;
}
.cnw-user-btn-connect:hover { background: var(--teal-dark); }
.cnw-user-btn-pending {
  background: #e0e0e0;
  color: var(--text-med);
  flex: 1;
  justify-content: center;
}
.cnw-user-btn-pending:hover { background: #d0d0d0; }
.cnw-user-btn-accept {
  background: var(--green);
  color: #fff;
  flex: 1;
  justify-content: center;
}
.cnw-user-btn-accept:hover { background: var(--green-dark); }
.cnw-user-btn-decline {
  background: #d63638;
  color: #fff;
  flex: 1;
  justify-content: center;
}
.cnw-user-btn-decline:hover { background: #b32d2f; }

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

@media (max-width: 760px) {
  .cnw-users-header { flex-wrap: wrap; }
  .cnw-users-toolbar { flex-direction: column; align-items: stretch; }
  .cnw-users-search-wrap { max-width: 100%; min-width: auto; }
}
@media (max-width: 480px) {
  .cnw-users-header { gap: 10px; }
  .cnw-users-title { font-size: 18px; }
  .cnw-users-grid { grid-template-columns: 1fr; }
  .cnw-users-tabs { width: 100%; }
  .cnw-users-tab { padding: 6px 10px; font-size: 12px; }
}
</style>
