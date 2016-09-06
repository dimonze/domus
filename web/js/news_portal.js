$(document).ready(function(){

  // pools page
  $('form.pool-item input, .rc-box.polls input').click(function(){
    $(this).closest('form').find('div.active-pool').removeClass('active-pool');
    $(this).parent().addClass('active-pool');    
  });
  $('form.pool-item input:checked, .rc-box.polls input:checked').each(function(){$(this).attr('checked', false);});
  
  // submit for green btn
  //$('.green-button').click(function(){ $(this).closest('form').submit();})


  // blogs
  $('a.blog-post-publish-link').click(function() {
    var a = $(this);
    $.getJSON(a.attr('href'),{},  function(data) {
      if('publish' == data.valid) {
        a.html('не показавать');
        a.attr('title','не показавать');
      }
      else {
        a.html('опубликовать');
        a.attr('title','опубликовать');
      }
    });
    return false;
  });

  $('a.blog-post-delete').click(function() {
    var a = $(this);
    if(confirm('Удалить пост?')) {
      $.post(a.attr('href'), function(data){
        a.closest('div.item').slideUp();
      });
    }
    return false;
  });

  // questionnaire_vote
  $('a.questionnaire_vote').click(function() {
    var form = $(this).closest('form');
    var post_data = form.serialize();
    $.post(form.attr('action'), post_data, function(data){
      if(data) {

        form.parent().append($(data).html());
        form.remove();
      }
    });
    return false;
  });
  
  //  insert in blog
  if($('div.insert-in-wrp').length) {
    $('textarea[name=comment-text]','div.insert-in-wrp').val(
      '<a href="' + $(location).attr('href') + '">' + $('title').text() + '</a>'
      );
  }

  /* Работа с комментариями */
  $('form.pop-up-content .green-button').live('click', function(){
    $(this).closest('form').bind('submit', ajax_validate);    
    $(this).closest('form').submit();
    return false;
  });

  $('a#leave-a-comm').click(function(){
    post_type = $('div.comments').attr('rel');
    post_id = $('div.comments').attr('value');
    var parent = $(this).parent();
    link = $(this);
    $(this).hide();
    parent_id = $(this).closest('.bubble3-content').attr('rel');
    if (parent_id == undefined) {
      parent_id = '';
    }
    $.get('/comment/getform',
      { parent_id :  parent_id, post_type: post_type, post_id: post_id }, function(data) {
      if (data.length > 0) {        
        if (parent_id != '') {
          parent.append($(data));
        }
        else {
          link.after($(data));
        }
      }
    });
     return false;
  });

  $('.comments a.pop-up-close-wrp').live('click',function(){
    $(this).parent().parent().find('.dashed').show();
    $(this).parent().remove();
    return false;
  });

  /* Конец работы с комментариями */
      
  $("#insert-in-blog").click(function(){
    $('div.pop-up-box').hide();
    $('#insert-pop-up').show();
    $('body').bind('click.status', function(e){
      if ($(e.target).parents('#insert-pop-up').size() == 0 && e.target.id != 'insert-pop-up') {
        $('#insert-pop-up').hide();
        $('body').unbind('click.status');  
      }
    });
    return false;
  });
  $("#insert-pop-up .pop-up-close").click(function(){$('#insert-pop-up').hide();});
  

 

  $('a.to-print').click(function(){
    $('div.district-description').remove();
    window.print();
    return false;
  })


  /*  calendar slider month */
  
  $('ul.select-box-months li').click(function(){
    $('ul.select-box-years').addClass('years-hidden');
    var ul = $(this).parent();     
    if (!$(this).hasClass('active-month')) {
      $(this).siblings('li').removeClass('active-month').end().addClass('active-month');
      $(this).children('a').trigger('click');
      ul.addClass('months-hidden'); 
      return true;
    } else {
      if (ul.hasClass('months-hidden')) {
        ul.removeClass('months-hidden');
      
        $('body').bind('click.status', function(e){
          if ($(e.target).parents('.select-box-months').size() == 0 && !$(e.target).hasClass('select-box-months')) {
            $('.select-box-months').addClass('months-hidden');
            $('body').unbind('click.status');
          }
        });
      } else {
        ul.addClass('months-hidden');
        $('body').unbind('click.status');
      }
    }    
    return false;
  });
  
  /*  calendar slider year */
  
  $('ul.select-box-years li').click(function(){    
    $('ul.select-box-months').addClass('months-hidden');
    var ul = $(this).parent();
    if (!$(this).hasClass('active-year')) {
      $(this).siblings('li').removeClass('active-year').end().addClass('active-year');
      ul.addClass('years-hidden');
      $(this).children('a').trigger('click');
      return true;
    } else {
      if (ul.hasClass('years-hidden')) {
        ul.removeClass('years-hidden');

        $('body').bind('click.status', function(e){
          if ($(e.target).parents('.select-box-years').size() == 0 && !$(e.target).hasClass('select-box-years')) {
            $('.select-box-years').addClass('years-hidden');
            $('body').unbind('click.status');
          }
        });
      } else {
        ul.addClass('years-hidden');
      $('body').unbind('click.status');
      }
    }
    return false;
  });
 
  //Delete comments
  $('.delete-comment a').click(function(){
    var url = $(this).attr('href');
    var bubble = $(this);
    $.getJSON(url, function(data){      
      if (data.complite == 'delete'){        
        bubble.closest('.comment-body')
          .html('<p>Комментарий был удалён модератором</p>');
      }
    });
    return false;
  });
  
  $('#content a.button').click(function(){
    $.get($(this).attr('href'), function (data){
      $popup = show_popup(data);
    });
    return false;
  });

  $('textarea#comment_body').live('keyup', function() {
    if($(this).val().length >= 1000) {
      $(this).val($(this).val().substring(0,1000));
      if(!$(this).closest('form').find('div.notify').length)
        $(this).before('<div class="notify">Комментарий может содержать не более 1000 символов</div>');
    }
  });

});
