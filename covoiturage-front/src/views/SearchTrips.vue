<script>
import axios from "axios";

export default {
  data() {
    return {
      search: {
        departure: "",
        destination: "",
        trip_date: "",
      },
      trips: [],
      loading: false,
      searchPerformed: false,
    };
  },
  computed: {
    todayDate() {
      return new Date().toISOString().split("T")[0];
    }
  },
  methods: {
    async fetchTrips() {
      this.loading = true;
      this.searchPerformed = true;

      try {
        const token = localStorage.getItem("access_token");
        if (!token) {
          alert("Vous devez être connecté pour rechercher un trajet.");
          this.loading = false;
          this.$router.push('/login');
          return;
        }

        const params = {};
        if (this.search.departure.trim()) params.departure = this.search.departure.trim();
        if (this.search.destination.trim()) params.destination = this.search.destination.trim();
        if (this.search.trip_date) params.trip_date = this.search.trip_date;

        const response = await axios.get("http://127.0.0.1:8000/api/search-trip", {
          headers: { Authorization: `Bearer ${token}` },
          params,
        });

        this.trips = response.data;
      } catch (error) {
        console.error("❌ Erreur lors du chargement des trajets:", error);
        alert(`Une erreur est survenue: ${error.response?.data?.message || error.message}`);
      } finally {
        this.loading = false;
      }
    },

    goToTripDetails(tripId) {
      this.$router.push(`/detailsTrip/${tripId}`);
    },

    formatTime(time) {
      return time ? time.slice(0, 5) : "Non spécifié";
    },
  },
  mounted() {
    this.fetchTrips(); // Charger tous les trajets au début
  }
};
</script>

<template>
  <div class="bg-white min-h-screen">
    <!-- Header -->
    <div class="sticky top-1 z-50">
      <div class="h-[300px] bg-cover bg-center brightness-70 relative"
        style="background-image: url('https://images.unsplash.com/photo-1686199948265-ddc4ebb1cc92?q=80&w=1470&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D');">
        <h2 class="text-4xl font-bold text-white text-center pt-20 drop-shadow-lg">Où voulez-vous aller ?</h2>

        <!-- Formulaire -->
        <div class="absolute bottom-[-20px] left-1/2 transform -translate-x-1/2 w-[90%] max-w-5xl bg-white p-2 rounded-full shadow-lg flex items-center justify-between space-x-5 z-50">
          <div class="flex items-center border border-gray-300 rounded-full px-4 py-2 w-full">
            <span class="text-pink-500 mr-2">📍</span>
            <input v-model="search.departure" type="text" placeholder="Départ" class="w-full focus:outline-none bg-transparent" />
          </div>

          <div class="flex items-center border border-gray-300 rounded-full px-4 py-2 w-full">
            <span class="text-pink-500 mr-2">📍</span>
            <input v-model="search.destination" type="text" placeholder="Destination" class="w-full focus:outline-none bg-transparent" />
          </div>

          <div class="flex items-center border border-gray-300 rounded-full px-4 py-2 w-full">
            <span class="text-gray-500 mr-2">📅</span>
            <input v-model="search.trip_date" type="date" class="w-full focus:outline-none bg-transparent" :min="todayDate" />
          </div>

          <button @click="fetchTrips" :disabled="loading"
            class="bg-blue-600 text-white px-6 py-3 rounded-full hover:bg-blue-700 transition disabled:opacity-50">
            Rechercher
          </button>
        </div>
      </div>
    </div>

    <div class="h-24"></div>

    <div class="max-w-7xl mx-auto px-4 pb-16">
      <div v-if="loading" class="text-center mt-6 text-gray-500">Chargement...</div>

      <div v-if="trips.length > 0">
        <h3 class="text-xl font-bold text-blue-600 mb-6 ml-2">Trajets disponibles :</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="trip in trips" :key="trip.id"
            @click="goToTripDetails(trip.id)"
            class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 hover:shadow-xl transition duration-300 transform hover:-translate-y-1 cursor-pointer group">

            <div class="bg-gradient-to-r from-blue-50 to-blue-100 p-4">
              <div class="flex justify-between items-start">
                <div>
                  <h4 class="text-lg font-bold text-gray-800">
                    <span class="text-blue-600">📍</span> {{ trip.departure }}
                  </h4>
                  <h4 class="text-lg font-bold text-gray-800 mt-1">
                    <span class="text-blue-600">🚩</span> {{ trip.destination }}
                  </h4>
                </div>
                <span class="bg-white px-3 py-1 rounded-full text-sm font-medium shadow-sm">
                  {{ trip.trip_date }}
                </span>
              </div>
            </div>

            <div class="p-4">
              <div class="flex justify-between items-center mb-4">
                <div class="flex items-center text-gray-600">
                  <svg class="w-5 h-5 mr-1 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                  {{ formatTime(trip.departure_time) }}
                </div>
                <div class="flex items-center text-gray-600">
                  <svg class="w-5 h-5 mr-1 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                  </svg>
                  {{ trip.available_seats }} places
                </div>
              </div>

              <div class="flex justify-between items-center mb-4">
                <span class="text-2xl font-bold text-blue-600">
                  {{ trip.price }} TND
                </span>
                <span class="text-sm text-gray-500">par personne</span>
              </div>

              <div class="flex items-center pt-4 border-t border-gray-100">
                <img v-if="trip.driver_photo" :src="trip.driver_photo" 
                  class="w-10 h-10 rounded-full object-cover mr-3 border-2 border-blue-200" />
                <div>
                  <p class="text-sm font-medium text-gray-700">Conducteur</p>
                  <p class="text-sm text-gray-500">{{ trip.driver_name || 'Non spécifié' }}</p>
                </div>
              </div>
            </div>

            <div class="bg-gray-50 px-4 py-3 text-right">
              <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                Voir détails →
              </button>
            </div>
          </div>
        </div>
      </div>

      <div v-if="searchPerformed && trips.length === 0" class="text-center mt-6 text-red-600 p-3">
        🚫 Aucun trajet ne correspond aux critères sélectionnés.
      </div>
    </div>
  </div>
</template>

<style>
.group:hover .group-hover\:text-blue-600 {
  color: #2563eb;
}
</style>
