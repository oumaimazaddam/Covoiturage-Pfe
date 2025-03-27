<script setup>
import { ref } from 'vue';
import AppMenuItem from './AppMenuItem.vue';

const model = ref([
    {
        label: 'Carpool',
        items: [
            { label: 'Dashboard Admin', icon: 'pi pi-home', to: '/dashboard' },
            { label: 'Gestion des droits des utilisateurs', icon: 'pi pi-user', to: '/uikit/utilisateur' },
            { label: 'Utilisateurs En attent', icon: 'pi pi-users', to: '/uikit/list' },
            { label: 'Gestion de Trajet', icon: 'pi pi-map', to: '/uikit/trajet', class: 'rotated-icon' },
            { label: 'Feedbacks', icon: 'pi pi-comments', to: '/uikit/feedbacks' },
            { label: 'Financement', icon: 'pi pi-wallet', to: '/uikit/financement' }
        ]
    },
    {
        label: 'UI Components',
        items: [
            { label: 'Login', icon: 'pi pi-fw pi-sign-in', to: '/login' },
            { label: 'User Profile', icon: 'pi pi-user', to: '/uikit/profile' },
            { label: 'Messengers', icon: 'pi pi-comment', to: '/messenger' },
            { label: 'Passengers Dashboard', icon: 'pi pi-id-card', to: '/uikit/passengers' },
            { label: 'Drivers Dashboard', icon: 'pi pi-car', to: '/uikit/drivers' },
            { label: 'Logout', icon: 'pi pi-sign-out', to: '/logout' }
        ]
    },
    {
        label: 'Get Started',
        items: [
            { label: 'Documentation', icon: 'pi pi-fw pi-book', to: '/documentation' },
            { label: 'View Source', icon: 'pi pi-fw pi-github', url: 'https://github.com/primefaces/sakai-vue', target: '_blank' }
        ]
    }
]);
</script>

<template>
    <ul class="layout-menu">
        <li v-for="(item, i) in model" :key="i"> <!-- Appliquez la clé sur un élément réel -->
            <template v-if="!item.separator">
                <app-menu-item :item="item" :index="i"></app-menu-item>
            </template>
            <template v-else> <!-- Appliquez v-else à un template -->
                <li class="menu-separator"></li> <!-- Séparateur -->
            </template>
        </li>
    </ul>
</template>

<style lang="scss" scoped></style>
