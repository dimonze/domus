if (String(document.cookie).indexOf('js_on') == -1) {
  document.cookie = 'js_on=1; path=/';
}

var gmap = null;
if(typeof google != "undefined"){
  var gmap_options = {
    mapTypeId: google.maps.MapTypeId.ROADMAP,
    scrollwheel: false,
    zoom: window.gmap_zoom == undefined ? 16 : window.gmap_zoom
  };
}
var gmap_marker_options = {};
var metrika_counters = ['yaCounter7508101', 'yaCounter883242'];

var spec_banners = {
  1 : new Array(),
  2 : new Array(),
  3 : new Array(),
  4 : new Array(),
  5 : new Array()
}

var spec_banners_backup = {
  1 : new Array(),
  2 : new Array(),
  3 : new Array(),
  4 : new Array(),
  5 : new Array()
}


var zones_loaded = false;
var OA_output = new Array();
var OA_zones = {
  'zone_75'   : 75,
  'zone_76'   : 76,
  'zone_77'   : 77,
  'zone_78'   : 78,
  'zone_79'   : 79,
  'zone_80'   : 80,
  'zone_233'  : 233,
  'zone_242'  : 242,
  'zone_243'  : 243,
  'zone_244'  : 244,
  'zone_245'  : 245,
  'zone_246'  : 246,
  'zone_247'  : 247,
  'zone_248'  : 248,
  'zone_249'  : 249,
  'zone_250'  : 250,
  'zone_251'  : 251,
  'zone_303'  : 303,
  'zone_304'  : 304,
  'zone_305'  : 305,
  'zone_306'  : 306,
  'zone_307'  : 307
};

if (String(window.location.href).match(/(search|prodazha|arenda).*/)) {
  var search_zones = {
    'zone_74-1' : 74,
    'zone_74-2' : 74,
    'zone_74-3' : 74,
    'zone_74-4' : 74,
    '2001'  : 333,
    '2002'  : 333,
    '2003'  : 333,
    '2004'  : 333
  }; // 15 per page, 1 banner after any 3;
  OA_zones = $.extend({}, OA_zones, search_zones);
}

//Note! "u\/novostroyki" matches "mesto.ru/novostroyki" and not matches "mesto.ru/special/novostroyki"
if(String(window.location.href).match(/u\/novostroyki\-v.*/)) {
  var search_zones = {
    '2005'  : 333,
    '2006'  : 333,
    '2007'  : 333
  } // 15 per page, 1 banner after any 3, plus 3 banners at top of search results
  OA_zones = $.extend({}, OA_zones, search_zones);
}

var _order = {
  head   : {},
  normal : OA_zones,
  inline : {},
  right  : {},
  left   : {},
  tail   : {}
};

var OA_zones_count = new Object();
if ('undefined' == typeof OA_remap) OA_remap = {};

function OA_show() {}

var _global_inline_blocks_counter = 0;

function mesto_bind(id, key, group)
{
  //id == 333 && console.log('mesto_bind('+id+','+key+','+group+')');
  if (OA_zones_count[id]) {
    key && !OA_zones[key] && OA_zones_count[id]++;
  }
  else {
    OA_zones_count[id] = 1;
  }

  if (!key) {
    key = 'zone_' + id + '-' + OA_zones_count[id];
  }
  group = (_order[group] && group) || 'normal';
  if (!zones_loaded) {
    _order[group][key] = id;
    OA_zones = $.extend({}, _order['head'], _order['normal'], _order['inline'], _order['right'], _order['left'],  _order['tail']);
    document.writeln('<div class="mesto-banner" id="mesto-' + key + '"></div>');
  }
  else {
    //console.log('call put_banner('+ key +') from mesto_bind()');
    put_banner(key);
  }
}

function hide_empty_block()
{
  $('.index-adv-teaser-wt div').each(function (){
    if($(this).hasClass('closer') || $(this).hasClass('closest')){
      if ($(this).length != 0){
        if ($(this).find('a').length == 0) {
          $(this).parent().css('display', 'none');
        }
        else {
          $(this).parent().show();
        }
      }
    }
  });

  $('.grey-adv-box .content').each(function(){
    if ($(this).length != 0){
      if ($(this).find('a').length == 0) {
        $(this).parent().css('display', 'none');
      }
      else {
        $(this).parent().show();
      }
    }
  });

  $('.searchResultItem.spec-lot .padding_6').each(function(){
    if ($(this).length != 0){
      if ($(this).find('a').length == 0) {
        $(this).parent().css('display', 'none');
      }
      else {
        $(this).parent().show();
      }
    }
  });

  $('.aside-adv-box').each(function(){
    if ($(this).length != 0){
      if ($(this).find('a').length == 0) {
        $(this).css('display', 'none');
      }
      else {
        $(this).show();
      }
    }
  });

  $.each($('.spec-lot'), function() {
    if ($(this).find('div.padding_6 div').length != 0) {
      if ($(this).find('div.padding_6 div a').length != 0) {
        $(this).show();
      }
    }
  });
}

function OA_zone_remap(key)
{
  $.each(OA_remap || [], function(s_id, t_id) {
    var regex = new RegExp('^(zone_)' + s_id + '(-[0-9]+)?$');
    key = key.replace(regex, '$1' + t_id + '$2');
  });

  return key;
}

(function() {
  if (OA_remap) {
    var _remapped = {};
    $.each(OA_zones, function(key) {
      key = OA_zone_remap(key);
      _remapped[key] = key.replace(/^zone_([0-9]+)(-[0-9]+)?$/, '$1');
    });
    OA_zones = _remapped;
  }
})();

function shuffle (inputArr) {
  var valArr = [],
    k = '',
    i = 0,
    strictForIn = false,
    populateArr = [];

  for (k in inputArr) { // Get key and value arrays
    if (inputArr.hasOwnProperty(k)) {
      valArr.push(inputArr[k]);
      if (strictForIn) {
        delete inputArr[k];
      }
    }
  }
  valArr.sort(function () {
    return 0.5 - Math.random();
  });

  // BEGIN REDUNDANT
  this.php_js = this.php_js || {};
  this.php_js.ini = this.php_js.ini || {};
  // END REDUNDANT
  strictForIn = this.php_js.ini['phpjs.strictForIn'] && this.php_js.ini['phpjs.strictForIn'].local_value && this.php_js.ini['phpjs.strictForIn'].local_value !== 'off';
  populateArr = strictForIn ? inputArr : populateArr;

  for (i = 0; i < valArr.length; i++) { // Repopulate the old array
    populateArr[i] = valArr[i];
  }

  return strictForIn || populateArr;
}

function get_from_backup_and_shuffle(id) {
  var id   = id || false;
  var from = id || 1;
  var to   = id || 5;
  for (i=from;i<=to;i++){
    temp_arr = spec_banners_backup[i].slice(0);
    temp_arr = shuffle(temp_arr);
    spec_banners[i] = temp_arr.slice(0);
  }
}

function put_banner(key) {
  var oa_key = OA_zone_remap(key);
  $('#mesto-' + key + ', #mesto-' + oa_key).replaceWith(OA_output[oa_key]);
}

function show_zones() {
  zones_loaded = true;

  $.each(OA_output, function(key){
    if($(this).length > 1 && key > 1000 && key < 9999 && OA_output[key].indexOf('put_banner') === -1) {
      not_add_to = Math.floor(key/1000);
      for(i=1;i<=5;i++) {
        if(not_add_to != i) {
          spec_banners_backup[i].push(key);
        }
      }
    }
  });

  get_from_backup_and_shuffle();

  $.each(_order, function(key){
    $.each(_order[key], function(o_key){
      put_banner(o_key);
    });
  });

  hide_empty_block();
};

function counterGoal(type) {
  $.each(metrika_counters, function(i, cnt) {
    try {window[cnt].reachGoal(type);}
    catch (e) {}
  })

  try {pageTracker._trackPageview('/' + type);}
  catch (e) {}

  try {_gaq.push(['_trackPageview', '/' + type]);}
  catch (e) {}

  try {
    var pageTracker = _gat._getTracker('UA-11545952-1');
    pageTracker._trackPageview('/' + type);
  }
  catch (e) {}
}

function counterHit(url) {
  url = url || location.href;

  $.each(metrika_counters, function(i, cnt) {
    try {window[cnt].hit(url);}
    catch (e) {}
  });

  try {pageTracker._trackPageview(url);}
  catch (e) {}

  try {_gaq.push(['_trackPageview', url]);}
  catch (e) {}

  try {
    var pageTracker = _gat._getTracker('UA-11545952-1');
    pageTracker._trackPageview(url);
  }
  catch (e) {}
}


$(function() {
  $('.free-consult').show();
  if ($(window).width() < 1024) $('.free-consult').addClass('free-consult-short');

  $('.free-consult').show().find('.close').bind('click', function(){

    var div = $(this).closest('div');
    div.toggleClass('free-consult-short');
    if (div.hasClass('free-consult-short')) {
      document.cookie = 'uglybutton=hide; path=/;';
    } else {
      document.cookie = 'uglybutton=show; path=/;';
    }
    return false;
  });


  $('form.statParams').each(function(){
    $(this).find('.datepicker').datepicker({
      dateFormat: 'dd.mm.yy',
      showOn: 'button',
      buttonImage: '/images/blank.gif',
      buttonImageOnly: true
    });
  });
  $('form.pay-import').each(function(){
    $(this).find('.datepicker').datepicker({
      dateFormat: 'dd.mm.yy',
      showOn: 'both',
      buttonImage: '/images/blank.gif',
      buttonImageOnly: true,
      minDate: new Date(),
      maxDate: '+3m'
    });
  });

  /**
   * переключение табов квартиры / новостройки
   */
//  $('div.house-tabs').each(function(){
//    $(this).find('a').live('click', function(){
//
//      var li = $(this).parent(),
//          cu = li.siblings('.current').removeClass('current').find('span'),
//          index = li.prevAll().length;
//
//      cu.replaceWith('<a href="#">'+ cu.text() +'</a>');
//
//      var current_tab = $(this).parent().addClass('current');
//      var tab_text = current_tab.find('a').text();
//
//      current_tab.find('a').replaceWith('<span>'+ tab_text + '</span>');
//
//      li.closest('div.house-tabs').siblings('.tabbed').find('form').hide().eq(index).show();
//
//      return false;
//    });
//  });

if ($.browser.msie) {$('#front #wrapper').css({zoom: 1});}
  /*animateAdvQuantity(123);*/

  $.extend({
    escapeAttr: function(val) {
      return '"' + String(val).split('"').join('\\"') + '"';
    }
  });

  var current_region = getcookie('current_region');
  if (current_region) {
    $('.cityScroll .current').removeClass('current').show();
    $('.cityScroll li[rel='+current_region+']').addClass('current').hide();

    $('#citySelect span').text($('.cityScroll li[rel='+current_region+']').text());
    $('.cityMenu').css('background-image',
      String($('.cityMenu').css('background-image')).replace(/\d+\.gif/, current_region+'.gif')
    );
  }

  /**
   * импорт XML-файла - таб-навигация
   */
  $('div.ad-import').each(function(){
    $(this).find('div.tabs li a').click(function(){
      var li = $(this).closest('li');
      if (!li.hasClass('current')) {
        li.addClass('current').siblings().removeClass('current');
        li.closest('div.tabs').children('div').hide().eq(li.prevAll().length).show();
      }
      return false;
    });
  });

  $('div.realtor-rate').each(function(){
    var box = $(this);
    $(this).find('a.inner').click(function(){
      box.find('div.scroller').toggle();
      return false;
    });
  });


  $("#global-loading").bind("ajaxSend", function(){
    if (window.loading_text) {
      $(this).addClass('withtext');
    }
    $(this).text(window.loading_text || '').show();
  }).bind("ajaxComplete", function(){
    $(this).hide();
  }).hide();


  $('a.post-toggle').live('click', function() {
    var $link = $(this);

    $.post($link.attr('href'), function(data) {
      $link = $('a.post-toggle[href=' +  $link.attr('href')+ ']');

      toggle_active_class($link);

      if ($link.is('.active[active]')) {
        $link.text($link.attr('active')).attr('title', $link.attr('active'));
      }
      else if ($link.not('.active').is('[inactive]')) {
        $link.text($link.attr('inactive')).attr('title', $link.attr('inactive'));
      }

      $link.is('.action_03') && update_compare_link(data);
    });

    return false;
  });

  $('a.post-load').live('click', function() {
    counterGoal('T_LOT_PHONE');
    var $link = $(this);

    $.get($link.attr('href'), function(data) {
      $link.after(data).remove();
    });

    return false;
  });

  $('.flash.success').each(function() {
    var html = $(this).html();

    var share = html.match(/#share-(\d+)#/)
    if (share) {
      var tag = $('.searchResultItem[rel=' + share[1] + ']').next().find('.insert-jj').html();
      if (tag) {
        tag = '<div class="insert-jj-popup">'
          + '<p>Вы можете опубликовать ссылку на объявление в социальные сети</p>'
          + '<p>' + $('.searchResultItem[rel=' + share[1] + ']').find('h2').html() + '</p>'
          + '<div class="insert-jj">' + tag + '</div></div>';
        show_popup(tag, 'loginwindow', 'Опубликовать в соц. сетях');
      }

      $(this).html(html.replace(share[0], ''));
    }
  });

  function update_compare_link(data) {
    var $link = $('.viewCompareList');
    data = eval('(' + data + ')');
    if (data.nb_items > 0) {
      $link.find('span').text('(' + data.nb_items + ')').show();
      !$link.is('.active') && toggle_active_class($link);
    }
    else {
      $link.find('span').hide();
      $link.is('.active') && toggle_active_class($link);
    }
  }

  function process_toggle(element, e) {
    var $toggle = $( element.attr('toggle') );
    if ($toggle.css('display') == 'block') $toggle.hide();
    else {
      $('a[toggle]').each(function(){
        $($(this).attr('toggle')).hide();
      });
      $toggle.show();

      $('body').bind('click.toggle', function(e) {

        if ($toggle.is($(e.target).closest('a[toggle]').attr('toggle'))) return;
        $toggle.hide();
        $('body').unbind('click.toggle');
      });
    }
    return false;
  }

  $('a.switcher').click(function(e) {
    process_toggle($(this), e);
  });

  $('a[toggle]').live('click', function(e){
    process_toggle($(this), e);
  });

  if(typeof google != "undefined"){
  //Simple marker images
  window.domus_icon = new google.maps.MarkerImage(
    "/images/marker.png",
    new google.maps.Size(16, 35),
    new google.maps.Point(0,0),
    new google.maps.Point(9, 35)
  );
  domus_icon.image = "/images/marker.png";

  window.domus_icon.shadow = new google.maps.MarkerImage(
    "/images/marker-shadow.png",
    new google.maps.Size(27, 35),
    new google.maps.Point(0,0),
    new google.maps.Point(9, 35)
  );
  domus_icon.shadow.image = "/images/marker-shadow.png";

  window.domus_icon.hover = new google.maps.MarkerImage(
    "/images/marker-hover.png",
    new google.maps.Size(16, 35),
    new google.maps.Point(0,0),
    new google.maps.Point(9, 35)
  );
  window.domus_icon.hover.image = "/images/marker-hover.png";

  //Multiple marker images
  window.domus_icon_multiple = new google.maps.MarkerImage(
    "/images/marker-many.png",
    new google.maps.Size(25, 37),
    new google.maps.Point(0,0),
    new google.maps.Point(9, 35)
  );
  window.domus_icon_multiple.image = "/images/marker-many.png";

  window.domus_icon_multiple.shadow = new google.maps.MarkerImage(
    "/images/marker-many-shadow.png",
    new google.maps.Size(32, 37),
    new google.maps.Point(0,0),
    new google.maps.Point(9, 35)
  );
  window.domus_icon_multiple.shadow.image = "/images/marker-many-shadow.png";

  window.domus_icon_multiple.hover = new google.maps.MarkerImage(
    "/images/marker-many-hover.png",
    new google.maps.Size(25, 37),
    new google.maps.Point(0,0),
    new google.maps.Point(9, 35)
  );
  window.domus_icon_multiple.hover.image = "/images/marker-many-hover.png";

  gmap_marker_options = {icon: domus_icon, shadow: domus_icon.shadow};
  gmap_marker_multiple_options = {icon: domus_icon_multiple, shadow: domus_icon_multiple.shadow};

  $('.gmap').each(function() {
    var $this = $(this);
    try {
      if ($this.attr('latitude') && $this.attr('longitude')) {
        var coords = new google.maps.LatLng($this.attr('latitude'), $this.attr('longitude'));
        gmap = new google.maps.Map(this, $.extend(gmap_options,{
          center: coords
        }));
        new google.maps.Marker($.extend( gmap_marker_options, {
            position: coords,
            map: gmap
        }));
      }
    } catch(e) {
      $(this).hide();
      $(this).closest('.mapBox').hide();
    }
  });

  var sv = null;
  $('.gmapstreetview').each(function(){
    var $this = $(this);
    sv = new google.maps.StreetViewService();
    var coords = new google.maps.LatLng($(this).attr('latitude'), $(this).attr('longitude'));
    sv.getPanoramaByLocation(coords, 50, function(data, status){
      if (status == google.maps.StreetViewStatus.OK) {
        var panorama = new google.maps.StreetViewPanorama($this.get(0), {
          position: coords,
          pov: { heading: 0, pitch: 0}
        });
        gmap.setStreetView(panorama);
        google.maps.event.addListener(panorama, 'position_changed', function() {
          panorama.setPov({
            heading: google.maps.geometry.spherical.computeHeading( panorama.getPosition(), coords ),
            pitch: 0
          });
        });
      } else {
        $this.hide();
      }
    });
  });

  $('.mapBox .collapse').click(function() {
    var $box = $(this).closest('.mapBox').find('.gmap, #gmap');
    if ($box.is(':visible')) {
      $box.slideUp('fast', function(){
        if(gmap != undefined && gmap != null) google.maps.event.trigger(gmap, 'resize');
      });
      if ($('input[type=checkbox][name=filter\[map_search\]]')){
        $('input[type=checkbox][name=filter\[map_search\]]').removeAttr('checked');
      }
      var tmp = '';
      if ($(this).is('.collapse-full-text')) {
        tmp = 'Развернуть карту';
      }
      else {tmp = 'Равернуть';}

      $(this).text(tmp);
    }
    else {
      $box.slideDown('fast', function(){
        if(gmap != undefined && gmap != null) google.maps.event.trigger(gmap, 'resize');
      });
      if ($('input[type=checkbox][name=filter\[map_search\]]')){
        $('input[type=checkbox][name=filter\[map_search\]]').attr('checked', 'checked');
      }
      var tmp = '';
      if ($(this).is('.collapse-full-text')) tmp = 'Свернуть карту';
      else tmp = 'Свернуть';
      $(this).text(tmp);
    }
    return false;
  });
  }

  if (typeof $('.scrollBox').scrollable == 'function') {
    var scrollable_int = window.setInterval(function() {
      // damn bug when containers resized after DOM is loaded
      if ($('.scrollBox').innerWidth() < 500) {
        $('.scrollBox').scrollable({
          prev: '.productImagesBox a.scrollLeft',
          next: '.productImagesBox a.scrollRight',
          noscroll: function() {
            $('.productImagesBox a.scrollLeft').addClass('scrollLeft-disabled');
            $('.productImagesBox a.scrollRight').addClass('scrollRight-disabled')
          },
          atStart: function() {$('.productImagesBox a.scrollLeft').addClass('scrollLeft-disabled')},
          inMiddle: function() {
            $('.productImagesBox a.scrollLeft').removeClass('scrollLeft-disabled');
            $('.productImagesBox a.scrollRight').removeClass('scrollRight-disabled');
          },
          atEnd: function() {$('.productImagesBox a.scrollRight').addClass('scrollRight-disabled')}
        });
        window.clearInterval(scrollable_int);
      }
    }, 100);
    $('a.scrollLeft, a.scrollRight').click(function() {return false;});
  }

  // gallery
  var $big_img_container = $('.productImagesBox .big-img-card').find('td').length ? $('.productImagesBox .big-img-card').find('td') : $('.productImagesBox .big-img-card').empty();

  $('.scrollBox img').each(function() {
    $big_img_container.append(
      '<a href="' + $(this).attr('data-big') + '" rel="images"><img src="'
      + $(this).attr('data-medium') + '" alt="' + $(this).attr('alt') + '"></a>'
    );
  });

  $('.scrollBox img').css({cursor: 'pointer'}).click(function() {
    var index = $(this).parent().prevAll().length;
    $big_img_container.find('a').eq(index).show().siblings().hide();
    $(this).addClass('current').siblings('.current').removeClass('current');
    return false;
  }).eq(0).trigger('click');

  if (typeof $('.big-img-card a').fancybox == 'function') {
    $('.big-img-card a').fancybox({
      overlayShow: false,
      titleShow: false,
      hideOnContentClick: true
    });
  }
  // /gallery


  $('a.popup').live('click', function() {
    var $this = $(this);

    if ($this.is('.delete') && !confirm('Действительно удалить?')) {
      return false;
    }

    $.get($this.attr('href'), function(data) {
      toggle_active_class($this);
      var $popup = show_popup(data, $this.attr('rel'),'');

      var url = parse_url($this.attr('href'));
      if (url.forward) {
        var $forward = $popup.find('form').find('input[name=forward]');
        if (!$forward.length) {
          $forward = $('<input/>').attr({name: 'forward', type: 'hidden'});
          $popup.find('form').prepend($forward);
        }
        $forward.val(urldecode(url.forward));
      }
      $('body').one('popup_close', function() {toggle_active_class($this);});
    });

    return false;
  });

  $('a.delete[name="delete_source"]').live('click', function(){
    if(!confirm('Действительно удалить?')) {
      return false;
    }
  });

  $('.register-form .popupSubmit').live('click',function() {
    counterGoal('T_REGISTRATION');
  });

  $('.addForm .addButton input:submit').live('click', function() {
    counterGoal('T_LOT_ADD');
  });

  $('a[href^=http://media.mesto.ru/]').live('click', function() {
    counterGoal('T_AD_CLICK');
  });

  $('div.hypothec a').live('click', function() {
    counterGoal('T_100_KREDITOV');
  });

  $('div.class_el_main_div[id^=ProactiveSupport] a[href*=launch_support]').live('click', function() {
    counterGoal('T_MARVA');
  });

  $('input[type="button"].realtor-on-duty').live('click', function(){
    counterGoal('T_REALTOR_ON_DUTY');
  });



  $('form.ajax-validate').bind('submit', ajax_validate);

  if ($.browser.msie) {
    $('input[type=checkbox]').each(function() {
      $(this).bind('click', function() {
        $(this).trigger('change').blur();
      });
    });
  };

  $('a.form-submit-button').live('click', function() {
    $(this).closest("form").submit();
  });

  $('.productMenu a.print').live('click', function() {
    window.print();
    return false;
  });

  $('a.link-back').bind('click', function() {
    try {window.history.back()} catch (exception) {}
    return false;
  });

  $('input[init]').each(function() {
    var $input = $(this);
    $input
      .bind('focus', function() {
        if ($input.val() == $input.attr('init')) {
          $input.val('').css('color', 'black');
        }
      })
      .bind('blur', function() {
        if (!$input.val()) {
          $input.val($input.attr('init')).css('color', 'silver');
        }
      })
      .trigger('blur')
      .closest('form')
        .bind('submit', function() {
          if ($input.val() == $input.attr('init')) {
            $input.val('');
          }
        });
  });

  window.setInterval(function() {

    if (String(window.location.href).match(/#c:\w+/)) {

      var data = String(window.location.href).split('#')[1];

      var _popup_data = data.split('$')[0].split(';');
      var popup_data = {};
      for (i in _popup_data) {
        var row = _popup_data[i].split(':');
        popup_data[row[0]] = urldecode(row[1]);
      }

      var params = {};
      if (data.split('$')[1]) {
        _popup_data = data.split('$')[1].split(';');
        for (i in _popup_data) {
          var row = _popup_data[i].split(':');
          params[row[0]] = urldecode(row[1]);
        }
      }

      var $link = $('a.popup[p=' + popup_data.c + ']').eq(0);
      var link_url = $link.attr('href');



      $link.attr('href', build_query(link_url, params)).trigger('click').attr('href', link_url);

      window.location.href = String(window.location.href).split('#')[0] + '#';

      $('body').one('popup_load', function(e, $popup) {
        for (var k in popup_data) {
          var name = String(k).replace('[', '\[').replace(']', '\]');
          var $input = $popup.find(':input[name=' + name + ']');
          if ($input.is(':radio,:checkbox')) {
            $input.filter('[value=' + popup_data[k] + ']').attr('checked', true).trigger('change');
          }
          else {
            $input.val(popup_data[k]).trigger('change');
          }
        }
      });
    }
  }, 200);

  $('div.prompt a.close').click(function(){
    cookie_expire = new Date();
    cookie_expire.setMonth(cookie_expire.getMonth() + 2);

    if ($('div.prompt input#donotshowanymore').attr('checked') == true){
      document.cookie = 'prompt=off; path=/; expires=' + cookie_expire.toString() + ';';
    }
    $(this).closest('div.prompt').hide();return false;
  })

  $('a.no-click').click(function(){
    return false;
  });

  $('.top-nav .inner > a','#header', '.cityMenu').click(function(){
    var li = $(this).parent();
    li.siblings()
        .removeClass('expanded')
        .removeClass('current-expanded')
        .removeClass('expanded-right')
        .removeClass('current-expanded-right');
    if (li.hasClass('current')) {
      li.toggleClass('current-expanded');
      if (li.is(':last-child')) li.toggleClass('current-expanded-right');
    } else {
      li.toggleClass('expanded');
      if (li.is(':last-child')) li.toggleClass('expanded-right');
    }
    if ($.browser.msie) {
      $('div.dropbox-tl','ul.top-nav').css({width: $(this).closest('li').find('div.dropbox-wrap').width()+2});
      if ($.browser.version == '6.0') {
        var db = li.find('div.dropbox');
        db.width(db.find('ul').width()+24);
      }

    }

    $('body').bind('click.topnav',function(e){
      if ($(e).closest('div.dropbox').length == 0 && e.className != 'dropbox') {
        $('ul.top-nav li').siblings()
        .removeClass('expanded')
        .removeClass('current-expanded')
        .removeClass('expanded-right')
        .removeClass('current-expanded-right');

        $('body').unbind('click.topnav');
      }
    });

    return false;
  });

  $('.realty-scroller[rel]').each(function() {
    var $sc = $(this);
    var type = $(this).attr('rel');
    var $container = $(this).find('div.cut');
    var $container_wrapper = $container.parent();
    var scroll_width = $container_wrapper.width()
    var is_search = $(this).find('div.scroller[rel]').attr('rel');
    var cParams = $(this).attr('data-params');
    if(cParams != undefined) cParams = window.scroller_params[cParams];

    var page = parseInt($container.attr('page') || 1, 10);
    var nb_pages = parseInt($container.attr('nb_pages'), 10);
    if (nb_pages && page == nb_pages) {
      $(this).find('a.next').addClass('next-disabled');
    }

    $(this).find('a.next, a.prev').bind('click', function() {
      if ($container.is('.loading')) {
        return false;
      }

      var $link = $(this);
      var page = parseInt($container.attr('page') || 1, 10) + ($link.is('.prev') ? -1 : +1);
      var nb_pages = parseInt($container.attr('nb_pages'), 10);

      if ($link.is('.prev-disabled') || $link.is('.next-disabled')){
        return false;
      }
      else {
        if ($link.is('.prev')) {
          var $next = $sc.find('a.next');
          if ($next.is('.next-disabled')){
            $next.removeClass('next-disabled');
          }
        }
        if ($link.is('.next')) {
          var $prev = $sc.find('a.prev');
          if ($prev.is('.prev-disabled')){
            $prev.removeClass('prev-disabled');
          }
        }
      }

      $container.addClass('loading');
      if (is_search) {
        lot_id = $(this).closest('div.realty-scroller[id]').attr('id');
        check_geo = $(this).closest('div.realty-scroller[id]').find('#check_geo').val();
        check_price = $(this).closest('div.realty-scroller[id]').find('#check_price').val();
        check_regular_price = $(this).closest('div.realty-scroller[id]').find('#check_regular_price').val();
        var params = {page: page, type: type, id: lot_id};
        if (check_price == undefined && check_geo == undefined) {
          url = '/search/getlast';
        }
        else {
          url = '/search/similar';
          params = {page: page, type: type, id: lot_id, check_price: check_price, check_geo: check_geo}
          if(check_geo) params.check_price = check_price;
          if(check_regular_price) params.check_regular_price = check_regular_price;
        }
      }
      else {
        var params = {page: page, homepage: true, images: true, type: type, order: 'rating desc'};
        if(cParams != undefined) params.params = JSON.stringify(cParams);
        url = '/search/getlast';
      }
      $.getJSON(url, params, function (data){
        $container.attr({page: page, nb_pages: data.nb_pages});

        if ($link.is('.prev')) {
          $container.prepend(data.lots.join(''));
          $container_wrapper.scrollLeft(scroll_width).animate(
            {scrollLeft: 0},
            'normal',
            function() {
              $container.removeClass('loading')
                .find('div').slice(4).remove();
            }
          );
        }
        else {
          $container.append(data.lots.join(''));
          $container_wrapper.animate(
            {scrollLeft: scroll_width},
            'normal',
            function() {
              $container.removeClass('loading')
                .find('div').slice(0, 4).remove();
              $container_wrapper.scrollLeft(0);
            }
          );
        }

        $('#global-loading').hide();
      });

      if (nb_pages && page == nb_pages) {
        if ($link.is('.next')) {
          $link.addClass('next-disabled');
        }
      }
      if (page == 1) {
        if ($link.is('.prev')) {
          $link.addClass('prev-disabled');
        }
      }
      return false;
    });
  });

  $('.search-description .showClosedDesc').live('click', function(){
    $(this).hide().next().show();
  });

  /*new buildings nav*/
  var nbNav = $('.nb-nav__item', '#nb-nav');
  var nbNavList = nbNav.find('.nb-subnav__list')
  nbNav.hover(
    function() {
      if (!nbNavList.is(':animated') && !$(this).hasClass('nb-nav__item_inactive')){
        $(this).addClass('nb-nav__item_active').find('.nb-subnav__list').slideDown(300);
      }
    },
    function() {
      nbNavList.stop(true, true).slideUp(0, function(){
          nbNav.removeClass('nb-nav__item_active');
      });
    }
  );
  if(!!('ontouchstart' in window)){//check for touch device
    nbNav.unbind('mouseenter mouseleave');
    nbNav.click(function(){
      if (!nbNavList.is(':animated') && !$(this).hasClass('nb-nav__item_inactive')){
        if ($(this).hasClass('nb-nav__item_active')){
          $(this).find('.nb-subnav__list').slideUp(200, function(){
              nbNav.removeClass('nb-nav__item_active');
          });
        } else {
          nbNav.removeClass('nb-nav__item_active');
          nbNavList.hide();
          $(this).addClass('nb-nav__item_active');
          $(this).find('.nb-subnav__list').slideDown(300);
        }
      }
    })
  }
  $('.nb-nav__item_inactive', '#nb-nav').click(function(){
    nbNav.removeClass('nb-nav__item_active');
    nbNavList.hide();
  })
  $(document).click(function(event){
    if($(event.target).closest(".nb-nav__item").length ) return;
    nbNav.removeClass('nb-nav__item_active');
    nbNavList.hide();
    event.stopPropagation();
  });

});

$(document).ready(function(){
   $(window).scroll(function(){
      fixedMenu();
   });
   fixedMenu();
});

function fixedMenu()
{

 if ($(window).scrollTop() >= $('#header').outerHeight() + $('#homepage_header').outerHeight()) {
      $('#nb-nav').addClass('nb-navFixed');
    }
    else {
       $('#nb-nav').removeClass('nb-navFixed');
    }
}


function toggle_active_class(o) {
  var $o = $(o);
  var classes = String($o.attr('class')).split(/\s/);
  for (i in classes) {
    var c = String(classes[i]);
    if (c.match(/action|maximize|viewCompareList/) && c.indexOf('active') == -1) {
      $o.toggleClass(c+'-active');
    }
  }
  $o.toggleClass('active');
}

/**
 *
 * @param html - разметка содержимого попапа
 * @param css_class - класс(ы), добавляемые попапу
 * @param title - заголовок попапа
 * @param extanded - параметр html, передаваемый в функцию, содержит не только внутреннюю часть попапа, но заголовок
 */
function show_popup(html, css_class, title, extanded) {

  var $popup = $('<div class="popupWrap"/>').addClass(css_class);

  if ($.browser.msie && $.browser.version == 6) {
    $('.sortBox select').css('visibility','hidden')
  }

  if ($popup.hasClass('.withTab') || $popup.hasClass('.roadbox')) {
    $('body').append('<div class="closePopupWithTab"></div>');
  }

  $popup.append('<div class="popupTop"><div class="popupTopL">&nbsp;</div><div class="popupTopR">&nbsp;</div><div class="popupTopC">&nbsp;</div></div>');
  if (extanded == undefined) {
   $popup.append('<div class="popupCenter"/>');
  }
  $popup.append('<div class="popupBottom"><div class="popupBottomL">&nbsp;</div><div class="popupBottomR">&nbsp;</div><div class="popupBottomC">&nbsp;</div></div>');
  var close_link = '<a class="popupClose" href="#"><img src="/images/blank.gif" alt="Закрыть" title="Закрыть" /></a>';

  if (title && extanded == undefined) {
    html = '<div class="body"><fieldset><legend><span class="prependClose">'+title+'</span></legend>'+html+'</fieldset></div>';
  }

  if (extanded == undefined) {
    $popup.find('.popupCenter').append(html);
  } else {
    $popup.find('div.popupTop').after(html);
  }

  $('.popupClose').trigger('click');

  if (extanded == undefined) {
    $popup.find('.prependClose').prepend(close_link);
  }

  $popup.find('.popupClose').click(function() {
    if($('.no-pay-import-popup').attr('checked') == true) {
      var cookie_expire = new Date(2050, 1, 1);
      document.cookie = 'nopayimportpopup=on; path=/; expires=' + cookie_expire.toString() + ';';
    }
    $('body').trigger('popup_close');
    if ($.browser.msie && $.browser.version == 6) {

        $('.sortBox select').css('visibility','visible');

    }
    $('.closePopupWithTab').hide();
    $(this).closest('.popupWrap').remove();
    return false;
  });

  $('.closePopupWithTab').click(function() {
    $(this).hide();
    $('.popupWrap').remove();
  });

  $popup.find('form.ajax-validate').bind('submit', ajax_validate);
  $popup.show().appendTo('body').animate(
    {
      marginTop: '+=' + $(window).scrollTop()
    },
    600,
    'linear',
    function() {
      $popup.find(':input:first').focus();
    }
  );

  $('body').trigger('popup_load', [$popup]);
  return $popup;
}

function ajax_validate() {
  var $ajaxForm = $(this);

  if($ajaxForm.attr('id') == 'landing_page_form') {
    $('#landing_query_text').val(window.location.hash);
  }

  var $ajaxUp = $ajaxForm.find('button.ajax-upload');
  var files_pending = false;
  $ajaxUp.closest('tr,fieldset').find('input[type=file],input[type=text]').each(function() {
    if ($(this).is(':visible') && !$(this).is('[disabled]') && $(this).val() && !$(this).attr('notchanged')) {
      files_pending = true;
      return false;
    }
  });

  if ($ajaxUp.length && files_pending) {
    $ajaxUp.bind('ajax-loaded', function() {
      $ajaxForm.trigger('submit');
    });
    $ajaxUp.trigger('click');
    $ajaxForm.find('[type=submit],[type=reset]').attr('disabled', true);
    return false;
  }

  var send = function(data) {
    var stop_processing = false;
    $.ajax({
      url: build_query($ajaxForm.attr('action'), {validate: true}),
      type: 'post',
      cache: false,
      data: data,
      dataType: 'json',
      complete: function() {
        if (!stop_processing) {
          $ajaxForm.find('[type=submit],[type=reset]').removeAttr('disabled');
          $ajaxForm.trigger('post-submit');
        }
      },
      success: function(response) {
        if (response.valid) {
          if (response.reload) {
            if ($('form.searchForm').length == 1){
              $('form.searchForm').find('input[type=submit]').trigger('click');
            }
            else{
              if (response.location && response.location != ''){
                window.location = '/';
              }
              else {
                location.reload();
              }
            }
          }
          else if (response.message) {
            $ajaxForm.find('fieldset').slice(1).remove();
            $ajaxForm.find('legend')
              .nextAll().remove().end()
              .after('<div>' + response.message + '<p>&nbsp;</p></div>');
          }
          else if (response.callback) {
            window[response.callback.name](response.callback.param || null)
          }
          else if (response.load) {
            $.get(response.load.url, function(data) {
              show_popup(data, response.load.rel || 'auth');
            });
          }
          else if (response.locate) {
            location = response.locate;
          }
          else {
            $ajaxForm.trigger('pre-submit');
            $('select[name]', $ajaxForm).each(function() {
              if (String($(this).val()).replace(/\s+/, '') == '') $(this).remove();
            });
            $ajaxForm.unbind('submit').submit();
            stop_processing = true;
          }

          if (response.close) {
            $ajaxForm.closest('.popupWrap').remove();
          }

          !stop_processing && $ajaxForm.find(':input').attr('disabled', true);
        }
        else if (response.errors) {
          for (name in response.errors.field) {
            var $container = $(':input[name='+name+'],:input[name*=\['+name+'\]]', $ajaxForm).eq(0)
              .bind('change', function() {
                $(this).closest('td,div').removeClass('formError')
                  .find('.errors').fadeOut('fast', function() {$(this).remove();});
              })
              .closest('td,div').eq(0).addClass('formError');
            //#18670 Correct error
            if(name == 'region_node') { //Node 2 and 3 always exists now
              if($('#dynamicform_region_node_2').val() == $('#dynamicform_region_node_1').val())
                $('.form-region_node td.field').css('background-position', '0 79px');
              response.errors.field[name] = 'Значение должно быть уникальным';
            }

            var $errorlist = $container.find('.errors');
            if ($errorlist.length == 0) {
              $errorlist = $container.append('<ul class="errors"/>').find('.errors');
            }

            message = response.errors.field[name];
            $('li', $errorlist).each(function() {
              if ($(this).text() == message) {
                message = null;
                return false;
              }
            });

            if (message) {
              $errorlist.append('<li>'+message+'</li>');
            }
          }
          var scrollToError = $('div,td').filter('.formError').filter(':first').find(':input').offset().top - 100;
          $(document).scrollTop(scrollToError);

        }
        else {
          location.reload();
        }
      }
    });
  }

  $ajaxForm.find('[type=submit],[type=reset]').attr('disabled', true);
  $ajaxForm.find('.formError').removeClass('formError');
  $ajaxForm.find('.errors').remove();

  $ajaxForm.trigger('pre-submit');
  window.setTimeout(function() {
    data = serialize_form($ajaxForm);
    send(data);
  }, 300);

  return false;
}

function serialize_form($form) {
  if (!$form.find(':input[name=referrer]').length) {
    $form.append($('<input/>').attr({name: 'referrer', type: 'hidden', value: document.referrer}));
  }

  data = [];
  $(':input[name]', $form).each(function() {
    $this = $(this);
    if (!$this.is(':checkbox,:radio') && String($this.val()).replace(/\s+/, '') == '')
      return true;
    if ($this.is(':checkbox,:radio') && !$this.is(':checked'))
      return true;
    if ($this.get(0).tagName == 'BUTTON') return true;

    data[data.length] = $(this).attr('name') + '=' + urlencode($this.val());
  });
  return data.join('&');
}



function urlencode( str ) {
    var histogram = {}, tmp_arr = [];
    var ret = (str+'').toString();

    var replacer = function(search, replace, str) {
        var tmp_arr = [];
        tmp_arr = str.split(search);
        return tmp_arr.join(replace);
    };

    // The histogram is identical to the one in urldecode.
    histogram["'"]   = '%27';
    histogram['(']   = '%28';
    histogram[')']   = '%29';
    histogram['*']   = '%2A';
    histogram['~']   = '%7E';
    histogram['!']   = '%21';
    histogram['%20'] = '+';
    histogram['\u20AC'] = '%80';
    histogram['\u0081'] = '%81';
    histogram['\u201A'] = '%82';
    histogram['\u0192'] = '%83';
    histogram['\u201E'] = '%84';
    histogram['\u2026'] = '%85';
    histogram['\u2020'] = '%86';
    histogram['\u2021'] = '%87';
    histogram['\u02C6'] = '%88';
    histogram['\u2030'] = '%89';
    histogram['\u0160'] = '%8A';
    histogram['\u2039'] = '%8B';
    histogram['\u0152'] = '%8C';
    histogram['\u008D'] = '%8D';
    histogram['\u017D'] = '%8E';
    histogram['\u008F'] = '%8F';
    histogram['\u0090'] = '%90';
    histogram['\u2018'] = '%91';
    histogram['\u2019'] = '%92';
    histogram['\u201C'] = '%93';
    histogram['\u201D'] = '%94';
    histogram['\u2022'] = '%95';
    histogram['\u2013'] = '%96';
    histogram['\u2014'] = '%97';
    histogram['\u02DC'] = '%98';
    histogram['\u2122'] = '%99';
    histogram['\u0161'] = '%9A';
    histogram['\u203A'] = '%9B';
    histogram['\u0153'] = '%9C';
    histogram['\u009D'] = '%9D';
    histogram['\u017E'] = '%9E';
    histogram['\u0178'] = '%9F';

    ret = encodeURIComponent(ret);

    for (search in histogram) {
        replace = histogram[search];
        ret = replacer(search, replace, ret) // Custom replace. No regexing
    }

    // Uppercase for full PHP compatibility
    return ret.replace(/(\%([a-z0-9]{2}))/g, function(full, m1, m2) {
        return "%"+m2.toUpperCase();
    });

    return ret;
}

function urldecode(str) {
  return decodeURIComponent(str).replace(/\+/g, '%20');
}


function ending(num, ending1, ending2, ending5, ending78){
    len = String(num).length;
    last =  String(num).split('')[len - 1];
    if (len > 1) {
      prev =  String(num).split('')[len - 2];
    }

    if (num > 6 && num <9 && ending78) {
      result = ending78;
    }
    else if (last == 1){
        if (len > 1){
            result = prev == 1 ? ending5 : ending1;
        }
        else{
            result = ending1;
        }
    }
    else if(last > 1 && last < 5){
        if (len > 1){
            result = prev == 1 ? ending5 : ending2;
        }
        else{
            result = ending2;
        }
    }
    else{
        result = ending5;
    }
    return result;
}

function parse_url(path, object, expand_as_arrays) {
  if (typeof object != 'object') {
    object = {};
  }
  path = String(path).split(/\?|&/);

  for (i in path) {
    if (!path[i]) {
      continue;
    }

    var row = String(path[i]).split('=');
    var key = row[0];

    if (!row[1]) {
      object['_base_'] = key;
    }
    else {
      if (expand_as_arrays && object[key]) {
        if (typeof object[key] == 'object') {
          object[key][object[key].length] = row[1];
        }
        else {
          object[key] = [object[key], row[1]];
        }
      }
      else {
        object[key] = row[1];
      }
    }
  }

  return object;
}

function build_query(path, query) {
  path = parse_url(path);
  switch (typeof query) {
    case 'string':
      path = parse_url(query, path);
      break;
    case 'object':
      for (q in query) path[q] = query[q];
      break;
  }

  for (i in path) {
   if (i == '_base_') {
     url = path[i] + '?';
   }
   else {
     url += i + '=' + urlencode(path[i]) + '&';
   }
  }

  return url.replace(/&$/, '');
}

function test_and_append(val, append) {
  if (typeof append != 'string') {
    append = ', ';
  }
  if (val && String(val).replace(/\s+/, '') != '') {
    return val + append;
  }
  else {
    return '';
  }
}

function is_city(region_id) {
  return region_id == 77 || region_id == 78;
}

function format_number(number) {
	number = parseInt(number, 10);

  if (number > 999 && number < 1000000 && number % 1000 == 0) {
    number = number / 1000 + ' тыс.';
  }
  else if (number > 999999 && number < 1000000000 && number % 1000000 == 0) {
    number = number / 1000000 + ' млн.';
  }
  else if (number > 999999999 && number < 1000000000000 && number % 1000000000 == 0) {
    number = number / 1000000000 + ' млрд.';
  }
  else {
    number = String(number);
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(number)) {
      number = number.replace(rgx, '$1' + ' ' + '$2');
    }
  }
	return number;
}
function unformat_number(number) {
  number = String(number);
  if (number.indexOf('тыс') != -1) {
    return parseInt(number.replace(/\D/g, '')) * 1000;
  }
  else if (number.indexOf('млн') != -1) {
    return parseInt(number.replace(/\D/g, '')) * 1000000;
  }
  else if (number.indexOf('млрд') != -1) {
    return parseInt(number.replace(/\D/g, '')) * 1000000000;
  }
  else {
    return parseInt(number.replace(/\D/g, '')) || 0;
  }
}

function getcookie(name) {
  var cookies = String(document.cookie).split('; ');
  for (i in cookies) {
    line = cookies[i].split('=');
    if (line[0] == name) {
      return line[1];
    }
  }
  return null;
}

function send_message_restrict() {
    $url = $(this).attr('href');
    $rel = $(this).attr('rel').split(',');
    $user_id = $rel[0].split(':');
    $lot_id = $rel[1].split(':');

    $.get('/user/pm/addmoderator?to=' + $user_id[1] + '&lot_id=' + $lot_id[1], function(data) {
      $('#global-loading').hide();
      toggle_active_class($(this));
      var $popup = show_popup(data, 'reg');

      $popup.find('select[name=email_themes]').bind('change', function(){
        if ($(this).find(':selected').text().length > 0){
          $theme_id = $(this).find(':selected').val();
          $('input[name=pm\[subject\]]').attr('value', $(this).find(':selected').text());
          $('input[name=pm\[lot_id\]]').attr('value', $lot_id[1]);
          $('textarea[name=pm\[message\]]').attr('value', $('input[type=hidden][name=themes_' + $theme_id + ']').val());
        }
        else {
          $('input[name=pm\[subject\]]').attr('value', '');
          $('input[name=pm\[lot_id\]]').attr('value', '');
          $('textarea[name=pm\[message\]]').attr('value', '');
        }
      });
    });
    return false;
  }


function animateAdvQuantity(count) {
  var interval = setInterval(function(){
    count--;
    if (count == 0) clearInterval(interval);
    animateNumber(0);
  }, 150);

}

function animateNumber(index) {
  var top_coords = [0, -59, -118, -176, -234, -292, -350, -408, -466, -524];
  var left_coords = [0, -31, -62];
  var speed = 50;
  var obj_array = $('div.adv-counter span');
  var count = obj_array.length;

  if (index == count) {obj_array.eq(0).before('<span class="num0"></span>');obj_array = $('div.adv-counter span');count = obj_array.length;}

  var obj = obj_array.eq(count - index - 1);
  var num = parseInt(obj.attr('class').replace('num',''));

  obj.css({backgroundPosition: left_coords[1] + 'px' + ' ' + top_coords[num] + 'px'});
  setTimeout(function(){obj.css({backgroundPosition: left_coords[2] + 'px' + ' ' + top_coords[num] + 'px'})}, speed);
  if (num == 9) {
    setTimeout(function(){obj.css({backgroundPosition: left_coords[0] + 'px' + ' ' + top_coords[0] + 'px'}).attr('class','num0')},speed * 2);
    animateNumber(index + 1);
  } else {
    setTimeout(function(){obj.css({backgroundPosition: left_coords[0] + 'px' + ' ' + top_coords[num + 1] + 'px'}).attr('class','num'+(num + 1))},speed * 2);
  }
}

function claim_off () {
  $('div.popupWrap').remove();
  $('a.claim').remove();
}

