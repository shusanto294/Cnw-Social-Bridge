<template>
  <div class="question-card">
    <!-- Card Header: avatar + meta + owner actions -->
    <div class="qcard-meta">
      <div class="qcard-meta-left">
        <span v-if="isAnonymous" class="qcard-anon-avatar" title="Anonymous">
          <svg aria-hidden="true" width="14" height="14" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.556 5.91c.504-.334.887-.822 1.093-1.391a2.97 2.97 0 0 0-.653-3.16A2.97 2.97 0 0 0 6 .747a2.97 2.97 0 0 0-1.68.555 2.97 2.97 0 0 0-1.016 1.448 2.97 2.97 0 0 0 1.14 3.16 4.47 4.47 0 0 0-3.114 4.185v.78c0 .1.04.195.11.265a.375.375 0 0 0 .265.11h8.19a.375.375 0 0 0 .375-.375v-.78a4.47 4.47 0 0 0-2.734-4.185zM6.259 5.25a.376.376 0 0 1-.529 0 .376.376 0 0 1 0-.533.375.375 0 0 1 .529 0 .376.376 0 0 1 0 .533zm.112-1.305v.191a.375.375 0 0 1-.75 0v-.491a.375.375 0 0 1 .375-.375.296.296 0 0 0 .296-.296.296.296 0 0 0-.296-.297.296.296 0 0 0-.3.3.375.375 0 0 1-.75 0 1.046 1.046 0 1 1 1.425.968z" fill="#fff"/></svg>
        </span>
        <template v-else>
          <router-link :to="'/users/' + thread.author_id" class="qcard-author-link" @click.stop>
            <img
              :src="avatarUrl"
              :alt="thread.author_name"
              class="cnw-social-worker-avatar qcard-avatar"
              width="34" height="34"
              loading="lazy"
            />
          </router-link>
          <router-link :to="'/users/' + thread.author_id" class="qcard-author qcard-author-link" @click.stop>{{ thread.author_name }}</router-link>
        </template>
        <span v-if="!isAnonymous" class="cnw-social-worker-verified" title="Verified" aria-label="Verified user">✓</span>
        <span v-if="!isAnonymous && thread.author_reputation" class="cnw-reputation-badge" :title="thread.author_reputation + ' reputation points'">
          <svg aria-hidden="true" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          {{ formatReputation(thread.author_reputation) }}
        </span>
        <span class="qcard-date">{{ formatDate(thread.created_at) }}</span>
      </div>
      <div class="qcard-meta-right">
        <button v-if="isLoggedIn" class="td-action-btn td-report-btn" @click.stop="showReportModal = true" title="Report" aria-label="Report this thread">
          <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
        </button>
      </div>
    </div>

    <!-- Title -->
    <h2 class="qcard-title" @click="open" @keydown.enter="open" role="link" tabindex="0" v-html="highlightText(thread.title)"></h2>
    <div v-if="isPinned || isClosed" class="thread-badges">
      <span v-if="isPinned" class="thread-badge badge-pinned">Pinned</span>
      <span v-if="isClosed" class="thread-badge badge-closed">Closed</span>
    </div>

    <!-- Excerpt -->
    <p v-if="!compact" class="qcard-excerpt" v-html="highlightText(truncate(thread.content, 200))"></p>

    <!-- Tags -->
    <div class="qcard-tags">
      <span v-for="tag in threadTags" :key="tag" class="qcard-tag">{{ tag }}</span>
    </div>

    <!-- Stats row 1: Upvote/Downvote + Helpful + Views -->
    <div class="qcard-stats-row">
      <button class="stat-btn vote-btn" :class="{ 'vote-active-up': userVote === 1 }" @click.stop="vote(1)" aria-label="Upvote" :aria-pressed="userVote === 1">
        <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3H14z"/><path d="M7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/></svg>
        <span>{{ localUpvotes }}</span>
        <span>Upvote</span>
      </button>
      <span class="stat-divider"></span>
      <button class="stat-btn vote-btn" :class="{ 'vote-active-down': userVote === -1 }" @click.stop="vote(-1)" aria-label="Downvote" :aria-pressed="userVote === -1">
        <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 15v4a3 3 0 0 0 3 3l4-9V2H5.72a2 2 0 0 0-2 1.7l-1.38 9a2 2 0 0 0 2 2.3H10z"/><path d="M17 2h3a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2h-3"/></svg>
        <span>{{ localDownvotes }}</span>
        <span>Downvote</span>
      </button>
      <span class="stat-divider"></span>
      <button class="stat-btn save-btn" :class="{ 'save-active': isSaved }" @click.stop="toggleSave" aria-label="Mark as helpful" :aria-pressed="isSaved">
        <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" :fill="isSaved ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
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
      <button class="stat-btn" @click.stop="toggleExpand" aria-label="Toggle replies" :aria-expanded="expanded">
      <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
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
        <span>{{ thread.reply_count || 0 }}</span>
        <span>Replies</span>
      </button>
      <span class="stat-divider"></span>
      <button class="stat-btn reply-link" @click.stop="toggleExpand" aria-label="Reply to thread" :aria-expanded="expanded">
        <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4l-4 4V6c0-1.1.9-2 2-2z"/></svg>
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
      <div v-if="loadingReplies" style="padding:12px;display:flex;flex-direction:column;gap:10px">
        <div v-for="n in 2" :key="n" class="cnw-skeleton-card" style="padding:10px;gap:8px">
          <div class="cnw-skeleton-row">
            <div class="cnw-skeleton cnw-skeleton-circle" style="width:24px;height:24px"></div>
            <div class="cnw-skeleton cnw-skeleton-line" style="width:25%"></div>
          </div>
          <div class="cnw-skeleton cnw-skeleton-line" style="width:85%"></div>
          <div class="cnw-skeleton cnw-skeleton-line-sm" style="width:40%"></div>
        </div>
      </div>
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
          :search-query="searchQuery"
          @reply-submitted="refreshReplies"
        />
        <!-- Closed thread notice -->
        <div v-if="isClosed" class="td-closed-notice">
          This thread has been closed by a moderator.
        </div>
        <!-- Write message box (logged-in only) -->
        <div v-else-if="isLoggedIn" class="inline-reply-form">
          <span v-if="isCurrentUserAnonymous" class="qcard-anon-avatar qcard-anon-avatar-sm">
            <svg aria-hidden="true" width="14" height="14" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.556 5.91c.504-.334.887-.822 1.093-1.391a2.97 2.97 0 0 0-.653-3.16A2.97 2.97 0 0 0 6 .747a2.97 2.97 0 0 0-1.68.555 2.97 2.97 0 0 0-1.016 1.448 2.97 2.97 0 0 0 1.14 3.16 4.47 4.47 0 0 0-3.114 4.185v.78c0 .1.04.195.11.265a.375.375 0 0 0 .265.11h8.19a.375.375 0 0 0 .375-.375v-.78a4.47 4.47 0 0 0-2.734-4.185zM6.259 5.25a.376.376 0 0 1-.529 0 .376.376 0 0 1 0-.533.375.375 0 0 1 .529 0 .376.376 0 0 1 0 .533zm.112-1.305v.191a.375.375 0 0 1-.75 0v-.491a.375.375 0 0 1 .375-.375.296.296 0 0 0 .296-.296.296.296 0 0 0-.296-.297.296.296 0 0 0-.3.3.375.375 0 0 1-.75 0 1.046 1.046 0 1 1 1.425.968z" fill="#fff"/></svg>
          </span>
          <img
            v-else
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
              id="qcard-reply-input"
              aria-label="Write a reply"
              @keydown.enter.ctrl.prevent="submitInlineReply"
            ></textarea>
            <div class="inline-reply-actions">
              <button
                class="inline-reply-send-btn"
                :disabled="!replyDraft.trim() || submitting"
                @click="submitInlineReply"
                aria-label="Submit reply"
              >Reply</button>
            </div>
          </div>
        </div>
        <div v-else-if="!isClosed" class="qcard-login-prompt">
          You must need to <a href="#" @click.prevent="openLoginModal">login</a> to add a reply.
        </div>
      </template>
    </div>

    <!-- Edit Modal -->
    <div v-if="showEditModal" class="td-modal-overlay" @click.self="closeEditModal" role="dialog" aria-modal="true" aria-labelledby="edit-thread-title">
      <div class="td-modal">
        <div class="td-modal-header">
          <h3 id="edit-thread-title">Edit Thread</h3>
          <button class="td-modal-close" @click="closeEditModal" aria-label="Close">
            <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
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
    <div v-if="showDeleteConfirm" class="td-modal-overlay" @click.self="showDeleteConfirm = false" role="dialog" aria-modal="true" aria-labelledby="delete-thread-title">
      <div class="td-modal td-modal-sm">
        <div class="td-modal-header">
          <h3 id="delete-thread-title">Delete Thread</h3>
          <button class="td-modal-close" @click="showDeleteConfirm = false" aria-label="Close">
            <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
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

    <!-- Report Modal -->
    <div v-if="showReportModal" class="td-modal-overlay" @click.self="showReportModal = false" role="dialog" aria-modal="true" aria-labelledby="report-thread-title">
      <div class="td-modal">
        <div class="td-modal-header">
          <h3 id="report-thread-title">Report Thread</h3>
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
          <button class="td-modal-save" :disabled="!reportType || !reportDescription.trim() || reportSending" @click="submitThreadReport" style="background:#e74c3c">
            {{ reportSending ? 'Submitting...' : 'Submit Report' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import ReplyCard from './ReplyCard.vue';
import { getReplies, createReply, createVote, saveThread, unsaveThread, updateThread, deleteThread, submitReport } from '@/api/index.js';

export default {
  name: 'QuestionCard',
  components: { ReplyCard },
  props: {
    thread: { type: Object, required: true },
    searchQuery: { type: String, default: '' },
    compact: { type: Boolean, default: false },
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
      showReportModal: false,
      reportType: '',
      reportDescription: '',
      reportSending: false,
    };
  },
  computed: {
    avatarUrl() {
      return this.thread.author_avatar || window.cnwData?.defaultAvatar || '';
    },
    currentUserAvatar() {
      const d = window.cnwData;
      return d?.currentUser?.avatar || window.cnwData?.defaultAvatar || '';
    },
    isCurrentUserAnonymous() {
      return !!(window.cnwData?.currentUser?.anonymous);
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
    isAnonymous() {
      return !!(this.thread.is_anonymous && parseInt(this.thread.is_anonymous));
    },
    isOwner() {
      const uid = window.cnwData?.currentUser?.id;
      return uid && String(this.thread.author_id) === String(uid);
    },
    isPinned() {
      return !!(parseInt(this.thread.is_pinned));
    },
    isClosed() {
      return !!(parseInt(this.thread.is_closed));
    },
  },
  mounted() {
    if (this.thread.has_reply_match && this.searchQuery) {
      this.expandReplies();
    }
  },
  methods: {
    openLoginModal() {
      window.dispatchEvent(new CustomEvent('cnw-open-login'));
    },
    open() {
      this.$router.push('/thread/' + this.thread.id);
    },
    async vote(type) {
      if (!this.isLoggedIn) { this.openLoginModal(); return; }
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
      if (!this.isLoggedIn) { this.openLoginModal(); return; }
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
        await this.loadRepliesData();
      }
    },
    async expandReplies() {
      if (this.expanded) return;
      this.expanded = true;
      if (this.replies.length === 0) {
        await this.loadRepliesData();
      }
    },
    async loadRepliesData() {
      this.loadingReplies = true;
      try {
        const data = await getReplies(this.thread.id);
        this.replies = data.replies || [];
      } catch (e) { /* silent */ } finally {
        this.loadingReplies = false;
      }
    },
    focusReplyBox() {
      this.$nextTick(() => this.$refs.replyInput?.focus());
    },
    async refreshReplies() {
      try {
        await this.loadRepliesData();
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
    async submitThreadReport() {
      if (!this.reportType || !this.reportDescription.trim()) return;
      this.reportSending = true;
      try {
        await submitReport({
          type: this.reportType,
          subject: 'Report: ' + this.thread.title,
          description: this.reportDescription,
          link: window.location.origin + window.location.pathname + '#/thread/' + this.thread.id,
          priority: 'medium',
          content_type: 'thread',
          content_id: this.thread.id,
        });
        this.showReportModal = false;
        this.reportType = '';
        this.reportDescription = '';
      } catch {} finally {
        this.reportSending = false;
      }
    },
    truncate(str, len) {
      if (!str) return '';
      return str.length > len ? str.slice(0, len) + '…' : str;
    },
    highlightText(text) {
      if (!text) return '';
      const q = this.searchQuery?.trim();
      if (!q) return this.escapeHtml(text);
      const escaped = this.escapeHtml(text);
      const regex = new RegExp('(' + q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
      return escaped.replace(regex, '<mark class="cnw-search-highlight">$1</mark>');
    },
    escapeHtml(str) {
      const div = document.createElement('div');
      div.textContent = str;
      return div.innerHTML;
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
.qcard-meta-right {
  margin-left: auto;
  flex-shrink: 0;
}
.qcard-meta-left img{
  width: 25px;
  height: 25px;
  object-fit: cover;
}
.qcard-avatar {
  border-radius: 50%;
}
.qcard-anon-avatar {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background: var(--primary, #3aa9da);
  flex-shrink: 0;
}
.qcard-anon-avatar-sm {
  width: 24px;
  height: 24px;
}
.qcard-anon-avatar-sm svg {
  width: 12px;
  height: 12px;
}
.qcard-author {
  font-size: var(--text-xs);
  font-weight: 300;
  color: #000;
  white-space: nowrap;
  line-height: 16px;
}
.qcard-author-link {
  text-decoration: none;
  color: inherit;
  cursor: pointer;
}
.qcard-author-link:hover {
  color: var(--primary);
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
.qcard-login-prompt {
  padding: var(--space-xs) var(--space-m);
  font-size: 0.92em;
  color: var(--text-secondary);
  margin-top: var(--space-xs);
}
.qcard-login-prompt a {
  color: var(--accent);
  font-weight: 600;
  text-decoration: none;
}
.qcard-login-prompt a:hover {
  text-decoration: underline;
}
.qcard-replies > .inline-reply-form {
  margin-top: var(--space-xs);
}
.inline-reply-form {
  display: flex;
  align-items: flex-start;
  gap: var(--space-2xs);
}

.inline-reply-form img{
  width: 22px;
  height: 22px;
  object-fit: cover;
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

@media (max-width: 760px) {
  .question-card {
    padding: var(--space-xs);
  }
  .qcard-meta {
    align-items: flex-start;
  }
  .qcard-meta-left {
    flex-wrap: wrap;
    flex: 1;
    min-width: 0;
  }
  .qcard-date {
    width: 100%;
    margin-top: 2px;
    white-space: normal;
  }
  .qcard-tag {
    padding: 3px 8px;
    font-size: 11px;
    line-height: 14px;
  }
  .qcard-tags {
    gap: var(--space-3xs);
  }
  .qcard-stats-row {
    flex-wrap: wrap;
    gap: var(--space-3xs);
  }
  .qcard-title {
    font-size: 16px;
    line-height: 22px;
  }
  .qcard-replies {
    padding-left: var(--space-xs);
  }
}

@media (max-width: 480px) {
  .question-card {
    padding: var(--space-2xs);
    gap: var(--space-3xs);
  }
  .qcard-meta-left {
    gap: var(--space-3xs);
  }
  .qcard-meta-right {
    gap: var(--space-3xs);
  }
  .qcard-date {
    font-size: 11px;
  }
  .qcard-title {
    font-size: 15px;
    line-height: 20px;
  }
  .qcard-excerpt {
    font-size: 13px;
  }
  .stat-btn span:last-child {
    display: none;
  }
  .stat-views-label {
    display: none;
  }
  .answered-badge span {
    font-size: 11px;
  }
  .qcard-replies {
    padding-left: var(--space-2xs);
  }
  .td-modal {
    width: auto;
    max-width: 95vw;
    margin: 0 8px;
  }
  .td-modal-sm {
    width: auto;
  }
}

/* Closed thread notice (matches ThreadDetailView) */
.qcard-replies .td-closed-notice {
  text-align: center;
  padding: var(--space-m);
  margin-top: 20px;
  color: #c62828;
  font-size: var(--text-xs);
  font-weight: 500;
  background: #ffebee;
  border-radius: var(--radius-m);
}
</style>
