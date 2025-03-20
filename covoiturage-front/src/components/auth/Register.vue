<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
      <h2 class="text-2xl font-bold text-center text-gray-700 mb-6">Sign Up</h2>

      <form @submit.prevent="submitRegister" class="space-y-4">
        <label class="block">
          <span class="text-gray-700">Select Role</span>
          <select v-model="role_id" class="w-full p-2 border rounded-md">
            <option value="" disabled>Select a role</option>
            <option value="2">Driver</option>
            <option value="3">Passenger</option>
          </select>
        </label>

        <label class="block">
          <span class="text-gray-700">Your Name</span>
          <input v-model="name" type="text" class="w-full p-2 border rounded-md" required />
        </label>

        <label class="block">
          <span class="text-gray-700">Your Email</span>
          <input v-model="email" type="email" class="w-full p-2 border rounded-md" required />
        </label>

        <label class="block">
          <span class="text-gray-700">Date of Birth</span>
          <input v-model="birthday" type="date" class="w-full p-2 border rounded-md" required @change="validateAge" />
          <span v-if="ageError" class="text-red-500 text-sm">{{ ageError }}</span>
        </label>

        <label class="block">
          <span class="text-gray-700">Phone Number</span>
          <input v-model="phone_number" type="text" class="w-full p-2 border rounded-md" required />
        </label>

        <label v-if="role_id == 2" class="block">
          <span class="text-gray-700">Vehicle Number</span>
          <input v-model="num_vehicule" type="text" class="w-full p-2 border rounded-md" />
        </label>

        <label v-if="role_id == 2" class="block">
          <span class="text-gray-700">License Number</span>
          <input v-model="matricule_permis" type="text" class="w-full p-2 border rounded-md" />
        </label>

        <label class="block">
          <span class="text-gray-700">Password</span>
          <input v-model="password" type="password" class="w-full p-2 border rounded-md" required />
        </label>

        <label class="block">
          <span class="text-gray-700">Confirm Password</span>
          <input v-model="password_confirmation" type="password" class="w-full p-2 border rounded-md" required />
        </label>

        <p v-if="errorMessage" class="text-red-500 text-center">{{ errorMessage }}</p>

        <button
          type="submit"
          class="w-full bg-blue-500 text-white py-2 rounded-md hover:bg-blue-600 transition"
          :disabled="loading"
        >
          {{ loading ? 'Registering...' : 'REGISTER' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      name: '',
      email: '',
      phone_number: '',
      birthday: '',
      role_id: '',
      num_vehicule: '',
      matricule_permis: '',
      password: '',
      password_confirmation: '',
      errorMessage: '',
      loading: false,
      ageError: '',
    };
  },
  methods: {
    validateAge() {
      if (!this.birthday) return;
      const birthDate = new Date(this.birthday);
      const today = new Date();
      let age = today.getFullYear() - birthDate.getFullYear();
      const monthDiff = today.getMonth() - birthDate.getMonth();

      if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
      }

      if (this.role_id == 2 && age < 18) {
        this.ageError = 'You must be at least 18 years old to register as a Driver.';
        return false;
      }
      this.ageError = '';
      return true;
    },

    async submitRegister() {
      this.loading = true;
      this.errorMessage = '';

      if (!this.validateAge()) {
        this.loading = false;
        return;
      }

      const formData = {
        name: this.name,
        email: this.email,
        phone_number: this.phone_number,
        birthday: this.birthday,
        role_id: this.role_id,
        num_vehicule: this.num_vehicule || null,
        matricule_permis: this.matricule_permis || null,
        password: this.password,
        password_confirmation: this.password_confirmation, 
      };

      console.log('Données envoyées:', formData);

      try {
        const response = await axios.post('http://localhost:8000/api/register', formData, {
          headers: { 'Content-Type': 'application/json' },
        });

        console.log('Réponse API:', response.data);

        if (response.data.token) {
          localStorage.setItem('token', response.data.token);
          localStorage.setItem('user_role', this.role_id); // Stocker le rôle
        }

        this.$router.push('/login'); // Redirection vers la connexion après l'inscription
      } catch (error) {
        console.error('Erreur API:', error.response?.data);
        this.errorMessage = error.response?.data?.message || 'Registration failed. Please try again.';
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

