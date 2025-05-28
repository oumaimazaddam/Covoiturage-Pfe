<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const router = useRouter();
const loading = ref(false);
const tripHistory = ref([]);
const errorMessage = ref('');
const successMessage = ref('');
const showReviewsModal = ref(false);
const selectedTripReviews = ref([]);
const selectedTripId = ref(null);

// 🔄 Récupération des trajets
const fetchTripHistory = async () => {
  loading.value = true;
  try {
    const token = localStorage.getItem('access_token');
    if (!token) throw new Error('Aucun token d’authentification trouvé');

    const response = await axios.get('http://127.0.0.1:8000/api/trips', {
      headers: {
        Authorization: `Bearer ${token}`, // Correction: Utilisation correcte du template littéral
      },
    });

    console.log('Trips API response:', response.data);
    tripHistory.value = response.data.data || response.data;
  } catch (error) {
    console.error('Erreur:', error);
    errorMessage.value = error.response?.data?.message || 'Erreur lors du chargement';
    if (error.response?.status === 401) router.push('/login');
  } finally {
    loading.value = false;
  }
};

// 🔍 Récupération des avis pour un trajet
const fetchTripReviews = async (tripId) => {
  try {
    const token = localStorage.getItem('access_token');
    if (!token) throw new Error('Aucun token d’authentification trouvé');

    const response = await axios.get('http://127.0.0.1:8000/api/driver-reviews', {
      headers: {
        Authorization: `Bearer ${token}`, // Correction: Utilisation correcte du template littéral
      },
    });

    console.log('Reviews API response:', response.data);
    selectedTripReviews.value = response.data.reviews?.filter(review => review.trip_id === tripId) || [];
    selectedTripId.value = tripId;
    showReviewsModal.value = true;

    if (selectedTripReviews.value.length === 0) {
      errorMessage.value = 'Aucun avis trouvé pour ce trajet.';
    }
  } catch (error) {
    console.error('Erreur lors de la récupération des avis:', error);
    errorMessage.value = error.response?.data?.message || 'Erreur lors de la récupération des avis';
    if (error.response?.status === 403) {
      errorMessage.value = 'Vous n’êtes pas autorisé à voir les avis.';
    }
  }
};

// 🗑️ Supprimer un trajet
const confirmDeleteTrip = async (tripId) => {
  if (!confirm('Confirmer la suppression ?')) return;

  try {
    const token = localStorage.getItem('access_token');
    if (!token) throw new Error('Aucun token d’authentification trouvé');

    await axios.delete(`http://127.0.0.1:8000/api/trips/${tripId}`, { // Correction: URL correcte avec template littéral
      headers: {
        Authorization: `Bearer ${token}`, // Correction: Utilisation correcte du template littéral
      },
    });

    successMessage.value = 'Trajet supprimé avec succès';
    setTimeout(() => (successMessage.value = ''), 3000);
    await fetchTripHistory();
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Erreur lors de la suppression';
    console.error('Erreur lors de la suppression:', error);
  }
};

// ✏️ Modifier un trajet
const editTrip = (tripData) => {
  console.log('editTrip called with tripData:', tripData);
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
  console.log('Formatted trip data for query:', formattedData);
  router.push({
    path: '/ajouter-trajet',
    query: {
      edit: 'true',
      trip: encodeURIComponent(JSON.stringify(formattedData)),
    },
  });
};

// 📅 Formater la date
const formatDate = (dateString) => {
  if (!dateString) return '';
  const options = { year: 'numeric', month: 'long', day: 'numeric' };
  return new Date(dateString).toLocaleDateString('fr-FR', options);
};

// 🕒 Formater l'heure
const formatTime = (timeString) => {
  return timeString?.substring(0, 5) || '';
};

// 📋 Texte du statut
const getStatusText = (status) => {
  const statusMap = {
    active: 'Actif',
    completed: 'Terminé',
    canceled: 'Annulé',
  };
  return statusMap[status] || status;
};

// 🎨 Classe CSS pour le statut
const statusClass = (status) => {
  const classMap = {
    active: 'text-green-500',
    completed: 'text-gray-500',
    canceled: 'text-red-500',
  };
  return classMap[status] || '';
};

// 🛠️ Initialisation au montage
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
  <div class="min-h-screen bg-white p-6 font-sans">
    <div class="max-w-7xl mx-auto">
      <!-- En-tête -->
      <div class="text-white p-12">
        <h1 class="text-3xl font-bold text-indigo-600 mb-4">Historique des Trajets</h1>
      </div>

      <!-- État de chargement -->
      <div v-if="loading" class="flex justify-center items-center py-16">
        <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-indigo-600"></div>
      </div>

      <!-- Aucune réservation -->
      <div
        v-if="!loading && tripHistory.length === 0"
        class="bg-white rounded-2xl shadow-md p-10 text-center animate-fade-in"
      >
        <div
          class="mx-auto w-32 h-32 bg-indigo-50 rounded-full flex items-center justify-center mb-6"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-16 w-16 text-indigo-600"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="1.5"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
            />
          </svg>
        </div>
        <h3 class="text-2xl font-semibold text-gray-800 mb-3">
          Aucun trajet enregistré
        </h3>
        <p class="text-gray-500 max-w-md mx-auto mb-6">
          Vous n'avez actuellement aucun trajet enregistré. Ajoutez un trajet pour commencer !
        </p>
        <button
          @click="router.push('/ajouter-trajet')"
          class="inline-flex items-center px-4 py-2 sm:px-6 sm:py-3 rounded-lg bg-indigo-500 text-white hover:bg-indigo-600 transition-all duration-200 hover:scale-105"
          aria-label="Ajouter un trajet"
        >
          Ajouter un trajet
        </button>
      </div>

      <!-- Liste des trajets -->
      <div v-else-if="!loading" class="space-y-8">
        <!-- Message de notification -->
        <div
          v-if="successMessage || errorMessage"
          :class="{
            'bg-green-50 border border-green-200 text-green-800': successMessage,
            'bg-red-50 border border-red-200 text-red-800': errorMessage,
          }"
          class="p-5 rounded-xl shadow-md flex items-start animate-slide-in"
        >
          <svg
            v-if="successMessage"
            xmlns="http://www.w3.org/2000/svg"
            class="h-6 w-6 mr-3 text-green-500"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
          <svg
            v-else
            xmlns="http://www.w3.org/2000/svg"
            class="h-6 w-6 mr-3 text-red-500"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
          <span class="flex-1">{{ successMessage || errorMessage }}</span>
          <button
            @click="successMessage = ''; errorMessage = ''"
            class="ml-4 text-gray-500 hover:text-gray-700"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-5 w-5"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </button>
        </div>

        <!-- Carte de trajet -->
        <div
          v-for="tripItem in tripHistory"
          :key="tripItem.id"
          class="bg-white rounded-2xl shadow-md overflow-hidden transition-all duration-300 hover:shadow-lg animate-fade-in border border-gray-300"
        >
          <div class="p-8">
            <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-8">
              <!-- Informations principales -->
              <div class="flex-1 space-y-4">
                <div class="flex items-center">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-6 w-6 text-gray-400 mr-3"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="1.5"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                    />
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                    />
                  </svg>
                  <span class="text-gray-700"
                    ><span class="font-semibold">Départ:</span>
                    {{ tripItem.departure }}</span
                  >
                </div>
                <div class="flex items-center">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-6 w-6 text-gray-400 mr-3"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="1.5"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                    />
                  </svg>
                  <span class="text-gray-700"
                    ><span class="font-semibold">Destination:</span>
                    {{ tripItem.destination }}</span
                  >
                </div>
                <div class="flex items-center">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-6 w-6 text-gray-400 mr-3"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="1.5"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                    />
                  </svg>
                  <span class="text-gray-700"
                    ><span class="font-semibold">Date:</span>
                    {{ formatDate(tripItem.trip_date) }}</span
                  >
                </div>
              </div>

              <!-- Informations secondaires -->
              <div class="flex-1 space-y-4">
                <div class="flex items-center">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-6 w-6 text-gray-400 mr-3"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="1.5"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                  </svg>
                  <span class="text-gray-700"
                    ><span class="font-semibold">Heure:</span>
                    {{ formatTime(tripItem.departure_time) }}</span
                  >
                </div>
                <div class="flex items-center">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-6 w-6 text-gray-400 mr-3"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="1.5"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                  </svg>
                  <span class="text-gray-700"
                    ><span class="font-semibold">Prix:</span>
                    {{ tripItem.price }} DT</span
                  >
                </div>
                <div class="flex items-center">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-6 w-6 text-gray-400 mr-3"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="1.5"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                    />
                  </svg>
                  <span class="text-gray-700"
                    ><span class="font-semibold">Places:</span>
                    {{ tripItem.available_seats }}</span
                  >
                </div>
              </div>
            </div>

            <!-- Statut du trajet -->
            <div class="mt-4">
              <span class="font-semibold">Statut:</span>
              <span :class="statusClass(tripItem.status)">{{ getStatusText(tripItem.status) }}</span>
            </div>

            <!-- Boutons d'actions -->
            <div class="mt-8 flex justify-end space-x-4">
              <button
                v-if="tripItem.status === 'completed'"
                @click="fetchTripReviews(tripItem.id)"
                class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all duration-200 hover:scale-105"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-5 w-5 mr-2"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                  stroke-width="2"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.784.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"
                  />
                </svg>
                Voir les avis
              </button>
              <button
                v-if="tripItem.status !== 'completed'"
                @click="editTrip(tripItem)"
                class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-all duration-200 hover:scale-105"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-5 w-5 mr-2"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                  stroke-width="2"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M11 5H6a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                  />
                </svg>
                Modifier
              </button>
              <button
                v-if="tripItem.status !== 'completed'"
                @click="confirmDeleteTrip(tripItem.id)"
                class="inline-flex items-center px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-all duration-200 hover:scale-105"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-5 w-5 mr-2"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                  stroke-width="2"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                  />
                </svg>
                Supprimer
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal pour afficher les avis -->
    <div
      v-if="showReviewsModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-lg p-6 w-full max-w-lg">
        <h2 class="text-xl font-bold mb-4">Avis pour le trajet {{ selectedTripId }}</h2>
        <div v-if="selectedTripReviews.length > 0" class="space-y-4 max-h-[60vh] overflow-y-auto">
          <div
            v-for="review in selectedTripReviews"
            :key="review.review_id"
            class="border p-4 rounded-lg bg-gray-50"
          >
            <p><strong>Passager:</strong> {{ review.passenger_name || 'Anonyme' }}</p>
            <p>
              <strong>Note:</strong>
              <span style="color: #FFC107;" v-html="'★'.repeat(review.rating) + '☆'.repeat(5 - review.rating)"></span>
            </p>
            <p><strong>Commentaire:</strong> {{ review.comment || 'Aucun commentaire' }}</p>
            <p><strong>Date:</strong> {{ new Date(review.created_at).toLocaleString('fr-FR') }}</p>
          </div>
        </div>
        <div v-else class="text-center text-gray-500 py-4">
          {{ errorMessage || 'Aucun avis pour ce trajet.' }}
        </div>
        <div class="mt-4 flex justify-end">
          <button
            @click="showReviewsModal = false"
            class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 transition"
          >
            Fermer
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Custom animations */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateX(20px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.animate-fade-in {
  animation: fadeIn 0.5s ease-out;
}

.animate-slide-in {
  animation: slideIn 0.5s ease-out;
}

/* Ensure Inter font is loaded */
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap");

body {
  font-family: "Inter", sans-serif;
}

/* Modal scroll */
.max-h-\[60vh\] {
  max-height: 60vh;
}

.overflow-y-auto {
  overflow-y: auto;
}
</style>