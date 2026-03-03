<template>
  <div class="thread-detail-view">
    <button class="back-btn" @click="$router.push('/')">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="15 18 9 12 15 6"/></svg>
      Back to Questions
    </button>

    <div v-if="loading" class="cnw-social-worker-loading">Loading…</div>
    <div v-else-if="!thread" class="cnw-social-worker-empty">Thread not found.</div>

    <template v-else>
      <!-- Thread post -->
      <div class="thread-card">
        <div class="thread-meta">
          <img :src="avatarUrl" class="cnw-social-worker-avatar" width="38" height="38" :alt="thread.author_name" />
          <span class="thread-author">{{ thread.author_name }}</span>
          <span class="cnw-social-worker-verified">✓</span>
          <span class="thread-date">{{ formatDate(thread.created_at) }}</span>
        </div>

        <h1 class="thread-title">{{ thread.title }}</h1>

        <div class="thread-content" v-html="thread.content"></div>

        <div class="thread-tags">
          <TagBadge v-for="tag in threadTags" :key="tag" :label="tag" />
        </div>

        <div class="thread-stats">
          <button class="stat-btn helpful-btn" @click="like">
            <svg width="14" height="14" viewBox="0 0 24 24" :fill="liked ? 'var(--red)' : 'none'" stroke="var(--red)" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            {{ localLikes }} Helpful
          </button>
          <span class="stat-sep">|</span>
          <span class="stat-text">Views: {{ formatNum(thread.views) }}</span>
          <span class="stat-sep">|</span>
          <span class="stat-text">{{ replies.length }} Replies</span>
          <span class="answered-badge" :class="replies.length > 0 ? 'is-answered' : 'is-unanswered'">
            {{ replies.length > 0 ? 'answered' : 'unanswered' }}
          </span>
        </div>
      </div>

      <!-- Replies -->
      <div class="replies-section">
        <h2 class="replies-heading">{{ replies.length }} {{ replies.length === 1 ? 'Reply' : 'Replies' }}</h2>
        <div v-if="loadingReplies" class="cnw-social-worker-loading">Loading replies…</div>
        <div v-else class="replies-list">
          <ReplyCard
            v-for="reply in topLevelReplies"
            :key="reply.id"
            :reply="reply"
            :all-replies="replies"
            :depth="0"
            @reply="focusReplyBox"
          />
        </div>
      </div>

      <!-- Reply form -->
      <div v-if="isLoggedIn" class="reply-form">
        <h3 class="reply-form-heading">Write a Reply</h3>
        <div class="reply-form-inner">
          <img :src="currentUserAvatar" class="cnw-social-worker-avatar" width="36" height="36" alt="You" />
          <div class="reply-input-wrap">
            <textarea
              ref="replyBox"
              v-model="replyContent"
              placeholder="Write your reply here…"
              class="reply-textarea"
              rows="4"
            ></textarea>
            <button
              class="cnw-social-worker-btn cnw-social-worker-btn-primary"
              :disabled="!replyContent.trim() || submitting"
              @click="submitReply"
            >{{ submitting ? 'Posting…' : 'Reply' }}</button>
          </div>
        </div>
      </div>
      <div v-else class="login-prompt">
        Please <a href="/wp-login.php">log in</a> to reply.
      </div>
    </template>
  </div>
</template>

<script>
import TagBadge from '@/components/shared/TagBadge.vue';
import ReplyCard from '@/components/shared/ReplyCard.vue';
import { getThread, getReplies, createReply } from '@/api/index.js';

const SAMPLE_TAGS = [
  ['Trauma-Informed Care', 'Therapy Referrals', 'Uninsured Adults'],
  ['Emergency Housing', 'Shelter Overflow', 'Adult Services'],
  ['Youth Services', 'McKinney-Vento', 'School Stability', 'Housing Insecurity'],
];

export default {
  name: 'ThreadDetailView',
  components: { TagBadge, ReplyCard },
  data() {
    return {
      thread: null,
      replies: [],
      loading: true,
      loadingReplies: false,
      liked: false,
      localLikes: 0,
      replyContent: '',
      submitting: false,
      isLoggedIn: !!(window.cnwData?.currentUser?.id > 0),
    };
  },
  computed: {
    avatarUrl() {
      const id = this.thread?.author_id || 0;
      return `https://www.gravatar.com/avatar/${id}?d=identicon&s=38`;
    },
    currentUserAvatar() {
      return window.cnwData?.currentUser?.avatar || 'https://www.gravatar.com/avatar/?d=mp&s=36';
    },
    topLevelReplies() {
      return this.replies.filter(r => !r.parent_id || r.parent_id === '0' || r.parent_id === 0);
    },
    threadTags() {
      const id = this.thread?.id || 1;
      return SAMPLE_TAGS[(id - 1) % SAMPLE_TAGS.length];
    },
  },
  async mounted() {
    const id = this.$route.params.id;
    try {
      this.thread = await getThread(id);
      this.localLikes = this.thread.likes || 0;
    } catch (e) {
      console.error(e);
    } finally {
      this.loading = false;
    }

    this.loadingReplies = true;
    try {
      const data = await getReplies(id);
      this.replies = data.replies || [];
    } catch (e) { /* silent */ } finally {
      this.loadingReplies = false;
    }
  },
  methods: {
    like() {
      this.liked = !this.liked;
      this.localLikes += this.liked ? 1 : -1;
    },
    focusReplyBox() {
      this.$nextTick(() => this.$refs.replyBox?.focus());
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
    formatDate(d) {
      if (!d) return '';
      const date = new Date(d);
      return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
        + ' · ' + date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
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
  gap: 16px;
}

.back-btn {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: none;
  border: none;
  color: var(--teal);
  font-size: 13.5px;
  font-weight: 500;
  padding: 0;
  align-self: flex-start;
}
.back-btn:hover { color: var(--teal-dark); }

/* Thread card */
.thread-card {
  background: #fff;
  border-radius: var(--radius);
  padding: 24px;
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border);
}

.thread-meta {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 14px;
}
.thread-author {
  font-size: 14px;
  font-weight: 700;
  color: var(--text-dark);
}
.thread-date {
  font-size: 12px;
  color: var(--text-light);
  margin-left: 4px;
}

.thread-title {
  font-size: 22px;
  font-weight: 800;
  color: var(--text-dark);
  line-height: 1.35;
  margin-bottom: 16px;
}

.thread-content {
  font-size: 14.5px;
  color: var(--text-med);
  line-height: 1.8;
  margin-bottom: 16px;
}

.thread-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 7px;
  margin-bottom: 16px;
}

.thread-stats {
  display: flex;
  align-items: center;
  gap: 8px;
  padding-top: 14px;
  border-top: 1px solid var(--border);
  flex-wrap: wrap;
}
.stat-btn {
  background: none;
  border: none;
  font-size: 13px;
  color: var(--text-light);
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 0;
}
.stat-btn:hover { color: var(--text-dark); }
.helpful-btn:hover { color: var(--red); }
.stat-text { font-size: 13px; color: var(--text-light); }
.stat-sep { color: var(--border); }
.answered-badge {
  margin-left: auto;
  font-size: 12px;
  font-weight: 600;
  padding: 3px 12px;
  border-radius: var(--radius-pill);
}
.is-answered { background: var(--green-light); color: var(--green-dark); }
.is-unanswered { background: var(--bg); color: var(--text-light); }

/* Replies */
.replies-section {
  background: #fff;
  border-radius: var(--radius);
  padding: 20px 24px;
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border);
}
.replies-heading {
  font-size: 16px;
  font-weight: 700;
  color: var(--text-dark);
  margin-bottom: 4px;
  padding-bottom: 12px;
  border-bottom: 2px solid var(--teal);
}
.replies-list { display: flex; flex-direction: column; }

/* Reply form */
.reply-form {
  background: #fff;
  border-radius: var(--radius);
  padding: 20px 24px;
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border);
}
.reply-form-heading {
  font-size: 15px;
  font-weight: 700;
  margin-bottom: 14px;
  color: var(--text-dark);
}
.reply-form-inner {
  display: flex;
  gap: 12px;
}
.reply-input-wrap {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.reply-textarea {
  width: 100%;
  padding: 12px;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  font-size: 14px;
  font-family: inherit;
  resize: vertical;
  color: var(--text-dark);
  background: var(--bg);
}
.reply-textarea:focus {
  outline: none;
  border-color: var(--teal);
  background: #fff;
}
.cnw-social-worker-btn { align-self: flex-end; }

.login-prompt {
  text-align: center;
  padding: 32px;
  color: var(--text-light);
  background: #fff;
  border-radius: var(--radius);
  border: 1px solid var(--border);
}
.login-prompt a { color: var(--teal); }
</style>
