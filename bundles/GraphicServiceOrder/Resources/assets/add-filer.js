/* eslint-env jquery */
// Add filer functionality to filefield.
$(document).ready(function() {
    $('[jquery_filer="filer_input"]').filer({
        changeInput: true,
        addMore: true,
        showThumbs: true
    });
});