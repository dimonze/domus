$(function(){

  //gmap
  var gmap = null;
  var first_init = true;
  var coords = {}; // {rel: {x: num, y: num}}
  var markersArray = [];
  var info_cache = {}; // {url: data}
  var $info = $('.mapPopup');
  
  function getPointFromLatLngToDivPixel(point, map){
    //Source: http://stackoverflow.com/questions/2674392/how-to-access-google-maps-api-v3-markers-div-and-its-pixel-position
    var scale = Math.pow(2, map.getZoom());
    var nw = new google.maps.LatLng(
        map.getBounds().getNorthEast().lat(),
        map.getBounds().getSouthWest().lng()
    );
    var worldCoordinateNW = map.getProjection().fromLatLngToPoint(nw);
    var worldCoordinate = map.getProjection().fromLatLngToPoint( point );
    var pixelOffset = new google.maps.Point(
        Math.floor((worldCoordinate.x - worldCoordinateNW.x) * scale),
        Math.floor((worldCoordinate.y - worldCoordinateNW.y) * scale)
    );
    return pixelOffset;
  }
  
  function update_gmap() {
    if (null == gmap) return false;
    var centered = false;
    
    coords = {};
    var bounds = new google.maps.LatLngBounds();
    
    //clearOverlays
    $.each(markersArray, function(i, val){
      markersArray[i].setMap(null);
    });
    markersArray.length = 0;

    var $items = $('.compareTable td[rel][latitude][longitude]').not('.invisible');
    try {
      $items.each(function() {
        this.marker = null;
        var point = new google.maps.LatLng($(this).attr('latitude'), $(this).attr('longitude'));
        bounds.extend(point);
      });
      
      if(!bounds.isEmpty()) {
        gmap.fitBounds(bounds);
        gmap.setCenter( bounds.getCenter() );
      }
      
      $items.each(set_marker);
    }catch(e){
      alert( 'Map rendering error' );
    }
    
    return true;
  }

  function set_marker($item) {
    $item = $(this);
    var point = new google.maps.LatLng($item.attr('latitude'), $item.attr('longitude'));
    var point_px = getPointFromLatLngToDivPixel(point, gmap);
    var radius = 15;
    var marker = null;
    var $parent = null;
    
    $.each(coords, function(){
      var px = this;
      if ((point_px.x - px.x) * (point_px.x - px.x) + (point_px.y - px.y) * (point_px.y - px.y) <= radius * radius) {
        $parent = px;
        return false;
      }
      return true;
    });
    point_px.div = $item;
    
    if($parent == null) {
      marker = new google.maps.Marker($.extend(gmap_marker_options, {
        position: point,
        map: gmap
      }));
      
      marker.rel = $item.attr('rel');
      marker.items = [$item[0]];
      marker.multiple = false;
      
      google.maps.event.addListener(marker, "click", gmap_show_info);
      google.maps.event.addListener(marker, "mouseover", function() {
        if (!$info.is(':visible')) set_marker_active(marker);
      });
      google.maps.event.addListener(marker, "mouseout", function() {
        if (!$info.is(':visible')) set_marker_inactive(marker);
      });
    } else {
      marker = $parent.marker;
      if(!marker.multiple){
        marker.setIcon(domus_icon_multiple);
        marker.setShadow(domus_icon_multiple.shadow);
        marker.multiple = true;
      }
      marker.items.push($item.get(0));
      marker.rel = marker.rel + ',' + $item.attr('rel');
    }

    point_px.marker = marker;
    coords[$item.attr('rel')] = point_px;
    
    $item.get(0).marker = marker;
    markersArray.push(marker);

    $item
      .unbind('mouseover mouseout')
      .bind('mouseover', function() {
        if (!$info.is(':visible')) set_marker_active(this.marker, this);
      })
      .bind('mouseout', function() {
        if (!$info.is(':visible')) set_marker_inactive(this.marker, this);
      });
  }

  function set_marker_active(marker, item) {
    if (marker.multiple) {
      marker.setIcon(window.domus_icon_multiple.hover);
    }
    else {
      marker.setIcon(window.domus_icon.hover);
    }

    if (item) {
      $(item).addClass('searchResultItemHover')
    }
    else {
      $(marker.items).addClass('searchResultItemHover')
    }
  }

  function set_marker_inactive(marker, item) {
    if (marker.multiple) {
      marker.setIcon(window.domus_icon_multiple);
    }
    else {
      marker.setIcon(window.domus_icon);
    }

    if (item) {
      $(item).removeClass('searchResultItemHover')
    }
    else {
      $(marker.items).removeClass('searchResultItemHover')
    }
  }

  function gmap_show_info() {
    var marker = this;

    function show_info_window(html) {
      if ($info.filter(':visible').attr('rel') == marker.rel) {
        return $('body').trigger('mousedown.map-info-popup');
      }
      else if ($info.is(':visible')) {
        $('body').trigger('mousedown.map-info-popup');
      }

      $info
          .attr('rel', marker.rel)
          .css({'margin-top': '-4000px', 'margin-left': '-4000px'})
          .html(html)
          .show();
      
      var pos = getPointFromLatLngToDivPixel(marker.getPosition(), gmap);
      $info.css({
          'top': 0,
          'left': $('#gmap').css('margin-left'),
          'margin-left': (pos.x - 260) + 'px',
          'margin-top': ($('#gmap').position().top + pos.y + 2) + 'px'
        });
      set_marker_active(marker);

      $('body').bind('mousedown.map-info-popup', function(e) {
        if (!$(e.target).closest('.mapPopup').size()) {
          $info.hide();
          try {
            set_marker_inactive(marker);
          } catch (exception) {}
          $('body').unbind('mousedown.map-info-popup');
        }
      });
      $info.find('a.close').bind('click', function() {
        $('body').trigger('mousedown.map-info-popup');
        return false;
      });
      return true;
    }

    var url = build_query('/lot/info-window', {id: marker.rel, currency: 'RUR'});
    if (!info_cache[url] || true) { // disabling cache because of ajax lot actions
      gmap.setOptions({draggable: false});
      $.get(url, function(html) {
        var $tmp = $('<div id ="tmp">' + html + '</div>');

        $tmp.find('a[rel]').each(function() {
          var link = $('.searchResultItem[rel=' + $(this).attr('rel') + '] h2 a').attr('href');
          $(this).attr('href', link);
        });

        info_cache[url] = $tmp.html();
        show_info_window($tmp.html());
        gmap.setOptions({draggable: true});
      });
    } else {
      show_info_window(info_cache[url]);
    }
    return true;
  }

  google.maps.event.addDomListener(window, 'load', function(){
    $('#gmap').each(function() {
      var $this = $(this);
      $this.show();
      gmap = new google.maps.Map(this, $.extend(gmap_options, {
        center: window.gmap_center ? window.gmap_center : new google.maps.LatLng(63.103883, 93.414063),
        zoom: String($('#citySelect').text()).indexOf('г.') != -1 ? 10 : 8
      }));

      google.maps.event.addListener(gmap, "click", function(overlay) {
        if (!overlay) $('body').trigger('mousedown.map-info-popup');
      });

      google.maps.event.addListener(gmap, "dragstart", function() {$('body').trigger('mousedown.map-info-popup');});
      google.maps.event.addListener(gmap, 'bounds_changed', function() { 
        if(first_init){
          first_init = false;
          update_gmap();
          $this.hide();
        }
      });
    });
  });

  // slider
  var n = $(".compareTable tbody tr.tr_01 th").length;
  var m = (n - 4);
  var a = 0;
  var b = 4;
  if (m > 0) {
    $('#right-arrows-compare, #right-arrows-compare-s').css({
      background: 'url(/images/right-compare-act.gif)',
      cursor: 'pointer'
    });
  }

  function toggle_cells() {
    $('.compareTable tbody').find('tr.tr_01 th, td').hide().addClass('invisible');
    $('.compareTable tbody tr.tr_01 td').removeClass('invisible').show();

    $('.compareTable tbody tr').not('.tr_01').each(function(){
      $(this).children('td').slice(a, b).removeClass('invisible').show();
    });
    $('.compareTable tbody tr.tr_01').each(function(){
      $(this).children('th').slice(a, b).removeClass('invisible').show();
    });

    if(gmap != null) update_gmap();
  }

  $('#right-arrows-compare, #right-arrows-compare-s').click(function(){
    if (b < n) {
      a++;
      b++;
      toggle_cells();

      $('#left-arrows-compare, #left-arrows-compare-s').css({
        background: 'url(/images/left-compare-act.gif)',
        cursor: 'pointer'
      });
      if (b == n) {
        $('#right-arrows-compare, #right-arrows-compare-s').css({
          background: 'url(/images/right-compare.gif)',
          cursor: 'default'
        });
      }
    }
    return false;
  });

  $('#left-arrows-compare, #left-arrows-compare-s').click(function(){
    if (a > 0) {
      a--;
      b--;
      toggle_cells()
      
      $('#right-arrows-compare, #right-arrows-compare-s').css({
        background: 'url(/images/right-compare-act.gif)',
        cursor: 'pointer'
      });

      if (a == 0) {
        $('#left-arrows-compare, #left-arrows-compare-s').css({
          background: 'url(/images/left-compare.gif)',
          cursor: 'default'
        });
      }
    }
    return false;
  });

  toggle_cells();
});