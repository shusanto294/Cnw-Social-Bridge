const REST_URL = window.cnwData?.restUrl || '';
const SITE_URL = window.cnwData?.siteUrl || '';
const NONCE = window.cnwData?.nonce || '';
const NS = '/cnw-social-bridge/v1';

const headers = () => ({
  'Content-Type': 'application/json',
  'X-WP-Nonce': NONCE,
});

/**
 * Build a REST API URL.
 * If REST_URL contains /wp-json/, pretty permalinks are enabled — use it directly.
 * Otherwise fall back to ?rest_route= query string format.
 */
const useFallback = !REST_URL || !REST_URL.includes('/wp-json/');

function apiUrl(path, params = {}) {
  const qs = new URLSearchParams(params);

  if (useFallback) {
    qs.set('rest_route', `${NS}${path}`);
    return `${SITE_URL}?${qs}`;
  }

  return `${REST_URL}${path}${qs.toString() ? '?' + qs : ''}`;
}

async function handleResponse(res) {
  const data = await res.json();
  if (data && data.code === 'account_suspended' && data.message) {
    alert(data.message);
    throw new Error(data.message);
  }
  return data;
}

async function apiFetch(path, params = {}, options = {}) {
  const url = apiUrl(path, params);
  const res = await fetch(url, { headers: headers(), ...options });
  return handleResponse(res);
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
  const url = apiUrl('/threads');
  const body = { title, content, tags };
  if (category_id) body.category_id = category_id;
  if (anonymous) body.anonymous = anonymous;
  const res = await fetch(url, {
    method: 'POST',
    headers: headers(),
    body: JSON.stringify(body),
  });
  return handleResponse(res);
}

export async function updateThread(id, { title, content }) {
  const url = apiUrl(`/threads/${id}`);
  const res = await fetch(url, {
    method: 'PUT',
    headers: headers(),
    body: JSON.stringify({ title, content }),
  });
  return handleResponse(res);
}

export async function deleteThread(id) {
  const url = apiUrl(`/threads/${id}`);
  const res = await fetch(url, {
    method: 'DELETE',
    headers: headers(),
  });
  return handleResponse(res);
}

export async function getReplies(threadId) {
  return apiFetch(`/threads/${threadId}/replies`);
}

export async function createReply({ thread_id, content, parent_id }) {
  const url = apiUrl('/replies');
  const body = { thread_id, content };
  if (parent_id) body.parent_id = parent_id;
  const res = await fetch(url, {
    method: 'POST',
    headers: headers(),
    body: JSON.stringify(body),
  });
  return handleResponse(res);
}

export async function updateReply(id, { content }) {
  const url = apiUrl(`/replies/${id}`);
  const res = await fetch(url, {
    method: 'PUT',
    headers: headers(),
    body: JSON.stringify({ content }),
  });
  return handleResponse(res);
}

export async function deleteReply(id) {
  const url = apiUrl(`/replies/${id}`);
  const res = await fetch(url, {
    method: 'DELETE',
    headers: headers(),
  });
  return handleResponse(res);
}

export async function getCategories() {
  return apiFetch('/categories');
}

export async function getTags() {
  return apiFetch('/tags');
}

export async function createTag({ name, description = '' }) {
  const url = apiUrl('/tags');
  const body = { name };
  if (description) body.description = description;
  const res = await fetch(url, {
    method: 'POST',
    headers: headers(),
    body: JSON.stringify(body),
  });
  return handleResponse(res);
}

export async function updateTag({ id, name, slug, description = '' }) {
  const url = apiUrl(`/tags/${id}`);
  const body = { name, description };
  if (slug) body.slug = slug;
  const res = await fetch(url, {
    method: 'PUT',
    headers: headers(),
    body: JSON.stringify(body),
  });
  return handleResponse(res);
}

export async function createCategory({ name }) {
  const url = apiUrl('/categories');
  const res = await fetch(url, {
    method: 'POST',
    headers: headers(),
    body: JSON.stringify({ name }),
  });
  return handleResponse(res);
}

export async function deleteTag(id) {
  const url = apiUrl(`/tags/${id}`);
  const res = await fetch(url, { method: 'DELETE', headers: headers() });
  return handleResponse(res);
}

export async function getFollowedTags() {
  return apiFetch('/tags/followed');
}

export async function followTag(tagId) {
  const url = apiUrl(`/tags/${tagId}/follow`);
  const res = await fetch(url, { method: 'POST', headers: headers() });
  return handleResponse(res);
}

export async function unfollowTag(tagId) {
  const url = apiUrl(`/tags/${tagId}/unfollow`);
  const res = await fetch(url, { method: 'POST', headers: headers() });
  return handleResponse(res);
}

export async function createVote({ target_type, target_id, vote_type }) {
  const url = apiUrl('/votes');
  const res = await fetch(url, {
    method: 'POST',
    headers: headers(),
    body: JSON.stringify({ target_type, target_id, vote_type }),
  });
  return handleResponse(res);
}

export async function saveThread(threadId) {
  const url = apiUrl(`/threads/${threadId}/save`);
  const res = await fetch(url, { method: 'POST', headers: headers() });
  return handleResponse(res);
}

export async function unsaveThread(threadId) {
  const url = apiUrl(`/threads/${threadId}/unsave`);
  const res = await fetch(url, { method: 'POST', headers: headers() });
  return handleResponse(res);
}

export async function getSavedThreads({ page = 1 } = {}) {
  return apiFetch('/saved-threads', { page });
}

export async function getHotQuestions() {
  return apiFetch('/hot-questions');
}

export async function getNotifications({ page = 1, per_page = 10 } = {}) {
  return apiFetch('/notifications', { page, per_page });
}

export async function getUnreadNotificationCount() {
  return apiFetch('/notifications/unread-count');
}

export async function markNotificationRead(id) {
  const url = apiUrl(`/notifications/${id}/read`);
  const res = await fetch(url, { method: 'POST', headers: headers() });
  return handleResponse(res);
}

export async function markAllNotificationsRead() {
  const url = apiUrl('/notifications/read-all');
  const res = await fetch(url, { method: 'POST', headers: headers() });
  return handleResponse(res);
}

export async function logout() {
  const url = apiUrl('/logout');
  const res = await fetch(url, { method: 'POST', headers: headers() });
  return handleResponse(res);
}

export async function forgotPassword({ user_login }) {
  const url = apiUrl('/forgot-password');
  const res = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ user_login }),
  });
  return handleResponse(res);
}

export async function markSolution(replyId) {
  const url = apiUrl(`/replies/${replyId}/solution`);
  const res = await fetch(url, { method: 'POST', headers: headers() });
  return handleResponse(res);
}

export async function getUserReputation(userId) {
  return apiFetch(`/users/${userId}/reputation`);
}

export async function register({ username, email, password, first_name, last_name }) {
  const url = apiUrl('/register');
  const res = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ username, email, password, first_name, last_name }),
  });
  const data = await res.json();
  if (!res.ok) throw new Error(data.message || 'Registration failed');
  return data;
}

export async function getUsers({ page = 1, per_page = 20, search = '' } = {}) {
  const params = { page, per_page };
  if (search) params.search = search;
  return apiFetch('/users', params);
}

export async function getUser(userId) {
  return apiFetch(`/users/${userId}`);
}

export async function getUserThreads(userId, { page = 1 } = {}) {
  return apiFetch(`/users/${userId}/threads`, { page });
}

export async function getUserReplies(userId, { page = 1 } = {}) {
  return apiFetch(`/users/${userId}/replies`, { page });
}

export async function updateUserProfile({ first_name, last_name, email, phone, bio, verified_label, professional_title }) {
  const url = apiUrl('/users/me/profile');
  const res = await fetch(url, {
    method: 'PUT',
    headers: headers(),
    body: JSON.stringify({ first_name, last_name, email, phone, bio, verified_label, professional_title }),
  });
  return handleResponse(res);
}

export async function toggleAnonymous() {
  const url = apiUrl('/users/me/anonymous');
  const res = await fetch(url, { method: 'POST', headers: headers() });
  return handleResponse(res);
}

export async function getUserActivity({ page = 1 } = {}) {
  return apiFetch('/users/me/activity', { page });
}

export async function uploadAvatar(file) {
  const url = apiUrl('/users/me/avatar');
  const formData = new FormData();
  formData.append('file', file);
  const res = await fetch(url, {
    method: 'POST',
    headers: { 'X-WP-Nonce': NONCE },
    body: formData,
  });
  return handleResponse(res);
}

export async function getConversations({ page = 1 } = {}) {
  return apiFetch('/conversations', { page });
}

export async function getUnreadMessageCount() {
  return apiFetch('/messages/unread-count');
}

export async function getConversation(userId, { before = 0 } = {}) {
  const params = {};
  if (before) params.before = before;
  return apiFetch(`/conversations/${userId}`, params);
}

export async function sendMessage({ recipient_id, content }) {
  const url = apiUrl('/messages');
  const res = await fetch(url, {
    method: 'POST',
    headers: headers(),
    body: JSON.stringify({ recipient_id, content }),
  });
  return handleResponse(res);
}

export async function markConversationRead(userId) {
  const url = apiUrl(`/conversations/${userId}/read`);
  const res = await fetch(url, { method: 'POST', headers: headers() });
  return handleResponse(res);
}

export async function setTyping(userId) {
  const url = apiUrl(`/typing/${userId}`);
  const res = await fetch(url, { method: 'POST', headers: headers() });
  return handleResponse(res);
}

export async function getTyping(userId) {
  return apiFetch(`/typing/${userId}`);
}

export async function broadcastStatus(status) {
  const url = apiUrl('/pusher/status');
  const res = await fetch(url, {
    method: 'POST',
    headers: headers(),
    body: JSON.stringify({ status }),
  });
  return handleResponse(res);
}

export async function submitReport({ type, subject, link, description, priority, content_type, content_id }) {
  const url = apiUrl('/reports');
  const body = { type, subject, link, description, priority };
  if (content_type) body.content_type = content_type;
  if (content_id) body.content_id = content_id;
  const res = await fetch(url, {
    method: 'POST',
    headers: headers(),
    body: JSON.stringify(body),
  });
  return handleResponse(res);
}

export async function getGuidelines() {
  return apiFetch('/guidelines');
}

export async function login({ username, password }) {
  const url = apiUrl('/login');
  const res = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ username, password }),
  });
  const data = await res.json();
  if (!res.ok) throw new Error(data.message || 'Login failed');
  return data;
}

// ── Connections ──────────────────────────────────────────────

export async function getConnections({ page = 1, search = '' } = {}) {
  const params = { page };
  if (search) params.search = search;
  return apiFetch('/connections', params);
}

export async function getConnectionRequests() {
  return apiFetch('/connections/requests');
}

export async function sendConnectionRequest(userId) {
  const url = apiUrl(`/connections/${userId}`);
  const res = await fetch(url, { method: 'POST', headers: headers() });
  return handleResponse(res);
}

export async function acceptConnection(userId) {
  const url = apiUrl(`/connections/${userId}/accept`);
  const res = await fetch(url, { method: 'POST', headers: headers() });
  return handleResponse(res);
}

export async function declineConnection(userId) {
  const url = apiUrl(`/connections/${userId}/decline`);
  const res = await fetch(url, { method: 'POST', headers: headers() });
  return handleResponse(res);
}

export async function removeConnection(userId) {
  const url = apiUrl(`/connections/${userId}`);
  const res = await fetch(url, { method: 'DELETE', headers: headers() });
  return handleResponse(res);
}

export async function getConnectionStatus(userId) {
  return apiFetch(`/connections/status/${userId}`);
}

// ── Restrictions ──────────────────────────────────────────────

export async function restrictUser(userId) {
  const url = apiUrl(`/restrict/${userId}`);
  const res = await fetch(url, { method: 'POST', headers: headers() });
  return handleResponse(res);
}

export async function unrestrictUser(userId) {
  const url = apiUrl(`/unrestrict/${userId}`);
  const res = await fetch(url, { method: 'POST', headers: headers() });
  return handleResponse(res);
}

export async function getRestrictions() {
  return apiFetch('/restrictions');
}

// ── Moderation ──────────────────────────────────────────────

export async function closeThread(threadId) {
  const url = apiUrl(`/threads/${threadId}/close`);
  const res = await fetch(url, { method: 'POST', headers: headers() });
  return handleResponse(res);
}

export async function pinThread(threadId) {
  const url = apiUrl(`/threads/${threadId}/pin`);
  const res = await fetch(url, { method: 'POST', headers: headers() });
  return handleResponse(res);
}

export async function getReports({ page = 1, status = '' } = {}) {
  const params = { page };
  if (status) params.status = status;
  return apiFetch('/reports', params);
}

export async function updateReport(id, { status, admin_notes }) {
  const url = apiUrl(`/reports/${id}`);
  const res = await fetch(url, {
    method: 'PUT',
    headers: headers(),
    body: JSON.stringify({ status, admin_notes }),
  });
  return handleResponse(res);
}

export async function warnUser(userId, { reason }) {
  const url = apiUrl(`/users/${userId}/warn`);
  const res = await fetch(url, {
    method: 'POST',
    headers: headers(),
    body: JSON.stringify({ reason }),
  });
  return handleResponse(res);
}

export async function suspendUser(userId, { reason, duration }) {
  const url = apiUrl(`/users/${userId}/suspend`);
  const res = await fetch(url, {
    method: 'POST',
    headers: headers(),
    body: JSON.stringify({ reason, duration }),
  });
  return handleResponse(res);
}

export async function getModerationStats() {
  return apiFetch('/moderation/stats');
}

export async function getWarnings({ page = 1 } = {}) {
  return apiFetch('/moderation/warnings', { page });
}

export async function deleteWarning(id) {
  const url = apiUrl(`/moderation/warnings/${id}`);
  const res = await fetch(url, { method: 'DELETE', headers: headers() });
  return handleResponse(res);
}

export async function getUserWarnings(userId, { page = 1 } = {}) {
  return apiFetch(`/users/${userId}/warnings`, { page });
}
