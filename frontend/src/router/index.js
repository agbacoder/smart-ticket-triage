import { createRouter, createWebHistory } from 'vue-router'
import Tickets from '../pages/Tickets.vue'
import Dashboard from '../pages/Dashboard.vue'


const routes = [
  { path: '/', redirect: '/dashboard' },
  { path: '/dashboard', name: 'Dashboard', component: Dashboard },
  { path: '/tickets', name: 'Tickets', component: Tickets },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router