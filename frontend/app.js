// Animation de switch entre login et register
const loginForm = document.getElementById('auth-form');
const registerForm = document.getElementById('register-form');
if (loginForm && registerForm && document.getElementById('show-register') && document.getElementById('show-login')) {
  document.getElementById('show-register').onclick = e => {
    e.preventDefault();
    loginForm.style.display = 'none';
    registerForm.style.display = 'block';
    registerForm.classList.add('fade-in');
  };
  document.getElementById('show-login').onclick = e => {
    e.preventDefault();
    registerForm.style.display = 'none';
    loginForm.style.display = 'block';
    loginForm.classList.add('fade-in');
  };
  ['auth-form','register-form'].forEach(id => {
    const el = document.getElementById(id);
    el.addEventListener('animationend', () => el.classList.remove('fade-in'));
  });
  // Placeholder pour la connexion/inscription (à brancher sur l'API)
  loginForm.onsubmit = e => {
    e.preventDefault();
    loginForm.querySelector('button').textContent = 'Connexion...';
    setTimeout(() => {
      loginForm.querySelector('button').textContent = 'Se connecter';
      alert('Connexion simulée !');
    }, 1200);
  };
  registerForm.onsubmit = e => {
    e.preventDefault();
    registerForm.querySelector('button').textContent = 'Inscription...';
    setTimeout(() => {
      registerForm.querySelector('button').textContent = 'S\'inscrire';
      alert('Inscription simulée !');
    }, 1200);
  };
}
// Animation d'activation de lien navbar
const navLinks = document.querySelectorAll('.nav-links a');
if (navLinks.length) {
  navLinks.forEach(link => {
    link.addEventListener('click', function() {
      navLinks.forEach(l => l.classList.remove('active'));
      this.classList.add('active');
    });
  });
}
// Animation bouton CTA
const ctaBtn = document.querySelector('.cta-btn');
if (ctaBtn) {
  ctaBtn.addEventListener('mouseenter', () => {
    ctaBtn.style.boxShadow = '0 0 48px 12px #00ffe7';
  });
  ctaBtn.addEventListener('mouseleave', () => {
    ctaBtn.style.boxShadow = '0 0 24px 4px #00ffe7';
  });
}
// Simuler l'état de connexion (à remplacer par vraie logique plus tard)
const isLoggedIn = false; // Passe à true pour voir le logo compte
const accountArea = document.getElementById('account-area');
if (accountArea) {
  if (isLoggedIn) {
    accountArea.innerHTML = `<svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
      <circle cx="14" cy="14" r="14" fill="#1976d2"/>
      <circle cx="14" cy="11" r="5" fill="#fff"/>
      <ellipse cx="14" cy="20.5" rx="7.5" ry="4.5" fill="#fff"/>
    </svg>`;
    accountArea.title = "Mon profil";
    accountArea.style.background = "none";
    accountArea.style.cursor = "pointer";
  } else {
    accountArea.textContent = "Connexion";
    accountArea.title = "Se connecter";
    accountArea.style.cursor = "pointer";
  }
}
// --- Carte Google Maps centrée sur Paris ---
const parkings = [
  { name: 'Parking Indigo - Place Vendôme', lat: 48.867, lng: 2.329 },
  { name: 'Parking Saemes - Notre-Dame', lat: 48.852, lng: 2.349 },
  { name: 'Parking Indigo - Bercy', lat: 48.838, lng: 2.384 },
  { name: 'Parking Vinci - Gare de Lyon', lat: 48.844, lng: 2.373 },
  { name: 'Parking Indigo - Champs-Élysées', lat: 48.870, lng: 2.307 }
];
if (document.getElementById('gmap')) {
  function initGMap() {
    const paris = { lat: 48.8566, lng: 2.3522 };
    const map = new google.maps.Map(document.getElementById('gmap'), {
      center: paris,
      zoom: 13,
      disableDefaultUI: false,
      mapTypeId: 'roadmap',
      styles: [
        { elementType: 'geometry', stylers: [{ color: '#181828' }] },
        { elementType: 'labels.text.stroke', stylers: [{ color: '#181828' }] },
        { elementType: 'labels.text.fill', stylers: [{ color: '#ffffff' }] },
        { featureType: 'poi', stylers: [{ visibility: 'off' }] },
        { featureType: 'road', stylers: [{ color: '#23233a' }] },
        { featureType: 'water', stylers: [{ color: '#222b36' }] }
      ]
    });
    // Afficher les parkings
    parkings.forEach(p => {
      new google.maps.Marker({
        position: { lat: p.lat, lng: p.lng },
        map,
        title: p.name,
        icon: {
          url: 'https://maps.gstatic.com/mapfiles/ms2/micons/parkinglot.png',
          scaledSize: new google.maps.Size(32, 32)
        }
      });
    });
  }
  if (window.google && window.google.maps) {
    initGMap();
  } else {
    window.initMap = initGMap;
  }
}
// --- Recherche d'adresse sur Google Maps ---
if (document.getElementById('gmap-search-btn') && document.getElementById('gmap-search-input') && document.getElementById('gmap')) {
  const searchBtn = document.getElementById('gmap-search-btn');
  const searchInput = document.getElementById('gmap-search-input');
  let gmap = null;
  function initGMap() {
    const paris = { lat: 48.8566, lng: 2.3522 };
    gmap = new google.maps.Map(document.getElementById('gmap'), {
      center: paris,
      zoom: 13,
      disableDefaultUI: false,
      mapTypeId: 'roadmap',
      styles: [
        { elementType: 'geometry', stylers: [{ color: '#181828' }] },
        { elementType: 'labels.text.stroke', stylers: [{ color: '#181828' }] },
        { elementType: 'labels.text.fill', stylers: [{ color: '#23233a' }] },
        { featureType: 'poi', stylers: [{ visibility: 'off' }] },
        { featureType: 'road', stylers: [{ color: '#23233a' }] },
        { featureType: 'water', stylers: [{ color: '#222b36' }] }
      ]
    });
    new google.maps.Marker({ position: paris, map: gmap, title: 'Paris' });
  }
  if (window.google && window.google.maps) {
    initGMap();
  } else {
    window.initMap = initGMap;
  }
  // Recherche géocodée
  function searchLocation() {
    const address = searchInput.value.trim();
    if (!address) return;
    const geocoder = new google.maps.Geocoder();
    geocoder.geocode({ address }, (results, status) => {
      if (status === 'OK' && results[0]) {
        gmap.setCenter(results[0].geometry.location);
        new google.maps.Marker({ position: results[0].geometry.location, map: gmap });
      } else {
        alert('Aucun résultat trouvé.');
      }
    });
  }
  searchBtn.addEventListener('click', searchLocation);
  searchInput.addEventListener('keydown', e => {
    if (e.key === 'Enter') searchLocation();
  });
}
// --- Recherche parking sur liste ---
if (document.getElementById('gmap-search-input') && document.getElementById('gmap-search-suggestions') && document.getElementById('gmap')) {
  const searchInput = document.getElementById('gmap-search-input');
  const suggestionsBox = document.getElementById('gmap-search-suggestions');
  let gmap = null;
  let markers = [];
  function initGMap() {
    const paris = { lat: 48.8566, lng: 2.3522 };
    gmap = new google.maps.Map(document.getElementById('gmap'), {
      center: paris,
      zoom: 13,
      disableDefaultUI: false,
      mapTypeId: 'roadmap',
      styles: [
        { elementType: 'geometry', stylers: [{ color: '#181828' }] },
        { elementType: 'labels.text.stroke', stylers: [{ color: '#181828' }] },
        { elementType: 'labels.text.fill', stylers: [{ color: '#23233a' }] },
        { featureType: 'poi', stylers: [{ visibility: 'off' }] },
        { featureType: 'road', stylers: [{ color: '#23233a' }] },
        { featureType: 'water', stylers: [{ color: '#222b36' }] }
      ]
    });
    // Afficher les parkings
    markers = parkings.map(p => new google.maps.Marker({
      position: { lat: p.lat, lng: p.lng },
      map: gmap,
      title: p.name,
      icon: {
        url: 'https://maps.gstatic.com/mapfiles/ms2/micons/parkinglot.png',
        scaledSize: new google.maps.Size(32, 32)
      }
    }));
  }
  if (window.google && window.google.maps) {
    initGMap();
  } else {
    window.initMap = initGMap;
  }
  // Suggestions dynamiques
  searchInput.addEventListener('input', function() {
    const val = this.value.trim().toLowerCase();
    suggestionsBox.innerHTML = '';
    if (!val) {
      suggestionsBox.classList.remove('active');
      return;
    }
    const filtered = parkings.filter(p => p.name.toLowerCase().includes(val));
    if (filtered.length === 0) {
      suggestionsBox.classList.remove('active');
      return;
    }
    filtered.forEach((p, idx) => {
      const div = document.createElement('div');
      div.className = 'gmap-search-suggestion';
      div.textContent = p.name;
      div.tabIndex = 0;
      div.addEventListener('mousedown', () => {
        gmap.setCenter({ lat: p.lat, lng: p.lng });
        gmap.setZoom(16);
        markers.forEach(m => m.setAnimation(null));
        const marker = markers[parkings.findIndex(pk => pk.name === p.name)];
        if (marker) { marker.setAnimation(google.maps.Animation.BOUNCE); }
        suggestionsBox.classList.remove('active');
        searchInput.value = p.name;
      });
      suggestionsBox.appendChild(div);
    });
    suggestionsBox.classList.add('active');
  });
  // Perte du focus sur la recherche
  searchInput.addEventListener('blur', () => {
    setTimeout(() => {
      suggestionsBox.classList.remove('active');
    }, 200);
  });
}
