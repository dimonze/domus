/**
 * jQuery Ajax upload 0.21
 *
 * @author Eugeniy Belyaev <eugeniy.b@garin-studio.ru>
 */

(function($) {

  $.fn.ajaxUpload = function (options) {
    return $(this).filter('input[type=file]').each(function() {

      var input = this;
      var $input = $(input);


      if (options == 'disable') {
        input.ajaxUpload.enabled = false;
        $input.attr('disabled', true);
        return true;
      }
      if (options == 'enable') {
        input.ajaxUpload.enabled = true;
        $input.removeAttr('disabled');
        return true;
      }
      if (options == 'submit') {
        input.ajaxUpload.submit();
        return true;
      }
      if (typeof options.action == 'undefined') {
        return false;
      }

      function clean_filename(str) {
        return str.replace(/.*(\/|\\)/, '');
      }

      input.ajaxUpload = $.extend({}, $.fn.ajaxUpload.defaults, options);
      input.ajaxUpload.submit = function() {
        if (! input.ajaxUpload.enabled || ! $input.val()) {
          return false;
        }

        $input.ajaxUpload('disable');
        input.ajaxUpload.onSubmit(clean_filename($input.val()));
        var $iframe = $('<iframe src="javascript:false;" name="'+Math.round(Math.random()*10000)+'"/>');
        var $newinput = null;
        var $form = null;

        $('#global-loading').trigger('ajaxSend');

        $iframe.bind('load', function() {
          if (!$iframe.attr('sending')) {
            $iframe.attr('sending', true);
            $form = $('<form method="post" enctype="multipart/form-data"/>');
            $form.attr({
              action: input.ajaxUpload.action,
              target: $iframe.attr('name')
            });
            $newinput = $input.clone(true).insertAfter($input);
            $form.append($input.attr('name', input.ajaxUpload.name).removeAttr('disabled'));

            $('body').append($form.hide());
            $form.trigger('submit');
          }
          else {


            //$iframe.unbind('load');

            var $response = $iframe.contents().find('.success:first');
            var $error = $iframe.contents().find('.error:first');

            input.ajaxUpload.onComplete(clean_filename($input.val()), $iframe.contents().find('body').clone());
            if ($response.length) {
              input.ajaxUpload.onSuccess(clean_filename($input.val()), $response.html());
            }
            else {
              input.ajaxUpload.onError(clean_filename($input.val()), $error.html());
            }
            $input.insertBefore($newinput);
            $newinput.remove();

            if ($.browser.opera) {
              // kind of strange way, isn't it? :)
              input.type = 'text';
              input.type = 'file';
            }
            else if ($.browser.msie) {
              var clone = $input.clone();
              $input.after(clone).remove();
              bindButtons(true);
            }

            else {
              $input.val('');
            }

            $input.ajaxUpload('enable');
            $input.unbind('load');

            if ($input.siblings('button').attr('disabled')) {
              $input.attr('disabled', true);
            }

            window.setTimeout(function() {
              $iframe.remove();
              $form.remove();
            }, 20);

            $('#global-loading').trigger('ajaxComplete');
          }
        });
        $('body').append($iframe.hide());

        return true;
      }

      return true;
    });
  };

  $.fn.ajaxUpload.defaults = {
    action: '',
    name: 'userfile',
    enabled: true,
    onSubmit: function(filename) { },
    onError: function(filename, response) { },
    onSuccess: function(filename, response) { },
    onComplete: function(filename, response) { }
  };

})(jQuery);
