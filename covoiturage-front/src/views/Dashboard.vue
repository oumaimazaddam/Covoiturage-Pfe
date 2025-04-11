<script setup>
import AvisPassengersWidget from '@/components/dashboard/AvisPassengersWidget.vue';
import ReservationWidget from '@/components/dashboard/ReservationWidget.vue';
import RepartitionTrajetWidget from '@/components/dashboard/RepartitionTrajetWidget.vue';

import RevenuePayementWidget from '@/components/dashboard/RevenuePayementWidget.vue';
import StatsWidget from '@/components/dashboard/StatsWidget.vue';
</script>

<template>
    <div class="grid grid-cols-12 gap-8">
        <StatsWidget />

        <div class="col-span-12 xl:col-span-6">
          
            <AvisPassengersWidget class="mb-6" />

            <RepartitionTrajetWidget />
        </div>

        <div class="col-span-12 xl:col-span-6">
            <RevenuePayementWidget />
            <ReservationWidget />
        </div>
    </div>
</template>

<style scoped>
/* Espace entre les composants avec margin */
.mb-6 {
  margin-bottom: 1.5rem; /* Tu peux ajuster la taille de l'espace */
}
</style>