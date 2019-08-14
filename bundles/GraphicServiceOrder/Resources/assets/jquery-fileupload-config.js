/* eslint-env jquery */
// Add filer functionality to filefield.
$(function () {
    var ul = $('#fileList');

    $('#graphic_service_order_form_files').fileupload({
        dataType: 'json',
        // This function is called when a file is added to the queue
        add: function (e, data) {
            data.formData = {
                originalName: data.files[0].name
            };
            // This area will contain file list and progress information.
            var tpl = $('<li class="working">' +
              '<input type="text" value="0" data-width="35" data-height="35" data-fgColor="#0747A6" data-readOnly="1" data-bgColor="#0747A6" />' +
              '<p></p><span></span><div class="remove-line"><i class="remove-line-icon fa fa fa-trash-alt"></i></div></li>');

            // Append the file name and file size
            tpl.attr('data-filename', data.files[0].name);

            tpl.find('p').text(data.files[0].name)
                .append('<i>(' + formatFileSize(data.files[0].size) + ')</i>');

            // Add the HTML to the UL element
            data.context = tpl.appendTo(ul);

            // Initialize the knob plugin. This part can be ignored, if you are showing progress in some other way.
            tpl.find('input').knob();

            // Listen for clicks on the cancel icon
            tpl.find('.remove-line').click(function () {
                if (tpl.hasClass('working')) {
                    jqXHR.abort();
                }
                tpl.fadeOut(function () {
                    tpl.remove();
                    // Remove files from hidden field, so they won't be processed.
                    var uploaded = $('#graphic_service_order_form_files_uploaded').val();
                    var removed = $(this).data('new-filename');
                    var newUploaded = uploaded.replace(removed + ';', '');
                    $('#graphic_service_order_form_files_uploaded').val(newUploaded);
                });
            });
            // Automatically upload the file once it is added to the queue

            var jqXHR = data.submit();
        },
        progress: function (e, data) {
            // Calculate the completion percentage of the upload
            var progress = parseInt(data.loaded / data.total * 100, 10);

            // Update the hidden input field and trigger a change
            // so that the jQuery knob plugin knows to update the dial
            data.context.find('input').val(progress).change();

            if (progress === 100) {
                data.context.removeClass('working');
            }
        },
        done: function (e, data) {
            $('[data-filename="' + data.result.files.file.old_name + '"]').attr('data-new-filename', data.result.files.file.name);
            var hiddenFieldValue = $('#graphic_service_order_form_files_uploaded').val();
            $('#graphic_service_order_form_files_uploaded').val(hiddenFieldValue + data.result.files.file.name + ';');
        }
    });
    // Helper function for calculation of progress
    function formatFileSize (bytes) {
        if (typeof bytes !== 'number') {
            return '';
        }

        if (bytes >= 1000000000) {
            return (bytes / 1000000000).toFixed(2) + ' GB';
        }

        if (bytes >= 1000000) {
            return (bytes / 1000000).toFixed(2) + ' MB';
        }
        return (bytes / 1000).toFixed(2) + ' KB';
    }
});
