/**
 *  CLWeb // datatron.js
 *
 *  A JS library for interfacing with data models (e.g. listings) and APIs
 */

var DataTron = function(){};

DataTron.prototype.setMapMarker = function(map, pos){
    /*
        Add marker to given Map() obj
     */

    // create marker
    var marker = new google.maps.Marker({
        position: pos,
        map: map
    });
};

DataTron.prototype.setListingsAsMarkers = function(map){
    /*
        Retrieve listing data and set as markers to given map
     */

    var DT = this;

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
                        // set pos data from geotag
                        // var listingGeoTag = listing.geotag.replace(/[{()}]/g, '');

                        // convert geotag string to marker location array
                        var listingPosData = listing.geotag.replace(/[{()}]/g, '').split(',', 2);
                        var listingPos = {
                            lat: parseFloat(listingPosData[0]),
                            lng: parseFloat(listingPosData[1])
                        };
                        // console.log(listingPos);

                        // create marker
                        DT.setMapMarker(map, listingPos);
                    }
                });
            }
        },
        error: function(){
            console.log('[ERROR] could not load listing data!');
        }
    });
};