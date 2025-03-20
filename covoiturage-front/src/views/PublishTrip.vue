<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100 relative">
    <!-- Image de fond -->
    <div class="absolute inset-0 bg-cover bg-center filter blur-sm brightness-75"
         style="background-image: url('https://images.unsplash.com/photo-1560791716-49baa22780b0?q=80&w=1470&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fA%3D%3D');">
    </div>

    <!-- Overlay -->
    <div class="bg-black bg-opacity-50 absolute inset-0"></div>

    <!-- Message de connexion requise -->
    <div v-if="!isLoggedIn" class="relative bg-white p-8 rounded-3xl shadow-lg w-full max-w-md z-10 text-center">
      <h2 class="text-2xl font-bold text-red-600 mb-4">⚠️ Connexion requise</h2>
      <p class="text-gray-700 mb-6">
        Vous devez être connecté pour publier un trajet.
      </p>
      <button @click="goToLogin" class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition">
        Se connecter / S'inscrire
      </button>
    </div>

    <!-- Formulaire de publication -->
    <div v-else class="relative bg-white p-8 rounded-3xl shadow-lg w-full max-w-md z-10">
      <h2 class="text-2xl font-bold text-center text-blue-600 mb-6">🚗 Publier un trajet</h2>

      <form @submit.prevent="submitTrip" class="space-y-4">
        <div>
          <label class="block text-gray-700">Départ</label>
          <div class="flex items-center border rounded-lg p-2">
            <span class="text-blue-500 mr-2">📍</span>
            <input v-model="trip.departure" type="text" placeholder="Départ" class="w-full outline-none" required />
          </div>
        </div>

        <div>
          <label class="block text-gray-700">Destination</label>
          <div class="flex items-center border rounded-lg p-2">
            <span class="text-red-500 mr-2">🚩</span>
            <input v-model="trip.destination" type="text" placeholder="Destination" class="w-full outline-none" required />
          </div>
        </div>

        <div>
          <label class="block text-gray-700">Date du trajet</label>
          <div class="flex items-center border rounded-lg p-2">
            <span class="text-purple-500 mr-2">📅</span>
            <input v-model="trip.trip_date" type="date" class="w-full outline-none" required />
          </div>
        </div>

        <div>
          <label class="block text-gray-700">Heure de départ</label>
          <div class="flex items-center border rounded-lg p-2">
            <span class="text-teal-500 mr-2">⏰</span>
            <input v-model="trip.departure_time" type="time" class="w-full outline-none" required />
          </div>
        </div>

        <div>
          <label class="block text-gray-700">Heure d'arrivée estimée</label>
          <div class="flex items-center border rounded-lg p-2">
            <span class="text-orange-500 mr-2">🕒</span>
            <input v-model="trip.estimate_arrival_time" type="time" class="w-full outline-none" required />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-gray-700">Prix (DT)</label>
            <div class="flex items-center border rounded-lg p-2">
              <span class="text-green-500 mr-2">💰</span>
              <input v-model="trip.price" type="number" min="0" class="w-full outline-none" required />
            </div>
          </div>

          <div>
            <label class="block text-gray-700">Places disponibles</label>
            <div class="flex items-center border rounded-lg p-2">
              <span class="text-blue-500 mr-2">🪑</span>
              <input v-model="trip.available_seats" type="number" min="1" class="w-full outline-none" required />
            </div>
          </div>
        </div>

        <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition" :disabled="loading">
          {{ loading ? "Publication en cours..." : "Publier un trajet" }}
        </button>
      </form>

      <!-- Message d'erreur -->
      <div v-if="errorMessage" class="text-red-500 text-center text-sm mt-3">
        {{ errorMessage }}
      </div>

     
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { useRouter } from "vue-router"; 

const router = useRouter();
const isLoggedIn = ref(false);
const loading = ref(false);
const errorMessage = ref("");
const submittedTrip = ref(null);

onMounted(() => {
  const token = localStorage.getItem("access_token");
  isLoggedIn.value = !!token;
});

const goToLogin = () => {
  router.push("/login");
};

const trip = ref({
  departure: "",
  destination: "",
  trip_date: "", // Correction : Utilisation du bon nom de champ
  departure_time: "",
  estimate_arrival_time: "",
  price: "",
  instant_booking: true,
  available_seats: 1,
});

const submitTrip = async () => {
  errorMessage.value = "";
  loading.value = true;

  const token = localStorage.getItem("access_token");

  if (!token) {
    errorMessage.value = "Vous devez être connecté pour publier un trajet !";
    loading.value = false;
    return;
  }

  try {
    const response = await axios.post(
      "http://127.0.0.1:8000/api/create-trip",
      trip.value,
      {
        headers: { Authorization: `Bearer ${token}` },
      }
    );

    submittedTrip.value = response.data; 
    trip.value = {}; 
  } catch (error) {
    errorMessage.value = error.response?.data?.message || "Erreur lors de la publication du trajet.";
  }

  loading.value = false;
};
</script>
