<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const trips = ref([]);

// Fonction pour récupérer les trajets populaires depuis l'API
const fetchPopularTrips = async () => {
  try {
    const response = await axios.get("http://127.0.0.1:8000/api/popular-trips");
    trips.value = response.data;
  } catch (error) {
    console.error("Erreur lors du chargement des trajets populaires :", error);
  }
};

onMounted(fetchPopularTrips);
</script>

<template>
  <div class="card">
    <h3 class="text-xl font-medium mb-4">Top 10 des Trajets les Plus Populaires</h3>
    <table class="table-auto w-full">
      <thead>
        <tr class="bg-gray-100">
          <th class="px-4 py-2 text-left">id</th>
          <th class="px-4 py-2 text-left">Destination</th>
          <th class="px-4 py-2 text-left">Price</th>
          <th class="px-4 py-2 text-left">Passagers</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(trip, index) in trips" :key="trip.id">
          <td class="px-4 py-2">{{ index + 1 }}</td>
          <td class="px-4 py-2">{{ trip.destination }}</td>
          <td class="px-4 py-2">{{ trip.price }}</td>
          <td class="px-4 py-2">{{ trip.passengers_count }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<style scoped>
table {
width: 100%;
border-collapse: collapse;
} 

  th, td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #ddd;
}

tr:hover {
  background-color: #f1f1f1;
}
</style>
