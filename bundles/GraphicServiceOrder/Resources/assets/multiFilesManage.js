/**
 * Manage order lines.
 * @see https://symfony.com/doc/current/form/form_collections.html
 *
 */
var $collectionHolder;
var $newLinkLi = $('<li></li>');

/**
 * Setup order lines form.
 */
jQuery(document).ready(function () {
    // Get the ul that holds the collection of lines
    $collectionHolder = $('ul.file-lines');

    $('.file-lines').append('<span class="content-end"></span>');

    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    $collectionHolder.data('index', $collectionHolder.find(':input').length);

    addTagForm($collectionHolder, $newLinkLi, 1);
    // Look for changes in file upload.
    $('.multi-upload-lines').change(function (e) {
        var fileLine = $(e.target).attr('id');
        var fileName = e.target.files[0].name;
        $('label[for="' + fileLine + '"]').html(fileName);

        addEmptyLine($collectionHolder, $newLinkLi);
    });
    // Handle invalid form errors.
    // All files must be uploaded again.
    if ($('.file-lines > li').length > 1) {
        $('.file-lines > li').each(function (index) {
            if ($(this).find('.custom-file-label').text() === '') {
                $(this).remove();
            }
        });
        $('.multi-upload-lines #graphic_service_order_form_multi_upload_help').append('<div style="color:#dc3545">Bemærk: Eventuelle filer skal uploades igen!</div>')
        addTagForm($collectionHolder, $newLinkLi, 1);
        $('.multi-upload-lines .custom-file-input').addClass('is-invalid');
    }
});

/**
 *  * Add a new form element.
 *
 * @param $collectionHolder
 * @param $newLinkLi
 * @param initial
 */
function addTagForm ($collectionHolder, $newLinkLi, initial) {
    // Get the data-prototype explained earlier
    var prototype = $collectionHolder.data('prototype');

    // get the new index
    var index = $collectionHolder.data('index');

    var newForm = prototype;

    // Replace '__name__' in the prototype's HTML to
    // instead be a number based on how many items we have
    newForm = newForm.replace(/__name__/g, index);

    // increase the index with one for the next item
    $collectionHolder.data('index', index + 1);

    // Display the form in the page in an li, before the "Add a line" link li
    var $newFormLi = $('<li></li>').append(newForm);

    $('.content-end').before($newFormLi);

    // add a delete link to the new form
    if (initial === 0) {
        addTagFormDeleteLink($newFormLi, $collectionHolder, $newLinkLi);
    }
}

/**
 * Add a delete link.
 *
 * @param $lineFormLi
 * @param $collectionHolder
 * @param $newLinkLi
 */
function addTagFormDeleteLink ($lineFormLi, $collectionHolder, $newLinkLi) {
    var $removeFormButton = $('<div class="remove-line-file"><i class="remove-line-icon fa fa fa-trash-alt"></i></div>');
    $lineFormLi.append($removeFormButton);

    $removeFormButton.on('click', function (e) {
        // remove the li for the line form
        $lineFormLi.remove();
        addEmptyLine($collectionHolder, $newLinkLi);
    });
}

/**
 * Add empty line if none exists.
 *
 * @param $collectionHolder
 * @param $newLinkLi
 */
function addEmptyLine ($collectionHolder, $newLinkLi) {
    // Make sure a new empty file field is always available.
    var emptyFieldExists = 1;
    $('.multi-upload-lines input').each(function () {
        if ($(this).val() === '') {
            emptyFieldExists = 0;
        }
    });
    if (emptyFieldExists > 0) {
        addTagForm($collectionHolder, $newLinkLi, 0);
    }
}
