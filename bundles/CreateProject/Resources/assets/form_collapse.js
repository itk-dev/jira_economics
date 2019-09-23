/**
 * Show hidden elements on form if their respective boxes are checked.
 * This is needed if a page reload is made half way through the form, or if
 * form validation fails.
 */
$(document).ready(function () {
    if ($('[data-target=".toggle-account-group"]').is(':checked')) {
        $('.toggle-account-group').addClass('show');
    }
    if ($('[data-target=".toggle-customer-group"]').is(':checked')) {
        $('.toggle-customer-group').addClass('show');
    }
});
