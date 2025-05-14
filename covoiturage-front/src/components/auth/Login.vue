<script setup>
import axios from 'axios';
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const email = ref('');
const password = ref('');
const remember = ref(false);
const errorMessage = ref('');
const loading = ref(false);
const router = useRouter();

const submitLogin = async () => {
    if (!email.value || !password.value) {
        errorMessage.value = 'Les deux champs sont obligatoires.';
        return;
    }

    loading.value = true;
    try {
        const response = await axios.post('http://localhost:8000/api/login', {
            email: email.value,
            password: password.value
        });

        const token = response.data.access_token;
        const user = response.data.user;

        localStorage.setItem('access_token', token);
        localStorage.setItem('user_id', user.id);
        localStorage.setItem('user_role', user.role_id); // Stocker le rôle

        console.log('User ID enregistré:', user.id);
        console.log('Role ID enregistré:', user.role_id);

        // Redirection selon le rôle
        if (user.role_id === 2) {
            router.push('/trajetH'); // Driver → Publier un trajet
        } else if (user.role_id === 1) {
            router.push('/dashboard'); // Admin → Dashboard
        } else if (user.role_id === 3) {
            router.push('/'); // Passenger → Accueil
        } else {
            router.push('/'); // Passenger → Publier un trajet
        }
    }  catch (error) {
    console.error('Erreur de connexion:', error); // Log complet de l'erreur
    
    // Vérifier le type d'erreur et afficher un message personnalisé
    if (error.response) {
        // Erreur liée à la réponse du serveur (mauvais identifiants, etc.)
        if (error.response.status === 401) {
            errorMessage.value = 'Identifiants incorrects. Vérifie ton email et ton mot de passe.';
        } else {
            errorMessage.value = error.response?.data?.message || 'Accès refusé, vous navez pas les autorisations nécessaires.';
        }
    } else if (error.request) {
        // Erreur liée à la demande (problème de connexion réseau)
        errorMessage.value = 'Problème de connexion. Assure-toi d\'avoir une connexion Internet et réessaie.';
    } else {
        // Autre erreur (par exemple, une erreur dans le code)
        errorMessage.value = 'Une erreur inattendue s\'est produite. Veuillez réessayer plus tard.';
    }
} finally {
    loading.value = false;
}
};
</script>


<template>
    
    <div> 
        
  
    <div class="bg-surface-50 dark:bg-surface-950 flex items-center justify-center min-h-screen min-w-[100vw] overflow-hidden">
        <div class="flex flex-col items-center justify-center">
            <div style="border-radius: 56px; padding: 0.3rem; background: linear-gradient(180deg, var(--primary-color) 10%, rgba(33, 150, 243, 0) 30%)">
                <div class="w-full bg-surface-0 dark:bg-surface-900 py-20 px-8 sm:px-20" style="border-radius: 53px">
                    <div class="text-center mb-8">
                        
                        <div class="text-surface-900 dark:text-surface-0 text-3xl font-medium mb-4">Bienvenue Covoiturage</div>
                        <span class="text-muted-color font-medium">Se connecter</span>
                    </div>
                    


                                 <form @submit.prevent="submitLogin">
                            <div class="field mb-3">
                                <label for="email" class="block text-sm font-medium text-900">Email</label>
                                <input type="email" id="email" v-model="email"  class="w-full p-3 border-2 border-solid border-surface-300 dark:border-surface-600 rounded-md 
         focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:focus:ring-primary-800 
         transition-colors duration-200 bg-surface-50 dark:bg-surface-800" placeholder="Enterz votre email" />
                            </div>

                            <div class="field mb-3">
                                <label for="password" class="block text-sm font-medium text-900">Mot de passe</label>
                                <input type="password" id="password" v-model="password"  class="w-full p-3 border-2 border-solid border-surface-300 dark:border-surface-600 rounded-md 
         focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:focus:ring-primary-800 
         transition-colors duration-200 bg-surface-50 dark:bg-surface-800" placeholder="Enterz votre mot de passe" />
                            </div>
                            

                            <div class="field mb-3">
                                <label for="remember" class="flex items-center text-sm font-medium text-900">
                                    <input type="checkbox" id="remember" v-model="remember" class="mr-2" />
                                   Souviens-toi de moi
                                </label>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="w-full py-3 mt-6 bg-primary-500 text-white font-medium text-lg rounded-md hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500" :disabled="loading">
                                    <span v-if="loading">Chargement...</span>
                                    <span v-else>Se connecter</span>
                                </button>
                            </div>
                            

                            <div class="text-center text-sm mt-4">
      Vous n'avez pas de compte ?
      <router-link to="/register" class="text-primary-500 hover:underline">S'inscrire</router-link>
    </div>
    <p v-if="errorMessage" class="text-red-500 mb-4 text-center">
  {{ errorMessage }}
</p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</template>


<style scoped>
.pi-eye {
    transform: scale(1.6);
    margin-right: 1rem;
}

.pi-eye-slash {
    transform: scale(1.6);
    margin-right: 1rem;
}
</style>