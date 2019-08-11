/**
 * Change submit button on submit.
 */
$(document).ready(function () {
    $('#graphic_service_order_form_save').click(function () {
        $('#graphic_service_order_form_save').css('display', 'none');
        $('#graphic_service_order_form_save_text').css('display', 'block');
    });
});
