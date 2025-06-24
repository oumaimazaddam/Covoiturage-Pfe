import AppLayout from '@/layout/AppLayout.vue';
import { createRouter, createWebHistory } from 'vue-router';
import Home from '../views/SearchTrips.vue';
import PublishTrip from '../views/PublishTrip.vue';
import Reservation from '../views/Reservation.vue';
import Register from '@/components/auth/Register.vue';
import Details from '../views/Details.vue';
import DetailsTrip from '../views/DetailsTrip.vue';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            component: Home,
            name: 'home'
        },
        { path: '/reservation/:tripId', name: 'reservation', component: Reservation},
        { path: '/register', name: 'Register', component: Register },
        { path: '/ajouter-trajet', component: PublishTrip,meta: { requiresAuth: true, requiredRole: 2 } },
        { path: '/details', component: Details },
        {
            path: '/detailsTrip/:id',
            name: 'TripDetails',
            component: DetailsTrip,
            props: true,
            meta: {
              hideSidebar: true
            }
          },
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
                    path: '/uikit/feedbacks',
                    name: 'feedbacks',
                    component: () => import('@/views/uikit/Feedbacks.vue')
                },
                {
                    path: '/uikit/utilisateur',
                    name: 'utilisateur',
                    component: () => import('@/views/uikit/GestionU.vue')
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
                    path: '/trajetH',
                    name: 'trajetH',
                    component: () => import('@/views/HistoriqueTrajet.vue')
                },
                {
                    path: '/reservation-history',
                    name: 'reservation-history',
                    component: () => import('@/views/HistoriqueReservation.vue')
                },

                
                {
                    path: '/messenger',
                    name: 'messenger',
                    component: () => import('@/views/Messenger.vue'),
                     // Automatically pass route params as props
                },
                {
                    path: '/feedbacksP/:tripId',
                    name: 'feedbacksP',
                    component: () => import('@/views/FeedbacksP.vue'),
                    props: true,// Require authentication
                     // Automatically pass route params as props
                },
                
                {
                    path: '/uikit/reservationL',
                    name: 'reservationL',
                    component: () => import('@/views/uikit/ListeR.vue')
                },
               
               
                {
                    path: '/documentation',
                    name: 'documentation',
                    component: () => import('@/views/pages/Documentation.vue')
                }
            ]
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
