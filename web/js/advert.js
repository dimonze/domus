$(document).ready(function(){

  // выбор города
  $('#advert-citySelect').click(function(){
    $('#advert-city-menu').show();
    $('body').bind('click.status', function(e){
      if ($(e.target).parents('#advert-city-menu').size() == 0 && e.target.id != 'advert-city-menu') {
        $('#advert-city-menu').hide();
        $('body').unbind('click.status');
      }
    });
    return false
  });

  var jscrollSet = {
      trackClickSpeed: 30,
      trackClickRepeatFreq: 0
  };

  //привязка скролла к схеме баннеров при загрузке - пока не загрузилась схема, привязывать нельзя.

  var init_panel = $('.adv-block-'+$('#advert-citySelect').find('span').attr('num')).show();
  /*init_panel.find('img.adv-page-scr').load(function(){*/
      init_panel.find('.page-wrp-inn').jScrollPane(jscrollSet);
  /*});*/

  //показываем подсказки с ценами при наведении на строки таблицы
  $('div.adv-ban').addClass('adv-bbl-h');
  $('.price-t tbody tr').hover(function(){
    var tr = $(this);
    var link = tr.attr('rel');
    var tPar = tr.closest('div.adv-block') ;
    var elem =  tPar.find('.'+link);
    var api = tPar.find('.page-wrp-inn').data('jsp');
    tPar.find('.price-t tbody tr').removeClass('hovered');
    tr.addClass('hovered');
    api.scrollToElement(elem, false, true);
    tPar.find('div.adv-ban').addClass('adv-bbl-h');
    elem.removeClass('adv-bbl-h');
  });

  //выбор другого региона
  $('#advert-city-menu').find('a').live('click', function(){
    var $link = $(this),
        $current = $('#advert-citySelect').html(),
        $clicked = $link.find('span'),
        num = $clicked.attr('num'),
        $panel = $('.adv-block-'+num);

    $('#advert-citySelect').html($clicked);
    $link.html($current);
    $('.adv-block').hide();
    $panel.show();
    if ($panel.find('.jspScrollable').length === 0) $panel.find('.page-wrp-inn').jScrollPane(jscrollSet);
    $('#advert-city-menu').hide();
    return false;
  });


//end
});

//var api = pane.data('jsp');
//
//$('img.adv-maket').bind('click', function(e){
//var imgPos = $(this).offset();
//var imgPosT = imgPos.top;
//var clickPos = e.pageY;
//var delt = Math.round(clickPos - imgPosT)*5;
//var xrr = 0;
//api.scrollTo(parseInt(xrr), parseInt(delt));
////alert(deltInt);
//return false;
//});
