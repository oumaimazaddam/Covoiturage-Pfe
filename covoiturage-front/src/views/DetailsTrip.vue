<script>
import axios from "axios";

export default {
  data() {
    return {
      trip: null,
      driver: null,
      loading: true,
      error: null,
    };
  },
  created() {
    this.fetchTripDetails();
  },
  methods: {
    async fetchTripDetails() {
      this.loading = true;
      this.error = null;
      const id = this.$route.params.id;
      const token = localStorage.getItem("access_token");

      try {
        // Récupérer les détails du trajet
        const tripResponse = await axios.get(`http://127.0.0.1:8000/api/trips/${id}`, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });
        console.log("Réponse API trajet:", JSON.stringify(tripResponse.data, null, 2));
        this.trip = tripResponse.data.trip;

        // Récupérer les informations du conducteur
        try {
          const driverResponse = await axios.get(`http://127.0.0.1:8000/api/trips/${id}/driver`, {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          });
          console.log("Réponse API conducteur:", driverResponse.data);
          this.driver = driverResponse.data;
        } catch (driverError) {
          console.warn("Conducteur non trouvé:", driverError.response?.data?.message || driverError.message);
          this.driver = null;
        }
      } catch (error) {
        console.error("Erreur lors du chargement du trajet:", error);
        this.error = error.response?.data?.message || "Une erreur est survenue lors du chargement du trajet.";
        if (error.response) {
          console.error("Détails de l'erreur:", error.response.data);
        }
      } finally {
        this.loading = false;
      }
    },
    formatDate(date) {
      if (!date) return "";
      const options = { weekday: "long", year: "numeric", month: "long", day: "numeric" };
      return new Date(date).toLocaleDateString("fr-FR", options);
    },
    formatTime(time) {
      return time ? time.substring(0, 5) : "";
    },
    reserveTrip() {
      if (this.trip?.available_seats > 0) {
        this.$router.push(`/reservation/${this.trip.id}`);
      }
    },
  },
};
</script>

<template>
  <div class="max-w-3xl mx-auto mt-6">
    <h1 class="text-3xl font-bold text-blue-600 mb-6 text-center">Détails du trajet</h1>

    <!-- Gestion des erreurs -->
    <div v-if="error" class="text-center text-red-500 mb-4">
      {{ error }}
    </div>

    <!-- Chargement -->
    <div v-else-if="loading" class="text-center text-gray-500 mt-10">
      Chargement des informations du trajet...
    </div>

    <!-- Affichage des détails du trajet -->
    <div v-else-if="trip" class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <div class="md:col-span-2 space-y-6">
        <!-- Itinéraire -->
        <div class="bg-gray-50 p-4 rounded-lg">
          <h2 class="text-xl font-semibold mb-4 flex items-center">
            <span class="text-blue-500 mr-2">🚗</span> Itinéraire
          </h2>
          <div class="space-y-3">
            <div class="flex items-start">
              <span class="text-green-500 text-xl mr-3">📍</span>
              <div>
                <p class="font-medium">Départ</p>
                <p class="text-gray-700">{{ trip.departure }}</p>
              </div>
            </div>
            <div class="flex items-start">
              <span class="text-red-500 text-xl mr-3">🏁</span>
              <div>
                <p class="font-medium">Destination</p>
                <p class="text-gray-700">{{ trip.destination }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Date et heure -->
        <div class="bg-gray-50 p-4 rounded-lg">
          <h2 class="text-xl font-semibold mb-4 flex items-center">
            <span class="text-blue-500 mr-2">📅</span> Date et heure
          </h2>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <p class="font-medium">Date</p>
              <p>{{ formatDate(trip.trip_date) }}</p>
            </div>
            <div>
              <p class="font-medium">Heure de départ</p>
              <p>{{ formatTime(trip.departure_time) }}</p>
            </div>
            <div>
              <p class="font-medium">Heure d'arrivée estimée</p>
              <p>{{ formatTime(trip.estimate_arrival_time) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Conducteur et réservation -->
      <div class="space-y-6">
        <!-- Conducteur -->
        <div class="bg-gray-50 p-4 rounded-lg">
          <h2 class="text-xl font-semibold mb-4 flex items-center">
            <span class="text-blue-500 mr-2">👤</span> Conducteur
          </h2>
          <div v-if="driver" class="flex items-center space-x-4">
            <img
              :src="driver.photo_profile || 'https://via.placeholder.com/80'"
              class="w-16 h-16 rounded-full object-cover border-2 border-blue-200"
              alt="Photo du conducteur"
            />
            <div>
              <p class="font-medium">{{ driver.name || 'Nom non disponible' }}</p>
              <div class="flex items-center mt-1">
                <span class="text-yellow-400">★</span>
                <span class="ml-1 text-gray-700">{{ driver.rating || 'N/A' }}</span>
                <span class="mx-2 text-gray-300">|</span>
                <span class="text-sm text-gray-500">
                  {{ driver.trips_count || 0 }} trajets publiés
                </span>
              </div>
            </div>
          </div>
          <div v-else class="text-gray-500">
            Aucun conducteur assigné à ce trajet.
          </div>
        </div>

        <!-- Prix et places -->
        <div class="bg-gray-50 p-4 rounded-lg w-full">
          <h2 class="text-xl font-semibold mb-4 flex items-center">
            <span class="text-blue-500 mr-2">💰</span> Prix et places
          </h2>
          <div class="space-y-3">
            <div class="flex justify-between items-center">
              <span class="font-medium">Prix par place</span>
              <span class="text-xl font-bold text-blue-600">{{ trip.price }} TND</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="font-medium">Places disponibles</span>
              <span
                class="text-lg font-medium"
                :class="{ 'text-green-600': trip.available_seats > 0, 'text-red-600': trip.available_seats <= 0 }"
              >
                {{ trip.available_seats }} places
              </span>
            </div>
          </div>
        </div>

        <!-- Bouton de réservation -->
        <button
          @click="reserveTrip"
          class="w-full py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition duration-200 flex items-center justify-center space-x-2"
          :disabled="trip.available_seats <= 0"
        >
          <span>📌</span>
          <span>Réserver une place</span>
        </button>

        <div v-if="trip.available_seats <= 0" class="text-center text-red-500 text-sm mt-2">
          Désolé, ce trajet est complet.
        </div>
      </div>
    </div>

    <!-- Cas où aucun trajet n'est trouvé -->
    <div v-else class="text-center text-gray-500 mt-10">
      Aucun trajet trouvé.
    </div>
  </div>
</template>

<style scoped>
.bg-gray-50 {
  background-color: #f9fafb;
}
.rounded-lg {
  border-radius: 0.5rem;
}
.shadow-md {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}
.transition {
  transition: all 0.2s ease;
}
button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  background-color: #ccc;
}
</style>