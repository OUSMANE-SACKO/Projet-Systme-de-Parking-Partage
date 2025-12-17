/**
 * TaxawCar API - Service de communication avec le middleware
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
      return response.ok ? { success: true, data: result } : { success: false, error: result.message || 'Erreur serveur' };
    } catch (error) {
      return { success: false, error: 'Erreur de connexion' };
    }
  },

  async login(email, password) {
    const result = await this.send('AuthenticateUserDTO', { email, password });
    if (result.success && result.data?.token) {
      localStorage.setItem('authToken', result.data.token);
      localStorage.setItem('user', JSON.stringify(result.data.user));
    }
    return result;
  },

  async register(name, forename, email, password) {
    const result = await this.send('RegisterCustomerDTO', { name, forename, email, password });
    if (result.success && result.data?.token) {
      localStorage.setItem('authToken', result.data.token);
      localStorage.setItem('user', JSON.stringify(result.data.user));
    }
    return result;
  },

  async reserveParking(parkingId, from, to, vehiclePlate = null) {
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    return this.send('ReserveParkingDTO', { customerId: user.id, parkingId, from, to, vehiclePlate });
  },

  async subscribe(subscriptionTypeId) {
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    return this.send('SubscribeToSubscriptionDTO', { customerId: user.id, subscriptionTypeId });
  },

  async enterParking(parkingId, vehiclePlate) {
    return this.send('EnterExitParkingDTO', { parkingId, vehiclePlate, action: 'enter' });
  },

  async exitParking(parkingId, vehiclePlate) {
    return this.send('EnterExitParkingDTO', { parkingId, vehiclePlate, action: 'exit' });
  },

  logout() {
    localStorage.removeItem('authToken');
    localStorage.removeItem('user');
  }
};
