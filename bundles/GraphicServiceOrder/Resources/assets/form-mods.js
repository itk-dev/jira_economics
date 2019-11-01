/**
 * Modify service order form.
 */
$(document).ready(function () {
    let debtor = $('#graphic_service_order_form_debitor');
    let libraries = $('.js-library');
    libraries.hide();

    function changeForm() {
        if ($('#graphic_service_order_form_marketing_account').is(':checked')) {
            debtor.val('');
            debtor.prop('disabled', true);
            libraries.show();
        } else {
            debtor.prop('disabled', false);
            libraries.hide();
        }
    }

    $('#graphic_service_order_form_marketing_account').change(function () {
        changeForm();
    });

    // Start the show
    $(document).ready(function () {
        changeForm();
    });
});
