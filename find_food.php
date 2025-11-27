<?php
// find_food.php — page for reporters to find nearby food donations
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Find Food — Smart Aid</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <!-- Leaflet (OpenStreetMap) for interactive map (no API key) -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

  <style>
    :root{
      --green:#185e34;
      --card-bg: rgba(255,255,255,0.95);
      --muted:#2f5c45;
    }
    body{font-family:Inter,Arial; margin:0; background:linear-gradient(180deg,#eaf8ef,#f7fff9); color:#08321b;}
    .wrap{max-width:1100px;margin:28px auto;padding:16px;}
    header{display:flex;align-items:center;justify-content:space-between;padding:8px 0;}
    h1{margin:0;font-size:22px;color:var(--green);}
    .controls{display:flex;gap:12px;align-items:center;margin:14px 0;}
    .btn{background:var(--green);color:#fff;padding:10px 14px;border-radius:10px;border:none;cursor:pointer;}
    .search{padding:10px;border-radius:10px;border:1px solid #ddd;width:260px}
    .main{display:grid;grid-template-columns: 1fr 380px;gap:16px}
    #map{height:640px;border-radius:14px;border:1px solid #e6efe8;box-shadow:0 10px 30px rgba(8,40,20,0.06)}
    .side{
      background:var(--card-bg); padding:14px;border-radius:12px; height:640px; overflow:auto;
      box-shadow:0 6px 20px rgba(8,40,20,0.06)
    }
    .donation{padding:12px;border-radius:10px;border:1px solid #eef5ef;margin-bottom:12px;}
    .donation h3{margin:0 0 6px 0;font-size:16px;color:var(--green)}
    .meta{font-size:13px;color:var(--muted);margin-bottom:6px}
    .small{font-size:12px;color:#666}
    .distance{font-weight:700;color:var(--green)}
    .empty{color:#666;padding:12px;border-radius:8px;background:#fbfffb}
    @media(max-width:980px){ .main{grid-template-columns:1fr} .side{height:360px} #map{height:360px} }
  </style>
</head>
<body>
  <div class="wrap">
    <header>
      <div>
        <a href="reporter_homepage.php" style="text-decoration:none;color:var(--green);font-weight:700">← Back</a>
      </div>
      <h1>Find Food Nearby</h1>
      <div></div>
    </header>

    <div class="controls">
      <input id="search" class="search" type="text" placeholder="Search by restaurant name or description (optional)">
      <button id="btnUseLocation" class="btn">Use my location</button>
      <select id="radius" class="search" style="width:auto;">
        <option value="1">Within 1 km</option>
        <option value="3" selected>Within 3 km</option>
        <option value="5">Within 5 km</option>
        <option value="10">Within 10 km</option>
      </select>
      <button id="btnSearch" class="btn">Search</button>
    </div>

    <div class="main">
      <div id="map"></div>

      <aside class="side">
        <div id="listContainer">
          <div class="empty">Enter search terms or click <strong>Use my location</strong> to find nearby donations.</div>
        </div>
      </aside>
    </div>
  </div>

<script>
let map = L.map('map').setView([20.5937,78.9629], 5); // India default view
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{
  maxZoom: 19,
  attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

let markersLayer = L.layerGroup().addTo(map);
let userMarker = null;
let userLocation = null;

function showError(msg){ alert(msg); }

// --- NEW: normalize coordinates & auto-fix swapped lat/lng ---
function normalizeCoords(lat, lng) {
  let la = parseFloat(lat);
  let ln = parseFloat(lng);
  if (isNaN(la) || isNaN(ln)) return null;

  // If looks like they are swapped (very common: lat=77, lng=12 etc for India),
  // then fix it. Lat should be roughly -90..90, lng -180..180
  if (Math.abs(la) > 60 && Math.abs(ln) < 60) {
    const tmp = la;
    la = ln;
    ln = tmp;
  }
  return { lat: la, lng: ln };
}

function renderList(items, userLat, userLng) {
  const ct = document.getElementById('listContainer');
  ct.innerHTML = '';
  if (!items || items.length === 0) {
    ct.innerHTML = '<div class="empty">No donations found nearby.</div>';
    return;
  }
  items.forEach(item=>{
    const coords = normalizeCoords(item.lat, item.lng);
    const safeDistance = item.distance ? Number(item.distance).toFixed(2) : '—';

    const div = document.createElement('div');
    div.className = 'donation';
    div.innerHTML = `
      <h3>${escapeHtml(item.name || 'Unnamed')}</h3>
      <div class="meta">${escapeHtml(item.address || '')}</div>
      <div class="small">${escapeHtml(item.description || '')}</div>
      <div style="margin-top:8px;display:flex;justify-content:space-between;align-items:center">
        <div class="distance">${safeDistance} km</div>
        ${
          coords
            ? `<div><a href="javascript:void(0)" onclick="centerTo(${coords.lat},${coords.lng})" style="text-decoration:none" class="btn">Show on map</a></div>`
            : `<div class="small" style="color:#999">No location</div>`
        }
      </div>
    `;
    ct.appendChild(div);
  });
}

// safe escape
function escapeHtml(s){ return String(s||'').replace(/[&<>"'`]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','`':'&#96;'}[c])); }

function centerTo(lat,lng){
  map.setView([lat,lng],16);
}

// fetch results from server
async function searchNearby(lat,lng,radiusKm, q){
  const params = new URLSearchParams();
  // If lat/lng are provided (numeric), include them to get distance-based results.
  if (typeof lat === 'number' && !isNaN(lat) && typeof lng === 'number' && !isNaN(lng)) {
    params.set('lat', lat);
    params.set('lng', lng);
    params.set('radius', radiusKm);
    if (q) params.set('q', q);
  } else {
    // No location — perform global text search using 'q' parameter.
    if (q) params.set('q', q);
    else params.set('q', '');
    params.set('text_only', '1');
  }
  const url = 'find_food_api.php?' + params.toString();
  const res = await fetch(url);
  if (!res.ok) {
    const txt = await res.text();
    showError('Server error: ' + txt);
    return [];
  }
  const data = await res.json();
  return data;
}

async function doSearch(useUser=false){
  const q = document.getElementById('search').value.trim();
  const radius = Number(document.getElementById('radius').value);
  let lat, lng;
  if (useUser && userLocation) {
    lat = userLocation.lat; lng = userLocation.lng;
  } else if (userLocation) {
    lat = userLocation.lat; lng = userLocation.lng;
  } else {
    // try browser geolocation
    try {
      const pos = await new Promise((res,rej)=> navigator.geolocation.getCurrentPosition(res,rej,{enableHighAccuracy:true}));
      lat = pos.coords.latitude; lng = pos.coords.longitude;
      userLocation = {lat,lng};
      if (userMarker){ map.removeLayer(userMarker); userMarker=null; }
      userMarker = L.marker([lat,lng], {title:'Your location'}).addTo(map);
      map.setView([lat,lng], 14);
    } catch(err){
      showError('Could not get your location. Please allow location access or enter a location.');
      return;
    }
  }

  // call API
  const items = await searchNearby(lat,lng,radius,q);

  // update map markers
  markersLayer.clearLayers();

  const boundsPoints = [];

  if (userLocation) {
    boundsPoints.push([userLocation.lat, userLocation.lng]);
  }

  items.forEach(it=>{
    const coords = normalizeCoords(it.lat, it.lng);
    if (!coords) return;
    const mk = L.marker([coords.lat, coords.lng]).addTo(markersLayer)
      .bindPopup(
        `<strong>${escapeHtml(it.name||'Unnamed')}</strong><br>`+
        `${escapeHtml(it.description||'')}<br>`+
        `<small>${escapeHtml(it.address||'')}</small><br>`+
        `<b>${it.distance ? Number(it.distance).toFixed(2) : '—'} km</b>`
      );
    boundsPoints.push([coords.lat, coords.lng]);
  });

  // Fit map so that both user + donations are visible
  if (boundsPoints.length > 0) {
    const bounds = L.latLngBounds(boundsPoints);
    map.fitBounds(bounds, { padding: [40, 40] });
  }

  renderList(items, lat, lng);
}

/* event handlers */
document.getElementById('btnUseLocation').addEventListener('click', async ()=>{
  try {
    const pos = await new Promise((res,rej)=> navigator.geolocation.getCurrentPosition(res,rej,{enableHighAccuracy:true}));
    userLocation = {lat: pos.coords.latitude, lng: pos.coords.longitude};
    if (userMarker) { map.removeLayer(userMarker); userMarker=null; }
    userMarker = L.marker([userLocation.lat,userLocation.lng], {title:'Your location'}).addTo(map);
    map.setView([userLocation.lat,userLocation.lng], 14);
    await doSearch(true);
  } catch(err){
    showError('Unable to access location. Allow location permission or try again.');
  }
});

document.getElementById('btnSearch').addEventListener('click', ()=> doSearch(false) );

// small initial search if user allows
(function tryAutoLocate(){
  if (!navigator.geolocation) return;
  navigator.geolocation.getCurrentPosition(async (pos)=>{
    userLocation = {lat: pos.coords.latitude, lng: pos.coords.longitude};
    userMarker = L.marker([userLocation.lat,userLocation.lng], {title:'Your location'}).addTo(map);
    map.setView([userLocation.lat,userLocation.lng], 13);
    await doSearch(true);
  }, ()=>{ /* ignore */ }, {timeout:3000});
})();
</script>

</body>
</html>
