(function ($) {
	"use strict";

	$(document).ready(function () {

		booknetic.initMultilangInput($("#input_location_name"), 'locations', 'name');
		booknetic.initMultilangInput($("#input_address"), 'locations', 'address');
		booknetic.initMultilangInput($("#input_note"), 'locations', 'notes');

		$('.fs-modal').on('click', '#addLocationSave', function () {
			let location_name = $("#input_location_name").val(),
				phone = $("#input_phone").val(),
				address = $("#input_address").val(),
				note = $("#input_note").val(),
				image = $("#input_image")[0].files[0];

			if (location_name === '') {
				booknetic.toast(booknetic.__('fill_all_required'), 'unsuccess');
				return;
			}

			const id = $("#add_new_JS").data('location-id');
			const data = new FormData();

			data.append('location_name', location_name);
			data.append('address', address);
			data.append('phone', phone);
			data.append('note', note);
			data.append('image', image);
			data.append('latitude', marker && marker.getLatLng ? marker.getLatLng().lat : '');
			data.append('longitude', marker && marker.getLatLng ? marker.getLatLng().lng : '');
			data.append('address_components', addressComponenets || '');
			data.append('translations', booknetic.getTranslationData($('.fs-modal').first()));

			const onSave = () => {
				const dataTable = $("#fs_data_table_div");

				booknetic.modalHide($(".fs-modal"));

				if (dataTable.length > 0) {
					booknetic.dataTable.reload(dataTable);
				}
			}

			if (!id) {
				booknetic.ajax('locations.create', data, onSave);
				return;
			}

			data.append('id', id);
			booknetic.ajax('locations.update', data, onSave);
		}).on('click', '#hideLocationBtn', function () {
			const id = $("#add_new_JS").data('location-id');
			booknetic.ajax('toggleVisibility', { id }, function () {
				booknetic.modalHide($(".fs-modal"));
				booknetic.dataTable.reload($("#fs_data_table_div"));
			});
		});

		let latitude = $('#add_new_JS').data('latitude') || 0;
		let longitude = $('#add_new_JS').data('longitude') || 0;
		let zoom = latitude > 0 ? 15 : 2;

		let map, marker, addressComponenets;

		function loadLeaflet() {
			return new Promise((resolve, reject) => {
				if (typeof L !== 'undefined') {
					resolve();
					return;
				}

				const link = document.createElement('link');
				link.rel = 'stylesheet';
				link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
				link.integrity = 'sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=';
				link.crossOrigin = '';
				document.head.appendChild(link);

				const script = document.createElement('script');
				script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
				script.integrity = 'sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=';
				script.crossOrigin = '';
				script.onload = resolve;
				script.onerror = reject;
				document.head.appendChild(script);
			});
		}

		function initMap() {
			const defaultLocation = [latitude, longitude];

			map = L.map('divmap').setView(defaultLocation, zoom);

			L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
				attribution: '© OpenStreetMap contributors'
			}).addTo(map);

			marker = L.marker(defaultLocation, { draggable: true }).addTo(map);

			if (!(latitude > 0)) {
				if (navigator.geolocation) {
					navigator.geolocation.getCurrentPosition(
						(position) => {
							const userLocation = [
								position.coords.latitude,
								position.coords.longitude
							];

							map.setView(userLocation, 15);
							marker.setLatLng(userLocation);

							reverseGeocode(userLocation);
						},
						(error) => {
							console.warn("Geolocation failed or was denied. Showing global view.");
						}
					);
				} else {
					console.warn("Geolocation is not supported by this browser. Showing global view.");
				}
			} else {
				reverseGeocode(defaultLocation);
			}

			marker.on('dragend', function () {
				const position = marker.getLatLng();
				reverseGeocode([position.lat, position.lng]);
			});
			map.on('click', function (e) {
				marker.setLatLng(e.latlng);
				reverseGeocode([e.latlng.lat, e.latlng.lng]);
			});

			setupAddressAutocomplete();
		}

		function setupAddressAutocomplete() {
			const input = document.getElementById("input_address");
			let timeoutId;

			input.addEventListener('input', function (e) {
				clearTimeout(timeoutId);
				const query = e.target.value;

				if (query.length < 3) return;

				timeoutId = setTimeout(() => {
					searchAddress(query);
				}, 500);
			});
		}

		function searchAddress(query) {
			fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5`)
				.then(response => response.json())
				.then(data => {
					if (data && data.length > 0) {
						const firstResult = data[0];
						const location = [parseFloat(firstResult.lat), parseFloat(firstResult.lon)];

						map.setView(location, 15);
						marker.setLatLng(location);
						updateDetails(firstResult);

						const addressInput = document.getElementById("input_address");
						if (addressInput && firstResult.display_name) {
							addressInput.value = firstResult.display_name;
						}
					}
				})
				.catch(error => console.error('Error searching address:', error));
		}

		function reverseGeocode(location) {
			fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${location[0]}&lon=${location[1]}`)
				.then(response => response.json())
				.then(data => {
					if (data && data.address) {
						updateDetails(data);

						const addressInput = document.getElementById("input_address");
						if (addressInput && data.display_name) {
							addressInput.value = data.display_name;
						}
					} else {
						addressComponenets = '';
					}
				})
				.catch(error => {
					console.error('Error reverse geocoding:', error);
					addressComponenets = '';
				});
		}

		function updateDetails(place) {
			addressComponenets = '';

			if (place.address) {
				const components = [];
				if (place.address.road) components.push(place.address.road);
				if (place.address.house_number) components.push(place.address.house_number);
				if (place.address.neighbourhood) components.push(place.address.neighbourhood);
				if (place.address.suburb) components.push(place.address.suburb);
				if (place.address.city) components.push(place.address.city);
				if (place.address.state) components.push(place.address.state);
				if (place.address.country) components.push(place.address.country);
				if (place.address.postcode) components.push(place.address.postcode);

				addressComponenets = components.join('>');
			} else if (place.display_name) {
				addressComponenets = place.display_name.replace(/, /g, '>');
			}
		}

		loadLeaflet()
			.then(() => {
				setTimeout(() => {
					initMap();
				}, 100);
			})
			.catch(error => {
				console.error('Failed to load Leaflet:', error);
				booknetic.toast('Failed to load map library', 'unsuccess');
			});

	});

})(jQuery);