/**
 *  CLWeb // errorbot.js
 *
 *  A JS library for displaying and delivering errors
 */

var errorModal = $('#errorModal');

var errorSeverities = [
    'INFO',
    'ERROR',
    'CRIT',
    'FATAL'
];

var ErrorBot = function(sev, msg){
    this.setError(sev, msg);
};

ErrorBot.prototype.setError = function(severity, msg){
    // sets characteristics of error
    // severity
    if(typeof severity === 'undefined')
    {
        this.severity = 0;
    }
    else
    {
        this.severity = severity;
    }

    // message
    if(typeof msg !== 'undefined')
    {
        this.msg = msg;
    }
    else
    {
        // set default msg
        this.msg = '|-- NO MSG SET --|';
    }

    this.setErrorModalHTML();
};

ErrorBot.prototype.setErrorModalHTML = function(){
    // sets the inner html of the error modal
    var formattedErrorMsg = '!';

    errorModal.html('<img src="/CLTools/CLWeb/static/media/icons/status/' + errorSeverities[this.severity].toLowerCase() + '.png" title="' + errorSeverities[this.severity] + '" alt="' + errorSeverities[this.severity] + ' status icon"><p>' + this.msg + '!</p>');
};

ErrorBot.prototype.displayError = function(delayTimeMS){
    // check for persistent display
    if(delayTimeMS === 0 || 2 <= this.severity)
    {
        // keep slid down/enable persistent display until user clicks the error modal
        // ( always enable persistent display with CRIT errors )
        errorModal.slideDown();

        // change cursor based on if help article is available (TODO)
        errorModal.css('cursor', 'pointer');
        errorModal.click(function(){
            $(this).slideUp();
        });

        return;
    }

    // check if delay time was provided
    if(typeof delayTimeMS === 'undefined')
    {
        // set to default delay time
        delayTimeMS = 5000;
    }

    // unhides the error modal to user
    errorModal.slideDown().delay(delayTimeMS).slideUp();

    // if the user hovers over the error modal, stay down until they untarget the error modal
    errorModal.hover(function() {
        $(this).stop(true, true);
    }, function(){
        $(this).delay(delayTimeMS).slideUp();
    });
};

ErrorBot.prototype.logErrorToConsole = function(){
    // logs detailed error message to js console
    console.log('[ ' + errorSeverities[this.severity] + ' ] ' + this.msg);
};

/* EXTERNAL / APIs */
ErrorBot.prototype.generateGoogleMapsAPIError = function(){
    // generate a Google Maps API error, which requires special-handling
    $('#map').html('' +
        '<div class="errorWrapper">' +
        '   <p class="error">[ ERROR ]</p>' +
        '   <br />' +
        '   <p class="error">Could not load Google Maps API</p>' +
        '   <br>' +
        '   <p class="error">(ಥ﹏ಥ)</p>' +
        '</div>');
};