<script setup>
import { ref, onBeforeMount } from 'vue';
import { FilterMatchMode } from '@primevue/core/api';

// Données utilisateurs simulées
const users = ref([]);
const loading = ref(true);

const sampleUsers = [
    { id: 1, fullName: 'John Doe', email: 'john@gmail.com', phone: '+33 6 12 34 56 78', role: 'Passager', licenseNumber: '', registeredAt: '2024-01-12',  reports: 0 },
    { id: 2, fullName: 'Jane Smith', email: 'jane@yahoo.com', phone: '+33 6 98 76 54 32', role: 'Chauffeur', licenseNumber: 'AB-123-CD', registeredAt: '2023-03-05', reports: 2 },
    { id: 3, fullName: 'Mike Johnson', email: 'mike@gmail.com', phone: '+33 7 45 67 89 10', role: 'Passager', licenseNumber: '', registeredAt: '2022-10-21', reports: 1 },
    { id: 4, fullName: 'Lucas Martin', email: 'lucas@gmail.com', phone: '+33 6 22 33 44 55', role: 'Chauffeur', licenseNumber: 'CD-456-EF', registeredAt: '2023-07-10',  reports: 0 }
];

// Filtres de la table
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    fullName: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
    role: { value: null, matchMode: FilterMatchMode.EQUALS }
});

// Charger les données simulées
onBeforeMount(() => {
    users.value = sampleUsers;
    loading.value = false;
});

// Formatage de la date
const formatDate = (value) => {
    return new Date(value).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};
</script>

<template>
    <div class="card">
        <h2 class="font-semibold text-xl mb-4">Gestion des Utilisateurs</h2>

        <!-- Barre de recherche -->
        <div class="flex justify-between mb-3">
            <Button type="button" icon="pi pi-filter-slash" label="Réinitialiser" outlined @click="filters.global.value = null" />
            <span class="p-input-icon-left">
                <i class="pi pi-search" />
                <InputText v-model="filters.global.value" placeholder="Rechercher un utilisateur..." />
            </span>
        </div>

        <!-- Tableau des utilisateurs -->
        <DataTable 
            :value="users"
            :paginator="true"
            :rows="5"
            dataKey="id"
            filterDisplay="menu"
            :filters="filters"
            :globalFilterFields="['fullName', 'email', 'phone', 'role']"
            :loading="loading"
            showGridlines
        >
            <template #empty>Aucun utilisateur trouvé.</template>
            <template #loading>Chargement des utilisateurs...</template>

            <Column field="id" header="ID" sortable></Column>

            <Column field="fullName" header="Nom complet" sortable></Column>

            <Column field="email" header="Email" sortable></Column>

            <Column field="phone" header="Téléphone" sortable></Column>

            <Column field="role" header="Rôle" sortable>
                <template #body="{ data }">
                    <Tag :value="data.role" :severity="data.role === 'Chauffeur' ? 'warning' : 'info'" />
                </template>
            </Column>

            <Column field="licenseNumber" header="Numéro de permis" sortable>
                <template #body="{ data }">
                    {{ data.role === 'Chauffeur' ? data.licenseNumber : '-' }}
                </template>
            </Column>

            <Column field="registeredAt" header="Date d’inscription" sortable>
                <template #body="{ data }">{{ formatDate(data.registeredAt) }}</template>
            </Column>

        

            <Column field="reports" header="Signalements" sortable>
                <template #body="{ data }">
                    <Tag :value="data.reports" :severity="data.reports > 0 ? 'danger' : 'success'" />
                </template>
            </Column>
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
