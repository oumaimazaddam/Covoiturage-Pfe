<template>
    <div class="flex flex-col items-center justify-center min-h-screen bg-gray-100">
      <h1 class="text-2xl font-bold text-gray-800">Vérifiez les informations passager</h1>
  
      <div class="mt-4 bg-white shadow-md p-6 rounded-lg w-full max-w-md">
        <p class="text-lg font-semibold text-gray-700">{{ userName }}</p>
  
        <hr class="my-4">
  
        <div class="flex items-center space-x-2 text-blue-600 cursor-pointer">
          <span>♿</span>
          <p>Demander une assistance voyage</p>
        </div>
  
        <button @click="continueBooking"
                class="mt-6 bg-blue-500 text-white px-6 py-2 rounded-md hover:bg-blue-600">
          Continuer
        </button>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    data() {
      return {
        userName: "farhat fatma", // Remplace par les infos dynamiques si besoin
      };
    },
    methods: {
      continueBooking() {
        alert("Aller à l'étape suivante !");
      }
    }
  };
  </script>
  