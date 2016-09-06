$(function() {
  $('button.photo-crop').bind('click', function(){
    var crop = window.open($(this).attr('url'), '', 'scrollbars=yes,menubar=no,location=no,width=800,height=600');
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
});