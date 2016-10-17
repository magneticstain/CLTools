/**
 *  CLWeb // main.js
 */

var GOOGLE_MAPS_API_KEY = 'AIzaSyBM_drWAJQL9PqXD_IGMrjv-zKg4Yu12oY';

function generateListingMap()
{
    /*
        Generates a map using the Google Maps JS API and data from the CLData API
     */

    // generate Map()
    var map = new google.maps.Map(
        document.getElementById('map'), {
            center: {
                lat: 0,
                lng: 0
            },
            zoom: 9
        }
    );

    // generate DataTron()
    var datatron = new DataTron();

    // add listings as markers
    datatron.setListingsAsMarkers(map);

    // get current location (via HTML5 ^_^)
    var pos = {
        lat: 0,
        lng: 0
    };
    if(navigator.geolocation)
    {
        navigator.geolocation.getCurrentPosition(function(currPos){
            // set position as current location
            pos.lat = currPos.coords.latitude;
            pos.lng = currPos.coords.longitude;

            // set center of map to current location
            map.setCenter(pos);
        });
    }
    else
    {
        // center on default position
        map.setCenter(pos);
    }
}

$(document).ready(function(){
    // generate ErrorBot()
    var errorBot = new ErrorBot();

    // import Google Maps API and generate listing map
    $.ajax({
        url: 'https://maps.googleapis.com/maps/api/js?key=' + GOOGLE_MAPS_API_KEY + '&callback=generateListingMap',
        crossdomain: true,
        dataType: 'script',
        timeout: 8000,
        error: function(){
            // couldn't load Google Maps JS API
            errorBot.generateGoogleMapsAPIError();
        }
    });
});