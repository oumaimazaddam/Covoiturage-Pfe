<script setup>
import { useLayout } from '@/layout/composables/layout';
import { onMounted, ref, watch } from 'vue';
import { Chart as ChartJS, LinearScale, CategoryScale, BarElement } from 'chart.js';

ChartJS.register(LinearScale, CategoryScale, BarElement);


const { getPrimary, getSurface, isDarkTheme } = useLayout();
const lineData = ref(null);
const lineOptions = ref(null);

// Fonction pour définir les options du graphique
function setColorOptions() {
    const documentStyle = getComputedStyle(document.documentElement);
    const textColor = documentStyle.getPropertyValue('--text-color');
    const textColorSecondary = documentStyle.getPropertyValue('--text-color-secondary');
    const surfaceBorder = documentStyle.getPropertyValue('--surface-border');

    // Définir les données du graphique : Réservations et Annulations
    lineData.value = {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'], // Mois de l'année
        datasets: [
            {
                label: 'Réservations',
                data: [12, 15, 18, 15, 2, 5, 3], // Données de réservations par mois
                fill: false,
                backgroundColor: documentStyle.getPropertyValue('--p-primary-500'),
                borderColor: documentStyle.getPropertyValue('--p-primary-500'),
                tension: 0.4
            },
            {
                label: 'Annulations',
                data: [0, 5, 0, 0, 0, 10, 2], // Données d'annulations par mois
                fill: false,
                backgroundColor: documentStyle.getPropertyValue('--p-primary-200'),
                borderColor: documentStyle.getPropertyValue('--p-primary-200'),
                tension: 0.4
            }
        ]
    };

    // Définir les options du graphique (couleur des ticks et des grilles)
    lineOptions.value = {
        plugins: {
            legend: {
                labels: {
                    fontColor: textColor
                }
            }
        },
        scales: {
            x: {
                ticks: {
                    color: textColorSecondary
                },
                grid: {
                    color: surfaceBorder,
                    drawBorder: false
                }
            },
            y: {
                ticks: {
                    color: textColorSecondary
                },
                grid: {
                    color: surfaceBorder,
                    drawBorder: false
                }
            }
        }
    };
}

// Initialisation des options du graphique lors du montage du composant
onMounted(() => {
    setColorOptions();
});

// Réagir aux changements de thème ou de couleurs
watch([getPrimary, getSurface, isDarkTheme], () => {
    setColorOptions();
}, { immediate: true });
</script>

<template>
    <div class="col-span-12 xl:col-span-6">
        <div class="card">
            <div class="font-semibold text-xl mb-4">Réservations et Annulations</div>
            <!-- Affichage du graphique linéaire -->
            <Chart type="line" :data="lineData" :options="lineOptions" class="h-80" />
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
</style>
