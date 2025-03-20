<template>
  <nav class="bg-white shadow-md p-4 flex justify-between items-center">
    <!-- Logo -->
    <div class="text-xl font-bold text-gray-700">My Dashboard</div>

    <!-- Navigation Links -->
    <div class="hidden md:flex space-x-6">
      <router-link to="/" class="nav-link">ACCUEIL</router-link>
      <router-link to="/details" class="nav-link">TRAJETS</router-link>
      <router-link to="/contact" class="nav-link">PROFILE</router-link>
    </div>

    <div class="flex items-center space-x-4">
      <!-- Bouton "PUBLIER UN TRAJET" -->
      <button 
        @click="handlePublish"
        class="bg-blue-500 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-600 transition"
      >
        PUBLIER UN TRAJET
      </button>

      <!-- Icône utilisateur avec menu déroulant -->
      <div class="relative">
        <button @click="toggleMenu" class="focus:outline-none">
          <img src="/user-icon.png" alt="User" class="w-10 h-10 rounded-full border border-gray-300">
        </button>

        <!-- Menu déroulant -->
        <div v-if="menuOpen" class="absolute right-0 mt-2 w-48 bg-white shadow-lg rounded-lg py-2 z-50">
          <router-link v-if="isAuthenticated" to="/contact" class="menu-item" @click="closeMenu">Profil</router-link>
          <button v-if="isAuthenticated" @click="logout" class="menu-item">Déconnexion</button>
          <router-link v-if="!isAuthenticated" to="/login" class="menu-item" @click="closeMenu">Se connecter</router-link>
          <router-link v-if="!isAuthenticated" to="/register" class="menu-item" @click="closeMenu">S'inscrire</router-link>
        </div>
      </div>
    </div>
  </nav>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';

export default {
  setup() {
    const router = useRouter();
    const isAuthenticated = ref(false);
    const menuOpen = ref(false);

    const checkAuth = () => {
      const token = localStorage.getItem('access_token');
      isAuthenticated.value = !!token;
    };

    const toggleMenu = () => {
      menuOpen.value = !menuOpen.value;
    };

    const closeMenu = () => {
      menuOpen.value = false;
    };

    const handlePublish = () => {
      if (!isAuthenticated.value) {
        alert("Vous devez être connecté pour publier un trajet !");
        router.push('/login');
        return;
      }
      router.push('/ajouter-trajet');
    };

    const logout = () => {
      localStorage.removeItem('access_token');
      isAuthenticated.value = false;
      menuOpen.value = false;
      router.push('/');
    };

    onMounted(checkAuth);

    return {
      isAuthenticated,
      menuOpen,
      toggleMenu,
      closeMenu,
      handlePublish,
      logout
    };
  },
};
</script>

<style scoped>
.nav-link {
  @apply text-gray-700 hover:text-blue-500 transition;
}

.menu-item {
  @apply block px-4 py-2 text-gray-700 hover:bg-gray-100 w-full text-left;
}
</style>
