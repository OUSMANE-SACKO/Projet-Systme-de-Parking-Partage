/**
 * TaxawCar API - Service de communication avec le middleware
 * Conforme au cahier des charges fonctionnel
 */

const API_URL = '/middleware/api.php';

// ========== API SERVICE ==========
const api = {
  async send(dtoType, data = {}) {
    try {
      const response = await fetch(API_URL, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('authToken') || ''}`
        },
        body: JSON.stringify({ dtoType, ...data })
      });

      const result = await response.json();
      // Le serveur renvoie { success, data: { ... } } - on aplatit la réponse
      if (response.ok && result.success) {
        return { success: true, ...result.data };
      }
      return { success: false, error: result.message || result.data?.message || 'Erreur serveur' };
    } catch (error) {
      return { success: false, error: 'Erreur de connexion' };
    }
  },

  // ========== AUTHENTIFICATION & INSCRIPTION ==========
  
  async login(email, password) {
    const result = await this.send('AuthenticateUserDTO', { email, password });
    if (result.success && result.authenticated && result.token) {
      localStorage.setItem('authToken', result.token);
      localStorage.setItem('user', JSON.stringify(result.user));
    }
    return result;
  },

  async register(name, forename, email, password) {
    return await this.send('RegisterCustomerDTO', { name, forename, email, password });
  },

  async registerOwner(name, forename, email, password) {
    return await this.send('RegisterOwnerDTO', { name, forename, email, password });
  },

  logout() {
    localStorage.removeItem('authToken');
    localStorage.removeItem('user');
  },

  getCurrentUser() {
    const userStr = localStorage.getItem('user');
    return userStr ? JSON.parse(userStr) : null;
  },

  isLoggedIn() {
    return !!localStorage.getItem('authToken');
  },

  // ========== GESTION DES PARKINGS ==========

  async getParkings(city = null) {
    return await this.send('GetParkingsDTO', { city });
  },

  async getParkingInfo(parkingId) {
    return await this.send('GetParkingInfoDTO', { parkingId });
  },

  async searchParkings(latitude, longitude, radiusKm = 5, timestamp = null) {
    return await this.send('SearchParkingsDTO', { 
      latitude, 
      longitude, 
      radiusKm,
      timestamp: timestamp || new Date().toISOString()
    });
  },

  async addParking(parkingData) {
    const user = this.getCurrentUser();
    return await this.send('AddParkingDTO', { 
      ownerId: user?.id, 
      ...parkingData 
    });
  },

  async updateParkingPricing(parkingId, hourlyRate, pricingTiers = []) {
    return await this.send('UpdateParkingPricingDTO', { 
      parkingId, 
      hourlyRate, 
      pricingTiers 
    });
  },

  // ========== RÉSERVATIONS ==========

  async reserveParking(parkingId, from, to, vehiclePlate = null) {
    const user = this.getCurrentUser();
    return await this.send('ReserveParkingDTO', { 
      customerId: user?.id, 
      parkingId, 
      from, 
      to, 
      vehiclePlate 
    });
  },

  async getParkingReservations(parkingId, status = null) {
    return await this.send('GetParkingReservationsDTO', { parkingId, status });
  },

  async getUserReservations() {
    const user = this.getCurrentUser();
    return await this.send('GetUserReservationsDTO', { userId: user?.id });
  },

  async getReservationInvoice(reservationId, format = 'html') {
    return await this.send('GetReservationInvoiceDTO', { reservationId, format });
  },

  // ========== STATIONNEMENTS (SESSIONS) ==========

  async enterParking(parkingId, vehiclePlate) {
    return await this.send('EnterExitParkingDTO', { parkingId, vehiclePlate, action: 'enter' });
  },

  async exitParking(parkingId, vehiclePlate) {
    return await this.send('EnterExitParkingDTO', { parkingId, vehiclePlate, action: 'exit' });
  },

  async getParkingSessions(parkingId, activeOnly = false) {
    return await this.send('GetParkingSessionsDTO', { parkingId, activeOnly });
  },

  async getUserSessions() {
    const user = this.getCurrentUser();
    return await this.send('GetUserSessionsDTO', { userId: user?.id });
  },

  // ========== ABONNEMENTS ==========

  async getParkingSubscriptions(parkingId) {
    return await this.send('GetParkingSubscriptionsDTO', { parkingId });
  },

  async subscribe(subscriptionTypeId) {
    const user = this.getCurrentUser();
    return await this.send('SubscribeToSubscriptionDTO', { 
      customerId: user?.id, 
      subscriptionTypeId 
    });
  },

  async addSubscriptionType(parkingId, name, description, monthlyPrice, durationMonths = 1) {
    return await this.send('AddSubscriptionTypeDTO', { 
      parkingId, 
      name, 
      description, 
      monthlyPrice, 
      durationMonths 
    });
  },

  // ========== ANALYTICS PROPRIÉTAIRE ==========

  async getParkingAvailability(parkingId, timestamp = null) {
    return await this.send('GetParkingAvailabilityDTO', { 
      parkingId, 
      timestamp: timestamp || new Date().toISOString() 
    });
  },

  async getParkingRevenue(parkingId, month = null, year = null) {
    const now = new Date();
    return await this.send('GetParkingRevenueDTO', { 
      parkingId, 
      month: month || now.getMonth() + 1, 
      year: year || now.getFullYear() 
    });
  },

  async getUnauthorizedDrivers(parkingId) {
    return await this.send('GetUnauthorizedDriversDTO', { parkingId });
  }
};
