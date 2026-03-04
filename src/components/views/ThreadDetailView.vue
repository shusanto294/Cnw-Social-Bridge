<template>
  <div class="thread-detail-view">
    <button class="back-btn" @click="$router.push('/')">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="15 18 9 12 15 6"/></svg>
      Back to Questions
    </button>

    <div v-if="loading" class="cnw-social-worker-loading">Loading…</div>
    <div v-else-if="!thread" class="cnw-social-worker-empty">Thread not found.</div>

    <template v-else>
      <!-- Thread post — matches QuestionCard design -->
      <div class="question-card">
        <!-- Header: avatar + name + verified + date -->
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
        <h2 class="qcard-title">{{ thread.title }}</h2>

        <!-- Content (full, not truncated) -->
        <p class="qcard-excerpt">{{ thread.content }}</p>

        <!-- Tags — gradient badges -->
        <div class="qcard-tags">
          <span v-for="tag in threadTags" :key="tag" class="qcard-tag">{{ tag }}</span>
        </div>

        <!-- Stats row 1: Helpful + Views -->
        <div class="qcard-stats-row">
          <button class="stat-btn helpful-btn" @click="like">
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
          <button class="stat-btn">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
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
            <svg v-if="replies.length > 0" width="14" height="14" viewBox="0 0 14 14"><circle cx="7" cy="7" r="7" fill="var(--green)"/></svg>
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
  </div>
</template>

<script>
import ReplyCard from '@/components/shared/ReplyCard.vue';
import { getThread, getReplies, createReply } from '@/api/index.js';

export default {
  name: 'ThreadDetailView',
  components: { ReplyCard },
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
      return `https://www.gravatar.com/avatar/${id}?d=identicon&s=34`;
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
</style>
