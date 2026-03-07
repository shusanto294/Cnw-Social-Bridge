<template>
  <div class="cnw-guidelines">
    <h1 class="cnw-guidelines-title">Community Guidelines</h1>
    <div class="cnw-guidelines-content" v-html="content"></div>
  </div>
</template>

<script>
import { getGuidelines } from '@/api/index.js';

const defaultContent = `
<p>Welcome to Social Bridge! Our community is built on mutual respect, professionalism, and a shared commitment to supporting one another. Please review the following guidelines to help us maintain a safe and productive space for all members.</p>

<h3>1. Be Respectful and Professional</h3>
<p>Treat every member with dignity and courtesy. Disagreements are natural, but always respond thoughtfully. Personal attacks, name-calling, and discriminatory language are not tolerated.</p>

<h3>2. Protect Confidentiality</h3>
<p>Never share personally identifiable information about clients, colleagues, or other members. When discussing cases, always use anonymized details. Respect the privacy and confidentiality of all individuals involved.</p>

<h3>3. Ask Thoughtful Questions</h3>
<p>Before posting, search the community to see if your question has been asked before. Provide context and details so others can give meaningful answers. Use appropriate tags to help organize discussions.</p>

<h3>4. Give Helpful Answers</h3>
<p>Share your knowledge and experience generously. Back up your advice with evidence-based practices when possible. If you're unsure, say so honestly rather than guessing. Mark helpful answers to assist future readers.</p>

<h3>5. Support the Community</h3>
<p>Upvote helpful content, report inappropriate behaviour, and welcome new members. A strong community is one where everyone contributes positively and supports each other's growth.</p>

<h3>6. No Spam or Self-Promotion</h3>
<p>Do not post advertisements, promotional material, or irrelevant links. Content should be relevant to social work practice, professional development, or community support topics.</p>

<h3>7. Report Violations</h3>
<p>If you encounter content that violates these guidelines, please use the Report an Issue page. Our moderators will review reports promptly and take appropriate action.</p>

<h3>Consequences of Violations</h3>
<p>Violations may result in content removal, temporary suspension, or permanent ban depending on severity. Repeated minor violations will be treated with increasing seriousness. Our moderators have final discretion on all enforcement decisions.</p>

<p><em>These guidelines may be updated from time to time. Continued use of the community constitutes acceptance of the current guidelines.</em></p>
`;

export default {
  name: 'GuidelinesView',
  data() {
    return {
      content: defaultContent,
    };
  },
  async created() {
    try {
      const data = await getGuidelines();
      if (data && data.html && data.html.trim()) {
        this.content = data.html;
      }
    } catch { /* use default */ }
  },
};
</script>

<style>
.cnw-guidelines { display: flex; flex-direction: column; gap: 16px; }
.cnw-guidelines-title {
  font-size: 20px;
  font-weight: 800;
  color: var(--text-dark);
}
.cnw-guidelines-content {
  background: #fff;
  border-radius: var(--radius);
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border);
  padding: 28px 32px;
  font-size: 14px;
  line-height: 1.75;
  color: var(--text-med);
}
.cnw-guidelines-content h2,
.cnw-guidelines-content h3,
.cnw-guidelines-content h4 {
  color: var(--text-dark);
  margin: 20px 0 8px;
  font-weight: 700;
}
.cnw-guidelines-content h2 { font-size: 18px; }
.cnw-guidelines-content h3 { font-size: 16px; }
.cnw-guidelines-content h4 { font-size: 14px; }
.cnw-guidelines-content h2:first-child,
.cnw-guidelines-content h3:first-child,
.cnw-guidelines-content h4:first-child { margin-top: 0; }
.cnw-guidelines-content p {
  margin: 0 0 12px;
}
.cnw-guidelines-content ul,
.cnw-guidelines-content ol {
  margin: 0 0 12px 20px;
  padding: 0;
}
.cnw-guidelines-content li {
  margin-bottom: 4px;
}
.cnw-guidelines-content a {
  color: var(--teal);
}
.cnw-guidelines-content a:hover {
  color: var(--teal-dark);
}
.cnw-guidelines-content em {
  color: var(--text-light);
}
.cnw-guidelines-content strong {
  color: var(--text-dark);
}
</style>
