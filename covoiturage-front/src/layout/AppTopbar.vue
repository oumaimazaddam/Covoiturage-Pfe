<script setup>
import { useLayout } from '@/layout/composables/layout';
import AppConfigurator from './AppConfigurator.vue';
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const { toggleMenu, toggleDarkMode, isDarkTheme } = useLayout();
const router = useRouter();

const isAuthenticated = ref(false);
const role_id = ref(null); // 👈 Ajout du role
const menuOpen = ref(false);

// Vérifie la présence du token au montage
const checkAuth = () => {
  const token = localStorage.getItem('access_token');
  isAuthenticated.value = !!token;
};

const toggleUserMenu = () => {
  menuOpen.value = !menuOpen.value;
};

const closeMenu = () => {
  menuOpen.value = false;
};

const handlePublish = async () => {
  if (!isAuthenticated.value) {
    alert("Vous devez être connecté pour publier un trajet !");
    router.push('/login');
    return;
  }

  try {
    const token = localStorage.getItem('access_token');
    if (!token) throw new Error("Token manquant");

    const response = await axios.get('http://127.0.0.1:8000/api/user', {
      headers: {
        Authorization: `Bearer ${token}`
      }
    });

    if (response.data.role_id === 2) {
      router.push('/ajouter-trajet');
    } else {
      alert("Seuls les conducteurs peuvent publier des trajets !");
    }
  } catch (error) {
    console.error("Erreur de vérification :", error);
    if (error.response?.status === 401) {
      localStorage.removeItem('access_token');
      isAuthenticated.value = false;
      router.push('/login');
    } else {
      alert("Erreur lors de la vérification des permissions");
    }
  }
};

const logout = () => {
  localStorage.removeItem('access_token');
  isAuthenticated.value = false;
  menuOpen.value = false;
  router.push('/logout');
};

onMounted(() => {
  checkAuth();

  // 👇 Récupération du rôle
  const storedRole = localStorage.getItem('user_role');
  role_id.value = storedRole ? Number(storedRole) : null;
});
</script>

<template>
  <div class="layout-topbar">
    <div class="layout-topbar-logo-container">
      <button class="layout-menu-button layout-topbar-action" @click="toggleMenu">
        <i class="pi pi-bars"></i>
      </button>
      <Router-link to="/" class="layout-topbar-logo">
        <svg><!-- Votre SVG logo --></svg>
        <span>Carpool</span>
      </router-link>
    </div>

    <!-- 🔒 Liens visibles uniquement pour les rôles 2 et 3 -->
    <div 
      v-if="role_id === 2 || role_id === 3" 
      class="hidden md:flex items-center gap-6 mx-6"
    >
      <router-link to="/" class="text-color hover:text-primary transition-colors">
        ACCUEIL
      </router-link>
      <router-link to="/details" class="text-color hover:text-primary transition-colors">
        TRAJETS
      </router-link>
      <!-- Bouton Historique des trajets pour role_id=2 -->
      <router-link 
        v-if="role_id === 2"
        to="/trajetH"
        class="text-color hover:text-primary transition-colors"
      >
        HISTORIQUE DES TRAJETS
      </router-link>

      <!-- Bouton Historique des réservations pour role_id=3 -->
      <router-link 
        v-if="role_id === 3"
        to="/reservation-history"
        class="text-color hover:text-primary transition-colors"
      >
        HISTORIQUE DES RÉSERVATIONS
      </router-link>
      <!-- Lien Profil pour les utilisateurs authentifiés avec role_id 2 ou 3 -->
     
      <button 
        @click="handlePublish"
        class="mr-4 bg-primary text-white font-bold py-2 px-4 rounded-lg hover:bg-primary-dark transition"
      >
        PUBLIER UN TRAJET
      </button>
    </div>

    <div class="layout-topbar-actions">
      <div class="layout-config-menu">
        <button type="button" class="layout-topbar-action" @click="toggleDarkMode">
          <i :class="['pi', { 'pi-moon': isDarkTheme, 'pi-sun': !isDarkTheme }]"></i>
        </button>
        <div class="relative">
          <button
            v-styleclass="{ selector: '@next', enterFromClass: 'hidden', enterActiveClass: 'animate-scalein', leaveToClass: 'hidden', leaveActiveClass: 'animate-fadeout', hideOnOutsideClick: true }"
            type="button"
            class="layout-topbar-action layout-topbar-action-highlight"
          >
            <i class="pi pi-palette"></i>
          </button>
          <AppConfigurator />
        </div>

        <!-- Menu utilisateur -->
        <div class="relative ml-2">
          <button @click="toggleUserMenu" class="layout-topbar-action">
            <i class="pi pi-user"></i>
          </button>
          <div v-if="menuOpen" class="absolute right-0 mt-2 w-48 bg-white shadow-lg rounded-lg py-2 z-50">
            <router-link 
              v-if="isAuthenticated" 
              to="/uikit/profile" 
              class="block px-4 py-2 text-color hover:bg-surface-100"
              @click="closeMenu"
            >
              Profil
            </router-link>
            <button 
              to="logout" 
              v-if="isAuthenticated"
              @click="logout"
              class="block w-full text-left px-4 py-2 text-color hover:bg-surface-100"
            >
              Déconnexion
            </button>
            <router-link 
              v-if="!isAuthenticated"
              to="/login"
              class="block px-4 py-2 text-color hover:bg-surface-100"
              @click="closeMenu"
            >
              Se connecter
            </router-link>
            <router-link 
              v-if="!isAuthenticated"
              to="/register"
              class="block px-4 py-2 text-color hover:bg-surface-100"
              @click="closeMenu"
            >
              S'inscrire
            </router-link>
          </div>
        </div>
      </div>

      <button
        class="layout-topbar-menu-button layout-topbar-action"
        v-styleclass="{ selector: '@next', enterFromClass: 'hidden', enterActiveClass: 'animate-scalein', leaveToClass: 'hidden', leaveActiveClass: 'animate-fadeout', hideOnOutsideClick: true }"
      >
        <i class="pi pi-ellipsis-v"></i>
      </button>
    </div>
  </div>
</template>