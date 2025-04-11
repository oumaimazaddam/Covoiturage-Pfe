<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

// Déclarations des variables
const users = ref([]);
const loading = ref(true);
const showModal = ref(false);
const currentUser = ref(null); // Utilisateur connecté
const router = useRouter();

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
const roleMapping = {
  1: 'Admin',
  2: 'Driver',
  3: 'Passenger'
};
const userToEdit = ref(null);

const fetchCurrentUser = async () => {
  const token = localStorage.getItem('access_token');

  if (!token) {
    console.error('Token non trouvé');
    router.push('/login'); // Redirige si pas connecté
    return;
  }

  try {
    const response = await axios.get('http://127.0.0.1:8000/api/user-profile', {
      headers: {
        'Authorization': `Bearer ${token}`,
      },
    });

    currentUser.value = response.data;
        console.log("Utilisateur connecté:", currentUser.value);


    // Vérification du rôle : Si ce n'est pas un admin, redirection
    if (currentUser.value.role_id !== 1) {
      alert("Accès refusé : Vous n'avez pas les permissions nécessaires.");
      router.push('/'); // Rediriger vers la page d'accueil ou une autre page
    }
  } catch (error) {
    console.error('Erreur lors de la récupération du profil utilisateur:', error);
    router.push('/login'); // Redirection en cas d'échec
  }
};

// Fonction pour récupérer les utilisateurs
const fetchUsers = async () => {
  const token = localStorage.getItem('access_token');
  
  if (!token) {
    console.error('Token non trouvé');
    return;
  }

  try {
    const response = await axios.get('http://127.0.0.1:8000/api/users', {
      headers: {
        'Authorization': `Bearer ${token}`,
      },
    });

    users.value = response.data;
    loading.value = false;
    
  } catch (error) {
    console.error('Erreur API:', error.response ? error.response.data : error);
    loading.value = false;
    
  }
};

// Ouvrir le modal pour l'ajout ou la modification
const openModal = (user = null) => {
  if (user) {
    userToEdit.value = user;
    formData.value = { ...user };
  } else {
    userToEdit.value = null;
    formData.value = {
      name: '',
      email: '',
      phone_number: '',
      birthday: '',
      role_id: '',
      car_id: '',
      drivingLicence: ''
    };
  }
  showModal.value = true;
};

// Soumettre le formulaire
const handleSubmit = () => {
  if (userToEdit.value) {
    updateUser(userToEdit.value.id);
  } else {
    if (formData.value.password !== formData.value.password_confirmation) {
      alert("Les mots de passe ne correspondent pas.");
      return;
    }
    
    createUser();
  }
};

// Créer un utilisateur
const createUser = async () => {
  const token = localStorage.getItem('access_token');
  
  if (!token) {
    console.error('Token non trouvé');
    return;
  }

  try {
    const response = await axios.post('http://127.0.0.1:8000/api/register', formData.value, {
      headers: {
        'Authorization': `Bearer ${token}`,
      },
    });

    users.value.push(response.data.user);
    showModal.value = false;
    resetForm();
  } catch (error) {
    console.error('Erreur de création:', error.response ? error.response.data : error);
  }
};

// Mettre à jour un utilisateur
const updateUser = async (id) => {
  const token = localStorage.getItem('access_token');
  
  if (!token) {
    console.error('Token non trouvé');
    return;
  }

  try {
    const response = await axios.put(`http://127.0.0.1:8000/api/user/${id}`, formData.value, {
      headers: {
        'Authorization': `Bearer ${token}`,
      },
    });

    const index = users.value.findIndex(user => user.id === id);
    if (index !== -1) {
      users.value[index] = response.data.user;
    }

    showModal.value = false;
    resetForm();
  } catch (error) {
    console.error('Erreur de mise à jour:', error.response ? error.response.data : error);
  }
};

// Supprimer un utilisateur
const deleteUser = async (id) => {
  const token = localStorage.getItem('access_token');
  
  if (!token) {
    console.error('Token non trouvé');
    return;
  }

  try {
    await axios.delete(`http://127.0.0.1:8000/api/user/${id}`, {
      headers: {
        'Authorization': `Bearer ${token}`,
      },
    });

    users.value = users.value.filter(user => user.id !== id);
  } catch (error) {
    console.error('Erreur de suppression:', error.response ? error.response.data : error);
  }
};

// Réinitialiser le formulaire
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
};

// Appeler la fonction pour récupérer les utilisateurs lors du montage
fetchUsers();
fetchCurrentUser();
</script>

<template>
   <div v-if="currentUser && currentUser.role_id === 1" class="container mx-auto p-4">
    <h2 class="text-2xl font-semibold mb-4">Liste des utilisateurs</h2>

    <div class="mb-4">
      <button 
        @click="openModal()" 
        class="bg-green-500 text-white py-2 px-4 rounded hover:bg-green-700"
      >
        Ajouter un compte
      </button>
    </div>

    <div v-if="loading" class="text-center text-gray-500">Chargement...</div>

   <DataTable :value="users" :paginator="true" :rows="5" dataKey="id" showGridlines responsiveLayout="scroll">
  <template #empty>Aucun utilisateur trouvé.</template>
  <template #loading>Chargement des utilisateurs...</template>

  <Column field="name" header="Nom" sortable></Column>
  <Column field="email" header="Email" sortable></Column>
  <Column field="phone_number" header="Téléphone" sortable></Column>
  <Column field="birthday" header="Date de naissance" sortable></Column>

  <Column header="Rôle" sortable>
    <template #body="{ data }">
      {{ roleMapping[data.role_id] || 'Inconnu' }}
    </template>
  </Column>

  <Column field="car_id" header="Matricule de véhicule" sortable></Column>
  <Column field="drivingLicence" header="Permis" sortable></Column>

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


    <!-- Modal d'édition/création -->
    <div v-if="showModal" class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50">
      <div class="bg-white p-6 rounded shadow-lg w-1/3">
        <h3 class="text-xl mb-4">{{ userToEdit ? 'Modifier l\'utilisateur' : 'Créer un utilisateur' }}</h3>
        <form @submit.prevent="handleSubmit">
          <input v-model="formData.name" type="text" placeholder="Nom" class="mb-4 p-2 w-full border rounded" required />
          <input v-model="formData.email" type="email" placeholder="Email" class="mb-4 p-2 w-full border rounded" required />
          <input v-model="formData.password" type="password" placeholder="Mot de passe" class="mb-4 p-2 w-full border rounded" required v-if="!userToEdit" />
          <input v-model="formData.password_confirmation" type="password" placeholder="Confirmer le mot de passe" class="mb-4 p-2 w-full border rounded" required v-if="!userToEdit" />
          <input v-model="formData.phone_number" type="text" placeholder="Téléphone" class="mb-4 p-2 w-full border rounded" required />
          <input v-model="formData.birthday" type="date" placeholder="Date de naissance" class="mb-4 p-2 w-full border rounded" required />
          <input v-model="formData.role_id" type="number" placeholder="Rôle" class="mb-4 p-2 w-full border rounded" required />
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
   <!-- Message d'accès refusé -->
 
</template>



<style scoped>
.custom-delete-button {
  background-color: #f80b0b; /* Couleur rouge claire */
  color: white;
  font-weight: bold;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  transition: background-color 0.3s ease, transform 0.2s ease;
}

.custom-delete-button:hover {
  background-color: #f44336; /* Couleur rouge plus foncée */
  transform: scale(1.05); /* Légère animation de zoom */
}
</style>
