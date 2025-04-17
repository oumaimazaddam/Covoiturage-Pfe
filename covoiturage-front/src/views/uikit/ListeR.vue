<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import TabView from 'primevue/tabview'
import TabPanel from 'primevue/tabpanel'

const reservations = ref([])
const cancelledReservations = ref([])

const fetchReservations = async () => {
  try {
    const token = localStorage.getItem('access_token')

    // Récupère toutes les réservations
    const res = await axios.get('http://localhost:8000/api/reservations', {
      headers: { Authorization: `Bearer ${token}` }
    })
    reservations.value = res.data

    // Récupère les réservations annulées
    const cancelledRes = await axios.get('http://localhost:8000/api/cancelled-annuler-reservations', {
      headers: { Authorization: `Bearer ${token}` }
    })

    // Log pour inspecter la structure des données
    console.log("Structure de cancelledRes.data :", JSON.stringify(cancelledRes.data, null, 2))

    // Transformation des données
    cancelledReservations.value = Array.isArray(cancelledRes.data) && cancelledRes.data[0]?.reservations
      ? cancelledRes.data[0].reservations.map(reservation => ({
          trip_id: reservation.trip_id || 'N/A',
          departure: reservation.departure || 'N/A',
          destination: reservation.destination || 'N/A',
          trip_date: reservation.trip_date || 'N/A',
          departure_time: reservation.departure_time || 'N/A',
          price: reservation.price || 0,
          passenger_name: reservation.passenger_name || 'N/A',
          driver_name: reservation.driver_name || 'N/A',
          cancelled_at: reservation.cancelled_at || 'N/A',
          status: reservation.status || 'N/A' // Ajout du champ status
        }))
      : []

    console.log("cancelledReservations après transformation :", cancelledReservations.value)
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
    <h2 class="text-xl font-bold mb-4">Gestion des réservations</h2>

    <TabView>
      <!-- Onglet : Toutes les réservations -->
      <TabPanel header="Toutes les réservations">
        <DataTable :value="reservations" :paginator="true" :rows="10" responsiveLayout="scroll">
          <Column field="trip_id" header="ID Trajet" />
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
                :label="slotProps.data.cancelled_at ? 'Annulé' : 'Confirmé'" 
                :severity="slotProps.data.cancelled_at ? 'danger' : 'success'" 
                outlined 
              />
            </template>
          </Column>
        </DataTable>
      </TabPanel>

      <!-- Onglet : Réservations annulées -->
      <TabPanel header="Réservations annulées">
        <div v-if="cancelledReservations.length">
          <p>Nombre de réservations annulées : {{ cancelledReservations.length }}</p>
          <DataTable 
            :value="cancelledReservations" 
            :paginator="true" 
            :rows="10" 
            responsiveLayout="scroll" 
            :key="cancelledReservations.length"
          >
            <Column field="trip_id" header="ID Trajet" />
            <Column field="departure" header="Départ" />
            <Column field="destination" header="Destination" />
            <Column field="trip_date" header="Date" />
            <Column field="departure_time" header="Heure" />
            <Column field="price" header="Prix (DT)" />
            <Column field="passenger_name" header="Passager" />
            <Column field="driver_name" header="Conducteur" />
            <Column header="Date d'annulation">
              <template #body="slotProps">
                {{ slotProps.data.cancelled_at && slotProps.data.cancelled_at !== 'N/A' 
                   ? new Date(slotProps.data.cancelled_at).toLocaleString() 
                   : 'Non disponible' }}
              </template>
            </Column>
            <Column header="Statut">
              <template #body="slotProps">
                <Button 
                  :label="slotProps.data.status === 'Annulé' ? 'Annulé' : 'Confirmé'" 
                  :severity="slotProps.data.status === 'Annulé' ? 'danger' : 'success'" 
                  outlined 
                />
              </template>
            </Column>
          </DataTable>
        </div>
        <div v-else>
          Aucune réservation annulée trouvée.
        </div>
      </TabPanel>
    </TabView>
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