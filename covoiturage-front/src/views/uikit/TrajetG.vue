<script setup>
import { ref } from 'vue';
import { FilterMatchMode } from '@primevue/core/api';

// Données des trajets simulées
const trips = ref([]);
const loadingTrips = ref(true);

const sampleTrips = [
    { trip_id: 1, departure_point: 'Paris, France', arrival_point: 'Lyon, France', departure_time: '2025-03-19T08:00:00', arrival_time_estimated: '2025-03-19T12:00:00', status: 'En cours', cost: 100.5, rating: 4.5},
    { trip_id: 2, departure_point: 'Marseille, France', arrival_point: 'Nice, France', departure_time: '2025-03-19T09:00:00', arrival_time_estimated: '2025-03-19T11:30:00', status: 'Terminé', cost: 50, rating: 4.0 },
    { trip_id: 3, departure_point: 'Lille, France', arrival_point: 'Paris, France', departure_time: '2025-03-18T15:00:00', arrival_time_estimated: '2025-03-18T17:30:00', status: 'Annulé', cost: 0, rating: null},
    { trip_id: 1, departure_point: 'Paris, France', arrival_point: 'Lyon, France', departure_time: '2025-03-19T08:00:00', arrival_time_estimated: '2025-03-19T12:00:00', status: 'En cours', cost: 100.5, rating: 4.5},
    { trip_id: 2, departure_point: 'Marseille, France', arrival_point: 'Nice, France', departure_time: '2025-03-19T09:00:00', arrival_time_estimated: '2025-03-19T11:30:00', status: 'Terminé', cost: 50, rating: 4.0 },
    { trip_id: 3, departure_point: 'Lille, France', arrival_point: 'Paris, France', departure_time: '2025-03-18T15:00:00', arrival_time_estimated: '2025-03-18T17:30:00', status: 'Annulé', cost: 0, rating: null}
];

// Données des réservations simulées
const reservations = ref([]);
const loadingReservations = ref(true);

const sampleReservations = [
    { reservation_id: 1, user: 'John Doe (Passager)', departure_point: 'Paris, France', arrival_point: 'Lyon, France', desired_departure_time: '2025-03-20T09:00:00', num_passengers: 1, status: 'Confirmée', cost: 100.5 },
    { reservation_id: 2, user: 'Jane Smith (Conducteur)', departure_point: 'Marseille, France', arrival_point: 'Nice, France', desired_departure_time: '2025-03-21T14:00:00', num_passengers: 2, status: 'En attente', cost: 50 },
    { reservation_id: 3, user: 'Mike Johnson (Passager)', departure_point: 'Lille, France', arrival_point: 'Paris, France', desired_departure_time: '2025-03-22T18:00:00', num_passengers: 1, status: 'Annulée', cost: 0 },
    { reservation_id: 1, user: 'John Doe (Passager)', departure_point: 'Paris, France', arrival_point: 'Lyon, France', desired_departure_time: '2025-03-20T09:00:00', num_passengers: 1, status: 'Confirmée', cost: 100.5 },
    { reservation_id: 2, user: 'Jane Smith (Conducteur)', departure_point: 'Marseille, France', arrival_point: 'Nice, France', desired_departure_time: '2025-03-21T14:00:00', num_passengers: 2, status: 'En attente', cost: 50 },
    { reservation_id: 3, user: 'Mike Johnson (Passager)', departure_point: 'Lille, France', arrival_point: 'Paris, France', desired_departure_time: '2025-03-22T18:00:00', num_passengers: 1, status: 'Annulée', cost: 0 }
];

// Initialisation de la variable `newReservation` avec des valeurs par défaut


// Filtres pour les tables
const filtersTrips = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    status: { value: null, matchMode: FilterMatchMode.EQUALS }
});

const filtersReservations = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    user: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
    status: { value: null, matchMode: FilterMatchMode.EQUALS }
});

trips.value = sampleTrips;
reservations.value = sampleReservations;

loadingTrips.value = false;
loadingReservations.value = false;

// Formatage de la date
const formatDate = (value) => {
    return new Date(value).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// Fonction pour gérer la réservation


   
</script>

<template>
    <div class="card">
        <h2 class="font-semibold text-xl mb-4">Tableau de Bord des Trajets</h2>

        <!-- Tableau des trajets -->
        <DataTable 
            :value="trips"
            :paginator="true"
            :rows="5"
            dataKey="trip_id"
            filterDisplay="menu"
            :filters="filtersTrips"
            :globalFilterFields="['departure_point', 'arrival_point', 'status']"
            :loading="loadingTrips"
            showGridlines
        >
            <template #empty>Aucun trajet trouvé.</template>
            <template #loading>Chargement des trajets...</template>

            <Column field="trip_id" header="ID" sortable></Column>
            <Column field="departure_point" header="Point de départ" sortable></Column>
            <Column field="arrival_point" header="Point d'arrivée" sortable></Column>
            <Column field="departure_time" header="Date et heure du départ">
                <template #body="{ data }">{{ formatDate(data.departure_time) }}</template>
            </Column>
            <Column field="arrival_time_estimated" header="Date et heure d'arrivée prévue">
                <template #body="{ data }">{{ formatDate(data.arrival_time_estimated) }}</template>
            </Column>
            <Column field="status" header="Statut" sortable>
                <template #body="{ data }">
                    <Tag :value="data.status" :severity="data.status === 'En cours' ? 'warning' : (data.status === 'Terminé' ? 'success' : 'danger')" />
                </template>
            </Column>
            <Column field="cost" header="Coût du trajet" sortable></Column>
            <Column field="rating" header="Évaluation">
                <template #body="{ data }">
                    <i v-if="data.rating" class="pi pi-star" :class="{ 'text-yellow-500': data.rating >= 4 }"></i>
                    <span v-if="data.rating">{{ data.rating }}</span>
                    <span v-else>Aucune évaluation</span>
                </template>
            </Column>
        </DataTable>

        <h2 class="font-semibold text-xl mb-4 mt-6">Planification et Réservation de Trajets</h2>

        <!-- Formulaire de réservation -->
       

        <!-- Tableau des réservations -->
        <DataTable 
            :value="reservations"
            :paginator="true"
            :rows="5"
            dataKey="reservation_id"
            filterDisplay="menu"
            :filters="filtersReservations"
            :globalFilterFields="['user', 'departure_point', 'arrival_point', 'status']"
            :loading="loadingReservations"
            showGridlines
        >
            <template #empty>Aucune réservation trouvée.</template>
            <template #loading>Chargement des réservations...</template>

            <Column field="reservation_id" header="ID" sortable></Column>
            <Column field="user" header="Utilisateur" sortable></Column>
            <Column field="departure_point" header="Point de départ" sortable></Column>
            <Column field="arrival_point" header="Point d'arrivée" sortable></Column>
            <Column field="desired_departure_time" header="Date et heure souhaitées">
                <template #body="{ data }">{{ formatDate(data.desired_departure_time) }}</template>
            </Column>
            <Column field="num_passengers" header="Nombre de passagers" sortable></Column>
            <Column field="status" header="Statut" sortable>
                <template #body="{ data }">
                    <Tag :value="data.status" :severity="data.status === 'Confirmée' ? 'success' : (data.status === 'En attente' ? 'warning' : 'danger')" />
                </template>
            </Column>
            <Column field="cost" header="Coût" sortable></Column>
        </DataTable>
    </div>
</template>

<style scoped>
:deep(.p-datatable-frozen-tbody) {
    font-weight: bold;
}

:deep(.p-datatable-scrollable .p-frozen-column) {
    font-weight: bold;
}
</style>
