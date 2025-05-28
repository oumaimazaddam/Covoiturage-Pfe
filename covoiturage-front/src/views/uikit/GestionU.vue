<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import { Button, DataTable, Column } from 'primevue';

const users = ref([]);
const loading = ref(true);
const showModal = ref(false);
const currentUser = ref(null);
const errorMessage = ref('');
const router = useRouter();
const searchQuery = ref('');
const formData = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  phone_number: '',
  birthday: '',
  role_id: '',
  car_id: '',
  drivingLicence: ''
});
const roleMapping = { 1: 'Admin', 2: 'Driver', 3: 'Passenger' };
const userToEdit = ref(null);

const filteredUsers = computed(() => {
  if (!searchQuery.value.trim()) {
    return users.value;
  }
  return users.value.filter(user =>
    user.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    
  );
  
});

const fetchCurrentUser = async () => {
  const token = localStorage.getItem('access_token');
  if (!token) {
    errorMessage.value = 'Token non trouvé. Veuillez vous reconnecter.';
    router.push('/login');
    return;
  }
  try {
    const response = await axios.get('http://127.0.0.1:8000/api/user-profile', {
      headers: { 'Authorization': `Bearer ${token}` },
    });
    currentUser.value = response.data;
    console.log('Utilisateur connecté:', currentUser.value);
    if (currentUser.value.role_id !== 1) {
      errorMessage.value = 'Accès refusé : Vous n\'avez pas les permissions nécessaires.';
      router.push('/');
    }
  } catch (error) {
    errorMessage.value = error.response?.status === 401
      ? 'Session expirée. Veuillez vous reconnecter.'
      : 'Erreur lors de la récupération du profil utilisateur.';
    console.error('Erreur lors de la récupération du profil utilisateur:', error);
    router.push('/login');
  }
};

const fetchUsers = async () => {
  const token = localStorage.getItem('access_token');
  if (!token) {
    errorMessage.value = 'Token non trouvé.';
    loading.value = false;
    return;
  }
  try {
    const response = await axios.get('http://127.0.0.1:8000/api/admin/users', {
      headers: { 'Authorization': `Bearer ${token}` },
    });
    users.value = Array.isArray(response.data) ? response.data : response.data.data || [];
    console.log('Réponse API brute:', response.data);
    console.log('Utilisateurs assignés:', users.value);
    loading.value = false;
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Erreur lors de la récupération des utilisateurs.';
    console.error('Erreur API:', error.response ? error.response.data : error);
    users.value = [];
    loading.value = false;
  }
};

const openModal = (user = null) => {
  if (user) {
    userToEdit.value = user;
    formData.value = { ...user, password: '', password_confirmation: '' };
  } else {
    userToEdit.value = null;
    resetForm();
  }
  showModal.value = true;
};

const handleSubmit = () => {
  if (!formData.value.name || !formData.value.email || !formData.value.phone_number || !formData.value.birthday || !formData.value.role_id) {
    errorMessage.value = 'Veuillez remplir tous les champs obligatoires.';
    return;
  }
  if (!['1', '2', '3'].includes(formData.value.role_id.toString())) {
    errorMessage.value = 'Rôle invalide. Choisissez entre 1 (Admin), 2 (Driver), ou 3 (Passenger).';
    return;
  }
  if (userToEdit.value) {
    updateUser(userToEdit.value.id);
  } else {
    if (formData.value.password !== formData.value.password_confirmation) {
      errorMessage.value = 'Les mots de passe ne correspondent pas.';
      return;
    }
    createUser();
  }
};

const createUser = async () => {
  const token = localStorage.getItem('access_token');
  if (!token) {
    errorMessage.value = 'Token non trouvé.';
    return;
  }
  try {
    const response = await axios.post('http://127.0.0.1:8000/api/register', formData.value, {
      headers: { 'Authorization': `Bearer ${token}` },
    });
    users.value.push(response.data.user);
    showModal.value = false;
    resetForm();
    errorMessage.value = '';
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Erreur lors de la création de l\'utilisateur.';
    console.error('Erreur de création:', error.response ? error.response.data : error);
  }
};

const updateUser = async (id) => {
  const token = localStorage.getItem('access_token');
  if (!token) {
    errorMessage.value = 'Token non trouvé.';
    return;
  }
  try {
    const response = await axios.put(`http://127.0.0.1:8000/api/user/${id}`, formData.value, {
      headers: { 'Authorization': `Bearer ${token}` },
    });
    const index = users.value.findIndex(user => user.id === id);
    if (index !== -1) {
      users.value[index] = response.data.user;
    }
    showModal.value = false;
    resetForm();
    errorMessage.value = '';
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Erreur lors de la mise à jour de l\'utilisateur.';
    console.error('Erreur de mise à jour:', error.response ? error.response.data : error);
  }
};

const deleteUser = async (id) => {
  const token = localStorage.getItem('access_token');
  if (!token) {
    errorMessage.value = 'Token non trouvé.';
    return;
  }
  try {
    await axios.delete(`http://127.0.0.1:8000/api/user/${id}`, {
      headers: { 'Authorization': `Bearer ${token}` },
    });
    users.value = users.value.filter(user => user.id !== id);
    errorMessage.value = '';
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Erreur lors de la suppression de l\'utilisateur.';
    console.error('Erreur de suppression:', error.response ? error.response.data : error);
  }
};

const resetForm = () => {
  formData.value = {
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    phone_number: '',
    birthday: '',
    role_id: '',
    car_id: '',
    drivingLicence: ''
  };
  errorMessage.value = '';
};

onMounted(async () => {
  await fetchCurrentUser();
  if (currentUser.value && currentUser.value.role_id === 1) {
    await fetchUsers();
  }
});
</script>

<template>
  <div class="container mx-auto p-4">
    <div v-if="!currentUser && !loading" class="text-center text-red-500">
      Chargement du profil utilisateur...
    </div>
    <div v-else-if="currentUser && currentUser.role_id !== 1" class="text-center text-red-500">
      {{ errorMessage || 'Accès refusé : Vous n\'avez pas les permissions nécessaires.' }}
    </div>
    <div v-else>
      <h2 class="text-2xl font-semibold mb-4">Liste des utilisateurs</h2>
      <div v-if="errorMessage" class="text-red-500 mb-4">{{ errorMessage }}</div>
   <div class="mb-4 flex items-center flex-col sm:flex-row">
  <div class="relative w-full sm:w-1/3">
    <input 
      v-model="searchQuery" 
      type="text" 
      placeholder="Rechercher par Nom" 
      class="p-2 pl-10 w-full border rounded"
    />
    <i class="pi pi-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500"></i>
  </div>
  <button 
    @click="openModal()" 
    class="bg-green-500 text-white py-2 px-4 rounded hover:bg-green-700 mt-2 sm:mt-0 sm:ml-auto"
  >
    Ajouter un compte
  </button>
</div>
      <div v-if="loading" class="text-center text-gray-500">Chargement...</div>
      <DataTable v-else :value="filteredUsers" :paginator="true" :rows="5" dataKey="id" showGridlines responsiveLayout="scroll">
        <template #empty>Aucun utilisateur trouvé.</template>
        <template #loading>Chargement des utilisateurs...</template>
        <Column field="name" header="Nom" sortable></Column>
        <Column field="email" header="Email" sortable></Column>
        <Column field="phone_number" header="Téléphone"  maxlength="8" sortable></Column>
        <Column field="birthday" header="Date de naissance" sortable></Column>
        <Column header="Rôle" sortable>
          <template #body="{ data }">
            {{ roleMapping[data.role_id] || 'Inconnu' }}
          </template>
        </Column>
        <Column field="car_id" header="Matricule de véhicule" sortable>
          <template #body="{ data }">
            {{ data.car_id  }}
          </template>
        </Column>
        <Column field="drivingLicence" header="Permis" sortable>
          <template #body="{ data }">
            {{ data.drivingLicence  }}
          </template>
        </Column>
        <Column header="Actions">
          <template #body="{ data }">
            <div class="flex gap-2">
              <Button 
                label="Modifier" 
                icon="pi pi-pencil"
                @click="openModal(data)" 
                class="bg-primary text-white font-bold py-2 px-4 rounded-lg hover:bg-primary-dark transition"
              />
              <Button 
                label="Supprimer" 
                icon="pi pi-trash"
                @click="deleteUser(data.id)" 
                class="custom-delete-button"
              />
            </div>
          </template>
        </Column>
      </DataTable>
      <div v-if="showModal" class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50">
        <div class="bg-white p-6 rounded shadow-lg w-1/3">
          <h3 class="text-xl mb-4">{{ userToEdit ? 'Modifier l\'utilisateur' : 'Créer un utilisateur' }}</h3>
          <form @submit.prevent="handleSubmit">
            <input v-model="formData.name" type="text" placeholder="Nom" class="mb-4 p-2 w-full border rounded" required />
            <input v-model="formData.email" type="email" placeholder="Email" class="mb-4 p-2 w-full border rounded" required />
            <input v-model="formData.password" type="password" placeholder="Mot de passe" class="mb-4 p-2 w-full border rounded" v-if="!userToEdit" />
            <input v-model="formData.password_confirmation" type="password" placeholder="Confirmer le mot de passe" class="mb-4 p-2 w-full border rounded" v-if="!userToEdit" />
            <input v-model="formData.phone_number" type="text" placeholder="Téléphone"  maxlength="8" class="mb-4 p-2 w-full border rounded" required />
            <input v-model="formData.birthday" type="date" placeholder="Date de naissance" class="mb-4 p-2 w-full border rounded" required />
            <input v-model="formData.role_id" type="number" placeholder="Rôle (1=Admin, 2=Driver, 3=Passenger)" class="mb-4 p-2 w-full border rounded" required />
            <input v-model="formData.car_id" type="text" placeholder="Matricule du véhicule" class="mb-4 p-2 w-full border rounded" />
            <input v-model="formData.drivingLicence" type="text" placeholder="Permis" class="mb-4 p-2 w-full border rounded" />
            <button type="submit" class="bg-green-500 text-white py-2 px-4 rounded hover:bg-green-700">
              {{ userToEdit ? 'Mettre à jour' : 'Créer' }}
            </button>
          </form>
          <button @click="showModal = false" class="mt-4 bg-gray-500 text-white py-2 px-4 rounded hover:bg-gray-700">
            Fermer
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.custom-delete-button {
  background-color: #f80b0b;
  color: white;
  font-weight: bold;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  transition: background-color 0.3s ease, transform 0.2s ease;
}
.custom-delete-button:hover {
  background-color: #f44336;
  transform: scale(1.05);
}
</style>
