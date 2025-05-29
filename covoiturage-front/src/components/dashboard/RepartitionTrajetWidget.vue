<script setup>
import { ref, onMounted } from 'vue';
import { Doughnut } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, ArcElement, CategoryScale } from 'chart.js';
import axios from 'axios';

// Enregistrer les composants Chart.js nécessaires
ChartJS.register(Title, Tooltip, Legend, ArcElement, CategoryScale);

// Données du graphique (initialisées vides)
const chartData = ref({
  labels: [], // Sera rempli dynamiquement avec les villes
  datasets: [
    {
      label: 'Répartition des Trajets par Ville',
      data: [], // Sera rempli dynamiquement avec les counts
      backgroundColor: [
        '#36A2EB', // Couleur pour la première ville
        '#FFCE56', // Couleur pour la deuxième ville
        '#FF9F40', // Couleur pour la troisième ville
        '#4BC0C0', // Couleur supplémentaire
        '#9966FF', // Couleur supplémentaire
        '#FF6384', // Couleur supplémentaire
      ],
      hoverBackgroundColor: [
        '#36A2EB',
        '#FFCE56',
        '#FF9F40',
        '#4BC0C0',
        '#9966FF',
        '#FF6384',
      ],
    },
  ],
});

const chartOptions = ref({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'top',
    },
    tooltip: {
      callbacks: {
        label: function (tooltipItem) {
          return `${tooltipItem.label}: ${tooltipItem.raw} trajets`;
        },
      },
    },
  },
});

const isLoading = ref(true);
const errorMessage = ref(null);
const totalTrips = ref(0);

// Fonction pour récupérer la répartition des trajets par ville
const fetchTripsByCity = async () => {
  try {
    const token = localStorage.getItem('access_token');
    const headers = token ? { Authorization: `Bearer ${token}` } : {};

    const response = await axios.get('http://localhost:8000/api/trips-by-city', { headers });
    const { cities, counts, total_trips } = response.data;

    // Mettre à jour les données du graphique
    chartData.value.labels = cities;
    chartData.value.datasets[0].data = counts;
    totalTrips.value = total_trips;

    if (total_trips === 0) {
      errorMessage.value = 'Aucun trajet disponible.';
    }
  } catch (error) {
    console.error('Erreur détaillée:', {
      message: error.message,
      status: error.response?.status,
      data: error.response?.data,
    });
    if (error.response?.status === 401) {
      errorMessage.value = 'Session expirée. Veuillez vous reconnecter.';
    } else if (error.response?.status === 403) {
      errorMessage.value = 'Accès non autorisé.';
    } else if (error.response?.status === 404) {
      errorMessage.value = 'Endpoint API introuvable.';
    } else {
      errorMessage.value = 'Erreur lors de la récupération des trajets. Veuillez réessayer.';
    }
  } finally {
    isLoading.value = false;
  }
};

// Charger les données au montage
onMounted(() => {
  fetchTripsByCity();
});
</script>

<template>
  <div class="col-span-12 xl:col-span-6">
    <div class="card flex flex-col items-center">
      <h3 class="text-xl font-medium mb-4">Répartition des Trajets par Ville</h3>
      <div v-if="isLoading" class="text-center">
        <p class="text-gray-500">Chargement des trajets...</p>
      </div>
      <div v-else-if="errorMessage" class="text-center text-red-500">
        {{ errorMessage }}
      </div>
      <div v-else class="chart-container">
       
        <Doughnut :data="chartData" :options="chartOptions" />
      </div>
    </div>
  </div>
</template>

<style scoped>
.card {
  background-color: #fff;
  border-radius: 5px;
  padding: 20px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.chart-container {
  position: relative;
  width: 100%;
  height: 300px;
}

@media (min-width: 1024px) {
  .chart-container {
    height: 300px;
  }
}
</style>