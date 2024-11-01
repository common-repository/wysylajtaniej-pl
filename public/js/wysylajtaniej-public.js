(function ($) {
    'use strict';

    $(document).ready(function () {
        $('body').on('change', '.shipping_method', function () {
            jQuery('#wysylajtaniejPoint').val("");
            jQuery('#wysylajtaniejPointName').val("");
        });
        /** get selected point after payment method change */
        $('body').on('click', 'ul.payment_methods li', function () {
            var codOnly = false;
            var payment = jQuery(this).find('input');

            if (payment.val() === 'cod') {
                codOnly = true;
            }
            jQuery('#wysylajtaniejPoint').val("");
            var data = {
                action: 'getPointToPayment',
                cod: codOnly,
                security: jQuery('#wysylajtaniej_setPoint').val(),
            };
            jQuery.post(settings.ajaxurl, data, function (response) {

                if (response.success == true) {
                    if(jQuery('#select-point-container').length){
                        jQuery('#select-point-container').html(response.data.button);
                        jQuery('#wysylajtaniejPoint').val(response.data.code);
                    }
                }
            });
        });
        /*woocommerce place order hook*/
        $("form.woocommerce-checkout")
            .on('checkout_place_order', function() {
                return wysylajtaniejFunction( this );
            } );
        $(document).on('click','.selectPoint',function(){

            currentService = jQuery(this).attr('data-taget');
            var name = jQuery(this).attr('data-name');
            var code = jQuery(this).attr('data-id');

            callbackwysylajtaniej(name,code);

            var resultDiv = jQuery('.selectPlaceholder');
            resultDiv.html('');
        });
    });

})(jQuery);

window.easyPackAsyncInit = function () {
    easyPack.init({
        defaultLocale: 'pl',
        mapType: 'osm',
        searchType: 'osm',
        points: {
            types: ['parcel_locker']
        },
        map: {
            initialTypes: ['parcel_locker']
        }
    });
};
var currentService = null;
/** click in select point link */
function openSelectOption(service, city, street) {
    currentService = service;
    jQuery('#wysylajtaniejService').val(currentService);
    var codOnly = false;
    if (jQuery('input[name="payment_method"]:checked').val() === 'cod') codOnly = true;
    if(service == "Paczkomat"){
        easyPack.modalMap(function(point, modal) {
            modal.closeModal();
            var name = point.name+" | "+point.address.line1;
            var code = point.name;
            callbackwysylajtaniej(name,code);
        }, { width: 500, height: 600,className:'popupMap' });
    }

}
/** save selected point in session */
function callbackwysylajtaniej(name,code) {


    jQuery('#selected-point').text(name);
    var codOnly = false;
    if (jQuery('input[name="payment_method"]:checked').val() === 'cod') codOnly = true;
    var data = {
        action: 'savePoint',
        currentService: currentService,
        name: name,
        code: code,
        cod: codOnly,
        security: jQuery('#wysylajtaniej_setPoint').val(),
    };
    jQuery('#wysylajtaniejPoint').val(code);
    jQuery('#wysylajtaniejPointName').val(name);
    jQuery.post(settings.ajaxurl, data);
}
/** check if point is selected before placing order */
function wysylajtaniejFunction( form ) {

    if(!jQuery('#select-point-container').length) return true;

    if(jQuery('#wysylajtaniejPoint').val()!='') return true;


        if(!jQuery('#select-point').length) return true;
        else{
            jQuery('html, body').animate({ scrollTop: jQuery('#select-point').offset().top - 50}, 500);
            jQuery('#select-point').click();
            return false;
        }
}


function searchDPDPLPoint(){
    searchPoint('DPDPL',jQuery('#searchDPDPLPoint').val());
    return false;
}
function searchPWRPoint(){
    searchPoint('PWR',jQuery('#searchPWRPoint').val());
    return false;
}

function searchPoint(courier,search) {
    var resultDiv = jQuery('.selectPlaceholder');
    resultDiv.html('');

    if(courier && search){
        resultDiv.addClass('loading');
        var data = {
            'action': 'wysylajtaniej_getPoints',
            'courier': courier,
            'search': search,
        };

        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        jQuery.post(settings.ajaxurl, data, function(response) {
            resultDiv.removeClass('loading');
            if(response.success){
                if(response.data.parcels){
                    jQuery.each( response.data.parcels, function( key, value ) {
                        var line = '<div class="parcel-line">' +
                            '<div class="parcel-name">'+(value['name'])+'</div>' +
                            '<div class="parcel-location">'+(value['location'])+'</div>'+
                            '<div class="buttonsSelect"> ' +
                            '<button data-id="'+(value['point_id'])+'" data-taget="'+courier+'"  data-name="'+value['location']+'" class="selectPoint" type="button">'+settings.selectPointLabel+'</button>' +
                        '</div>';
                        resultDiv.append(line);
                    });
                }
            }else{
                if(response.data.errors){
                    jQuery.each( response.data.errors, function( key, value ) {
                        var line = '<div class="parcel-error updated woocommerce-error inline">' +
                            '<div class="parcel-error-name">'+(value)+'</div>' +
                            ' </div>';
                        resultDiv.append(line);
                    });
                }
            }

        });
    }
}

