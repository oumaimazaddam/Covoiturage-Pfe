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
      return new Date().toISOString().split("T")[0]; // Format YYYY-MM-DD
    },
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

        const response = await axios.get("http://127.0.0.1:8000/api/search-trip", {
          headers: { Authorization: `Bearer ${token}` },
          params: {
            departure: this.search.departure.trim(),
            destination: this.search.destination.trim(),
            trip_date: this.search.trip_date,
          },
        });

        this.trips = response.data;
      } catch (error) {
        console.error("❌ Erreur lors du chargement des trajets:", error);
        alert(`Une erreur est survenue : ${error.response?.data?.message || error.message}`);
      } finally {
        this.loading = false;
      }
    },

    reserveTrip(tripId) {
      this.$router.push(`/reservation/${tripId}`);
    },

    formatTime(time) {
      return time ? time.slice(0, 5) : "Non spécifié";
    },
  },
};
</script>

<template>
  
  <div class="p-60 bg-white h-screen relative overflow-hidden">


    <div class="absolute top-1 left-0 w-full h-[50vh] bg-cover bg-center brightness-70"
       style="background-image: url('https://images.unsplash.com/photo-1686199948265-ddc4ebb1cc92?q=80&w=1470&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D');">
  </div>
  

    <div class="-50 absolute inset-0"></div>

    <div class="relative max-w-4xl mx-auto  p-10 rounded-lg z-6">
      <h2 class="text-4xl font-bold text-center text-white mb-6">Où voulez-vous aller ?</h2>

      <!-- Formulaire de recherche -->
      <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-[90%] max-w-5xl bg-white p-2 rounded-full shadow-lg flex items-center justify-between space-x-5">
  
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

  <button @click="fetchTrips" class="bg-blue-600 text-white px-6 py-3 rounded-full hover:bg-blue-700 transition">
    Rechercher
  </button>
</div>

</div>

    <!-- Loader -->
    <div v-if="loading" class="text-center mt-6 text-gray-500">Chargement...</div>

    <!-- Résultats des trajets -->
    <div v-if="trips.length > 0" class="mt-6 max-w-2xl mx-auto relative z-10">
      <h3 class="text-xl font-bold text-blue-600">Trajets disponibles :</h3>

      <div v-for="trip in trips" :key="trip.id" class="bg-white shadow-md p-4 rounded-md mt-4 border relative">
        <p class="text-lg font-semibold">📍 {{ trip.departure }} → 🚩 {{ trip.destination }}</p>
        <p class="text-sm text-gray-500">📅 {{ trip.trip_date }} | 🕒 {{ formatTime(trip.departure_time) }} | 👤 {{ trip.available_seats }} places disponibles</p>
        <p class="text-xl font-bold text-blue-600">💰 {{ trip.price }} TND</p>

        <button @click="reserveTrip(trip.id)" class="mt-4 bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition"
                :disabled="trip.available_seats === 0">
          Réserver
        </button>
      </div>
    </div>

    <!-- Message d'erreur si aucun trajet ne correspond -->
    <div  v-if="searchPerformed && trips.length === 0"  class="text-center mt-6 text-red-600 p-3    relative z-90">
    🚫 Aucun trajet ne correspond aux critères sélectionnés.
</div>



  </div>
</template>

