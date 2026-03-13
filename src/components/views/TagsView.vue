<template>
  <div class="tags-view">

    <!-- Page header -->
    <div class="tags-page-header">
      <div class="tags-page-header-text">
        <h1 class="tags-view-heading">Tags</h1>
        <p class="tags-view-subtitle">Browse and follow topics you're interested in</p>
      </div>
      <button v-if="isLoggedIn" class="tags-create-btn" @click="openCreateModal">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        New Tag
      </button>
    </div>

    <!-- Filter tabs + search -->
    <div class="tags-toolbar">
      <div class="tags-filter-tabs">
        <button class="tags-filter-tab" :class="{ active: filter === 'all' }" @click="filter = 'all'">All Tags</button>
        <button v-if="isLoggedIn" class="tags-filter-tab" :class="{ active: filter === 'mine' }" @click="filter = 'mine'">My Tags</button>
        <button v-if="isLoggedIn" class="tags-filter-tab" :class="{ active: filter === 'following' }" @click="filter = 'following'">Following</button>
        <button v-if="isLoggedIn" class="tags-filter-tab" :class="{ active: filter === 'notfollowing' }" @click="filter = 'notfollowing'">Not Following</button>
      </div>
      <div class="tags-search-wrap">
        <input
          v-model="search"
          type="text"
          class="tags-search-input"
          placeholder="Search tags..."
          aria-label="Search tags"
        />
        <svg class="tags-search-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <button v-if="search" class="tags-search-clear" @click="search = ''" aria-label="Clear search">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
    </div>

    <div v-if="loading" class="tags-grid">
      <div v-for="n in 8" :key="n" class="cnw-skeleton-card" style="padding:14px;gap:8px">
        <div class="cnw-skeleton cnw-skeleton-line-lg" style="width:50%"></div>
        <div class="cnw-skeleton cnw-skeleton-line-sm" style="width:80%"></div>
        <div class="cnw-skeleton cnw-skeleton-line-sm" style="width:35%"></div>
      </div>
    </div>
    <div v-else-if="filteredTags.length === 0" class="tags-empty">
      <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
      <span>{{ filter === 'mine' ? 'You haven\'t created any tags yet.' : filter === 'following' ? 'You aren\'t following any tags yet.' : 'No tags found.' }}</span>
    </div>

    <div v-else class="tags-grid">
      <div
        v-for="tag in visibleTags"
        :key="tag.id"
        class="tag-card"
        :class="{ 'is-followed': followedIds.has(Number(tag.id)) }"
      >
        <div class="tag-card-top">
          <div class="tag-card-icon-wrap" :class="{ 'icon-followed': followedIds.has(Number(tag.id)) }">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
          </div>
          <div class="tag-card-actions">
            <button v-if="canEdit(tag)" class="tag-icon-btn tag-edit-btn" data-tooltip="Edit" @click="openEditModal(tag)" aria-label="Edit tag">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </button>
            <button v-if="canEdit(tag)" class="tag-icon-btn tag-delete-btn" data-tooltip="Delete" @click="openDeleteConfirm(tag)" aria-label="Delete tag">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
            </button>
          </div>
        </div>
        <span class="tag-card-name">{{ tag.name }}</span>
        <div class="tag-card-meta">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          <span>{{ tag.question_count || 0 }} {{ (tag.question_count == 1) ? 'question' : 'questions' }}</span>
        </div>
        <p v-if="tag.description" class="tag-card-desc">{{ tag.description }}</p>

        <div v-if="isLoggedIn" class="tag-follow-wrap">
          <button
            class="tag-follow-btn"
            :class="{ 'is-following': followedIds.has(Number(tag.id)) }"
            :disabled="togglingId === Number(tag.id)"
            @click="toggleFollow(tag)"
          >
            <svg v-if="followedIds.has(Number(tag.id))" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            <svg v-else width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            {{ followedIds.has(Number(tag.id)) ? 'Following' : 'Follow' }}
          </button>
        </div>
      </div>
    </div>
    <!-- Pagination -->
    <nav v-if="totalPages > 1" class="tags-pagination" aria-label="Pagination">
      <button class="tags-page-btn" :disabled="currentPage <= 1" @click="currentPage--" aria-label="Previous page">&#x2039;</button>
      <button
        v-for="p in pageNumbers"
        :key="p"
        class="tags-page-btn"
        :class="{ 'is-active': p === currentPage }"
        @click="currentPage = p"
        :aria-label="'Page ' + p"
        :aria-current="p === currentPage ? 'page' : undefined"
      >{{ p }}</button>
      <button class="tags-page-btn" :disabled="currentPage >= totalPages" @click="currentPage++" aria-label="Next page">&#x203A;</button>
    </nav>

    <!-- Modal (create + edit) -->
    <div v-if="showModal" class="tags-modal-overlay" @click.self="closeModal">
      <div class="tags-modal" role="dialog" aria-modal="true" aria-labelledby="tags-modal-title">
        <div class="tags-modal-header">
          <h3 class="tags-modal-title" id="tags-modal-title">{{ modalMode === 'create' ? 'New Tag' : 'Edit Tag' }}</h3>
          <button class="tags-modal-close" @click="closeModal" aria-label="Close">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
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

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteConfirm" class="tags-modal-overlay" @click.self="showDeleteConfirm = false">
      <div class="tags-modal" role="dialog" aria-modal="true" aria-labelledby="tags-delete-modal-title">
        <div class="tags-modal-header">
          <h3 class="tags-modal-title" id="tags-delete-modal-title">Delete Tag</h3>
          <button class="tags-modal-close" @click="showDeleteConfirm = false" aria-label="Close">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div class="tags-modal-body">
          <p class="tags-delete-msg">Are you sure you want to delete the tag <strong>"{{ deleteTargetTag?.name }}"</strong>? This will remove it from all threads and cannot be undone.</p>
        </div>
        <div class="tags-modal-footer">
          <button class="tags-modal-delete-btn" :disabled="deleteLoading" @click="confirmDeleteTag">
            {{ deleteLoading ? 'Deleting…' : 'Delete' }}
          </button>
          <button class="tags-modal-cancel-btn" @click="showDeleteConfirm = false">Cancel</button>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import { getTags, getFollowedTags, followTag, unfollowTag, updateTag, createTag, deleteTag } from '@/api/index.js';

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
      pageSize: 21,
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

      // Delete confirmation
      showDeleteConfirm: false,
      deleteTargetTag: null,
      deleteLoading: false,
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
              this.followedIds.add(res.id);
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

    openDeleteConfirm(tag) {
      this.deleteTargetTag = tag;
      this.showDeleteConfirm = true;
    },

    async confirmDeleteTag() {
      if (!this.deleteTargetTag) return;
      this.deleteLoading = true;
      try {
        const res = await deleteTag(this.deleteTargetTag.id);
        if (res && res.success) {
          this.tags = this.tags.filter(t => t.id !== this.deleteTargetTag.id);
          this.followedIds.delete(Number(this.deleteTargetTag.id));
          this.followedIds = new Set(this.followedIds);
          this.showDeleteConfirm = false;
          this.deleteTargetTag = null;
        }
      } catch { /* silent */ }
      finally { this.deleteLoading = false; }
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
/* ── Layout ─────────────────────────────────────────────────── */
.tags-view {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* ── Page header ────────────────────────────────────────────── */
.tags-page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
}
.tags-page-header-text {
  flex: 1;
}
.tags-view-heading {
  font-size: 22px;
  font-weight: 700;
  color: var(--text-dark);
  margin: 0;
  line-height: 1.2;
}
.tags-view-subtitle {
  font-size: 13px;
  color: var(--text-light);
  margin: 2px 0 0;
  font-weight: 400;
}
.tags-create-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 9px 20px;
  background: var(--primary);
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
  font-family: inherit;
  cursor: pointer;
  white-space: nowrap;
  transition: all 0.2s;
}
.tags-create-btn:hover {
  background: var(--teal-dark);
  box-shadow: 0 2px 8px rgba(58, 169, 218, 0.3);
}

/* ── Toolbar: filters + search ──────────────────────────────── */
.tags-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  background: var(--white);
  padding: 10px 16px;
  border-radius: 10px;
  border: 1px solid var(--border);
  flex-wrap: wrap;
}
.tags-filter-tabs {
  display: flex;
  gap: 4px;
  flex-wrap: wrap;
}
.tags-filter-tab {
  padding: 7px 14px;
  font-size: 13px;
  font-weight: 500;
  font-family: inherit;
  color: var(--text-med);
  background: none;
  display: inline-flex;
  align-items: center;
  line-height: 1;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.15s;
}
.tags-filter-tab:hover {
  background: var(--bg);
  color: var(--text-dark);
}
.tags-filter-tab.active {
  background: var(--primary);
  color: #fff;
}

/* ── Search ─────────────────────────────────────────────────── */
.tags-search-wrap {
  display: flex;
  align-items: center;
  gap: 8px;
  background: var(--bg);
  border: 1px solid var(--border);
  border-radius: 8px;
  padding: 7px 12px;
  min-width: 200px;
  max-width: 280px;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.tags-search-wrap:focus-within {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(58, 169, 218, 0.1);
}
.tags-search-icon {
  flex-shrink: 0;
  color: var(--text-light);
}
.tags-search-input {
  flex: 1;
  padding: 0;
  border: none;
  font-size: 13px;
  font-weight: 400;
  font-family: inherit;
  color: var(--text-body);
  background: transparent;
  line-height: 1;
  min-width: 0;
}
.tags-search-input::placeholder {
  color: var(--text-light);
  font-weight: 400;
}
.tags-search-input:focus {
  outline: none;
}
.tags-search-clear {
  background: none;
  border: none;
  color: var(--text-light);
  cursor: pointer;
  line-height: 1;
  padding: 2px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  border-radius: 50%;
  transition: all 0.15s;
}
.tags-search-clear:hover {
  color: #dc2626;
  background: #fef2f2;
}

/* ── Empty state ────────────────────────────────────────────── */
.tags-empty {
  text-align: center;
  padding: 48px 20px;
  color: var(--text-light);
  font-size: 14px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  background: var(--white);
  border-radius: 10px;
  border: 1px dashed var(--border);
}

/* ── Grid ───────────────────────────────────────────────────── */
.tags-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 14px;
}

/* ── Card ───────────────────────────────────────────────────── */
.tag-card {
  display: flex;
  flex-direction: column;
  gap: 8px;
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 16px;
  transition: all 0.2s;
  position: relative;
}
.tag-card:hover {
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
  transform: translateY(-1px);
}
.tag-card.is-followed {
  border-color: var(--primary);
  background: linear-gradient(135deg, rgba(58, 169, 218, 0.03), rgba(95, 191, 145, 0.03));
}

/* Card top row */
.tag-card-top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
}
.tag-card-icon-wrap {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f0f9ff;
  color: var(--primary);
  flex-shrink: 0;
  transition: all 0.2s;
}
.tag-card-icon-wrap.icon-followed {
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  color: #fff;
}
.tag-card-name {
  font-size: 15px;
  font-weight: 600;
  color: var(--text-dark);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  line-height: 1.3;
}
.tag-card-meta {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 12px;
  font-weight: 400;
  color: var(--text-light);
  line-height: 1;
}
.tag-card-meta svg {
  stroke: var(--text-light);
}
.tag-card-desc {
  font-size: 12px;
  font-weight: 400;
  color: var(--text-med);
  line-height: 1.5;
  margin: 0;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* ── Card action icons ──────────────────────────────────────── */
.tag-card-actions {
  display: flex;
  align-items: center;
  gap: 4px;
  flex-shrink: 0;
  opacity: 0;
  transition: opacity 0.15s;
}
.tag-card:hover .tag-card-actions {
  opacity: 1;
}
.tag-icon-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border-radius: 6px;
  border: 1px solid var(--border);
  background: var(--white);
  color: var(--text-light);
  cursor: pointer;
  padding: 0;
  position: relative;
  transition: all 0.15s;
}
.tag-edit-btn:hover {
  background: #f0f9ff;
  color: var(--primary);
  border-color: var(--primary);
}
.tag-delete-btn:hover {
  background: #fef2f2;
  color: #dc2626;
  border-color: #dc2626;
}

/* ── Follow button ──────────────────────────────────────────── */
.tag-follow-wrap {
  margin-top: auto;
  padding-top: 4px;
}
.tag-follow-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 5px;
  padding: 7px 16px;
  border: 1px solid var(--border);
  border-radius: 8px;
  background: var(--white);
  color: var(--text-med);
  font-size: 12px;
  font-weight: 500;
  font-family: inherit;
  cursor: pointer;
  transition: all 0.2s;
  width: 100%;
}
.tag-follow-btn:hover {
  border-color: var(--primary);
  color: var(--primary);
  background: #f0f9ff;
}
.tag-follow-btn.is-following {
  background: var(--primary);
  color: #fff;
  border-color: var(--primary);
}
.tag-follow-btn.is-following:hover {
  background: #dc2626;
  border-color: #dc2626;
}
.tag-follow-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* ── Pagination ─────────────────────────────────────────────── */
.tags-pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 4px;
  padding: 8px 0;
}
.tags-page-btn {
  width: 36px;
  height: 36px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 1px solid var(--border);
  background: var(--white);
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
  font-family: inherit;
  color: var(--text-med);
  cursor: pointer;
  transition: all 0.2s;
}
.tags-page-btn:hover:not(:disabled):not(.is-active) {
  border-color: var(--primary);
  color: var(--primary);
  background: #f0f9ff;
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

/* ── Modal ──────────────────────────────────────────────────── */
.tags-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  backdrop-filter: blur(2px);
}
.tags-modal {
  background: #fff;
  border-radius: 12px;
  width: 100%;
  max-width: 440px;
  margin: 16px;
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}
.tags-modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 18px 20px 14px;
  border-bottom: 1px solid var(--border);
}
.tags-modal-title {
  font-size: 16px;
  font-weight: 600;
  color: var(--text-dark);
  margin: 0;
}
.tags-modal-close {
  background: none;
  border: none;
  color: var(--text-light);
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
  border-radius: 6px;
  transition: all 0.15s;
}
.tags-modal-close:hover {
  color: var(--text-dark);
  background: var(--bg);
}
.tags-modal-body {
  display: flex;
  flex-direction: column;
  gap: 12px;
  padding: 20px;
}
.tags-modal-input, .tags-modal-textarea {
  width: 100%;
  padding: 9px 12px;
  border: 1px solid var(--border);
  border-radius: 8px;
  font-size: 13px;
  font-family: inherit;
  color: var(--text-dark);
  background: var(--white);
  box-sizing: border-box;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.tags-modal-input:focus, .tags-modal-textarea:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(58, 169, 218, 0.1);
}
.tags-modal-textarea {
  resize: vertical;
}
.tags-modal-error {
  font-size: 12px;
  color: #dc2626;
  margin: 0;
  padding: 6px 10px;
  background: #fef2f2;
  border-radius: 6px;
  border: 1px solid #fecaca;
}
.tags-modal-footer {
  display: flex;
  gap: 10px;
  padding: 14px 20px 18px;
  border-top: 1px solid var(--border);
}
.tags-modal-submit-btn {
  flex: 1;
  padding: 9px 0;
  background: var(--primary);
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
  font-family: inherit;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s;
}
.tags-modal-submit-btn:hover { background: var(--teal-dark); }
.tags-modal-submit-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.tags-modal-cancel-btn {
  flex: 1;
  padding: 9px 0;
  background: var(--white);
  color: var(--text-med);
  border: 1px solid var(--border);
  border-radius: 8px;
  font-size: 13px;
  font-family: inherit;
  text-align: center;
  cursor: pointer;
  transition: all 0.15s;
}
.tags-modal-cancel-btn:hover { background: var(--bg); color: var(--text-dark); }

/* ── Delete confirmation ────────────────────────────────────── */
.tags-delete-msg {
  font-size: 14px;
  color: var(--text-med);
  line-height: 1.5;
  margin: 0;
}
.tags-delete-msg strong {
  color: var(--text-dark);
}
.tags-modal-delete-btn {
  flex: 1;
  padding: 9px 0;
  background: #dc2626;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
  font-family: inherit;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s;
}
.tags-modal-delete-btn:hover { background: #b91c1c; }
.tags-modal-delete-btn:disabled { opacity: 0.5; cursor: not-allowed; }

/* ── Tooltip ────────────────────────────────────────────────── */
.tag-icon-btn::after {
  content: attr(data-tooltip);
  position: absolute;
  bottom: calc(100% + 8px);
  left: 50%;
  transform: translateX(-50%) scale(0.8);
  background: var(--text-dark);
  color: #fff;
  font-size: 11px;
  font-weight: 500;
  font-family: inherit;
  padding: 4px 10px;
  border-radius: 6px;
  white-space: nowrap;
  pointer-events: none;
  opacity: 0;
  transition: opacity 0.15s, transform 0.15s;
}
.tag-icon-btn::before {
  content: '';
  position: absolute;
  bottom: calc(100% + 2px);
  left: 50%;
  transform: translateX(-50%) scale(0.8);
  border: 5px solid transparent;
  border-top-color: var(--text-dark);
  pointer-events: none;
  opacity: 0;
  transition: opacity 0.15s, transform 0.15s;
}
.tag-icon-btn:hover::after,
.tag-icon-btn:hover::before {
  opacity: 1;
  transform: translateX(-50%) scale(1);
}

/* ── Responsive ─────────────────────────────────────────────── */
@media (max-width: 760px) {
  .tags-page-header {
    flex-wrap: wrap;
  }
  .tags-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  .tags-toolbar {
    flex-direction: column;
    align-items: stretch;
  }
  .tags-search-wrap {
    max-width: 100%;
    min-width: auto;
  }
}
@media (max-width: 480px) {
  .tags-grid {
    grid-template-columns: 1fr;
    gap: 10px;
  }
  .tags-page-header {
    gap: 10px;
  }
  .tags-view-heading {
    font-size: 18px;
  }
  .tags-filter-tabs {
    width: 100%;
  }
  .tags-filter-tab {
    padding: 6px 10px;
    font-size: 12px;
  }
  .tags-create-btn {
    padding: 8px 14px;
    font-size: 12px;
  }
  .tag-card {
    padding: 14px;
  }
  .tag-card-actions {
    opacity: 1;
  }
  .tags-modal {
    max-width: 95vw;
    margin: 8px;
  }
}
</style>
