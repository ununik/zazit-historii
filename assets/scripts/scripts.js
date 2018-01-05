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

var home_path = '';
function initAutocomplete(response) {
    if (typeof google === 'undefined') return
    home_path = response.home_path;
    
    var markerIcon = {
      url: home_path+'/assets/images/icons/map_sword.svg',
      scaledSize: new google.maps.Size(32, 32),
      origin: new google.maps.Point(0, 0),
      //anchor: new google.maps.Point(0,0),
      labelOrigin:  new google.maps.Point(16,-6),
      labelClass: "labels"
    };

    const map = new google.maps.Map($mapEl[0])

    // LIST
    const bounds = new google.maps.LatLngBounds()
    const markers = response.response.map(function (item, i) { 
        const location = item.location
        if (location.lat && location.lng) {
            var contentString = '<div id="map_content" class="map_text">'+
            '<h3>'+item.name+'</h3>'+
            '<div>'+item.date+'</div>'+
            '</div>';
            var infowindow = new google.maps.InfoWindow({
                content: contentString
            });

            const marker = new google.maps.Marker({
                position: location,
                title: item.name,
                icon: markerIcon,
                map: map,
                label: {
                    text: item.name,
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
    /*google.maps.event.addListener(map, 'click', function() {
        infowindow.close();
    }); */
    
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
    } catch (e) {
        //$mapEl.animate({opacity: 0, height: 0}, 500)
        console.warn(e)
    }          
}