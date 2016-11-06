/**
 *  CLWeb // main.js
 */

$(document).ready(function(){
    // initialize ErrorBot()
    var errorBot = new ErrorBot();

    // check if advanced stats should be started automatically
    if(window.location.hash === '#advanced')
    {
        Statscream.startAdvancedStats();
    }
    else
    {
        Statscream.startBasicStats()
    }
});