<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

// PrimeVue
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'

const reservations = ref([])

const fetchReservations = async () => {
  try {
    const token = localStorage.getItem('access_token') // ou sessionStorage ou pinia/vuex, selon où tu le stockes
    const res = await axios.get('http://localhost:8000/api/reservations', {
      headers: {
        Authorization: `Bearer ${token}`
      }
    })
    reservations.value = res.data
  } catch (error) {
    console.error("Erreur lors du chargement des réservations :", error)
  }
}


onMounted(() => {
  fetchReservations()
})
</script>

<template>
  <div class="p-4">
    <h2 class="text-xl font-bold mb-4">Liste des réservations</h2>
    
    <DataTable :value="reservations" :paginator="true" :rows="10" responsiveLayout="scroll">
      <Column field="trip_id" header="ID Trajet" />
      <Column field="departure" header="Départ" />
      <Column field="destination" header="Destination" />
      <Column field="trip_date" header="Date" />
      <Column field="departure_time" header="Heure" />
      <Column field="price" header="Prix (DT)" />
      <Column field="passenger_name" header="Passager" />
      <Column field="driver_name" header="Conducteur" />
    </DataTable>
  </div>
</template>


<style scoped>
h2 {
  color: #333;
}
</style>
