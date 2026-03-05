const REST_URL = window.cnwData?.restUrl || '';
const SITE_URL = window.cnwData?.siteUrl || '';
const NONCE = window.cnwData?.nonce || '';
const NS = '/cnw-social-bridge/v1';

const headers = () => ({
  'Content-Type': 'application/json',
  'X-WP-Nonce': NONCE,
});

/**
 * Build a REST API URL that works with both pretty permalinks and plain query-string fallback.
 * Tries the pretty /wp-json/ URL first; if it 404s, subsequent calls use ?rest_route= instead.
 */
let useFallback = null; // null = untested, true = use ?rest_route=

async function apiUrl(path, params = {}) {
  const qs = new URLSearchParams(params);

  // If we already know pretty URLs fail, go straight to fallback
  if (useFallback) {
    qs.set('rest_route', `${NS}${path}`);
    return `${SITE_URL}?${qs}`;
  }

  const pretty = `${REST_URL}${path}${qs.toString() ? '?' + qs : ''}`;

  if (useFallback === null) {
    // First call — probe the pretty URL
    try {
      const probe = await fetch(pretty, { method: 'HEAD', headers: headers() });
      if (probe.ok || probe.status === 401 || probe.status === 403) {
        useFallback = false;
        return pretty;
      }
    } catch { /* network error */ }
    // Pretty URL failed — switch to fallback permanently
    useFallback = true;
    qs.set('rest_route', `${NS}${path}`);
    return `${SITE_URL}?${qs}`;
  }

  return pretty;
}

async function apiFetch(path, params = {}, options = {}) {
  const url = await apiUrl(path, params);
  const res = await fetch(url, { headers: headers(), ...options });
  return res.json();
}

export async function getThreads({ page = 1, filter = 'newest', search = '' } = {}) {
  const params = { page, filter };
  if (search) params.search = search;
  return apiFetch('/threads', params);
}

export async function getThread(id) {
  return apiFetch(`/threads/${id}`);
}

export async function createThread({ title, content, tags = [], category_id, anonymous }) {
  const url = await apiUrl('/threads');
  const body = { title, content, tags };
  if (category_id) body.category_id = category_id;
  if (anonymous) body.anonymous = anonymous;
  const res = await fetch(url, {
    method: 'POST',
    headers: headers(),
    body: JSON.stringify(body),
  });
  return res.json();
}

export async function updateThread(id, { title, content }) {
  const url = await apiUrl(`/threads/${id}`);
  const res = await fetch(url, {
    method: 'PUT',
    headers: headers(),
    body: JSON.stringify({ title, content }),
  });
  return res.json();
}

export async function deleteThread(id) {
  const url = await apiUrl(`/threads/${id}`);
  const res = await fetch(url, {
    method: 'DELETE',
    headers: headers(),
  });
  return res.json();
}

export async function getReplies(threadId) {
  return apiFetch(`/threads/${threadId}/replies`);
}

export async function createReply({ thread_id, content, parent_id }) {
  const url = await apiUrl('/replies');
  const body = { thread_id, content };
  if (parent_id) body.parent_id = parent_id;
  const res = await fetch(url, {
    method: 'POST',
    headers: headers(),
    body: JSON.stringify(body),
  });
  return res.json();
}

export async function getCategories() {
  return apiFetch('/categories');
}

export async function getTags() {
  return apiFetch('/tags');
}

export async function createTag({ name, description = '' }) {
  const url = await apiUrl('/tags');
  const body = { name };
  if (description) body.description = description;
  const res = await fetch(url, {
    method: 'POST',
    headers: headers(),
    body: JSON.stringify(body),
  });
  return res.json();
}

export async function updateTag({ id, name, description = '' }) {
  const url = await apiUrl(`/tags/${id}`);
  const body = { name, description };
  const res = await fetch(url, {
    method: 'PUT',
    headers: headers(),
    body: JSON.stringify(body),
  });
  return res.json();
}

export async function createCategory({ name }) {
  const url = await apiUrl('/categories');
  const res = await fetch(url, {
    method: 'POST',
    headers: headers(),
    body: JSON.stringify({ name }),
  });
  return res.json();
}

export async function deleteTag(id) {
  const url = await apiUrl(`/tags/${id}`);
  const res = await fetch(url, { method: 'DELETE', headers: headers() });
  return res.json();
}

export async function getFollowedTags() {
  return apiFetch('/tags/followed');
}

export async function followTag(tagId) {
  const url = await apiUrl(`/tags/${tagId}/follow`);
  const res = await fetch(url, { method: 'POST', headers: headers() });
  return res.json();
}

export async function unfollowTag(tagId) {
  const url = await apiUrl(`/tags/${tagId}/unfollow`);
  const res = await fetch(url, { method: 'POST', headers: headers() });
  return res.json();
}

export async function createVote({ target_type, target_id, vote_type }) {
  const url = await apiUrl('/votes');
  const res = await fetch(url, {
    method: 'POST',
    headers: headers(),
    body: JSON.stringify({ target_type, target_id, vote_type }),
  });
  return res.json();
}

export async function saveThread(threadId) {
  const url = await apiUrl(`/threads/${threadId}/save`);
  const res = await fetch(url, { method: 'POST', headers: headers() });
  return res.json();
}

export async function unsaveThread(threadId) {
  const url = await apiUrl(`/threads/${threadId}/unsave`);
  const res = await fetch(url, { method: 'POST', headers: headers() });
  return res.json();
}

export async function getSavedThreads() {
  return apiFetch('/saved-threads');
}

export async function getHotQuestions() {
  return apiFetch('/hot-questions');
}

export async function getNotifications({ page = 1 } = {}) {
  return apiFetch('/notifications', { page });
}

export async function getUnreadNotificationCount() {
  return apiFetch('/notifications/unread-count');
}

export async function markNotificationRead(id) {
  const url = await apiUrl(`/notifications/${id}/read`);
  const res = await fetch(url, { method: 'POST', headers: headers() });
  return res.json();
}

export async function markAllNotificationsRead() {
  const url = await apiUrl('/notifications/read-all');
  const res = await fetch(url, { method: 'POST', headers: headers() });
  return res.json();
}
