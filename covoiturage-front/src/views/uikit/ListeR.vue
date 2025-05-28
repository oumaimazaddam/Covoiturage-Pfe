<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';

const reservations = ref([]);
const activeTab = ref('active'); // New ref to track the active tab ('active' or 'completed')

// Computed properties to split reservations
const activeReservations = computed(() => {
  return reservations.value.filter(reservation => reservation.status === 'active');
});
const completedReservations = computed(() => {
  return reservations.value.filter(reservation => reservation.status === 'completed');
});

const fetchReservations = async () => {
  try {
    const token = localStorage.getItem('access_token');
    const res = await axios.get('http://localhost:8000/api/reservations', {
      headers: { Authorization: `Bearer ${token}` }
    });
    reservations.value = res.data;
    console.log("Reservations:", reservations.value);
    console.log("Active Reservations:", activeReservations.value);
    console.log("Completed Reservations:", completedReservations.value);
  } catch (error) {
    console.error("Erreur lors du chargement des réservations :", error.response?.data || error.message);
  }
};

onMounted(() => {
  fetchReservations();
});
</script>

<template>
  <div class="card">
    <h2 class="font-semibold text-2xl mb-4">Gestion des Réservations</h2>
   <div class="flex gap-4 mb-6 justify-end">
  <button 
    @click="activeTab = 'active'" 
    :class="[
      'py-2 px-4 rounded-lg font-medium transition-all duration-200',
      activeTab === 'active' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
    ]"
  >
    Réservations Actives
  </button>
  <button 
    @click="activeTab = 'completed'" 
    :class="[
      'py-2 px-4 rounded-lg font-medium transition-all duration-200',
      activeTab === 'completed' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
    ]"
  >
    Réservations Terminées
  </button>
</div>

    <DataTable 
      :value="activeTab === 'active' ? activeReservations : completedReservations" 
      :paginator="true" 
      :rows="10" 
      responsiveLayout="scroll"
      class="shadow-lg rounded-lg"
    >
      <template #empty>
        {{ activeTab === 'active' ? 'Aucune réservation active trouvée.' : 'Aucune réservation terminée trouvée.' }}
      </template>
      <template #loading>Chargement des réservations...</template>
      <Column field="departure" header="Départ" sortable class="text-left" />
      <Column field="destination" header="Destination" sortable class="text-left" />
      <Column field="trip_date" header="Date" sortable class="text-left" />
      <Column field="departure_time" header="Heure" class="text-left" />
      <Column field="price" header="Prix (DT)" sortable class="text-left" />
      <Column field="passenger_name" header="Passager" sortable class="text-left" />
      <Column field="driver_name" header="Conducteur" sortable class="text-left" />
      <Column header="Statut" class="text-left">
        <template #body="slotProps">
          <Button
            :label="slotProps.data.status === 'canceled' ? 'Annulé' : slotProps.data.status === 'completed' ? 'Terminé' : 'Actif'"
            :class="{
              'bg-primary text-white font-bold py-2 px-4 rounded-lg hover:bg-primary-dark transition': slotProps.data.status === 'completed',
              'bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg hover:bg-gray-300 transition': slotProps.data.status === 'active',
              'bg-red-500 text-white font-bold py-2 px-4 rounded-lg hover:bg-red-600 transition': slotProps.data.status === 'canceled'
            }"
            :severity="slotProps.data.status === 'canceled' ? 'danger' : slotProps.data.status === 'completed' ? 'success' : 'info'"
          />
        </template>
      </Column>
    </DataTable>
  </div>
</template>

<style scoped>
.card {
  padding: 1.5rem;
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  margin: 1rem;
}

:deep(.p-datatable) {
  border-radius: 8px;
  overflow: hidden;
}

:deep(.p-datatable-header) {
  background: #f8fafc;
  padding: 1rem;
  font-weight: 600;
  color: #1f2937;
}

:deep(.p-datatable-thead > tr > th) {
  background: #f1f5f9;
  padding: 1rem;
  font-weight: 500;
  color: #374151;
  border-bottom: 2px solid #e5e7eb;
}

:deep(.p-datatable-tbody > tr) {
  transition: background-color 0.2s;
}

:deep(.p-datatable-tbody > tr:hover) {
  background: #f9fafb;
}

:deep(.p-datatable-tbody > tr > td) {
  padding: 1rem;
  border-bottom: 1px solid #e5e7eb;
}

:deep(.p-paginator) {
  background: #f8fafc;
  padding: 0.5rem;
  border-top: 1px solid #e5e7eb;
}

:deep(.p-paginator .p-paginator-current) {
  color: #374151;
}


</style>