<script>
import { ref, computed, onMounted, watch } from 'vue';
import { useChatStore } from '@/stores/chatStore';
import axios from 'axios';

export default {
  setup() {
    const chatStore = useChatStore();
    const users = ref([]);
    const selectedUser = ref(null);
    const newMessage = ref("");
    const userId = ref(1); // Remplace par l'ID de l'utilisateur connecté (peut être récupéré depuis l'auth JWT)
    
    // Charger la liste des utilisateurs
    const fetchUsers = async () => {
      try {
        const response = await axios.get("http://127.0.0.1:8000/api/users", {
          headers: { Authorization: `Bearer ${localStorage.getItem("access_token")}` },
        });
        users.value = response.data;
      } catch (error) {
        console.error("Erreur lors du chargement des utilisateurs", error);
      }
    };

    // Charger les messages du user sélectionné
    const selectUser = async (user) => {
      selectedUser.value = user;
      await chatStore.fetchMessages(user.id);
      // Écouter les messages en temps réel après sélection d'un utilisateur
      chatStore.listenForMessages();
    };

    // Messages extraits de Pinia
    const messages = computed(() => chatStore.messages);

    // Envoyer un message
    const sendMessage = async () => {
      if (!newMessage.value.trim() || !selectedUser.value) return;
      await chatStore.sendMessage(selectedUser.value.id, newMessage.value);
      newMessage.value = "";
    };

    // Supprimer un message
    const deleteMessage = async (messageId) => {
      await chatStore.deleteMessage(messageId);
    };

    // Charger les utilisateurs à l'initialisation
    onMounted(() => {
      fetchUsers();
    });

    // Réagir à la réception d'un message
    watch(messages, (newMessages) => {
      if (newMessages.length > 0) {
        // Scroller vers le bas si de nouveaux messages sont ajoutés
        const messagesContainer = document.querySelector('.messages');
        if (messagesContainer) {
          messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
      }
    });

    return {
      users,
      selectedUser,
      messages,
      newMessage,
      sendMessage,
      deleteMessage,
      selectUser,
      userId,
    };
  },
};
</script>

<template>
  <div class="chat-container">
    <h2>Messagerie</h2>

    <!-- Liste des utilisateurs pour le chat -->
    <div class="users-list">
      <div 
        v-for="user in users" 
        :key="user.id" 
        @click="selectUser(user)"
        :class="{ active: selectedUser && selectedUser.id === user.id }"
      >
        {{ user.name }}
      </div>
    </div>

    <!-- Zone de discussion -->
    <div v-if="selectedUser" class="chat-box">
      <h3>Discussion avec {{ selectedUser.name }}</h3>

      <div class="messages">
        <div 
          v-for="message in messages" 
          :key="message.id"
          :class="{'message sent': message.from_id === userId, 'message received': message.from_id !== userId}"
        >
          <p>{{ message.body }}</p>
          <span v-if="message.seen" class="seen">✔✔</span>
          <button @click="deleteMessage(message.id)" class="delete-btn">🗑</button>
        </div>
      </div>

      <div class="message-input">
        <input v-model="newMessage" placeholder="Écrire un message..." @keyup.enter="sendMessage" />
        <button @click="sendMessage">Envoyer</button>
      </div>
    </div>
    <div v-else class="no-chat">Sélectionnez un utilisateur pour commencer la discussion.</div>
  </div>
</template>



<style scoped>
.chat-container {
  display: flex;
  width: 100%;
  max-width: 900px;
  margin: auto;
  border: 1px solid #ccc;
  border-radius: 8px;
  background: #f9f9f9;
}

.users-list {
  width: 30%;
  border-right: 1px solid #ccc;
  padding: 10px;
}

.users-list div {
  padding: 10px;
  cursor: pointer;
  border-radius: 5px;
  transition: background 0.3s;
}

.users-list div:hover,
.users-list div.active {
  background: #ddd;
}

.chat-box {
  width: 70%;
  padding: 10px;
}

.messages {
  max-height: 300px;
  overflow-y: auto;
  margin-bottom: 10px;
}

.message {
  padding: 8px;
  border-radius: 5px;
  margin: 5px 0;
  position: relative;
}

.message.sent {
  background: #dcf8c6;
  text-align: right;
}

.message.received {
  background: #fff;
  text-align: left;
}

.seen {
  font-size: 12px;
  color: #34b7f1;
}

.delete-btn {
  background: none;
  border: none;
  cursor: pointer;
  color: red;
  position: absolute;
  right: 5px;
  top: 5px;
}

.message-input {
  display: flex;
  gap: 5px;
}

input {
  flex: 1;
  padding: 5px;
  border: 1px solid #ccc;
  border-radius: 5px;
}

button {
  padding: 5px 10px;
  background: #007bff;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}
</style>
