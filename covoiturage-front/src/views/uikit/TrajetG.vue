<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';

const router = useRouter();

const trips = ref([]);
const loadingTrips = ref(true);
const userRole = ref(null);
const isAdmin = ref(false);
const activeTab = ref('active');

const activeTrips = computed(() => {
    return trips.value.filter(trip => trip.status === 'active');
});
const completedTrips = computed(() => {
    return trips.value.filter(trip => trip.status === 'completed');
});

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
        <h2 class="font-semibold text-2xl mb-4">Gestion des Trajets</h2>
        <div class="flex gap-4 mb-6">
    <button 
        @click="activeTab = 'active'" 
        :class="[
            'py-2 px-4 rounded-lg font-medium transition-all duration-200 ml-auto',
            activeTab === 'active' ? 'bg-primary text-white font-bold' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
        ]"
    >
        Trajets Actifs
    </button>
    <button 
        @click="activeTab = 'completed'" 
        :class="[
            'py-2 px-4 rounded-lg font-medium transition-all duration-200',
            activeTab === 'completed' ? 'bg-primary text-white font-bold' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
        ]"
    >
        Trajets Terminés
    </button>
</div>
        <DataTable 
            :value="activeTab === 'active' ? activeTrips : completedTrips" 
            :loading="loadingTrips" 
            paginator 
            :rows="5"
            class="shadow-lg rounded-lg"
        >
            <template #empty>
                {{ activeTab === 'active' ? 'Aucun trajet actif trouvé.' : 'Aucun trajet terminé trouvé.' }}
            </template>
            <template #loading>Chargement des trajets...</template>
            <Column field="departure" header="Point de départ" sortable class="text-left"></Column>
            <Column field="destination" header="Point d'arrivée" sortable class="text-left"></Column>
            <Column field="trip_date" header="Date de trajet" sortable class="text-left"></Column>
            <Column field="departure_time" header="Heure de départ" class="text-left">
                <template #body="{ data }">{{ formatTime(data.departure_time) }}</template>
            </Column>
            <Column field="estimate_arrival_time" header="Arrivée prévue" class="text-left">
                <template #body="{ data }">{{ formatTime(data.estimate_arrival_time) }}</template>
            </Column>
            <Column field="price" header="Prix" sortable class="text-left"></Column>
            <Column field="driver_name" header="Conducteur" sortable class="text-left"></Column>
            <Column field="available_seats" header="Places disponibles" sortable class="text-left"></Column>
            <Column field="instant_booking" header="Réservation instantanée" sortable class="text-left">
                <template #body="slotProps">
                    {{ slotProps.data.instant_booking === 0 ? 'Trajet en attente' : 'Trajet confirmé' }}
                </template>
            </Column>
            <Column field="status" header="Statut" sortable class="text-left">
                <template #body="{ data }">
                    <Button
                        :label="formatStatus(data.status)"
                        :class="{
                            'bg-green-500 text-white font-bold py-1 px-3 rounded-lg hover:bg-green-600 transition': data.status === 'completed',
                            'bg-primary text-white font-bold py-1 px-3 rounded-lg hover:bg-primary-dark transition': data.status === 'active',
                            'bg-red-500 text-white font-bold py-1 px-3 rounded-lg hover:bg-red-600 transition': data.status === 'canceled'
                        }"
                        :severity="data.status === 'completed' ? 'success' : data.status === 'active' ? 'info' : 'danger'"
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