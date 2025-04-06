<script>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router'; // Import useRoute
import { useChatStore } from '@/stores/chatStore';
import '@/service/echo';

export default {
  name: 'Messenger',
  props: {
    tripId: {
      type: [Number, String],
      required: false, // Make optional since we're using query
      default: null,
    },
  },
  setup(props) {
    const route = useRoute(); // Access route info
    const chatStore = useChatStore();
    const newMessage = ref('');
    const userId = ref(localStorage.getItem('user_id'));

    // Use query param if prop isn’t provided
    const effectiveTripId = computed(() => props.tripId || route.query.tripId);

    console.log('Setup - effectiveTripId:', effectiveTripId.value);

    const fetchMessages = async () => {
      console.log('fetchMessages - effectiveTripId:', effectiveTripId.value);
      if (!effectiveTripId.value) {
        console.error('tripId is undefined in fetchMessages');
        return;
      }
      await chatStore.fetchMessages(effectiveTripId.value);
    };

    const sendMessage = async () => {
      console.log('sendMessage - effectiveTripId:', effectiveTripId.value, 'content:', newMessage.value);
      const tripIdNum = Number(effectiveTripId.value);
      if (!newMessage.value.trim() || !tripIdNum) {
        console.error('Cannot send message: tripId or content missing');
        return;
      }
      try {
        await chatStore.sendMessage(tripIdNum, newMessage.value);
        newMessage.value = '';
      } catch (error) {
        console.error('Failed to send message:', error);
      }
    };

    const deleteMessage = async (messageId) => {
      console.log('deleteMessage - effectiveTripId:', effectiveTripId.value);
      const tripIdNum = Number(effectiveTripId.value);
      if (!tripIdNum) {
        console.error('Cannot delete message: tripId missing');
        return;
      }
      await chatStore.deleteMessage(messageId, tripIdNum);
    };

    const isOwnMessage = (message) => {
      return message.user_id === parseInt(userId.value);
    };

    onMounted(() => {
      console.log('onMounted - effectiveTripId:', effectiveTripId.value);
      if (!effectiveTripId.value) {
        console.error('No tripId provided on mount');
        return;
      }
      if (window.Echo && chatStore.currentChannel) {
        window.Echo.leave(chatStore.currentChannel);
      }
      fetchMessages();
      chatStore.listenForMessages(effectiveTripId.value);
    });

    watch(() => chatStore.messages, (newMessages) => {
      if (newMessages.length > 0) {
        const messagesContainer = document.querySelector('.messages');
        if (messagesContainer) {
          messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
      }
    });

    return {
      messages: computed(() => chatStore.messages),
      newMessage,
      sendMessage,
      deleteMessage,
      isOwnMessage,
      userId,
     // Expose to template
    };
  },
};
</script>

<template>
  <div class="chat-container">
    <h2>Chat pour le trajet #{{ tripId }}</h2>
    <div class="chat-box">
      <div class="messages">
        <div
          v-for="message in messages"
          :key="message.id"
          :class="{
            'message-container-right': isOwnMessage(message),
            'message-container-left': !isOwnMessage(message),
          }"
        >
          <div
            class="message-bubble"
            :class="{ 'sent': isOwnMessage(message), 'received': !isOwnMessage(message) }"
          >
            <p class="message-text">{{ message.content }}</p>
            <div class="message-meta">
              <span>{{ message.user.name }}</span>
              <span v-if="isOwnMessage(message)" class="message-status">
                {{ message.seen ? '✓✓' : '✓' }}
              </span>
            </div>
          </div>
          <button
            v-if="isOwnMessage(message)"
            @click="deleteMessage(message.id)"
            class="delete-btn"
          >
            🗑
          </button>
        </div>
      </div>
      <div class="message-input">
        <input
          v-model="newMessage"
          placeholder="Écrire un message..."
          @keyup.enter="sendMessage"
        />
        <button @click="sendMessage">Envoyer</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Your existing styles remain unchanged */
.chat-container {
  width: 100%;
  max-width: 900px;
  margin: auto;
  border: 1px solid #ccc;
  border-radius: 8px;
  background: #f9f9f9;
  height: 80vh;
  display: flex;
  flex-direction: column;
}

.chat-box {
  flex: 1;
  display: flex;
  flex-direction: column;
  padding: 10px;
}

.messages {
  flex: 1;
  overflow-y: auto;
  padding: 10px;
  display: flex;
  flex-direction: column;
}

.message-container-right {
  display: flex;
  justify-content: flex-end;
  margin: 8px 0;
  align-items: flex-end;
}

.message-container-left {
  display: flex;
  justify-content: flex-start;
  margin: 8px 0;
  align-items: flex-end;
}

.message-bubble {
  max-width: 70%;
  padding: 10px 15px;
  border-radius: 18px;
  position: relative;
  word-wrap: break-word;
}

.message-bubble.sent {
  background-color: #dcf8c6;
  border-top-right-radius: 0;
  margin-left: auto;
}

.message-bubble.received {
  background-color: #ffffff;
  border-top-left-radius: 0;
  margin-right: auto;
  border: 1px solid #eee;
}

.message-text {
  margin: 0;
  padding: 0;
}

.message-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 4px;
  font-size: 12px;
  color: #666;
}

.message-status {
  color: #34b7f1;
}

.delete-btn {
  background: none;
  border: none;
  color: #ff6b6b;
  margin-left: 8px;
  cursor: pointer;
  opacity: 0;
  transition: opacity 0.2s;
}

.message-container-right:hover .delete-btn {
  opacity: 1;
}

.message-input {
  display: flex;
  gap: 8px;
  padding: 10px;
  border-top: 1px solid #eee;
}

input {
  flex: 1;
  padding: 8px 12px;
  border: 1px solid #ccc;
  border-radius: 20px;
  outline: none;
}

button {
  padding: 8px 16px;
  background: #007bff;
  color: white;
  border: none;
  border-radius: 20px;
  cursor: pointer;
}
</style>