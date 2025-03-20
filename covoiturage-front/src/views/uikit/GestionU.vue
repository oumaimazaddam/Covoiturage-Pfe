<script setup>
import { ref, onBeforeMount } from 'vue';
import { FilterMatchMode } from '@primevue/core/api';

// Données utilisateurs simulées
const customers1 = ref([]);
const loading1 = ref(true);

const sampleUsers = [
    { id: 1, name: 'John Doe', email: 'john.doe@gmail.com', role: 'passenger', birthday: '1990-05-15', status: 'active' },
    { id: 2, name: 'Jane Smith', email: 'jane.smith@yahoo.com', role: 'driver', birthday: '1985-07-22', status: 'inactive' },
    { id: 3, name: 'Mike Johnson', email: 'mike.johnson@gmail.com', role: 'passenger', birthday: '1993-02-10', status: 'active' },
    { id: 4, name: 'John Doe', email: 'john.doe@gmail.com', role: 'passenger', birthday: '1990-05-15', status: 'active' },
    { id: 5, name: 'Jane Smith', email: 'jane.smith@yahoo.com', role: 'driver', birthday: '1985-07-22', status: 'inactive' },
    { id: 6, name: 'Mike Johnson', email: 'mike.johnson@gmail.com', role: 'passenger', birthday: '1993-02-10', status: 'active' },
    { id: 7, name: 'John Doe', email: 'john.doe@gmail.com', role: 'passenger', birthday: '1990-05-15', status: 'active' },
    { id: 8, name: 'Jane Smith', email: 'jane.smith@yahoo.com', role: 'driver', birthday: '1985-07-22', status: 'inactive' },
    { id: 9, name: 'Mike Johnson', email: 'mike.johnson@gmail.com', role: 'passenger', birthday: '1993-02-10', status: 'active' },
];

// Vérification automatique de l'email
const checkEmailVerification = (user) => {
    user.verified = user.email.endsWith('@gmail.com');
};

// Charger les données et appliquer la vérification des emails
onBeforeMount(() => {
    customers1.value = sampleUsers.map(user => {
        checkEmailVerification(user);
        return user;
    });
    loading1.value = false;
});

// Filtres de la table
const filters1 = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    name: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
    email: { value: null, matchMode: FilterMatchMode.CONTAINS },
    role: { value: null, matchMode: FilterMatchMode.EQUALS },
    status: { value: null, matchMode: FilterMatchMode.EQUALS }
});

// Changer le statut (activer/désactiver)
const toggleStatus = (user) => {
    user.status = user.status === 'active' ? 'inactive' : 'active';
};

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
            <Button type="button" icon="pi pi-filter-slash" label="Réinitialiser" outlined @click="filters1.global.value = null" />
            <span class="p-input-icon-left">
                <i class="pi pi-search" />
                <InputText v-model="filters1.global.value" placeholder="Rechercher un utilisateur..." />
            </span>
        </div>

        <!-- Tableau des utilisateurs -->
        <DataTable 
            :value="customers1"
            :paginator="true"
            :rows="5"
            dataKey="id"
            filterDisplay="menu"
            :filters="filters1"
            :globalFilterFields="['name', 'email', 'role', 'status']"
            :loading="loading1"
            showGridlines
        >
            <template #empty>Aucun utilisateur trouvé.</template>
            <template #loading>Chargement des utilisateurs...</template>

            <Column field="name" header="Nom" sortable>
                <template #body="{ data }">{{ data.name }}</template>
            </Column>

            <Column field="email" header="Email" sortable>
                <template #body="{ data }">{{ data.email }}</template>
            </Column>

            <Column field="role" header="Rôle" sortable>
                <template #body="{ data }">
                    <Tag :value="data.role" :severity="data.role === 'driver' ? 'warning' : 'info'" />
                </template>
            </Column>

            <Column field="birthday" header="Date de naissance">
                <template #body="{ data }">{{ formatDate(data.birthday) }}</template>
            </Column>

            <Column field="status" header="Statut">
                <template #body="{ data }">
                    <Tag :value="data.status" :severity="data.status === 'active' ? 'success' : 'danger'" />
                </template>
            </Column>

            <Column field="verified" header="Vérifié">
                <template #body="{ data }">
                    <i class="pi" :class="{ 'pi-check-circle text-green-500': data.verified, 'pi-times-circle text-red-500': !data.verified }"></i>
                </template>
            </Column>

            <Column header="Actions">
                <template #body="{ data }">
                    <Button 
                        :label="data.status === 'active' ? 'Désactiver' : 'Activer'"
                        :severity="data.status === 'active' ? 'danger' : 'success'"
                        @click="toggleStatus(data)"
                    />
                </template>
            </Column>
        </DataTable>
    </div>
</template>

<style scoped lang="scss">
:deep(.p-datatable-frozen-tbody) {
    font-weight: bold;
}

:deep(.p-datatable-scrollable .p-frozen-column) {
    font-weight: bold;
}
</style>
