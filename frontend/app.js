/**
 * TaxawCar - Application Frontend
 * Gestion de l'authentification, navigation et interactions
 */

// ========== AUTHENTIFICATION ==========
const Auth = {
  isLoggedIn: () => !!localStorage.getItem('user'),
  getUser: () => JSON.parse(localStorage.getItem('user') || 'null'),
  
  async login(email, password) {
    if (!email || !password) return { success: false, error: 'Email et mot de passe requis' };
      const result = await api.login(email, password);
      // Stocker le rôle dans le localStorage si présent
      if (result.success && result.user && result.user.role) {
        localStorage.setItem('userRole', result.user.role);
      } else if (result.success && result.user && result.user.isOwner) {
        // fallback pour compatibilité : si isOwner true, rôle = owner
        localStorage.setItem('userRole', result.user.isOwner ? 'owner' : 'customer');
      }
      return result;
  },

  async register(data) {
    const { nom, prenom, email, password } = data;
    if (!nom || !prenom || !email || !password) return { success: false, error: 'Tous les champs requis' };
    return await api.register(nom, prenom, email, password);
  },

  logout() {
    api.logout();
    window.location.href = 'index.html';
  },

  requireAuth() {
    if (!this.isLoggedIn()) {
      window.location.href = 'index.html';
      return false;
    }
    return true;
  },

  redirectIfLoggedIn() {
    if (this.isLoggedIn()) {
      window.location.href = 'dashboard.html';
      return true;
    }
    return false;
  }
};

// Fonction globale pour le bouton de déconnexion
function logout() {
  Auth.logout();
}

// ========== CONFIGURATION DES PAGES ==========
const currentPage = window.location.pathname.split('/').pop() || 'index.html';
const protectedPages = ['dashboard.html', 'reserver.html', 'abonnement.html', 'mes-reservations.html', 'mes-stationnements.html'];
const authPages = ['index.html', 'register.html', 'register-owner.html'];

// Protection des pages
if (protectedPages.includes(currentPage)) Auth.requireAuth();
if (authPages.includes(currentPage)) Auth.redirectIfLoggedIn();

// ========== UTILITAIRES FORMULAIRES ==========
function showErrors(form, errors) {
  let container = form.querySelector('.form-errors');
  if (!container) {
    container = document.createElement('div');
    container.className = 'form-errors';
    form.insertBefore(container, form.firstChild);
  }
  container.innerHTML = (Array.isArray(errors) ? errors : [errors])
    .map(e => `<p class="error-message">${e}</p>`).join('');
}

function clearErrors(form) {
  const container = form.querySelector('.form-errors');
  if (container) container.innerHTML = '';
}

// ========== FORMULAIRE DE CONNEXION ==========
const loginForm = document.getElementById('login-form');
if (loginForm) {
  loginForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = loginForm.querySelector('button[type="submit"]');
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Connexion...';
    clearErrors(loginForm);

    const result = await Auth.login(
      document.getElementById('email').value,
      document.getElementById('password').value
    );

    if (result.success) {
      // Rediriger selon le rôle
      const role = localStorage.getItem('userRole');
      if (role === 'owner') {
        window.location.href = 'owner-dashboard.html';
      } else {
        window.location.href = 'dashboard.html';
      }
    } else {
      showErrors(loginForm, result.error || 'Erreur de connexion');
      btn.disabled = false;
      btn.textContent = originalText;
    }
  // Affichage du menu selon le rôle
  document.addEventListener('DOMContentLoaded', () => {
    const role = localStorage.getItem('userRole');
    // Si on est sur dashboard.html et c'est un owner, rediriger
    if (currentPage === 'dashboard.html' && role === 'owner') {
      window.location.href = 'owner-dashboard.html';
    }
    // Si on est sur owner-dashboard.html et pas owner, rediriger
    if (currentPage === 'owner-dashboard.html' && role !== 'owner') {
      window.location.href = 'dashboard.html';
    }
    // Adapter le menu si besoin (exemple : masquer les liens client pour owner)
    if (role === 'owner') {
      const nav = document.querySelector('.nav-center');
      if (nav) nav.innerHTML = '<li><a href="owner-dashboard.html" class="active">Mon Espace</a></li>';
      const navName = document.getElementById('nav-user-name');
      if (navName) navName.textContent = 'Propriétaire';
    }
  });
  });
}

// ========== FORMULAIRE D'INSCRIPTION ==========
const registerForm = document.getElementById('register-form');
if (registerForm) {
  registerForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = registerForm.querySelector('button[type="submit"]');
    const originalText = btn.textContent;
    clearErrors(registerForm);

    const password = document.getElementById('password').value;
    if (password !== document.getElementById('password2').value) {
      showErrors(registerForm, 'Les mots de passe ne correspondent pas');
      return;
    }

    btn.disabled = true;
    btn.textContent = 'Création...';

    const result = await Auth.register({
      nom: document.getElementById('nom').value,
      prenom: document.getElementById('prenom').value,
      email: document.getElementById('email').value,
      password
    });

    if (result.success) {
      localStorage.removeItem('user');
      localStorage.removeItem('authToken');
      alert('Compte créé avec succès ! Connectez-vous.');
      window.location.href = 'index.html';
    } else {
      showErrors(registerForm, result.error || 'Erreur lors de l\'inscription');
      btn.disabled = false;
      btn.textContent = originalText;
    }
  });
}

// ========== FORMULAIRE D'INSCRIPTION PROPRIÉTAIRE ==========
const ownerRegisterForm = document.getElementById('owner-register-form');
if (ownerRegisterForm) {
  ownerRegisterForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = ownerRegisterForm.querySelector('button[type="submit"]');
    const originalText = btn.textContent;
    clearErrors(ownerRegisterForm);

    const password = document.getElementById('password').value;
    if (password !== document.getElementById('password2').value) {
      showErrors(ownerRegisterForm, 'Les mots de passe ne correspondent pas');
      return;
    }
    if (password.length < 6) {
      showErrors(ownerRegisterForm, 'Le mot de passe doit contenir au moins 6 caractères');
      return;
    }

    btn.disabled = true;
    btn.textContent = 'Création...';

    const result = await api.registerOwner(
      document.getElementById('nom').value,
      document.getElementById('prenom').value,
      document.getElementById('email').value,
      password
    );

    if (result.success) {
      alert('Compte propriétaire créé avec succès ! Connectez-vous.');
      window.location.href = 'index.html';
    } else {
      showErrors(ownerRegisterForm, result.error || 'Erreur lors de l\'inscription');
      btn.disabled = false;
      btn.textContent = originalText;
    }
  });
}

// ========== DÉCONNEXION & AFFICHAGE UTILISATEUR ==========
// Note: logout() est maintenant une fonction globale définie plus haut

if (protectedPages.includes(currentPage)) {
  const user = Auth.getUser();
  if (user) {
    const name = user.forename || user.prenom || user.name || user.email;
    // Afficher le nom dans la navbar
    const navUserName = document.getElementById('nav-user-name');
    if (navUserName) navUserName.textContent = name;
    // Afficher le nom de bienvenue dans le dashboard
    const welcomeName = document.getElementById('welcome-name');
    if (welcomeName) welcomeName.textContent = name;
  }
}

// ========== DONNÉES PARKINGS ==========
let parkings = [];

async function loadParkings() {
  const result = await api.getParkings();
  if (result.success && result.parkings) {
    parkings = result.parkings;
  }
  return parkings;
}

// ========== RÉSERVATIONS (Dashboard) ==========
function computeReservationStatus(startIso, endIso) {
  const now = new Date();
  const start = startIso ? new Date(startIso) : null;
  const end = endIso ? new Date(endIso) : null;
  if (start && end) {
    if (now >= start && now <= end) return 'active';
    if (now < start) return 'pending';
    return 'completed';
  }
  return 'pending';
}

async function loadDashboardReservations() {
  if (currentPage !== 'dashboard.html') return;
  const container = document.getElementById('reservations-container');
  if (!container) return;
  container.innerHTML = '<p>Chargement...</p>';
  try {
    const res = await api.getUserReservations();
    if (!res.success) {
      container.innerHTML = `<p class="error">Erreur: ${res.error || res.message}</p>`;
      return;
    }
    const reservations = res.reservations || [];
    if (!reservations.length) {
      container.innerHTML = `
        <div class="empty-state">
          <span>🚗</span>
          <p>Aucune réservation pour le moment</p>
          <a href="reserver.html" class="cta-btn-small">Réserver maintenant</a>
        </div>`;
      return;
    }

    container.innerHTML = reservations.map(r => {
      const start = r.startTime || r.from || r.begin || r.fromTime;
      const end = r.endTime || r.to || r.finish || r.toTime;
      const status = computeReservationStatus(start, end);
      const label = status === 'active' ? '🟢 En cours' : (status === 'pending' ? '⏳ À venir' : '✅ Terminée');
      return `
        <div class="reservation-summary">
          <div class="res-left">
            <strong>${r.parkingName || r.parking?.name || 'Parking'}</strong>
            <div class="res-dates">${start ? new Date(start).toLocaleString() : '-'} → ${end ? new Date(end).toLocaleString() : '-'}</div>
          </div>
          <div class="res-right">
            <span class="status-badge ${status}">${label}</span>
            <div class="res-price">${(r.totalPrice || r.price || 0).toFixed ? (r.totalPrice || r.price || 0).toFixed(2) + '€' : (r.totalPrice || r.price || 0) + '€'}</div>
          </div>
        </div>`;
    }).join('');
  } catch (e) {
    container.innerHTML = '<p class="error">Erreur de connexion au serveur.</p>';
  }
}

// Charger les résa sur la page d'accueil si présent
if (currentPage === 'dashboard.html') {
  loadDashboardReservations();
}

// ========== GOOGLE MAPS ==========
if (document.getElementById('gmap')) {
  window.initMap = async function() {
    const map = new google.maps.Map(document.getElementById('gmap'), {
      center: { lat: 46.603354, lng: 1.888334 },
      zoom: 6,
      styles: [
        { elementType: 'geometry', stylers: [{ color: '#181828' }] },
        { elementType: 'labels.text.fill', stylers: [{ color: '#ffffff' }] },
        { featureType: 'road', stylers: [{ color: '#23233a' }] },
        { featureType: 'water', stylers: [{ color: '#222b36' }] },
        { featureType: 'poi', stylers: [{ visibility: 'off' }] }
      ]
    });

    await loadParkings();

    parkings.forEach(p => {
      const marker = new google.maps.Marker({
        position: { lat: p.lat, lng: p.lng },
        map,
        title: p.name,
        icon: {
          url: 'https://maps.gstatic.com/mapfiles/ms2/micons/parkinglot.png',
          scaledSize: new google.maps.Size(32, 32)
        }
      });

      const info = new google.maps.InfoWindow({
        content: `<div style="padding:10px;color:#333">
          <b>${p.name}</b><br>${p.address}, ${p.city}<br>
          <b style="color:#8e24aa">${p.price.toFixed(2)}€/h</b>
        </div>`
      });

      marker.addListener('click', () => info.open(map, marker));
    });

    const list = document.getElementById('parking-list');
    if (list) {
      list.innerHTML = parkings.map(p => `
        <div class="parking-item" data-id="${p.id}">
          <div class="parking-item-name">${p.name}</div>
          <div class="parking-item-address">${p.address}, ${p.city}</div>
          <div class="parking-item-price">${p.price.toFixed(2)}€/h</div>
        </div>
      `).join('');

      list.querySelectorAll('.parking-item').forEach(item => {
        item.onclick = () => {
          const parking = parkings.find(p => p.id == item.dataset.id);
          if (parking) {
            map.setCenter({ lat: parking.lat, lng: parking.lng });
            map.setZoom(16);
            openReservationModal(parking);
          }
        };
      });
    }

    setupMapSearch(map);
  };

  if (window.google?.maps) window.initMap();
}

function setupMapSearch(map) {
  const searchInput = document.getElementById('gmap-search-input');
  const suggestions = document.getElementById('gmap-search-suggestions');
  if (!searchInput || !suggestions) return;

  searchInput.oninput = function() {
    const val = this.value.toLowerCase();
    suggestions.innerHTML = '';
    if (!val) { suggestions.classList.remove('active'); return; }

    const filtered = parkings.filter(p => 
      p.name.toLowerCase().includes(val) || 
      p.address.toLowerCase().includes(val) ||
      p.city.toLowerCase().includes(val)
    );

    if (!filtered.length) { suggestions.classList.remove('active'); return; }

    filtered.forEach(p => {
      const div = document.createElement('div');
      div.className = 'gmap-search-suggestion';
      div.textContent = `${p.name} - ${p.city}`;
      div.onmousedown = () => {
        map.setCenter({ lat: p.lat, lng: p.lng });
        map.setZoom(16);
        suggestions.classList.remove('active');
        searchInput.value = p.name;
      };
      suggestions.appendChild(div);
    });
    suggestions.classList.add('active');
  };

  searchInput.onblur = () => setTimeout(() => suggestions.classList.remove('active'), 200);
}

// ========== MODAL DE RÉSERVATION ==========
async function openReservationModal(parking) {
  const modal = document.getElementById('reservation-modal');
  if (!modal) return;

  // Charger les infos détaillées du parking (avec les tranches tarifaires)
  try {
    const result = await api.getParkingInfo(parking.id);
    if (result.success && result.parking) {
      parking.pricingTiers = result.parking.pricingTiers || [];
      // override base price if API provides a different one
      if (typeof result.parking.price === 'number') parking.price = result.parking.price;
    }
  } catch (e) {
    // ignore and use local parking data
  }

  document.getElementById('modal-parking-info').innerHTML = `
    <h3>${parking.name}</h3>
    <p>${parking.address}, ${parking.city}</p>
    <p><strong>${parking.price.toFixed(2)}€/h</strong> (tarif de base)</p>
    <p class="pricing-note">💡 Le tarif peut varier selon l'heure de stationnement</p>
  `;
  modal.style.display = 'flex';

  document.getElementById('modal-close').onclick = () => modal.style.display = 'none';
  modal.onclick = (e) => { if (e.target === modal) modal.style.display = 'none'; };

  const dateDebut = document.getElementById('modal-date-debut');
  const dateFin = document.getElementById('modal-date-fin');
  const priceDisplay = document.getElementById('modal-price');

  // Fonction pour obtenir le tarif applicable selon l'heure
  const getPriceForHour = (hour) => {
    if (!parking.pricingTiers || parking.pricingTiers.length === 0) {
      return parking.price;
    }
    
    // Trier les tranches par heure
    const sortedTiers = [...parking.pricingTiers].sort((a, b) => {
      const timeA = a.time.split(':').map(Number);
      const timeB = b.time.split(':').map(Number);
      return (timeA[0] * 60 + timeA[1]) - (timeB[0] * 60 + timeB[1]);
    });
    
    // Trouver la tranche applicable
    let applicableTier = sortedTiers[0];
    for (const tier of sortedTiers) {
      const tierHour = parseInt(tier.time.split(':')[0]);
      if (tierHour <= hour) {
        applicableTier = tier;
      }
    }
    
    return applicableTier ? applicableTier.price : parking.price;
  };

  // Calculer le prix en tenant compte des tranches horaires et des jours
  const calcPrice = () => {
    if (dateDebut.value && dateFin.value) {
      const start = new Date(dateDebut.value);
      const end = new Date(dateFin.value);
      if (isNaN(start) || isNaN(end) || end <= start) {
        priceDisplay.textContent = '--€';
        return;
      }
      let totalPrice = 0;
      let current = new Date(start);
      // Boucle heure par heure, même sur plusieurs jours
      while (current < end) {
        const hour = current.getHours();
        const hourlyRate = getPriceForHour(hour);
        // Calcul de la durée de ce segment (jusqu'à la prochaine heure ou la fin)
        let nextHour = new Date(current);
        nextHour.setHours(current.getHours() + 1, 0, 0, 0);
        let delta = Math.min((end - current) / 3600000, 1);
        if (delta > 0) totalPrice += hourlyRate * delta;
        current = nextHour;
      }
      priceDisplay.textContent = totalPrice.toFixed(2) + '€';
    }
  };
  dateDebut.onchange = dateFin.onchange = calcPrice;

  document.getElementById('confirm-reservation').onclick = async function() {
    if (!dateDebut.value || !dateFin.value) { alert('Sélectionnez les dates'); return; }
    this.disabled = true;
    this.textContent = 'Réservation...';
    
    const result = await api.reserveParking(parking.id, dateDebut.value, dateFin.value);
    if (result.success) {
      showGlobalMessage('✅ Réservation confirmée !', 'success');
      modal.style.display = 'none';
    } else {
      showGlobalMessage('❌ Erreur: ' + (result.error || result.message), 'error');
    }
    
    this.disabled = false;
    this.textContent = 'Confirmer la réservation';
  };
}

// Message global simple (bandeau en haut de page)
function showGlobalMessage(message, type = 'info') {
  let div = document.getElementById('global-message');
  if (!div) {
    div = document.createElement('div');
    div.id = 'global-message';
    div.style.position = 'fixed';
    div.style.top = '16px';
    div.style.left = '50%';
    div.style.transform = 'translateX(-50%)';
    div.style.zIndex = 9999;
    div.style.padding = '10px 16px';
    div.style.borderRadius = '6px';
    div.style.boxShadow = '0 2px 8px rgba(0,0,0,0.2)';
    div.style.fontSize = '14px';
    document.body.appendChild(div);
  }
  div.textContent = message;
  if (type === 'success') div.style.background = '#d4edda', div.style.color = '#155724';
  else if (type === 'error') div.style.background = '#f8d7da', div.style.color = '#721c24';
  else div.style.background = '#cce5ff', div.style.color = '#004085';
  div.style.display = 'block';
  clearTimeout(div._timeout);
  div._timeout = setTimeout(() => { div.style.display = 'none'; }, 5000);
}

// ========== ABONNEMENTS ==========
document.querySelectorAll('.sub-btn').forEach(btn => {
  btn.onclick = async function() {
    const planMap = { 'essentiel-1': '1', 'premium': '2', 'annuel': '3' };
    this.disabled = true;
    const originalText = this.textContent;
    this.textContent = 'Traitement...';
    
    const result = await api.subscribe(planMap[this.dataset.plan] || this.dataset.plan);
    alert(result.success ? 'Abonnement souscrit !' : 'Erreur: ' + (result.error || result.message));
    
    if (result.success) {
      const current = document.getElementById('current-subscription');
      if (current) current.style.display = 'block';
    }
    
    this.disabled = false;
    this.textContent = originalText;
  };
});
