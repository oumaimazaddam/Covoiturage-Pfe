<script>
import axios from "axios";
import L from "leaflet";
import "leaflet/dist/leaflet.css";
import "leaflet-routing-machine";
import "leaflet-routing-machine/dist/leaflet-routing-machine.css";
import markerIcon from "leaflet/dist/images/marker-icon.png";
import markerIcon2x from "leaflet/dist/images/marker-icon-2x.png";
import markerShadow from "leaflet/dist/images/marker-shadow.png";

// Fix Leaflet default icon
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: markerIcon2x,
  iconUrl: markerIcon,
  shadowUrl: markerShadow,
});

export default {
  data() {
    return {
      trip: null,
      driver: null,
      loading: true,
      error: null,
      map: null,
      routingControl: null,
      // Fallback coordinates for known locations
      fallbackCoords: {
        Sousse: [35.8256, 10.6411],
        Monastir: [35.7770, 10.8263],
        Tunis: [36.8065, 10.1815],
        Sfax: [34.7406, 10.7603],
        Mahdia: [35.5047, 11.0622],
        "Beb Bhar": [36.800556, 10.180000],
      },
      // Default locations for fallback
      defaultDeparture: "Sousse",
      defaultDestination: "Monastir",
    };
  },
  watch: {
    '$route.params.id': {
      immediate: true,
      handler(newId) {
        if (newId) this.fetchTripDetails();
      },
    },
  },
  created() {
    this.fetchTripDetails();
  },
  beforeUnmount() {
    this.destroyMap();
  },
  methods: {
    destroyMap() {
      if (this.routingControl) {
        this.map.removeControl(this.routingControl);
        this.routingControl = null;
      }
      if (this.map) {
        this.map.remove();
        this.map = null;
      }
    },
    // Helper method to normalize location names and handle typos
    normalizeLocation(location) {
      if (!location) return location;
      const normalized = location.toLowerCase().trim();
      // Handle common typos
      if (normalized.includes("tunise")) return "Tunis";
      if (normalized.includes("bebebhar")) return "Beb Bhar";
      return location; // Return original if no typo detected
    },
    async fetchTripDetails() {
      this.loading = true;
      this.error = null;
      const id = this.$route.params.id;
      const token = localStorage.getItem("access_token");

      // Clean up previous map
      this.destroyMap();

      try {
        // Fetch trip details
        const tripResponse = await axios.get(`http://127.0.0.1:8000/api/trips/${id}`, {
          headers: { Authorization: `Bearer ${token}` },
        });
        console.log("API trip response:", JSON.stringify(tripResponse.data, null, 2));
        this.trip = tripResponse.data.trip;

        // Log raw coordinates for debugging
        console.log("Raw departure_coords:", this.trip.departure_coords);
        console.log("Raw destination_coords:", this.trip.destination_coords);
        console.log("Departure location (raw):", this.trip.departure);
        console.log("Destination location (raw):", this.trip.destination);

        // Normalize departure and destination names to handle typos
        this.trip.departure = this.normalizeLocation(this.trip.departure) || this.defaultDeparture;
        this.trip.destination = this.normalizeLocation(this.trip.destination) || this.defaultDestination;

        console.log("Departure location (normalized):", this.trip.departure);
        console.log("Destination location (normalized):", this.trip.destination);

        // Parse coordinates
        this.trip.departure_coords = this.parseCoords(this.trip.departure_coords, this.trip.departure);
        this.trip.destination_coords = this.parseCoords(this.trip.destination_coords, this.trip.destination);

        // Log parsed coordinates for debugging
        console.log("Parsed departure_coords:", this.trip.departure_coords);
        console.log("Parsed destination_coords:", this.trip.destination_coords);

        // Apply fallback only if coordinates are invalid, with case-insensitive matching
        if (!this.isValidCoords(this.trip.departure_coords)) {
          const departureKey = Object.keys(this.fallbackCoords).find(
            key => key.toLowerCase() === this.trip.departure.toLowerCase()
          );
          if (departureKey) {
            console.warn(
              `Invalid departure coordinates for ${this.trip.departure}, using fallback: ${JSON.stringify(this.fallbackCoords[departureKey])}`
            );
            this.trip.departure_coords = this.fallbackCoords[departureKey];
          } else {
            console.warn(
              `No fallback coordinates found for ${this.trip.departure}, using default departure (${this.defaultDeparture}): ${JSON.stringify(this.fallbackCoords[this.defaultDeparture])}`
            );
            this.trip.departure = this.defaultDeparture;
            this.trip.departure_coords = this.fallbackCoords[this.defaultDeparture];
          }
        }

        if (!this.isValidCoords(this.trip.destination_coords)) {
          const destinationKey = Object.keys(this.fallbackCoords).find(
            key => key.toLowerCase() === this.trip.destination.toLowerCase()
          );
          if (destinationKey) {
            console.warn(
              `Invalid destination coordinates for ${this.trip.destination}, using fallback: ${JSON.stringify(this.fallbackCoords[destinationKey])}`
            );
            this.trip.destination_coords = this.fallbackCoords[destinationKey];
          } else {
            console.warn(
              `No fallback coordinates found for ${this.trip.destination}, using default destination (${this.defaultDestination}): ${JSON.stringify(this.fallbackCoords[this.defaultDestination])}`
            );
            this.trip.destination = this.defaultDestination;
            this.trip.destination_coords = this.fallbackCoords[this.defaultDestination];
          }
        }

        console.log("Processed trip:", JSON.stringify(this.trip, null, 2));

        // Fetch driver details
        try {
          const driverResponse = await axios.get(`http://127.0.0.1:8000/api/trips/${id}/driver`, {
            headers: { Authorization: `Bearer ${token}` },
          });
          console.log("API driver response:", driverResponse.data);
          this.driver = driverResponse.data;
        } catch (driverError) {
          console.warn("Driver not found:", driverError.response?.data?.message || driverError.message);
          this.driver = null;
        }

        // Initialize map if coordinates are valid
        this.$nextTick(() => {
          if (this.isValidCoords(this.trip.departure_coords) && this.isValidCoords(this.trip.destination_coords)) {
            this.initMap();
          } else {
            console.error("Cannot initialize map: Invalid coordinates", {
              departure_coords: this.trip.departure_coords,
              destination_coords: this.trip.destination_coords,
            });
            this.error = `Impossible d'afficher la carte : coordonnées non valides pour ${this.trip.departure} ou ${this.trip.destination}.`;
          }
        });
      } catch (error) {
        console.error("Error loading trip:", error);
        this.error = error.response?.data?.message || "Une erreur est survenue lors du chargement du trajet.";
        if (error.response) {
          console.error("Error details:", error.response.data);
        }
      } finally {
        this.loading = false;
      }
    },

    isValidCoords(coords) {
      return (
        Array.isArray(coords) &&
        coords.length === 2 &&
        !isNaN(coords[0]) &&
        !isNaN(coords[1]) &&
        coords[0] !== 0 &&
        coords[1] !== 0 &&
        coords[0] >= -90 &&
        coords[0] <= 90 &&
        coords[1] >= -180 &&
        coords[1] <= 180
      );
    },

    parseCoords(coords, location) {
      try {
        // Handle null or undefined
        if (coords == null) {
          console.warn(`No coordinates provided for ${location || "unknown"}`);
          return null;
        }

        // Handle array format (e.g., [35.8256, 10.6411])
        if (Array.isArray(coords) && coords.length === 2 && !isNaN(coords[0]) && !isNaN(coords[1])) {
          console.log(`Parsed array coords for ${location}:`, coords);
          return [parseFloat(coords[0]), parseFloat(coords[1])];
        }

        // Handle string format
        if (typeof coords === "string") {
          // Handle comma-separated string without brackets (e.g., "35.8256,10.6411")
          if (coords.includes(",")) {
            const [lat, lon] = coords.split(",").map(coord => parseFloat(coord.trim()));
            if (!isNaN(lat) && !isNaN(lon)) {
              console.log(`Parsed comma-separated string coords for ${location}:`, [lat, lon]);
              return [lat, lon];
            }
          }

          // Handle JSON string format (e.g., "[35.8256, 10.6411]")
          try {
            const parsed = JSON.parse(coords);
            if (Array.isArray(parsed) && parsed.length === 2 && !isNaN(parsed[0]) && !isNaN(parsed[1])) {
              console.log(`Parsed JSON string coords for ${location}:`, parsed);
              return [parseFloat(parsed[0]), parseFloat(parsed[1])];
            }
          } catch (e) {
            console.warn(`Invalid JSON string format for ${location}:`, coords);
          }
        }

        // Handle object format (e.g., { lat: 35.8256, lng: 10.6411 })
        if (coords && typeof coords === "object" && "lat" in coords && "lng" in coords) {
          if (!isNaN(coords.lat) && !isNaN(coords.lng)) {
            console.log(`Parsed lat/lng object for ${location}:`, [coords.lat, coords.lng]);
            return [parseFloat(coords.lat), parseFloat(coords.lng)];
          }
        }

        // Handle object format (e.g., { latitude: 35.8256, longitude: 10.6411 })
        if (coords && typeof coords === "object" && "latitude" in coords && "longitude" in coords) {
          if (!isNaN(coords.latitude) && !isNaN(coords.longitude)) {
            console.log(`Parsed latitude/longitude object for ${location}:`, [coords.latitude, coords.longitude]);
            return [parseFloat(coords.latitude), parseFloat(coords.longitude)];
          }
        }

        // Handle object format (e.g., { x: 35.8256, y: 10.6411 })
        if (coords && typeof coords === "object" && "x" in coords && "y" in coords) {
          if (!isNaN(coords.x) && !isNaN(coords.y)) {
            console.log(`Parsed x/y object for ${location}:`, [coords.x, coords.y]);
            return [parseFloat(coords.x), parseFloat(coords.y)];
          }
        }

        // Log invalid coordinates
        console.warn(`Invalid coordinates format for ${location || "unknown"}:`, coords);
        return null;
      } catch (e) {
        console.error(`Failed to parse coordinates for ${location || "unknown"}:`, coords, e);
        return null;
      }
    },

    initMap() {
      this.$nextTick(() => {
        const mapContainer = document.getElementById("map");
        if (!mapContainer) {
          console.error("Map container not found");
          return;
        }

        // Log coordinates being used for the map
        console.log("Using departure_coords for map:", this.trip.departure_coords);
        console.log("Using destination_coords for map:", this.trip.destination_coords);

        // Calculate dynamic center based on trip coordinates
        const centerLat = (this.trip.departure_coords[0] + this.trip.destination_coords[0]) / 2;
        const centerLng = (this.trip.departure_coords[1] + this.trip.destination_coords[1]) / 2;
        const map = L.map("map").setView([centerLat, centerLng], 8);
        this.map = map;

        // Add tile layer
        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
          attribution: '© OpenStreetMap contributors',
        }).addTo(map);

        // Group for fitting bounds
        const markerGroup = L.featureGroup();

        // Add markers and route for the trip
        if (this.isValidCoords(this.trip.departure_coords) && this.isValidCoords(this.trip.destination_coords)) {
          // Departure marker (green)
          const departureMarker = L.marker([this.trip.departure_coords[0], this.trip.departure_coords[1]], {
            icon: L.icon({
              iconUrl: markerIcon,
              iconSize: [25, 41],
              iconAnchor: [12, 41],
              popupAnchor: [1, -34],
              shadowUrl: markerShadow,
              shadowSize: [41, 41],
              className: "leaflet-marker-green",
            }),
          })
            .addTo(map)
            .bindPopup(
              `<b>Trajet #${this.trip.id}</b><br>Départ: ${this.trip.departure}<br>Lat: ${this.trip.departure_coords[0]}, Lon: ${this.trip.departure_coords[1]}`
            );

          // Destination marker (red)
          const destinationMarker = L.marker([this.trip.destination_coords[0], this.trip.destination_coords[1]], {
            icon: L.icon({
              iconUrl: markerIcon,
              iconSize: [25, 41],
              iconAnchor: [12, 41],
              popupAnchor: [1, -34],
              shadowUrl: markerShadow,
              shadowSize: [41, 41],
              className: "leaflet-marker-red",
            }),
          })
            .addTo(map)
            .bindPopup(
              `<b>Trajet #${this.trip.id}</b><br>Destination: ${this.trip.destination}<br>Lat: ${this.trip.destination_coords[0]}, Lon: ${this.trip.destination_coords[1]}`
            );

          // Add markers to group for bounds
          markerGroup.addLayer(departureMarker);
          markerGroup.addLayer(destinationMarker);

          // Add route with error handling
          try {
            const waypoints = [
              L.latLng(this.trip.departure_coords[0], this.trip.departure_coords[1]),
              L.latLng(this.trip.destination_coords[0], this.trip.destination_coords[1]),
            ];

            console.log("Routing waypoints:", waypoints); // Debug waypoints

            this.routingControl = L.Routing.control({
              waypoints: waypoints,
              routeWhileDragging: false,
              show: false, // Hide default instructions
              lineOptions: {
                styles: [{ color: "#0078A8", weight: 5, opacity: 0.8 }], // Blue, thicker line
              },
              createMarker: () => null, // Use custom markers
              router: L.Routing.osrmv1({
                serviceUrl: "https://router.project-osrm.org/route/v1",
                profile: "car",
              }),
            })
              .on("routesfound", (e) => {
                console.log("Route found:", e.routes);
                const route = e.routes[0];
                if (!route.coordinates || route.coordinates.length === 0) {
                  console.warn("Route found but no coordinates available");
                  this.error = "Impossible de tracer l'itinéraire : aucune route valide trouvée.";
                }
              })
              .on("routingerror", (error) => {
                console.error("Routing error:", error);
                this.error = "Impossible de tracer l'itinéraire. Problème avec le service de routage ou les coordonnées.";
              })
              .addTo(map);

            // Fit map to markers with padding
            if (markerGroup.getLayers().length > 0) {
              try {
                map.fitBounds(markerGroup.getBounds(), { padding: [50, 50], maxZoom: 14 });
              } catch (boundsError) {
                console.warn("Error fitting bounds, using default zoom:", boundsError);
                map.setView([centerLat, centerLng], 8);
              }
            }

            console.log("Map initialized successfully for trip", this.trip.id, {
              departure_coords: this.trip.departure_coords,
              destination_coords: this.trip.destination_coords,
              center: [centerLat, centerLng],
            });
          } catch (routingError) {
            console.error("Failed to initialize routing control:", routingError);
            this.error = "Erreur lors du traçage de l'itinéraire.";
          }
        } else {
          console.error(`Cannot create route for trip #${this.trip.id}: Invalid coordinates`, {
            departure_coords: this.trip.departure_coords,
            destination_coords: this.trip.destination_coords,
          });
          this.error = `Impossible de tracer l'itinéraire : coordonnées non valides pour ${this.trip.departure} ou ${this.trip.destination}.`;
        }
      });
    },

    formatDate(date) {
      if (!date) return "";
      const options = { weekday: "long", year: "numeric", month: "long", day: "numeric" };
      return new Date(date).toLocaleDateString("fr-FR", options);
    },

    formatTime(time) {
      return time ? time.substring(0, 5) : "";
    },

    reserveTrip() {
      if (this.trip?.available_seats > 0) {
        this.$router.push(`/reservation/${this.trip.id}`);
      }
    },
  },
};
</script>

<template>
  <div class="max-w-4xl mx-auto mt-6">
    <h1 class="text-3xl font-bold text-blue-600 mb-6 text-center">Détails du trajet</h1>

    <!-- Gestion des erreurs -->
    <div v-if="error" class="text-center text-red-500 mb-4">
      {{ error }}
    </div>

    <!-- Chargement -->
    <div v-else-if="loading" class="text-center text-gray-500 mt-10">
      Chargement des informations du trajet...
    </div>

    <!-- Affichage des détails du trajet -->
    <div v-else-if="trip" class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <div class="md:col-span-2 space-y-6">
        <!-- Itinéraire -->
        <div class="bg-gray-50 p-4 rounded-lg">
          <h2 class="text-xl font-semibold mb-4 flex items-center">
            <span class="text-blue-500 mr-2">🚗</span> Itinéraire
          </h2>
          <div class="space-y-3">
            <div class="flex items-start">
              <span class="text-green-500 text-xl mr-3">📍</span>
              <div>
                <p class="font-medium">Départ</p>
                <p class="text-gray-700">{{ trip.departure }}</p>
                <p v-if="isValidCoords(trip.departure_coords)" class="text-gray-500 text-sm">
                </p>
                <p v-else class="text-red-500 text-sm">Coordonnées non disponibles</p>
              </div>
            </div>
            <div class="flex items-start">
              <span class="text-red-500 text-xl mr-3">🏁</span>
              <div>
                <p class="font-medium">Destination</p>
                <p class="text-gray-700">{{ trip.destination }}</p>
                <p v-if="isValidCoords(trip.destination_coords)" class="text-gray-500 text-sm">
                </p>
                
                <p v-else class="text-red-500 text-sm">Coordonnées non disponibles</p>
                
              </div>
            </div>
          </div>
        </div>

        <!-- Map -->
        <div class="bg-gray-50 p-4 rounded-lg">
          <h2 class="text-xl font-semibold mb-4 flex items-center">
            <span class="text-blue-500 mr-2">🗺️</span> Carte du trajet
          </h2>
          <div id="map" style="height: 400px; width: 100%;" class="rounded-lg shadow-md"></div>
        </div>

        <!-- Date et heure -->
        <div class="bg-gray-50 p-4 rounded-lg">
          <h2 class="text-xl font-semibold mb-4 flex items-center">
            <span class="text-blue-500 mr-2">📅</span> Date et heure
          </h2>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <p class="font-medium">Date</p>
              <p>{{ formatDate(trip.trip_date) }}</p>
            </div>
            <div>
              <p class="font-medium">Heure de départ</p>
              <p>{{ formatTime(trip.departure_time) }}</p>
            </div>
            <div>
              <p class="font-medium">Heure d'arrivée estimée</p>
              <p>{{ formatTime(trip.estimate_arrival_time) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Conducteur et réservation -->
      <div class="space-y-6">
        <!-- Conducteur -->
        <div class="bg-gray-50 p-4 rounded-lg">
          <h2 class="text-xl font-semibold mb-4 flex items-center">
            <span class="text-blue-500 mr-2">👤</span> Conducteur
          </h2>
          <div v-if="driver" class="flex items-center space-x-4">
           
            <div>
              <p class="font-medium">{{ driver.name || 'Nom non disponible' }}</p>
              <div class="flex items-center mt-1">
               
                
              </div>
            </div>
          </div>
          <div v-else class="text-gray-500">
            Aucun conducteur assigné à ce trajet.
          </div>
        </div>

        <!-- Prix et places -->
        <div class="bg-gray-50 p-4 rounded-lg w-full">
          <h2 class="text-xl font-semibold mb-4 flex items-center">
            <span class="text-blue-500 mr-2">💰</span> Prix et places
          </h2>
          <div class="space-y-3">
            <div class="flex justify-between items-center">
              <span class="font-medium">Prix par place</span>
              <span class="text-xl font-bold text-blue-600">{{ trip.price }} TND</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="font-medium">Places disponibles</span>
              <span
                class="text-lg font-medium"
                :class="{ 'text-green-600': trip.available_seats > 0, 'text-red-600': trip.available_seats <= 0 }"
              >
                {{ trip.available_seats }} places
              </span>
            </div>
          </div>
        </div>

        <!-- Bouton de réservation -->
        <button
          @click="reserveTrip"
          class="w-full py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition duration-200 flex items-center justify-center space-x-2"
          :disabled="trip.available_seats <= 0"
        >
          <span>📌</span>
          <span>Réserver une place</span>
        </button>

        <div v-if="trip.available_seats <= 0" class="text-center text-red-500 text-sm mt-2">
          Désolé, ce trajet est complet.
        </div>
      </div>
    </div>

    <!-- Cas où aucun trajet n'est trouvé -->
    <div v-else class="text-center text-gray-500 mt-10">
      Aucun trajet trouvé.
    </div>
  </div>
</template>

<style scoped>
.bg-gray-50 {
  background-color: #f9fafb;
}
.rounded-lg {
  border-radius: 0.5rem;
}
.shadow-md {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}
.transition {
  transition: all 0.2s ease;
}
button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  background-color: #ccc;
}
#map {
  height: 400px;
  width: 100%;
  z-index: 1;
  border: 1px solid #ccc;
}
/* Custom marker colors */
.leaflet-marker-green {
  filter: hue-rotate(120deg); /* Green hue */
}
.leaflet-marker-red {
  filter: hue-rotate(0deg); /* Red hue */
}
</style>