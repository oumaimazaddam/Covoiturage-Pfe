<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';

const router = useRouter();

const trips = ref([]);
const loadingTrips = ref(true);
const userRole = ref(null);
const isAdmin = ref(false);

// Fetch user role
const fetchUserRole = async () => {
    try {
        const token = localStorage.getItem("access_token");
        if (!token) {
            throw new Error("Aucun token trouvé. Veuillez vous reconnecter.");
        }

        const response = await axios.get("http://localhost:8000/api/admins/users", {
            headers: { Authorization: `Bearer ${token}` }
        });
        console.log("Réponse API :", response.data);

        userRole.value = response.data.user.role_id;
        isAdmin.value = userRole.value === 1;

        if (!isAdmin.value) {
            alert("Accès refusé : Vous n'avez pas les permissions nécessaires.");
            router.push("/");
        }
    } catch (error) {
        console.error("Erreur lors de la récupération du rôle", error);
        router.push("/");
    }
};

// Fetch driver details for a specific trip
const fetchDriverName = async (tripId) => {
    try {
        const token = localStorage.getItem("access_token");
        const response = await axios.get(`http://127.0.0.1:8000/api/trips/${tripId}/driver`, {
            headers: { Authorization: `Bearer ${token}` }
        });
        console.log("Réponse API conducteur:", response.data);
        return response.data.name || "Inconnu";
    } catch (error) {
        console.warn("Conducteur non trouvé:", error.response?.data?.message || error.message);
        return "Inconnu";
    }
};

// Fetch all trips and enrich with driver names
const fetchTrips = async () => {
    try {
        const token = localStorage.getItem("access_token");
        if (!token) {
            throw new Error("Aucun token trouvé. Veuillez vous reconnecter.");
        }

        const response = await axios.get("http://localhost:8000/api/all-trips", {
            headers: { Authorization: `Bearer ${token}` }
        });
        console.log("Données reçues :", response.data);

        const enrichedTrips = await Promise.all(
            response.data.map(async (trip) => {
                const driverName = await fetchDriverName(trip.id);
                // Ensure status is 'completed' if available_seats is 0
                const status = trip.available_seats === 0 ? 'completed' : trip.status;
                return { ...trip, driver_name: driverName, status };
            })
        );

        trips.value = enrichedTrips;
    } catch (error) {
        console.error("Erreur lors du chargement des trajets:", error.response?.data?.message || error.message);
    } finally {
        loadingTrips.value = false;
    }
};

onMounted(async () => {
    await fetchUserRole();
    if (isAdmin.value) {
        fetchTrips();
    }
});

const formatTime = (value) => {
    if (!value) return "Aucune heure";
    if (/^\d{2}:\d{2}$/.test(value)) {
        return value;
    }
    try {
        const fullDateTime = `1970-01-01T${value}`;
        return new Date(fullDateTime).toLocaleTimeString('fr-FR', {
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (error) {
        return "Heure invalide";
    }
};

// Format status
const formatStatus = (status) => {
    switch (status) {
        case 'completed':
            return 'Terminé';
        case 'active':
            return 'Actif';
        case 'canceled':
            return 'Annulé';
        default:
            return 'Inconnu';
    }
};
</script>

<template>
    <div class="card">
        <h2 class="font-semibold text-xl mb-4">Tous les Trajets</h2>

        <DataTable :value="trips" :loading="loadingTrips" paginator :rows="5">
            <Column field="departure" header="Point de départ" sortable></Column>
            <Column field="destination" header="Point d'arrivée" sortable></Column>
            <Column field="trip_date" header="Date de trajet" sortable></Column>
            <Column field="departure_time" header="Heure de départ">
                <template #body="{ data }">{{ formatTime(data.departure_time) }}</template>
            </Column>
            <Column field="estimate_arrival_time" header="Arrivée prévue">
                <template #body="{ data }">{{ formatTime(data.estimate_arrival_time) }}</template>
            </Column>
            <Column field="price" header="Prix" sortable></Column>
            <Column field="driver_name" header="Conducteur" sortable></Column>
            <Column field="available_seats" header="Places disponibles" sortable></Column>
            <Column field="instant_booking" header="Réservation instantanée" sortable>
                <template #body="slotProps">
                    {{ slotProps.data.instant_booking === 0 ? 'Trajet en attente' : 'Trajet confirmé' }}
                </template>
            </Column>
          <Column field="status" header="Statut" sortable>
                <template #body="{ data }">
                    <Button
                        :label="formatStatus(data.status)"
                        :class="{
                            'bg-primary text-white font-bold py-2 px-4 rounded-lg hover:bg-primary-dark transition': data.status === 'completed',
                            'p-button-outlined p-button-info': data.status === 'active',
                            'p-button-outlined p-button-danger': data.status === 'canceled'
                        }"
                        :severity="data.status === 'completed' ? 'success' : data.status === 'active' ? 'info' : 'danger'"
                        outlined
                    />
                </template>
            </Column>
        </DataTable>
    </div>
</template>

<style scoped>
.card {
    padding: 1rem;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
</style>