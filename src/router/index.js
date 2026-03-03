import { createRouter, createWebHashHistory } from 'vue-router';
import QuestionListView from '@/components/views/QuestionListView.vue';
import ThreadDetailView from '@/components/views/ThreadDetailView.vue';
import AskQuestionView from '@/components/views/AskQuestionView.vue';
import MessagesView from '@/components/views/MessagesView.vue';
import UsersView from '@/components/views/UsersView.vue';

const routes = [
  { path: '/', component: QuestionListView },
  { path: '/thread/:id', component: ThreadDetailView },
  { path: '/ask', component: AskQuestionView },
  { path: '/messages', component: MessagesView },
  { path: '/users', component: UsersView },
  { path: '/tags', redirect: '/' },
  { path: '/activity', redirect: '/' },
];

export default createRouter({
  history: createWebHashHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 };
  },
});
