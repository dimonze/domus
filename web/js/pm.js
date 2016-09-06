$(function() {
  $('.message-page-nav a').live('click', function() {
    var $block = $(this).closest('.pm-block');
    $.get(this.href, function(data) {
      $block.after($(data).find('#' + $block.attr('id')));
      $block.remove();
    });
    return false;
  });

  $('.pm-subject a').live('click', function() {
    var $pm = $(this).closest('.pm');
    var $msg = $pm.find('.pm-body');

    if ($msg.length) {
      $msg.slideToggle('fast');
    }
    else {
      var url = $(this).attr('href');
      $msg = $('<div class="pm-body"/>');
      $msg.append('<div class="pm-message">/');
      if ($pm.find('.pm-from').length) {
        $msg.append('(<a href="' + url.replace('get-message', 'add') + '" class="popup" rel="reg">ответить</a>)');
      }
      $msg.hide().appendTo($pm);
      $msg.find('.pm-message').load(url, function() {
        if ($pm.is('.pm-unread')) {
          $pm.removeClass('pm-unread');
          $('.unread-messages').each(function() {
            var nb= parseInt($(this).text());
            var $container = $(this).closest('.unread-messages-container');

            if (--nb || !$container.length) {
              $(this).text(nb--);
            }
            else {
              $container.remove();
            }
          });
        }

        $msg.slideDown('fast')
      });
    }

    return false;
  });

  $('.pm a.delete').live('click', function() {
    var $pm = $(this).closest('.pm');

    confirm('Удалить?') && $.get(this.href, function() {
      var $link = $pm.closest('.pm-block').find('.message-page-nav a.current');
      if ($link.length) {
        // timeout to enable loader progress
        window.setTimeout(function() {$link.trigger('click');}, 100);
      }
      else {
        $pm.remove();
      }
    });

    return false;
  });
  
 $('.profile-message a.inner').live('click', function() {
   //$(this).parent();
   $.getJSON(this.href, function(data){
     if(data.id != false){
       var elem = $('<div></div>')
       .addClass('profile-message')
       .addClass(data.priority);
       
       $('<a></a>')
       .addClass('inner')
       .attr('href', '/pm/read-and-next?id=' + data.id)
       .text('x')
       .appendTo(elem);
       
       $('<h5></h5>')
       .text(data.date)
       .appendTo(elem);
       
       $('<p></p>')
       .html(data.subject)
       .appendTo(elem);
       
       $('<p></p>')
       .html(data.message)
       .appendTo(elem);
       
       $('<a></a>')
       .addClass('more')
       .attr('href', '/user/pm#m' + data.id)
       .text('Подробнее')
       .appendTo(elem);
       
       $('.profile-message:last').after(elem);
     }
   });
   $(this).parent().remove();
   return false;
 });

 var url = document.location.toString();
 if(url.match('#')) {
   url = url.split('#').pop();
   $('#' + url).click();
 }

 $('.profile-message a.more').click(function (){
   var m_id = $(this).attr('href').split('#').pop();
   if(!!$('a#' + m_id).attr('href')){
     $('a#' + m_id).click();
   }
 });
});