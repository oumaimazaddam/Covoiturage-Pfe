<script setup>
import { ref } from 'vue';

import AppMenuItem from './AppMenuItem.vue';
//test
const model = ref([
    {
        label: 'Carpool',
        items: [
            { label: 'Dashboard Admin', icon: 'pi pi-home', to: '/' },
            { label: 'Gestion des droits des utilisateurs', icon: 'pi pi-user', to: '/uikit/utilisateur' },
            { label: 'Liste des Utilisateurs', icon: 'pi pi-users', to: '/uikit/liste' },
            { label: 'Gestion de Trajet', icon: 'pi pi-map', to: '/uikit/trajet', class: 'rotated-icon' },
            { label: 'Feedbacks', icon: 'pi pi-comments', to: '/uikit/feedbacks' },
            { label: 'Financement', icon: 'pi pi-wallet', to: '/uikit/financement' }
        ]
    },
    {
        label: 'UI Components',
        items: [
            {
                label: 'Login',
                icon: 'pi pi-fw pi-sign-in',
                to: '/login'
            },
            { label: 'User Profile', icon: 'pi pi-user', to: '/uikit/profile' },
            { label: 'Messengers', icon: 'pi pi-comment', to: '/uikit/messengers' },
            { label: 'Passengers Dashboard', icon: 'pi pi-id-card', to: '/uikit/passengers' },
            { label: 'Drivers Dashboard', icon: 'pi pi-car', to: '/uikit/drivers' },
            { label: 'Logout', icon: 'pi pi-sign-out', to: '/logout' }
        ]
    },

    {
        label: 'Get Started',
        items: [
            {
                label: 'Documentation',
                icon: 'pi pi-fw pi-book',
                to: '/documentation'
            },
            {
                label: 'View Source',
                icon: 'pi pi-fw pi-github',
                url: 'https://github.com/primefaces/sakai-vue',
                target: '_blank'
            }
        ]
    }
]);
</script>

<template>
    <ul class="layout-menu">
        <template v-for="(item, i) in model" :key="item">
            <app-menu-item v-if="!item.separator" :item="item" :index="i"></app-menu-item>
            <li v-if="item.separator" class="menu-separator"></li>
        </template>
    </ul>
</template>

<style lang="scss" scoped></style>
