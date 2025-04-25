
<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const router = useRouter();
const loading = ref(false);
const tripHistory = ref([]);
const errorMessage = ref('');
const successMessage = ref('');

// 🔄 Récupération des trajets
const fetchTripHistory = async () => {
  loading.value = true;
  try {
    const token = localStorage.getItem('access_token');
    if (!token) throw new Error('Aucun token d’authentification trouvé');

    const response = await axios.get('http://127.0.0.1:8000/api/trips', {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    });

    console.log('API response:', response.data); // Debug API response
    tripHistory.value = response.data.data || response.data; // Handle wrapped response
  } catch (error) {
    console.error('Erreur:', error);
    errorMessage.value = error.response?.data?.message || 'Erreur lors du chargement';
    if (error.response?.status === 401) router.push('/login');
  } finally {
    loading.value = false;
  }
};

// 🗑️ Supprimer un trajet
const confirmDeleteTrip = async (tripId) => {
  if (!confirm('Confirmer la suppression ?')) return;

  try {
    const token = localStorage.getItem('access_token');
    await axios.delete(`http://127.0.0.1:8000/api/trips/${tripId}`, {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    });

    successMessage.value = 'Trajet supprimé avec succès';
    setTimeout(() => (successMessage.value = ''), 3000);
    await fetchTripHistory();
  } catch (error) {
    errorMessage.value = 'Erreur lors de la suppression';
  }
};

// ✏️ Modifier un trajet
const editTrip = (tripData) => {
  console.log('editTrip called with tripData:', tripData); // Debug input data
  const formattedData = {
    id: tripData.id,
    departure: tripData.departure || '',
    destination: tripData.destination || '',
    trip_date: tripData.trip_date?.split('T')[0] || '',
    departure_time: tripData.departure_time?.substring(0, 5) || '',
    estimate_arrival_time: tripData.estimate_arrival_time?.substring(0, 5) || '',
    price: tripData.price || 0,
    available_seats: tripData.available_seats || 1,
    instant_booking: tripData.instant_booking !== undefined ? tripData.instant_booking : true,
  };
  console.log('Formatted trip data for query:', formattedData); // Debug formatted data
  router.push({
    path: '/ajouter-trajet',
    query: {
      edit: 'true',
      trip: encodeURIComponent(JSON.stringify(formattedData)), // URL-encode JSON
    },
  });
};

// 📅 Format date
const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('fr-FR');
};

// ⏰ Format heure
const formatTime = (timeString) => {
  return timeString?.substring(0, 5) || '';
};

// 🟢 Statut
const getStatusText = (status) => {
  return {
    active: 'Actif',
    completed: 'Terminé',
    canceled: 'Annulé',
  }[status] || status;
};

// 🎨 Classe CSS pour le statut
const statusClass = (status) => {
  return {
    active: 'text-green-500',
    completed: 'text-gray-500',
    canceled: 'text-red-500',
  }[status];
};

// 🔁 On lance la récupération des trajets si l'utilisateur est connecté
onMounted(() => {
  const token = localStorage.getItem('access_token');
  if (!token) {
    router.push('/login');
  } else {
    fetchTripHistory();
  }
});
</script>

<template>
  <div class="min-h-screen bg-white">
    <!-- En-tête -->
    <div class="bg-blue-600 text-white p-4 shadow-md">
      <h1 class="text-2xl font-bold text-center">📜 Historique des trajets</h1>
    </div>

    <!-- Contenu principal -->
    <div class="p-6 max-w-4xl mx-auto">
      <!-- Bouton retour -->
      <div class="mb-6">
        <button
          @click="router.push('/ajouter-trajet')"
          class="flex items-center px-4 py-2 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors duration-200 font-medium"
        >
          ➕ Ajouter un trajet
        </button>
      </div>

      <!-- Messages -->
      <div
        v-if="successMessage"
        class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg"
      >
        <div class="flex items-center">
          <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path
              fill-rule="evenodd"
              d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
              clip-rule="evenodd"
            />
          </svg>
          <span class="font-medium">{{ successMessage }}</span>
        </div>
      </div>

      <div
        v-if="errorMessage"
        class="text-red-500 text-center mt-4 p-3 bg-red-50 rounded-lg"
      >
        {{ errorMessage }}
      </div>

      <!-- Chargement -->
      <div v-if="loading" class="text-center py-8">
        <div
          class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500"
        ></div>
        <p class="mt-2">Chargement de l'historique...</p>
      </div>

      <!-- Liste des trajets -->
      <div v-else>
        <div v-if="tripHistory.length > 0" class="space-y-4">
          <div
            v-for="tripItem in tripHistory"
            :key="tripItem.id"
            class="border rounded-lg p-4 hover:shadow-md transition-shadow"
          >
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
              <div>
                <span class="font-semibold">Trajet:</span>
                {{ tripItem.departure }} → {{ tripItem.destination }}
              </div>
              <div>
                <span class="font-semibold">Date:</span>
                {{ formatDate(tripItem.trip_date) }}
              </div>
              <div>
                <span class="font-semibold">Heure:</span>
                {{ formatTime(tripItem.departure_time) }}
              </div>
              <div>
                <span class="font-semibold">Prix:</span>
                {{ tripItem.price }} DT
              </div>
              <div>
                <span class="font-semibold">Places:</span>
                {{ tripItem.available_seats }}
              </div>
              <div>
                <span class="font-semibold">Statut:</span>
                <span :class="statusClass(tripItem.status)">
                  {{ getStatusText(tripItem.status) }}
                </span>
              </div>
            </div>
            <div class="mt-2 flex justify-end space-x-2">
              <button
                @click="editTrip(tripItem)"
                class="px-3 py-1 bg-blue-100 text-blue-600 rounded hover:bg-blue-200 transition text-sm font-medium"
              >
                Modifier
              </button>
              <button
                @click="confirmDeleteTrip(tripItem.id)"
                class="px-3 py-1 bg-red-100 text-red-600 rounded hover:bg-red-200 transition text-sm font-medium"
              >
                Supprimer
              </button>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-8 text-gray-500">
          Aucun trajet enregistré pour le moment
        </div>
      </div>
    </div>
  </div>
</template>

<style>
body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
</style>
