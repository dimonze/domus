$(function() {
  var disable_popups = null;
  var $info = $('.mapPopup');
  var coords = {}; // {rel: {x: num, y: num}}

  function execute_action() {
    url = $(this).attr('href');
    if ($(this).attr('class') == 'delit'){
      confirm_delete = confirm('Вы уверены?');      
      if (confirm_delete == true){
        $.getJSON(url, function(data){
          $('#global-loading').hide();
          if (data.save == true){
            $('form.searchForm').find('input[type=submit]').trigger('click');
          }
        });
      }
    }
    else {
      $.getJSON(url, function(data){
        $('#global-loading').hide();
        if (data.save == true){
          $('form.searchForm').find('input[type=submit]').trigger('click');
        }
      });
    }
    return false;
  }
  $('.resultForm').each(function() {
    $(this).bind('submit', function() {
      if (!$('.resultTable tbody tr :checkbox:checked', this).length) {
        alert('Выберите одно или несколько объявлений');
        return false;
      }
      if ($('select[name=do]', this).val() == '') {
        alert('Выберите действие');
        return false;
      }
      if ($('select[name=do]', this).val() == 'delete') {
        return confirm('Вы действительно хотите удалить выбранные объявления?');
      }
    
      post_data = $('.resultForm').serialize();
      $.post($('.resultForm').attr('action'), post_data, function(data){
        if (data.save == true){
          $('form.searchForm').find('input[type=submit]').trigger('click');
        }
      }, 'json');
      return false;
    });
  });

  $('.resultTable tbody tr td[class!="notify"]').bind('click', function(e) {
    if (!$(e.target).is('a,:checkbox')) {
      var $chkbox = $(this).closest('tr').find(':checkbox');
      if ($chkbox.is(':checked')) {
        $chkbox.removeAttr('checked');
      }
      else {
        $chkbox.attr('checked', true);
      }
    }
  });

  $('.resultTable thead :checkbox').bind('click', function() {
    if ($(this).is(':checked')) {
      $('.resultTable tbody tr :checkbox').attr('checked', true);
    }
    else {
      $('.resultTable tbody tr :checkbox').removeAttr('checked');
    }
  });

  $('.datepicker').datepicker({
    dateFormat: 'dd.mm.yy',
    showOn: 'button',
    buttonImage: '/images/blank.gif',
    buttonImageOnly: true
  });
  $('div.pagesNaviBox').each(function(){
    $(this).find('ul a').click(function(){
      page = '<input type="hidden" name="filter[page]" value="' + $(this).attr('rel') + '">';
      $('form.searchForm').append(page);
      $('form.searchForm').find('div.buttons input[type=submit]').trigger('click');
      return false;
    });
  });

  $('.resultForm').each(function() {
    $('a[name=stop-active-pm]').bind('click', send_message_restrict);
    $('a.none').bind('click', execute_action);
    $('a.delit').bind('click', execute_action);
    $(this).find('select[name=do]').bind('change', function() {
      if ($(this).val() == 'restrict_send_message') {
        $('.resultForm').find('input[type=submit]').bind('click', function() {
          var users = '';
          var lots = '';
          $('.resultTable').find('input[type=checkbox][name=id\[\]][checked=true]').each(function() {
            user = $(this).attr('rel').split(':');
            if (users != ''){
              users = users + ',' + user[1];
            } else {
              users = user[1];
            }
            if (lots != ''){
              lots = lots + ',' + $(this).val();
            } else {
              lots = $(this).val();
            }            
          });
          if (lots == ''){
            alert('Вы не выбрали ни одного объявления.');
            return false;
          }
          
          $.get('/user/pm/addmoderator?to=' + users + '&lot_id=' + lots, function(data){
            $('#global-loading').hide();
            var $popup = show_popup(data, 'reg');
            $('input[name=pm\[lot_id\]]').attr('value', lots);
            $popup.find('select[name=email_themes]').bind('change', function(){              
              if ($(this).find(':selected').text().length > 0){
                $theme_id = $(this).find(':selected').val();
                $('input[name=pm\[subject\]]').attr('value', $(this).find(':selected').text());                
                $('textarea[name=pm\[message\]]').attr('value', $('input[type=hidden][name=themes_' + $theme_id + ']').val());
              }
              else {
                $('input[name=pm\[subject\]]').attr('value', '');
//                $('input[name=pm\[lot_id\]]').attr('value', '');
                $('textarea[name=pm\[message\]]').attr('value', '');
              }
            });
          });
          return false;
        });
      } else {
        $('.resultForm').find('input[type=submit]').unbind('click');
      }
    });
  });

  $('#gmap').each(function() {    
    function update_gmap() {
      if (!gmap) return false;

      coords = {};
      var bounds = new GLatLngBounds();

      gmap.clearOverlays();

      $('.resultForm tr[rel][latitude][longitude]').each(function() {
        if (!(parseFloat($(this).attr('latitude')) > 0 && parseFloat($(this).attr('longitude')) > 0)) {
          $(this).removeAttr('latitude').removeAttr('longitude');
          return true;
        }
        this.marker = null;
        var point = new GLatLng($(this).attr('latitude'), $(this).attr('longitude'));
        bounds.extend(point);
        coords[$(this).attr('rel')] = gmap.fromLatLngToDivPixel(point);
      });

      $('.resultForm tr[rel][latitude][longitude]').each(set_marker);
    }

    function set_marker($item) {
      $item = $(this);
      var radius = 15;
      var marker = null;
      var point = new GLatLng($item.attr('latitude'), $item.attr('longitude'));
      var point_px = gmap.fromLatLngToDivPixel(point);
      var $parent = null;

      $item.siblings().each(function() {
        if (this.marker) {
          var $this = $(this);
          var px = coords[$this.attr('rel')]

          if ( (point_px.x-px.x) * (point_px.x-px.x) + (point_px.y-px.y) * (point_px.y-px.y) <= radius*radius) {
            this.marker.rel += ','+$item.attr('rel');
            marker = this.marker;
            marker.items[marker.items.length] = $item[0];
            $parent = $(this);
            return false;
          }
        }
      });

      if (!marker) {
        marker = new GMarker(point, gmap_marker_options);
        marker.rel = $item.attr('rel');
        marker.items = [$item[0]];
        marker.multiple = false;

        gmap.addOverlay(marker);

        GEvent.addListener(marker, "click", gmap_show_info);
        GEvent.addListener(marker, "mouseover", function() {if (!$info.is(':visible')) set_marker_active(marker);});
        GEvent.addListener(marker, "mouseout", function() {if (!$info.is(':visible')) set_marker_inactive(marker);});
      }
      else if ($parent && $parent[0].marker.multiple == false) {
        var mmarker = new GMarker(marker.getPoint(), gmap_marker_multiple_options);
        mmarker.rel = marker.rel;
        mmarker.items = marker.items;
        mmarker.items[mmarker.items.length] = $item[0];
        mmarker.multiple = true;

        gmap.addOverlay(mmarker);
        gmap.removeOverlay(marker);
        marker = mmarker;

        $parent[0].marker = marker;
        GEvent.addListener(marker, "click", gmap_show_info);
        GEvent.addListener(marker, "mouseover", function() {if (!$info.is(':visible')) set_marker_active(marker);});
        GEvent.addListener(marker, "mouseout", function() {if (!$info.is(':visible')) set_marker_inactive(marker);});
      }

      $item[0].marker = marker;

      $item
        .unbind('mouseover mouseout')
        .bind('mouseover', function() {if (!$info.is(':visible')) set_marker_active(this.marker, this);})
        .bind('mouseout', function() {if (!$info.is(':visible')) set_marker_inactive(this.marker, this);});
    }

    function gmap_show_info() {
      if (disable_popups) {
        return false
      }

      var marker = this;
      function show_info_window (html) {
        if ($info.filter(':visible').attr('rel') == marker.rel) {
          return $('body').trigger('mousedown.map-info-popup');
        }
        else if ($info.is(':visible')) {
          $('body').trigger('mousedown.map-info-popup');
        }

        $info
          .attr('rel', marker.rel).css({'margin-top': '-4000px', 'margin-left': '-4000px'})
          .html(html).show();
        var pos = gmap.fromLatLngToContainerPixel(marker.getLatLng());
        $info.css({
          'margin-left': -$('#gmap').width() + pos.x + 300 + 'px',
          'margin-top':  pos.y - 60 -400 + 'px'
        });

        var info_height = $info.height() + parseInt($info.css('padding-top')) + parseInt($info.css('padding-bottom'));
        if (info_height + pos.y - 35 - 400 > $('.mapBox').height()) {
          $info.css('margin-top', $('.mapBox').height() - info_height + 'px');
        }

        if ($info.offset().left + $info.width() /2 < $(document).width() / 2) {
          $info.css('margin-left', parseInt($info.css('margin-left')) + $info.width() + 60 + 'px');
        }

        set_marker_active(marker);

        $('body').bind('mousedown.map-info-popup', function(e) {
          if (!$(e.target).closest('.mapPopup').length) {
            $info.hide();
            try {
              set_marker_inactive(marker);
            }
            catch (exception) { }
            $('body').unbind('mousedown.map-info-popup');
          }
        });
        $info.find('a.close').bind('click', function() {
          $('body').trigger('mousedown.map-info-popup');
          return false;
        });
        $info.find('a[name=stop-active-pm]').bind('click', send_message_restrict);
        return true;
      };

      var url = build_query('/lot/info-window-moderate', {id: marker.rel, currency: 'RUR'});
      gmap.disableDragging();
      $.get(url, function(html) {
        var $tmp = $('<div id ="tmp">'+html+'</div>');
        $info.html($tmp.html());
        $popup_info_window = show_info_window($tmp.html());
        gmap.enableDragging();
      });
    }

    function set_marker_active(marker, item) {
      if (marker.multiple) {
        marker.setImage(window.domus_icon_multiple_hover.image);
      }
      else {
        marker.setImage(window.domus_icon_hover.image);
      }
    }

    function set_marker_inactive(marker, item) {
      if (marker.multiple) {
        marker.setImage(window.domus_icon_multiple.image);
      }
      else {
        marker.setImage(window.domus_icon.image);
      }
    }
    
    if (typeof GBrowserIsCompatible == 'function' && GBrowserIsCompatible()) {
      $(this).show();
      var gmap = new GMap2(this);
      var info_cache     = {}; // {url: data}
      gmap.setUIToDefault();
      gmap.disableScrollWheelZoom();

      GEvent.addListener(gmap, "moveend", update_gmap);
      if (!$('input[name=filter\[region_center_coords\]]').val()) {
        region = $('div#region_coords').attr('rel').split(/\s*,\s*/);
        region[0] = parseFloat(region[0]);
        region[1] = parseFloat(region[1]);
        region[2] = parseFloat(region[2]);
      }
      else{
        region = $('input[name=filter\[region_center_coords\]]').val().split(/\s*,\s*/);
        region[0] = parseFloat(region[0]);
        region[1] = parseFloat(region[1]);
        region[2] = parseFloat(region[2]);
      }
      gmap.setCenter(new GLatLng(region[0], region[1]), region[2]);

      $(this).hide();
      if ($('input[type=checkbox][name=filter\[map_search\]]').attr('checked')){
        $('.mapBox .collapse-full-text').text('Свернуть карту');// ?
        $(this).show();
      }

      $('.searchForm').each(function (){
        $(this).find('select[name=filter\[region_id\]]').bind('change', function(){
          region_id = $(this).val();
          if (region_id == ''){
            region = $('div#region_coords').attr('rel').split(/\s*,\s*/);
            gmap.setCenter(new GLatLng(region[0], region[1]), region[2]);
            $('input[name=filter\[region_center_coords\]]').attr(
              'value',
              region[0] + ', '
              + region[1] + ', '
              + region[2] + ', '
              + region[3]
            );
          }else {
            $.getScript('/form/gmap.js?region_id=' + region_id, function() {
              gmap.setCenter(new GLatLng(window.gmap_center[0], window.gmap_center[1]), window.gmap_zoom);
              $('input[name=filter\[region_center_coords\]]').attr(
                'value',
                window.gmap_center[0] + ', '
                + window.gmap_center[1] + ', '
                + window.gmap_zoom + ', '
                + region_id
              );
            });
          }
        });
        $('input[type=checkbox][name=filter\[map_search\]]').click(function(){
          if ($('#gmap').is(':visible')){
            $('#gmap').slideUp();
            $('.mapBox .collapse').text($('.mapBox .collapse').is('.collapse-full-text') ? 'Развернуть карту' : 'Равернуть');
          }
          else {
            $('#gmap').slideDown();
            $('.mapBox .collapse').text($('.mapBox .collapse').is('.collapse-full-text') ? 'Свернуть карту' : 'Свернуть');
          }
        });
        $(this).find('select[name=filter\[type\]]').bind('change', function(){
          type = $('select[name=filter\[type\]]').attr('value');
          if (type == 'apartament-sale' || type == 'apartament-rent'){
            $('input[name=filter\[num_rooms\]]').attr('disabled', false);
            $('input[name=filter\[area_country_from\]]').attr('disabled', true);
            $('input[name=filter\[area_country_to\]]').attr('disabled', true);
          }
          if (type == 'house-sale' || type == 'house-rent'){
            $('input[name=filter\[area_country_from\]]').attr('disabled', false);
            $('input[name=filter\[area_country_to\]]').attr('disabled', false);
            $('input[name=filter\[num_rooms\]]').attr('disabled', true);
          }
        });
      });
      
      $('form.searchForm').find('input[type=submit]').click(function() {
        disable_popups = true;
        $('input[type=hidden][name=filter\[coords\]]').attr('value', gmap.getBounds());
        latitude = gmap.getCenter().lat();
        longitude = gmap.getCenter().lng();
        zoom = gmap.getZoom();
        $('input[name=filter\[region_center_coords\]]').attr(
          'value',
          latitude + ', '
          + longitude + ', '
          + zoom + ', '
          + $('select[name=filter\[region_id\]]').val()
        );
      });
      //Устанавливаем маркеры на карту
      update_gmap();
    }
  });

  // Work with user filters
  $('div[name=moder-filters]').each(function(){

    // Add new filter
    $(this).find('a[name=moder-filters\[add\]]').click(function() {
      $('div.moder-filters-new').show();
      $('a[name=moder-filters\[add\]]').hide();
      $('a[name=moder-filters\[delete\]]').hide();
      $('a[name=moder-filters\[rename\]]').hide();
      return false;
    });

    $(this).find('input[type=button][name=moder-filters\[ok\]]').click(function() {
      var name = $('input[name=moder-filters\[name\]]').val();
      if (name == 'Введите название фильтра') {
        alert('Вы не ввели название для фильтра.');
      } else {
        $filters = $('[name^=filter]','form.searchForm').not('[type=hidden]');
        $data = $filters.serialize();
        post_data = $data + '&name=' + name;
        $.post($('a[name=moder-filters\[add\]]').attr('href'), post_data, function(data){
          if (data.id) {
            $('select[name=moder-filters]').append('<option value="' + data.id + '">' + data.name + '</option>');
            $('select[name=moder-filters]').find('option[value=' + data.id +']').attr('selected', true);
            $('select[name=moder-filters]').trigger('change');

            $('div.moder-filters-new').hide();
            $('a[name=moder-filters\[add\]]').show();
          }
          else if (data.error){
            if (data.error == 'name_exist') {
              alert('Фильтр с таким именем уже существует.');
            }
          }
        }, 'json');
      }
    });
    //Delete user filter
    $(this).find('a[name=moder-filters\[delete\]]').click(function() {
      $filter_id = $('select[name=moder-filters]').attr('value');
      if ($filter_id != ''){
        if (confirm('Вы уверены что хотите удалить фильтр "' + $('select[name=moder-filters] option:selected').text() + '"?')) {
          $.post($(this).attr('href'), 'id=' + $filter_id, function(data){
            if (data.success){
              $('form.searchForm')[0].reset();
              $('select[name=moder-filters]').find('option[value=0]').attr('selected', true);
              $('select[name=moder-filters]').trigger('change');
              $('select[name=moder-filters]').find('option[value=' + $filter_id + ']').remove();
            }
            else {
              alert('Удаление фильтра не произведено.');
            }
          }, 'json');
        }
      }
      else {
        alert('Вы не выбрали фильтр для удаления.');
      }
      return false;
    });

    //Fill data on change user filter
    $(this).find('select[name=moder-filters]').change(function(){
      $('.global-loading').show();
      if ($(this).attr('value')) {
        $.post('/filter/get', 'id=' + $(this).attr('value'), function(data){
          if (data.params){
            $('form.searchForm')[0].reset();
            $('form.searchForm :input[name^=filter][type=checkbox]').removeAttr('checked');
            for(k in data.params)
            {
              if (data.params[k] == 'on'){
                $('form.searchForm :input[name=filter\['+k+'\]][type=checkbox]').attr('checked', 'checked');
              }else {
                $('form.searchForm :input[name=filter\['+k+'\]]').attr('value', data.params[k]);
              }
            }
          }else {
          }
        }, 'json');
        $('a[name=moder-filters\[delete\]]').show();
        $('a[name=moder-filters\[rename\]]').show();
      } else {
        $('form.searchForm')[0].reset();
        $('a[name=moder-filters\[delete\]]').hide();
        $('a[name=moder-filters\[rename\]]').hide();
      }
    });

    // Rename filter
    $(this).find('a[name=moder-filters\[rename\]]').click(function() {
      if ($('select[name=moder-filters]').val() != ''){
        $('input[name=moder-filters\[rename\]]').val($('select[name=moder-filters] option:selected').text());
        $('div.moder-filters-rename').show();
        $('a[name=moder-filters\[add\]]').hide();
        $('a[name=moder-filters\[delete\]]').hide();
        $('a[name=moder-filters\[rename\]]').hide();
      }
      return false;
    });

    //Send ajax query with new name to server
    $(this).find('input[type=button][name=moder-filters\[rename-ok\]]').click(function() {
      var name = $('input[name=moder-filters\[rename\]]').val();
      if (name != $('select[name=moder-filters] option:selected').text()) {
        post_data = 'id=' + $('select[name=moder-filters] option:selected').val() + '&name=' + name;
        $.post($('a[name=moder-filters\[rename\]]').attr('href'), post_data, function(data){
          if (data.success) {
            $('select[name=moder-filters] option:selected').text(name);            

            $('div.moder-filters-rename').hide();
            $('a[name=moder-filters\[add\]]').show();
            $('a[name=moder-filters\[delete\]]').show();
            $('a[name=moder-filters\[rename\]]').show();
          }
          else {
            alert('Не удалось переименовать фильтр.');
          }
        }, 'json');
      }
      else {
        $('div.moder-filters-rename').hide();
        $('a[name=moder-filters\[add\]]').show();
        $('a[name=moder-filters\[delete\]]').show();
        $('a[name=moder-filters\[rename\]]').show();
      }
    });
  });  
})