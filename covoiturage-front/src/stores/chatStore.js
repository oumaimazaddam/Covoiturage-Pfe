import { defineStore } from 'pinia';
import axios from 'axios';

export const useChatStore = defineStore("chat", {
  state: () => ({
    messages: [],
  }),

  actions: {
    // Charger les messages existants pour l'utilisateur
    async fetchMessages(userId) {
      try {
        const response = await axios.get(`http://127.0.0.1:8000/api/messages/${userId}`, {
          headers: { Authorization: `Bearer ${localStorage.getItem("access_token")}` },
        });
        this.messages = response.data;
      } catch (error) {
        console.error("Erreur lors de la récupération des messages", error);
      }
    },

    // Envoi d'un message au serveur
    async sendMessage(toId, body) {
      try {
        const response = await axios.post(
          "http://127.0.0.1:8000/api/create-message",
          { to_id: toId, body },
          { headers: { Authorization: `Bearer ${localStorage.getItem("access_token")}` } }
        );
        
        // Ajouter le message envoyé à l'état de Pinia (réagit instantanément)
        this.messages.push(response.data.data);

        // Diffuser le message via Echo en temps réel sur le canal privé (chat.[to_id])
        window.Echo.channel(`chat.${toId}`)
          .whisper('message.sent', response.data.data); // Envoi sur le canal privé

      } catch (error) {
        console.error("Erreur lors de l'envoi du message", error);
      }
    },

    // Supprimer un message et mettre à jour l'état en conséquence
    async deleteMessage(messageId) {
      try {
        await axios.delete(`http://127.0.0.1:8000/api/delete-messages/${messageId}`, {
          headers: { Authorization: `Bearer ${localStorage.getItem("access_token")}` },
        });

        // Filtrer et retirer le message supprimé de l'état
        this.messages = this.messages.filter(msg => msg.id !== messageId);
      } catch (error) {
        console.error("Erreur lors de la suppression du message", error);
      }
    },

    // Écouter les messages en temps réel via Laravel Echo
    listenForMessages() {
        const userId = localStorage.getItem('userId');
        if (userId) {
          // Assurez-vous que l'utilisateur est connecté et que Echo est configuré
          window.Echo.channel(`chat.${userId}`)
            .listen('message.sent', (event) => {
              // Ajouter le message en temps réel à l'état
              this.messages.push(event.message);
            });
        }
    },
  },

  getters: {
    getMessages() {
      return this.messages;
    }
  },
});
