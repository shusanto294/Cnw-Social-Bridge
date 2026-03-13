<template>
  <div class="reply-card" :class="{ 'is-nested': depth > 0, 'is-last': isLast }">
    <!-- L-shaped connector line -->
    <div class="reply-connector">
      <div class="connector-vertical"></div>
      <div class="connector-horizontal"></div>
    </div>

    <!-- Reply content bubble -->
    <div
      class="reply-bubble"
      :class="{ 'reply-best-answer': localIsSolution, 'reply-has-actions': isOwner || canModerate, 'reply-highlighted': isHighlighted }"
      :ref="isHighlighted ? 'highlightedReply' : undefined"
    >
      <!-- Header: avatar + name + verified + date + owner actions -->
      <div class="reply-header">
        <div class="reply-header-left">
          <div class="reply-user-info">
            <div class="reply-avatar-wrap">
              <span v-if="isAnonymous" class="reply-anon-avatar" title="Anonymous">
                <svg aria-hidden="true" width="14" height="14" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.556 5.91c.504-.334.887-.822 1.093-1.391a2.97 2.97 0 0 0-.653-3.16A2.97 2.97 0 0 0 6 .747a2.97 2.97 0 0 0-1.68.555 2.97 2.97 0 0 0-1.016 1.448 2.97 2.97 0 0 0 1.14 3.16 4.47 4.47 0 0 0-3.114 4.185v.78c0 .1.04.195.11.265a.375.375 0 0 0 .265.11h8.19a.375.375 0 0 0 .375-.375v-.78a4.47 4.47 0 0 0-2.734-4.185zM6.259 5.25a.376.376 0 0 1-.529 0 .376.376 0 0 1 0-.533.375.375 0 0 1 .529 0 .376.376 0 0 1 0 .533zm.112-1.305v.191a.375.375 0 0 1-.75 0v-.491a.375.375 0 0 1 .375-.375.296.296 0 0 0 .296-.296.296.296 0 0 0-.296-.297.296.296 0 0 0-.3.3.375.375 0 0 1-.75 0 1.046 1.046 0 1 1 1.425.968z" fill="#fff"/></svg>
              </span>
              <router-link v-else :to="'/users/' + reply.author_id" class="qcard-author-link">
                <img
                  :src="avatarUrl"
                  :alt="reply.author_name"
                  class="cnw-social-worker-avatar reply-avatar"
                  width="22" height="22"
                />
              </router-link>
            </div>
            <router-link v-if="!isAnonymous" :to="'/users/' + reply.author_id" class="reply-author qcard-author-link">{{ reply.author_name }}</router-link>
            <span v-else class="reply-author">{{ reply.author_name }}</span>
            <span v-if="!isAnonymous" class="cnw-social-worker-verified" title="Verified" aria-label="Verified user">✓</span>
            <span v-if="!isAnonymous && reply.author_reputation" class="cnw-reputation-badge" :title="reply.author_reputation + ' reputation points'">
              <svg aria-hidden="true" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
              {{ formatReputation(reply.author_reputation) }}
            </span>
          </div>
          <div class="reply-date-wrap">
            <span class="reply-date">{{ formatDate(reply.created_at) }}</span>
          </div>
        </div>
        <span v-if="localIsSolution" class="reply-best-answer-badge">
          <svg aria-hidden="true" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
          Best Answer
        </span>
        <div class="reply-actions">
          <button v-if="isLoggedIn" class="td-action-btn td-report-btn reply-report-btn" @click="showReportModal = true" title="Report" aria-label="Report this reply">
            <svg aria-hidden="true" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
          </button>
          <button v-if="isOwner || canModerate" class="td-action-btn td-edit-btn" @click="openEditModal" title="Edit" aria-label="Edit this reply">
            <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          </button>
          <button v-if="isOwner || canModerate" class="td-action-btn td-delete-btn" @click="showDeleteConfirm = true" title="Delete" aria-label="Delete this reply">
            <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
          </button>
        </div>
      </div>

      <!-- Body text -->
      <p class="reply-body" v-html="highlightedContent"></p>

      <!-- Vote buttons -->
      <div class="reply-helpful">
        <button class="reply-stat-btn vote-btn" :class="{ 'vote-active-up': userVote === 1 }" @click="vote(1)" aria-label="Upvote" :aria-pressed="userVote === 1">
          <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3H14z"/><path d="M7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/></svg>
          <span>{{ localUpvotes }}</span>
          <span>Upvote</span>
        </button>
        <span class="reply-divider"></span>
        <button class="reply-stat-btn vote-btn" :class="{ 'vote-active-down': userVote === -1 }" @click="vote(-1)" aria-label="Downvote" :aria-pressed="userVote === -1">
          <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 15v4a3 3 0 0 0 3 3l4-9V2H5.72a2 2 0 0 0-2 1.7l-1.38 9a2 2 0 0 0 2 2.3H10z"/><path d="M17 2h3a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2h-3"/></svg>
          <span>{{ localDownvotes }}</span>
          <span>Downvote</span>
        </button>
        <template v-if="canAccept || localIsSolution">
          <span class="reply-divider"></span>
          <button class="reply-stat-btn accept-btn" :class="{ 'accept-active': localIsSolution }" @click="toggleSolution" :disabled="!canAccept || markingSolution" :title="acceptTooltip" :aria-label="acceptTooltip" :aria-pressed="localIsSolution">
            <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" :fill="localIsSolution ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            <span>{{ localIsSolution ? 'Accepted' : 'Accept' }}</span>
          </button>
        </template>
      </div>

      <!-- Footer: Replies | Reply -->
      <div class="reply-footer">
        <button class="reply-stat-btn" @click="toggleNested" aria-label="Toggle nested replies">
          <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          <span>{{ nestedReplies.length || reply.reply_count || 0 }}</span>
          <span>Replies</span>
        </button>
        <span class="reply-divider"></span>
        <button class="reply-stat-btn" @click="handleReplyClick" aria-label="Reply to this comment" :aria-expanded="showReplyBox">
          <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4l-4 4V6c0-1.1.9-2 2-2z"/></svg>
          <span>Reply</span>
        </button>
      </div>

      <!-- Login prompt for non-logged-in users -->
      <div v-if="showReplyBox && !isLoggedIn" class="reply-login-prompt">
        You must need to <a href="#" @click.prevent="openLoginModal">login</a> to add a reply.
      </div>

      <!-- Inline reply box for THIS specific reply -->
      <div v-if="showReplyBox && isLoggedIn" class="reply-inline-form">
        <span v-if="isCurrentUserAnonymous" class="reply-anon-avatar">
          <svg aria-hidden="true" width="14" height="14" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.556 5.91c.504-.334.887-.822 1.093-1.391a2.97 2.97 0 0 0-.653-3.16A2.97 2.97 0 0 0 6 .747a2.97 2.97 0 0 0-1.68.555 2.97 2.97 0 0 0-1.016 1.448 2.97 2.97 0 0 0 1.14 3.16 4.47 4.47 0 0 0-3.114 4.185v.78c0 .1.04.195.11.265a.375.375 0 0 0 .265.11h8.19a.375.375 0 0 0 .375-.375v-.78a4.47 4.47 0 0 0-2.734-4.185zM6.259 5.25a.376.376 0 0 1-.529 0 .376.376 0 0 1 0-.533.375.375 0 0 1 .529 0 .376.376 0 0 1 0 .533zm.112-1.305v.191a.375.375 0 0 1-.75 0v-.491a.375.375 0 0 1 .375-.375.296.296 0 0 0 .296-.296.296.296 0 0 0-.296-.297.296.296 0 0 0-.3.3.375.375 0 0 1-.75 0 1.046 1.046 0 1 1 1.425.968z" fill="#fff"/></svg>
        </span>
        <img
          v-else
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
            aria-label="Write a reply"
          ></textarea>
          <div class="reply-inline-actions">
            <button
              class="reply-inline-send-btn"
              :disabled="!replyDraft.trim() || submitting"
              @click="submitReply"
              aria-label="Submit reply"
            >Reply</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Reply Modal -->
    <div v-if="showEditModal" class="td-modal-overlay" @click.self="closeEditModal" role="dialog" aria-modal="true" aria-labelledby="edit-reply-title">
      <div class="td-modal">
        <div class="td-modal-header">
          <h3 id="edit-reply-title">Edit Reply</h3>
          <button class="td-modal-close" @click="closeEditModal" aria-label="Close">
            <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
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
    <div v-if="showDeleteConfirm" class="td-modal-overlay" @click.self="showDeleteConfirm = false" role="dialog" aria-modal="true" aria-labelledby="delete-reply-title">
      <div class="td-modal td-modal-sm">
        <div class="td-modal-header">
          <h3 id="delete-reply-title">Delete Reply</h3>
          <button class="td-modal-close" @click="showDeleteConfirm = false" aria-label="Close">
            <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
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

    <!-- Report Reply Modal -->
    <div v-if="showReportModal" class="td-modal-overlay" @click.self="showReportModal = false" role="dialog" aria-modal="true" aria-labelledby="report-reply-title">
      <div class="td-modal">
        <div class="td-modal-header">
          <h3 id="report-reply-title">Report Reply</h3>
          <button class="td-modal-close" @click="showReportModal = false" aria-label="Close">
            <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div class="td-modal-body">
          <label class="td-modal-label">Report Type</label>
          <select v-model="reportType" class="td-modal-input">
            <option value="">Select a reason...</option>
            <option value="inappropriate_content">Inappropriate Content</option>
            <option value="harassment">Harassment or Bullying</option>
            <option value="spam">Spam or Self-Promotion</option>
            <option value="confidentiality">Confidentiality Violation</option>
            <option value="misinformation">Misinformation</option>
            <option value="other">Other</option>
          </select>
          <label class="td-modal-label">Description</label>
          <textarea v-model="reportDescription" class="td-modal-textarea" rows="4" placeholder="Describe the issue..."></textarea>
        </div>
        <div class="td-modal-footer">
          <button class="td-modal-cancel" @click="showReportModal = false">Cancel</button>
          <button class="td-modal-save" :disabled="!reportType || !reportDescription.trim() || reportSending" @click="submitReplyReport" style="background:#e74c3c">
            {{ reportSending ? 'Submitting...' : 'Submit Report' }}
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
        :search-query="searchQuery"
        :highlight-reply-id="highlightReplyId"
        @reply-submitted="$emit('reply-submitted')"
      />
    </div>
  </div>
</template>

<script>
import { createReply, createVote, updateReply, deleteReply, markSolution, submitReport } from '@/api/index.js';

export default {
  name: 'ReplyCard',
  props: {
    reply: { type: Object, required: true },
    allReplies: { type: Array, default: () => [] },
    depth: { type: Number, default: 0 },
    isLast: { type: Boolean, default: false },
    threadId: { type: [Number, String], default: 0 },
    threadAuthorId: { type: [Number, String], default: 0 },
    highlightReplyId: { type: [Number, String], default: 0 },
    searchQuery: { type: String, default: '' },
  },
  emits: ['reply-submitted'],
  mounted() {
    if (this.isHighlighted) {
      this.$nextTick(() => {
        setTimeout(() => {
          const el = this.$refs.highlightedReply;
          if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
          }
        }, 300);
      });
    }
  },
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
      showReportModal: false,
      reportType: '',
      reportDescription: '',
      reportSending: false,
      canModerate: !!(window.cnwData?.currentUser?.canModerate),
    };
  },
  computed: {
    highlightedContent() {
      const text = this.localContent || '';
      const q = this.searchQuery?.trim();
      if (!q) return this.escapeHtml(text);
      const escaped = this.escapeHtml(text);
      const regex = new RegExp('(' + q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
      return escaped.replace(regex, '<mark class="cnw-search-highlight">$1</mark>');
    },
    isHighlighted() {
      return this.highlightReplyId && String(this.highlightReplyId) === String(this.reply.id);
    },
    nestedReplies() {
      return this.allReplies.filter(r => String(r.parent_id) === String(this.reply.id));
    },
    isAnonymous() {
      return !!(this.reply.is_anonymous && parseInt(this.reply.is_anonymous));
    },
    avatarUrl() {
      return this.reply.author_avatar || window.cnwData?.defaultAvatar || '';
    },
    currentUserAvatar() {
      const d = window.cnwData;
      return d?.currentUser?.avatar || window.cnwData?.defaultAvatar || '';
    },
    isCurrentUserAnonymous() {
      return !!(window.cnwData?.currentUser?.anonymous);
    },
    isOwner() {
      const uid = window.cnwData?.currentUser?.id;
      return uid && String(this.reply.author_id) === String(uid);
    },
    isThreadOwner() {
      const uid = window.cnwData?.currentUser?.id;
      return uid && String(this.threadAuthorId) === String(uid);
    },
    isReplyByThreadOwner() {
      return String(this.reply.author_id) === String(this.threadAuthorId);
    },
    canAccept() {
      if (!this.isLoggedIn || !this.isThreadOwner) return false;
      if (this.localIsSolution) return true; // always allow undo
      return !this.isReplyByThreadOwner;
    },
    acceptTooltip() {
      if (this.localIsSolution) return 'Undo — remove best answer';
      if (!this.isThreadOwner) return 'Only the question author can accept an answer';
      if (this.isReplyByThreadOwner) return 'You cannot accept your own answer';
      return 'Accept as best answer (+25 rep)';
    },
  },
  methods: {
    escapeHtml(str) {
      const div = document.createElement('div');
      div.textContent = str;
      return div.innerHTML;
    },
    handleReplyClick() {
      this.showReplyBox = !this.showReplyBox;
    },
    openLoginModal() {
      window.dispatchEvent(new CustomEvent('cnw-open-login'));
    },
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
      if (!this.isLoggedIn) { this.openLoginModal(); return; }
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
    async submitReplyReport() {
      if (!this.reportType || !this.reportDescription.trim()) return;
      this.reportSending = true;
      try {
        await submitReport({
          type: this.reportType,
          subject: 'Report: Reply by ' + this.reply.author_name,
          description: this.reportDescription,
          link: window.location.href,
          priority: 'medium',
          content_type: 'reply',
          content_id: this.reply.id,
        });
        this.showReportModal = false;
        this.reportType = '';
        this.reportDescription = '';
      } catch {} finally {
        this.reportSending = false;
      }
    },
    formatDate(d) {
      if (!d) return '';
      const date = new Date(d);
      return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
        + ' • ' + date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
    },
    formatReputation(n) {
      if (!n) return '0';
      n = Number(n);
      if (n >= 1000) return (n / 1000).toFixed(1).replace(/\.0$/, '') + 'k';
      return n.toString();
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

/* ── Reply highlighted (reported) ─────────────────────────────── */
.reply-bubble.reply-highlighted {
  background-color: #fff0f0 !important;
  border: 2px solid #e74c3c !important;
  box-shadow: 0 0 12px rgba(231, 76, 60, 0.25);
  animation: reply-highlight-pulse 2s ease-in-out 2;
}
@keyframes reply-highlight-pulse {
  0%, 100% { background-color: #fff0f0; }
  50% { background-color: #ffe0e0; }
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
  position: relative;
}

/* ── Header ───────────────────────────────────────────────────── */
.reply-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--space-2xs);
}
.reply-actions {
  display: flex;
  align-items: center;
  gap: var(--space-3xs);
  flex-shrink: 0;
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
.reply-avatar-wrap img{
  width: 22px;
  height: 22px;
  object-fit: cover;
}
.reply-avatar {
  border-radius: 50%;
}
.reply-anon-avatar {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: var(--primary, #3aa9da);
  flex-shrink: 0;
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

/* ── Accept (checkmark) button ────────────────────────────────── */
.accept-btn:hover {
  color: #22a55b;
}
.accept-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.accept-active {
  color: #22a55b !important;
  font-weight: 500;
}

/* ── Best Answer badge ───────────────────────────────────────── */
.reply-best-answer-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: #e8f5e9;
  color: #22a55b;
  font-size: 11px;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 10px;
  white-space: nowrap;
  line-height: 16px;
  flex-shrink: 0;
}

/* ── Best Answer bubble highlight ────────────────────────────── */
.reply-bubble.reply-best-answer {
  border: 1.5px solid #22a55b;
  background: #f6fef8;
}

.reply-login-prompt {
  padding: var(--space-xs) var(--space-m);
  font-size: 0.92em;
  color: var(--text-secondary);
}
.reply-login-prompt a {
  color: var(--accent);
  font-weight: 600;
  text-decoration: none;
}
.reply-login-prompt a:hover {
  text-decoration: underline;
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

.reply-report-btn {
  flex-shrink: 0;
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
    padding-bottom: var(--space-xs);
  }
  .reply-bubble.reply-has-actions {
    padding-bottom: 28px;
  }
  .reply-bubble.reply-best-answer {
    padding-bottom: 28px;
  }
  .reply-bubble.reply-has-actions.reply-best-answer {
    padding-bottom: 50px;
  }
  .reply-header {
    align-items: flex-start;
  }
  .reply-header-left {
    flex: 1;
    min-width: 0;
  }
  .reply-best-answer-badge {
    position: absolute;
    bottom: 6px;
    right: 8px;
  }
  .reply-owner-actions {
    position: absolute;
    bottom: 6px;
    right: 8px;
  }
  /* When both badge and actions exist, stack actions above badge */
  .reply-bubble.reply-best-answer .reply-owner-actions {
    bottom: 28px;
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
  .reply-bubble.reply-has-actions {
    padding-bottom: 26px;
  }
  .reply-bubble.reply-best-answer {
    padding-bottom: 26px;
  }
  .reply-bubble.reply-has-actions.reply-best-answer {
    padding-bottom: 48px;
  }
  .reply-header {
    flex-wrap: wrap;
    gap: var(--space-3xs);
  }
  .reply-best-answer-badge {
    position: absolute;
    bottom: 5px;
    right: 6px;
    font-size: 10px;
    padding: 2px 6px;
  }
  .reply-owner-actions {
    position: absolute;
    bottom: 5px;
    right: 6px;
  }
  .reply-bubble.reply-best-answer .reply-owner-actions {
    bottom: 26px;
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
