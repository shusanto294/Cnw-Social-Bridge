<template>
  <div class="ask-view">
    <button class="back-btn" @click="$router.push('/')">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="15 18 9 12 15 6"/></svg>
      Back to Questions
    </button>

    <div class="ask-card">
      <h1 class="ask-heading">Ask a Question</h1>

      <div v-if="!isLoggedIn" class="login-prompt">
        Please <a href="/wp-login.php">log in</a> to ask a question.
      </div>

      <form v-else class="ask-form" @submit.prevent="submit">
        <div class="form-group">
          <label class="form-label">Title</label>
          <input
            v-model="title"
            type="text"
            placeholder="What's your question? Be specific."
            class="form-input"
            required
          />
        </div>

        <div class="form-group">
          <label class="form-label">Details</label>
          <textarea
            v-model="content"
            placeholder="Provide more context and details about your question…"
            class="form-textarea"
            rows="8"
            required
          ></textarea>
        </div>

        <div class="form-group">
          <label class="form-label">Tags</label>
          <div class="tag-input-box" :class="{ focused: tagFocused }">
            <span
              v-for="tag in selectedTags"
              :key="tag"
              class="selected-tag"
            >
              {{ tag }}
              <button type="button" class="remove-tag-btn" @click="removeTag(tag)">×</button>
            </span>
            <input
              v-model="tagInput"
              type="text"
              placeholder="Add tags (press Enter)…"
              class="tag-input-field"
              @keydown.enter.prevent="addTag"
              @focus="tagFocused = true"
              @blur="tagFocused = false"
            />
          </div>
          <p class="form-hint">Press Enter to add each tag.</p>
        </div>

        <div class="form-actions">
          <button
            type="submit"
            class="cnw-social-worker-btn cnw-social-worker-btn-primary submit-btn"
            :disabled="!canSubmit || submitting"
          >{{ submitting ? 'Posting…' : 'Post Your Question' }}</button>
          <button type="button" class="cnw-social-worker-btn cancel-btn" @click="$router.push('/')">Cancel</button>
        </div>

        <p v-if="error" class="form-error">{{ error }}</p>
      </form>
    </div>
  </div>
</template>

<script>
import { createThread } from '@/api/index.js';

export default {
  name: 'AskQuestionView',
  data() {
    return {
      title: '',
      content: '',
      tagInput: '',
      selectedTags: [],
      tagFocused: false,
      submitting: false,
      error: '',
      isLoggedIn: !!(window.cnwData?.currentUser?.id > 0),
    };
  },
  computed: {
    canSubmit() {
      return this.title.trim().length > 0 && this.content.trim().length > 0;
    },
  },
  methods: {
    addTag() {
      const tag = this.tagInput.trim();
      if (tag && !this.selectedTags.includes(tag) && this.selectedTags.length < 10) {
        this.selectedTags.push(tag);
      }
      this.tagInput = '';
    },
    removeTag(tag) {
      this.selectedTags = this.selectedTags.filter(t => t !== tag);
    },
    async submit() {
      if (!this.canSubmit) return;
      this.submitting = true;
      this.error = '';
      try {
        const data = await createThread({
          title: this.title,
          content: this.content,
          tags: this.selectedTags,
        });
        if (data.success && data.id) {
          this.$router.push('/thread/' + data.id);
        } else {
          this.error = data.message || 'Failed to post question. Please try again.';
        }
      } catch (e) {
        this.error = 'An error occurred. Please try again.';
      } finally {
        this.submitting = false;
      }
    },
  },
};
</script>

<style scoped>
.ask-view {
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

.ask-card {
  background: #fff;
  border-radius: var(--radius);
  padding: 28px 32px;
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border);
}

.ask-heading {
  font-size: 22px;
  font-weight: 800;
  color: var(--text-dark);
  margin-bottom: 24px;
}

.ask-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-label {
  font-size: 13.5px;
  font-weight: 700;
  color: var(--text-dark);
}

.form-input,
.form-textarea {
  padding: 11px 14px;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  font-size: 14px;
  font-family: inherit;
  color: var(--text-dark);
  background: var(--bg);
  transition: border-color 0.12s;
}
.form-input:focus,
.form-textarea:focus {
  outline: none;
  border-color: var(--teal);
  background: #fff;
}
.form-textarea { resize: vertical; }

.tag-input-box {
  display: flex;
  flex-wrap: wrap;
  gap: 7px;
  padding: 8px 10px;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  background: var(--bg);
  min-height: 46px;
  transition: border-color 0.12s;
}
.tag-input-box.focused {
  border-color: var(--teal);
  background: #fff;
}

.selected-tag {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: var(--teal);
  color: #fff;
  font-size: 12.5px;
  font-weight: 500;
  padding: 3px 10px;
  border-radius: var(--radius-pill);
}
.remove-tag-btn {
  background: none;
  border: none;
  color: rgba(255,255,255,0.8);
  font-size: 15px;
  line-height: 1;
  padding: 0;
  display: flex;
  align-items: center;
}
.remove-tag-btn:hover { color: #fff; }

.tag-input-field {
  flex: 1;
  min-width: 140px;
  border: none;
  background: transparent;
  font-size: 13.5px;
  font-family: inherit;
  color: var(--text-dark);
  padding: 2px 4px;
}
.tag-input-field:focus { outline: none; }

.form-hint {
  font-size: 12px;
  color: var(--text-light);
  margin-top: 2px;
}

.form-actions {
  display: flex;
  gap: 10px;
  align-items: center;
}

.submit-btn {
  padding: 11px 28px;
  font-size: 14px;
}

.cancel-btn {
  background: none;
  border: 1px solid var(--border);
  color: var(--text-med);
  padding: 11px 20px;
  border-radius: var(--radius-sm);
  font-size: 14px;
}
.cancel-btn:hover { background: var(--bg); }

.form-error {
  color: var(--red);
  font-size: 13px;
  padding: 10px 14px;
  background: var(--red-light);
  border-radius: var(--radius-sm);
}

.login-prompt {
  text-align: center;
  padding: 40px;
  color: var(--text-light);
}
.login-prompt a { color: var(--teal); }
</style>
