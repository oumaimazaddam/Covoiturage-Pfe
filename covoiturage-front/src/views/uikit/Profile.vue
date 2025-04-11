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
            current_password: '', // Added current password field
            password: '',
            password_confirmation: ''
        });

        const profilePreview = ref(null);
        const successMessage = ref('');
        const router = useRouter();
        const userId = localStorage.getItem('user_id');
        const accessToken = localStorage.getItem('access_token');
        const roleId = ref(localStorage.getItem('user_role'));
        const deletePassword = ref(''); // Password for deletion confirmation
        const showDeletePassword = ref(false); // Toggle visibility of password field

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
                roleId.value = response.data.user.role_id;
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

            // Include passwords only if at least one is provided
            if (profile.value.current_password || profile.value.password || profile.value.password_confirmation) {
                if (!profile.value.current_password) {
                    alert('Veuillez entrer votre mot de passe actuel pour modifier le mot de passe.');
                    return;
                }
                formData.append('current_password', profile.value.current_password);
                formData.append('password', profile.value.password);
                formData.append('password_confirmation', profile.value.password_confirmation);
            }

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
                // Clear password fields after success
                profile.value.current_password = '';
                profile.value.password = '';
                profile.value.password_confirmation = '';
            } catch (error) {
                console.error('Erreur mise à jour :', error.response?.data || error.message);
                alert('Une erreur est survenue lors de la mise à jour du profil : ' + (error.response?.data?.message || error.message));
            }
        };

        const toggleDeletePassword = () => {
            showDeletePassword.value = !showDeletePassword.value; // Toggle visibility
            if (!showDeletePassword.value) deletePassword.value = ''; // Clear password when hiding
        };

        const deleteProfile = async () => {
            if (!showDeletePassword.value) {
                toggleDeletePassword(); // Show password field if not visible
                return;
            }

            if (!deletePassword.value) {
                alert('Veuillez entrer votre mot de passe pour confirmer la suppression.');
                return;
            }

            try {
                await axios.delete(`http://localhost:8000/api/delete-profile/${userId}`, {
                    headers: { Authorization: `Bearer ${accessToken}` },
                    data: { password: deletePassword.value }
                });
                alert('Profil supprimé !');
                localStorage.clear();
                router.push('/login');
            } catch (error) {
                console.error('Erreur suppression du profil :', error.response?.data || error.message);
                alert('Mot de passe incorrect ou erreur lors de la suppression.');
            }
        };

        onMounted(fetchProfile);

        return {
            profile,
            profilePreview,
            updateProfile,
            deleteProfile,
            handleFileUpload,
            successMessage,
            roleId,
            deletePassword,
            showDeletePassword,
            toggleDeletePassword
        };
    }
};
</script>

<template>
  <div>
    <div class="bg-surface-50 dark:bg-surface-950 flex items-center justify-center min-h-screen">
      <div class="flex flex-col items-center justify-center mt-16 mb-8">
        <div style="border-radius: 56px; padding: 0.3rem; background: linear-gradient(180deg, var(--primary-color) 10%, rgba(33, 150, 243, 0) 45%)">
          <div class="w-full bg-surface-0 dark:bg-surface-900 py-12 px-8 sm:px-16" style="border-radius: 53px">
            <div class="text-center mb-8">
              <div class="text-surface-900 dark:text-surface-0 text-3xl font-medium mb-4">Modifier le Profil</div>
              <span class="text-muted-color font-medium">Mettez à jour vos informations</span>
            </div>

            <div v-if="successMessage" class="mb-4 p-3 bg-green-100 text-green-800 rounded-md text-center">
              {{ successMessage }}
            </div>

            <form @submit.prevent="updateProfile" enctype="multipart/form-data" class="space-y-4">
              <div class="field">
                <label class="block text-sm font-medium text-900 mb-1">Nom</label>
                <input v-model="profile.name" type="text" class="input-field" />
              </div>

              <div class="field">
                <label class="block text-sm font-medium text-900 mb-1">Email</label>
                <input v-model="profile.email" type="email" class="input-field" />
              </div>

              <div class="field">
                <label class="block text-sm font-medium text-900 mb-1">Téléphone</label>
                <input v-model="profile.phone_number" type="number" maxlength="8"
                  @input="profile.phone_number = profile.phone_number.toString().slice(0, 8)"
                  class="input-field" />
              </div>

              <div v-if="roleId == 2" class="field">
                <label class="block text-sm font-medium text-900 mb-1">Matricule</label>
                <input v-model="profile.car_id" type="text" class="input-field" />
              </div>

              <div v-if="roleId == 2" class="field">
                <label class="block text-sm font-medium text-900 mb-1">Permis</label>
                <input v-model="profile.drivingLicence" type="text" class="input-field" />
              </div>

              <div class="field">
                <label class="block text-sm font-medium text-900 mb-1">Photo de profil</label>
                <input type="file" @change="handleFileUpload" class="input-field" />
                <div v-if="profilePreview" class="mt-2 flex items-center">
                  <img :src="profilePreview" alt="Aperçu de la photo" class="h-16 w-16 rounded-full" />
                </div>
              </div>

              <div class="field">
                <label class="block text-sm font-medium text-900 mb-1">Mot de passe actuel</label>
                <input v-model="profile.current_password" type="password" class="input-field" placeholder="Entrez votre mot de passe actuel" />
              </div>

              <div class="field">
                <label class="block text-sm font-medium text-900 mb-1">Nouveau mot de passe</label>
                <input v-model="profile.password" type="password" class="input-field" />
              </div>

              <div class="field">
                <label class="block text-sm font-medium text-900 mb-1">Confirmer le nouveau mot de passe</label>
                <input v-model="profile.password_confirmation" type="password" class="input-field" />
              </div>

              <div class="text-center">
                <button type="submit" class="btn-primary mt-6">Mettre à jour</button>
              </div>
            </form>

            <div class="text-center mt-6">
              <button @click="deleteProfile" class="btn-danger mt-4">
                {{ showDeletePassword ? 'Confirmer la Désactivation' : 'Désactiver le Compte' }}
              </button>
              <div v-if="showDeletePassword" class="field mt-4">
                <label class="block text-sm font-medium text-900 mb-1">Mot de passe actuel (pour suppression)</label>
                <input v-model="deletePassword" type="password" class="input-field" placeholder="Entrez votre mot de passe" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.input-field {
  width: 100%;
  padding: 12px;
  border: 2px solid #ddd;
  border-radius: 8px;
  outline: none;
  background-color: #f9fafb;
  transition: all 0.2s ease-in-out;
}

.input-field:focus {
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
}

.btn-primary {
  width: 100%;
  background-color: #2563eb;
  color: white;
  padding: 12px;
  border-radius: 8px;
  font-weight: 600;
}

.btn-danger {
  width: 100%;
  background-color: #dc2626;
  color: white;
  padding: 12px;
  border-radius: 8px;
  font-weight: 600;
}
</style>