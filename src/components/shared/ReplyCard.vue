<template>
  <div class="reply-card" :class="{ 'is-nested': depth > 0, 'is-last': isLast }">
    <!-- L-shaped connector line -->
    <div class="reply-connector">
      <div class="connector-vertical"></div>
      <div class="connector-horizontal"></div>
    </div>

    <!-- Reply content bubble -->
    <div class="reply-bubble">
      <!-- Header: avatar + name + verified + date + owner actions -->
      <div class="reply-header">
        <div class="reply-header-left">
          <div class="reply-user-info">
            <div class="reply-avatar-wrap">
              <img
                :src="avatarUrl"
                :alt="reply.author_name"
                class="cnw-social-worker-avatar reply-avatar"
                width="22" height="22"
              />
            </div>
            <span class="reply-author">{{ reply.author_name }}</span>
            <span class="cnw-social-worker-verified" title="Verified">✓</span>
          </div>
          <div class="reply-date-wrap">
            <span class="reply-date">{{ formatDate(reply.created_at) }}</span>
          </div>
        </div>
        <div v-if="isOwner" class="reply-owner-actions">
          <button class="td-action-btn td-edit-btn" @click="openEditModal" title="Edit">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          </button>
          <button class="td-action-btn td-delete-btn" @click="showDeleteConfirm = true" title="Delete">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
          </button>
        </div>
      </div>

      <!-- Body text -->
      <p class="reply-body">{{ localContent }}</p>

      <!-- Vote buttons -->
      <div class="reply-helpful">
        <button class="reply-stat-btn vote-btn" :class="{ 'vote-active-up': userVote === 1 }" @click="vote(1)" :disabled="!isLoggedIn || isOwner" :title="isOwner ? 'You cannot vote on your own content' : ''">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3H14z"/><path d="M7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/></svg>
          <span>{{ localUpvotes }}</span>
          <span>Upvote</span>
        </button>
        <span class="reply-divider"></span>
        <button class="reply-stat-btn vote-btn" :class="{ 'vote-active-down': userVote === -1 }" @click="vote(-1)" :disabled="!isLoggedIn || isOwner" :title="isOwner ? 'You cannot vote on your own content' : ''">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 15v4a3 3 0 0 0 3 3l4-9V2H5.72a2 2 0 0 0-2 1.7l-1.38 9a2 2 0 0 0 2 2.3H10z"/><path d="M17 2h3a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2h-3"/></svg>
          <span>{{ localDownvotes }}</span>
          <span>Downvote</span>
        </button>
        <span class="reply-divider"></span>
        <button class="reply-stat-btn helpful-btn" :class="{ 'helpful-active': localIsSolution }" @click="toggleSolution" :disabled="!isLoggedIn || markingSolution">
          <svg width="14" height="14" viewBox="0 0 24 24" :fill="localIsSolution ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
          <span>Helpful</span>
        </button>
      </div>

      <!-- Footer: Replies | Reply -->
      <div class="reply-footer">
        <button class="reply-stat-btn" @click="toggleNested">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          <span>{{ nestedReplies.length || reply.reply_count || 0 }}</span>
          <span>Replies</span>
        </button>
        <span class="reply-divider"></span>
        <button class="reply-stat-btn" @click="showReplyBox = !showReplyBox">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4l-4 4V6c0-1.1.9-2 2-2z"/></svg>
          <span>Reply</span>
        </button>
      </div>

      <!-- Inline reply box for THIS specific reply -->
      <div v-if="showReplyBox" class="reply-inline-form">
        <img
          :src="currentUserAvatar"
          class="cnw-social-worker-avatar"
          width="24" height="24"
          alt="You"
        />
        <div class="reply-inline-input-wrap">
          <textarea
            ref="replyInput"
            v-model="replyDraft"
            placeholder="Write Message:"
            class="reply-inline-input"
            rows="3"
          ></textarea>
          <div class="reply-inline-actions">
            <button
              class="reply-inline-send-btn"
              :disabled="!replyDraft.trim() || submitting"
              @click="submitReply"
            >Reply</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Reply Modal -->
    <div v-if="showEditModal" class="td-modal-overlay" @click.self="closeEditModal">
      <div class="td-modal">
        <div class="td-modal-header">
          <h3>Edit Reply</h3>
          <button class="td-modal-close" @click="closeEditModal">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div class="td-modal-body">
          <label class="td-modal-label">Content</label>
          <textarea v-model="editContent" class="td-modal-textarea" rows="6"></textarea>
        </div>
        <div class="td-modal-footer">
          <button class="td-modal-cancel" @click="closeEditModal">Cancel</button>
          <button class="td-modal-save" :disabled="!editContent.trim() || saving" @click="submitEdit">
            {{ saving ? 'Saving…' : 'Save Changes' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Delete Reply Confirmation -->
    <div v-if="showDeleteConfirm" class="td-modal-overlay" @click.self="showDeleteConfirm = false">
      <div class="td-modal td-modal-sm">
        <div class="td-modal-header">
          <h3>Delete Reply</h3>
          <button class="td-modal-close" @click="showDeleteConfirm = false">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div class="td-modal-body">
          <p class="td-delete-msg">Are you sure you want to delete this reply? This action cannot be undone.</p>
        </div>
        <div class="td-modal-footer">
          <button class="td-modal-cancel" @click="showDeleteConfirm = false">Cancel</button>
          <button class="td-modal-delete" :disabled="deleting" @click="confirmDelete">
            {{ deleting ? 'Deleting…' : 'Delete' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Nested replies -->
    <div v-if="nestedReplies.length" class="nested-replies">
      <ReplyCard
        v-for="(nr, idx) in nestedReplies"
        :key="nr.id"
        :reply="nr"
        :all-replies="allReplies"
        :depth="depth + 1"
        :is-last="idx === nestedReplies.length - 1"
        :thread-id="threadId"
        :thread-author-id="threadAuthorId"
        @reply-submitted="$emit('reply-submitted')"
      />
    </div>
  </div>
</template>

<script>
import { createReply, createVote, updateReply, deleteReply, markSolution } from '@/api/index.js';

export default {
  name: 'ReplyCard',
  props: {
    reply: { type: Object, required: true },
    allReplies: { type: Array, default: () => [] },
    depth: { type: Number, default: 0 },
    isLast: { type: Boolean, default: false },
    threadId: { type: [Number, String], default: 0 },
    threadAuthorId: { type: [Number, String], default: 0 },
  },
  emits: ['reply-submitted'],
  data() {
    return {
      userVote: this.reply.user_vote ? parseInt(this.reply.user_vote) : 0,
      localUpvotes: parseInt(this.reply.likes) || 0,
      localDownvotes: parseInt(this.reply.dislikes) || 0,
      isLoggedIn: !!(window.cnwData?.currentUser?.id > 0),
      showReplyBox: false,
      replyDraft: '',
      submitting: false,
      localContent: this.reply.content,
      showEditModal: false,
      editContent: '',
      saving: false,
      showDeleteConfirm: false,
      deleting: false,
      localIsSolution: !!(this.reply.is_solution && parseInt(this.reply.is_solution)),
      markingSolution: false,
    };
  },
  computed: {
    nestedReplies() {
      return this.allReplies.filter(r => String(r.parent_id) === String(this.reply.id));
    },
    avatarUrl() {
      return this.reply.author_avatar || `https://www.gravatar.com/avatar/?d=mp&s=30`;
    },
    currentUserAvatar() {
      const d = window.cnwData;
      return d?.currentUser?.avatar || 'https://www.gravatar.com/avatar/?d=mp&s=30';
    },
    isOwner() {
      const uid = window.cnwData?.currentUser?.id;
      return uid && String(this.reply.author_id) === String(uid);
    },
    isThreadOwner() {
      const uid = window.cnwData?.currentUser?.id;
      return uid && String(this.threadAuthorId) === String(uid);
    },
  },
  methods: {
    async toggleSolution() {
      if (this.markingSolution) return;
      this.markingSolution = true;
      try {
        const res = await markSolution(this.reply.id);
        if (res.success) {
          this.localIsSolution = res.is_solution;
          this.$emit('reply-submitted');
        }
      } catch { /* silent */ }
      finally { this.markingSolution = false; }
    },
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
        await createVote({ target_type: 'reply', target_id: this.reply.id, vote_type: type });
      } catch {
        this.userVote = prev;
        this.localUpvotes = prevUp;
        this.localDownvotes = prevDown;
      }
    },
    toggleNested() {
      // could toggle nested visibility in the future
    },
    async submitReply() {
      if (!this.replyDraft.trim()) return;
      this.submitting = true;
      try {
        await createReply({
          thread_id: this.threadId || this.reply.thread_id,
          content: this.replyDraft,
          parent_id: this.reply.id,
        });
        this.replyDraft = '';
        this.showReplyBox = false;
        this.$emit('reply-submitted');
      } catch (e) { /* silent */ } finally {
        this.submitting = false;
      }
    },
    openEditModal() {
      this.editContent = this.localContent;
      this.showEditModal = true;
    },
    closeEditModal() {
      this.showEditModal = false;
    },
    async submitEdit() {
      if (!this.editContent.trim()) return;
      this.saving = true;
      try {
        await updateReply(this.reply.id, { content: this.editContent });
        this.localContent = this.editContent;
        this.reply.content = this.editContent;
        this.showEditModal = false;
      } catch { /* silent */ }
      finally { this.saving = false; }
    },
    async confirmDelete() {
      this.deleting = true;
      try {
        await deleteReply(this.reply.id);
        this.showDeleteConfirm = false;
        this.$emit('reply-submitted');
      } catch { /* silent */ }
      finally { this.deleting = false; }
    },
    formatDate(d) {
      if (!d) return '';
      const date = new Date(d);
      return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
        + ' • ' + date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
    },
  },
};
</script>

<style>
/* ── Reply card layout ────────────────────────────────────────── */
.reply-card {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-start;
}

/* ── Top-level (depth 0): border-left = continuous teal line ──── */
.reply-card:not(.is-nested):not(.is-last) {
  border-left: 1px solid var(--primary);
  padding-bottom: var(--space-xs);
}
.reply-card:not(.is-nested).is-last {
  border-left: 1px solid transparent; /* keep alignment, no visible line */
}

/* ── Top-level connector ─────────────────────────────────────── */
/* Non-last: just horizontal arm (border-left provides vertical) */
.reply-card:not(.is-nested):not(.is-last) > .reply-connector {
  width: 30px;
  flex-shrink: 0;
  padding-top: 16px;
}
.reply-card:not(.is-nested):not(.is-last) > .reply-connector > .connector-vertical {
  display: none;
}
.reply-card:not(.is-nested):not(.is-last) > .reply-connector > .connector-horizontal {
  width: 30px;
  height: 1px;
  background: var(--primary);
}
/* Last: L-shape — vertical down then horizontal right */
.reply-card:not(.is-nested).is-last > .reply-connector {
  width: 30px;
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}
.reply-card:not(.is-nested).is-last > .reply-connector > .connector-vertical {
  display: block;
  width: 1px;
  height: 16px;
  background: var(--primary);
  flex-shrink: 0;
  margin-left: -1px;
}
.reply-card:not(.is-nested).is-last > .reply-connector > .connector-horizontal {
  width: 30px;
  height: 1px;
  background: var(--primary);
  margin-left: -1px;
}

/* ── Nested (depth > 0): border-left = green/secondary line ──── */
.reply-card.is-nested:not(.is-last) {
  border-left: 1px solid var(--secondary);
  padding-bottom: var(--space-xs);
}
.reply-card.is-nested.is-last {
  border-left: 1px solid transparent;
}

/* ── Nested connector ────────────────────────────────────────── */
/* Non-last: just horizontal arm (border-left provides vertical) */
.reply-card.is-nested:not(.is-last) > .reply-connector {
  width: 10px;
  flex-shrink: 0;
  padding-top: 16px;
}
.reply-card.is-nested:not(.is-last) > .reply-connector > .connector-vertical {
  display: none;
}
.reply-card.is-nested:not(.is-last) > .reply-connector > .connector-horizontal {
  width: 10px;
  height: 1px;
  background: var(--secondary);
}
/* Last nested: L-shape in secondary/green */
.reply-card.is-nested.is-last > .reply-connector {
  width: 10px;
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}
.reply-card.is-nested.is-last > .reply-connector > .connector-vertical {
  display: block;
  width: 1px;
  height: 16px;
  background: var(--secondary);
  flex-shrink: 0;
  margin-left: -1px;
}
.reply-card.is-nested.is-last > .reply-connector > .connector-horizontal {
  width: 10px;
  height: 1px;
  background: var(--secondary);
  margin-left: -1px;
}

/* ── Reply content bubble ─────────────────────────────────────── */
.reply-bubble {
  flex: 1;
  min-width: 0;
  background: var(--bg);
  border-radius: var(--radius-l);
  padding: var(--space-2xs) var(--space-m);
  display: flex;
  flex-direction: column;
  gap: var(--space-xs);
}

/* ── Header ───────────────────────────────────────────────────── */
.reply-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--space-2xs);
}
.reply-header-left {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: var(--space-2xs);
  min-width: 0;
}
.reply-user-info {
  display: flex;
  align-items: center;
  gap: var(--space-2xs);
  flex-shrink: 0;
}
.reply-date-wrap {
  display: flex;
  align-items: center;
}
.reply-owner-actions {
  display: flex;
  gap: var(--space-3xs);
  flex-shrink: 0;
}
.reply-avatar-wrap {
  padding: var(--space-4xs);
  border-radius: 50%;
  flex-shrink: 0;
}
.reply-avatar {
  border-radius: 50%;
}
.reply-author {
  font-size: var(--text-xs);
  font-weight: 300;
  color: #000;
  white-space: nowrap;
  line-height: 16px;
}
.reply-date {
  font-size: var(--text-xs);
  font-weight: 300;
  color: #999;
  line-height: 16px;
}

/* ── Body text ────────────────────────────────────────────────── */
.reply-body {
  font-size: var(--text-xs);
  font-weight: 300;
  color: #000;
  line-height: 16px;
  padding-left: var(--space-m);
}

/* ── Helpful stat ─────────────────────────────────────────────── */
.reply-helpful {
  display: flex;
  align-items: center;
  gap: var(--space-2xs);
  padding-left: var(--space-m);
}

/* ── Footer stats ─────────────────────────────────────────────── */
.reply-footer {
  display: flex;
  align-items: center;
  gap: var(--space-2xs);
  padding-left: var(--space-m);
}

/* Shared stat button for reply card */
.reply-stat-btn {
  background: none;
  border: none;
  font-size: var(--text-xs);
  font-weight: 300;
  color: var(--text-body);
  display: inline-flex;
  align-items: center;
  gap: var(--space-4xs);
  padding: 0;
  line-height: 16px;
  white-space: nowrap;
  cursor: pointer;
  transition: color 0.12s;
}
.reply-stat-btn:hover {
  color: var(--text-dark);
}

/* Teal vertical divider */
.reply-divider {
  width: 2px;
  align-self: stretch;
  background: var(--primary);
  flex-shrink: 0;
}

/* ── Helpful (heart) button ───────────────────────────────────── */
.helpful-btn:hover {
  color: #e74c3c;
}
.helpful-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.helpful-active {
  color: #e74c3c !important;
}

/* ── Inline reply form (inside the bubble) ────────────────────── */
.reply-inline-form {
  display: flex;
  align-items: flex-start;
  gap: var(--space-2xs);
  padding-left: var(--space-m);
}
.reply-inline-input-wrap {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: var(--space-3xs);
  background: var(--white);
  border-radius: var(--radius-m);
  padding: var(--space-2xs) var(--space-xs);
  border: 1px solid var(--border);
}
.reply-inline-input {
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
.reply-inline-input:focus {
  outline: none;
  color: var(--text-body);
}
.reply-inline-actions {
  display: flex;
  justify-content: flex-end;
}
.reply-inline-send-btn {
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
.reply-inline-send-btn:hover {
  background: var(--secondary);
}
.reply-inline-send-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* ── Nested replies — indentation, primary border continues ──── */
.nested-replies {
  width: 100%;
  display: flex;
  flex-direction: column;
  padding-left: 79px;
  padding-top: var(--space-xs);
}

@media (max-width: 760px) {
  .nested-replies {
    padding-left: 30px;
  }
  .reply-bubble {
    padding: var(--space-2xs) var(--space-xs);
  }
  .reply-header {
    align-items: flex-start;
  }
  .reply-header-left {
    flex: 1;
    min-width: 0;
  }
  .reply-body {
    padding-left: var(--space-xs);
  }
  .reply-helpful,
  .reply-footer,
  .reply-inline-form {
    padding-left: var(--space-xs);
  }
}

@media (max-width: 480px) {
  .nested-replies {
    padding-left: 16px;
  }
  .reply-bubble {
    padding: var(--space-3xs) var(--space-2xs);
    gap: var(--space-3xs);
  }
  .reply-header {
    flex-wrap: wrap;
    gap: var(--space-3xs);
  }
  .reply-date {
    font-size: 11px;
  }
  .reply-body,
  .reply-helpful,
  .reply-footer,
  .reply-inline-form {
    padding-left: var(--space-2xs);
  }
  .reply-stat-btn span:last-child {
    display: none;
  }
}
</style>
