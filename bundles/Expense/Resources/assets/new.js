/* global Expense:readonly */
const $ = require('jquery')
require('select2')

require('./scss/new.scss')

$(() => {
    const $issueCtrl = $('#form_issue_key').hide()
    const $issuePicker = $('<select class="form-control"></select>')
        .insertBefore($issueCtrl)
        .css({width: '100%'})
        .select2()

    const $projectCtrl = $('#form_project');

    const buildIssuePicker = () => {
        const project = $projectCtrl.val()
        if (project) {
            $issuePicker
                .select2('destroy')
                .select2({
                    placeholder: Expense.messages.search_for_issue_in_project.replace('{project}', project),
                    ajax: {
                        url: Expense.project_issues_url.replace('{project}', project),
                        dataType: 'json',
                        processResults: function (data) {
                            return {
                                results: data.issues.map(function (issue) {
                                    return {
                                        id: issue.key,
                                        text: issue.summary + ' (' + issue.key + ')'
                                    }
                                })
                            }
                        }
                    }
                })
                .on('select2:select', function (e) {
                    $issueCtrl.val(e.params.data.id)
                })
                .val('').trigger('change')
        }
    }

    $projectCtrl.on('change', buildIssuePicker)
    buildIssuePicker()
})
