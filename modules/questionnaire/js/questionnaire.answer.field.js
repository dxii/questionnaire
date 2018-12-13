(function ($, Drupal) {
  // Requires only 1 checkbox to check
  Drupal.behaviors.questionAnswer = {
    attach: function(context, settings) {
      questionAnswer();

      $(window, context).once('questionAnswer').each(function() {
        $(this).on('scroll resize orientationchange', function () {
          questionAnswer();
        });
      });

      // Deselect other checkbox
      function questionAnswer() {
        $('.field--name-field-question-answer-list').each(function() {
          var id = $(this).attr('id');
          $('#' + id + ' input.form-checkbox').on('change', function() {
            $('#' + id + ' input.form-checkbox').not(this).prop('checked', false);
          });
        });
      }
    }
  };

})(jQuery, Drupal);


