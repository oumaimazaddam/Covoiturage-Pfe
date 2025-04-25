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
      // Supprimer tous les caractères non numériques
      this.phone_number = this.phone_number.replace(/\D/g, '');

      // Limiter à 8 chiffres
      if (this.phone_number.length > 8) {
        this.phone_number = this.phone_number.slice(0, 8);
      }

      // Valider que c'est exactement 8 chiffres
      if (this.phone_number.length !== 8 && this.phone_number.length > 0) {
        this.phoneError = 'Phone number must be exactly 8 digits';
        return false;
      } else {
        this.phoneError = '';
        return true;
      }
    },

    validateAge() {
      if (!this.birthday) return true;
      const birthDate = new Date(this.birthday);
      const today = new Date();
      let age = today.getFullYear() - birthDate.getFullYear();
      const monthDiff = today.getMonth() - birthDate.getMonth();

      if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
      }

      const role = parseInt(this.role_id);

      if (role === 2 && age < 18) {
        this.ageError = 'You must be at least 18 years old to register as a Driver.';
        return false;
      }
      if (role === 3 && age < 16) {
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

      // Validation de l'âge
      if (!this.validateAge()) {
        this.loading = false;
        return;
      }

      // Validation du numéro de téléphone
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
          localStorage.setItem('user_role', this.role_id);
        }

        this.$router.push('/login');
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

<template>
  <div> 
    <FloatingConfigurator />
    <div class="bg-surface-50 dark:bg-surface-950 flex items-center justify-center min-h-screen min-w-[100vw] overflow-hidden">
      <div class="flex flex-col items-center justify-center">
        <div style="border-radius: 56px; padding: 0.3rem; background: linear-gradient(180deg, var(--primary-color) 10%, rgba(33, 150, 243, 0) 45%)">
          <div class="w-full bg-surface-0 dark:bg-surface-900 py-12 px-8 sm:px-16" style="border-radius: 53px">
            <div class="text-center mb-8">
              <svg viewBox="0 0 54 40" fill="none" xmlns="http://www.w3.org/2000/svg" class="mb-8 w-16 shrink-0 mx-auto">
                <path
                  fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M17.1637 19.2467C17.1566 19.4033 17.1529 19.561 17.1529 19.7194C17.1529 25.3503 21.7203 29.915 27.3546 29.915C32.9887 29.915 37.5561 25.3503 37.5561 19.7194C37.5561 19.5572 37.5524 19.3959 37.5449 19.2355C38.5617 19.0801 39.5759 18.9013 40.5867 18.6994L40.6926 18.6782C40.7191 19.0218 40.7326 19.369 40.7326 19.7194C40.7326 27.1036 34.743 33.0896 27.3546 33.0896C19.966 33.0896 13.9765 27.1036 13.9765 19.7194C13.9765 19.374 13.9896 19.0316 14.0154 18.6927L14.0486 18.6994C15.0837 18.9062 16.1223 19.0886 17.1637 19.2467ZM33.3284 11.4538C31.6493 10.2396 29.5855 9.52381 27.3546 9.52381C25.1195 9.52381 23.0524 10.2421 21.3717 11.4603C20.0078 11.3232 18.6475 11.1387 17.2933 10.907C19.7453 8.11308 23.3438 6.34921 27.3546 6.34921C31.36 6.34921 34.9543 8.10844 37.4061 10.896C36.0521 11.1292 34.692 11.3152 33.3284 11.4538ZM43.826 18.0518C43.881 18.6003 43.9091 19.1566 43.9091 19.7194C43.9091 28.8568 36.4973 36.2642 27.3546 36.2642C18.2117 36.2642 10.8 28.8568 10.8 19.7194C10.8 19.1615 10.8276 18.61 10.8816 18.0663L7.75383 17.4411C7.66775 18.1886 7.62354 18.9488 7.62354 19.7194C7.62354 30.6102 16.4574 39.4388 27.3546 39.4388C38.2517 39.4388 47.0855 30.6102 47.0855 19.7194C47.0855 18.9439 47.0407 18.1789 46.9536 17.4267L43.826 18.0518ZM44.2613 9.54743L40.9084 10.2176C37.9134 5.95821 32.9593 3.1746 27.3546 3.1746C21.7442 3.1746 16.7856 5.96385 13.7915 10.2305L10.4399 9.56057C13.892 3.83178 20.1756 0 27.3546 0C34.5281 0 40.8075 3.82591 44.2613 9.54743Z"
                  fill="var(--primary-color)"
                />
              </svg>
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

