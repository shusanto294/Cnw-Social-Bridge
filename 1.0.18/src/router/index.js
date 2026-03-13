import { createRouter, createWebHashHistory } from 'vue-router';

const QuestionListView = () => import('@/components/views/QuestionListView.vue');
const ThreadDetailView = () => import('@/components/views/ThreadDetailView.vue');
const AskQuestionView = () => import('@/components/views/AskQuestionView.vue');
const MessagesView = () => import('@/components/views/MessagesView.vue');
const UsersView = () => import('@/components/views/UsersView.vue');
const TagsView = () => import('@/components/views/TagsView.vue');
const ActivityView = () => import('@/components/views/ActivityView.vue');
const SavedThreadsView = () => import('@/components/views/SavedThreadsView.vue');
const GuidelinesView = () => import('@/components/views/GuidelinesView.vue');
const ReportIssueView = () => import('@/components/views/ReportIssueView.vue');
const UserProfileView = () => import('@/components/views/UserProfileView.vue');
const LoginView = () => import('@/components/views/LoginView.vue');
const NotFoundView = () => import('@/components/views/NotFoundView.vue');
const ModerationView = () => import('@/components/views/ModerationView.vue');

const routes = [
  { path: '/', component: QuestionListView },
  { path: '/thread/:id', component: ThreadDetailView },
  { path: '/ask', component: AskQuestionView, meta: { requiresAuth: true } },
  { path: '/messages', component: MessagesView, meta: { requiresAuth: true } },
  { path: '/users', component: UsersView, meta: { requiresAuth: true } },
  { path: '/users/:id', component: UserProfileView, props: true },
  { path: '/profile', component: UserProfileView },
  { path: '/tags', component: TagsView },
  { path: '/activity', component: ActivityView, meta: { requiresAuth: true } },
  { path: '/saved', component: SavedThreadsView, meta: { requiresAuth: true } },
  { path: '/guidelines', component: GuidelinesView },
  { path: '/report', component: ReportIssueView, meta: { requiresAuth: true } },
  { path: '/login', name: 'Login', component: LoginView, meta: { guestOnly: true } },
  { path: '/moderation', component: ModerationView, meta: { requiresAuth: true } },
  { path: '/:pathMatch(.*)*', name: 'NotFound', component: NotFoundView },
];

const router = createRouter({
  history: createWebHashHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 };
  },
});

router.beforeEach((to, from, next) => {
  const userId = window.cnwData && window.cnwData.currentUser && window.cnwData.currentUser.id;
  if (to.meta.requiresAuth && !userId) {
    return next({ path: '/login', query: { redirect: to.fullPath } });
  }
  if (to.meta.guestOnly && userId) {
    return next('/');
  }
  next();
});

export default router;
