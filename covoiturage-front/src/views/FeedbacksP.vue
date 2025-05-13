<script>
export default {
  name: 'FeedbacksP',
  props: {
    tripId: {
      type: [Number, String],
      required: true,
    },
  },
  data() {
    return {
      rating: 0,
      emoji: '',
      comment: '',
      emojis: ['😞', '😕', '😐', '😊', '😍'],
      loading: false,
      success: false,
      error: '',
    };
  },
  methods: {
    async submitFeedback() {
      // Reset messages
      this.success = false;
      this.error = '';

      // Validate rating
      if (this.rating === 0) {
        this.error = 'Veuillez sélectionner une note.';
        return;
      }

      // Validate tripId
      const tripId = parseInt(this.tripId, 10);
      if (isNaN(tripId) || tripId <= 0) {
        this.error = 'Identifiant de trajet invalide. Veuillez vérifier le trajet.';
        console.error('Invalid tripId:', this.tripId); // Debug log
        return;
      }

      this.loading = true;

      try {
        // Get token from localStorage
        const token = localStorage.getItem('access_token');
        if (!token) {
          this.error = 'Vous devez être connecté pour soumettre un avis.';
          this.loading = false;
          return;
        }

        // Prepare payload
        const payload = {
          trip_id: tripId,
          rating: parseInt(this.rating, 10),
          comment: this.comment || null,
        };
        console.log('Sending payload:', payload); // Debug log

        const response = await this.$axios.post(
          'http://localhost:8000/api/reviews',
          payload,
          {
            headers: {
              Authorization: `Bearer ${token}`,
              'Content-Type': 'application/json',
              'Accept': 'application/json',
            },
          }
        );

        if (response.status === 201) {
          this.success = true;
          this.rating = 0;
          this.emoji = '';
          this.comment = '';
        }
      } catch (error) {
        console.error('Error response:', error.response); // Debug log
        if (error.response) {
          if (error.response.status === 401) {
            this.error = 'Session invalide. Veuillez vous reconnecter.';
          } else if (error.response.status === 422) {
            this.error = error.response.data.message || 'Erreur de validation.';
            if (error.response.data.errors) {
              this.error += ' ' + Object.values(error.response.data.errors).flat().join(' ');
            }
          } else if (error.response.status === 403) {
            this.error = 'Vous n’êtes pas passager de ce trajet.';
          } else if (error.response.status === 400) {
            this.error = error.response.data.message || 'Requête invalide.';
          } else {
            this.error = 'Une erreur est survenue lors de la soumission.';
          }
        } else {
          this.error = 'Erreur de connexion au serveur.';
        }
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

<template>
  <div class="relative min-h-screen flex items-center justify-center bg-gray-50 px-4">
    <!-- Feedback card -->
    <div class="max-w-6.5xl w-full p-40 bg-white shadow-3xl rounded-3xl z-20">
      <h2 class="text-3xl font-bold mb-12 text-center text-gray-800">Laissez un avis sur votre trajet</h2>

      <!-- Note par étoiles -->
      <div class="flex justify-center mb-6">
        <button
          v-for="i in 5"
          :key="i"
          @click="rating = i"
          class="text-5xl transition-transform duration-150 hover:scale-110"
        >
          <span :class="i <= rating ? 'text-yellow-400' : 'text-gray-300'">★</span>
        </button>
      </div>

      <!-- Emoji de satisfaction -->
      <div class="flex justify-center space-x-6 mb-6 text-4xl">
        <span
          v-for="face in emojis"
          :key="face"
          @click="emoji = face"
          class="cursor-pointer transition-transform duration-150"
          :class="emoji === face ? 'scale-125' : ''"
        >
          {{ face }}
        </span>
      </div>

      <!-- Commentaire -->
      <textarea
        v-model="comment"
        rows="5"
        placeholder="Partagez votre expérience..."
        class="w-full border rounded-md p-4 text-lg focus:outline-none focus:ring focus:ring-blue-300 mb-6"
      ></textarea>

      <!-- Bouton envoyer -->
      <div class="flex justify-center">
        <button
          @click="submitFeedback"
          :disabled="loading"
          class="w-40 bg-blue-500 text-white text-base py-2 rounded-2xl hover:bg-blue-600 transition duration-200 disabled:opacity-50"
        >
          {{ loading ? 'Envoi...' : 'Envoyer' }}
        </button>
      </div>

      <!-- Message de succès -->
      <p v-if="success" class="text-green-600 mt-6 text-center font-medium text-lg">
        Merci pour votre avis ! 🌟
      </p>

      <!-- Message d'erreur -->
      <p v-if="error" class="text-red-600 mt-6 text-center font-medium text-lg">
        {{ error }}
      </p>
    </div>

    <!-- Overlay de chargement -->
    <div
      v-if="loading"
      class="absolute inset-0 bg-white bg-opacity-70 flex items-center justify-center z-50"
    >
      <svg class="animate-spin h-12 w-12 text-blue-500" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" />
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
      </svg>
    </div>
  </div>
</template>

<style scoped>
.scale-125 {
  transform: scale(1.25);
}
</style>