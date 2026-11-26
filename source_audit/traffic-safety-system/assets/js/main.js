document.addEventListener('DOMContentLoaded', () => {
    const menuButton = document.querySelector('.menu-toggle');
    const nav = document.querySelector('.main-nav');
    if (menuButton && nav) {
        menuButton.addEventListener('click', () => {
            const open = nav.classList.toggle('is-open');
            menuButton.setAttribute('aria-expanded', String(open));
        });
    }

    const description = document.getElementById('description');
    const count = document.getElementById('description-count');
    if (description && count) {
        const updateCount = () => { count.textContent = String(description.value.length); };
        description.addEventListener('input', updateCount);
        updateCount();
    }

    document.querySelectorAll('[data-copy]').forEach((button) => {
        button.addEventListener('click', async () => {
            const value = button.getAttribute('data-copy') || '';
            try {
                await navigator.clipboard.writeText(value);
                const original = button.textContent;
                button.textContent = 'Copied!';
                window.setTimeout(() => { button.textContent = original; }, 1600);
            } catch (_) {
                window.prompt('Copy this reference number:', value);
            }
        });
    });

    document.querySelectorAll('[data-confirm]').forEach((element) => {
        element.addEventListener('click', (event) => {
            const message = element.getAttribute('data-confirm') || 'Continue?';
            if (!window.confirm(message)) event.preventDefault();
        });
    });

    const locationButton = document.getElementById('capture-location');
    const mapElement = document.getElementById('incident-map');
    let incidentMap = null;
    let incidentMarker = null;

    const setLocation = (latitude, longitude, sourceText) => {
        document.getElementById('latitude').value = Number(latitude).toFixed(7);
        document.getElementById('longitude').value = Number(longitude).toFixed(7);
        const status = document.getElementById('location-status');
        if (status) status.textContent = sourceText + ': ' + Number(latitude).toFixed(5) + ', ' + Number(longitude).toFixed(5);
        if (incidentMap) {
            const point = [Number(latitude), Number(longitude)];
            incidentMap.setView(point, 16);
            if (incidentMarker) incidentMarker.setLatLng(point);
            else incidentMarker = L.marker(point).addTo(incidentMap);
        }
    };

    if (mapElement && window.L) {
        incidentMap = L.map(mapElement).setView([20.5937, 78.9629], 5);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom:19, attribution:'&copy; OpenStreetMap contributors'}).addTo(incidentMap);
        incidentMap.on('click', (event) => setLocation(event.latlng.lat, event.latlng.lng, 'Map point selected'));
    }

    if (locationButton) {
        locationButton.addEventListener('click', () => {
            const status = document.getElementById('location-status');
            if (!window.isSecureContext) {
                status.textContent = 'Automatic capture requires localhost or HTTPS. Click the map instead.';
                if (incidentMap) incidentMap.scrollIntoView({behavior:'smooth', block:'center'});
                return;
            }
            if (!navigator.geolocation) {
                status.textContent = 'Geolocation is unsupported. Click the map to select a point.';
                return;
            }
            locationButton.disabled = true;
            status.textContent = 'Requesting location permission...';
            navigator.geolocation.getCurrentPosition((position) => {
                setLocation(position.coords.latitude, position.coords.longitude, 'Location captured');
                locationButton.disabled = false;
            }, (error) => {
                const messages = {1:'Location permission denied. Click the map to select a point.', 2:'Location unavailable. Click the map to select a point.', 3:'Location request timed out. Click the map to select a point.'};
                status.textContent = messages[error.code] || 'Could not capture location. Click the map instead.';
                locationButton.disabled = false;
                if (incidentMap) incidentMap.scrollIntoView({behavior:'smooth', block:'center'});
            }, {enableHighAccuracy:true, timeout:15000, maximumAge:60000});
        });
    }
});
