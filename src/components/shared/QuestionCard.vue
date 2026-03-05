<template>
  <div class="question-card">
    <!-- Card Header: avatar + meta + owner actions -->
    <div class="qcard-meta">
      <div class="qcard-meta-left">
        <img
          :src="avatarUrl"
          :alt="thread.author_name"
          class="cnw-social-worker-avatar qcard-avatar"
          width="34" height="34"
        />
        <span class="qcard-author">{{ thread.author_name }}</span>
        <span class="cnw-social-worker-verified" title="Verified">✓</span>
      </div>
      <div class="qcard-meta-right">
        <span class="qcard-date">{{ formatDate(thread.created_at) }}</span>
        <div v-if="isOwner" class="td-owner-actions">
          <button class="td-action-btn td-edit-btn" @click.stop="openEditModal" title="Edit">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          </button>
          <button class="td-action-btn td-delete-btn" @click.stop="showDeleteConfirm = true" title="Delete">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Title -->
    <h2 class="qcard-title" @click="open">{{ thread.title }}</h2>

    <!-- Excerpt -->
    <p class="qcard-excerpt">{{ truncate(thread.content, 200) }}</p>

    <!-- Tags -->
    <div class="qcard-tags">
      <span v-for="tag in threadTags" :key="tag" class="qcard-tag">{{ tag }}</span>
    </div>

    <!-- Stats row 1: Upvote/Downvote + Views -->
    <div class="qcard-stats-row">
      <button class="stat-btn vote-btn" :class="{ 'vote-active-up': userVote === 1 }" @click.stop="vote(1)" :disabled="!isLoggedIn">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3H14z"/><path d="M7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/></svg>
        <span>{{ localUpvotes }}</span>
        <span>Upvote</span>
      </button>
      <button class="stat-btn vote-btn" :class="{ 'vote-active-down': userVote === -1 }" @click.stop="vote(-1)" :disabled="!isLoggedIn">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 15v4a3 3 0 0 0 3 3l4-9V2H5.72a2 2 0 0 0-2 1.7l-1.38 9a2 2 0 0 0 2 2.3H10z"/><path d="M17 2h3a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2h-3"/></svg>
        <span>{{ localDownvotes }}</span>
        <span>Downvote</span>
      </button>
      <button class="stat-btn save-btn" :class="{ 'save-active': isSaved }" @click.stop="toggleSave" :disabled="!isLoggedIn">
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
      <button class="stat-btn" @click.stop="toggleExpand">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        <span>{{ thread.reply_count || 0 }}</span>
        <span>Replies</span>
      </button>
      <span class="stat-divider"></span>
      <button class="stat-btn reply-link" @click.stop="toggleExpand">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4l-4 4V6c0-1.1.9-2 2-2z"/></svg>
        <span>Reply</span>
      </button>
      <span class="stat-divider"></span>
      <span class="answered-badge" :class="isAnswered ? 'is-answered' : 'is-unanswered'">
        <svg v-if="isAnswered" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
          <circle cx="7" cy="7" r="7" fill="#24F95D"/>
        </svg>

        <svg v-else width="14" height="14" viewBox="0 0 14 14"><circle cx="7" cy="7" r="6" fill="none" stroke="var(--text-light)" stroke-width="2"/></svg>
        <span>{{ isAnswered ? 'answered' : 'unanswered' }}</span>
      </span>
    </div>

    <!-- Inline replies (expanded) -->
    <div v-if="expanded" class="qcard-replies">
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
          @reply-submitted="refreshReplies"
        />
        <!-- Write message box -->
        <div class="inline-reply-form">
          <img
            :src="currentUserAvatar"
            class="cnw-social-worker-avatar"
            width="24" height="24"
            alt="You"
          />
          <div class="inline-reply-input-wrap">
            <textarea
              ref="replyInput"
              v-model="replyDraft"
              placeholder="Write Message:"
              class="inline-reply-input"
              rows="3"
              @keydown.enter.ctrl.prevent="submitInlineReply"
            ></textarea>
            <div class="inline-reply-actions">
              <button
                class="inline-reply-send-btn"
                :disabled="!replyDraft.trim() || submitting"
                @click="submitInlineReply"
              >Reply</button>
            </div>
          </div>
        </div>
      </template>
    </div>

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
import ReplyCard from './ReplyCard.vue';
import { getReplies, createReply, createVote, saveThread, unsaveThread, updateThread, deleteThread } from '@/api/index.js';

export default {
  name: 'QuestionCard',
  components: { ReplyCard },
  props: {
    thread: { type: Object, required: true },
  },
  emits: ['open', 'deleted'],
  data() {
    return {
      userVote: this.thread.user_vote ? parseInt(this.thread.user_vote) : 0,
      localUpvotes: parseInt(this.thread.likes) || 0,
      localDownvotes: parseInt(this.thread.dislikes) || 0,
      expanded: false,
      replies: [],
      loadingReplies: false,
      replyDraft: '',
      submitting: false,
      isLoggedIn: !!(window.cnwData?.currentUser?.id > 0),
      isSaved: !!(this.thread.is_saved && parseInt(this.thread.is_saved)),
      localSavesCount: parseInt(this.thread.saves_count) || 0,
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
      const id = this.thread.author_id || 0;
      return `https://www.gravatar.com/avatar/${id}?d=identicon&s=34`;
    },
    currentUserAvatar() {
      const d = window.cnwData;
      return d?.currentUser?.avatar || 'https://www.gravatar.com/avatar/?d=mp&s=30';
    },
    threadTags() {
      return this.thread.tags || [];
    },
    isAnswered() {
      return this.replies.length > 0 || this.thread.reply_count > 0;
    },
    topLevelReplies() {
      return this.replies.filter(r => !r.parent_id || r.parent_id === '0' || r.parent_id === 0);
    },
    isOwner() {
      const uid = window.cnwData?.currentUser?.id;
      return uid && String(this.thread.author_id) === String(uid);
    },
  },
  methods: {
    open() {
      this.$router.push('/thread/' + this.thread.id);
    },
    async vote(type) {
      if (!this.isLoggedIn) return;
      const prev = this.userVote;
      // Optimistic update
      if (prev === type) {
        // Toggle off
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
        // Revert on error
        this.userVote = prev;
        this.localUpvotes = parseInt(this.thread.likes) || 0;
        this.localDownvotes = parseInt(this.thread.dislikes) || 0;
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
    async toggleExpand() {
      this.expanded = !this.expanded;
      if (this.expanded && this.replies.length === 0) {
        this.loadingReplies = true;
        try {
          const data = await getReplies(this.thread.id);
          this.replies = data.replies || [];
        } catch (e) { /* silent */ } finally {
          this.loadingReplies = false;
        }
      }
    },
    focusReplyBox() {
      this.$nextTick(() => this.$refs.replyInput?.focus());
    },
    async refreshReplies() {
      try {
        const data = await getReplies(this.thread.id);
        this.replies = data.replies || [];
      } catch (e) { /* silent */ }
    },
    async submitInlineReply() {
      if (!this.replyDraft.trim()) return;
      this.submitting = true;
      try {
        await createReply({ thread_id: this.thread.id, content: this.replyDraft });
        this.replyDraft = '';
        const data = await getReplies(this.thread.id);
        this.replies = data.replies || [];
      } catch (e) { /* silent */ } finally {
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
      } catch (e) { /* silent */ }
      finally { this.saving = false; }
    },
    async confirmDelete() {
      this.deleting = true;
      try {
        await deleteThread(this.thread.id);
        this.showDeleteConfirm = false;
        this.$emit('deleted', this.thread.id);
      } catch (e) { /* silent */ }
      finally { this.deleting = false; }
    },
    truncate(str, len) {
      if (!str) return '';
      return str.length > len ? str.slice(0, len) + '…' : str;
    },
    formatDate(d) {
      if (!d) return '';
      const date = new Date(d);
      return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
        + ' • ' + date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
    },
    formatNum(n) {
      if (!n) return '0';
      return Number(n).toLocaleString();
    },
  },
};
</script>

<style>
/* ── Card container ──────────────────────────────────────────── */
.question-card {
  background: var(--white);
  border-radius: var(--radius-m);
  padding: var(--space-m);
  border: var(--radius-xs) solid var(--primary);
  display: flex;
  flex-direction: column;
  gap: var(--space-xs);
}

/* ── Header: avatar + name + verified + date ─────────────────── */
.qcard-meta {
  display: flex;
  align-items: center;
  gap: var(--space-xs);
}
.qcard-meta-left {
  display: flex;
  align-items: center;
  gap: var(--space-2xs);
}
.qcard-avatar {
  border-radius: 50%;
}
.qcard-author {
  font-size: var(--text-xs);
  font-weight: 300;
  color: #000;
  white-space: nowrap;
  line-height: 16px;
}
.qcard-date {
  font-size: var(--text-xs);
  font-weight: 300;
  color: #999;
  white-space: nowrap;
  line-height: 16px;
}

/* ── Title ────────────────────────────────────────────────────── */
.qcard-title {
  font-size: 18px;
  font-weight: 600;
  color: #000;
  line-height: 24.5px;
  cursor: pointer;
}
.qcard-title:hover {
  color: var(--teal-dark);
}

/* ── Excerpt ──────────────────────────────────────────────────── */
.qcard-excerpt {
  font-size: var(--text-m);
  font-weight: 300;
  color: #000;
  line-height: 1.32;
}

/* ── Tags — gradient badges ───────────────────────────────────── */
.qcard-tags {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-2xs);
}
.qcard-tag {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: var(--space-3xs) var(--space-2xs);
  background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
  color: var(--white);
  font-size: var(--text-xs);
  font-weight: 300;
  line-height: 16px;
  border-radius: var(--radius-xs);
  white-space: nowrap;
}

/* ── Stats rows ───────────────────────────────────────────────── */
.qcard-stats-row {
  display: flex;
  align-items: center;
  gap: var(--space-2xs);
}

.stat-btn {
  background: none;
  border: none;
  font-size: var(--text-xs);
  font-weight: 300;
  color: var(--text-body);
  display: inline-flex;
  align-items: center;
  gap: var(--space-4xs);
  padding: 0;
  padding-bottom: var(--space-4xs);
  line-height: 16px;
  white-space: nowrap;
  transition: color 0.12s;
}
.stat-btn:hover {
  color: var(--text-dark);
}
.vote-btn:hover {
  color: var(--secondary);
}
.vote-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.vote-active-up {
  color: var(--secondary) !important;
}
.vote-active-up svg {
  fill: var(--secondary);
  stroke: var(--secondary);
}
.vote-active-down {
  color: var(--red) !important;
}
.vote-active-down svg {
  fill: var(--red);
  stroke: var(--red);
}

/* Save/love button */
.save-btn:hover {
  color: #e74c3c;
}
.save-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.save-active {
  color: #e74c3c !important;
}

/* Teal vertical divider */
.stat-divider {
  width: 2px;
  align-self: stretch;
  background: var(--primary);
  flex-shrink: 0;
}

/* Views */
.stat-views {
  display: inline-flex;
  align-items: center;
  gap: var(--space-4xs);
  font-size: var(--text-xs);
  font-weight: 300;
  line-height: 16px;
  white-space: nowrap;
}
.stat-views-label {
  color: #999;
}
.stat-views-value {
  color: var(--text-body);
}

/* Reply link with underline */
.reply-link {
  color: var(--text-body);
  border-bottom: var(--radius-xs) solid var(--primary);
}
.reply-link:hover {
  color: var(--teal-dark);
}

/* Answered badge — simple dot + text */
.answered-badge {
  display: inline-flex;
  align-items: center;
  gap: var(--space-4xs);
  font-size: var(--text-xs);
  font-weight: 300;
  color: var(--text-body);
  line-height: 16px;
  white-space: nowrap;
  padding-bottom: var(--space-4xs);
}

/* ── Inline replies ───────────────────────────────────────────── */
.qcard-replies {
  display: flex;
  flex-direction: column;
  padding-left: var(--space-m);
}

/* Write message box — matches Figma */
.qcard-replies > .inline-reply-form {
  margin-top: var(--space-xs);
}
.inline-reply-form {
  display: flex;
  align-items: flex-start;
  gap: var(--space-2xs);
}
.inline-reply-input-wrap {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: var(--space-3xs);
  background: var(--bg);
  border-radius: var(--radius-m);
  padding: var(--space-2xs) var(--space-xs);
}
.inline-reply-input {
  width: 100%;
  border: none;
  font-size: var(--text-xs);
  font-weight: 300;
  font-family: inherit;
  color: #999;
  background: transparent;
  resize: none;
  line-height: 16px;
}
.inline-reply-input:focus {
  outline: none;
  color: var(--text-body);
}
.inline-reply-actions {
  display: flex;
  justify-content: flex-end;
}
.inline-reply-send-btn {
  background: var(--primary);
  color: var(--white);
  border: none;
  padding: var(--space-3xs) var(--space-s);
  border-radius: var(--radius-xs);
  font-size: var(--text-xs);
  font-weight: 300;
  font-family: inherit;
  line-height: 16px;
  cursor: pointer;
}
.inline-reply-send-btn:hover {
  background: var(--secondary);
}
.inline-reply-send-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
