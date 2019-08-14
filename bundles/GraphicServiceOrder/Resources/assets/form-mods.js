/**
 * Modify service order form.
 */
$(document).ready(function () {
    var debitor = $('#graphic_service_order_form_debitor');
    $('#graphic_service_order_form_marketing_account').change(function () {
        if ($(this).is(':checked')) {
            debitor.val('');
            debitor.prop('disabled', true);
        } else {
            debitor.prop('disabled', false);
        }
    });
});
