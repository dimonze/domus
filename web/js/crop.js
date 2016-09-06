$(function() {
  var appendCrop = function(){
    $('input[type=button][name=save_crop]').attr('disabled', false);
  }

  var updateCoords = function(coords){
    $('#coord_x').val(coords.x);
    $('#coord_y').val(coords.y);
    $('#width').val(coords.w);
    $('#height').val(coords.h);
  }

  var checkCoords = function(){
    if (parseInt($('#width').val())) return true;
    alert('Вы не выбрали область для кадрирования.');
    return false;
  }
  
  if ((window.opener.$('#user_name').length == 0) &&
      (window.opener.$('#post_author_name').length == 0)
    ){
    $('img[name=imagecropbox]').Jcrop({
        onSelect: updateCoords,
        onChange: appendCrop,
        aspectRatio: 281 / 209,
        minSize: [281, 209],
      });
  }else {
      $('img[name=imagecropbox]').Jcrop({
        onSelect: updateCoords,
        onChange: appendCrop,
        aspectRatio: 50 / 50,
        minSize: [150, 150],
        setSelect: [0,0, 150, 150]
      });
  }
  $('input[type=submit][name=load_image]').click(function(){
    if ($('input[type=file][name=image]').val() == ''){
      alert('Вы не выбрали изображение для загрузки');
      return false;
    }
  });
  $('input[name=save_crop]').click(function(){
    checkCoords();
    if ($('img[name=imagecropbox]')){      
      $.get(
        $('form').attr('action'),
        $('form').serialize(),
        function(data){
          window.opener.$('input[name*=\[photo\]]').attr('value', data.source);
          window.opener.$('input[name*=\[photo\]]').trigger('click');
          window.opener.$('input[name*=\[title_photo\]]').attr('value', data.source);
          window.opener.$('input[name*=\[title_photo\]]').trigger('click');
          window.opener.$('.logoProfile').remove();
          window.close();
        },
        'json'
      );
      return false;
    }
    else {
      alert('Сначала выберите изображение для загрузки.');
    }
    return false;
  });
});