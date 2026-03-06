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
            <span class="profile-verified-label">{{ user.verified_label || 'Verified Social Worker' }}</span>
          </div>
          <p class="profile-title">{{ user.professional_title || 'Licensed Clinical Social Worker' }}</p>
          <p class="profile-helpful">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" fill="#e53935"/></svg>
            {{ user.helpful_count || 0 }}&nbsp; Helpful answers
          </p>
        </div>
      </div>

      <!-- Anonymous Toggle -->
      <div v-if="isOwn" class="profile-anon-row">
        <button type="button" class="ask-anon-toggle" :class="{ 'is-active': user.anonymous }" @click="handleToggleAnonymous">
          <span>Anonymous</span>
          <span class="toggle-track" :class="{ on: user.anonymous }">
            <span class="toggle-thumb"></span>
          </span>
        </button>
      </div>

      <!-- Personal Info -->
      <div class="profile-info-card">
        <div class="profile-info-header">
          <h3>Personal Info</h3>
          <button v-if="isOwn" class="profile-edit-btn" @click="startEdit">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit
          </button>
        </div>
        <div class="profile-info-grid">
          <div class="profile-info-field">
            <label>Full Name:</label>
            <span>{{ fullName }}</span>
          </div>
          <div class="profile-info-field">
            <label>Email:</label>
            <span>{{ user.email || '---' }}</span>
          </div>
          <div class="profile-info-field">
            <label>Phone:</label>
            <span>{{ user.phone || '---' }}</span>
          </div>
        </div>
      </div>

      <!-- Edit Profile Modal -->
      <div v-if="editing" class="profile-modal-overlay" @click.self="cancelEdit">
        <div class="profile-modal">
          <div class="profile-modal-header">
            <h3>Edit Personal Info</h3>
            <button class="profile-modal-close" @click="cancelEdit">&times;</button>
          </div>
          <div class="profile-modal-body">
            <div class="profile-modal-field">
              <label>First Name</label>
              <input v-model="editForm.first_name" placeholder="First name" class="profile-input" />
            </div>
            <div class="profile-modal-field">
              <label>Last Name</label>
              <input v-model="editForm.last_name" placeholder="Last name" class="profile-input" />
            </div>
            <div class="profile-modal-field">
              <label>Email</label>
              <input :value="user.email || '---'" class="profile-input" disabled />
            </div>
            <div class="profile-modal-field">
              <label>Phone</label>
              <input v-model="editForm.phone" placeholder="Phone number" class="profile-input" />
            </div>
            <div class="profile-modal-field">
              <label>Verified Label</label>
              <input v-model="editForm.verified_label" placeholder="e.g. Verified Social Worker" class="profile-input" />
            </div>
            <div class="profile-modal-field">
              <label>Professional Title</label>
              <input v-model="editForm.professional_title" placeholder="e.g. Licensed Clinical Social Worker" class="profile-input" />
            </div>
          </div>
          <div class="profile-modal-footer">
            <button class="profile-cancel-btn" @click="cancelEdit">Cancel</button>
            <button class="profile-save-btn" :disabled="saving" @click="saveProfile">{{ saving ? 'Saving...' : 'Save' }}</button>
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
          <div v-if="repliesPages > 1" class="profile-pagination">
            <button :disabled="repliesPage <= 1" @click="fetchReplies(repliesPage - 1)">&laquo; Prev</button>
            <button v-for="p in repliesPages" :key="p" class="profile-page-btn" :class="{ 'is-active': p === repliesPage }" @click="fetchReplies(p)">{{ p }}</button>
            <button :disabled="repliesPage >= repliesPages" @click="fetchReplies(repliesPage + 1)">Next &raquo;</button>
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
          <div v-if="threadsPages > 1" class="profile-pagination">
            <button :disabled="threadsPage <= 1" @click="fetchThreads(threadsPage - 1)">&laquo; Prev</button>
            <button v-for="p in threadsPages" :key="p" class="profile-page-btn" :class="{ 'is-active': p === threadsPage }" @click="fetchThreads(p)">{{ p }}</button>
            <button :disabled="threadsPage >= threadsPages" @click="fetchThreads(threadsPage + 1)">Next &raquo;</button>
          </div>
        </template>

        <!-- Saved Tab -->
        <template v-if="activeTab === 'saved'">
          <div v-if="loadingSaved" class="cnw-social-worker-loading" style="padding:20px">Loading saved threads...</div>
          <div v-else-if="savedThreads.length === 0" class="profile-empty-state">
            <p class="profile-empty-title">No saved threads yet</p>
            <p class="profile-empty-desc">Threads you mark as helpful will appear here.</p>
          </div>
          <div v-else class="profile-cards-grid">
            <div v-for="thread in savedThreads" :key="thread.id" class="profile-content-card" @click="$router.push('/thread/' + thread.id)">
              <h4 class="profile-card-title">{{ thread.title }}</h4>
              <p class="profile-card-excerpt">{{ truncate(thread.content, 120) }}</p>
              <div class="profile-card-tags">
                <span v-for="tag in thread.tags" :key="tag" class="qcard-tag">{{ tag }}</span>
              </div>
              <p class="profile-card-meta">{{ formatDate(thread.created_at) }} &bull; {{ thread.saves_count || 0 }} helpful</p>
            </div>
          </div>
          <div v-if="savedPages > 1" class="profile-pagination">
            <button :disabled="savedPage <= 1" @click="fetchSaved(savedPage - 1)">&laquo; Prev</button>
            <button v-for="p in savedPages" :key="p" class="profile-page-btn" :class="{ 'is-active': p === savedPage }" @click="fetchSaved(p)">{{ p }}</button>
            <button :disabled="savedPage >= savedPages" @click="fetchSaved(savedPage + 1)">Next &raquo;</button>
          </div>
        </template>

        <!-- Activity Tab -->
        <template v-if="activeTab === 'activity'">
          <div v-if="loadingActivity" class="cnw-social-worker-loading" style="padding:20px">Loading activity...</div>
          <div v-else-if="activities.length === 0" class="profile-empty-state">
            <p class="profile-empty-title">No activity yet</p>
            <p class="profile-empty-desc">Your actions will be recorded here.</p>
          </div>
          <div v-else class="profile-activity-list">
            <div v-for="act in activities" :key="act.id" class="profile-activity-row">
              <div class="activity-icon" :class="'activity-icon--' + act.action_type">
                <svg v-if="act.action_type === 'login'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                <svg v-else-if="act.action_type === 'thread_created'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <svg v-else-if="act.action_type === 'reply_created'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4l-4 4V6c0-1.1.9-2 2-2z"/></svg>
                <svg v-else-if="act.action_type === 'voted' || act.action_type === 'received_vote'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"/></svg>
                <svg v-else-if="act.action_type === 'best_answer' || act.action_type === 'marked_solution'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                <svg v-else-if="act.action_type === 'thread_saved'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
              </div>
              <div class="activity-body">
                <p class="activity-desc">
                  {{ act.description }}
                  <router-link v-if="act.link" :to="act.link.replace('#', '')" class="activity-link">View &rarr;</router-link>
                </p>
                <div class="activity-meta">
                  <span class="activity-date">{{ formatDate(act.created_at) }}</span>
                  <span v-if="act.points > 0" class="activity-points activity-points--positive">+{{ act.points }} pts</span>
                  <span v-else-if="act.points < 0" class="activity-points activity-points--negative">{{ act.points }} pts</span>
                  <span v-else-if="act.reason" class="activity-reason">{{ act.reason }}</span>
                </div>
              </div>
            </div>
          </div>
          <div v-if="activityPages > 1" class="profile-pagination">
            <button :disabled="activityPage <= 1" @click="fetchActivity(activityPage - 1)">&laquo; Prev</button>
            <button v-for="p in activityPages" :key="p" class="profile-page-btn" :class="{ 'is-active': p === activityPage }" @click="fetchActivity(p)">{{ p }}</button>
            <button :disabled="activityPage >= activityPages" @click="fetchActivity(activityPage + 1)">Next &raquo;</button>
          </div>
        </template>
      </div>
    </template>
  </div>
</template>

<script>
import { getUser, getUserThreads, getUserReplies, getSavedThreads, getUserActivity, updateUserProfile, toggleAnonymous, uploadAvatar } from '@/api/index.js';

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
      savedThreads: [],
      loadingThreads: false,
      loadingReplies: false,
      loadingSaved: false,
      repliesPage: 1,
      repliesPages: 1,
      threadsPage: 1,
      threadsPages: 1,
      savedPage: 1,
      savedPages: 1,
      activities: [],
      loadingActivity: false,
      activityPage: 1,
      activityPages: 1,
      editing: false,
      saving: false,
      editForm: { first_name: '', last_name: '', phone: '', verified_label: '', professional_title: '' },
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
      if (tab === 'answers' && this.replies.length === 0) this.fetchReplies(1);
      if (tab === 'questions' && this.threads.length === 0) this.fetchThreads(1);
      if (tab === 'saved' && this.savedThreads.length === 0) this.fetchSaved(1);
      if (tab === 'activity' && this.activities.length === 0) this.fetchActivity(1);
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
    async fetchThreads(page) {
      if (page) this.threadsPage = page;
      this.loadingThreads = true;
      try {
        const data = await getUserThreads(this.userId, { page: this.threadsPage });
        this.threads = data.threads || [];
        this.threadsPages = data.pages || 1;
      } catch { /* silent */ }
      finally { this.loadingThreads = false; }
    },
    async fetchReplies(page) {
      if (page) this.repliesPage = page;
      this.loadingReplies = true;
      try {
        const data = await getUserReplies(this.userId, { page: this.repliesPage });
        this.replies = data.replies || [];
        this.repliesPages = data.pages || 1;
      } catch { /* silent */ }
      finally { this.loadingReplies = false; }
    },
    async fetchSaved(page) {
      if (page) this.savedPage = page;
      this.loadingSaved = true;
      try {
        const data = await getSavedThreads({ page: this.savedPage });
        this.savedThreads = data.threads || data || [];
        this.savedPages = data.pages || 1;
      } catch { /* silent */ }
      finally { this.loadingSaved = false; }
    },
    async fetchActivity(page) {
      if (page) this.activityPage = page;
      this.loadingActivity = true;
      try {
        const data = await getUserActivity({ page: this.activityPage });
        this.activities = data.activities || [];
        this.activityPages = data.pages || 1;
      } catch { /* silent */ }
      finally { this.loadingActivity = false; }
    },
    startEdit() {
      this.editForm.first_name = this.user.first_name || '';
      this.editForm.last_name = this.user.last_name || '';
      this.editForm.phone = this.user.phone || '';
      this.editForm.verified_label = this.user.verified_label || 'Verified Social Worker';
      this.editForm.professional_title = this.user.professional_title || 'Licensed Clinical Social Worker';
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
        this.user.verified_label = this.editForm.verified_label;
        this.user.professional_title = this.editForm.professional_title;
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
  justify-content: flex-end;
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
/* ── Edit Profile Modal ────────────────────────────────────── */
.profile-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}
.profile-modal {
  background: #fff;
  border-radius: var(--radius, 8px);
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}
.profile-modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: var(--space-s, 19.8px) var(--space-s, 19.8px) 0;
}
.profile-modal-header h3 {
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
  font-size: var(--text-m, 16px);
  color: #000;
  margin: 0;
}
.profile-modal-close {
  background: none;
  border: none;
  font-size: 24px;
  color: #999;
  cursor: pointer;
  line-height: 1;
  padding: 0;
}
.profile-modal-close:hover {
  color: #000;
}
.profile-modal-body {
  padding: var(--space-s, 19.8px);
  display: flex;
  flex-direction: column;
  gap: var(--space-xs, 14px);
}
.profile-modal-field {
  display: flex;
  flex-direction: column;
  gap: var(--space-4xs, 4.95px);
}
.profile-modal-field label {
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: var(--text-xs, 14px);
  color: #999;
}
.profile-modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: var(--space-2xs, 9.9px);
  padding: 0 var(--space-s, 19.8px) var(--space-s, 19.8px);
}

/* ── Tabs ───────────────────────────────────────────────────── */
.profile-tabs {
  display: flex;
  justify-content: center;
  gap: 0;
  background: var(--bg);
  border-radius: var(--radius-m);
  padding: var(--space-3xs);
  margin-top: var(--space-s);
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
  transition: color 0.15s, background 0.15s;
}
.profile-tab:hover {
  color: #000;
}
.profile-tab.is-active {
  color: #fff;
  background: var(--primary);
  border-radius: var(--radius-m);
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

/* ── Activity List ─────────────────────────────────────────── */
.profile-activity-list {
  display: flex;
  flex-direction: column;
  gap: 0;
}
.profile-activity-row {
  display: flex;
  align-items: flex-start;
  gap: var(--space-xs, 14px);
  padding: var(--space-xs, 14px) 0;
  border-bottom: 1px solid var(--border);
}
.profile-activity-row:last-child {
  border-bottom: none;
}
.activity-icon {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: var(--bg);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  color: var(--text-body, #414141);
}
.activity-icon--thread_created { background: #e3f2fd; color: #1976d2; }
.activity-icon--reply_created { background: #e8f5e9; color: #388e3c; }
.activity-icon--voted,
.activity-icon--received_vote { background: #fff3e0; color: #f57c00; }
.activity-icon--best_answer,
.activity-icon--marked_solution { background: #e8f5e9; color: #22a55b; }
.activity-icon--login { background: #f3e5f5; color: #7b1fa2; }
.activity-icon--thread_saved,
.activity-icon--thread_unsaved { background: #fce4ec; color: #c62828; }
.activity-icon--registered { background: #e0f7fa; color: #00838f; }
.activity-icon--vote_removed,
.activity-icon--vote_changed { background: #fff3e0; color: #f57c00; }
.activity-icon--thread_updated,
.activity-icon--reply_updated { background: #e3f2fd; color: #1976d2; }
.activity-icon--thread_deleted,
.activity-icon--reply_deleted { background: #ffebee; color: #c62828; }
.activity-icon--logout { background: #f3e5f5; color: #7b1fa2; }
.activity-icon--profile_updated,
.activity-icon--avatar_updated,
.activity-icon--anonymous_toggled { background: #e0f7fa; color: #00838f; }
.activity-icon--tag_followed,
.activity-icon--tag_unfollowed { background: #fff3e0; color: #f57c00; }
.activity-body {
  flex: 1;
  min-width: 0;
}
.activity-desc {
  font-family: 'Poppins', sans-serif;
  font-weight: 400;
  font-size: var(--text-xs, 14px);
  line-height: 20px;
  color: #000;
}
.activity-link {
  color: var(--primary);
  text-decoration: none;
  font-weight: 500;
  margin-left: 6px;
}
.activity-link:hover {
  text-decoration: underline;
}
.activity-meta {
  display: flex;
  margin-top: 4px;
}
.activity-date {
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: 12px;
  color: #999;
}
.activity-points {
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
  font-size: 12px;
  padding: 2px 8px;
  border-radius: 10px;
}
.activity-points--positive {
  background: #e8f5e9;
  color: #22a55b;
}
.activity-points--negative {
  background: #ffebee;
  color: #c62828;
}
.activity-reason {
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: 12px;
  color: #999;
  font-style: italic;
}

/* ── Pagination ────────────────────────────────────────────── */
.profile-pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: var(--space-4xs, 4.95px);
  margin-top: var(--space-s, 19.8px);
}
.profile-pagination button {
  padding: var(--space-3xs, 7px) var(--space-2xs, 9.9px);
  border: 1px solid var(--border);
  background: #fff;
  border-radius: var(--radius-s, 2px);
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: var(--text-xs, 14px);
  line-height: 16px;
  color: var(--text-body, #414141);
  cursor: pointer;
  min-width: 36px;
  text-align: center;
}
.profile-pagination button:hover:not(:disabled) {
  border-color: var(--primary);
  color: var(--primary);
}
.profile-pagination button:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}
.profile-page-btn.is-active {
  background: var(--primary);
  color: #fff;
  border-color: var(--primary);
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
  .profile-anon-row {
    justify-content: center;
  }
  .profile-info-grid {
    grid-template-columns: 1fr;
  }
  .profile-cards-grid {
    grid-template-columns: 1fr;
  }
  .profile-tabs {
    flex-direction: column;
    gap: var(--space-3xs, 7px);
    align-items: stretch;
  }
  .profile-tab {
    justify-content: center;
    padding: 10px 14px;
    font-size: 13px;
  }
  .profile-activity-row .activity-body {
    text-align: left;
  }
  .profile-activity-row .activity-meta {
    flex-direction: column;
    align-items: flex-start;
    gap: 4px;
  }
  .profile-activity-row .activity-link {
    display: block;
    margin-left: 0;
    margin-top: 4px;
  }
}
</style>
