/**
 * Apply datepicker.
 */
$(document).ready(function () {
    $('.js-datepicker').datepicker({
        format: 'dd-mm-yyyy',
        daysOfWeekDisabled: '06',
        weekStart: '1'
    });
});
