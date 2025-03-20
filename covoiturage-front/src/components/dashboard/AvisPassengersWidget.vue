<script setup>
import { ref } from 'vue';
import { Doughnut } from 'vue-chartjs'; // Import du composant Doughnut de vue-chartjs
import { Chart as ChartJS, Title, Tooltip, Legend, ArcElement, CategoryScale } from 'chart.js';

// Enregistrer les composants Chart.js nécessaires
ChartJS.register(Title, Tooltip, Legend, ArcElement, CategoryScale);

// Données de notation des avis des conducteurs
const chartData = ref({
  labels: ["Très Satisfait", "Satisfait", "Insatisfait"], // Types d'avis
  datasets: [
    {
      label: "Notation des Avis des Conducteurs",
      data: [50, 30, 20], // Nombre d'avis pour chaque catégorie
      backgroundColor: [
       "#4CAF50", // Couleur pour "Très Satisfait" (vert)
        "#FF9800", // Couleur pour "Satisfait" (orange)
        "#F44336", // Couleur pour "Insatisfait" (rouge)
      ],
      hoverBackgroundColor: [
        "#4CAF50", "#FF9800", "#F44336",
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
          return tooltipItem.label + ': ' + tooltipItem.raw + ' avis'; // Personnalisation du tooltip pour les avis
        }
      }
    }
  }
});
</script>

<template>
  <div class="col-span-12 xl:col-span-6">
    <div class="card flex flex-col items-center">
      <h3 class="text-xl font-medium mb-4">Notation des Avis des Conducteurs</h3> <!-- Nouveau titre -->
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
