/**
 * Compare input values of project name and project key to list generated from api on form load.
 */
$(document).ready(function () {
    let projects = $('#create-project-form').data('form-config');
    let nameSelector = $('#create_project_form_project_name');
    let keySelector = $('#create_project_form_project_key');
    let buttonSelector = $('#create_project_form_save');
    nameSelector.on('keyup touchend', function () {
        let activeSelector = nameSelector;
        findMatch(activeSelector, projects, 'name');
    });

    keySelector.on('keyup touchend', function () {
        let selector = keySelector;
        findMatch(selector, projects, 'key');
    });

    /**
     * Look for input value in projects array.
     *
     * @param activeSelector
     *   The activeSelector that holds the form element.
     * @param projects
     *   A list of project names and their keys.
     * @param type
     *   Whether we compare name og key from array.
     */
    function findMatch (activeSelector, projects, type) {
        let elementExists = false;
        for (let projectKey in projects.allProjects) {
            if (projects.allProjects.hasOwnProperty(projectKey)) {
                let item = projects.allProjects[projectKey];
                if (item['name'] === activeSelector.val() || item['key'] === activeSelector.val().toUpperCase()) {
                    elementExists = true;
                }
            }
        }

        if (elementExists) {
            $('.' + type + '-warning-message').addClass('show');
            activeSelector.addClass('is-invalid');
            buttonSelector.attr('disabled', 'disabled');
        } else {
            $('.' + type + '-warning-message').removeClass('show');
            activeSelector.removeClass('is-invalid');
            buttonSelector.removeAttr('disabled');
        }
    }
});
