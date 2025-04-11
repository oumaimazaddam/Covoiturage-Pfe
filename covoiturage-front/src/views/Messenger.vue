<script>
import { ref, computed, onMounted, watch, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';
import { useChatStore } from '@/stores/chatStore';
import '@/service/echo';
import { nextTick } from 'vue';
import { useToast } from 'vue-toastification';

export default {
  name: 'Messenger',
  props: {
    tripId: {
      type: [Number, String],
      required: false,
      default: null,
    },
  },
  setup(props) {
    const route = useRoute();
    const chatStore = useChatStore();
    const newMessage = ref('');
    const userId = ref(localStorage.getItem('user_id'));
    const emojis = [
      '😀', '😂', '😍', '😎', '😭', '😜', '😇', '🥺', '🤩', '🤔',
      '🚗', '🚙', '🛻', '🚌', '🚕',
      '👨‍✈️', '👩‍✈️', '🧳', '👫', '🚶‍♂️', '🚶‍♀️',
    ];

    const showEmojiPicker = ref(false);
    const showMenu = ref(false);
    const otherUserInfo = ref(null);
    const toast = useToast();
    const effectiveTripId = computed(() => props.tripId || route.query.tripId);

    // Notification-related state
    const showNotifications = ref(false);
    const notifications = ref([]); // Store all notifications

    const fetchMessages = async () => {
      if (!effectiveTripId.value) return;
      await chatStore.fetchMessages(effectiveTripId.value);
    };

    const sendMessage = async () => {
      const tripIdNum = Number(effectiveTripId.value);
      if (!newMessage.value.trim() || !tripIdNum) return;
      try {
        await chatStore.sendMessage(tripIdNum, newMessage.value);
        newMessage.value = '';
      } catch (error) {
        console.error('Failed to send message:', error);
        const errorMsg = 'Failed to send message';
        toast.error(errorMsg);
        notifications.value.push({ type: 'error', message: errorMsg, timestamp: new Date() });
      }
    };

    const deleteMessage = async (messageId) => {
      const tripIdNum = Number(effectiveTripId.value);
      if (!tripIdNum) return;
      try {
        await chatStore.deleteMessage(messageId, tripIdNum);
        const successMsg = 'Message deleted';
        toast.success(successMsg);
        notifications.value.push({ type: 'success', message: successMsg, timestamp: new Date() });
      } catch (error) {
        console.error('Failed to delete message:', error);
        const errorMsg = 'Failed to delete message';
        toast.error(errorMsg);
        notifications.value.push({ type: 'error', message: errorMsg, timestamp: new Date() });
      }
    };

    const isOwnMessage = (message) => {
      return message.user_id === parseInt(userId.value);
    };

    const showUserInfo = () => {
      const otherUser = chatStore.messages.find((msg) => msg.user_id !== parseInt(userId.value))?.user;
      if (otherUser) {
        otherUserInfo.value = otherUser;
      } else {
        otherUserInfo.value = { name: 'Utilisateur inconnu' };
      }
    };

    const closeUserInfo = () => {
      otherUserInfo.value = null;
    };

    const toggleMenu = () => {
      showMenu.value = !showMenu.value;
    };

    const toggleNotifications = () => {
      showNotifications.value = !showNotifications.value;
    };

    const handleClickOutside = (event) => {
      const menu = document.querySelector('.dropdown-menu');
      const button = document.querySelector('.info-btn');
      const notificationMenu = document.querySelector('.notification-dropdown');
      const notificationBtn = document.querySelector('.notification-btn');
      if (menu && !menu.contains(event.target) && !button.contains(event.target)) {
        showMenu.value = false;
      }
      if (
        notificationMenu &&
        !notificationMenu.contains(event.target) &&
        !notificationBtn.contains(event.target)
      ) {
        showNotifications.value = false;
      }
    };

    onMounted(() => {
      if (!effectiveTripId.value) return;
      if (window.Echo && chatStore.currentChannel) {
        window.Echo.leave(chatStore.currentChannel);
      }
      fetchMessages();
      chatStore.listenForMessages(effectiveTripId.value);
      chatStore.pollMessages(effectiveTripId.value);
      document.addEventListener('click', handleClickOutside);
    });

    onUnmounted(() => {
      document.removeEventListener('click', handleClickOutside);
    });

    watch(
      () => chatStore.messages,
      async (newMessages, oldMessages) => {
        if (newMessages.length > oldMessages.length) {
          const latestMessage = newMessages[newMessages.length - 1];
          if (latestMessage.user_id !== parseInt(userId.value)) {
            const newMsg = `New message from ${latestMessage.user.name}: ${latestMessage.content}`;
            toast.info(newMsg);
            notifications.value.push({ type: 'info', message: newMsg, timestamp: new Date() });
          }
        }
        await nextTick();
        const messagesContainer = document.querySelector('.messages');
        if (messagesContainer) {
          messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
      },
      { deep: true }
    );

    return {
      messages: computed(() => chatStore.messages),
      newMessage,
      sendMessage,
      deleteMessage,
      isOwnMessage,
      userId,
      emojis,
      showEmojiPicker,
      showUserInfo,
      defaultAvatar: 'https://ui-avatars.com/api/?name=User&background=random&rounded=true',
      showMenu,
      toggleMenu,
      otherUserInfo,
      closeUserInfo,
      effectiveTripId,
      showNotifications,
      toggleNotifications,
      notifications,
    };
  },
};
</script>

<template>
  <div class="chat-container">
    <h2>Chat de trajet {{ effectiveTripId }}</h2>

    <!-- Notification Icon and Dropdown -->
    <div class="notification-wrapper">
      <button @click="toggleNotifications" class="notification-btn">
        🔔
        <span v-if="notifications.length" class="notification-badge">{{ notifications.length }}</span>
      </button>
      <div v-if="showNotifications" class="notification-dropdown">
        <h3>Notifications</h3>
        <ul v-if="notifications.length" class="notification-list">
          <li v-for="(notif, index) in notifications" :key="index" :class="notif.type">
            <span>{{ notif.message }}</span>
            <small>{{ new Date(notif.timestamp).toLocaleTimeString() }}</small>
          </li>
        </ul>
        <p v-else>Aucune notification</p>
      </div>
    </div>

    <!-- Carte infos utilisateur -->
    <div v-if="otherUserInfo" class="user-info-card">
      <button class="close-btn" @click="closeUserInfo">✖️</button>
      <h3>Informations de l'utilisateur</h3>
      <p><strong>Nom :</strong> {{ otherUserInfo.name }}</p>
      <p v-if="otherUserInfo.email"><strong>Email :</strong> {{ otherUserInfo.email }}</p>
      <p v-if="otherUserInfo.phone_number"><strong>Téléphone :</strong> {{ otherUserInfo.phone_number }}</p>
      <p v-if="otherUserInfo.birthday"><strong>Date de naissance :</strong> {{ otherUserInfo.birthday }}</p>
      <p v-if="otherUserInfo.role_id">
        <strong>Identité :</strong>
        {{ otherUserInfo.role_id === 2 ? 'Driver' : otherUserInfo.role_id === 3 ? 'Passenger' : 'Rôle inconnu' }}
      </p>
    </div>

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
          <img
            :src="`https://randomuser.me/api/portraits/men/${message.user.id % 100}.jpg`"
            alt="avatar"
            class="avatar"
          />
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
        <button
          @click="sendMessage"
          class="bg-primary text-white font-bold py-2 px-4 rounded-lg hover:bg-primary-dark transition"
        >
          Envoyer
        </button>
        <button @click="showEmojiPicker = !showEmojiPicker" class="stickers-btn">
          <span role="img" aria-label="emoji" class="emoji-icon">😊</span>
        </button>
        <div v-if="showEmojiPicker" class="emoji-picker">
          <button
            v-for="emoji in emojis"
            :key="emoji"
            @click="newMessage += emoji; showEmojiPicker = false"
          >
            {{ emoji }}
          </button>
        </div>
      <div class="relative">
          <button @click="toggleMenu" class="info-btn">⋮</button>
          <div v-if="showMenu" class="dropdown-menu">
            <button @click="showUserInfo">À propos</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.chat-container {
  width: 100%;
  max-width: 800px;
  margin: auto;
  border-radius: 15px;
  background: #f4f4f9;
  height: 90vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  position: relative; /* Added for positioning notification wrapper */
}

/* Notification Styles */
.notification-wrapper {
  position: absolute;
  top: 10px;
  right: 20px;
}

.notification-btn {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  position: relative;
  transition: color 0.3s ease;
}

.notification-btn:hover {
  color: #34b7f1;
}

.notification-badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background: #ff6b6b;
  color: white;
  border-radius: 50%;
  width: 18px;
  height: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
}

.notification-dropdown {
  position: absolute;
  top: 40px;
  right: 0;
  background: white;
  border: 1px solid #ddd;
  border-radius: 8px;
  box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
  width: 300px;
  max-height: 400px;
  overflow-y: auto;
  z-index: 1000;
  padding: 10px;
}

.notification-dropdown h3 {
  margin: 0 0 10px 0;
  font-size: 16px;
  color: #333;
}

.notification-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.notification-list li {
  padding: 8px;
  border-bottom: 1px solid #eee;
  font-size: 14px;
}

.notification-list li:last-child {
  border-bottom: none;
}

.notification-list li.info {
  color: #34b7f1;
}

.notification-list li.success {
  color: #28a745;
}

.notification-list li.error {
  color: #dc3545;
}

.notification-list small {
  display: block;
  color: #888;
  font-size: 12px;
  margin-top: 4px;
}

/* Existing Styles */
.chat-box {
  display: flex;
  flex-direction: column;
  padding: 20px;
  flex: 1;
  overflow: hidden;
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
}

.message-container-left {
  display: flex;
  justify-content: flex-start;
  margin: 8px 0;
}

.message-bubble {
  max-width: 75%;
  padding: 14px 20px;
  border-radius: 25px;
  position: relative;
  background-color: #e0f7fa;
  box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.message-bubble.sent {
  background-color: #c6d4f8;
  margin-left: auto;
}

.message-bubble.received {
  background-color: #ffffff;
  border: 1px solid #ddd;
  margin-right: auto;
}

.message-text {
  margin: 0;
  padding: 0;
}

.message-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 5px;
  font-size: 12px;
  color: #888;
}

.message-status {
  color: #34b7f1;
  font-size: 12px;
}

.delete-btn {
  background: none;
  border: none;
  color: #ff6b6b;
  margin-left: 10px;
  cursor: pointer;
  opacity: 0;
  transition: opacity 0.2s ease;
}

.message-container-right:hover .delete-btn {
  opacity: 1;
}

.message-input {
  display: flex;
  gap: 12px;
  padding: 12px;
  border-top: 1px solid #ddd;
  position: sticky;
  bottom: 0;
  background-color: white;
  z-index: 1;
}

input {
  flex: 1;
  padding: 10px 15px;
  border: 1px solid #ccc;
  border-radius: 25px;
  outline: none;
  font-size: 14px;
  transition: all 0.3s ease;
}

input:focus {
  border-color: #34b7f1;
  box-shadow: 0 0 8px rgba(52, 183, 241, 0.3);
}

.stickers-btn,
.camera-btn,
.info-btn {
  background: none;
  border: none;
  font-size: 20px;
  cursor: pointer;
}

.stickers-btn:hover,
.camera-btn:hover,
.info-btn:hover {
  color: #34b7f1;
}

.emoji-picker {
  position: absolute;
  bottom: 60px;
  background-color: white;
  box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
  border-radius: 5px;
  padding: 10px;
  display: flex;
  flex-wrap: wrap;
}

.emoji-picker button {
  font-size: 20px;
  margin: 5px;
  padding: 5px;
  cursor: pointer;
}

.dropdown-menu {
  position: absolute;
  right: 0;
  bottom: 50px;
  background-color: white;
  border: 1px solid #ddd;
  border-radius: 8px;
  box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
  z-index: 10;
  padding: 8px 0;
  min-width: 120px;
}

.dropdown-menu button {
  width: 100%;
  background: none;
  border: none;
  padding: 10px 16px;
  text-align: left;
  cursor: pointer;
  font-size: 14px;
  transition: background 0.2s ease;
}

.dropdown-menu button:hover {
  background-color: #f0f0f0;
}

.user-info-card {
  background-color: #ffffff;
  border: 1px solid #ddd;
  border-radius: 12px;
  padding: 16px;
  margin: 10px 20px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
  position: relative;
}

.user-info-card h3 {
  margin-top: 0;
  font-size: 16px;
  color: #333;
}

.user-info-card p {
  margin: 4px 0;
  font-size: 14px;
  color: #555;
}

.close-btn {
  position: absolute;
  top: 8px;
  right: 12px;
  border: none;
  background: none;
  font-size: 18px;
  cursor: pointer;
  color: #999;
}

.close-btn:hover {
  color: #333;
}

.stickers-btn .emoji-icon {
  font-size: 24px;
  transition: transform 0.3s ease;
}

.stickers-btn:hover .emoji-icon {
  transform: scale(1.2);
  color: #34b7f1;
}

.message-wrapper {
  display: flex;
  align-items: flex-end;
  margin: 8px 0;
}

.message-container-left {
  flex-direction: row;
}

.message-container-right {
  flex-direction: row-reverse;
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  margin: 0 10px;
}
</style>