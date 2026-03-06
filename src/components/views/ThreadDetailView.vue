<template>
  <div class="thread-detail-view">
    <button class="back-btn" @click="$router.push('/')">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="15 18 9 12 15 6"/></svg>
      Back to Questions
    </button>

    <div v-if="loading" class="cnw-social-worker-loading">Loading…</div>
    <NotFoundView v-else-if="notFound" message="This thread does not exist or has been removed." />

    <template v-else>
      <!-- Thread post — matches QuestionCard design -->
      <div class="question-card">
        <!-- Header: avatar + name + verified + date + owner actions -->
        <div class="qcard-meta">
          <div class="qcard-meta-left">
            <span v-if="isAnonymous" class="qcard-anon-avatar" title="Anonymous">
              <svg width="14" height="14" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.556 5.91c.504-.334.887-.822 1.093-1.391a2.97 2.97 0 0 0-.653-3.16A2.97 2.97 0 0 0 6 .747a2.97 2.97 0 0 0-1.68.555 2.97 2.97 0 0 0-1.016 1.448 2.97 2.97 0 0 0 1.14 3.16 4.47 4.47 0 0 0-3.114 4.185v.78c0 .1.04.195.11.265a.375.375 0 0 0 .265.11h8.19a.375.375 0 0 0 .375-.375v-.78a4.47 4.47 0 0 0-2.734-4.185zM6.259 5.25a.376.376 0 0 1-.529 0 .376.376 0 0 1 0-.533.375.375 0 0 1 .529 0 .376.376 0 0 1 0 .533zm.112-1.305v.191a.375.375 0 0 1-.75 0v-.491a.375.375 0 0 1 .375-.375.296.296 0 0 0 .296-.296.296.296 0 0 0-.296-.297.296.296 0 0 0-.3.3.375.375 0 0 1-.75 0 1.046 1.046 0 1 1 1.425.968z" fill="#fff"/></svg>
            </span>
            <img
              v-else
              :src="avatarUrl"
              :alt="thread.author_name"
              class="cnw-social-worker-avatar qcard-avatar"
              width="34" height="34"
            />
            <span class="qcard-author">{{ thread.author_name }}</span>
            <span v-if="!isAnonymous" class="cnw-social-worker-verified" title="Verified">✓</span>
            <span class="qcard-date">{{ formatDate(thread.created_at) }}</span>
          </div>
          <div class="qcard-meta-right">
            <div v-if="isOwner" class="td-owner-actions">
              <button class="td-action-btn td-edit-btn" @click="openEditModal" title="Edit">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              </button>
              <button class="td-action-btn td-delete-btn" @click="showDeleteConfirm = true" title="Delete">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Title -->
        <h2 class="qcard-title">{{ thread.title }}</h2>

        <!-- Content (full, not truncated) -->
        <p class="qcard-excerpt">{{ thread.content }}</p>

        <!-- Tags — gradient badges -->
        <div class="qcard-tags">
          <span v-for="tag in threadTags" :key="tag" class="qcard-tag">{{ tag }}</span>
        </div>

        <!-- Stats row 1: Upvote/Downvote + Helpful + Views -->
        <div class="qcard-stats-row">
          <button class="stat-btn vote-btn" :class="{ 'vote-active-up': userVote === 1 }" @click="vote(1)" :disabled="!isLoggedIn || isOwner" :title="isOwner ? 'You cannot vote on your own content' : ''">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3H14z"/><path d="M7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/></svg>
            <span>{{ localUpvotes }}</span>
            <span>Upvote</span>
          </button>
          <span class="stat-divider"></span>
          <button class="stat-btn vote-btn" :class="{ 'vote-active-down': userVote === -1 }" @click="vote(-1)" :disabled="!isLoggedIn || isOwner" :title="isOwner ? 'You cannot vote on your own content' : ''">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 15v4a3 3 0 0 0 3 3l4-9V2H5.72a2 2 0 0 0-2 1.7l-1.38 9a2 2 0 0 0 2 2.3H10z"/><path d="M17 2h3a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2h-3"/></svg>
            <span>{{ localDownvotes }}</span>
            <span>Downvote</span>
          </button>
          <span class="stat-divider"></span>
          <button class="stat-btn save-btn" :class="{ 'save-active': isSaved }" @click="toggleSave" :disabled="!isLoggedIn">
            <svg width="14" height="14" viewBox="0 0 24 24" :fill="isSaved ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            <span>{{ localSavesCount }}</span>
            <span>Helpful</span>
          </button>
          <span class="stat-divider"></span>
          <span class="stat-views">
            <span class="stat-views-label">Views</span>
            <span class="stat-views-value">{{ formatNum(thread.views) }}</span>
          </span>
        </div>

        <!-- Stats row 2: Replies + Reply + Answered -->
        <div class="qcard-stats-row">
          <button class="stat-btn">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
            <g clip-path="url(#clip0_20026_7995)">
              <path d="M9.06276 6.21553L9.05248 1.2279C9.05108 0.55084 8.49909 0 7.82201 0H1.23047C0.551988 0 0 0.551988 0 1.23044V6.20687C0 6.88532 0.551988 7.43731 1.23047 7.43731H2.07813V8.25759C2.07813 8.59482 2.4643 8.78825 2.73438 8.58572L4.26508 7.43772L7.82857 7.44849C8.52048 7.44849 9.06415 6.89106 9.06276 6.21553ZM4.51172 5.76939C4.2852 5.76939 4.10156 5.58575 4.10156 5.35924C4.10156 5.13272 4.2852 4.94908 4.51172 4.94908C4.73823 4.94908 4.92188 5.13272 4.92188 5.35924C4.92188 5.58575 4.73823 5.76939 4.51172 5.76939ZM4.94922 4.05844V4.10145C4.94922 4.32797 4.76558 4.51161 4.53906 4.51161C4.31255 4.51161 4.12891 4.32797 4.12891 4.10145V3.84554C4.12891 3.57465 4.33024 3.34203 4.59722 3.30444C4.7979 3.27619 4.94922 3.10163 4.94922 2.89833C4.94922 2.59943 4.61984 2.34314 4.24298 2.58429C4.05212 2.70635 3.79851 2.65059 3.67645 2.45982C3.55439 2.26901 3.61011 2.01537 3.80092 1.89328C4.22393 1.62269 4.72049 1.59477 5.12933 1.81852C5.5242 2.03473 5.76953 2.44847 5.76953 2.89836C5.76953 3.42497 5.43028 3.88746 4.94922 4.05844Z" fill="#3AA9DA"/>
              <path d="M12.7695 5.76929H9.88214L9.88307 6.21373C9.88515 7.22644 9.15988 8.04822 8.23058 8.23017H11.9219C12.1484 8.23017 12.332 8.41381 12.332 8.64033C12.332 8.86684 12.1484 9.05048 11.9219 9.05048H7C6.77348 9.05048 6.58984 8.86684 6.58984 8.64033C6.58984 8.47312 6.69006 8.32954 6.83356 8.26569L4.94922 8.25997V11.9488C4.94922 12.6273 5.50121 13.1793 6.17969 13.1793H8.95396L10.508 13.9563C10.7799 14.0922 11.1016 13.8942 11.1016 13.5894V13.1793H12.7695C13.448 13.1793 14 12.6273 14 11.9488V6.99973C14 6.32125 13.448 5.76929 12.7695 5.76929ZM11.9219 10.691H7C6.77348 10.691 6.58984 10.5074 6.58984 10.2809C6.58984 10.0544 6.77348 9.87071 7 9.87071H11.9219C12.1484 9.87071 12.332 10.0544 12.332 10.2809C12.332 10.5074 12.1484 10.691 11.9219 10.691Z" fill="#3AA9DA"/>
            </g>
            <defs>
              <clipPath id="clip0_20026_7995">
                <rect width="14" height="14" fill="white"/>
              </clipPath>
            </defs>
          </svg>
            <span>{{ replies.length }}</span>
            <span>Replies</span>
          </button>
          <span class="stat-divider"></span>
          <button class="stat-btn reply-link" @click="focusReplyBox">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4l-4 4V6c0-1.1.9-2 2-2z"/></svg>
            <span>Reply</span>
          </button>
          <span class="stat-divider"></span>
          <span class="answered-badge" :class="replies.length > 0 ? 'is-answered' : 'is-unanswered'">
            <svg v-if="replies.length > 0" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
              <circle cx="7" cy="7" r="7" fill="#24F95D"/>
            </svg>
            <svg v-else width="14" height="14" viewBox="0 0 14 14"><circle cx="7" cy="7" r="6" fill="none" stroke="var(--text-light)" stroke-width="2"/></svg>
            <span>{{ replies.length > 0 ? 'answered' : 'unanswered' }}</span>
          </span>
        </div>

        <!-- Replies section — same as QuestionCard expanded -->
        <div class="qcard-replies">
          <div v-if="loadingReplies" class="cnw-social-worker-loading" style="padding:16px">Loading replies…</div>
          <template v-else>
            <ReplyCard
              v-for="(reply, idx) in topLevelReplies"
              :key="reply.id"
              :reply="reply"
              :all-replies="replies"
              :depth="0"
              :is-last="idx === topLevelReplies.length - 1"
              :thread-id="thread.id"
              :thread-author-id="thread.author_id"
              @reply-submitted="refreshReplies"
            />
          </template>

          <!-- Write message box -->
          <div v-if="isLoggedIn" class="inline-reply-form">
            <img
              :src="currentUserAvatar"
              class="cnw-social-worker-avatar"
              width="24" height="24"
              alt="You"
            />
            <div class="inline-reply-input-wrap">
              <textarea
                ref="replyBox"
                v-model="replyContent"
                placeholder="Write Message:"
                class="inline-reply-input"
                rows="3"
                @keydown.enter.ctrl.prevent="submitReply"
              ></textarea>
              <div class="inline-reply-actions">
                <button
                  class="inline-reply-send-btn"
                  :disabled="!replyContent.trim() || submitting"
                  @click="submitReply"
                >{{ submitting ? 'Posting…' : 'Reply' }}</button>
              </div>
            </div>
          </div>
          <div v-else class="td-login-prompt">
            Please <a href="/wp-login.php">log in</a> to reply.
          </div>
        </div>
      </div>
    </template>

    <!-- Edit Modal -->
    <div v-if="showEditModal" class="td-modal-overlay" @click.self="closeEditModal">
      <div class="td-modal">
        <div class="td-modal-header">
          <h3>Edit Thread</h3>
          <button class="td-modal-close" @click="closeEditModal">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div class="td-modal-body">
          <label class="td-modal-label">Title</label>
          <input v-model="editTitle" class="td-modal-input" type="text" />
          <label class="td-modal-label">Content</label>
          <textarea v-model="editContent" class="td-modal-textarea" rows="6"></textarea>
        </div>
        <div class="td-modal-footer">
          <button class="td-modal-cancel" @click="closeEditModal">Cancel</button>
          <button class="td-modal-save" :disabled="!editTitle.trim() || !editContent.trim() || saving" @click="submitEdit">
            {{ saving ? 'Saving…' : 'Save Changes' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation -->
    <div v-if="showDeleteConfirm" class="td-modal-overlay" @click.self="showDeleteConfirm = false">
      <div class="td-modal td-modal-sm">
        <div class="td-modal-header">
          <h3>Delete Thread</h3>
          <button class="td-modal-close" @click="showDeleteConfirm = false">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div class="td-modal-body">
          <p class="td-delete-msg">Are you sure you want to delete this thread? This action cannot be undone.</p>
        </div>
        <div class="td-modal-footer">
          <button class="td-modal-cancel" @click="showDeleteConfirm = false">Cancel</button>
          <button class="td-modal-delete" :disabled="deleting" @click="confirmDelete">
            {{ deleting ? 'Deleting…' : 'Delete' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import ReplyCard from '@/components/shared/ReplyCard.vue';
import NotFoundView from '@/components/views/NotFoundView.vue';
import { getThread, getReplies, createReply, createVote, saveThread, unsaveThread, updateThread, deleteThread } from '@/api/index.js';

export default {
  name: 'ThreadDetailView',
  components: { ReplyCard, NotFoundView },
  data() {
    return {
      thread: null,
      notFound: false,
      replies: [],
      loading: true,
      loadingReplies: false,
      userVote: 0,
      localUpvotes: 0,
      localDownvotes: 0,
      replyContent: '',
      submitting: false,
      isLoggedIn: !!(window.cnwData?.currentUser?.id > 0),
      isSaved: false,
      localSavesCount: 0,
      showEditModal: false,
      editTitle: '',
      editContent: '',
      saving: false,
      showDeleteConfirm: false,
      deleting: false,
    };
  },
  computed: {
    avatarUrl() {
      return this.thread?.author_avatar || `https://www.gravatar.com/avatar/?d=mp&s=34`;
    },
    currentUserAvatar() {
      return window.cnwData?.currentUser?.avatar || 'https://www.gravatar.com/avatar/?d=mp&s=30';
    },
    topLevelReplies() {
      return this.replies.filter(r => !r.parent_id || r.parent_id === '0' || r.parent_id === 0);
    },
    threadTags() {
      return this.thread?.tags || [];
    },
    isAnonymous() {
      return !!(this.thread?.is_anonymous && parseInt(this.thread.is_anonymous));
    },
    isOwner() {
      const uid = window.cnwData?.currentUser?.id;
      return uid && this.thread && String(this.thread.author_id) === String(uid);
    },
  },
  async mounted() {
    const id = this.$route.params.id;
    try {
      const result = await getThread(id);
      if (!result || result.code || !result.id) {
        this.notFound = true;
      } else {
        this.thread = result;
        this.localUpvotes = parseInt(this.thread.likes) || 0;
        this.localDownvotes = parseInt(this.thread.dislikes) || 0;
        this.userVote = this.thread.user_vote ? parseInt(this.thread.user_vote) : 0;
        this.isSaved = !!(this.thread.is_saved && parseInt(this.thread.is_saved));
        this.localSavesCount = parseInt(this.thread.saves_count) || 0;
      }
    } catch (e) {
      this.notFound = true;
    } finally {
      this.loading = false;
    }

    if (this.notFound) return;

    this.loadingReplies = true;
    try {
      const data = await getReplies(id);
      this.replies = data.replies || [];
    } catch (e) { /* silent */ } finally {
      this.loadingReplies = false;
    }
  },
  methods: {
    async vote(type) {
      if (!this.isLoggedIn || this.isOwner) return;
      const prev = this.userVote;
      const prevUp = this.localUpvotes;
      const prevDown = this.localDownvotes;
      if (prev === type) {
        this.userVote = 0;
        if (type === 1) this.localUpvotes--;
        else this.localDownvotes--;
      } else {
        this.userVote = type;
        if (type === 1) {
          this.localUpvotes++;
          if (prev === -1) this.localDownvotes--;
        } else {
          this.localDownvotes++;
          if (prev === 1) this.localUpvotes--;
        }
      }
      try {
        await createVote({ target_type: 'thread', target_id: this.thread.id, vote_type: type });
      } catch {
        this.userVote = prev;
        this.localUpvotes = prevUp;
        this.localDownvotes = prevDown;
      }
    },
    async toggleSave() {
      if (!this.isLoggedIn) return;
      const prev = this.isSaved;
      const prevCount = this.localSavesCount;
      this.isSaved = !prev;
      this.localSavesCount += this.isSaved ? 1 : -1;
      try {
        if (this.isSaved) {
          await saveThread(this.thread.id);
        } else {
          await unsaveThread(this.thread.id);
        }
      } catch {
        this.isSaved = prev;
        this.localSavesCount = prevCount;
      }
    },
    focusReplyBox() {
      this.$nextTick(() => this.$refs.replyBox?.focus());
    },
    async refreshReplies() {
      try {
        const data = await getReplies(this.$route.params.id);
        this.replies = data.replies || [];
      } catch (e) { /* silent */ }
    },
    async submitReply() {
      if (!this.replyContent.trim()) return;
      this.submitting = true;
      try {
        await createReply({ thread_id: this.$route.params.id, content: this.replyContent });
        this.replyContent = '';
        const data = await getReplies(this.$route.params.id);
        this.replies = data.replies || [];
      } catch (e) {
        console.error(e);
      } finally {
        this.submitting = false;
      }
    },
    openEditModal() {
      this.editTitle = this.thread.title;
      this.editContent = this.thread.content;
      this.showEditModal = true;
    },
    closeEditModal() {
      this.showEditModal = false;
    },
    async submitEdit() {
      if (!this.editTitle.trim() || !this.editContent.trim()) return;
      this.saving = true;
      try {
        await updateThread(this.thread.id, { title: this.editTitle, content: this.editContent });
        this.thread.title = this.editTitle;
        this.thread.content = this.editContent;
        this.showEditModal = false;
      } catch (e) {
        console.error(e);
      } finally {
        this.saving = false;
      }
    },
    async confirmDelete() {
      this.deleting = true;
      try {
        await deleteThread(this.thread.id);
        this.showDeleteConfirm = false;
        this.$router.push('/');
      } catch (e) {
        console.error(e);
      } finally {
        this.deleting = false;
      }
    },
    formatDate(d) {
      if (!d) return '';
      const date = new Date(d);
      return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
        + ' • ' + date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
    },
    formatNum(n) {
      return n ? Number(n).toLocaleString() : '0';
    },
  },
};
</script>

<style>
.thread-detail-view {
  display: flex;
  flex-direction: column;
  gap: var(--space-xs);
}

.back-btn {
  display: inline-flex;
  align-items: center;
  gap: var(--space-4xs);
  background: none;
  border: none;
  color: var(--primary);
  font-size: var(--text-xs);
  font-weight: 300;
  padding: 0;
  align-self: flex-start;
  line-height: 16px;
  cursor: pointer;
}
.back-btn:hover {
  color: var(--teal-dark);
}

/* Login prompt inside replies area */
.td-login-prompt {
  text-align: center;
  padding: var(--space-m);
  color: #999;
  font-size: var(--text-xs);
  font-weight: 300;
  line-height: 16px;
}
.td-login-prompt a {
  color: var(--primary);
}

/* ── Meta right: date + owner actions ────────────────────────── */
.qcard-meta-right {
  display: flex;
  align-items: center;
  gap: var(--space-2xs);
  margin-left: auto;
}
.td-owner-actions {
  display: flex;
  gap: var(--space-3xs);
}
.td-action-btn {
  background: none;
  border: 1px solid var(--border, #e0e0e0);
  border-radius: var(--radius-xs);
  padding: var(--space-3xs);
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: var(--text-body);
  transition: all 0.15s;
}
.td-edit-btn:hover {
  color: var(--primary);
  border-color: var(--primary);
}
.td-delete-btn:hover {
  color: #e74c3c;
  border-color: #e74c3c;
}

/* ── Modal overlay ───────────────────────────────────────────── */
.td-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}
.td-modal {
  background: var(--white, #fff);
  border-radius: var(--radius-m, 8px);
  width: 520px;
  max-width: 92vw;
  max-height: 85vh;
  overflow-y: auto;
  box-shadow: 0 8px 32px rgba(0,0,0,0.18);
  display: flex;
  flex-direction: column;
}
.td-modal-sm {
  width: 400px;
}
.td-modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: var(--space-s) var(--space-m);
  border-bottom: 1px solid var(--border, #e0e0e0);
}
.td-modal-header h3 {
  font-size: 16px;
  font-weight: 600;
  color: var(--text-dark);
  margin: 0;
}
.td-modal-close {
  background: none;
  border: none;
  color: var(--text-light, #999);
  cursor: pointer;
  padding: 0;
  display: flex;
}
.td-modal-close:hover {
  color: var(--text-dark);
}
.td-modal-body {
  padding: var(--space-m);
  display: flex;
  flex-direction: column;
  gap: var(--space-xs);
}
.td-modal-label {
  font-size: var(--text-xs, 14px);
  font-weight: 500;
  color: var(--text-dark);
}
.td-modal-input {
  width: 100%;
  padding: var(--space-2xs) var(--space-xs);
  border: 1px solid var(--border, #e0e0e0);
  border-radius: var(--radius-xs, 4px);
  font-size: var(--text-xs, 14px);
  font-family: inherit;
  color: var(--text-body);
  box-sizing: border-box;
}
.td-modal-input:focus, .td-modal-textarea:focus {
  outline: none;
  border-color: var(--primary);
}
.td-modal-textarea {
  width: 100%;
  padding: var(--space-2xs) var(--space-xs);
  border: 1px solid var(--border, #e0e0e0);
  border-radius: var(--radius-xs, 4px);
  font-size: var(--text-xs, 14px);
  font-family: inherit;
  color: var(--text-body);
  resize: vertical;
  box-sizing: border-box;
}
.td-modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: var(--space-2xs);
  padding: var(--space-s) var(--space-m);
  border-top: 1px solid var(--border, #e0e0e0);
}
.td-modal-cancel {
  background: none;
  border: 1px solid var(--border, #e0e0e0);
  border-radius: var(--radius-xs, 4px);
  padding: var(--space-3xs) var(--space-s);
  font-size: var(--text-xs, 14px);
  font-family: inherit;
  color: var(--text-body);
  cursor: pointer;
}
.td-modal-cancel:hover {
  background: var(--bg, #f5f5f5);
}
.td-modal-save {
  background: var(--primary);
  color: var(--white, #fff);
  border: none;
  border-radius: var(--radius-xs, 4px);
  padding: var(--space-3xs) var(--space-s);
  font-size: var(--text-xs, 14px);
  font-family: inherit;
  cursor: pointer;
}
.td-modal-save:hover {
  background: var(--secondary);
}
.td-modal-save:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.td-modal-delete {
  background: #e74c3c;
  color: #fff;
  border: none;
  border-radius: var(--radius-xs, 4px);
  padding: var(--space-3xs) var(--space-s);
  font-size: var(--text-xs, 14px);
  font-family: inherit;
  cursor: pointer;
}
.td-modal-delete:hover {
  background: #c0392b;
}
.td-modal-delete:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.td-delete-msg {
  font-size: var(--text-xs, 14px);
  color: var(--text-body);
  line-height: 1.5;
  margin: 0;
}

@media (max-width: 480px) {
  .td-modal {
    width: auto;
    max-width: 95vw;
    margin: 0 8px;
  }
  .td-modal-sm {
    width: auto;
  }
}
</style>
