<script>
import axios from 'axios';

export default {
  data() {
    return {
      counts: {
        total_users: 0,
        drivers: 0,
        passengers: 0,
        trips: 0,
      },
      isLoading: false,
    };
  },
  mounted() {
    this.fetchCounts();
  },
  methods: {
    async fetchCounts() {
      this.isLoading = true;
      try {
        const response = await axios.get('http://localhost:8000/api/dashboard/counts', {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('access_token')}`,
          },
        });
        this.counts = response.data;
      } catch (error) {
        console.error('Error fetching dashboard counts:', error);
        this.$toast?.add({
          severity: 'error',
          summary: 'Erreur',
          detail: 'Impossible de charger les données du tableau de bord',
          life: 3000,
        });
      } finally {
        this.isLoading = false;
      }
    },
  },
};
</script>
<template>
    <div class="col-span-12 lg:col-span-6 xl:col-span-3">
        <div class="card mb-0">
            <div class="flex justify-between mb-4">
                <div>
                    <span class="block text-muted-color font-medium mb-4">Utilsateurs</span>
                    <div class="text-surface-900 dark:text-surface-0 font-medium text-xl">{{ counts.total_users || 0 }}</div>
                </div>
                <div class="flex items-center justify-center bg-blue-100 dark:bg-blue-400/10 rounded-border" style="width: 5.5rem; height: 4.5rem">
                    <i class="pi pi-users text-blue-500 !text-xl"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-span-12 lg:col-span-6 xl:col-span-3">
        <div class="card mb-0">
            <div class="flex justify-between mb-4">
                <div>
                    <span class="block text-muted-color font-medium mb-4">conducteurs</span>
                    <div class="text-surface-900 dark:text-surface-0 font-medium text-xl">{{ counts.drivers || 0 }}</div>
                </div>
                <div class="flex items-center justify-center bg-orange-100 dark:bg-orange-400/10 rounded-border" style="width: 5.5rem; height: 4.5rem">
                    <i class="pi pi-car text-orange-500 !text-xl"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-span-12 lg:col-span-6 xl:col-span-3">
        <div class="card mb-0">
            <div class="flex justify-between mb-4">
                <div>
                    <span class="block text-muted-color font-medium mb-4">Passagers</span>
                    <div class="text-surface-900 dark:text-surface-0 font-medium text-xl">{{ counts.passengers || 0 }}</div>
                </div>
                <div class="flex items-center justify-center bg-cyan-100 dark:bg-cyan-400/10 rounded-border" style="width: 5.5rem; height: 4.5rem">
                    <i class="pi pi-id-card text-cyan-500 !text-xl"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-span-12 lg:col-span-6 xl:col-span-3">
        <div class="card mb-0">
            <div class="flex justify-between mb-4">
                <div>
                    <span class="block text-muted-color font-medium mb-4">Trajets</span>
                    <div class="text-surface-900 dark:text-surface-0 font-medium text-xl">{{ counts.trips || 0 }}</div>
                </div>
                <div class="flex items-center justify-center bg-purple-100 dark:bg-purple-400/10 rounded-border" style="width: 5.5rem; height: 4.5rem">
                    <i class="pi pi-map text-purple-500 !text-xl"></i>
                </div>
            </div>
        </div>
    </div>
</template>
