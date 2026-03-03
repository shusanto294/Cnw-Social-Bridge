const BASE_URL = window.cnwData?.restUrl || '';
const NONCE = window.cnwData?.nonce || '';

const headers = () => ({
  'Content-Type': 'application/json',
  'X-WP-Nonce': NONCE,
});

export async function getThreads({ page = 1, filter = 'newest', search = '' } = {}) {
  const params = new URLSearchParams({ page, filter });
  if (search) params.append('search', search);
  const res = await fetch(`${BASE_URL}/threads?${params}`, { headers: headers() });
  return res.json();
}

export async function getThread(id) {
  const res = await fetch(`${BASE_URL}/threads/${id}`, { headers: headers() });
  return res.json();
}

export async function createThread({ title, content, tags = [] }) {
  const res = await fetch(`${BASE_URL}/threads`, {
    method: 'POST',
    headers: headers(),
    body: JSON.stringify({ title, content, tags }),
  });
  return res.json();
}

export async function getReplies(threadId) {
  const res = await fetch(`${BASE_URL}/threads/${threadId}/replies`, { headers: headers() });
  return res.json();
}

export async function createReply({ thread_id, content }) {
  const res = await fetch(`${BASE_URL}/replies`, {
    method: 'POST',
    headers: headers(),
    body: JSON.stringify({ thread_id, content }),
  });
  return res.json();
}

export async function getTags() {
  const res = await fetch(`${BASE_URL}/tags`, { headers: headers() });
  return res.json();
}

export async function getHotQuestions() {
  const res = await fetch(`${BASE_URL}/hot-questions`, { headers: headers() });
  return res.json();
}
