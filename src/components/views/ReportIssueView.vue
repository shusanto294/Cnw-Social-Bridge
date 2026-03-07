<template>
  <div class="cnw-report">
    <h1 class="cnw-report-title">Report an Issue</h1>
    <p class="cnw-report-intro">
      Help us keep the community safe and welcoming. Use this form to report inappropriate content, technical problems, or any other concerns. All reports are reviewed by our moderation team.
    </p>

    <div v-if="submitted" class="cnw-report-success">
      <div class="cnw-report-success-icon">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#22a55b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
      </div>
      <h3>Report Submitted Successfully</h3>
      <p>Thank you for helping us maintain a safe community. Our moderation team will review your report and take appropriate action. You may be contacted for additional details if needed.</p>
      <button class="cnw-report-btn" @click="resetForm">Submit Another Report</button>
    </div>

    <div v-if="error" class="cnw-report-error">{{ error }}</div>

    <form v-if="!submitted" class="cnw-report-form" @submit.prevent="submitReport">
      <div class="cnw-report-field">
        <label class="cnw-report-label">Report Type</label>
        <select v-model="form.type" class="cnw-report-select" required>
          <option value="" disabled>Select a category...</option>
          <option value="inappropriate_content">Inappropriate Content</option>
          <option value="harassment">Harassment or Bullying</option>
          <option value="spam">Spam or Self-Promotion</option>
          <option value="confidentiality">Confidentiality Violation</option>
          <option value="misinformation">Misinformation or Harmful Advice</option>
          <option value="technical">Technical Issue / Bug</option>
          <option value="other">Other</option>
        </select>
      </div>

      <div class="cnw-report-field">
        <label class="cnw-report-label">Subject</label>
        <input v-model="form.subject" type="text" class="cnw-report-input" placeholder="Brief summary of the issue" required />
      </div>

      <div class="cnw-report-field">
        <label class="cnw-report-label">Link to Content (optional)</label>
        <input v-model="form.link" type="text" class="cnw-report-input" placeholder="Paste the URL or thread link if applicable" />
      </div>

      <div class="cnw-report-field">
        <label class="cnw-report-label">Description</label>
        <textarea v-model="form.description" class="cnw-report-textarea" rows="6" placeholder="Please describe the issue in detail. Include what happened, who was involved, and any other relevant context..." required></textarea>
      </div>

      <div class="cnw-report-actions">
        <button type="submit" class="cnw-report-btn cnw-report-btn-submit" :disabled="submitting">
          {{ submitting ? 'Submitting...' : 'Submit Report' }}
        </button>
      </div>
    </form>

    <div class="cnw-report-info">
      <h3>What Happens Next?</h3>
      <div class="cnw-report-steps">
        <div class="cnw-report-step">
          <div class="cnw-report-step-num">1</div>
          <div>
            <h4>Report Received</h4>
            <p>Your report is logged and queued for review by our moderation team.</p>
          </div>
        </div>
        <div class="cnw-report-step">
          <div class="cnw-report-step-num">2</div>
          <div>
            <h4>Under Review</h4>
            <p>A moderator will investigate the reported content or issue and gather any additional context needed.</p>
          </div>
        </div>
        <div class="cnw-report-step">
          <div class="cnw-report-step-num">3</div>
          <div>
            <h4>Action Taken</h4>
            <p>Appropriate action will be taken based on our community guidelines. This may include content removal, warnings, or account restrictions.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { submitReport } from '@/api/index.js';

export default {
  name: 'ReportIssueView',
  data() {
    return {
      form: { type: '', subject: '', link: '', description: '' },
      submitting: false,
      submitted: false,
      error: '',
    };
  },
  methods: {
    async submitReport() {
      if (!this.form.type || !this.form.subject || !this.form.description) return;
      this.submitting = true;
      this.error = '';
      try {
        const res = await submitReport(this.form);
        if (res.success) {
          this.submitted = true;
        } else {
          this.error = res.message || 'Failed to submit report. Please try again.';
        }
      } catch {
        this.error = 'Failed to submit report. Please try again.';
      }
      this.submitting = false;
    },
    resetForm() {
      this.form = { type: '', subject: '', link: '', description: '', priority: 'medium' };
      this.submitted = false;
      this.error = '';
    },
  },
};
</script>

<style>
.cnw-report { display: flex; flex-direction: column; gap: 16px; }
.cnw-report-title {
  font-size: 20px;
  font-weight: 800;
  color: var(--text-dark);
}
.cnw-report-intro {
  font-size: 14px;
  line-height: 1.7;
  color: var(--text-med);
  background: #fff;
  padding: 18px 20px;
  border-radius: var(--radius);
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border);
  margin: 0;
}

/* Success */
.cnw-report-success {
  background: #fff;
  border-radius: var(--radius);
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border);
  padding: 40px 28px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
}
.cnw-report-success h3 {
  font-size: 17px;
  font-weight: 700;
  color: var(--text-dark);
  margin: 0;
}
.cnw-report-success p {
  font-size: 13px;
  color: var(--text-med);
  line-height: 1.6;
  max-width: 480px;
  margin: 0;
}

/* Form */
.cnw-report-form {
  background: #fff;
  border-radius: var(--radius);
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border);
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 18px;
}
.cnw-report-field { display: flex; flex-direction: column; gap: 6px; }
.cnw-report-label {
  font-size: 13px;
  font-weight: 600;
  color: var(--text-dark);
}
.cnw-report-input,
.cnw-report-select,
.cnw-report-textarea {
  padding: 10px 14px;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  font-size: 13px;
  font-family: inherit;
  outline: none;
  background: #fff;
  color: var(--text-dark);
}
.cnw-report-input:focus,
.cnw-report-select:focus,
.cnw-report-textarea:focus { border-color: var(--teal); }
.cnw-report-textarea { resize: vertical; min-height: 120px; }
.cnw-report-select { cursor: pointer; }

/* Actions */
.cnw-report-actions { display: flex; gap: 10px; padding-top: 4px; }
.cnw-report-btn {
  padding: 10px 24px;
  border-radius: var(--radius-sm);
  font-size: 13px;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: background 0.15s;
}
.cnw-report-btn-submit {
  background: var(--teal);
  color: #fff;
}
.cnw-report-btn-submit:hover { background: var(--teal-dark); }
.cnw-report-btn-submit:disabled { opacity: 0.5; cursor: not-allowed; }
.cnw-report-error {
  background: var(--red-light);
  color: var(--red);
  padding: 12px 16px;
  border-radius: var(--radius-sm);
  font-size: 13px;
  border: 1px solid var(--red);
}

/* Info section */
.cnw-report-info {
  background: #fff;
  border-radius: var(--radius);
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border);
  padding: 24px;
}
.cnw-report-info h3 {
  font-size: 16px;
  font-weight: 700;
  color: var(--text-dark);
  margin-bottom: 16px;
}
.cnw-report-steps { display: flex; flex-direction: column; gap: 16px; }
.cnw-report-step {
  display: flex;
  gap: 14px;
  align-items: flex-start;
}
.cnw-report-step-num {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: var(--teal);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  font-weight: 700;
  flex-shrink: 0;
}
.cnw-report-step h4 {
  font-size: 14px;
  font-weight: 600;
  color: var(--text-dark);
  margin: 0 0 4px;
}
.cnw-report-step p {
  font-size: 13px;
  color: var(--text-med);
  line-height: 1.55;
  margin: 0;
}

</style>
