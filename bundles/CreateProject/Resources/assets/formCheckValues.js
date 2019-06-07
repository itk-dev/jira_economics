/**
 * Compare input values of project name and project key to list generated from api on form load.
 */
$(document).ready(function() {
  var projects = $('#create-project-form').data('form-config');

  $('#create_project_form_project_name').on('keyup touchend', function(){
    var selector = '#create_project_form_project_name';
    findMatch(selector, projects, 'name')
  });
  $('#create_project_form_project_key').on('keyup touchend', function(){
    var selector = '#create_project_form_project_key';
    findMatch(selector, projects, 'key')
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
  function findMatch(selector, projects, type) {
    if (type === 'name') {
      var nameExists = false;
      projects.allProjects.forEach(function(item, indexKey) {
        if (item.name === $(selector).val()) {
          nameExists = true;
        }
      });
      if (nameExists) {
        $('.name-warning-message').addClass('show');
        $(selector).addClass('is-invalid');
        $('#create_project_form_save').attr("disabled","disabled");
      } else {
        $('.name-warning-message').removeClass('show');
        $(selector).removeClass('is-invalid');
        $('#create_project_form_save').removeAttr("disabled");
      }
    }
    if (type === 'key') {
      var keyExists = false;
      projects.allProjects.forEach(function(item, indexKey) {
        if (item.key === $(selector).val().toUpperCase()) {
          keyExists = true;
        }
      });
      if (keyExists) {
        $('.key-warning-message').addClass('show');
        $(selector).addClass('is-invalid');
        $('#create_project_form_save').attr("disabled","disabled");
      } else {
        $('.key-warning-message').removeClass('show');
        $(selector).removeClass('is-invalid');
        $('#create_project_form_save').removeAttr("disabled");
      }
    }
  }
});

/**
 * Object.prototype.forEach() polyfill
 * https://gomakethings.com/looping-through-objects-with-es6/
 * @author Chris Ferdinandi
 * @license MIT
 */
if (!Object.prototype.forEach) {
  Object.defineProperty(Object.prototype, 'forEach', {
    value: function (callback, thisArg) {
      if (this == null) {
        throw new TypeError('Not an object');
      }
      thisArg = thisArg || window;
      for (var key in this) {
        if (this.hasOwnProperty(key)) {
          callback.call(thisArg, this[key], key, this);
        }
      }
    }
  });
}
