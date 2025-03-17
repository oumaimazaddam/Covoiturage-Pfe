<script>
import axios from 'axios';
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';

export default {
    setup() {
        const profile = ref({
            name: '',
            email: '',
            phone_number: '',
            photo_profile: '',
            password: '',
            password_confirmation: ''
        });

        const profilePreview = ref(null);
        const successMessage = ref('');
        const router = useRouter();
        const userId = localStorage.getItem('user_id');
        const accessToken = localStorage.getItem('access_token');
        console.log('User ID:', userId);
        console.log('Access Token:', accessToken);

        const fetchProfile = async () => {
            if (!accessToken || !userId) {
                console.error('Token ou ID utilisateur introuvable');
                return;
            }

            try {
                const response = await axios.get(`http://localhost:8000/api/show-profile/${userId}`, {
                    headers: { Authorization: `Bearer ${accessToken}` }
                });
                profile.value = response.data.user;
            } catch (error) {
                console.error('Erreur chargement profil :', error.response?.data || error.message);
            }
        };

        const handleFileUpload = (event) => {
            const file = event.target.files[0];
            profile.value.photo_profile = file;

            const reader = new FileReader();
            reader.onload = (e) => {
                profilePreview.value = e.target.result;
            };
            reader.readAsDataURL(file);
        };

        const updateProfile = async () => {
            if (!accessToken || !userId) {
                console.error('Token ou ID utilisateur manquant !');
                return;
            }

            const formData = new FormData();
            formData.append('_method', 'PUT'); // 🔥 Ajout de _method pour simuler un PUT

            formData.append('id', userId);
            formData.append('name', profile.value.name);
            formData.append('email', profile.value.email);
            formData.append('phone_number', profile.value.phone_number);

            formData.append('password', profile.value.password);
            formData.append('password_confirmation', profile.value.password_confirmation);

            if (profile.value.photo_profile) {
                formData.append('photo_profile', profile.value.photo_profile);
            }

            console.log('FormData entries:', [...formData.entries()]);

            try {
                const response = await axios.post(`http://localhost:8000/api/update-profile/${userId}`, formData, {
                    headers: {
                        Authorization: `Bearer ${accessToken}`
                    }
                });

                console.log('Réponse API :', response.data);

                // Mise à jour des données du profil dans le frontend
                profile.value = { ...profile.value, ...response.data.user };

                // Message de succès
                successMessage.value = response.data.message;
                alert(response.data.message);
            } catch (error) {
                // Gestion des erreurs
                console.error('Erreur mise à jour :', error.response?.data || error.message);
                alert('Une erreur est survenue lors de la mise à jour du profil.');
            }
        };

        const deleteProfile = async () => {
            if (!accessToken || !userId) return;

            try {
                await axios.delete(`http://localhost:8000/api/delete-profile/${userId}`, {
                    headers: { Authorization: `Bearer ${accessToken}` }
                });
                alert('Profil supprimé !');
                localStorage.removeItem('access_token');
                router.push('/login');
            } catch (error) {
                console.error('Erreur suppression du profil :', error.response?.data || error.message);
            }
        };

        onMounted(fetchProfile);

        return { profile, profilePreview, updateProfile, deleteProfile, handleFileUpload, successMessage };
    }
};
</script>
<template>
    <div class="max-w-lg mx-auto p-6 bg-white rounded-lg shadow-md">
        <h2 class="text-2xl font-bold mb-4">Modifier le Profil</h2>
        <!-- Message de succès -->
        <div v-if="successMessage" class="mb-4 p-2 bg-green-100 text-green-800 rounded">
            {{ successMessage }}
        </div>

        <form @submit.prevent="updateProfile" class="space-y-4">
            <div>
                <label class="block text-gray-700">Nom</label>
                <input v-model="profile.name" type="text" class="input-field" />
            </div>

            <div>
                <label class="block text-gray-700">Email</label>
                <input v-model="profile.email" type="email" class="input-field" />
            </div>

            <div>
                <label class="block text-gray-700">Téléphone</label>
                <input v-model="profile.phone_number" type="text" class="input-field" />
            </div>

            <div>
                <label class="block text-gray-700">Photo de profil</label>
                <input type="file" @change="handleFileUpload" class="input-field" />
                <img v-if="profilePreview" :src="profilePreview" alt="Aperçu" class="mt-2 h-16 w-16 rounded-full" />
            </div>

            <div>
                <label class="block text-gray-700">Nouveau mot de passe</label>
                <input v-model="profile.password" type="password" class="input-field" />
            </div>

            <div>
                <label class="block text-gray-700">Confirmer le mot de passe</label>
                <input v-model="profile.password_confirmation" type="password" class="input-field" />
            </div>

            <button type="submit" class="btn-primary">Mettre à jour</button>
        </form>

        <button @click="deleteProfile" class="btn-danger mt-4">Supprimer le profil</button>
    </div>
</template>

<style scoped>
.input-field {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 6px;
    outline: none;
}

.btn-primary {
    width: 100%;
    background-color: #2563eb;
    color: white;
    padding: 10px;
    border-radius: 6px;
    text-align: center;
}

.btn-danger {
    width: 100%;
    background-color: #dc2626;
    color: white;
    padding: 10px;
    border-radius: 6px;
    text-align: center;
}
</style>
