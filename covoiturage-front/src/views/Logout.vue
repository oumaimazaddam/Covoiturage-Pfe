<script setup>
import axios from 'axios';
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const logout = async () => {
    try {
        const token = localStorage.getItem('access_token');
        if (token) {
            await axios.post('http://localhost:8000/api/logout', {}, { headers: { Authorization: `Bearer ${token}` } });
        }
    } catch (error) {
        console.error('Erreur lors de la déconnexion', error);
    } finally {
        localStorage.removeItem('access_token');
        router.push('/login');
    }
};

onMounted(() => {
    logout();
});
</script>

<template>
    <div class="flex items-center justify-center min-h-screen bg-gray-100">
        <div class="text-center">
            <p class="text-lg font-semibold text-gray-700">Déconnexion en cours...</p>
        </div>
    </div>
</template>
