<script>
import axios from "axios";

export default {
  data() {
    return {
      trips: [],
      unreadNotifications: 0, // valeur par défaut
    };
  },
  mounted() {
    this.fetchTrips();
    this.fetchNotifications();
  },
  methods: {
    async fetchTrips() {
      try {
        const token = localStorage.getItem("access_token");
        const response = await axios.get("http://127.0.0.1:8000/api/all-trips", {
          headers: token ? { Authorization: `Bearer ${token}` } : {},
        });
        this.trips = response.data;
      } catch (error) {
        console.error("Erreur lors de la récupération des trajets :", error);
      }
    },

    async fetchNotifications() {
      try {
        const token = localStorage.getItem("access_token");
        if (token) {
          const response = await axios.get("http://127.0.0.1:8000/api/notifications/unread-count", {
            headers: { Authorization: `Bearer ${token}` },
          });
          this.unreadNotifications = response.data.unread_count || 0;
        }
      } catch (error) {
        console.error("Erreur lors de la récupération des notifications :", error);
      }
    },

    showNotifications() {
      this.$router.push({ name: 'notifications' });
    },

    formatDate(dateString) {
      if (!dateString) return 'Date non spécifiée';
      const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
      return new Date(dateString).toLocaleDateString('fr-FR', options);
    },

    getInitials(name) {
      if (!name) return '?';
      return name.split(' ')
        .map(part => part[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
    },

    reserveTrip(trip) {
      alert(`Réservation du trajet ${trip.departure} → ${trip.destination}`);
    },

    voteTrip(tripId, voteType) {
      console.log(`Vote ${voteType} pour le trajet ${tripId}`);
    },

    createNewTrip() {
      this.$router.push({ name: 'create-trip' });
    }
  },
};
</script>

<template>
  <div class="min-h-screen p-20 max-w-6xl mx-auto">
    <!-- En-tête avec bouton de notification -->
    <div class="flex justify-between items-center mb-8">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Trajets disponibles</h1>
        <p class="text-gray-500 mt-1">Découvrez les trajets partagés par la communauté</p>
      </div>
      <button 
        @click="showNotifications"
        class="relative p-2 text-gray-500 hover:text-blue-600 transition"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <span v-if="unreadNotifications" class="absolute top-0 right-0 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
          {{ unreadNotifications }}
        </span>
      </button>
    </div>

    <!-- Liste des trajets -->
    <div class="grid gap-5">
      <div 
        v-for="trip in trips" 
        :key="trip.id"
        class="bg-white p-5 rounded-xl border border-gray-200 hover:border-blue-200 transition-all shadow-sm hover:shadow-md"
      >
        <!-- En-tête de la carte -->
        <div class="flex justify-between items-start mb-4">
          <div>
            <h2 class="font-bold text-xl text-blue-600">
              {{ trip.departure }} → {{ trip.destination }}
            </h2>
            <p class="text-sm text-gray-500 mt-1">
              <span class="inline-block w-3 h-3 rounded-full bg-blue-500 mr-1"></span>
              {{ formatDate(trip.trip_date) }}
            </p>
          </div>
          <span class="bg-blue-100 text-blue-800 text-xs px-3 py-1 rounded-full">
            {{ trip.available_seats > 0 ? 'Disponible' : 'Complet' }}
          </span>
        </div>

        <!-- Détails principaux -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
          <div>
            <h3 class="font-semibold text-gray-700 mb-2">Horaires</h3>
            <ul class="space-y-2">
              <li class="flex items-center text-sm text-gray-600">
                <span class="text-blue-500 mr-2">⏱</span>
                <span class="font-medium">Départ:</span> 
                {{ trip.departure_time }}
              </li>
              <li class="flex items-center text-sm text-gray-600">
                <span class="text-blue-500 mr-2">⌛</span>
                <span class="font-medium">Arrivée estimée:</span> 
                {{ trip.estimate_arrival_time }}
              </li>
            </ul>
          </div>

          <div>
            <h3 class="font-semibold text-gray-700 mb-2">Conditions</h3>
            <ul class="space-y-2">
              <li class="flex items-center text-sm text-gray-600">
                <span class="text-green-500 mr-2">💰</span>
                <span class="font-medium">Prix:</span> 
                {{ trip.price }} TND
              </li>
              <li class="flex items-center text-sm text-gray-600">
                <span class="text-purple-500 mr-2">🧑</span>
                <span class="font-medium">Places disponibles:</span> 
                {{ trip.available_seats }}
              </li>
              <li class="flex items-center text-sm text-gray-600">
                <span class="text-yellow-500 mr-2">⚡</span>
                <span class="font-medium">Réservation:</span> 
                {{ trip.instant_booking ? 'Instantanée' : 'Sur demande' }}
              </li>
            </ul>
          </div>
        </div>

        <!-- Conducteur -->
        <div class="flex items-center mt-4 pt-4 border-t border-gray-100" v-if="trip.driver">
          <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center mr-3 overflow-hidden">
            <img 
              v-if="trip.driver.photo" 
              :src="trip.driver.photo" 
              alt="Photo du conducteur"
              class="w-full h-full object-cover"
            >
            <span v-else class="text-gray-500 text-sm">{{ getInitials(trip.driver.name) }}</span>
          </div>
          <div class="flex-1">
            <p class="text-sm font-medium text-gray-700">{{ trip.driver.name }}</p>
            <p class="text-xs text-gray-500">Conducteur</p>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-between items-center mt-4 pt-4 border-t border-gray-100">
          <div class="flex space-x-2">
            <button 
              @click="voteTrip(trip.id, 'up')"
              class="flex items-center text-gray-500 hover:text-green-500 transition"
            >
              <span class="mr-1">⬆</span> {{ trip.upvotes || 0 }}
            </button>
            <button 
              @click="voteTrip(trip.id, 'down')"
              class="flex items-center text-gray-500 hover:text-red-500 transition ml-2"
            >
              <span class="mr-1">↓</span> {{ trip.downvotes || 0 }}
            </button>
          </div>
          
          <div class="flex space-x-2">
            <div 
              v-if="trip.available_seats <= 0" 
              class="bg-red-100 text-red-700 text-sm font-semibold px-4 py-2 rounded-lg flex items-center space-x-2"
            >
              <span>🚫</span>
              <span>Ce trajet est complet</span>
            </div>

            <button
              v-else
              @click="reserveTrip(trip)"
              class="flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-700 text-white text-sm font-semibold rounded-lg shadow hover:from-blue-600 hover:to-blue-800 transition duration-200"
            >
              <span class="mr-2">✅</span> Réserver ma place
            </button>

            <router-link 
              :to="{ name: 'messenger', query: { tripId: trip.id } }"
              class="flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-green-700 text-white text-sm font-semibold rounded-lg shadow hover:from-green-600 hover:to-green-800 transition duration-200"
            >
              <span class="mr-2">💬</span> Contacter le conducteur
            </router-link>
          </div>
        </div>
      </div>

      <!-- Aucun trajet -->
      <div v-if="!trips.length" class="text-center p-8 bg-white rounded-xl border border-gray-200">
        <div class="text-gray-400 mb-3 text-5xl">🚗</div>
        <h3 class="text-lg font-medium text-gray-700">Aucun trajet disponible</h3>
        <p class="text-gray-500 mt-1">Revenez plus tard ou proposez votre propre trajet</p>
        <button 
          class="mt-4 bg-blue-600 text-white py-2 px-6 rounded-lg hover:bg-blue-700 transition"
          @click="createNewTrip"
        >
          Proposer un trajet
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

.hover\:shadow-md:hover {
  --tw-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
  box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow);
}
</style>
