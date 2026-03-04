<template>
  <div class="tags-view">

    <!-- Header row: title + filters + new tag button -->
    <div class="tags-view-top">
      <h1 class="tags-view-heading">Tags</h1>
      <div class="tags-filter-tabs">
        <button class="tags-filter-tab" :class="{ active: filter === 'all' }" @click="filter = 'all'">All Tags</button>
        <button v-if="isLoggedIn" class="tags-filter-tab" :class="{ active: filter === 'mine' }" @click="filter = 'mine'">My Tags</button>
        <button v-if="isLoggedIn" class="tags-filter-tab" :class="{ active: filter === 'following' }" @click="filter = 'following'">Following</button>
        <button v-if="isLoggedIn" class="tags-filter-tab" :class="{ active: filter === 'notfollowing' }" @click="filter = 'notfollowing'">Not Following</button>
      </div>
      <div class="tags-create-wrap">
        <button v-if="isLoggedIn" class="cnw-social-worker-btn cnw-social-worker-btn-primary tags-create-btn" @click="openCreateModal">
          + New Tag
        </button>
      </div>
    </div>

    <!-- Search -->
    <div class="tags-search-wrap">
      <input
        v-model="search"
        type="text"
        class="tags-search-input"
        placeholder="Search"
      />
      <button v-if="search" class="tags-search-clear" @click="search = ''">×</button>
      <svg class="tags-search-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    </div>

    <div v-if="loading" class="cnw-social-worker-loading">Loading tags…</div>
    <div v-else-if="filteredTags.length === 0" class="cnw-social-worker-empty">
      {{ filter === 'mine' ? 'You haven\'t created any tags yet.' : filter === 'following' ? 'You aren\'t following any tags yet.' : 'No tags found.' }}
    </div>

    <div v-else class="tags-grid">
      <div
        v-for="tag in visibleTags"
        :key="tag.id"
        class="tag-card"
        :class="{ 'is-followed': followedIds.has(Number(tag.id)) }"
      >

        <div class="tag-card-header">
          <div class="tag-card-name-row">
            <span v-if="followedIds.has(Number(tag.id))" class="tag-followed-tick" title="Following">
              <svg viewBox="0 0 18 18" fill="none"><circle cx="9" cy="9" r="9" fill="#22a55b"/><path d="M4.5 9.5l3 3 6-6" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
            <span class="tag-card-name">{{ tag.name }}</span>
          </div>
          <button v-if="canEdit(tag)" class="tag-edit-btn" @click="openEditModal(tag)">Edit</button>
        </div>
        <p v-if="tag.description" class="tag-card-desc">{{ tag.description }}</p>
        <div class="tag-card-footer">
          <button
            v-if="isLoggedIn"
            class="tag-follow-btn"
            :class="{ 'is-following': followedIds.has(Number(tag.id)) }"
            :disabled="togglingId === Number(tag.id)"
            @click="toggleFollow(tag)"
          >{{ followedIds.has(Number(tag.id)) ? 'Following' : 'Follow' }}</button>
        </div>

      </div>
    </div>
    <!-- Pagination -->
    <div v-if="totalPages > 1" class="tags-pagination">
      <button class="tags-page-btn" :disabled="currentPage <= 1" @click="currentPage--">‹</button>
      <button
        v-for="p in pageNumbers"
        :key="p"
        class="tags-page-btn"
        :class="{ 'is-active': p === currentPage }"
        @click="currentPage = p"
      >{{ p }}</button>
      <button class="tags-page-btn" :disabled="currentPage >= totalPages" @click="currentPage++">›</button>
    </div>

    <!-- Modal (create + edit) -->
    <div v-if="showModal" class="tags-modal-overlay" @click.self="closeModal">
      <div class="tags-modal">
        <div class="tags-modal-header">
          <h3 class="tags-modal-title">{{ modalMode === 'create' ? 'New Tag' : 'Edit Tag' }}</h3>
          <button class="tags-modal-close" @click="closeModal">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div class="tags-modal-body">
          <input
            v-model="modalName"
            type="text"
            class="tags-modal-input"
            placeholder="Tag name"
            ref="modalNameInput"
            @keydown.enter.prevent="submitModal"
          />
          <textarea
            v-model="modalDescription"
            class="tags-modal-textarea"
            placeholder="Description (optional)"
            rows="3"
          ></textarea>
          <p v-if="modalError" class="tags-modal-error">{{ modalError }}</p>
        </div>
        <div class="tags-modal-footer">
          <button class="tags-modal-submit-btn" @click="submitModal" :disabled="modalSaving || !modalName.trim()">
            {{ modalSaving ? '…' : (modalMode === 'create' ? 'Create Tag' : 'Save Changes') }}
          </button>
          <button class="tags-modal-cancel-btn" @click="closeModal">Cancel</button>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import { getTags, getFollowedTags, followTag, unfollowTag, updateTag, createTag } from '@/api/index.js';

export default {
  name: 'TagsView',
  data() {
    return {
      tags: [],
      followedIds: new Set(),
      loading: true,
      togglingId: null,
      filter: 'all',
      search: '',
      currentPage: 1,
      pageSize: 20,
      isLoggedIn: !!(window.cnwData?.currentUser?.id > 0),
      currentUserId: Number(window.cnwData?.currentUser?.id || 0),

      // Modal (shared for create + edit)
      showModal: false,
      modalMode: 'create', // 'create' | 'edit'
      modalTag: null,
      modalName: '',
      modalDescription: '',
      modalSaving: false,
      modalError: '',
    };
  },
  computed: {
    filteredTags() {
      let list = this.tags;
      if (this.filter === 'mine') {
        list = list.filter(t => Number(t.created_by) === this.currentUserId);
      } else if (this.filter === 'following') {
        list = list.filter(t => this.followedIds.has(Number(t.id)));
      } else if (this.filter === 'notfollowing') {
        list = list.filter(t => !this.followedIds.has(Number(t.id)));
      }
      const q = this.search.trim().toLowerCase();
      if (q) {
        list = list.filter(t => t.name.toLowerCase().includes(q) || (t.description || '').toLowerCase().includes(q));
      }
      return list;
    },
    totalPages() {
      return Math.max(1, Math.ceil(this.filteredTags.length / this.pageSize));
    },
    visibleTags() {
      const start = (this.currentPage - 1) * this.pageSize;
      return this.filteredTags.slice(start, start + this.pageSize);
    },
    pageNumbers() {
      const pages = [];
      const start = Math.max(1, this.currentPage - 2);
      const end = Math.min(this.totalPages, this.currentPage + 2);
      for (let i = start; i <= end; i++) pages.push(i);
      return pages;
    },
  },
  watch: {
    filter() { this.currentPage = 1; },
    search() { this.currentPage = 1; },
  },
  async mounted() {
    try {
      const [allTags, followed] = await Promise.all([
        getTags(),
        this.isLoggedIn ? getFollowedTags() : Promise.resolve([]),
      ]);
      this.tags = allTags || [];
      (followed || []).forEach(t => this.followedIds.add(Number(t.id)));
    } catch { /* silent */ }
    finally { this.loading = false; }
  },
  methods: {
    openCreateModal() {
      this.modalMode = 'create';
      this.modalTag = null;
      this.modalName = '';
      this.modalDescription = '';
      this.modalError = '';
      this.showModal = true;
      this.$nextTick(() => this.$refs.modalNameInput?.focus());
    },

    openEditModal(tag) {
      this.modalMode = 'edit';
      this.modalTag = tag;
      this.modalName = tag.name;
      this.modalDescription = tag.description || '';
      this.modalError = '';
      this.showModal = true;
      this.$nextTick(() => this.$refs.modalNameInput?.focus());
    },

    closeModal() {
      this.showModal = false;
      this.modalTag = null;
      this.modalError = '';
    },

    async submitModal() {
      const name = this.modalName.trim();
      if (!name) return;
      this.modalSaving = true;
      this.modalError = '';
      try {
        if (this.modalMode === 'create') {
          const res = await createTag({ name, description: this.modalDescription.trim() });
          if (res && res.success && res.id) {
            if (!res.existing) {
              this.tags.push({ id: res.id, name, description: this.modalDescription.trim(), created_by: this.currentUserId });
            }
            this.closeModal();
          } else {
            this.modalError = res?.message || 'Failed to create tag.';
          }
        } else {
          const res = await updateTag({ id: this.modalTag.id, name, description: this.modalDescription.trim() });
          if (res && res.success) {
            this.modalTag.name = name;
            this.modalTag.description = this.modalDescription.trim();
            this.closeModal();
          } else {
            this.modalError = res?.message || 'Failed to save.';
          }
        }
      } catch {
        this.modalError = 'Network error.';
      } finally {
        this.modalSaving = false;
      }
    },

    canEdit(tag) {
      return this.currentUserId && Number(tag.created_by) === this.currentUserId;
    },

    async toggleFollow(tag) {
      if (!this.isLoggedIn) return;
      const id = Number(tag.id);
      this.togglingId = id;

      // Optimistic update — show tick immediately
      const wasFollowing = this.followedIds.has(id);
      if (wasFollowing) {
        this.followedIds.delete(id);
      } else {
        this.followedIds.add(id);
      }
      this.followedIds = new Set(this.followedIds);

      try {
        if (wasFollowing) {
          await unfollowTag(id);
        } else {
          await followTag(id);
        }
      } catch {
        // Revert on failure
        if (wasFollowing) {
          this.followedIds.add(id);
        } else {
          this.followedIds.delete(id);
        }
        this.followedIds = new Set(this.followedIds);
      } finally {
        this.togglingId = null;
      }
    },

  },
};
</script>

<style>
.tags-view {
  display: flex;
  flex-direction: column;
  gap: var(--space-s);
}

/* Header row */
.tags-view-top {
  display: flex;
  align-items: center;
  gap: var(--space-s);
}

.tags-view-heading {
  font-size: 18px;
  font-weight: 600;
  color: var(--text-dark);
  line-height: 24.5px;
  flex: 1;
}

/* New Tag button — matches Ask a Question */
.tags-create-wrap {
  flex: 1;
  display: flex;
  justify-content: flex-end;
}

.tags-create-btn {
  padding: 9px 20px;
  font-size: 14px;
  border-radius: var(--radius-sm);
  white-space: nowrap;
}

/* Filter tabs — matches homepage */
.tags-filter-tabs {
  display: flex;
  gap: 5px;
  flex-wrap: wrap;
}


.tags-filter-tab {
  padding: 5px 10px;
  font-size: 14px;
  font-weight: 300;
  font-family: 'Poppins', sans-serif;
  color: var(--text-body);
  background: none;
  display: inline-flex;
  align-items: center;
  line-height: 1;
  border: 1px solid var(--tertiary);
  cursor: pointer;
  transition: background 0.12s;
}
.tags-filter-tab:hover {
  background: var(--bg);
}
.tags-filter-tab.active {
  background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
  color: #fff;
  border: none;
}

/* Search */
.tags-search-wrap {
  display: flex;
  align-items: center;
  gap: var(--space-m);
  max-width: 430px;
  width: 100%;
  margin: var(--space-m) auto;
  background: var(--light, #fff);
  border: none;
  border-radius: var(--radius-l);
  padding: var(--space-4xs) var(--space-s);
  box-shadow: 0 0 var(--radius-s) 0 var(--dark-20, rgba(0, 0, 0, 0.1));
}

.tags-search-input {
  flex: 1;
  padding: 0;
  border: none;
  font-size: var(--text-xs);
  font-weight: 300;
  font-family: 'Poppins', sans-serif;
  color: var(--text-body);
  background: transparent;
  line-height: 16px;
}
.tags-search-input::placeholder {
  color: var(--text-body, #999);
  font-weight: 300;
  font-size: var(--text-xs);
  font-family: 'Poppins', sans-serif;
  opacity: 1;
}
.tags-search-input:focus {
  outline: none;
}

.tags-search-icon {
  flex-shrink: 0;
  color: var(--text-body, #999);
}

.tags-search-clear {
  background: none;
  border: none;
  font-size: 16px;
  color: #aaa;
  cursor: pointer;
  line-height: 1;
  padding: 0;
  flex-shrink: 0;
}
.tags-search-clear:hover { color: var(--text-dark); }

/* Grid */
.tags-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: var(--space-xs);
}

/* Card */
.tag-card {
  display: flex;
  flex-direction: column;
  gap: var(--space-3xs);
  background: #fff;
  border: 1px solid var(--border, #e0e0e0);
  border-radius: var(--radius-m);
  padding: var(--space-xs);
  transition: border-color 0.12s;
}
.tag-card.is-followed {
  border-color: var(--primary);
}

/* Card header row */
.tag-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--space-2xs);
}

.tag-card-name-row {
  display: flex;
  align-items: center;
  gap: 6px;
  min-width: 0;
}

/* Tick mark */
.tag-followed-tick {
  flex-shrink: 0;
  width: 16px;
  height: 16px;
  display: flex;
  align-items: center;
}
.tag-followed-tick svg {
  width: 16px;
  height: 16px;
}

.tag-card-name {
  font-size: var(--text-xs);
  font-weight: 500;
  color: var(--text-dark);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.tag-card-desc {
  font-size: 11px;
  font-weight: 300;
  color: var(--text-body, #666);
  line-height: 1.4;
  margin: 0;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Card footer */
.tag-card-footer {
  margin-top: auto;
  padding-top: var(--space-3xs);
}

/* Edit button */
.tag-edit-btn {
  flex-shrink: 0;
  background: none;
  border: 1px solid var(--border, #ddd);
  border-radius: var(--radius-xs);
  padding: 2px var(--space-2xs);
  font-size: 11px;
  font-weight: 400;
  font-family: 'Poppins', sans-serif;
  color: var(--text-body, #666);
  cursor: pointer;
  transition: background 0.1s, color 0.1s;
}
.tag-edit-btn:hover {
  background: var(--bg);
  color: var(--primary);
  border-color: var(--primary);
}

/* Follow button */
.tag-follow-btn {
  background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
  color: #fff;
  border: none;
  border-radius: var(--radius-xs);
  padding: var(--space-4xs) var(--space-xs);
  font-size: 12px;
  font-weight: 400;
  font-family: 'Poppins', sans-serif;
  line-height: 16px;
  cursor: pointer;
  transition: opacity 0.12s;
  width: 100%;
  text-align: center;
}
.tag-follow-btn:hover {
  opacity: 0.9;
}
.tag-follow-btn.is-following {
  background: none;
  color: var(--primary);
  border: 1px solid var(--primary);
}
.tag-follow-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Pagination */
.tags-pagination {
  display: flex;
  justify-content: center;
  gap: 4px;
  margin-top: var(--space-xs);
}

.tags-page-btn {
  padding: 7px 13px;
  border: 1px solid var(--border);
  background: #fff;
  border-radius: var(--radius-sm);
  font-size: 13px;
  font-family: 'Poppins', sans-serif;
  color: var(--text-body);
  cursor: pointer;
  transition: background 0.12s, color 0.12s;
}
.tags-page-btn:hover:not(:disabled) {
  background: var(--bg);
  color: var(--primary);
}
.tags-page-btn.is-active {
  background: var(--primary);
  color: #fff;
  border-color: var(--primary);
}
.tags-page-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

/* Modal */
.tags-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.tags-modal {
  background: #fff;
  border-radius: var(--radius-m);
  width: 100%;
  max-width: 420px;
  margin: 16px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.18);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.tags-modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: var(--space-s) var(--space-s) var(--space-xs);
  border-bottom: 1px solid var(--border, #eee);
}

.tags-modal-title {
  font-size: var(--text-s);
  font-weight: 600;
  color: var(--text-dark);
  margin: 0;
}

.tags-modal-close {
  background: none;
  border: none;
  color: #aaa;
  cursor: pointer;
  padding: 2px;
  display: flex;
  align-items: center;
  transition: color 0.12s;
}
.tags-modal-close:hover { color: var(--text-dark); }

.tags-modal-body {
  display: flex;
  flex-direction: column;
  gap: var(--space-xs);
  padding: var(--space-s);
}

.tags-modal-input {
  width: 100%;
  padding: var(--space-3xs) var(--space-xs);
  border: 1px solid var(--border, #ddd);
  border-radius: var(--radius-xs);
  font-size: var(--text-xs);
  font-family: 'Poppins', sans-serif;
  color: var(--text-dark);
  background: var(--bg);
  box-sizing: border-box;
}
.tags-modal-input:focus { outline: none; border-color: var(--primary); }

.tags-modal-textarea {
  width: 100%;
  padding: var(--space-3xs) var(--space-xs);
  border: 1px solid var(--border, #ddd);
  border-radius: var(--radius-xs);
  font-size: var(--text-xs);
  font-family: 'Poppins', sans-serif;
  color: var(--text-dark);
  background: var(--bg);
  resize: vertical;
  box-sizing: border-box;
}
.tags-modal-textarea:focus { outline: none; border-color: var(--primary); }

.tags-modal-error {
  font-size: 12px;
  color: #c0392b;
  margin: 0;
}

.tags-modal-footer {
  display: flex;
  gap: var(--space-xs);
  padding: var(--space-xs) var(--space-s) var(--space-s);
  border-top: 1px solid var(--border, #eee);
}

.tags-modal-submit-btn {
  flex: 1;
  padding: var(--space-3xs) 0;
  background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
  color: #fff;
  border: none;
  border-radius: var(--radius-xs);
  font-size: var(--text-xs);
  font-weight: 500;
  font-family: 'Poppins', sans-serif;
  text-align: center;
  cursor: pointer;
  transition: opacity 0.12s;
}
.tags-modal-submit-btn:hover { opacity: 0.9; }
.tags-modal-submit-btn:disabled { opacity: 0.5; cursor: not-allowed; }

.tags-modal-cancel-btn {
  flex: 1;
  padding: var(--space-3xs) 0;
  background: #fff;
  color: var(--text-body);
  border: 1px solid var(--border, #ddd);
  border-radius: var(--radius-xs);
  font-size: var(--text-xs);
  font-family: 'Poppins', sans-serif;
  text-align: center;
  cursor: pointer;
  transition: background 0.1s;
}
.tags-modal-cancel-btn:hover { background: var(--bg); }
</style>
