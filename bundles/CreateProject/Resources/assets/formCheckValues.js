/**
 * Compare input values of project name and project key to list generated from api on form load.
 */
$(document).ready(function () {
    var projects = $('#create-project-form').data('form-config');

    $('#create_project_form_project_name').on('keyup touchend', function () {
        var selector = '#create_project_form_project_name';
        findMatch(selector, projects, 'name');
    });

    $('#create_project_form_project_key').on('keyup touchend', function () {
        var selector = '#create_project_form_project_key';
        findMatch(selector, projects, 'key');
    });

    /**
     * Look for input value in projects array.
     *
     * @param selector
     *   The selector that holds the form element.
     * @param projects
     *   A list of project names and their keys.
     * @param type
     *   Whether we compare name og key from array.
     */
    function findMatch (selector, projects, type) {
        var keyExists = false;

        for (var projectKey in projects.allProjects) {
            if (projects.allProjects.hasOwnProperty(projectKey)) {
                var item = projects.allProjects[projectKey];

                if (item[type] === $(selector).val().toUpperCase()) {
                    keyExists = true;
                }
            }
        }

        if (keyExists) {
            $('.key-warning-message').addClass('show');
            $(selector).addClass('is-invalid');
            $('#create_project_form_save').attr('disabled', 'disabled');
        } else {
            $('.key-warning-message').removeClass('show');
            $(selector).removeClass('is-invalid');
            $('#create_project_form_save').removeAttr('disabled');
        }
    }
});
