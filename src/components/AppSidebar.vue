<template>
  <aside class="cnw-social-worker-sidebar">
    <!-- User Card -->
    <div class="sidebar-user-card">
      <div class="sidebar-user-bg"></div>
      <div class="sidebar-user-identity">
        <img
          :src="currentUser.avatar || defaultAvatar"
          :alt="displayName"
          class="cnw-social-worker-avatar sidebar-avatar"
          width="72" height="72"
        />
        <p class="sidebar-username">{{ displayName }}</p>
      </div>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
      <div class="sidebar-nav-group">
        <router-link to="/" class="sidebar-nav-item" active-class="is-active" exact>
          <span class="nav-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          </span>
          <span>Questions</span>
          <span v-if="questionCount > 0" class="nav-badge">{{ questionCount }}</span>
        </router-link>

        <router-link to="/tags" class="sidebar-nav-item" active-class="is-active">
          <span class="nav-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
          </span>
          <span>Tags</span>
          <span class="nav-badge">{{ tagCount }}</span>
        </router-link>

        <router-link to="/ask" class="sidebar-nav-item" active-class="is-active">
          <span class="nav-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
          </span>
          <span>Ask a Question</span>
        </router-link>

        <router-link to="/activity" class="sidebar-nav-item" active-class="is-active">
          <span class="nav-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          </span>
          <span>My Activity</span>
        </router-link>

        <router-link to="/messages" class="sidebar-nav-item" active-class="is-active">
          <span class="nav-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          </span>
          <span>Message</span>
        </router-link>

        <router-link to="/users" class="sidebar-nav-item" active-class="is-active">
          <span class="nav-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          </span>
          <span>Users</span>
        </router-link>
      </div>

      <!-- Support section -->
      <div class="sidebar-nav-group">
        <p class="sidebar-section-label">Support</p>

        <a href="#" class="sidebar-nav-item">
          <span class="nav-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
          </span>
          <span>Saved Threads</span>
        </a>

        <a href="#" class="sidebar-nav-item">
          <span class="nav-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          </span>
          <span>Community Guidelines</span>
        </a>

        <a href="#" class="sidebar-nav-item">
          <span class="nav-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          </span>
          <span>Report an Issue</span>
        </a>

        <a href="/wp-login.php?action=logout" class="sidebar-nav-item">
          <span class="nav-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          </span>
          <span>Logout</span>
        </a>
      </div>
    </nav>
  </aside>
</template>

<script>
import { getThreads } from '@/api/index.js';

export default {
  name: 'AppSidebar',
  data() {
    return {
      currentUser: window.cnwData?.currentUser || { name: '', first_name: '', last_name: '', avatar: '' },
      questionCount: 0,
      tagCount: 8,
      defaultAvatar: 'https://www.gravatar.com/avatar/?d=mp&s=72',
    };
  },
  computed: {
    displayName() {
      const fn = this.currentUser.first_name || '';
      const ln = this.currentUser.last_name || '';
      const full = (fn + ' ' + ln).trim();
      return full || this.currentUser.name || 'Guest';
    },
  },
  async mounted() {
    try {
      const data = await getThreads({ page: 1 });
      this.questionCount = data.total || 0;
    } catch (e) { /* silent */ }
  },
};
</script>

<style scoped>
.cnw-social-worker-sidebar {
  width: 278px;
  flex-shrink: 0;
  background: #fff;
  border-radius: var(--radius);
  position: sticky;
  top: 80px;
  height: fit-content;
}

.sidebar-user-card {
  margin-bottom: 12px;
}

.sidebar-user-bg {
  height: 78px;
  background: linear-gradient(180deg, var(--primary) 0%, var(--secondary) 100%);
  border-radius: var(--radius) var(--radius) 0 0;
}

.sidebar-user-identity {
  margin-top: -36px;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding-bottom: 16px;
}

.sidebar-avatar {
  width: 72px;
  height: 72px;
  border: 3px solid #fff;
  margin-bottom: 8px;
}

.sidebar-username {
  color: var(--text-dark);
  font-size: 14px;
  font-weight: 600;
  text-align: center;
  padding: 0 8px;
}

.sidebar-nav {
  border-radius: var(--radius);
  overflow: hidden;
  margin: 0 20px 20px;
}

.sidebar-nav-group {
  display: flex;
  flex-direction: column;
  gap: 10px;
  padding: 10px 0px;
  border-bottom: 1px solid rgba(255,255,255,0.08);
}
.sidebar-nav-group:last-child {
  border-bottom: none;
}

.sidebar-section-label {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.6px;
  padding: 2px 6px;
}

.sidebar-nav-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 16px;
  color: #fff;
  font-size: 13.5px;
  font-weight: 500;
  text-decoration: none;
  background: var(--bd-body-5);
  border-radius: var(--radius-sm);
  transition: background 0.15s;
}
.sidebar-nav-item:hover,
.sidebar-nav-item.is-active {
  background: linear-gradient(88deg, var(--bg-body) 22.95%, var(--secondary) 80.13%);
  color: #fff;
}

.nav-icon {
  width: 18px;
  height: 18px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  color: inherit;
}

.nav-badge {
  margin-left: auto;
  background: var(--teal);
  color: #fff;
  font-size: 11px;
  font-weight: 700;
  min-width: 20px;
  height: 20px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 5px;
}
</style>
