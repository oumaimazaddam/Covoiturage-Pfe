<script setup>
import { ref, onMounted } from 'vue';
import { Doughnut } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, ArcElement, CategoryScale } from 'chart.js';
import axios from 'axios';

ChartJS.register(Title, Tooltip, Legend, ArcElement, CategoryScale);

const chartData = ref({
  labels: ['Très Satisfait', 'Satisfait', 'Insatisfait'],
  datasets: [
    {
      label: 'Résumé des Avis',
      data: [0, 0, 0],
      backgroundColor: ['#4CAF50', '#FF9800', '#F44336'],
      hoverBackgroundColor: ['#4CAF50', '#FF9800', '#F44336'],
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
          return `${tooltipItem.label}: ${tooltipItem.raw} avis`;
        },
      },
    },
  },
});

const isLoading = ref(true);
const errorMessage = ref(null);
const totalReviews = ref(0);

const fetchReviewsSummary = async () => {
  try {
    const token = localStorage.getItem('access_token');
    if (!token) {
      errorMessage.value = 'Veuillez vous connecter pour voir les avis.';
      isLoading.value = false;
      return;
    }

    const response = await axios.get('http://localhost:8000/api/reviews-summary', {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    });

    const { ratings, total_reviews } = response.data;

    chartData.value.datasets[0].data = [
      ratings.tres_satisfait,
      ratings.satisfait,
      ratings.insatisfait,
    ];
    totalReviews.value = total_reviews;

    if (total_reviews === 0) {
      errorMessage.value = 'Aucun avis disponible.';
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
      errorMessage.value = 'Erreur lors de la récupération des avis. Veuillez réessayer.';
    }
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  fetchReviewsSummary();
});
</script>

<template>
  <div class="col-span-12 xl:col-span-6">
    <div class="card flex flex-col items-center">
      <h3 class="text-xl font-medium mb-4">Résumé des Avis de Tous les Conducteurs</h3>
      <div v-if="isLoading" class="text-center">
        <p class="text-gray-500">Chargement des avis...</p>
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