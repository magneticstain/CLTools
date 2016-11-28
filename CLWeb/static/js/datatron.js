/**
 *  CLWeb // Datatron.js
 *
 *  A JS library for interfacing with data models (e.g. listings) and APIs
 */

var DataTron = function(){};

DataTron.infoWindows = [];

// MAPS
DataTron.closeAllInfoWindows = function(){
    /*
        Close all infoWindow's in this.infoWindows group
     */

    for(var i = 0; i < DataTron.infoWindows.length;i++)
    {
        DataTron.infoWindows[i].close();
    }
};

DataTron.prototype.setMapMarker = function(map, pos, infoWindow){
    /*
        Add marker to given Map() obj
     */

    // create marker
    var marker = new google.maps.Marker({
        position: pos,
        map: map
    });

    // add info window if requested
    if(typeof infoWindow !== 'undefined')
    {
        // add infoWindow to group
        DataTron.infoWindows.push(infoWindow);

        // add event listener for when opening a new map marker
        marker.addListener('click', function(){
            DataTron.closeAllInfoWindows();
            infoWindow.open(map, marker);
        });
    }
};

DataTron.setListingsAsMarkers = function(map){
    /*
        Retrieve listing data and set as markers to given map
     */

    var IW = null;

    // Query CLData for listing data with given parameters
    $.ajax({
        url: '/CLTools/CLData/api/v1/data/all/',
        dataType: 'json',
        success: function(listingData){
            if(listingData.success)
            {
                // traverse through each listing
                $.each(listingData.data, function(i, listing){
                    // if geotag exists, set marker
                    if(listing.geotag !== 'None')
                    {
                        // convert geotag string to marker location array
                        var listingPosData = listing.geotag.replace(/[{()}]/g, '').split(',', 2);
                        var listingPos = {
                            lat: parseFloat(listingPosData[0]),
                            lng: parseFloat(listingPosData[1])
                        };

                        // create InfoWindow instance
                        IW = new google.maps.InfoWindow({
                            content: '<a target="_blank" rel="noopener noreferrer" href="' + listing.url + '">' + listing.name + '</a>' +
                            '<p><strong class="listingLocation">' + listing.location + '</strong>, <i class="listingPrice">$' + listing.price + '</i></p>'
                        });

                        // create and set marker w/ IW
                        DataTron.prototype.setMapMarker(map, listingPos, IW);
                    }
                });
            }
        },
        error: function(){
            var errorBot = new ErrorBot(2, 'could not load listing data');
            errorBot.displayError();
            errorBot.logErrorToConsole();
        }
    });
};

DataTron.generateListingMap = function(){
    /*
        Generates a map using the Google Maps JS API and data from the CLData API
     */

    // generate generic Map()
    var map = new google.maps.Map(
        document.getElementById('map'), {
            center: {
                lat: 0,
                lng: 0
            },
            zoom: 9
        }
    );

    // add listings as markers
    DataTron.setListingsAsMarkers(map);

    // get current location (via HTML5 ^_^)
    var pos = {
        lat: 0,
        lng: 0
    };
    if(navigator.geolocation)
    {
        // user is using a modern browser
        navigator.geolocation.getCurrentPosition(function(currPos){
            // current location retrieved successfully, set as current map position
            pos.lat = currPos.coords.latitude;
            pos.lng = currPos.coords.longitude;
            map.setCenter(pos);
        });
    }
    else
    {
        // user is using a dusty, ancient browser so we'll just center on the default position
        map.setCenter(pos);
    }
};

// STATS
DataTron.formatReturnData = function(dataType, data){
    /*
        Format given data based on specified data type
     */

    var formattedData = null;

    // normalize dataType
    dataType = dataType.toUpperCase();

    switch(dataType){
        case 'CURRENCY':
            // parse as float, limited to 2 decimal places
            formattedData = parseFloat(data).toFixed(2);

            // prepend dollar sign
            formattedData = '$' + formattedData;

            break;
        default:
            // keep input data unmodified
            formattedData = data;
    }

    return formattedData;
};