<template>
  <span class="cnw-social-worker-tag-badge" :class="colorClass">{{ label }}</span>
</template>

<script>
const TAG_COLORS = {
  'emergency housing':      'cnw-social-worker-tag-teal',
  'shelter overflow':       'cnw-social-worker-tag-teal',
  'adult services':         'cnw-social-worker-tag-teal',
  'trauma-informed care':   'cnw-social-worker-tag-teal',
  'therapy referrals':      'cnw-social-worker-tag-teal',
  'uninsured adults':       'cnw-social-worker-tag-teal',
  'mental health services': 'cnw-social-worker-tag-teal',
  'crisis intervention':    'cnw-social-worker-tag-orange',
  'domestic violence':      'cnw-social-worker-tag-orange',
  'youth services':         'cnw-social-worker-tag-green',
  'youth & family support': 'cnw-social-worker-tag-green',
  'mckinney-vento':         'cnw-social-worker-tag-green',
  'school stability':       'cnw-social-worker-tag-green',
  'housing insecurity':     'cnw-social-worker-tag-green',
  'substance use services': 'cnw-social-worker-tag-orange',
  'benefits & eligibility': 'cnw-social-worker-tag-teal',
  'legal & advocacy':       'cnw-social-worker-tag-teal',
};

export default {
  name: 'TagBadge',
  props: {
    label: { type: String, required: true },
  },
  computed: {
    colorClass() {
      return TAG_COLORS[this.label.toLowerCase()] || 'cnw-social-worker-tag-teal';
    },
  },
};
</script>
