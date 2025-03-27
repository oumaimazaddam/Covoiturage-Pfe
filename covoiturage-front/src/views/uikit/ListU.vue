<script setup>
import { ref, onBeforeMount } from 'vue';
import axios from 'axios';

const users = ref([]);
const loading = ref(true);

// Fonction pour récupérer les utilisateurs en attente d'activation
const fetchPendingUsers = async () => {
    try {
        const response = await axios.get('http://127.0.0.1:8000/api/admins/users', {
            headers: {
                Authorization: `Bearer ${localStorage.getItem('access_token')}`, // Authentification via JWT
            },
        });
        users.value = response.data;
        loading.value = false;
    } catch (error) {
        console.error('Erreur lors de la récupération des utilisateurs : ', error);
        loading.value = false;
    }
};

// Fonction pour activer un utilisateur
const activateUser = async (id) => {
    try {
        const response = await axios.put(`http://127.0.0.1:8000/api/admin/activate-user/${id}`, {}, {
            headers: {
                Authorization: `Bearer ${localStorage.getItem('access_token')}`, // Authentification via JWT
            },
        });
        alert(response.data.message); // Afficher un message de succès
        fetchPendingUsers(); // Rafraîchir la liste des utilisateurs
    } catch (error) {
        console.error('Erreur lors de l\'activation de l\'utilisateur : ', error);
        alert('Erreur lors de l\'activation');
    }
   
};
const getRoleName = (roleId) => {
    switch (roleId) {
        case 2: return 'Passenger';
        case 3: return 'Conducteur';
       
    }
};
// Appeler la fonction lors de la montée du composant
onBeforeMount(() => {
    fetchPendingUsers();
});
</script>

<template>
    <div class="card">
        <h2 class="font-semibold text-xl mb-4">Utilisateurs en attente d'activation</h2>

        <div v-if="loading" class="text-center">Chargement...</div>
        <div v-else>
            <DataTable :value="users" :paginator="true" :rows="5" dataKey="id" showGridlines>
                <template #empty>Aucun utilisateur en attente.</template>
                <template #loading>Chargement des utilisateurs...</template>

               
                <Column field="name" header="Nom complet" sortable></Column>
                <Column field="email" header="Email" sortable></Column>
                <Column field="phone_number" header="Téléphone" sortable></Column>
                <Column field="birthday" header="date de naissance" sortable></Column>
                <Column header="Rôle" sortable>
                    <template #body="{ data }">
                        {{ getRoleName(data.role_id) }}
                    </template>
                </Column>
                 <Column field="created_at" header="Date d’inscription" sortable>
                    <template #body="{ data }">
                        {{ new Date(data.created_at).toLocaleDateString('fr-FR', {
                            day: '2-digit',
                            month: '2-digit',
                            year: 'numeric'
                        }) }}
                    </template>
                </Column>

                <!-- Colonne pour activer l'utilisateur -->
                <Column header="Actions">
                    <template #body="{ data }">
                        <Button
                            label="Activer"
                            icon="pi pi-check"
                            @click="activateUser(data.id)"
                            :disabled="data.is_active"
                            class="p-button-success"
                        />
                    </template>
                </Column>
            </DataTable>
        </div>
    </div>
</template>
