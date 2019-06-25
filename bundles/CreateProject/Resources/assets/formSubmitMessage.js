/**
 * Change submit button on submit.
 */
$(document).ready(function() {
  $('#create_project_form_save').click(function() {
    $('#create_project_form_save').css("display","none");
    $('#create_project_form_save_text').css("display","block");
  });
});