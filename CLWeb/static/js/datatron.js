/**
 *  CLWeb // datatron.js
 *
 *  A JS library for interfacing with data models (e.g. listings) and APIs
 */

var DataTron = function(){
    this.infoWindows = [];
};

// MAPS
DataTron.closeAllInfoWindows = function(infoWindowGrp){
    /*
        Close all infoWindow's in this.infoWindows group
     */

    for(var i = 0; i < infoWindowGrp.length;i++)
    {
        infoWindowGrp[i].close();
    }
};

DataTron.prototype.setMapMarker = function(map, pos, infoWindow){
    /*
        Add marker to given Map() obj
     */

    var infoWindowGrp = this.infoWindows;

    // create marker
    var marker = new google.maps.Marker({
        position: pos,
        map: map
    });

    // add info window if requested
    if(typeof infoWindow !== 'undefined')
    {
        // add infoWindow to group
        this.infoWindows.push(infoWindow);

        // add event listener
        marker.addListener('click', function(){
            DataTron.closeAllInfoWindows(infoWindowGrp);
            infoWindow.open(map, marker);
        });
    }
};

DataTron.prototype.setListingsAsMarkers = function(map){
    /*
        Retrieve listing data and set as markers to given map
     */

    var DT = this;
    var IW = null;

    // Query CLData for listing data with given parameters
    var apiUrl = '/CLTools/CLData/api/v1/data/all/';

    $.ajax({
        url: apiUrl,
        dataType: 'json',
        success: function(listingData){
            if(listingData.success)
            {
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

                        // create marker
                        DT.setMapMarker(map, listingPos, IW);
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
        case 'DIMENSIONS':

            break;
        default:
            // keep input data unmodified
            formattedData = data;
    }

    return formattedData;
};