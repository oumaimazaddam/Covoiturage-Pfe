<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';

const router = useRouter();

const reviews = ref([]);
const loadingReviews = ref(true);
const userRole = ref(null);
const isAdmin = ref(false);

// Fetch user role
const fetchUserRole = async () => {
    try {
        const token = localStorage.getItem('access_token');
        if (!token) {
            throw new Error('Aucun token trouvé. Veuillez vous reconnecter.');
        }

        const response = await axios.get('http://localhost:8000/api/admins/users', {
            headers: { Authorization: `Bearer ${token}` }
        });
        userRole.value = response.data.user.role_id;
        isAdmin.value = userRole.value === 1;

        if (!isAdmin.value) {
            alert("Accès refusé : Vous n'avez pas les permissions nécessaires.");
            router.push('/');
        }
    } catch (error) {
        console.error('Erreur lors de la récupération du rôle', error);
        router.push('/');
    }
};

// Fetch all reviews
const fetchReviews = async () => {
    try {
        const token = localStorage.getItem('access_token');
        if (!token) {
            throw new Error('Aucun token trouvé. Veuillez vous reconnecter.');
        }

        const response = await axios.get('http://localhost:8000/api/all-reviews', {
            headers: { Authorization: `Bearer ${token}` }
        });
        reviews.value = response.data;
    } catch (error) {
        console.error('Erreur lors du chargement des avis:', error.response?.data?.message || error.message);
    } finally {
        loadingReviews.value = false;
    }
};

// Format review as stars
const formatReview = (review) => {
    return '★'.repeat(review) + '☆'.repeat(5 - review);
};

// On component mount
onMounted(async () => {
    await fetchUserRole();
    if (isAdmin.value) {
        fetchReviews();
    }
});
</script>

<template>
    <div class="card">
        <h2 class="font-semibold text-xl mb-4">Tous les Avis</h2>

        <DataTable :value="reviews" :loading="loadingReviews" paginator :rows="5">
            
            <Column field="departure" header="Départ" sortable></Column>
            <Column field="destination" header="Destination" sortable></Column>
            <Column field="passenger_name" header="Passager" sortable></Column>
            <Column field="driver_name" header="Conducteur" sortable></Column>
            <Column field="comment" header="Commentaire" sortable></Column>
            <Column field="rating" header="Note">
                <template #body="{ data }">
                    <span class="rating-stars">{{ formatReview(data.rating) }}</span>
                </template>
            </Column>
            <Column field="created_at" header="Date " sortable></Column>
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
.rating-stars {
    color: #FFD700; /* Jaune (gold) */
}
</style>