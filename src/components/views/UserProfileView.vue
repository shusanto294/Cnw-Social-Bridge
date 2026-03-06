<template>
  <div class="cnw-profile-view">
    <div v-if="loading" class="cnw-social-worker-loading">Loading profile...</div>
    <div v-else-if="error" class="cnw-social-worker-empty">{{ error }}</div>
    <template v-else>
      <!-- Profile Header -->
      <div class="profile-header">
        <div class="profile-header-left">
          <img :src="user.avatar || defaultAvatar" :alt="fullName" class="profile-avatar" width="100" height="100" />
          <button v-if="isOwn" class="profile-upload-btn" @click="triggerUpload">Upload New Photo</button>
          <input ref="fileInput" type="file" accept="image/*" style="display:none" @change="handleAvatarUpload" />
        </div>
        <div class="profile-header-info">
          <p class="profile-joined">Joined {{ joinedDate }}</p>
          <div class="profile-name-row">
            <h1 class="profile-name">{{ fullName }}</h1>
            <span class="cnw-social-worker-verified" title="Verified">&#10003;</span>
            <span class="profile-verified-label">Verified Social Worker</span>
          </div>
          <p class="profile-title">Licensed Clinical Social Worker</p>
          <p class="profile-helpful">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" fill="#e53935"/></svg>
            {{ user.helpful_count || 0 }}&nbsp; Helpful answers
          </p>
        </div>
      </div>

      <!-- Anonymous Toggle -->
      <div v-if="isOwn" class="profile-anon-row">
        <span>Set Anonymous</span>
        <label class="profile-toggle">
          <input type="checkbox" :checked="user.anonymous" @change="handleToggleAnonymous" />
          <span class="profile-toggle-slider"></span>
        </label>
      </div>

      <!-- Personal Info -->
      <div class="profile-info-card">
        <div class="profile-info-header">
          <h3>Personal Info</h3>
          <button v-if="isOwn && !editing" class="profile-edit-btn" @click="startEdit">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit
          </button>
          <div v-if="editing" class="profile-edit-actions">
            <button class="profile-save-btn" :disabled="saving" @click="saveProfile">{{ saving ? 'Saving...' : 'Save' }}</button>
            <button class="profile-cancel-btn" @click="cancelEdit">Cancel</button>
          </div>
        </div>
        <div class="profile-info-grid">
          <div class="profile-info-field">
            <label>Full Name:</label>
            <template v-if="editing">
              <div class="profile-edit-name">
                <input v-model="editForm.first_name" placeholder="First name" class="profile-input" />
                <input v-model="editForm.last_name" placeholder="Last name" class="profile-input" />
              </div>
            </template>
            <span v-else>{{ fullName }}</span>
          </div>
          <div class="profile-info-field">
            <label>Email:</label>
            <template v-if="editing">
              <input :value="user.email || '---'" class="profile-input" disabled />
            </template>
            <span v-else>{{ user.email || '---' }}</span>
          </div>
          <div class="profile-info-field">
            <label>Phone:</label>
            <template v-if="editing">
              <input v-model="editForm.phone" placeholder="Phone number" class="profile-input" />
            </template>
            <span v-else>{{ user.phone || '---' }}</span>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="profile-tabs">
        <button v-for="tab in tabs" :key="tab.key" class="profile-tab" :class="{ 'is-active': activeTab === tab.key }" @click="activeTab = tab.key">
          <svg v-if="tab.key === 'answers'" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4l-4 4V6c0-1.1.9-2 2-2z"/></svg>
          <svg v-if="tab.key === 'questions'" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          <svg v-if="tab.key === 'saved'" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
          <svg v-if="tab.key === 'activity'" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
          {{ tab.label }}
        </button>
      </div>

      <!-- Tab Content -->
      <div class="profile-tab-content">
        <!-- Answers Tab -->
        <template v-if="activeTab === 'answers'">
          <div v-if="loadingReplies" class="cnw-social-worker-loading" style="padding:20px">Loading answers...</div>
          <div v-else-if="replies.length === 0" class="profile-empty-state">
            <p class="profile-empty-title">No answers yet</p>
            <p class="profile-empty-desc">When this user shares answers, they'll appear here.</p>
            <router-link to="/" class="profile-empty-link">unanswered questions</router-link>
          </div>
          <div v-else class="profile-cards-grid">
            <div v-for="reply in replies" :key="reply.id" class="profile-content-card" @click="$router.push('/thread/' + reply.thread_id)">
              <h4 class="profile-card-title">{{ reply.thread_title }}</h4>
              <p class="profile-card-excerpt">{{ truncate(reply.content, 120) }}</p>
              <div class="profile-card-tags">
                <span v-for="tag in reply.tags" :key="tag" class="qcard-tag">{{ tag }}</span>
              </div>
              <p class="profile-card-meta">{{ formatDate(reply.created_at) }} &bull; {{ reply.saves_count || 0 }} helpful</p>
            </div>
          </div>
        </template>

        <!-- Questions Tab -->
        <template v-if="activeTab === 'questions'">
          <div v-if="loadingThreads" class="cnw-social-worker-loading" style="padding:20px">Loading questions...</div>
          <div v-else-if="threads.length === 0" class="profile-empty-state">
            <p class="profile-empty-title">No questions yet</p>
            <p class="profile-empty-desc">When this user asks questions, they'll appear here.</p>
          </div>
          <div v-else class="profile-cards-grid">
            <div v-for="thread in threads" :key="thread.id" class="profile-content-card" @click="$router.push('/thread/' + thread.id)">
              <h4 class="profile-card-title">{{ thread.title }}</h4>
              <p class="profile-card-excerpt">{{ truncate(thread.content, 120) }}</p>
              <div class="profile-card-tags">
                <span v-for="tag in thread.tags" :key="tag" class="qcard-tag">{{ tag }}</span>
              </div>
              <p class="profile-card-meta">{{ formatDate(thread.created_at) }} &bull; {{ thread.saves_count || 0 }} helpful</p>
            </div>
          </div>
        </template>

        <!-- Saved Tab -->
        <template v-if="activeTab === 'saved'">
          <div class="profile-empty-state">
            <p class="profile-empty-title">Saved threads</p>
            <p class="profile-empty-desc">View saved threads from the <router-link to="/saved">Saved Threads</router-link> page.</p>
          </div>
        </template>

        <!-- Activity Tab -->
        <template v-if="activeTab === 'activity'">
          <div class="profile-empty-state">
            <p class="profile-empty-title">Activity</p>
            <p class="profile-empty-desc">View activity from the <router-link to="/activity">My Activity</router-link> page.</p>
          </div>
        </template>
      </div>
    </template>
  </div>
</template>

<script>
import { getUser, getUserThreads, getUserReplies, updateUserProfile, toggleAnonymous, uploadAvatar } from '@/api/index.js';

export default {
  name: 'UserProfileView',
  data() {
    return {
      user: {},
      loading: true,
      error: '',
      activeTab: 'answers',
      tabs: [
        { key: 'answers', label: 'Answers' },
        { key: 'questions', label: 'Questions' },
        { key: 'saved', label: 'Saved' },
        { key: 'activity', label: 'Activity' },
      ],
      threads: [],
      replies: [],
      loadingThreads: false,
      loadingReplies: false,
      editing: false,
      saving: false,
      editForm: { first_name: '', last_name: '', phone: '' },
      defaultAvatar: 'https://www.gravatar.com/avatar/?d=mp&s=150',
    };
  },
  computed: {
    userId() {
      return window.cnwData?.currentUser?.id || 0;
    },
    isOwn() {
      return this.user.is_own || false;
    },
    fullName() {
      const fn = this.user.first_name || '';
      const ln = this.user.last_name || '';
      const full = (fn + ' ' + ln).trim();
      return full || this.user.display_name || 'User';
    },
    joinedDate() {
      if (!this.user.user_registered) return '';
      const d = new Date(this.user.user_registered);
      return d.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
    },
    userRole() {
      return 'Verified Social Worker';
    },
  },
  watch: {
    activeTab(tab) {
      if (tab === 'answers' && this.replies.length === 0) this.fetchReplies();
      if (tab === 'questions' && this.threads.length === 0) this.fetchThreads();
    },
  },
  created() {
    this.loadProfile();
  },
  methods: {
    async loadProfile() {
      this.loading = true;
      this.error = '';
      this.threads = [];
      this.replies = [];
      try {
        this.user = await getUser(this.userId);
        this.fetchReplies();
      } catch {
        this.error = 'Failed to load user profile.';
      } finally {
        this.loading = false;
      }
    },
    async fetchThreads() {
      this.loadingThreads = true;
      try {
        const data = await getUserThreads(this.userId);
        this.threads = data.threads || [];
      } catch { /* silent */ }
      finally { this.loadingThreads = false; }
    },
    async fetchReplies() {
      this.loadingReplies = true;
      try {
        const data = await getUserReplies(this.userId);
        this.replies = data.replies || [];
      } catch { /* silent */ }
      finally { this.loadingReplies = false; }
    },
    startEdit() {
      this.editForm.first_name = this.user.first_name || '';
      this.editForm.last_name = this.user.last_name || '';
      this.editForm.phone = this.user.phone || '';
      this.editing = true;
    },
    cancelEdit() {
      this.editing = false;
    },
    async saveProfile() {
      this.saving = true;
      try {
        await updateUserProfile(this.editForm);
        this.user.first_name = this.editForm.first_name;
        this.user.last_name = this.editForm.last_name;
        this.user.phone = this.editForm.phone;
        this.editing = false;
      } catch { /* silent */ }
      finally { this.saving = false; }
    },
    async handleToggleAnonymous() {
      try {
        const res = await toggleAnonymous();
        this.user.anonymous = res.anonymous;
        if (window.cnwData?.currentUser) {
          window.cnwData.currentUser.anonymous = res.anonymous;
        }
        window.dispatchEvent(new CustomEvent('cnw-anonymous-updated', { detail: res.anonymous }));
      } catch { /* silent */ }
    },
    triggerUpload() {
      this.$refs.fileInput.click();
    },
    async handleAvatarUpload() {
      const file = this.$refs.fileInput.files[0];
      if (!file) return;
      try {
        const res = await uploadAvatar(file);
        if (res.success && res.avatar) {
          this.user.avatar = res.avatar;
          if (window.cnwData?.currentUser) {
            window.cnwData.currentUser.avatar = res.avatar;
          }
          window.dispatchEvent(new CustomEvent('cnw-avatar-updated', { detail: res.avatar }));
        }
      } catch { /* silent */ }
      this.$refs.fileInput.value = '';
    },
    truncate(str, len) {
      if (!str) return '';
      return str.length > len ? str.slice(0, len) + '...' : str;
    },
    formatDate(d) {
      if (!d) return '';
      const date = new Date(d);
      return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    },
  },
};
</script>

<style>
.cnw-profile-view {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* ── Profile Header ─────────────────────────────────────────── */
.profile-header {
  display: flex;
  gap: var(--space-m, 28px);
  align-items: center;
}
.profile-header-left {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: var(--space-xs, 14px);
  flex-shrink: 0;
}
.profile-avatar {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  object-fit: cover;
}
.profile-upload-btn {
  padding: var(--space-3xs, 7px) var(--space-s, 19.8px);
  border: 1px solid #414141;
  background: #fff;
  border-radius: var(--radius-s, 2px);
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: var(--text-xs, 14px);
  line-height: 1;
  color: var(--text-body, #414141);
  cursor: pointer;
  white-space: nowrap;
  text-align: center;
}

.profile-header-info {
  display: flex;
  flex-direction: column;
  gap: var(--space-2xs, 9.9px);
  justify-content: center;
  flex: 1;
  min-height: 1px;
  min-width: 1px;
}
.profile-joined {
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: var(--text-xs, 14px);
  line-height: 16px;
  color: #999;
}
.profile-name-row {
  display: flex;
  align-items: center;
  gap: var(--space-2xs, 9.9px);
  flex-wrap: wrap;
}
.profile-name {
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
  font-size: var(--text-m, 18px);
  line-height: 24.5px;
  color: #000;
}
.profile-name-row .cnw-social-worker-verified {
  width: 14px;
  height: 14px;
  font-size: 9px;
}
.profile-verified-label {
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: var(--text-xs, 14px);
  line-height: 16px;
  color: #999;
}
.profile-title {
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: var(--text-xs, 14px);
  line-height: 16px;
  color: var(--text-body, #414141);
}
.profile-helpful {
  display: flex;
  align-items: center;
  gap: var(--space-4xs, 4.95px);
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: var(--text-xs, 14px);
  line-height: 16px;
  color: var(--text-body, #414141);
}
.profile-helpful svg {
  flex-shrink: 0;
}

/* ── Anonymous Toggle ───────────────────────────────────────── */
.profile-anon-row {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: var(--space-2xs, 9.9px);
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: var(--text-xs, 14px);
  line-height: 16px;
  color: var(--text-body, #414141);
}
.profile-toggle {
  position: relative;
  display: inline-block;
  width: 42px;
  height: 22px;
}
.profile-toggle input {
  opacity: 0;
  width: 0;
  height: 0;
}
.profile-toggle-slider {
  position: absolute;
  cursor: pointer;
  inset: 0;
  background: #ccc;
  border-radius: 22px;
  transition: 0.2s;
}
.profile-toggle-slider::before {
  content: "";
  position: absolute;
  height: 16px;
  width: 16px;
  left: 3px;
  bottom: 3px;
  background: white;
  border-radius: 50%;
  transition: 0.2s;
}
.profile-toggle input:checked + .profile-toggle-slider {
  background: var(--primary);
}
.profile-toggle input:checked + .profile-toggle-slider::before {
  transform: translateX(20px);
}

/* ── Personal Info Card ─────────────────────────────────────── */
.profile-info-card {
  border: var(--radius-xs, 1px) solid var(--border);
  border-radius: var(--radius);
  padding: var(--space-s, 19.8px);
}
.profile-info-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: var(--space-xs, 14px);
}
.profile-info-header h3 {
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
  font-size: var(--text-m, 16px);
  line-height: 24.5px;
  color: #000;
}
.profile-edit-btn {
  display: flex;
  align-items: center;
  gap: var(--space-4xs, 4.95px);
  background: none;
  border: none;
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: var(--text-xs, 14px);
  line-height: 16px;
  color: var(--primary);
  cursor: pointer;
}
.profile-edit-btn:hover {
  color: var(--teal-dark);
}
.profile-edit-actions {
  display: flex;
  gap: var(--space-2xs, 9.9px);
}
.profile-save-btn {
  padding: var(--space-3xs, 7px) var(--space-xs, 14px);
  background: var(--primary);
  color: #fff;
  border: none;
  border-radius: var(--radius-s, 2px);
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: var(--text-xs, 14px);
  line-height: 16px;
  cursor: pointer;
}
.profile-save-btn:hover { background: var(--secondary); }
.profile-save-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.profile-cancel-btn {
  padding: var(--space-3xs, 7px) var(--space-xs, 14px);
  background: none;
  border: var(--radius-xs, 1px) solid var(--border);
  border-radius: var(--radius-s, 2px);
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: var(--text-xs, 14px);
  line-height: 16px;
  cursor: pointer;
  color: var(--text-body, #414141);
}
.profile-info-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: var(--space-xs, 14px);
}
.profile-info-field {
  display: flex;
  flex-direction: column;
  gap: var(--space-4xs, 4.95px);
}
.profile-info-field label {
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: var(--text-xs, 14px);
  line-height: 16px;
  color: #999;
}
.profile-info-field span {
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: var(--text-xs, 14px);
  line-height: 16px;
  color: var(--text-body, #414141);
}
.profile-input {
  padding: var(--space-3xs, 7px) var(--space-2xs, 9.9px);
  border: var(--radius-xs, 1px) solid var(--border);
  border-radius: var(--radius-s, 2px);
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: var(--text-xs, 14px);
  line-height: 16px;
  color: var(--text-body, #414141);
  width: 100%;
}
.profile-input:focus {
  outline: none;
  border-color: var(--primary);
}
.profile-edit-name {
  display: flex;
  gap: var(--space-2xs, 9.9px);
}

/* ── Tabs ───────────────────────────────────────────────────── */
.profile-tabs {
  display: flex;
  gap: 0;
  border-bottom: 2px solid var(--border);
}
.profile-tab {
  display: flex;
  align-items: center;
  gap: var(--space-4xs, 4.95px);
  padding: var(--space-2xs, 9.9px) var(--space-s, 19.8px);
  background: none;
  border: none;
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: var(--text-xs, 14px);
  line-height: 16px;
  color: var(--text-body, #414141);
  cursor: pointer;
  border-bottom: 2px solid transparent;
  margin-bottom: -2px;
  transition: color 0.15s, border-color 0.15s, background 0.15s;
}
.profile-tab:hover {
  color: #000;
}
.profile-tab.is-active {
  color: #fff;
  background: var(--primary);
  border-radius: var(--radius-m) var(--radius-m) 0 0;
  border-bottom-color: var(--primary);
}
.profile-tab.is-active svg {
  stroke: #fff;
}

/* ── Tab Content ────────────────────────────────────────────── */
.profile-tab-content {
  min-height: 200px;
}
.profile-cards-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: var(--space-xs, 14px);
}
.profile-content-card {
  border: var(--radius-xs, 1px) solid var(--primary);
  border-radius: var(--radius);
  padding: var(--space-xs, 14px);
  cursor: pointer;
  transition: box-shadow 0.15s;
  display: flex;
  flex-direction: column;
  gap: var(--space-2xs, 9.9px);
}
.profile-content-card:hover {
  box-shadow: var(--shadow-md);
}
.profile-card-title {
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
  font-size: var(--text-xs, 14px);
  line-height: 16px;
  color: #000;
}
.profile-card-excerpt {
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: var(--text-xs, 14px);
  line-height: 16px;
  color: var(--text-body, #414141);
}
.profile-card-tags {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-4xs, 4.95px);
}
.profile-card-meta {
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: var(--text-xs, 14px);
  line-height: 16px;
  color: #999;
}

/* ── Empty State ────────────────────────────────────────────── */
.profile-empty-state {
  border: var(--radius-xs, 1px) solid var(--primary);
  border-radius: var(--radius);
  padding: var(--space-m, 28px) var(--space-s, 19.8px);
  text-align: center;
}
.profile-empty-title {
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
  font-size: var(--text-m, 16px);
  line-height: 24.5px;
  color: #000;
  margin-bottom: var(--space-4xs, 4.95px);
}
.profile-empty-desc {
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: var(--text-xs, 14px);
  line-height: 16px;
  color: var(--text-body, #414141);
}
.profile-empty-link {
  display: inline-block;
  margin-top: var(--space-2xs, 9.9px);
  padding: var(--space-3xs, 7px) var(--space-s, 19.8px);
  background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
  color: #fff;
  border-radius: var(--radius-s, 2px);
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: var(--text-xs, 14px);
  line-height: 16px;
  text-decoration: none;
}

/* ── Responsive ─────────────────────────────────────────────── */
@media (max-width: 760px) {
  .profile-header {
    flex-direction: column;
    align-items: center;
    text-align: center;
  }
  .profile-header-info {
    align-items: center;
  }
  .profile-name-row {
    justify-content: center;
  }
  .profile-helpful {
    justify-content: center;
  }
  .profile-info-grid {
    grid-template-columns: 1fr;
  }
  .profile-cards-grid {
    grid-template-columns: 1fr;
  }
  .profile-tabs {
    overflow-x: auto;
  }
  .profile-tab {
    padding: 8px 14px;
    font-size: 13px;
    white-space: nowrap;
  }
}
</style>
