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
        user_id: localStorage.getItem('user_id') || null,
      },
      successMessage: '',
      errorMessage: '',
      baseUrl: import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api',
    };
  },

  mounted() {
    this.checkAuthAndFetch();
  },

  methods: {
    async checkAuthAndFetch() {
      const token = localStorage.getItem('token');
      const userId = localStorage.getItem('user_id');

      if (!token || !userId) {
        this.errorMessage = 'Veuillez vous connecter pour réserver un trajet.';
        this.loading = false;

        setTimeout(() => {
          this.$router.push('/login');
        }, 1000);
        return;
      }

      if (!this.tripId) {
        this.errorMessage = 'Identifiant de trajet manquant';
        this.loading = false;
        return;
      }

      this.reservation.user_id = userId;
      await this.fetchTripData(token);
    },

    async fetchTripData(token) {
      try {
        const response = await axios.get(
          `${this.baseUrl}/trips/${this.tripId}`,
          {
            headers: { Authorization: `Bearer ${token}` },
          }
        );
        this.trip = response.data.trip || response.data;
        this.reservation.trip_id = this.trip.id;
      } catch (error) {
        this.handleFetchError(error);
      } finally {
        this.loading = false;
      }
    },

    handleFetchError(error) {
      if (error.response?.status === 401) {
        this.errorMessage = 'Session expirée. Redirection...';
        setTimeout(() => this.$router.push('/login'), 1500);
      } else if (error.response?.status === 404) {
        this.errorMessage = 'Trajet introuvable';
      } else {
        this.errorMessage = error.response?.data?.message || 'Erreur de connexion au serveur';
      }
    },

    async submitReservation() {
      if (!this.validateReservation()) return;

      this.loading = true;
      this.successMessage = '';
      this.errorMessage = '';

      try {
        const token = localStorage.getItem('token');
        const response = await axios.post(
          `${this.baseUrl}/trips/${this.tripId}/passenger/${this.reservation.user_id}`,
          {},
          {
            headers: { Authorization: `Bearer ${token}` },
          }
        );

        this.handleReservationSuccess(response);
      } catch (error) {
        this.handleReservationError(error);
      } finally {
        this.loading = false;
      }
    },

    validateReservation() {
      if (!this.trip) {
        this.errorMessage = 'Trajet non chargé';
        return false;
      }
      if (this.trip.available_seats <= 0) {
        this.errorMessage = 'Plus de places disponibles';
        return false;
      }
      return true;
    },

    handleReservationSuccess(response) {
      this.successMessage = response.data.message || 'Réservation confirmée avec succès!';
      if (response.data.available_seats !== undefined) {
        this.trip.available_seats = response.data.available_seats;
      }
    },

    handleReservationError(error) {
      if (error.response?.status === 401) {
        this.errorMessage = 'Session expirée. Redirection...';
        setTimeout(() => this.$router.push('/login'), 1500);
      } else if (error.response?.status === 400) {
        this.errorMessage = error.response.data.message || 'Erreur dans la requête';
      } else {
        this.errorMessage = error.response?.data?.message || 'Erreur lors de la réservation';
      }
    },

    goToReservationHistory() {
      this.$router.push('/reservation-history');
    },
  },
};
</script>

<template>
<div class="min-h-screen pt-20 flex justify-center p-4">


    <div class="w-full max-w-md">
      <!-- Carte principale simplifiée -->
      <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <!-- Contenu -->
        <div class="p-6">
          <!-- Titre intégré -->
          <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Votre réservation</h1>
            <p class="text-gray-500 mt-1">Confirmation en quelques secondes</p>
          </div>

          <!-- Chargement -->
          <div v-if="loading" class="flex flex-col items-center justify-center py-8">
            <div class="w-16 h-16 border-4 border-indigo-100 rounded-full border-t-indigo-600 animate-spin mb-4"></div>
            <p class="text-gray-600">Recherche du trajet...</p>
          </div>

          <!-- Erreur -->
          <div v-else-if="!trip" class="text-center py-8">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
              <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-1">Trajet non disponible</h3>
            <p class="text-sm text-gray-500">Le trajet demandé n'existe pas ou a été supprimé.</p>
          </div>

          <!-- Détails du trajet -->
          <div v-else>
            <!-- Itinéraire -->
            <div class="flex items-center mb-6">
              <div class="flex flex-col items-center mr-4">
                <div class="w-3 h-3 rounded-full bg-indigo-600 mb-1"></div>
                <div class="w-1 h-8 bg-gray-300"></div>
                <div class="w-3 h-3 rounded-full bg-green-600 mt-1"></div>
              </div>
              <div class="flex-1">
                <div class="font-semibold text-gray-900">{{ trip.departure }}</div>
                <div class="text-xs text-gray-500 mb-4">Départ</div>
                <div class="font-semibold text-gray-900">{{ trip.destination }}</div>
                <div class="text-xs text-gray-500">Destination</div>
              </div>
            </div>

            <!-- Informations -->
            <div class="grid grid-cols-2 gap-4 mb-6">
              <div class="bg-gray-50 p-3 rounded-lg">
                <p class="text-xs text-gray-500 uppercase">Date</p>
                <p class="font-medium">{{ trip.trip_date }}</p>
              </div>
              <div class="bg-gray-50 p-3 rounded-lg">
                <p class="text-xs text-gray-500 uppercase">Heure</p>
                <p class="font-medium">{{ trip.departure_time || '--:--' }}</p>
              </div>
              <div class="bg-gray-50 p-3 rounded-lg">
                <p class="text-xs text-gray-500 uppercase">Places</p>
                <p class="font-medium">{{ trip.available_seats }} disponible(s)</p>
              </div>
              <div class="bg-gray-50 p-3 rounded-lg">
                <p class="text-xs text-gray-500 uppercase">Prix</p>
                <p class="font-medium text-green-600">{{ trip.price }} TND</p>
              </div>
            </div>

            <!-- Bouton de confirmation -->
            <button
              @click="submitReservation"
              :disabled="loading"
              class="w-full flex items-center justify-center px-6 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200 disabled:opacity-70 disabled:cursor-not-allowed"
            >
              <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ loading ? 'Traitement...' : 'Confirmer la réservation' }}
            </button>

            <!-- Messages -->
            <div class="mt-4 space-y-2">
              <div v-if="successMessage" class="p-3 bg-green-50 text-green-800 rounded-lg flex items-start">
                <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>{{ successMessage }}</span>
              </div>
              <div v-if="errorMessage" class="p-3 bg-red-50 text-red-800 rounded-lg flex items-start">
                <svg class="h-5 w-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ errorMessage }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pied de page -->
      <div class="mt-4 text-center text-sm text-gray-500">
        <p>En confirmant, vous acceptez nos <a href="#" class="text-indigo-600 hover:text-indigo-500">conditions d'utilisation</a></p>
      </div>
      <div class="mt-4 text-center">
  <button
    @click="goToReservationHistory"
    class="w-full px-6 py-3 text-base font-medium text-indigo-600 bg-transparent border border-indigo-600 rounded-md hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors duration-200"
  >
    Voir mon historique de réservations
  </button>
</div>
    </div>
  </div>
</template>

