import { defineStore } from 'pinia';
import axios from 'axios';
import { useToast } from 'vue-toastification';
export const useChatStore = defineStore('chat', {
  state: () => ({
    messages: [], // Array of message objects
    currentUserId: localStorage.getItem('user_id'),
    currentChannel: null,
  }),
  actions: {
    async fetchMessages(tripId) {
      try {
        if (!tripId) {
          console.error('No tripId provided for fetchMessages');
          return;
        }
        const response = await axios.get(`http://127.0.0.1:8000/api/chat/messages/${tripId}`, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('access_token')}`,
            Accept: 'application/json',
          },
        });
        // Ensure messages match the backend schema
        this.messages = response.data.map(msg => ({
          id: msg.id,
          user_id: msg.user_id,
          trip_id: msg.trip_id,
          content: msg.content,
          attachment: msg.attachment || null,
          seen: msg.seen || false,
          created_at: msg.created_at,
          updated_at: msg.updated_at,
          user: msg.user || { id: msg.user_id, name: `User ${msg.user_id}` },
        }));
      } catch (error) {
        console.error('Erreur lors de la récupération des messages', error);
        throw error;
      }
    },

    async sendMessage(tripId, content) {
      try {
        if (!tripId || !content) {
          console.error('Missing tripId or content for sendMessage');
          return;
        }
        const payload = {
          trip_id: Number(tripId), // Ensure numeric for backend
          content: content,
          attachment: null, // Nullable as per schema
        };

        const response = await axios.post(
          'http://127.0.0.1:8000/api/create-message',
          payload,
          {
            headers: {
              Authorization: `Bearer ${localStorage.getItem('access_token')}`,
              'Content-Type': 'application/json',
              Accept: 'application/json',
            },
          }
        );
        console.log('API Response:', response.data);

        const newMessage = response.data.message;
        if (newMessage && !this.messages.some((m) => m.id === newMessage.id)) {
          this.messages.push({
            id: newMessage.id,
            user_id: newMessage.user_id,
            trip_id: newMessage.trip_id,
            content: newMessage.content,
            attachment: newMessage.attachment || null,
            seen: newMessage.seen || false,
            created_at: newMessage.created_at,
            updated_at: newMessage.updated_at,
            user: newMessage.user || { id: newMessage.user_id, name: `User ${newMessage.user_id}` },
          });
        }
      } catch (error) {
        console.error('Erreur lors de l’envoi du message', error);
        if (error.response) {
          console.error('Response data:', error.response.data);
          console.error('Response status:', error.response.status);
          console.error('Response headers:', error.response.headers);
        }
        throw error;
      }
    },

    async deleteMessage(messageId, tripId) {
      try {
        if (!messageId || !tripId) {
          console.error('Missing messageId or tripId for deleteMessage');
          return;
        }
        await axios.delete(`http://127.0.0.1:8000/api/chat/messages/${messageId}`, {
          data: { trip_id: Number(tripId) }, // Ensure numeric
          headers: {
            Authorization: `Bearer ${localStorage.getItem('access_token')}`,
            'Content-Type': 'application/json',
          },
        });

        this.messages = this.messages.filter((msg) => msg.id !== messageId);
      } catch (error) {
        console.error('Erreur lors de la suppression du message', error);
        throw error;
      }
    },

    listenForMessages(tripId) {
      const toast = useToast();
      try {
        if (!this.currentUserId) {
          console.error('No user ID found in localStorage');
          return;
        }
        if (!tripId) {
          console.error('No tripId provided for listenForMessages');
          return;
        }
    
        if (this.currentChannel) {
          window.Echo.leave(this.currentChannel);
        }
    
        this.currentChannel = `trip.${tripId}.user.${this.currentUserId}`;
        console.log(`Listening on channel: ${this.currentChannel}`);
        
        window.Echo.private(this.currentChannel)
        .listen('.new-toast', (e) => {
            console.log('New toast received:', e);
            toast.success(`New message from ${e.message.user.name}: ${e.message.content}`);
        })
        window.Echo.private(this.currentChannel)
          .listen('.message.sent', (e) => {
            console.log('New message received:', e);
            const message = e.message;
            if (message && !this.messages.some((m) => m.id === message.id)) {
              // Ensure the message is added to the store
              this.messages.push({
                id: message.id,
                user_id: message.user_id,
                trip_id: message.trip_id,
                content: message.content,
                attachment: message.attachment || null,
                seen: message.seen || false,
                created_at: message.created_at,
                updated_at: message.updated_at,
                user: message.user || { id: message.user_id, name: `User ${message.user_id}` },
              });
            }
          })
          .listen('pusher:subscription_succeeded', () => {
            console.log('✅ Successfully subscribed to channel');
          })
          .listen('pusher:subscription_error', (error) => {
            console.error('❌ Subscription failed:', error);
          });
      } catch (error) {
        console.error('Real-time listener error:', error);
      }
    },   
    async pollMessages(tripId) {
      setInterval(async () => {
        if (tripId) {
          await this.fetchMessages(tripId);
        }
      }, 2000); // Poll every 2 seconds
    }
     
  },
});