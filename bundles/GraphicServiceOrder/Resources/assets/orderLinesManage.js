/**
 * Manage order lines.
 * @see https://symfony.com/doc/current/form/form_collections.html
 *
 */
var $collectionHolder;

// setup an "add a line" link
var $addTagButton = $('<button type="button" class="btn-sm btn-primary add_line_link">Tilføj endnu et produkt</button>');
var $newLinkLi = $('<li></li>').append($addTagButton);

/**
 * Setup order lines form.
 */
jQuery(document).ready(function () {
    // Get the ul that holds the collection of lines
    $collectionHolder = $('ul.lines');

    // add a delete link to all of the existing line form li elements
    $collectionHolder.find('li').each(function () {
        addTagFormDeleteLink($(this));
    });

    // add the "add a line" anchor and li to the lines ul
    $collectionHolder.append($newLinkLi);

    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    $collectionHolder.data('index', $collectionHolder.find(':input').length);

    $addTagButton.on('click', function (e) {
    // add a new line form (see next code block)
        addTagForm($collectionHolder, $newLinkLi);
    });

    // Create one line on form creation if no line is there.
    if (!$('.order-lines .remove-line')[0]) {
        addTagForm($collectionHolder, $newLinkLi);
    }
});

/**
 * Add a new form element.
 *
 * @param $collectionHolder
 * @param $newLinkLi
 */
function addTagForm ($collectionHolder, $newLinkLi) {
    // Get the data-prototype explained earlier
    var prototype = $collectionHolder.data('prototype');

    // get the new index
    var index = $collectionHolder.data('index');

    var newForm = prototype;
    // You need this only if you didn't set 'label' => false in your lines field in TaskType
    // Replace '__name__label__' in the prototype's HTML to
    // instead be a number based on how many items we have
    // newForm = newForm.replace(/__name__label__/g, index);

    // Replace '__name__' in the prototype's HTML to
    // instead be a number based on how many items we have
    newForm = newForm.replace(/__name__/g, index);

    // increase the index with one for the next item
    $collectionHolder.data('index', index + 1);

    // Display the form in the page in an li, before the "Add a line" link li
    var $newFormLi = $('<li></li>').append(newForm);
    $newLinkLi.before($newFormLi);

    // add a delete link to the new form
    addTagFormDeleteLink($newFormLi);
}

/**
 * Add a delete link.
 *
 * @param $lineFormLi
 */
function addTagFormDeleteLink ($lineFormLi) {
    var $removeFormButton = $('<div class="remove-line"><i class="remove-line-icon fa fa fa-trash-alt"></i></div>');
    $lineFormLi.append($removeFormButton);

    $removeFormButton.on('click', function (e) {
    // remove the li for the line form
        $lineFormLi.remove();
    });
}
