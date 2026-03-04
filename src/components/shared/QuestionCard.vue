<template>
  <div class="question-card">
    <!-- Card Header: avatar + meta -->
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
      <span class="qcard-date">{{ formatDate(thread.created_at) }}</span>
    </div>

    <!-- Title -->
    <h2 class="qcard-title" @click="open">{{ thread.title }}</h2>

    <!-- Excerpt -->
    <p class="qcard-excerpt">{{ truncate(thread.content, 200) }}</p>

    <!-- Tags -->
    <div class="qcard-tags">
      <span v-for="tag in threadTags" :key="tag" class="qcard-tag">{{ tag }}</span>
    </div>

    <!-- Stats row 1: Helpful + Views -->
    <div class="qcard-stats-row">
      <button class="stat-btn helpful-btn" @click.stop="like">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="var(--red)" stroke="none"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        <span>{{ localLikes }}</span>
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
      <span class="answered-badge" :class="thread.reply_count > 0 ? 'is-answered' : 'is-unanswered'">
        <svg v-if="thread.reply_count > 0" width="14" height="14" viewBox="0 0 14 14"><circle cx="7" cy="7" r="7" :fill="'var(--green)'"/></svg>
        <svg v-else width="14" height="14" viewBox="0 0 14 14"><circle cx="7" cy="7" r="6" fill="none" stroke="var(--text-light)" stroke-width="2"/></svg>
        <span>{{ thread.reply_count > 0 ? 'answered' : 'unanswered' }}</span>
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
  </div>
</template>

<script>
import ReplyCard from './ReplyCard.vue';
import { getReplies, createReply } from '@/api/index.js';

const SAMPLE_TAGS = [
  ['Mental Health Services', 'Therapy Referrals', 'Uninsured Adults'],
  ['Emergency Housing', 'Shelter Overflow', 'Adult Services'],
  ['Youth Services', 'McKinney-Vento', 'School Stability', 'Housing Insecurity'],
  ['Mental Health Services', 'Crisis Intervention'],
  ['Domestic Violence', 'Substance Use Services'],
  ['Benefits & Eligibility', 'Legal & Advocacy'],
];

export default {
  name: 'QuestionCard',
  components: { ReplyCard },
  props: {
    thread: { type: Object, required: true },
  },
  emits: ['open'],
  data() {
    return {
      liked: false,
      localLikes: this.thread.likes || 0,
      expanded: false,
      replies: [],
      loadingReplies: false,
      replyDraft: '',
      submitting: false,
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
      const idx = (this.thread.id - 1) % SAMPLE_TAGS.length;
      return SAMPLE_TAGS[idx] || SAMPLE_TAGS[0];
    },
    topLevelReplies() {
      return this.replies.filter(r => !r.parent_id || r.parent_id === '0' || r.parent_id === 0);
    },
  },
  methods: {
    open() {
      this.$router.push('/thread/' + this.thread.id);
    },
    like() {
      this.liked = !this.liked;
      this.localLikes += this.liked ? 1 : -1;
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
.helpful-btn:hover {
  color: var(--red);
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
