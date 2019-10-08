/**
 * Apply autofiller for debitor number.
 */
$(document).ready(function () {
    let target = $('.js-debtor-exists');
    let inputField = $('#graphic_service_order_form_debitor');
    inputField.on('input', function() {
        validateInput($(this))
    });

    $('.js-fill-debtor').click(function() {
        let number = $(this).text().split(':');
        inputField.val(number[0]);
        validateInput(inputField);
    });

    function validateInput(input){
        if(allDebtors.hasOwnProperty(input.val())) {
            target.text(allDebtors[input.val()]);
        }
        else {
            target.text('Ukendt');
        }
    }
});
