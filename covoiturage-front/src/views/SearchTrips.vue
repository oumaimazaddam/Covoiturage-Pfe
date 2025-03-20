<template>
  <div class="p-6 bg-gray-100 min-h-screen relative">
    <div class="absolute inset-0 bg-cover bg-center brightness-75"

         style="background-image: url('https://st.depositphotos.com/62628780/61510/i/1600/depositphotos_615108510-stock-photo-freedom-travel-black-woman-window.jpg');">
    </div>
    <div class="bg-black bg-opacity-50 absolute inset-0"></div>

    <div class="relative max-w-4xl mx-auto bg-white shadow-md p-6 rounded-lg z-10">
      <h2 class="text-3xl font-bold text-center text-gray-800 mb-6">Où voulez-vous aller ?</h2>

      <!-- Formulaire de recherche -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="flex items-center border rounded-md px-3 py-2">
          <span class="mr-2">📍</span>
          <input v-model="search.departure" type="text" placeholder="Départ (optionnel)" class="w-full focus:outline-none" />
        </div>

        <div class="flex items-center border rounded-md px-3 py-2">
          <span class="mr-2">📍</span>
          <input v-model="search.destination" type="text" placeholder="Destination (optionnel)" class="w-full focus:outline-none" />
        </div>

        <div class="flex items-center border rounded-md px-3 py-2">
          <span class="mr-2">📅</span>
          <input v-model="search.trip_date" type="date" class="w-full focus:outline-none" :min="todayDate" />
        </div>

        <button @click="fetchTrips" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition"
                :disabled="!search.trip_date">
          Rechercher
        </button>
      </div>
    </div>

    <!-- Loader -->
    <div v-if="loading" class="text-center mt-6 text-gray-500">Chargement...</div>

    <!-- Résultats des trajets -->
    <div v-if="trips.length > 0" class="mt-6 max-w-2xl mx-auto relative z-10">
      <h3 class="text-lg font-semibold text-gray-700">Trajets disponibles :</h3>

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
    <div v-if="!loading && searchPerformed && trips.length === 0" class="text-center mt-6 text-red-500">
      Aucun trajet ne correspond aux critères sélectionnés.
    </div>
  </div>
</template>

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
      return new Date().toISOString().split("T")[0]; // Format YYYY-MM-DD pour désactiver les dates passées
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
          return;
        }

        // Récupérer TOUS les trajets pour la date choisie
        const response = await axios.get("http://127.0.0.1:8000/api/search-trip", {
          headers: { Authorization: `Bearer ${token}` },
          params: {
            trip_date: this.search.trip_date, // On envoie uniquement la date pour récupérer tous les trajets de ce jour
          },
        });

        this.trips = response.data; // On affiche tous les trajets trouvés pour cette date
      } catch (error) {
        console.error("❌ Erreur lors du chargement des trajets:", error);
        alert(`Une erreur est survenue: ${error.response?.data?.message || error.message}`);
      } finally {
        this.loading = false;
      }
    },

    async reserveTrip(tripId) {
      try {
        const token = localStorage.getItem("access_token");
        if (!token) {
          alert("Vous devez être connecté pour réserver un trajet.");
          return;
        }

        await axios.post(`http://127.0.0.1:8000/api/reserve-trip/${tripId}`, {}, {
          headers: { Authorization: `Bearer ${token}` },
        });

        alert("Réservation réussie !");
        this.$router.push("/confirmation");
      } catch (error) {
        console.error("❌ Erreur lors de la réservation :", error);
        alert(`Échec de la réservation : ${error.response?.data?.message || error.message}`);
      }
    },

    formatTime(time) {
      return time ? time.slice(0, 5) : "Non spécifié";
    },
  },
};
</script>
