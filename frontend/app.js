// ========== AUTH ==========
const Auth = {
  isLoggedIn: () => localStorage.getItem('user') !== null,
  getUser: () => JSON.parse(localStorage.getItem('user') || 'null'),
  
  async login(email, password) {
    if (!email || !password) return { success: false, error: 'Email et mot de passe requis' };
    const result = await api.login(email, password);
    return result;
  },

  async register(data) {
    const { nom, prenom, email, password } = data;
    if (!nom || !prenom || !email || !password) return { success: false, error: 'Tous les champs sont requis' };
    const result = await api.register(nom, prenom, email, password);
    return result;
  },

  logout() {
    api.logout();
    window.location.href = 'index.html';
  },

  requireAuth() {
    if (!this.isLoggedIn()) { window.location.href = 'index.html'; return false; }
    return true;
  },

  redirectIfLoggedIn() {
    if (this.isLoggedIn()) { window.location.href = 'dashboard.html'; return true; }
    return false;
  }
};

// ========== PAGE PROTECTION ==========
const currentPage = window.location.pathname.split('/').pop() || 'index.html';
const protectedPages = ['dashboard.html', 'reserver.html', 'tarifs.html', 'abonnement.html'];

if (protectedPages.includes(currentPage)) Auth.requireAuth();
if (['index.html', 'register.html'].includes(currentPage)) Auth.redirectIfLoggedIn();

// ========== FORMS ==========
function showErrors(form, errors) {
  let container = form.querySelector('.form-errors');
  if (!container) {
    container = document.createElement('div');
    container.className = 'form-errors';
    form.insertBefore(container, form.firstChild);
  }
  container.innerHTML = (Array.isArray(errors) ? errors : [errors]).map(e => `<p class="error-message">${e}</p>`).join('');
}

function clearErrors(form) {
  const container = form.querySelector('.form-errors');
  if (container) container.innerHTML = '';
}

// Login
const loginForm = document.getElementById('login-form');
if (loginForm) {
  loginForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = loginForm.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.textContent = 'Connexion...';
    clearErrors(loginForm);

    const result = await Auth.login(
      document.getElementById('email').value,
      document.getElementById('password').value
    );

    if (result.success) {
      window.location.href = 'dashboard.html';
    } else {
      showErrors(loginForm, result.error || 'Erreur de connexion');
      btn.disabled = false;
      btn.textContent = 'Se connecter';
    }
  });
}

// Register
const registerForm = document.getElementById('register-form');
if (registerForm) {
  registerForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = registerForm.querySelector('button[type="submit"]');
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
      // Déconnecter pour forcer la connexion manuelle
      localStorage.removeItem('user');
      localStorage.removeItem('authToken');
      alert('Compte créé avec succès ! Connectez-vous.');
      window.location.href = 'index.html';
    } else {
      showErrors(registerForm, result.error || 'Erreur lors de l\'inscription');
      btn.disabled = false;
      btn.textContent = 'Créer mon compte';
    }
  });
}

// Logout
document.getElementById('logout-btn')?.addEventListener('click', () => Auth.logout());

// User display
if (protectedPages.includes(currentPage)) {
  const user = Auth.getUser();
  if (user) {
    const name = user.forename || user.prenom || user.email;
    const nameSpan = document.querySelector('#user-display .user-name');
    if (nameSpan) nameSpan.textContent = name;
    const welcomeName = document.getElementById('welcome-name');
    if (welcomeName) welcomeName.textContent = name;
  }
}

// ========== PARKINGS ==========
let parkings = []; // Sera chargé depuis l'API

async function loadParkings() {
  const result = await api.getParkings();
  if (result.success && result.parkings) {
    parkings = result.parkings;
  }
  return parkings;
}

// ========== GOOGLE MAPS ==========
if (document.getElementById('gmap')) {
  window.initMap = async function() {
    const map = new google.maps.Map(document.getElementById('gmap'), {
      center: { lat: 48.8566, lng: 2.3522 },
      zoom: 6, // Zoom plus large pour voir toute la France
      styles: [
        { elementType: 'geometry', stylers: [{ color: '#181828' }] },
        { elementType: 'labels.text.fill', stylers: [{ color: '#ffffff' }] },
        { featureType: 'road', stylers: [{ color: '#23233a' }] },
        { featureType: 'water', stylers: [{ color: '#222b36' }] },
        { featureType: 'poi', stylers: [{ visibility: 'off' }] }
      ]
    });

    // Charger les parkings depuis l'API
    await loadParkings();

    parkings.forEach(p => {
      const marker = new google.maps.Marker({
        position: { lat: p.lat, lng: p.lng },
        map,
        title: p.name,
        icon: { url: 'https://maps.gstatic.com/mapfiles/ms2/micons/parkinglot.png', scaledSize: new google.maps.Size(32, 32) }
      });

      const info = new google.maps.InfoWindow({
        content: `<div style="padding:10px"><b>${p.name}</b><br>${p.address}<br><b style="color:#8e24aa">${p.price.toFixed(2)}€/h</b></div>`
      });

      marker.addListener('click', () => info.open(map, marker));
    });

    // Parking list
    const list = document.getElementById('parking-list');
    if (list) {
      parkings.forEach(p => {
        const div = document.createElement('div');
        div.className = 'parking-item';
        div.innerHTML = `<div class="parking-item-name">${p.name}</div><div class="parking-item-address">${p.address}</div><div class="parking-item-price">${p.price.toFixed(2)}€/h</div>`;
        div.onclick = () => { map.setCenter({ lat: p.lat, lng: p.lng }); map.setZoom(16); openReservationModal(p); };
        list.appendChild(div);
      });
    }

    // Search
    const searchInput = document.getElementById('gmap-search-input');
    const suggestions = document.getElementById('gmap-search-suggestions');
    if (searchInput && suggestions) {
      searchInput.oninput = function() {
        const val = this.value.toLowerCase();
        suggestions.innerHTML = '';
        if (!val) { suggestions.classList.remove('active'); return; }
        const filtered = parkings.filter(p => p.name.toLowerCase().includes(val) || p.address.toLowerCase().includes(val));
        if (!filtered.length) { suggestions.classList.remove('active'); return; }
        filtered.forEach(p => {
          const div = document.createElement('div');
          div.className = 'gmap-search-suggestion';
          div.textContent = p.name;
          div.onmousedown = () => { map.setCenter({ lat: p.lat, lng: p.lng }); map.setZoom(16); suggestions.classList.remove('active'); searchInput.value = p.name; };
          suggestions.appendChild(div);
        });
        suggestions.classList.add('active');
      };
      searchInput.onblur = () => setTimeout(() => suggestions.classList.remove('active'), 200);
    }
  };

  if (window.google?.maps) window.initMap();
}

// ========== RESERVATION MODAL ==========
function openReservationModal(parking) {
  const modal = document.getElementById('reservation-modal');
  if (!modal) return;

  document.getElementById('modal-parking-info').innerHTML = `<h3>${parking.name}</h3><p>${parking.address}</p><p><strong>${parking.price.toFixed(2)}€/h</strong></p>`;
  modal.style.display = 'flex';

  document.getElementById('modal-close').onclick = () => modal.style.display = 'none';
  modal.onclick = (e) => { if (e.target === modal) modal.style.display = 'none'; };

  const dateDebut = document.getElementById('modal-date-debut');
  const dateFin = document.getElementById('modal-date-fin');
  const priceDisplay = document.getElementById('modal-price');

  const calcPrice = () => {
    if (dateDebut.value && dateFin.value) {
      const hours = (new Date(dateFin.value) - new Date(dateDebut.value)) / 3600000;
      if (hours > 0) priceDisplay.textContent = (10 + hours * parking.price).toFixed(2) + '€';
    }
  };
  dateDebut.onchange = dateFin.onchange = calcPrice;

  document.getElementById('confirm-reservation').onclick = async function() {
    if (!dateDebut.value || !dateFin.value) { alert('Sélectionnez les dates'); return; }
    this.disabled = true;
    this.textContent = 'Réservation...';
    
    const result = await api.reserveParking(parking.id, dateDebut.value, dateFin.value);
    alert(result.success ? 'Réservation confirmée !' : 'Erreur: ' + result.error);
    if (result.success) modal.style.display = 'none';
    
    this.disabled = false;
    this.textContent = 'Confirmer la réservation';
  };
}

// ========== SUBSCRIPTIONS ==========
document.querySelectorAll('.sub-btn').forEach(btn => {
  btn.onclick = async function() {
    const planMap = { 'essentiel-1': '1', 'premium': '2', 'annuel': '3' };
    this.disabled = true;
    this.textContent = 'Traitement...';
    
    const result = await api.subscribe(planMap[this.dataset.plan] || this.dataset.plan);
    alert(result.success ? 'Abonnement souscrit !' : 'Erreur: ' + result.error);
    
    if (result.success) {
      const current = document.getElementById('current-subscription');
      if (current) current.style.display = 'block';
    }
    
    this.disabled = false;
    this.textContent = 'Choisir';
  };
});
