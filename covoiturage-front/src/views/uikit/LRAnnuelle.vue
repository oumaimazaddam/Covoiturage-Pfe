
<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useToast } from 'primevue/usetoast'

const reservations = ref([])
const toast = useToast()
//test
// Récupère l'utilisateur connecté

const fetchReservations = async () => {
  try {
    const token = localStorage.getItem('access_token')
    const response = await axios.get('http://localhost:8000/api/reservations', {
      headers: {
        Authorization: `Bearer ${token}`
      }
    })
    reservations.value = response.data
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de charger les réservations', life: 3000 })
  }
}

onMounted(() => {
  fetchReservations()
})
</script>
<template>
  <div class="p-4">
    <h2 class="text-xl font-semibold mb-4">Liste de Réservations Annuelles</h2>

    <DataTable :value="reservations" :paginator="true" :rows="10" :responsiveLayout="'scroll'" class="p-datatable-sm">
      <Column field="trip_id" header="ID Trajet" />
      <Column field="departure" header="Départ" />
      <Column field="destination" header="Destination" />
      <Column field="trip_date" header="Date" />
      <Column field="departure_time" header="Heure" />
      <Column field="price" header="Prix (TND)" />
      <Column field="driver_name" header="Conducteur" />
      <Column field="passenger_name" header="Passager" />
    </DataTable>

    <Toast />
  </div>
</template>



<style scoped>
h2 {
  color: #333;
}
</style>
