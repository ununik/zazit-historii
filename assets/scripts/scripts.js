$ = jQuery;
$(document).ready(function(){
    //OPEN MENU
    $('.events_wrapper').css('height', $('.events_wrapper').css('width') );
    $('#menu_toggle').click(function(){
        $(this).toggleClass('open');
        $('.main-menu-list').toggleClass('open-menu');
        $('#navigation-panel').toggleClass('open-panel');
    });
    $('.current_user').click(function(){
        $('.user_menu').toggle();
    });
    $('.remove').click(function( event ){
        var message = $(this).data('remove');
        if (confirm(message)) {
            // Save it!
        } else {
            event.preventDefault()
        }
    });

    $('.partner-wrapper').click(function () {
        var id = $(this).data('id')

        var data = {
            'action': 'ad_click',
            'ad_id': id
        };

        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        jQuery.post(ajax_url, data, function(response) {});
    })
});

const $mapEl = $('#g-map')

var home_path = '';
function initAutocomplete(response) {
    if (response.response.length > 0) {
        $('#g-map').css('height', Math.round(parseInt($('#g-map').css('width')) * 0.5))
    }
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
            '<a href="'+item.link+'" target="_blank">' + 
            '<h3>'+item.name+'</h3>';
             if (item.city != '') {
                contentString += '<div class="map_city">'+item.city+'</div>'
             }
            contentString += '<div class="map_date">'+item.date+'</div>'+
            '</a>'+
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
    
    var clusterStyles = [
      {
        textColor: 'black',
        url: home_path+'/assets/images/icons/map_sword_cluster1.svg',
        height: 50,
        width: 50
      },
     {
        textColor: 'black',
        url: home_path+'/assets/images/icons/map_sword_cluster2.svg',
        height: 50,
        width: 50
      },
     {
        textColor: 'white',
        url: home_path+'/assets/images/icons/map_sword_cluster3.svg',
        height: 60,
        width: 60
      },
      {
        textColor: 'white',
        url: home_path+'/assets/images/icons/map_sword_cluster4.svg',
        height: 60,
        width: 60
      },
      {
        textColor: 'white',
        url: home_path+'/assets/images/icons/map_sword_cluster5.svg',
        height: 60,
        width: 60
      }
    ];
    
    var mcOptions = {
    gridSize: 60,
    styles: clusterStyles,
    maxZoom: 15
};

     var markerCluster = new MarkerClusterer(map, markers, mcOptions);

    
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