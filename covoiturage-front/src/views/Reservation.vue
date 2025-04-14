<script>
import axios from 'axios';

export default {
  data() {
    return {
      tripId: this.$route.params.tripId || null,
      trip: null,
      loading: true,
      reservation: {
        trip_id: null,
        user_id: localStorage.getItem('user_id') || null, // Initialize from localStorage
      },
      successMessage: '',
      errorMessage: '',
      baseUrl: process.env.VUE_APP_API_URL || 'http://127.0.0.1:8000/api',
    };
  },
  mounted() {
    this.checkAuthAndFetch();
  },
  methods: {
    async checkAuthAndFetch() {
      const token = localStorage.getItem('token');
      const userId = localStorage.getItem('user_id'); 

      console.log('Token:', token);
      console.log('User ID from localStorage:', userId);
      console.log('Trip ID:', this.tripId);

      if (!token || !userId) {
        this.errorMessage = 'Veuillez vous connecter pour réserver un trajet.';
        this.loading = false;
        this.$router.push('/login');
        return;
      }

      if (!this.tripId) {
        this.errorMessage = 'ID du trajet non trouvé.';
        this.loading = false;
        return;
      }

      this.reservation.user_id = userId; // Ensure it’s set
      await this.fetchTripData(token);
    },

    async fetchTripData(token) {
      try {
        const tripResponse = await axios.get(`${this.baseUrl}/trips/${this.tripId}`, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });
        this.trip = tripResponse.data.trip || tripResponse.data;
        this.reservation.trip_id = this.trip.id;
        console.log('Trip Data:', this.trip);

        // Skip GET /api/user since user_id is from localStorage
        console.log('User ID (used):', this.reservation.user_id);
      } catch (error) {
        console.error('Fetch Error:', error.response);
        this.errorMessage =
          error.response?.status === 401
            ? 'Session expirée. Veuillez vous reconnecter.'
            : error.response?.data?.message || 'Erreur de chargement des données.';
        if (error.response?.status === 401) {
          this.$router.push('/login');
        }
      } finally {
        this.loading = false;
      }
    },

    async submitReservation() {
      const token = localStorage.getItem('token');
      const userId = localStorage.getItem('user_id'); // Re-fetch for safety

      console.log('Submitting with Token:', token);
      console.log('URL:', `${this.baseUrl}/trips/${this.tripId}/passenger/${this.reservation.user_id}`);

      if (!token || !userId) {
        this.errorMessage = 'Veuillez vous connecter pour réserver un trajet.';
        this.$router.push('/login');
        return;
      }

      if (!this.trip || !this.reservation.user_id) {
        this.errorMessage = 'Données incomplètes. Veuillez recharger la page.';
        return;
      }

      this.loading = true;
      this.successMessage = '';
      this.errorMessage = '';

      try {
        const response = await axios.post(
          `${this.baseUrl}/trips/${this.tripId}/passenger/${this.reservation.user_id}`,
          {},
          {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          }
        );
        this.successMessage = response.data.message || 'Réservation confirmée !';
        this.trip.available_seats = response.data.available_seats || this.trip.available_seats - 1;
        console.log('Success Response:', response.data);
      } catch (error) {
        console.error('Reservation Error:', error.response);
        this.errorMessage = error.response?.data?.message || 'Erreur lors de la réservation.';
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>
<template>
  <div class="flex justify-center items-center min-h-screen bg-gray-100 p-6">
    <div class="bg-white shadow-lg rounded-lg p-6 w-full max-w-lg relative">
      <h1 class="text-3xl font-bold text-center text-gray-800">🚗 Réserver votre trajet</h1>

      <p v-if="loading" class="text-center text-gray-500 mt-4">Chargement...</p>

      <div v-else-if="trip" class="mt-6">
        <div class="bg-blue-100 p-4 rounded-lg shadow-md">
          <p class="text-lg font-semibold">📍 Départ : {{ trip.departure }}</p>
          <p class="text-lg font-semibold">🚩 Destination : {{ trip.destination }}</p>
          <p class="text-lg">📅 Date : {{ trip.trip_date }}</p>
          <p class="text-lg">💰 Prix : <span class="text-green-600">{{ trip.price }} TND</span></p>
          <p class="text-lg">👥 Places disponibles : {{ trip.available_seats }}</p>
        </div>

        <form @submit.prevent="submitReservation" class="mt-6">
          <button type="submit"
                  class="mt-6 w-full bg-green-600 text-white py-2 px-4 rounded-md text-lg font-semibold hover:bg-green-700 transition"
                  :disabled="loading">
            {{ loading ? 'Réservation en cours...' : 'Confirmer la réservation' }}
          </button>
        </form>

        <p v-if="successMessage" class="text-green-600 mt-4">{{ successMessage }}</p>
        <p v-if="errorMessage" class="text-red-600 mt-4">{{ errorMessage }}</p>
      </div>

      <p v-else class="text-center text-red-500 mt-6">🚨 Trajet non trouvé.</p>
    </div>
  </div>
</template>

<style scoped>
.trip-list {
  list-style: none;
  padding: 0;
  max-width: 600px;
  margin: 0 auto;
}
.trip-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px;
  margin: 10px 0;
  border: 1px solid #ddd;
  border-radius: 5px;
  background-color: #f9f9f9;
}
button {
  padding: 8px 16px;
  background-color: #28a745;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}
button:disabled {
  background-color: #6c757d;
  cursor: not-allowed;
}
.error {
  color: red;
  text-align: center;
}
.success {
  color: green;
  text-align: center;
}
</style>