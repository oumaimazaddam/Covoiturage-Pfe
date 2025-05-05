
import { defineStore } from 'pinia';
import { useToast } from 'vue-toastification';

export const useTripStore = defineStore('trip', {
  state: () => ({
    notifications: [],
    showNotifications: false,
    currentUserId: localStorage.getItem('user_id'),
  }),

  actions: {
    clearNotifications() {
      this.notifications = [];
    },

    toggleNotifications() {
      this.showNotifications = !this.showNotifications;
      console.log('tripStore.toggleNotifications, showNotifications:', this.showNotifications);
    },

    resetNotifications() {
      this.notifications = [];
      this.showNotifications = false;
      console.log('Notifications reset');
    },

    listenForNewTrips() {
      const toast = useToast();
      try {
        if (!this.currentUserId) {
          console.error('No user ID found in localStorage');
          return;
        }

        window.Echo.channel('trips')
          .listen('.trip.published', (data) => {
            console.log('New trip published event:', data);
            if (data.trip.driver_id === parseInt(this.currentUserId)) {
              const notification = {
                type: 'info',
                trip: {
                  id: data.trip.id,
                  departure: data.trip.departure,
                  destination: data.trip.destination,
                  trip_date: data.trip.trip_date,
                  departure_time: data.trip.departure_time
                },
                driver_name: data.trip.driver_name || 'Unknown Driver',
                timestamp: new Date()
              };
              this.notifications.push(notification);
              toast.info(`Nouveau trajet publié: ${notification.trip.departure} à ${notification.trip.destination}`);
              console.log('Added notification for user-published trip:', notification);
            }
          })
          .listen('pusher:subscription_succeeded', () => {
            console.log('✅ Successfully subscribed to trips channel');
          })
          .listen('pusher:subscription_error', (error) => {
            console.error('❌ Subscription failed:', error);
          });
      } catch (error) {
        console.error('Real-time trip listener error:', error);
      }
    }
  }
});
