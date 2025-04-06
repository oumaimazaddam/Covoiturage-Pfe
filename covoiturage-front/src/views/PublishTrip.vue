<script setup>
import { ref, onMounted, watch } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";

const router = useRouter();
const loading = ref(false);
const tripHistoryLoading = ref(false);
const errorMessage = ref("");
const successMessage = ref("");
const todayDate = ref(new Date().toISOString().split("T")[0]);
const currentStep = ref(1);
const tripHistory = ref([]);

const recommendedPrice = ref({ min: 10, max: 50 });
const priceAdvice = ref("Sélectionnez un trajet pour voir les prix recommandés");
const distance = ref(0);

const steps = [
  { title: "Trajet" },
  { title: "Date" },
  { title: "Heures" },
  { title: "Prix/Places" },
  { title: "Confirmation" }
];

const trip = ref({
  id: null,
  departure: "",
  destination: "",
  trip_date: "",
  departure_time: "",
  estimate_arrival_time: "",
  price: "",
  available_seats: 1,
  instant_booking: true
});

const cityDistances = {
  'tunis-sousse': 140,
  'sousse-tunis': 140,
  'tunis-sfax': 270,
  'sfax-tunis': 270,
  'sousse-sfax': 130,
  'sfax-sousse': 130
};

const showHistory = () => {
  fetchTripHistory();
  currentStep.value = 6;
};

const fetchTripHistory = async () => {
  tripHistoryLoading.value = true;
  try {
    const token = localStorage.getItem("access_token");
    if (!token) throw new Error("Aucun token d'authentification trouvé");

    const endpointsToTry = [
      "http://127.0.0.1:8000/api/user-trips",
      "http://127.0.0.1:8000/api/trips/user",
      "http://127.0.0.1:8000/api/trips"
    ];

    let response;
    for (const endpoint of endpointsToTry) {
      try {
        response = await axios.get(endpoint, {
          headers: {
            Authorization: `Bearer ${token}`,
            "Content-Type": "application/json"
          }
        });
        if (response.status === 200) break;
      } catch (error) {
        continue;
      }
    }

    if (!response || response.status !== 200) {
      throw new Error("Aucun endpoint valide trouvé");
    }

    tripHistory.value = response.data;
  } catch (error) {
    console.error("Erreur lors de la récupération de l'historique:", error);
    errorMessage.value = "Impossible de charger l'historique des trajets";
    if (error.response?.status === 401) {
      localStorage.removeItem("access_token");
      router.push("/login");
    }
  } finally {
    tripHistoryLoading.value = false;
  }
};

const confirmDeleteTrip = async (tripId) => {
  if (!confirm("Êtes-vous sûr de vouloir supprimer ce trajet ?")) return;

  try {
    const token = localStorage.getItem("access_token");
    const response = await axios.delete(
      `http://127.0.0.1:8000/api/trips/${tripId}`,
      {
        headers: {
          Authorization: `Bearer ${token}`
        }
      }
    );

    if (response.status === 200) {
      await fetchTripHistory();
      successMessage.value = "Trajet supprimé avec succès!";
      setTimeout(() => (successMessage.value = ""), 5000);
    }
  } catch (error) {
    console.error("Erreur lors de la suppression:", error);
    errorMessage.value = "Erreur lors de la suppression du trajet";
  }
};

const editTrip = (tripData) => {
  resetForm();

  trip.value = {
    id: tripData.id,
    departure: tripData.departure,
    destination: tripData.destination,
    trip_date: tripData.trip_date.includes("T")
      ? tripData.trip_date.split("T")[0]
      : tripData.trip_date,
    departure_time: tripData.departure_time?.substring(0, 5) || "",
    estimate_arrival_time: tripData.estimate_arrival_time?.substring(0, 5) || "",
    price: tripData.price,
    available_seats: tripData.available_seats,
    instant_booking: tripData.instant_booking !== undefined ? tripData.instant_booking : true
  };

  calculateDistanceAndPrice();
  currentStep.value = 1;
  window.scrollTo(0, 0);
};

const startNewTrip = () => {
  resetForm();
  currentStep.value = 1;
};

const getStatusText = (status) => {
  const statusMap = {
    active: "Actif",
    completed: "Terminé",
    canceled: "Annulé"
  };
  return statusMap[status] || status;
};

const calculateDistanceAndPrice = () => {
  if (!trip.value.departure || !trip.value.destination) {
    recommendedPrice.value = { min: 10, max: 50 };
    priceAdvice.value = "Sélectionnez un trajet pour voir les prix recommandés";
    return;
  }

  const dep = trip.value.departure.toLowerCase();
  const dest = trip.value.destination.toLowerCase();
  const routeKey = `${dep}-${dest}`;
  const reverseRouteKey = `${dest}-${dep}`;

  distance.value = cityDistances[routeKey] || cityDistances[reverseRouteKey] || 50;

  const pricePerKm = 0.2;
  const basePrice = Math.round(distance.value * pricePerKm);

  recommendedPrice.value = {
    min: Math.max(10, basePrice - 5),
    max: Math.max(15, basePrice + 5)
  };

  if (distance.value > 200) {
    priceAdvice.value = `Long trajet (${distance.value}km) - Prix élevé recommandé`;
  } else if (distance.value > 100) {
    priceAdvice.value = `Trajet moyen (${distance.value}km) - Prix standard`;
  } else {
    priceAdvice.value = `Court trajet (${distance.value}km) - Prix économique`;
  }

  if (!trip.value.price) {
    trip.value.price = Math.round(
      (recommendedPrice.value.min + recommendedPrice.value.max) / 2
    );
  }
};

const nextStep = () => {
  errorMessage.value = "";

  if (currentStep.value === 1 && (!trip.value.departure || !trip.value.destination)) {
    errorMessage.value = "Veuillez saisir le départ et la destination";
    return;
  }
  if (currentStep.value === 2 && !trip.value.trip_date) {
    errorMessage.value = "Veuillez sélectionner une date";
    return;
  }
  if (currentStep.value === 3 && (!trip.value.departure_time || !trip.value.estimate_arrival_time)) {
    errorMessage.value = "Veuillez saisir les heures de départ et d'arrivée";
    return;
  }
  if (currentStep.value === 4 && (!trip.value.price || !trip.value.available_seats)) {
    errorMessage.value = "Veuillez saisir le prix et le nombre de places";
    return;
  }

  if (currentStep.value < steps.length) {
    currentStep.value++;
  }
};

const prevStep = () => {
  errorMessage.value = "";
  if (currentStep.value > 1) {
    currentStep.value--;
  }
};

const formatDate = (dateString) => {
  if (!dateString) return "";
  const date = new Date(dateString);
  return date.toLocaleDateString("fr-FR", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric"
  });
};

const submitTrip = async () => {
  errorMessage.value = "";
  successMessage.value = "";
  loading.value = true;

  try {
    const token = localStorage.getItem("access_token");
    if (!token) throw new Error("Aucun token d'authentification trouvé");

    const tripData = {
      departure: trip.value.departure,
      destination: trip.value.destination,
      trip_date: trip.value.trip_date,
      departure_time: trip.value.departure_time,
      estimate_arrival_time: trip.value.estimate_arrival_time,
      price: parseFloat(trip.value.price),
      available_seats: parseInt(trip.value.available_seats),
      instant_booking: true
    };

    let response;
    if (trip.value.id) {
      response = await axios.put(
        `http://127.0.0.1:8000/api/trips/${trip.value.id}`,
        tripData,
        {
          headers: {
            Authorization: `Bearer ${token}`,
            "Content-Type": "application/json"
          }
        }
      );
      successMessage.value = "Trajet mis à jour avec succès!";
    } else {
      response = await axios.post(
        "http://127.0.0.1:8000/api/create-trip",
        tripData,
        {
          headers: {
            Authorization: `Bearer ${token}`,
            "Content-Type": "application/json"
          }
        }
      );
      successMessage.value = "Trajet publié avec succès!";
    }

    if ([200, 201].includes(response.status)) {
      await fetchTripHistory();
      currentStep.value = 6;
      resetForm();
      setTimeout(() => {
        successMessage.value = "";
      }, 5000);
    }
  } catch (error) {
    handleApiError(error);
  } finally {
    loading.value = false;
  }
};

const handleApiError = (error) => {
  console.error("Erreur API:", error);
  if (error.response) {
    if (error.response.status === 401) {
      errorMessage.value = "Session expirée - Veuillez vous reconnecter";
      localStorage.removeItem("access_token");
      router.push("/login");
    } else {
      errorMessage.value =
        error.response.data?.message || `Erreur serveur (${error.response.status})`;
    }
  } else if (error.request) {
    errorMessage.value = "Le serveur ne répond pas";
  } else {
    errorMessage.value = "Erreur de configuration de la requête";
  }
};

const resetForm = () => {
  trip.value = {
    id: null,
    departure: "",
    destination: "",
    trip_date: "",
    departure_time: "",
    estimate_arrival_time: "",
    price: "",
    available_seats: 1,
    instant_booking: true
  };
  distance.value = 0;
  recommendedPrice.value = { min: 10, max: 50 };
  priceAdvice.value = "Sélectionnez un trajet pour voir les prix recommandés";
};

onMounted(() => {
  const token = localStorage.getItem("access_token");
  if (!token) {
    router.push("/login");
  } else {
    fetchTripHistory();
    calculateDistanceAndPrice();
  }
});

watch(
  () => [trip.value.departure, trip.value.destination],
  () => {
    calculateDistanceAndPrice();
  }
);
</script>


<template>
  <div class="min-h-screen bg-white">
    <!-- En-tête -->
    <div class="bg-blue-600 text-white p-4 shadow-md">
      <h1 class="text-2xl font-bold text-center">🚗 Publier un trajet</h1>
    </div>

    <!-- Barre de progression avec bouton Historique -->
    <div class="flex justify-between px-8 py-4 border-b relative">
      <!-- Étapes principales -->
      <div v-for="(step, index) in steps" :key="index" class="text-center flex-1">
        <div :class="['rounded-full w-8 h-8 flex items-center justify-center mx-auto', 
                     currentStep > index ? 'bg-green-500 text-white' : 
                     currentStep === index ? 'bg-blue-500 text-white' : 'bg-gray-200']">
          {{ index + 1 }}
        </div>
        <div class="text-sm mt-1">{{ step.title }}</div>
      </div>
      
      <!-- Bouton Historique à droite -->
      <div class="absolute right-0 top-4 ml-4">
        <button @click="showHistory" 
                :class="['rounded-full w-8 h-8 flex items-center justify-center mx-auto',
                        currentStep === 6 ? 'bg-blue-500 text-white' : 'bg-gray-200 hover:bg-gray-300']"
                title="Voir l'historique">
          <h2 class="text-xl font-bold text-blue-600 mb-4 text-center">📜 Historique</h2>
        </button>
      </div>
    </div>

    <!-- Contenu principal -->
    <div class="p-6 max-w-2xl mx-auto">
      <!-- Message de succès -->
      <div v-if="successMessage" class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
        <div class="flex items-center">
          <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
          </svg>
          <span class="font-medium">{{ successMessage }}</span>
        </div>
      </div>

      <!-- Message d'erreur -->
      <div v-if="errorMessage" class="text-red-500 text-center mt-4 p-3 bg-red-50 rounded-lg">
        {{ errorMessage }}
      </div>

      <!-- Étape 1: Départ et Destination -->
      <div v-show="currentStep === 1" class="space-y-6">
        <h2 class="text-2xl font-bold text-blue-600 mb-2 text-center">Où allez-vous ?</h2>
        
        <div>
          <label class="block text-gray-700 mb-2">Départ</label>
          <input v-model="trip.departure" type="text" placeholder="Ville de départ" 
                 class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" required />
        </div>

        <div>
          <label class="block text-gray-700 mb-2">Destination</label>
          <input v-model="trip.destination" type="text" placeholder="Ville d'arrivée" 
                 class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" required />
        </div>
      </div>

      <!-- Étape 2: Date seulement -->
      <div v-show="currentStep === 2" class="space-y-6">
        <h2 class="text-2xl font-bold text-blue-600 mb-6 text-center">📅 Quand partez-vous ?</h2>
        
        <div>
          <label class="block text-gray-700 mb-2">Date du trajet</label>
          <input v-model="trip.trip_date" type="date" :min="todayDate"
                 class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" required />
        </div>
      </div>

      <!-- Étape 3: Heures seulement -->
      <div v-show="currentStep === 3" class="space-y-6">
        <h2 class="text-2xl font-bold text-blue-600 mb-6 text-center">⏰ À quelle heure ?</h2>
        
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-gray-700 mb-2">Heure de départ</label>
            <input v-model="trip.departure_time" type="time"
                   class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" required />
          </div>
          <div>
            <label class="block text-gray-700 mb-2">Heure d'arrivée estimée</label>
            <input v-model="trip.estimate_arrival_time" type="time"
                   class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" required />
          </div>
        </div>
      </div>

      <!-- Étape 4: Prix et Places -->
      <div v-show="currentStep === 4" class="space-y-6">
        <h2 class="text-xl font-bold text-blue-600 mb-4 text-center">Fixez votre prix par place</h2>
        
        <div class="p-4 rounded-lg">
          <div class="space-y-4">
            <div>
              <label class="block text-gray-700 mb-2 font-medium">Prix (DT)</label>
              <input v-model="trip.price" type="number" :min="recommendedPrice.min" step="0.5"
                     class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" required />
              <p class="text-sm text-gray-500 mt-1">Prix recommandé: {{ recommendedPrice.min }} - {{ recommendedPrice.max }} DT</p>
            </div>
            <div>
              <label class="block text-gray-700 mb-2 font-medium">Places disponibles</label>
              <input v-model="trip.available_seats" type="number" min="1" max="8" placeholder="1"
                     class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" required />
            </div>
          </div>
        </div>
      </div>

      <!-- Étape 5: Confirmation -->
      <div v-show="currentStep === 5" class="space-y-6">
        <h2 class="text-xl font-bold text-blue-600 mb-4 text-center">✅ Confirmation</h2>
        
        <div class="bg-gray-50 p-4 rounded-lg border">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="mb-3">
              <span class="font-semibold">Départ:</span> {{ trip.departure || 'Non spécifié' }}
            </div>
            <div class="mb-3">
              <span class="font-semibold">Destination:</span> {{ trip.destination || 'Non spécifié' }}
            </div>
            <div class="mb-3">
              <span class="font-semibold">Date:</span> {{ trip.trip_date ? formatDate(trip.trip_date) : 'Non spécifié' }}
            </div>
            <div class="mb-3">
              <span class="font-semibold">Heure de départ:</span> {{ trip.departure_time || 'Non spécifié' }}
            </div>
            <div class="mb-3">
              <span class="font-semibold">Heure d'arrivée estimée:</span> {{ trip.estimate_arrival_time || 'Non spécifié' }}
            </div>
            <div class="mb-3">
              <span class="font-semibold">Prix:</span> {{ trip.price || '0' }} DT
            </div>
            <div class="mb-3">
              <span class="font-semibold">Places disponibles:</span> {{ trip.available_seats || '1' }}
            </div>
          </div>
        </div>
      </div>

      <!-- Étape 6: Historique des trajets -->
      <div v-show="currentStep === 6" class="space-y-6">
        <h2 class="text-xl font-bold text-blue-600 mb-4 text-center">📜 Historique de vos trajets</h2>
        
        <div v-if="tripHistoryLoading" class="text-center py-8">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500"></div>
          <p class="mt-2">Chargement de l'historique...</p>
        </div>
        
        <div v-else>
          <div v-if="tripHistory.length > 0" class="space-y-4">
            <div v-for="tripItem in tripHistory" :key="tripItem.id" 
                 class="border rounded-lg p-4 hover:shadow-md transition-shadow">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                <div>
                  <span class="font-semibold">Trajet:</span> {{ tripItem.departure }} → {{ tripItem.destination }}
                </div>
                <div>
                  <span class="font-semibold">Date:</span> {{ formatDate(tripItem.trip_date) }}
                </div>
                <div>
                  <span class="font-semibold">Heure:</span> {{ tripItem.departure_time }}
                </div>
                <div>
                  <span class="font-semibold">Prix:</span> {{ tripItem.price }} DT
                </div>
                <div>
                  <span class="font-semibold">Places:</span> {{ tripItem.available_seats }}
                </div>
                <div>
                  <span class="font-semibold">Statut:</span> 
                  <span :class="{
                    'text-green-500': tripItem.status === 'active', 
                    'text-gray-500': tripItem.status === 'completed',
                    'text-red-500': tripItem.status === 'canceled'
                  }">
                    {{ getStatusText(tripItem.status) }}
                  </span>
                </div>
              </div>
               <router-link 
                  :to="{ name: 'messenger', query: { tripId: tripItem.id, departure: tripItem.departure, destination: tripItem.destination } }"
                  class="px-3 py-1 bg-green-100 text-green-600 rounded hover:bg-green-200 transition text-sm font-medium flex items-center"
                >
                  <span class="mr-1">💬</span> Message
                </router-link>
              <div class="mt-2 flex justify-end space-x-2">
                <button @click="editTrip(tripItem)" 
                        class="px-3 py-1 bg-blue-100 text-blue-600 rounded hover:bg-blue-200 transition text-sm font-medium">
                  Modifier
                </button>
                <button @click="confirmDeleteTrip(tripItem.id)" 
                        class="px-3 py-1 bg-red-100 text-red-600 rounded hover:bg-red-200 transition text-sm font-medium">
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

      <!-- Boutons de navigation -->
      <div class="flex justify-between mt-8">
        <button v-if="currentStep > 1 && currentStep <= steps.length" @click="prevStep" type="button"
                class="bg-gray-200 text-gray-700 py-3 px-6 rounded-lg hover:bg-gray-300 transition font-medium">
          Précédent
        </button>
        <div v-else></div>

        <button v-if="currentStep < steps.length" @click="nextStep" type="button"
                class="bg-blue-600 text-white py-3 px-6 rounded-lg hover:bg-blue-700 transition font-medium ml-auto">
          Suivant
        </button>
        
        <button v-if="currentStep === steps.length" @click="submitTrip" type="button" :disabled="loading"
                class="bg-green-600 text-white py-3 px-6 rounded-lg hover:bg-green-700 transition font-medium ml-auto">
          {{ loading ? "Publication en cours..." : "Publier le trajet" }}
        </button>

        <button v-if="currentStep === 6" @click="startNewTrip" type="button"
                class="bg-blue-600 text-white py-3 px-6 rounded-lg hover:bg-blue-700 transition font-medium ml-auto">
          Nouveau trajet
        </button>
      </div>
    </div>
  </div>
</template>
<style>
body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
input[type="text"],
input[type="date"],
input[type="time"],
input[type="number"] {
  transition: border-color 0.3s, box-shadow 0.3s;
}

/* Styles améliorés pour les boutons */
button {
  transition: all 0.2s ease;
}
button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}
</style>
