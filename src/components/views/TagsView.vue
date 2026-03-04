<template>
  <div class="tags-view">
    <h1 class="tags-view-heading">Tags</h1>
    <p class="tags-view-subtitle">Follow tags to personalize your feed and stay updated on topics you care about.</p>

    <div v-if="loading" class="cnw-social-worker-loading">Loading tags…</div>
    <div v-else-if="tags.length === 0" class="cnw-social-worker-empty">No tags available yet.</div>

    <div v-else class="tags-grid">
      <div
        v-for="tag in tags"
        :key="tag.id"
        class="tag-card"
        :class="{ 'is-followed': followedIds.has(tag.id) }"
      >
        <span class="tag-card-name">{{ tag.name }}</span>
        <button
          class="tag-follow-btn"
          :class="{ 'is-following': followedIds.has(tag.id) }"
          :disabled="togglingId === tag.id"
          @click="toggleFollow(tag)"
        >{{ followedIds.has(tag.id) ? 'Following' : 'Follow' }}</button>
      </div>
    </div>
  </div>
</template>

<script>
import { getTags, getFollowedTags, followTag, unfollowTag } from '@/api/index.js';

export default {
  name: 'TagsView',
  data() {
    return {
      tags: [],
      followedIds: new Set(),
      loading: true,
      togglingId: null,
      isLoggedIn: !!(window.cnwData?.currentUser?.id > 0),
    };
  },
  async mounted() {
    try {
      const [allTags, followed] = await Promise.all([
        getTags(),
        this.isLoggedIn ? getFollowedTags() : Promise.resolve([]),
      ]);
      this.tags = allTags || [];
      (followed || []).forEach(t => this.followedIds.add(Number(t.id)));
    } catch (e) { /* silent */ }
    finally { this.loading = false; }
  },
  methods: {
    async toggleFollow(tag) {
      if (!this.isLoggedIn) return;
      const id = Number(tag.id);
      this.togglingId = id;
      try {
        if (this.followedIds.has(id)) {
          await unfollowTag(id);
          this.followedIds.delete(id);
        } else {
          await followTag(id);
          this.followedIds.add(id);
        }
        // Force reactivity
        this.followedIds = new Set(this.followedIds);
      } catch (e) { /* silent */ }
      finally { this.togglingId = null; }
    },
  },
};
</script>

<style>
.tags-view {
  display: flex;
  flex-direction: column;
  gap: var(--space-xs);
}

.tags-view-heading {
  font-size: 18px;
  font-weight: 600;
  color: var(--text-dark);
  line-height: 24.5px;
}

.tags-view-subtitle {
  font-size: var(--text-xs);
  font-weight: 300;
  color: var(--text-body);
  line-height: 1.4;
}

.tags-grid {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-2xs);
}

.tag-card {
  display: flex;
  align-items: center;
  gap: var(--space-2xs);
  background: #fff;
  border: 1px solid var(--border);
  border-radius: var(--radius-m);
  padding: var(--space-3xs) var(--space-xs);
  transition: border-color 0.12s;
}
.tag-card.is-followed {
  border-color: var(--primary);
}

.tag-card-name {
  font-size: var(--text-xs);
  font-weight: 300;
  color: var(--text-dark);
  line-height: 16px;
  white-space: nowrap;
}

.tag-follow-btn {
  background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
  color: #fff;
  border: none;
  border-radius: var(--radius-xs);
  padding: var(--space-4xs) var(--space-2xs);
  font-size: 12px;
  font-weight: 300;
  font-family: 'Poppins', sans-serif;
  line-height: 16px;
  cursor: pointer;
  transition: opacity 0.12s;
  white-space: nowrap;
}
.tag-follow-btn:hover {
  opacity: 0.9;
}
.tag-follow-btn.is-following {
  background: var(--bg);
  color: var(--primary);
  border: 1px solid var(--primary);
}
.tag-follow-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
