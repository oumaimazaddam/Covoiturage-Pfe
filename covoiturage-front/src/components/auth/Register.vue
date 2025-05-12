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
      phoneError: ''
    };
  },
  methods: {
    validatePhoneNumber() {
      this.phone_number = this.phone_number.replace(/\D/g, '');

      if (this.phone_number.length > 8) {
        this.phone_number = this.phone_number.slice(0, 8);
      }

      if (this.phone_number.length !== 8 && this.phone_number.length > 0) {
        this.phoneError = 'Phone number must be exactly 8 digits';
        return false;
      } else {
        this.phoneError = '';
        return true;
      }
    },

    validateAge() {
      if (!this.birthday) return false;

      const birthDate = new Date(this.birthday);
      const today = new Date();
      let age = today.getFullYear() - birthDate.getFullYear();
      const monthDiff = today.getMonth() - birthDate.getMonth();

      if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
      }

      if (parseInt(this.role_id) === 2 && age < 18) {
        this.ageError = 'You must be at least 18 years old to register as a Driver.';
        return false;
      }

      if (parseInt(this.role_id) === 3 && age < 16) {
        this.ageError = 'You must be at least 16 years old to register as a Passenger.';
        return false;
      }

      this.ageError = '';
      return true;
    },

    async submitRegister() {
      this.loading = true;
      this.errorMessage = '';
      this.phoneError = '';

      if (!this.validateAge()) {
        this.loading = false;
        return;
      }

      if (!this.validatePhoneNumber()) {
        this.loading = false;
        return;
      }

      const formData = {
        name: this.name,
        email: this.email,
        phone_number: this.phone_number,
        birthday: this.birthday,
        role_id: this.role_id,
        car_id: this.num_vehicule || null,
        drivingLicence: this.matricule_permis || null,
        password: this.password,
        password_confirmation: this.password_confirmation
      };

      try {
        const response = await axios.post('http://localhost:8000/api/register', formData, {
          headers: { 'Content-Type': 'application/json' }
        });

        if (response.data.token) {
          localStorage.setItem('token', response.data.token);
          localStorage.setItem('user_role', this.role_id);
        }

        this.$router.push('/login');
      } catch (error) {
        this.errorMessage = error.response?.data?.message || 'Registration failed. Please try again.';
        console.error('Erreur API:', error.response?.data);
      } finally {
        this.loading = false;
      }
    }
  }
};
</script>

<template>
  <div> 
    <div class="bg-surface-50 dark:bg-surface-950 flex items-center justify-center min-h-screen min-w-[100vw] overflow-hidden">
      <div class="flex flex-col items-center justify-center">
        <div style="border-radius: 56px; padding: 0.3rem; background: linear-gradient(180deg, var(--primary-color) 10%, rgba(33, 150, 243, 0) 45%)">
          <div class="w-full bg-surface-0 dark:bg-surface-900 py-12 px-8 sm:px-16" style="border-radius: 53px">
            <div class="text-center mb-8">
            
              <div class="text-surface-900 dark:text-surface-0 text-3xl font-medium mb-4">Welcome to Covoiturage</div>
              <span class="text-muted-color font-medium">Create your account</span>
            </div>

            <form @submit.prevent="submitRegister" class="space-y-4">
              <div class="field">
                <label class="block text-sm font-medium text-900 mb-1">Role</label>
                <select 
                  v-model="role_id" 
                  class="w-full p-3 border-2 border-solid border-surface-300 dark:border-surface-600 rounded-md
         focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:focus:ring-primary-800
         transition-colors duration-200 bg-surface-50 dark:bg-surface-800"
                  required
                >
                  <option value="" disabled>Select your role</option>
                  <option value="2">Driver</option>
                  <option value="3">Passenger</option>
                </select>
              </div>

              <div class="field">
                <label class="block text-sm font-medium text-900 mb-1">Full Name</label>
                <input 
                  v-model="name" 
                  type="text" 
                  class="w-full p-3 border-2 border-solid border-surface-300 dark:border-surface-600 rounded-md 
         focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:focus:ring-primary-800 
         transition-colors duration-200 bg-surface-50 dark:bg-surface-800"
                  placeholder="Enter your full name"
                  required
                />
              </div>

              <div class="field">
                <label class="block text-sm font-medium text-900 mb-1">Email</label>
                <input 
                  v-model="email" 
                  type="email" 
                  class="w-full p-3 border-2 border-solid border-surface-300 dark:border-surface-600 rounded-md 
         focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:focus:ring-primary-800 
         transition-colors duration-200 bg-surface-50 dark:bg-surface-800"
                  placeholder="Enter your email"
                  required
                />
              </div>

              <div class="field">
                <label class="block text-sm font-medium text-900 mb-1">Date of Birth</label>
                <input 
                  v-model="birthday" 
                  type="date" 
                  class="w-full p-3 border-2 border-solid border-surface-300 dark:border-surface-600 rounded-md 
         focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:focus:ring-primary-800 
         transition-colors duration-200 bg-surface-50 dark:bg-surface-800"
                  @change="validateAge"
                  required
                />
                <span v-if="ageError" class="text-red-500 text-sm">{{ ageError }}</span>
              </div>

              <div class="field">
                <label class="block text-sm font-medium text-900 mb-1">Phone Number</label>
                <input 
                  v-model="phone_number" 
                  type="tel"
                  maxlength="8"
                  @input="validatePhoneNumber"
                  class="w-full p-3 border-2 border-solid border-surface-300 dark:border-surface-600 rounded-md 
         focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:focus:ring-primary-800 
         transition-colors duration-200 bg-surface-50 dark:bg-surface-800"
                  placeholder="Enter your phone number (8 digits)"
                  required
                />
                <span v-if="phoneError" class="text-red-500 text-sm">{{ phoneError }}</span>
              </div>

              <div v-if="role_id == '2'" class="field">
                <label class="block text-sm font-medium text-900 mb-1">Vehicle Number</label>
                <input 
                  v-model="num_vehicule" 
                  type="text" 
                  class="w-full p-3 border-2 border-solid border-surface-300 dark:border-surface-600 rounded-md 
         focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:focus:ring-primary-800 
         transition-colors duration-200 bg-surface-50 dark:bg-surface-800"
                  placeholder="Enter your vehicle number"
                />
              </div>

              <div v-if="role_id == '2'" class="field">
                <label class="block text-sm font-medium text-900 mb-1">License Number</label>
                <input 
                  v-model="matricule_permis" 
                  type="text" 
                  class="w-full p-3 border-2 border-solid border-surface-300 dark:border-surface-600 rounded-md 
         focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:focus:ring-primary-800 
         transition-colors duration-200 bg-surface-50 dark:bg-surface-800"
                  placeholder="Enter your license number"
                />
              </div>

              <div class="field">
                <label class="block text-sm font-medium text-900 mb-1">Password</label>
                <input 
                  v-model="password" 
                  type="password" 
                  class="w-full p-3 border-2 border-solid border-surface-300 dark:border-surface-600 rounded-md 
         focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:focus:ring-primary-800 
         transition-colors duration-200 bg-surface-50 dark:bg-surface-800"
                  placeholder="Create a password"
                  required
                />
              </div>

              <div class="field">
                <label class="block text-sm font-medium text-900 mb-1">Confirm Password</label>
                <input 
                  v-model="password_confirmation" 
                  type="password" 
                  class="w-full p-3 border-2 border-solid border-surface-300 dark:border-surface-600 rounded-md 
         focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:focus:ring-primary-800 
         transition-colors duration-200 bg-surface-50 dark:bg-surface-800"
                  placeholder="Confirm your password"
                  required
                />
              </div>

              <div class="text-center">
                <button 
                  type="submit" 
                  class="w-full py-3 mt-6 bg-primary-500 text-white font-medium text-lg rounded-md hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500" 
                  :disabled="loading"
                >
                  <span v-if="loading">Creating account...</span>
                  <span v-else>Register</span>
                </button>
              </div>

              <div v-if="errorMessage" class="text-red-500 text-center">{{ errorMessage }}</div>

              <div class="text-center text-sm mt-4">
                Already have an account? 
                <router-link to="/login" class="text-primary-500 hover:underline">Sign in</router-link>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

