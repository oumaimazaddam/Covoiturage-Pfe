<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import TabPanel from 'primevue/tabpanel'

const reservations = ref([])
const completedReservations = ref([]) // New array for completed reservations

const fetchReservations = async () => {
  try {
    const token = localStorage.getItem('access_token')

    // Fetch all reservations
    const res = await axios.get('http://localhost:8000/api/reservations', {
      headers: { Authorization: `Bearer ${token}` }
    })

    // Assign all reservations
    reservations.value = res.data

    // Filter cancelled reservations (based on cancelled_at or status)

    // Filter completed reservations (status: completed)
    completedReservations.value = res.data.filter(reservation => reservation.status === 'completed')

    console.log("Reservations:", reservations.value)
    console.log("Completed Reservations:", completedReservations.value)
  } catch (error) {
    console.error("Erreur lors du chargement des réservations :", error.response?.data || error.message)
  }
}

onMounted(() => {
  fetchReservations()
})
</script>

<template>
  <div class="p-4">
    <h2 class="text-xl font-bold mb-4">Liste  des réservations</h2>

    
      <!-- Onglet : Toutes les réservations -->
      <TabPanel header="Toutes les réservations">
        <DataTable :value="reservations" :paginator="true" :rows="10" responsiveLayout="scroll">
          <Column field="departure" header="Départ" />
          <Column field="destination" header="Destination" />
          <Column field="trip_date" header="Date" />
          <Column field="departure_time" header="Heure" />
          <Column field="price" header="Prix (DT)" />
          <Column field="passenger_name" header="Passager" />
          <Column field="driver_name" header="Conducteur" />
          <Column header="Statut">
            <template #body="slotProps">
              <Button
                :label="slotProps.data.status === 'canceled' ? 'Annulé' : slotProps.data.status === 'completed' ? 'Terminé' : 'Actif'"
                :severity="slotProps.data.status === 'canceled' ? 'danger' : slotProps.data.status === 'completed' ? 'success' : 'info'"
                outlined
              />
            </template>
          </Column>
        </DataTable>
      </TabPanel>

      
     
    
  </div>
</template>

<style scoped>
h2 {
  color: #333;
}
.p-tabview {
  margin-top: 1rem;
}
</style>