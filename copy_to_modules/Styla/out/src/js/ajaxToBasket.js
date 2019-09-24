/**
 * Ajax to Basket for Styla
 *
 * -------------------------------------------------------------------------------------------------------------- */

/* Functions
 * ============================================================================================================== */
/**
 * Styla_ajaxToBasket
 * -----------------------------------------------------------------------------------------------------------------
 * Global function for adding articles to basket.
 * 
 * @param OXID {String} OXID of article-variant
 * @param amount {Integer} Amount of Articles to be added to the basket.
 * @param persParams {null|String|Object} Additional Parameters
 * @param onSuccess {Function} Callback Function on success: function (response) {} 
 * @param onError {Function} Callback Function on error: function (response) { console.error(response.error); } 
 *
 * @return null
 */

Styla_ajaxToBasket = function(OXID, amount, persParams, onSuccess, onError) {

    // variables
    var stoken = $('#js-articleToBasketLang input[name="stoken"]').val(),
        baseParams,
        formURL,
        urlParamString,
        encodedObject;

    // set amount if not given
    if (amount === null || typeof amount == 'undefined') {
        amount = '1';
    }

    // build base parameters
    baseParams = {
        stoken: stoken,
        actcontrol: 'Styla_AjaxBasket',
        cl: 'Styla_AjaxBasket',
        aid: OXID,
        fnc: 'toBasket',
        am: amount
    };

    // add addtionals persParams, if given
    if (typeof persParams === 'undefined' || persParams === 0 || persParams === null) {
        encodedObject = $.param(baseParams);
        urlParamString = decodeURIComponent(encodedObject);
    } else if (typeof persParams === 'string') {
        encodedObject = $.param(baseParams);
        urlParamString = decodeURIComponent(encodedObject) + '&' + persParams;
    } else if (typeof persParams === 'object') {
        // merge baseparams and persparams
        jQuery.extend(baseParams, persParams);
        encodedObject = $.param(baseParams);
        urlParamString = decodeURIComponent(encodedObject);
    }

    // build url
    formURL = Styla_ajaxToBasket_baseURL + '?' + urlParamString;

    // ajax call
    $.ajax({
        type: 'POST',
        url:  formURL,
        
        // on ajax success
        success: function(response) {
            // on response success
            if (response.success) {
                // success callback, if given
                if (typeof onSuccess === 'function' && onSuccess) {
                    onSuccess(response, function () {
                        onSuccess(response);
                    })

                // info log, if no callback is given
                } else {
                    console.info("Styla Add2Basket successfull");
                }

            // on response error (oxid)
            } else  {
                // error callback, if given
                if (typeof onError === 'function' && onError) {
                    onError(response);

                // error log, if no callback is given
                } else {
                    console.error("Styla Add2Basket failed:", response.error || "Unknown Error");
                }
            }
        },

        // on ajax error (http)
        error: function( response) {
            // error callback, if given
            if (typeof onError === 'function' && onError) {
                onError(response);

            // error log, if no callback is given
            } else {
                console.error("Styla Add2Basket failed:", response.error|| "Unknown Error");
            }
        }
    });
};