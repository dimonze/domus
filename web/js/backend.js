$.fn.reverse = [].reverse;

$(function() {
  $('#widgets').each(function() {
    function bind_widget($el) {
      $el
        .hover(
          function(){
            $(this).addClass('hover').siblings().removeClass('hover');
          },
          function(){
            $(this).removeClass('hover');
          }
        )
        .find('.actions a.required').bind('click', function() {
          $(this).closest('.widget').toggleClass('required');
          return false;
        }).end()
        .find('.actions a.delete').bind('click', function() {
          if (confirm('Действительно удалить? При удалении виджет будет удален из всех форм как и вся введенна пользователями информация.')) {
            var $widget_for_delete = $(this).closest('.widget');
            $.get($(this).attr('href'), {},  function() {
              $widget_for_delete.fadeOut('slow', function() {$(this).remove()})
            });
          }
          return false;
        });
    }
    bind_widget($('.widget', this));

    $('.form .sf_admin_action_save a').bind('click', function() {
      var $widgets = $('#form-container .widget[rel]');

      // check for <required> widgets ordering
//      var wrong_order = false;
//      $widgets.each(function() {
//        $this = $(this);
//        if (!$this.is('.required') && $this.nextAll('.required').length) {
//          wrong_order = true;
//          return false;
//        }
//      });
//      if (wrong_order) {
//        if (confirm('Внимание! Обязательные поля должны идти строго до необязательных. Пересортировать автоматически? Если вы выберете "отмена", то будет сохранен текущий порядок.')) {
//          $widgets.not('.required').reverse().each(function() {
//            $(this).insertAfter('#form-container .widget.required:last');
//          });
//        }
//      }

      var data = [];
      var position = 1;
      $widgets.each(function() {
        data[data.length] = 'data['+position+'][id]='                     + $(this).attr('rel');
        data[data.length] = 'data['+position+'][required]='               + ($(this).is('.required') ? 1 : 0);
        data[data.length] = 'data['+position+'][position]='               + position;
        data[data.length] = 'data['+position+'][show_additional_header]=' + ($(this).next('.additional').length ? 1 : 0);
        position++;
      });

      $.post(window.form_save_url, data.join('&'), function(r) {
        alert(r);
      });


      return false;
    });

    $('.order .sf_admin_action_save a').bind('click', function() {
      data = [];
      i = 1;

      $('#group-container>div.block.editable').each(function() {
        group = [];
        $('.widget', this).each(function() {
          group[group.length] = 'data['+i+'][]='+$(this).attr('rel');
        });
        if (group.length) {
          data[data.length] = group.join('&');
          i++;
        };
      });

      $.post(window.order_save_url, data.join('&'), function(r) {
        alert(r);
      });

      return false;
    });

    $('.order .sf_admin_action_new a').bind('click', function() {
      $('<div class="block editable"/>').html('<div class="move"></div>').appendTo('#group-container');
      init_sortable();
      return false;
    });


    var tries = 100;
    function init() {
      if (typeof window.widgets_in_use != 'undefined') {
        for (i in window.widgets_in_use) {
          var $widget = $('#widgets .widget[rel=' + window.widgets_in_use[i].id + ']');
          $widget.appendTo('#form-container');
          if (window.widgets_in_use[i].required) {
            $widget.addClass('required');
          }
          if (window.widgets_in_use[i].show_additional_header) {
            $('#widgets .widget.additional').appendTo('#form-container');
          }
        }
        return true;
      }
      if (typeof window.groups_in_use != 'undefined') {
        for (i in window.groups_in_use) {
          $('.order .sf_admin_action_new a').trigger('click');
          for (j in window.groups_in_use[i]) {
            $('.widget[rel='+window.groups_in_use[i][j]+']').appendTo('#group-container .block.editable:last');
          }
        }
        return true;
      }

      if (tries-- != 0) {
        window.setTimeout(init, 50);
      }
      return false;
    }
    init();

    function init_sortable() {
      $('#widget-container, #form-container, #group-container>div.block.editable').sortable('destroy').sortable({
        connectWith: '#widget-container, #form-container, #group-container>div.block.editable',
        cursor: 'move',
        handle: '.label',
        receive: function() {
          $('#widget-container .widget.required').removeClass('required');
        }
      });
      $('#group-container').sortable('destroy').sortable({
        cursor: 'move',
        handle: '.move'
      });
    }
    init_sortable();

    window.widget_add = function(data) {
      if ($('.widget[rel='+data.id+']').length) {
        $widget = $('.widget[rel='+data.id+']');
      }
      else {
        $widget = $('#widget-container .widget').not('.additional').eq(0).clone().attr('rel', data.id)
          .appendTo('#widget-container');
      }
      $widget
        .hide()
        .find('.label').html(data.comment || data.label).end()
        .find('.value').html(data.value).end()
        .find('a').each(function() {
          $(this).attr(
            'href',
            $(this).attr('href').replace(/id=\d+/, 'id='+data.id).replace(/id\/\d+/, 'id/'+data.id)
          );
        }).end()
        .addClass('hover')
        .fadeIn('slow', function() {$(this).removeClass('hover')});

      if ($widget.attr('rel') > 55) {
        $widget.addClass('can_delete');
      }
      else {
        $widget.removeClass('can_delete');
      }

      offset = $widget.offset()['top'];
      $('#widget-container').scrollTop($('#widget-container').scrollTop() + offset + (offset < 0 ? -150 : +150));

      bind_widget($widget);
    };

  });

  $('ul:has(li.group-credentials)').each(function() {
    function check_dependencies() {
      if (tree[this.value] && tree[this.value].length) {
        var $items = $(tree[this.value]);

        if ($(this).is(':checked')) {
          $items.removeAttr('disabled').next('label').css('color', null);
        }
        else {
          $items.attr('disabled', true).next('label').css('color', 'silver');
        }

        $items.each(check_dependencies);
      }
    }

    var $credentials = $(this).find(':checkbox');
    var tree = {};

    $credentials.each(function() {
      var name = String(this.value);
      var re = new RegExp('^' + name.replace(/-access$/, '') + '-');
      var items = [];

      var level = name.replace(/-access$/, '').split('-').length - 1;
      $(this).closest('li').css('margin-left', 2 * level + 'em');

      $credentials.each(function() {
        if (this.value != name && re.test(this.value)) {
          items[items.length] = this;
        }
      });

      tree[name] = items;
    });

    $credentials.bind('click', check_dependencies).each(check_dependencies);
  });



  $("#global-loading").bind("ajaxSend", function(){
    $(this).show();
  }).bind("ajaxComplete", function(){
    $(this).hide();
  }).hide();

  $('fieldset fieldset:has(legend)').each(function() {
    $(this).children('legend')
      .css('cursor', 'pointer')
      .bind('click', function() {
        $(this).siblings().toggle();
      })
      .trigger('click');
  });

  $("input[type=button][name=submit]").click(function(){
    var action = $("select[name=user\[actions\]]").val();
    var user_ids = $('div.sf_admin_list table').find('input[type=checkbox][name=ids\[\]][checked]').map(function(){
      return $(this).val();
    });
    var to;

    if (action){
      if (1 == action) {
        if (confirm('Подтвердите удаление')) {
          var $form = $('<form action="user/batch/action" method="post"/>');
          $form.append('<input type="hidden" name="batch_action" value="batchDelete" />');
          $.each(user_ids, function(i, id) {
            $form.append('<input type="hidden" name="ids[]" value="' + id + '" />');
          });
          $form.appendTo('body').trigger('submit');
          return false;
        }
      }
      else if (2 == action) {
        to = user_ids.get().join(', ');
      }
      else if (3 == action) {
        to = '<All>'
      }
      if (to.length > 0){
        $.post('/user/pm/addadminmessage', {
          'to': to
        }, function(data) {
          $('#global-loading').hide();
          var $popup = show_popup(data, 'reg');

          $popup.find('select[name=email_themes]').bind('change', function(){
            if ($(this).find(':selected').text().length > 0){
              $theme_id = $(this).find(':selected').val();
              $('input[name=pm\[subject\]]').attr('value', $(this).find(':selected').text());
              $('textarea[name=pm\[message\]]').attr('value', $('input[type=hidden][name=themes_' + $theme_id + ']').val());
            }
            else {
              $('input[name=pm\[subject\]]').attr('value', '');
              $('textarea[name=pm\[message\]]').attr('value', '');
            }
          });
          $popup.find('input[type=submit]').bind('click', function(){
            count_chk = $popup.find('input[type=checkbox][checked]').length;
            if (count_chk == 0){
              alert('Выберите куда отправить сообщение!');
              return false;
            }
          })
        });
      }
      else {
        alert('Выберите пользователей для отправки сообщения.');
      }
    }
  });
  $('select[name=default_email_theme]').bind('change', function(){
    selected_value = $(this).val();
    email_body = $('input[type=hidden][name=email_theme\[' + selected_value + '\]]').attr('value');
    $('span#message_body').html(email_body);
  });
  $('input[type=checkbox][name=users_all]').bind('click', function(){
    if ($(this).is(':checked')){
      $('table').find('input[type=checkbox][name=user\[id\]]').attr('checked', 'checked');
    }
    else {
      $('table').find('input[type=checkbox][name=user\[id\]]').removeAttr('checked');
    }
  });

  $('input[type=checkbox][name*=\[region_list\]][value=]').click(function(){
    if ($(this).attr('data-checked') == 0){
      $('input[type=checkbox][name*=\[region_list\]]').attr('checked', 'checked');
      $(this).attr('data-checked',1).removeAttr('checked');
    }
    else {
      $('input[type=checkbox][name*=\[region_list\]]').removeAttr('checked');
      $(this).attr('data-checked',0).removeAttr('checked');
      $('input:disabled[type=checkbox][name*=\[region_list\]]').attr('checked', 1);
    }
  });
  $('input[type=checkbox][name*=\[themes_list\]][value=]').click(function(){
    if ($(this).attr('checked')){
      $('input[type=checkbox][name*=\[themes_list\]]').attr('checked', 'checked');
    }
    else {
      $('input[type=checkbox][name*=\[themes_list\]]').removeAttr('checked');
    }
  });

  $('button.photo-crop').bind('click', function(){
    var crop = window.open($(this).attr('url'), '', 'scrollbars=yes,menubar=no,location=no,width=700,height=500');
    crop.parent.focus();

    return false;
  });

  $('input[name*=\[title_photo\]]').bind('click', function() {
    $('div.title-photo').find('div.success').remove('div.success');
    if ($(this).attr('value') != ''){
      $('#global-loading').show();
      $.get('/form/upload?name=' + $(this).attr('value'), function(data) {
        $('#global-loading').hide();
        $('div.title-photo').append(data);
      });
    }
  });
  $('input[name*=\[photo\]]').bind('click', function() {
    $('div.title-photo').find('div.success').remove('div.success');
    if ($(this).attr('value') != ''){
      $('#global-loading').show();
      $.get('/form/upload?name=' + $(this).attr('value'), function(data) {
        $('#global-loading').hide();
        $('div.title-photo').append(data);
      });
    }
  });
  $('input[name*=\[title_photo\]]').trigger('click');
  $('input[name*=\[photo\]]').trigger('click');

  $('a[name=user\[delete_image\]]').click(function(){
    image = $('div.user_image').find('img').attr('src');
    if (image != '/images/pict_company.png' && image != '/images/pict_user.png'){
      $.getJSON('/user/deleteuserimage?user_id=' + $(this).attr('rel'), function(data){
        if (data.image == 'deleted'){
          $('div.user_image').find('img').remove();
          $('a[name=user\[delete_image\]]').hide();
        }
        else {
          alert('Изображение не удалено.');
        }
      },
      'json');
    }
    else {
      alert('Это изображение по-умолчанию.');
    }
    return false;
  });

  $('a.del-answer').live('click', function(){
    var cont = $(this).closest('div.sf_admin_form_row');
    var answer_id = cont.find('input[name=answer_id]').val();
      $.post('/questionnaire/deleteanswer', {answer_id : answer_id}, function(data) {
        cont.remove();
      });
    return false;
  });
  $('a.add-answer').live('click', function() {
    var q_id = $('input[name=questionnaire[id]]').val();
    $.post('/questionnaire/addanswer', {q_id : q_id}, function(data) {
      $('fieldset#sf_fieldset_none').append(data);
    });
    return false;
    alert(q_id);

  });

  $('a.view-blog-posts').live('click', function(){
    $('form[name=show-blog-posts]').find('input[name=blog_post_filters[blog_id]]').val($(this).find('input').val());
    $('form[name=show-blog-posts]').submit();
    return false;
  });

  $('a.ajax-promote').click(function(){
    var a = $(this);
    $.get(a.attr('href'), function(data){
      if(data) {
        a.closest('td').find('span','div.promote-count').text(data);
      }
    });
    return false;
  });

  $('input[type="submit"][rel="post"]').bind('click', function() {
    $('#global-loading').show();
    text_id = $(this).closest('form')
    .find('textarea[name*=post_text]').attr('id');
    tiny_obj = tinyMCE.get(text_id);
    text = tiny_obj.getContent();

    post_type = $(this).closest('form')
      .find('textarea[name*=post_text]').attr('name');

    $.post('/form/autolinkator', {text: text, post_type: post_type}, function(data){
      if (data.length != 0) {
        $('textarea[name*=post_text]').closest('.content').append(data);
        window.location.hash = '#links';
      }
      else {
        $('input[type="submit"][rel="post"]').closest('form').trigger('submit');
      }
    }, 'html');
    $('#global-loading').hide();
    return false;
  });

  $('input[name="discard_links"]').live('click', function(){
    $(this).closest('div#post_text_links').hide().remove();
    return false;
  });
});