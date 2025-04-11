<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
const router = useRouter();

const trips = ref([]);
const reservedTrips = ref([]);
const loadingTrips = ref(true);
const loadingReservedTrips = ref(true);
const userRole = ref(null); 
const isAdmin = ref(false); 


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
// Charger tous les trajets
const fetchTrips = async () => {
    try {
        const token = localStorage.getItem("access_token");
        console.log("Token JWT récupéré :", token); 

        if (!token) {
            throw new Error("Aucun token trouvé. Veuillez vous reconnecter.");
        }

        const response = await axios.get("http://localhost:8000/api/trips", {
            headers: { Authorization: `Bearer ${token}` }
        });

        console.log("Données reçues :", response.data);
        trips.value = response.data;
    } catch (error) {
        console.error("Erreur lors du chargement des trajets", error);
    } finally {
        loadingTrips.value = false;
    }
};



const fetchReservedTrips = async () => {
    try {
        const response = await axios.get('http://localhost:8000/api/reservations', {
            headers: { Authorization: `Bearer ${localStorage.getItem("access_token")}` }
        });
        reservedTrips.value = response.data;
    } catch (error) {
        console.error("Erreur lors du chargement des trajets réservés", error);
    } finally {
        loadingReservedTrips.value = false;
    }
};

onMounted(async () => {
    await fetchUserRole(); 
    if (isAdmin.value) {
        fetchTrips();
        fetchReservedTrips();
    }
});



const formatTime = (value) => {
    if (!value) return "Aucune heure";

   
    if (/^\d{2}:\d{2}$/.test(value)) {
        return value;
    }

    try {
        // Ajouter une date fictive pour éviter "Invalid Date"
        const fullDateTime = `1970-01-01T${value}`;
        return new Date(fullDateTime).toLocaleTimeString('fr-FR', {
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (error) {
        return "Heure invalide";
    }
};



</script>

<template>
    <div class="card">
        <h2 class="font-semibold text-xl mb-4">Tous les Trajets</h2>

        <DataTable :value="trips" :loading="loadingTrips" paginator :rows="5">
           
            <Column field="departure" header="Point de départ" sortable></Column>
            <Column field="destination" header="Point d'arrivée" sortable></Column>
             <Column field="trip_date" header="date de trip" sortable></Column>
            <Column field="departure_time" header="Date et heure de départ">
                <template #body="{ data }">{{ formatTime(data.departure_time) }}</template>
            </Column>
            <Column field="estimate_arrival_time" header="Arrivée prévue">
                <template #body="{ data }">{{ formatTime(data.estimate_arrival_time) }}</template>
            </Column>
             <Column field="price" header="Prix" sortable></Column>
            <Column field="driver_id" header="conducteur_name" sortable></Column>
            <Column field="available_seats" header="Place Disponible" sortable></Column>
             <Column field="instant_booking" header="Réservation_instantanee" sortable>
             <template #body="slotProps">
             {{ slotProps.data.instant_booking === 0 ? 'Trajet en attente' : 'Trajet confirmée' }}
             </template>
            </Column>



        </DataTable>
           <div class="card">
          <h2 class="font-semibold text-xl mb-4">Les Trajets Reservee</h2>
         <DataTable :value="reservedTrips" :loading="loadingReservedTrips" paginator :rows="5">
            <Column field="user_id" header="ID Utilisateur" sortable></Column>
            <Column field="date_trajet" header="Date du Trajet" sortable>
                <template #body="{ data }">{{ formatDate(data.date_trajet) }}</template>
            </Column>
            <Column field="heure_depart" header="Heure de départ">
                <template #body="{ data }">{{ formatTime(data.heure_depart) }}</template>
            </Column>
            <Column field="ville_depart" header="Ville de départ" sortable></Column>
            <Column field="heure_arrivee" header="Heure d'arrivée">
                <template #body="{ data }">{{ formatTime(data.heure_arrivee) }}</template>
            </Column>
            <Column field="ville_arrivee" header="Ville d'arrivée" sortable></Column>
            <Column field="prix_total" header="Prix Total" sortable></Column>
            <Column field="nombre_passagers" header="Nombre de Passagers" sortable></Column>

            <Column field="status" header="Statut" sortable>
                <template #body="{ data }">
                    <Tag :value="data.status" :severity="data.status === 'En cours' ? 'warning' : (data.status === 'Terminé' ? 'success' : 'danger')" />
                </template>
            </Column>
        </DataTable>
        
    </div>
     </div>
</template>
