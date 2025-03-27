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

    <table v-else class="min-w-full bg-white border border-gray-300 rounded-lg shadow-md">
      <thead>
        <tr class="bg-gray-100 text-gray-600">
          <th class="py-3 px-6 text-left">Nom</th>
          <th class="py-3 px-6 text-left">Email</th>
          <th class="py-3 px-6 text-left">Téléphone</th>
          <th class="py-3 px-6 text-left">Date de naissance</th>
          <th class="py-3 px-6 text-left">Rôle</th>
          <th class="py-3 px-6 text-left">Matricule de véhicule</th>
          <th class="py-3 px-6 text-left">Permis</th>
          <th class="py-3 px-6 text-left">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="user in users" :key="user.id" class="border-t hover:bg-gray-50">
          <td class="py-3 px-6">{{ user.name }}</td>
          <td class="py-3 px-6">{{ user.email }}</td>
          <td class="py-3 px-6">{{ user.phone_number }}</td>
          <td class="py-3 px-6">{{ user.birthday }}</td>
         <td class="py-3 px-6">{{ roleMapping[user.role_id] || 'Inconnu' }}</td>

          <td class="py-3 px-6">{{ user.car_id }}</td>
          <td class="py-3 px-6">{{ user.drivingLicence }}</td>
          <td class="py-3 px-6">
             <div class="flex space-x-2">
            <button 
              @click="openModal(user)" 
              class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-700"
            >
              Modifier
            </button>
            <button 
              @click="deleteUser(user.id)" 
              class="bg-red-500 text-white py-2 px-4 rounded hover:bg-red-700"
            >
              Supprimer
            </button>
             </div>
          </td>
        </tr>
      </tbody>
    </table>

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
  <div v-else class="text-center text-red-500 font-semibold text-xl p-10">
    Accès refusé 🚫
  </div>
</template>



<style scoped>
</style>
