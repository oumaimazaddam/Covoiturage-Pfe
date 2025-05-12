import { defineStore } from 'pinia';
import { useToast } from 'vue-toastification';

export const useTripStore = defineStore('trip', {
  state: () => ({
    notifications: [],
    showNotifications: false,
    currentUserId: localStorage.getItem('user_id'),
    isListening: false, // Track if listener is active
  }),

  actions: {
    clearNotifications() {
      this.notifications = [];
      console.log('Notifications cleared');
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
      if (this.isListening) {
        console.log('Already listening for trips, skipping duplicate subscription');
        return;
      }

      const toast = useToast();
      try {
        if (!this.currentUserId) {
          console.error('No user ID found in localStorage');
          return;
        }

        console.log('Subscribing to trips-toast channel for user:', this.currentUserId);

        window.Echo.channel('trips-toast')
          .listen('.new-trip-toast', (data) => {
            console.log('New trip toast event received:', data);
            // Only add notification if the current user is not the trip creator
            if (data.user_id !== parseInt(this.currentUserId)) {
              const notification = {
                type: 'info',
                message: `Nouveau trajet disponible par ${data.trip.driver_name}: ${data.trip.departure} → ${data.trip.destination}`,
                trip: {
                  id: data.trip.id,
                  departure: data.trip.departure,
                  destination: data.trip.destination,
                  trip_date: data.trip.trip_date,
                  departure_time: data.trip.departure_time,
                  driver_name: data.trip.driver_name
                },
                timestamp: new Date()
              };
              // Check for duplicate notifications
              const isDuplicate = this.notifications.some(
                (notif) => notif.trip.id === notification.trip.id && notif.timestamp.getTime() === notification.timestamp.getTime()
              );
              if (!isDuplicate) {
                this.notifications.push(notification);
                toast.info(notification.message);
                console.log('Added notification for trip toast:', notification);
              } else {
                console.log('Duplicate notification ignored:', notification);
              }
            } else {
              console.log('Notification ignored: User is the trip creator');
            }
          })
          .listen('pusher:subscription_succeeded', () => {
            console.log('✅ Successfully subscribed to trips-toast channel');
            this.isListening = true;
          })
          .listen('pusher:subscription_error', (error) => {
            console.error('❌ Subscription failed for trips-toast channel:', error);
            this.isListening = false;
          });

      } catch (error) {
        console.error('Real-time trip listener error:', error);
        this.isListening = false;
      }
    },

    stopListening() {
      console.log('Stopping trip listeners');
      window.Echo.leave('trips-toast');
      this.isListening = false;
    }
  }
});