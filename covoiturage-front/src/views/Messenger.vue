<script>
import Avatar from "primevue/avatar";

export default {
  components: { Avatar },
  data() {
    return {
      activeGroup: null,
      newMessage: "",
      activeTab: "groups",
      selectedContact: null,
      groups: [
        {
          id: 1,
          name: "Messages",
          members: [
            { id: 10, name: "Alice Johnson", avatar: "https://randomuser.me/api/portraits/women/7.jpg" },
            { id: 11, name: "Mark Smith", avatar: "https://randomuser.me/api/portraits/men/8.jpg" },
          ],
          messages: [
            { id: 1, sender: "Alice Johnson", avatar: "https://randomuser.me/api/portraits/women/7.jpg", text: "Salut les devs !" },
            { id: 2, sender: "Mark Smith", avatar: "https://randomuser.me/api/portraits/men/8.jpg", text: "Hello tout le monde !" },
          ],
        },
        {
          id: 2,
          name: "Groupe",
          members: [
            { id: 10, name: "Alice Johnson", avatar: "https://randomuser.me/api/portraits/women/7.jpg" },
            { id: 12, name: "Chris Brown", avatar: "https://randomuser.me/api/portraits/men/6.jpg" },
          ],
          messages: [
            { id: 1, sender: "Alice Johnson", avatar: "https://randomuser.me/api/portraits/women/7.jpg", text: "Hey Chris, tu vas bien ?" },
            { id: 2, sender: "Chris Brown", avatar: "https://randomuser.me/api/portraits/men/6.jpg", text: "Salut Alice, tout va bien !" },
          ],
        },
      ],
      contacts: [
        { id: 21, name: "Harriet McBride", avatar: "https://randomuser.me/api/portraits/women/9.jpg", phone: "123-456-7890", email: "harriet@example.com" },
        { id: 22, name: "Danny Conner", avatar: "https://randomuser.me/api/portraits/men/10.jpg", phone: "987-654-3210", email: "danny@example.com" },
      ],
      onlineUsers: [
        { id: 31, name: "Jane West", avatar: "https://randomuser.me/api/portraits/women/11.jpg" },
        { id: 32, name: "Bryan Murray", avatar: "https://randomuser.me/api/portraits/men/12.jpg" },
      ],
      showAddMemberModal: false,
      availableContacts: [],
    };
  },
  computed: {
    sortedMessages() {
      return this.activeGroup ? this.activeGroup.messages : [];
    },
  },
  methods: {
    selectGroup(group) {
      this.activeGroup = group;
    },
    sendMessage() {
      if (this.newMessage.trim() === "") return;

      this.activeGroup.messages.push({
        id: Date.now(),
        sender: "Me",
        avatar: "https://randomuser.me/api/portraits/men/1.jpg", // Ajouter ton avatar
        text: this.newMessage,
      });

      this.newMessage = "";

      this.$nextTick(() => {
        const messageContainer = this.$refs.messageContainer;
        messageContainer.scrollTop = messageContainer.scrollHeight;
      });
    },
    adjustTextareaHeight(event) {
      const textarea = event.target;
      this.newMessageHeight = textarea.scrollHeight;
    },
    selectContact(contact) {
      this.selectedContact = contact;
    },
    makeCall(contact) {
      alert(`Appel en cours avec ${contact.name}`);
    },
    closeProfile() {
      this.selectedContact = null;
    },
    openAddMemberModal() {
      this.showAddMemberModal = true;
      this.availableContacts = this.contacts.filter(contact => !this.activeGroup.members.some(member => member.id === contact.id));
    },
    addMember(contact) {
      if (this.activeGroup && !this.activeGroup.members.some(member => member.id === contact.id)) {
        this.activeGroup.members.push(contact);
      }
      this.showAddMemberModal = false;
    },
  },
  mounted() {
    this.messageContainer = this.$refs.messageContainer;
  },
};
</script>

<template>
  <div class="flex h-screen bg-white text-black">
    <!-- Sidebar -->
    <aside class="w-80 bg-gray-100 p-4 flex flex-col">
      <div class="flex items-center gap-3 mb-4">
        <Avatar class="w-10 h-10" image="https://randomuser.me/api/portraits/men/1.jpg" shape="circle" />
        <input type="text" class="w-full p-2 bg-gray-200 rounded-md text-black placeholder-gray-500" placeholder="Rechercher..." />
      </div>

      <div class="flex justify-around border-b border-gray-300 mb-2">
        <button @click="activeTab = 'groups'" class="pb-2 text-lg" :class="activeTab === 'groups' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500'">Groupes</button>
        <button @click="activeTab = 'contacts'" class="pb-2 text-lg" :class="activeTab === 'contacts' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500'">Contacts</button>
        <button @click="activeTab = 'connections'" class="pb-2 text-lg" :class="activeTab === 'connections' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500'">Connexions</button>
      </div>

      <div v-if="activeTab === 'groups'" class="space-y-2">
        <h2 class="text-red-600 text-lg font-bold mb-2">Groupes</h2>
        <ul>
          <li v-for="group in groups" :key="group.id" @click="selectGroup(group)" class="p-3 rounded-lg flex items-center gap-3 cursor-pointer transition-all hover:bg-gray-200" :class="{ 'bg-gray-200': group.id === activeGroup?.id }">
            <i class="pi pi-users text-lg text-black"></i>
            <div class="flex-1">
              <p class="font-semibold">{{ group.name }}</p>
              <p class="text-gray-500 text-sm">{{ group.members.length }} membres</p>
            </div>
            <button @click.stop="openAddMemberModal" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition">
              Ajouter un membre
            </button>
          </li>
        </ul>
      </div>

      <div v-if="activeTab === 'contacts'" class="space-y-2">
        <h2 class="text-green-500 text-lg font-bold mb-2">Contacts</h2>
        <ul>
          <li v-for="contact in contacts" :key="contact.id" class="p-3 rounded-lg flex items-center gap-3 cursor-pointer transition-all hover:bg-gray-700" @click="selectContact(contact)">
            <Avatar class="w-8 h-8" :image="contact.avatar" shape="circle" />
            <p class="flex-1">{{ contact.name }}</p>
            <button @click.stop="makeCall(contact)" class="bg-blue-500 px-4 py-2 rounded-lg text-white ml-2 hover:bg-blue-600 transition">
              Appeler
            </button>
          </li>
        </ul>
      </div>

      <div v-if="activeTab === 'connections'" class="space-y-2">
        <h2 class="text-yellow-500 text-lg font-bold mb-2">Connexions</h2>
        <ul>
          <li v-for="user in onlineUsers" :key="user.id" class="p-3 rounded-lg flex items-center gap-3 cursor-pointer transition-all hover:bg-gray-700">
            <Avatar class="w-8 h-8" :image="user.avatar" shape="circle" />
            <p class="flex-1">{{ user.name }}</p>
            <span class="w-3 h-3 bg-green-500 rounded-full"></span>
          </li>
        </ul>
      </div>
    </aside>

    <!-- Chat Principal -->
    <main class="flex-1 flex flex-col bg-white">
      <div v-if="!activeGroup" class="flex items-center justify-center flex-1 text-gray-500">
        <i class="pi pi-comments text-gray-400 text-5xl"></i>
        <p class="ml-2">Sélectionnez un groupe pour discuter</p>
      </div>

      <div v-if="activeGroup" class="flex flex-col flex-1">
        <header class="p-4 bg-gray-200 flex items-center">
          <h2 class="text-xl font-bold flex-1">{{ activeGroup.name }}</h2>
        </header>

        <!-- Messages -->
        <div ref="messageContainer" class="flex-1 p-4 overflow-y-auto space-y-4 flex flex-col mb-16">
          <div v-for="msg in sortedMessages" :key="msg.id" class="flex items-start gap-3" :class="{ 'justify-end': msg.sender === 'Me' }">
            <Avatar v-if="msg.sender !== 'Me'" class="w-8 h-8" :image="msg.avatar" shape="circle" />
            <div class="p-3 rounded-lg max-w-xs" :class="msg.sender === 'Me' ? 'bg-blue-500 text-white' : 'bg-gray-300 text-black'">
              <p class="font-bold text-sm">{{ msg.sender }}</p>
              <p>{{ msg.text }}</p>
            </div>
          </div>
        </div>

        <!-- Footer (input message) -->
        <footer class="p-4 bg-gray-200 flex items-center gap-3 mt-auto">
          <input v-model="newMessage" type="text" class="flex-1 p-2 bg-gray-100 rounded-md text-black placeholder-gray-500" placeholder="Écrire un message..." @keyup.enter="sendMessage" />
          <button @click="sendMessage" class="bg-blue-500 px-6 py-2 rounded-lg text-white hover:bg-blue-600 transition">
            <i class="pi pi-send"></i>
          </button>
        </footer>
      </div>
    </main>

    <!-- Profil du contact -->
    <div v-if="selectedContact" class="fixed inset-0 bg-gray-500 bg-opacity-50 flex justify-center items-center">
      <div class="bg-white p-6 rounded-lg w-80">
        <Avatar class="w-20 h-20 mb-4" :image="selectedContact.avatar" shape="circle" />
        <h3 class="text-xl font-bold mb-2">{{ selectedContact.name }}</h3>
        <p class="text-gray-600">Téléphone : {{ selectedContact.phone }}</p>
        <p class="text-gray-600">Email : {{ selectedContact.email }}</p>
        <button @click="closeProfile" class="mt-4 bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition">
          Fermer
        </button>
      </div>
    </div>
  </div>
</template>
