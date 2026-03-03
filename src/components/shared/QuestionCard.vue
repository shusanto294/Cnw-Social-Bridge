<template>
  <div class="question-card">
    <!-- Card Header: avatar + meta -->
    <div class="qcard-meta">
      <img
        :src="avatarUrl"
        :alt="thread.author_name"
        class="cnw-social-worker-avatar qcard-avatar"
        width="34" height="34"
      />
      <span class="qcard-author">{{ thread.author_name }}</span>
      <span class="cnw-social-worker-verified" title="Verified">✓</span>
      <span class="qcard-date">{{ formatDate(thread.created_at) }}</span>
    </div>

    <!-- Title -->
    <h2 class="qcard-title" @click="open">{{ thread.title }}</h2>

    <!-- Excerpt -->
    <p class="qcard-excerpt">{{ truncate(thread.content, 200) }}</p>

    <!-- Tags -->
    <div class="qcard-tags">
      <TagBadge v-for="tag in threadTags" :key="tag" :label="tag" />
    </div>

    <!-- Stats row -->
    <div class="qcard-footer">
      <button class="stat-btn helpful-btn" @click.stop="like">
        <svg width="14" height="14" viewBox="0 0 24 24" :fill="liked ? 'var(--red)' : 'none'" stroke="var(--red)" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        {{ localLikes }} Helpful
      </button>
      <span class="stat-sep">|</span>
      <span class="stat-text">Views: {{ formatNum(thread.views) }}</span>
      <span class="stat-sep">|</span>
      <button class="stat-btn" @click.stop="toggleExpand">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        {{ thread.reply_count || 0 }} Replies
      </button>
      <span class="stat-sep">|</span>
      <button class="stat-btn reply-link" @click.stop="toggleExpand">Reply</button>
      <span class="answered-badge" :class="thread.reply_count > 0 ? 'is-answered' : 'is-unanswered'">
        <svg v-if="thread.reply_count > 0" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        <svg v-else width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>
        {{ thread.reply_count > 0 ? 'answered' : 'unanswered' }}
      </span>
    </div>

    <!-- Inline replies (expanded) -->
    <div v-if="expanded" class="qcard-replies">
      <div v-if="loadingReplies" class="cnw-social-worker-loading" style="padding:16px">Loading replies…</div>
      <template v-else>
        <ReplyCard
          v-for="reply in topLevelReplies"
          :key="reply.id"
          :reply="reply"
          :all-replies="replies"
          :depth="0"
          @reply="focusReplyBox"
        />
        <!-- Write message box -->
        <div class="inline-reply-form">
          <img
            :src="currentUserAvatar"
            class="cnw-social-worker-avatar"
            width="30" height="30"
            alt="You"
          />
          <div class="inline-reply-input-wrap">
            <input
              ref="replyInput"
              v-model="replyDraft"
              type="text"
              placeholder="Write Message..."
              class="inline-reply-input"
              @keydown.enter.prevent="submitInlineReply"
            />
            <button
              class="cnw-social-worker-btn cnw-social-worker-btn-primary cnw-social-worker-btn-sm inline-reply-send"
              :disabled="!replyDraft.trim() || submitting"
              @click="submitInlineReply"
            >Reply</button>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script>
import TagBadge from './TagBadge.vue';
import ReplyCard from './ReplyCard.vue';
import { getReplies, createReply } from '@/api/index.js';

const SAMPLE_TAGS = [
  ['Trauma-Informed Care', 'Therapy Referrals', 'Uninsured Adults'],
  ['Emergency Housing', 'Shelter Overflow', 'Adult Services'],
  ['Youth Services', 'McKinney-Vento', 'School Stability', 'Housing Insecurity'],
  ['Mental Health Services', 'Crisis Intervention'],
  ['Domestic Violence', 'Substance Use Services'],
  ['Benefits & Eligibility', 'Legal & Advocacy'],
];

export default {
  name: 'QuestionCard',
  components: { TagBadge, ReplyCard },
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
        + ' · ' + date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
    },
    formatNum(n) {
      if (!n) return '0';
      return Number(n).toLocaleString();
    },
  },
};
</script>

<style scoped>
.question-card {
  background: #fff;
  border-radius: var(--radius);
  padding: 20px 22px;
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border);
  transition: box-shadow 0.15s;
}
.question-card:hover {
  box-shadow: var(--shadow-md);
}

.qcard-meta {
  display: flex;
  align-items: center;
  gap: 7px;
  margin-bottom: 12px;
}
.qcard-avatar {
  border: 2px solid var(--border);
}
.qcard-author {
  font-size: 13.5px;
  font-weight: 600;
  color: var(--text-dark);
}
.qcard-date {
  font-size: 12px;
  color: var(--text-light);
  margin-left: 4px;
}

.qcard-title {
  font-size: 17px;
  font-weight: 700;
  color: var(--text-dark);
  line-height: 1.4;
  margin-bottom: 10px;
  cursor: pointer;
}
.qcard-title:hover {
  color: var(--teal-dark);
}

.qcard-excerpt {
  font-size: 13.5px;
  color: var(--text-med);
  line-height: 1.65;
  margin-bottom: 14px;
}

.qcard-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 7px;
  margin-bottom: 14px;
}

.qcard-footer {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
  padding-top: 12px;
  border-top: 1px solid var(--border);
}

.stat-btn {
  background: none;
  border: none;
  font-size: 12.5px;
  color: var(--text-light);
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 0;
  transition: color 0.12s;
}
.stat-btn:hover {
  color: var(--text-dark);
}
.helpful-btn:hover { color: var(--red); }
.reply-link {
  color: var(--teal);
  font-weight: 500;
}
.reply-link:hover { color: var(--teal-dark); }

.stat-text {
  font-size: 12.5px;
  color: var(--text-light);
}
.stat-sep {
  color: var(--border);
  font-size: 12px;
}

.answered-badge {
  margin-left: auto;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  font-weight: 600;
  padding: 3px 10px;
  border-radius: var(--radius-pill);
}
.answered-badge.is-answered {
  background: var(--green-light);
  color: var(--green-dark);
}
.answered-badge.is-unanswered {
  background: var(--bg);
  color: var(--text-light);
}

/* Inline replies */
.qcard-replies {
  margin-top: 16px;
  border-top: 2px solid var(--border);
  padding-top: 4px;
}

.inline-reply-form {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-top: 14px;
  padding-top: 12px;
  border-top: 1px solid var(--border);
}
.inline-reply-input-wrap {
  flex: 1;
  display: flex;
  gap: 8px;
}
.inline-reply-input {
  flex: 1;
  padding: 8px 14px;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  font-size: 13px;
  font-family: inherit;
  color: var(--text-dark);
  background: var(--bg);
  transition: border-color 0.12s;
}
.inline-reply-input:focus {
  outline: none;
  border-color: var(--teal);
  background: #fff;
}
.inline-reply-send {
  flex-shrink: 0;
}
</style>
