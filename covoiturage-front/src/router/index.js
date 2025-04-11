import AppLayout from '@/layout/AppLayout.vue';
import { createRouter, createWebHistory } from 'vue-router';
import Home from '../views/SearchTrips.vue';
import PublishTrip from '../views/PublishTrip.vue';

import Register from '@/components/auth/Register.vue';
import Details from '../views/Details.vue';
import ConfirmationPage from '../views/ConfirmationPage.vue';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            component: Home,
            name: 'home'
        },
       
        { path: '/register', name: 'Register', component: Register },
        { path: '/ajouter-trajet', component: PublishTrip },
        { path: '/details', component: Details },
        { path: '/confirmation', component: ConfirmationPage },
        {
            path: '/dashboard',
            component: AppLayout,
            children: [
                {
                    path: '/dashboard',
                    name: 'dashboard',
                    component: () => import('@/views/Dashboard.vue')
                },
        
                {
                    path: '/uikit/profile',
                    name: 'profile',
                    component: () => import('@/views/uikit/Profile.vue')
                },
                {
                    path: '/uikit/input',
                    name: 'input',
                    component: () => import('@/views/uikit/InputDoc.vue')
                },
                {
                    path: '/uikit/button',
                    name: 'button',
                    component: () => import('@/views/uikit/ButtonDoc.vue')
                },
                {
                    path: '/uikit/utilisateur',
                    name: 'utilisateur',
                    component: () => import('@/views/uikit/GestionU.vue')
                },
                {
                    path: '/uikit/list',
                    name: 'list',
                    component: () => import('@/views/uikit/ListDoc.vue')
                },
                {
                    path: '/uikit/list',
                    name: 'list',
                    component: () => import('@/views/uikit/ListU.vue')
                },
                {
                    path: '/uikit/trajet',
                    name: 'trajet',
                    component: () => import('@/views/uikit/TrajetG.vue')
                },

                {
                    path: '/uikit/overlay',
                    name: 'overlay',
                    component: () => import('@/views/uikit/OverlayDoc.vue')
                },
                {
                    path: '/uikit/media',
                    name: 'media',
                    component: () => import('@/views/uikit/MediaDoc.vue')
                },
                {
                    path: '/messenger',
                    name: 'messenger',
                    component: () => import('@/views/Messenger.vue'),
                     // Automatically pass route params as props
                },
                {
                    path: '/uikit/file',
                    name: 'file',
                    component: () => import('@/views/uikit/FileDoc.vue')
                },
                {
                    path: '/uikit/menu',
                    name: 'menu',
                    component: () => import('@/views/uikit/MenuDoc.vue')
                },
                {
                    path: '/uikit/charts',
                    name: 'charts',
                    component: () => import('@/views/uikit/ChartDoc.vue')
                },
                {
                    path: '/uikit/misc',
                    name: 'misc',
                    component: () => import('@/views/uikit/MiscDoc.vue')
                },
                {
                    path: '/uikit/timeline',
                    name: 'timeline',
                    component: () => import('@/views/uikit/TimelineDoc.vue')
                },
                {
                    path: '/pages/empty',
                    name: 'empty',
                    component: () => import('@/views/pages/Empty.vue')
                },
                {
                    path: '/pages/crud',
                    name: 'crud',
                    component: () => import('@/views/pages/Crud.vue')
                },
                {
                    path: '/documentation',
                    name: 'documentation',
                    component: () => import('@/views/pages/Documentation.vue')
                }
            ]
        },
        {
            path: '/landing',
            name: 'landing',
            component: () => import('@/views/pages/Landing.vue')
        },
        {
            path: '/logout',
            name: 'logout',
            component: () => import('@/components/auth/Login.vue')
        },

        {
            path: '/login',
            name: 'login',
            component: () => import('@/components/auth/Login.vue')
        },
       
    ]
});


export default router;
