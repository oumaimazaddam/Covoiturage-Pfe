<script setup>
import { ref } from 'vue';
import AppMenuItem from './AppMenuItem.vue';

// Récupère le role_id et le convertit bien en nombre
const role_id = Number(localStorage.getItem('user_role'));

// Déclare le menu model
const model = ref([]);

if (role_id === 1) {
    // Admin : accès à tout
    model.value = [
        {
            label: 'Covoiturage',
            items: [
                { label: 'Administrateur du tableau de bord', icon: 'pi pi-home', to: '/dashboard' },
                { label: 'Gestion des droits des utilisateurs', icon: 'pi pi-user', to: '/uikit/utilisateur' },
                { label: 'Utilisateurs En attent', icon: 'pi pi-users', to: '/uikit/list' },
                { label: 'Gestion de Trajet', icon: 'pi pi-map', to: '/uikit/trajet', class: 'rotated-icon' },
                { label: 'Liste de Reservation', icon: 'pi pi-book	', to: '/uikit/reservationL' },
                { label: 'Commentaires', icon: 'pi pi-comments', to: '/uikit/feedbacks' }
               
                

            ]
        },
        {
            label: 'Composants de linterface',
            items: [
                
                { label: 'Profil utilisateur', icon: 'pi pi-user', to: '/uikit/profile' },
               
                { label: 'Déconnexion', icon: 'pi pi-sign-out', to: '/logout' }
            ]
        },
        
    ];
} else {
    // Rôles 2 et 3 : menu limité
    model.value = [
        {
            label: 'UI Components',
            items: [
               
                { label: 'Profil utilisateur', icon: 'pi pi-user', to: '/uikit/profile' },
               
                { label: 'Déconnexion', icon: 'pi pi-sign-out', to: '/logout' }
            ]
        }
    ];
}
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
