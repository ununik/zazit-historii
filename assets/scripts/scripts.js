$ = jQuery;
$(document).ready(function(){
        //OPEN MENU
    $('.events_wrapper').css('height', $('.events_wrapper').css('width') );

    $('#footer_menu_wrapper').css('top', '-'+$('#footer_menu_wrapper ul').css('height'));
    $('#main-footer').css('top', $('#footer_menu_wrapper ul').css('height'));

    var heights = $(".half_page").map(function ()
    {
        return $(this).height();
    }).get(),
    maxHeight = Math.max.apply(null, heights);
    $('.half_page').css('height', maxHeight );
    $('#menu_toggle').click(function(){
        $(this).toggleClass('open');
        $('.main-menu-list').toggleClass('open-menu');
        $('#navigation-panel').toggleClass('open-panel');
    });
    $('.current_user').click(function(){
        $('.user_menu').toggle();
    });
    $('.parent_toggle').click(function(){
        var toggleInfo = !($(this).hasClass( "return_menu" ))

        if (toggleInfo) {
            $(this).closest('li').find('ul .inner_link').first().toggle(true);
            $(this).closest('li').find('ul .parent_toggle').first().toggle(true);
        } else {
            $(this).closest('li').find('ul .inner_link').toggle(false);
            $(this).closest('li').find('ul .parent_toggle').toggle(false);
            $(this).closest('li').find('.return_menu').removeClass('return_menu');
        }
        $(this).toggleClass('return_menu');
    });
    $('.remove').click(function( event ){
        var message = $(this).data('remove');
        if (confirm(message)) {
            // Save it!
        } else {
            event.preventDefault()
        }
    });

    $('.navigation_title').click( function (){

        $('.navigation_title_toggle').toggleClass('return_menu');
        if ($(this).data('open') == 'off') {
            $(this).data('open', 'on')
            $('#navigation-panel').css('height', 'auto');
        } else {
            $(this).data('open', 'off');
            $('#navigation-panel').css('height', '40px');
        }
    })

    $('.partner-wrapper').click(function () {
        var id = $(this).data('id')

        var data = {
            'action': 'ad_click',
            'ad_id': id
        };

        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        jQuery.post(ajax_url, data, function(response) {});
    })



    $(window).scroll(function(){
        if($(this).scrollTop() >= 10) {
            $('.small_icon').css('left', '1px');
            $('.site-title').css('top', '-80px');
        }
        if($(this).scrollTop() < 10) {
            $('.small_icon').css('left', '-41px');
            $('.site-title').css('top', '0px');
        }
    });

    $('.current_link').closest('.main-parent').find('.inner_link').toggle(true);
    $('.current_link').closest('.main-parent').find('.parent_toggle').toggle(true);
    $('.current_link').closest('.main-parent').find('.parent_toggle').toggleClass('return_menu', true);
    $('.current_link').closest('.term-navigation').find('.term-navigation').find('.inner_link').toggle(true);
    $('.current_link').closest('.term-navigation').find('.term-navigation').find('.parent_toggle').toggleClass('return_menu', true);

    $('.remove_image').click(function() {
        $.post(ajax_url, {
            action: 'delete_attachment',
            fileId: $('.remove_image').data('id')
        }).always(function() {
            $preview = $('.remove_image_wrapper');
            $preview.fadeOut(function() {
                return $preview.off().remove()
            })
        })
        $('#file_upload_data').val('');
    })

    function show_map_picker() {
        if ($('.map_location').prop('checked') == true ) {
            $('#map_picker').toggle(true);

            $('#map_picker').locationpicker(
                {
                    location: {
                        latitude: $('#lat_value').val(),
                        longitude: $('#lng_value').val(),
                    },
                    zoom: 7,
                    inputBinding: {
                        latitudeInput: $('#lat_value'),
                        longitudeInput: $('#lng_value'),
                        locationNameInput: $('#location_name_value'),
                    },
                    enableAutocomplete: true,
                    enableAutocompleteBlur: true,
                    markerIcon: $('#map_picker').data('maker'),
                }
            );
        } else {
            $('#map_picker').toggle(false);
        }
    }

    show_map_picker();
    $('.map_location').click(function() {
        show_map_picker();
    })
});

const $mapEl = $('#g-map')

var home_path = '';
function initAutocomplete(response) {
    $('#g-map').css('height', Math.round(parseInt($('#g-map').css('width')) / 2))
    $('#g-map').css('left', 0);
    $('#g-map').css('margin', 0);

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
             if (item.date != '') {
                 contentString += '<div class="map_date">' + item.date + '</div>'
             }
            contentString += '</a>'+
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

//////////////////////////////////////////// NEW DESIGN

$(document).ready(function () {
    // calculate element sizes here is accurate because the entire page has been loaded
    $(window).load(function(){
        $('.next_event_page').click(function( event ){
            $('.loader').toggleClass('loader-hide', false);
            var data = {
                action: 'next_event_page',
                page: $(this).data('page'),
                age: $(this).data('ages'),
                search: $(this).data('search'),
            }
            jQuery.getJSON(ajax_url, data, function(response) {
                if (response.next_page == false) {
                    $('.next_event_page').toggle(false);
                }

                $('.events-rows').append(response.content);
                $('.loader').toggleClass('loader-hide', true);
                next_page = $('.next_event_page').data('page') + 1;
                $('.next_event_page').data('page', next_page);
                fixEventsCards();
                fixFooter();
            });
        });


        $("#show_hide_map").click(function () {
            $('.map').toggleClass('map-hide')
            fixFooter();
        })

        function fixFooter(){
            $('#content').css('margin-bottom', $('#main-footer').height()+'px')
            var windowHeight = $(window).height();
            var bodyHeight = $('body').height();
            var footerBottom = $('#main-footer').position().top + $('#main-footer').outerHeight(true);

            if(footerBottom <= windowHeight){
                // slam the footer to the bottom
                $('#main-footer').css("position", "absolute");
                $('#main-footer').css("left", 0);
                $('#main-footer').css("right", 0);
                $('#main-footer').css("bottom", 0);
            } else {
                $('#main-footer').css("position", "relative");
                $('#main-footer').css("left", 0);
                $('#main-footer').css("right", 0);
                $('#main-footer').css("bottom", 0);
            }
        }

        function fixEventsCards() {
            var width = $('.archive_events_thumbnail').width();
            $('.archive_events_thumbnail').css('height', parseInt(width*2/3)+'px');
            $('.events_wrapper').css('height', $('.events_wrapper').width()+'px');
        }

        fixEventsCards();
        fixFooter();

        $(window).resize(function(){
            fixEventsCards();
            fixFooter();
        });

        $('.closebtn').click(function () {
            var type = $(this).data('type');
            var id = $(this).data('id');
            if(type == 'cookies') {
                document.cookie = "alert-"+id+"=AGREE; path=/";
            }

            $(this).closest('.alert').toggle(false);
        })
    });
});