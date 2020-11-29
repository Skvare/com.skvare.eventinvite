CRM.$(function($) {
    $('#rsvp-block').detach().appendTo('div.custom_pre-section fieldset');

    function feeBlocks() {
        if ($("input[name='rsvp']:checked").val() == 1) {
            $('fieldset#priceset').show();
            $('div#priceset').show();
            $('fieldset.payment_options-group').show();
            $('div#billing-payment-block').show();
        }
        else {
            //unset all price values
            $('div#priceset input').each(function(){
                if ($(this).prop('type') == 'text') {
                    $(this).val(''); //text fields
                }
                if ($("input[name='rsvp']:checked").val() == 0) {
                    $(this).prop('checked', false); //radio/checkbox
                }
            });

            //select fields
            $('div#priceset select').each(function(){
                $(this).val(null).trigger("change");
            });

            //hide price blocks
            $('fieldset#priceset').hide();
            $('div#priceset').hide();
            $('fieldset.payment_options-group').hide();
            $('div#billing-payment-block').hide();
        }
    }

    feeBlocks();
    $('input[name=rsvp]').change(function(){
        feeBlocks();
    });
});
