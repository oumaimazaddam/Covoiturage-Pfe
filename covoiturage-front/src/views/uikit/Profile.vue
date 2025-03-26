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
            photo_profile: null,
            photoUrl: '',
            car_id: '',
            drivingLicence: '',
            password: '',
            password_confirmation: ''
        });

        const profilePreview = ref(null);
        const successMessage = ref('');
        const router = useRouter();
        const userId = localStorage.getItem('user_id');
        const accessToken = localStorage.getItem('access_token');
        const roleId = ref(localStorage.getItem('user_role')); // Récupération du rôle utilisateur

        console.log('User ID:', userId);
        console.log('Access Token:', accessToken);
        console.log('Role ID:', roleId.value);

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
                roleId.value = response.data.user.role_id; // Met à jour le rôle
                localStorage.setItem('user_role', response.data.user.role_id);
            } catch (error) {
                console.error('Erreur chargement profil :', error.response?.data || error.message);
            }
        };

        const handleFileUpload = (event) => {
            const file = event.target.files[0];

            const validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            if (!validTypes.includes(file.type)) {
                alert('Veuillez sélectionner une image valide (jpg, jpeg, png).');
                return;
            }

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
            formData.append('_method', 'PUT');
            formData.append('id', userId);
            formData.append('name', profile.value.name);
            formData.append('email', profile.value.email);
            formData.append('phone_number', profile.value.phone_number);
            if (roleId.value == 2) {
                formData.append('car_id', profile.value.car_id);
                formData.append('drivingLicence', profile.value.drivingLicence);
            }
            formData.append('password', profile.value.password);
            formData.append('password_confirmation', profile.value.password_confirmation);

            if (profile.value.photo_profile) {
                formData.append('photo_profile', profile.value.photo_profile);
            }

            try {
                const response = await axios.post(`http://localhost:8000/api/update-profile/${userId}`, formData, {
                    headers: {
                        Authorization: `Bearer ${accessToken}`
                    }
                });

                profile.value = { ...profile.value, ...response.data.user };
                successMessage.value = response.data.message;
                alert(response.data.message);
            } catch (error) {
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
                localStorage.clear();
                router.push('/login');
            } catch (error) {
                console.error('Erreur suppression du profil :', error.response?.data || error.message);
            }
        };

        onMounted(fetchProfile);

        return { profile, profilePreview, updateProfile, deleteProfile, handleFileUpload, successMessage, roleId };
    }
};
</script>

<template>
    <div class="max-w-lg mx-auto p-6 bg-white rounded-lg shadow-md">
        <h2 class="text-2xl font-bold mb-4">Modifier le Profil</h2>

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
                <input v-model="profile.phone_number" type="number" class="input-field" maxlength="8"
                    @input="profile.phone_number = profile.phone_number.toString().slice(0, 8)" />
            </div>

            <!-- Afficher ces champs uniquement si roleId == 2 -->
            <div v-if="roleId == 2">
                <label class="block text-gray-700">Matricule</label>
                <input v-model="profile.car_id" type="text" class="input-field" />
            </div>

            <div v-if="roleId == 2">
                <label class="block text-gray-700">Permis</label>
                <input v-model="profile.drivingLicence" type="text" class="input-field" />
            </div>

            <div>
                <input type="file" @change="handleFileUpload" class="input-field mb-2" />
                <div v-if="profilePreview" class="flex items-center">
                    <img :src="profilePreview" alt="Aperçu de la photo" class="h-16 w-16 rounded-full" />
                </div>
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
