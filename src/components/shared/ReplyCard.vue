<template>
  <div class="reply-card" :class="{ 'is-nested': depth > 0 }" :style="nestStyle">
    <div class="reply-header">
      <img
        :src="avatarUrl"
        :alt="reply.author_name"
        class="cnw-social-worker-avatar reply-avatar"
        width="30" height="30"
      />
      <span class="reply-author">{{ reply.author_name }}</span>
      <span class="cnw-social-worker-verified" title="Verified">✓</span>
      <span class="reply-date">{{ formatDate(reply.created_at) }}</span>
    </div>

    <div class="reply-body" v-html="reply.content"></div>

    <div class="reply-footer">
      <button class="stat-btn helpful-btn" @click="like">
        <svg width="13" height="13" viewBox="0 0 24 24" :fill="liked ? 'var(--red)' : 'none'" stroke="var(--red)" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        {{ localLikes }} Helpful
      </button>
      <span class="stat-sep">|</span>
      <button class="stat-btn" @click="$emit('reply', reply)">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        {{ reply.reply_count || 0 }} Replies
      </button>
      <span class="stat-sep">|</span>
      <button class="stat-btn reply-action-btn" @click="$emit('reply', reply)">Reply</button>
    </div>

    <!-- Nested replies -->
    <div v-if="nestedReplies.length" class="nested-replies">
      <ReplyCard
        v-for="nr in nestedReplies"
        :key="nr.id"
        :reply="nr"
        :all-replies="allReplies"
        :depth="depth + 1"
        @reply="$emit('reply', $event)"
      />
    </div>
  </div>
</template>

<script>
export default {
  name: 'ReplyCard',
  props: {
    reply: { type: Object, required: true },
    allReplies: { type: Array, default: () => [] },
    depth: { type: Number, default: 0 },
  },
  emits: ['reply'],
  data() {
    return {
      liked: false,
      localLikes: this.reply.likes || 0,
    };
  },
  computed: {
    nestedReplies() {
      return this.allReplies.filter(r => String(r.parent_id) === String(this.reply.id));
    },
    nestStyle() {
      if (this.depth === 0) return {};
      return { marginLeft: `${Math.min(this.depth * 28, 80)}px` };
    },
    avatarUrl() {
      const id = this.reply.author_id || 0;
      return `https://www.gravatar.com/avatar/${id}?d=identicon&s=30`;
    },
  },
  methods: {
    like() {
      this.liked = !this.liked;
      this.localLikes += this.liked ? 1 : -1;
    },
    formatDate(d) {
      if (!d) return '';
      const date = new Date(d);
      return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
        + ' · ' + date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
    },
  },
};
</script>

<style>
.reply-card {
  padding: 14px 0 10px;
  border-top: 1px solid var(--border);
}
.reply-card.is-nested {
  border-top: none;
  border-left: 2px solid var(--border);
  padding-left: 14px;
  margin-top: 10px;
}

.reply-header {
  display: flex;
  align-items: center;
  gap: 7px;
  margin-bottom: 8px;
}

.reply-avatar {
  border: 2px solid var(--border);
}

.reply-author {
  font-size: 13px;
  font-weight: 600;
  color: var(--text-dark);
}

.reply-date {
  font-size: 12px;
  color: var(--text-light);
  margin-left: 4px;
}

.reply-body {
  font-size: 13.5px;
  color: var(--text-med);
  line-height: 1.65;
  margin-bottom: 10px;
}

.reply-footer {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
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
.helpful-btn:hover {
  color: var(--red);
}
.reply-action-btn {
  color: var(--teal);
  font-weight: 500;
}
.reply-action-btn:hover {
  color: var(--teal-dark);
}

.stat-sep {
  color: var(--border);
}

.nested-replies {
  margin-top: 4px;
}
</style>
