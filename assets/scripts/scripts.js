$ = jQuery;
$(document).ready(function(){
    //OPEN MENU
    $('#menu_toggle').click(function(){
        $(this).toggleClass('open');
        $('.main-menu-list').toggleClass('open-menu');
        $('#navigation-panel').toggleClass('open-panel');
    });
});

const $mapEl = $('#g-map')

var markerIcon = {
    url: 'http://violinorum.staging-development.com/wp-content/plugins/hd_violinorum/assets/images/violinorum_map-marker.svg',
    scaledSize: new google.maps.Size(32, 32),
    origin: new google.maps.Point(0, 0),
    anchor: new google.maps.Point(32,65),
    labelOrigin:  new google.maps.Point(16,-6),
    labelClass: "labels"
};

function initAutocomplete(response) {
    if (typeof google === 'undefined') return
    const map = new google.maps.Map($mapEl[0])

    google.maps.event.addListener(map, 'click', function() {
        infowindow.close();
    });


    // LIST
    const bounds = new google.maps.LatLngBounds()
    const markers = response.map(function (item, i) {
        const location = item.location
        if (location.lat && location.lng) {
            var contentString = '<div id="content" class="map">test'+
                '</div>';
            var infowindow = new google.maps.InfoWindow({
                content: contentString
            });

            const marker = new google.maps.Marker({
                position: location,
                title: item.name,
                icon: markerIcon,
                label: {
                    text: item.value,
                    color: 'black',
                    fontSize: '12px',
                    fontWeight: 'bold',
                }
            })
            marker.addListener('click', function() {
                infowindow.open(map, marker);
            });
            // extend the bounds to include each marker's position
            bounds.extend(marker.position)
            return marker
        }
    })

    try {
        // This is needed to set the zoom after fitbounds,
        google.maps.event.addListener(map, 'zoom_changed', function () {
            const zoomChangeBoundsListener =
                google.maps.event.addListener(map, 'bounds_changed', function () {
                    if (this.getZoom() > 15 && this.initialZoom === true) {
                        // Change max/min zoom here
                        this.setZoom(15)
                        this.initialZoom = false
                    }
                    google.maps.event.removeListener(zoomChangeBoundsListener)
                })
        })
        map.initialZoom = true
        // now fit the map to the newly inclusive bounds
        map.fitBounds(bounds)
        new MarkerClusterer(map, markers, {
            textColor: 'white',
            imagePath: 'http://violinorum.staging-development.com/wp-content/plugins/hd_violinorum/assets/images/violinorum_map-marker_large'
        })
    } catch (e) {
        $mapEl.animate({opacity: 0, height: 0}, 500)
        console.warn(e)
    }
}