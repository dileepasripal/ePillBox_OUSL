<h2>Pharmacy Locator</h2>

<input type="text" id="search-input" placeholder="Enter location or pharmacy name">
<button onclick="searchPharmacies()">Search</button>

<div id="map" style="height: 400px;"></div> 

<div id="pharmacy-details">
    </div>
<script>
    function initMap() {
        const map = new google.maps.Map(document.getElementById("map"), {
            center: { lat: 7.8731, lng: 80.7718 }, // Center the map on Sri Lanka (adjust as needed)
            zoom: 8,
        });

        // Fetch pharmacy data from the server (you'll need to implement this)
        fetch('get_pharmacies.php') 
            .then(response => response.json())
            .then(pharmacies => {
                pharmacies.forEach(pharmacy => {
                    const marker = new google.maps.Marker({
                        position: { lat: pharmacy.latitude, lng: pharmacy.longitude },
                        map: map,
                        title: pharmacy.name
                    });

                    // Add an info window to display pharmacy details when the marker is clicked (optional)
                    const infowindow = new google.maps.InfoWindow({
                        content: `<h4>${pharmacy.name}</h4><p>${pharmacy.address}</p>` 
                    });

                    marker.addListener("click", () => {
                        infowindow.open({
                            anchor: marker,
                            map,
                            shouldFocus: false,
                        });
                    });
                });
            });
    }
    function searchPharmacies() {
        const searchTerm = document.getElementById('search-input').value;

        // Fetch pharmacy data from the server, filtering by search term (you'll need to implement this in get_pharmacies.php)
        fetch(`get_pharmacies.php?search=${searchTerm}`) 
            .then(response => response.json())
            .then(pharmacies => {
                // Clear existing markers 
                markers.forEach(marker => marker.setMap(null));
                markers = []; // Reset the markers array

                pharmacies.forEach(pharmacy => {
                    const marker = new google.maps.Marker({
                        // ... (rest of the marker creation code)
                    });

                    markers.push(marker); // Add the new marker to the array

                    // Add an info window to display pharmacy details when the marker is clicked
                    const infowindow = new google.maps.InfoWindow({
                        content: `
                            <h4>${pharmacy.name}</h4>
                            <p>${pharmacy.address}</p>
                            <p>Contact: ${pharmacy.contact_information}</p> 
                            <p>Hours: ${pharmacy.opening_hours}</p> 
                        ` // Include contact information and opening hours
                    });

                    marker.addListener("click", () => {
                        infowindow.open({
                            anchor: marker,
                            map,
                            shouldFocus: false,
                        });

                        // Display pharmacy details in the side panel
                        displayPharmacyDetails(pharmacy); 
                    });
                });

                // If no pharmacies are found, display a message
                if (pharmacies.length === 0) {
                    document.getElementById('pharmacy-details').innerHTML = '<p>No pharmacies found.</p>';
                }
            });
    }

    let markers = []; // Array to store markers for easy clearing

    function displayPharmacyDetails(pharmacy) {
        const detailsDiv = document.getElementById('pharmacy-details');
        detailsDiv.innerHTML = `
            <h3>${pharmacy.name}</h3>
            <p>${pharmacy.address}</p>
            <p>Contact: ${pharmacy.contact_information}</p>
            <p>Hours: ${pharmacy.opening_hours}</p>
        `;
    }
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap"
        async defer></script>