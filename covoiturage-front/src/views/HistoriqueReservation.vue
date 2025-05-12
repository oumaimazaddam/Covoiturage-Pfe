<script>
import axios from "axios";

export default {
  name: "UserReservations",
  data() {
    return {
      reservations: [],
      loading: true,
      message: "",
      messageType: "",
      currentUser: null,
    };
  },
  computed: {
    messageClass() {
      return {
        "bg-green-50 border border-green-200 text-green-800":
          this.messageType === "success",
        "bg-red-50 border border-red-200 text-red-800":
          this.messageType === "error",
      };
    },
  },
  async mounted() {
    await this.loadUserData();
    await this.fetchReservations();
  },
  methods: {
    formatDate(dateString) {
      const options = { year: "numeric", month: "long", day: "numeric" };
      return new Date(dateString).toLocaleDateString("fr-FR", options);
    },

    async loadUserData() {
      const userId = localStorage.getItem("user_id");
      if (userId) {
        this.currentUser = { id: userId };
      }
    },

    async fetchReservations() {
      try {
        const token = localStorage.getItem("access_token"); // ← ou "access_token", selon ton backend
        const response = await axios.get(
          `http://localhost:8000/api/reservations/passenger/${this.currentUser.id}`,
          {
            headers: { Authorization: `Bearer ${token}` },
          }
        );
        this.reservations = response.data;
      } catch (error) {
        console.error("Erreur:", error);
        this.showMessage(
          "Erreur lors du chargement des réservations",
          "error"
        );
      } finally {
        this.loading = false;
      }
    },

    confirmCancel(tripId, passengerId) {
      if (confirm("Êtes-vous sûr de vouloir annuler cette réservation ?")) {
        this.cancelReservation(tripId, passengerId);
      }
    },

    async cancelReservation(tripId, passengerId) {
      try {
        const token = localStorage.getItem("token"); // ← ou "access_token"
        const response = await axios.delete(
          `http://localhost:8000/api/trips/${tripId}/passengers/${passengerId}`,
          {
            headers: { Authorization: `Bearer ${token}` },
          }
        );

        if (
          response.data.message === "Reservation cancelled successfully" ||
          response.data.message === "Réservation annulée avec succès"
        ) {
          this.showMessage("Réservation annulée avec succès", "success");
          await this.fetchReservations();
        }
      } catch (error) {
        console.error("Erreur:", error);
        this.showMessage(
          error.response?.data?.message || "Échec de l'annulation",
          "error"
        );
      }
    },

    editReservation(reservation) {
      localStorage.setItem(
        "reservationToEdit",
        JSON.stringify(reservation)
      );
      this.$router.push("/publish-trip");
    },

    showMessage(text, type) {
      this.message = text;
      this.messageType = type;
      setTimeout(() => {
        this.message = "";
        this.messageType = "";
      }, 5000);
    },
  },
};
</script>

<template>
  <div class="min-h-screen bg-white p-6 font-sans">
  
      <div class="max-w-7xl mx-auto">
        <!-- En-tête -->
        <div class=" text-white p-12 ">
          <h1 class="text-3xl font-bold text-indigo-600 mb-4">Mes Réservations</h1>
          
        </div>
  
        <!-- État de chargement -->
        <div v-if="loading" class="flex justify-center items-center py-16">
          <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-indigo-600"></div>
        </div>
  
        <!-- Aucune réservation -->
        <div
          v-else-if="reservations.length === 0"
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
            Aucune réservation active
          </h3>
          <p class="text-gray-500 max-w-md mx-auto mb-6">
            Vous n'avez actuellement aucun trajet réservé. Réservez un trajet pour commencer !
          </p>
          <router-link
            to="/"
            class="p-button p-button-primary p-button-raised inline-flex items-center px-4 py-2 sm:px-6 sm:py-3 rounded-lg bg-primary-500 text-white hover:bg-primary-600 dark:bg-primary-600 dark:hover:bg-primary-700 transition-all duration-200 hover:scale-105"
            aria-label="Trouver un trajet"
          >
            Trouver un trajet
          </router-link>
        </div>
  
        <!-- Liste des réservations -->
        <div v-else class="space-y-8">
          <!-- Message de notification -->
          <div
            v-if="message"
            :class="messageClass"
            class="p-5 rounded-xl shadow-md flex items-start animate-slide-in"
          >
            <svg
              v-if="messageType === 'success'"
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
            <span class="flex-1">{{ message }}</span>
            <button
              @click="message = ''"
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
  
          <!-- Carte de réservation -->
          <div
            v-for="reservation in reservations"
            :key="reservation.trip_id"
            class="bg-white rounded-2xl shadow-md overflow-hidden transition-all duration-300 hover:shadow-lg animate-fade-in border border-gray-300 rounded-lg"
          >
            <div class="p-8">
              <div
                class="flex flex-col md:flex-row md:justify-between md:items-start gap-8"
              >
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
                      {{ reservation.departure }}</span
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
                      {{ reservation.destination }}</span
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
                      {{ formatDate(reservation.trip_date) }}</span
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
                      {{ reservation.departure_time }}</span
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
                      {{ reservation.price }} TND</span
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
                      ><span class="font-semibold">Passager:</span>
                      {{ reservation.passenger_name }}</span
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
                      ><span class="font-semibold">Conducteur:</span>
                      {{ reservation.driver_name }}</span
                    >
                  </div>
                </div>
              </div>
  
              <!-- Boutons d'actions -->
              <div class="mt-8 flex justify-end space-x-4">
               
                <button
                  @click="confirmCancel(reservation.trip_id, currentUser.id)"
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
                  Annuler la Reservation
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
 
  
  <style>
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
  
  /* Ensure Inter or Poppins font is loaded */
  @import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap");
  
  body {
    font-family: "Inter", sans-serif;
  }
  </style>