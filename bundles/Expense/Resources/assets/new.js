/* global Expense:readonly */
const $ = require('jquery');
require('select2');

require('./scss/new.scss');

$(() => {
    const $issueCtrl = $('#form_issue_key').hide();
    const $issuePicker = $('<select class="form-control"></select>')
        .insertBefore($issueCtrl)
        .css({ width: '100%' })
        .select2();

    const $projectCtrl = $('#form_project');

    const renderIssue = issue => issue.summary + ' (' + issue.key + ')';

    const buildIssuePicker = (event) => {
        const project = $projectCtrl.val();
        $issuePicker
            .select2('destroy')
            .select2({
                placeholder: Expense.messages['expense.new.project.placeholder']
            });
        if (project) {
            $issuePicker
                .select2({
                    placeholder: Expense.messages['expense.new.search_for_issue_in_project'].replace('{project}', project),
                    ajax: {
                        url: Expense.project_issues_url.replace('{project}', project),
                        dataType: 'json',
                        processResults: function (data) {
                            return {
                                results: data.issues.map(function (issue) {
                                    return {
                                        id: issue.key,
                                        text: renderIssue(issue)
                                    };
                                })
                            };
                        }
                    }
                })
                .on('select2:select', function (e) {
                    $issueCtrl.val(e.params.data.id);
                })
                .val('').trigger('change');

            // Initialize from control data on first run (i.e. not triggered by change of project).
            if (!event) {
                const selectedIssue = $issueCtrl.data('issue');
                if (selectedIssue) {
                    // @see https://stackoverflow.com/a/39359653
                    $issuePicker
                        .append($('<option/>', { value: selectedIssue.key }).html(renderIssue(selectedIssue)))
                        .val(selectedIssue.key)
                        .trigger('change');
                }
            }
        }
    };

    $projectCtrl.on('change', buildIssuePicker);
    buildIssuePicker();
});
