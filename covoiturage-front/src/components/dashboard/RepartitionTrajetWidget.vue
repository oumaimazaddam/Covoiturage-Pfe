<script setup>
import { ref } from 'vue';
import { Doughnut } from 'vue-chartjs'; // Import du composant Doughnut de vue-chartjs
import { Chart as ChartJS, Title, Tooltip, Legend, ArcElement, CategoryScale } from 'chart.js';

// Enregistrer les composants Chart.js nécessaires
ChartJS.register(Title, Tooltip, Legend, ArcElement, CategoryScale);

// Données de répartition des trajets par ville
const chartData = ref({
  labels: ["Monastir", "Tunis", "Sousse"], // Villes
  datasets: [
    {
      label: "Répartition des Trajets par Ville",
      data: [40, 35, 25], // Nombre de trajets par ville
      backgroundColor: [
        "#36A2EB", // Couleur pour Monastir
        "#FFCE56", // Couleur pour Tunis
        "#FF9F40", // Couleur pour Sousse
      ],
      hoverBackgroundColor: [
        "#36A2EB", "#FFCE56", "#FF9F40",
      ],
    }
  ]
});

const chartOptions = ref({
  responsive: true,
  maintainAspectRatio: false, // Permet de redimensionner
  plugins: {
    legend: {
      position: "top", // Position de la légende
    },
    tooltip: {
      callbacks: {
        label: function (tooltipItem) {
          return tooltipItem.label + ': ' + tooltipItem.raw + ' trajets'; // Personnalisation du tooltip
        }
      }
    }
  }
});
</script>

<template>
  <div class="col-span-12 xl:col-span-6">
    <div class="card flex flex-col items-center">
      <h3 class="text-xl font-medium mb-4">Répartition des Trajets par Ville</h3>
      <!-- Graphique Doughnut -->
      <div class="chart-container">
        <Doughnut :data="chartData" :options="chartOptions" />
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Style pour la carte contenant le graphique */
.card {
  background-color: #fff;
  border-radius: 5px;
  padding: 20px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

/* Style spécifique pour le conteneur du graphique */
.chart-container {
  position: relative;
  width: 100%;
  height: 300px; /* Ajuste la hauteur du graphique */
}

@media (min-width: 1024px) {
  .chart-container {
    height: 300px; /* Taille plus grande pour les écrans larges */
  }
}
</style>
