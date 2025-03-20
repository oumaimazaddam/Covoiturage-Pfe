<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-100 relative">
      <!-- Image de fond -->
      <div class="absolute inset-0 bg-cover bg-center filter blur-sm brightness-75" 
           style="background-image: url('/path-to-your-image.jpg');">
      </div>
  
      <!-- Overlay -->
      <div class="bg-black bg-opacity-50 absolute inset-0"></div>
  
      <!-- Carte des trajets -->
      <div class="relative bg-white p-8 rounded-3xl shadow-lg w-full max-w-2xl z-10">
        <h2 class="text-2xl font-bold text-center text-blue-600 mb-6">🚗 Trajets disponibles</h2>
  
        <div v-if="trips.length" class="space-y-4">
          <div v-for="trip in trips" :key="trip.id" class="p-4 bg-gray-200 rounded-lg shadow hover:shadow-lg transition">
            <p class="text-blue-800 font-bold flex items-center">
              <span class="mr-2">✈️</span> Départ : {{ trip.departure }}
            </p>
            <p class="text-red-800 font-bold flex items-center">
              <span class="mr-2">🚩</span> Destination : {{ trip.destination }}
            </p>
            <p class="text-gray-700 flex items-center">
              <span class="mr-2">⏰</span> Heure de départ : {{ trip.departure_time }}
            </p>
            <p class="text-green-700 font-semibold flex items-center">
              <span class="mr-2">💰</span> Prix : <strong>{{ trip.price }}</strong> TND
            </p>
            <button class="mt-2 w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition">
              Réserver
            </button>
          </div>
        </div>
        <div v-else class="text-center text-gray-600">Aucun trajet disponible.</div>
      </div>
    </div>
  </template>
  
  <script>
  import axios from "axios";
  
  export default {
    data() {
      return {
        trips: [],
      };
    },
    async mounted() {
      await this.fetchTrips();
    },
    methods: {
      async fetchTrips() {
        try {
          const token = localStorage.getItem("access_token");
          const response = await axios.get("http://127.0.0.1:8000/api/trips", {
            headers: { Authorization: `Bearer ${token}` },
          });
          this.trips = response.data;
        } catch (error) {
          console.error("Erreur lors de la récupération des trajets:", error);
        }
      },
    },
  };
  </script>
  
  <style scoped>
  .bg-cover {
    background-size: cover;
  }
  </style>
  