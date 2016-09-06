$(function() {
  var $form = $('.searchObjectForm');
  var $info = $('.mapPopup');
  var $pager_map = $('#pager-map');

  var search_timeout = null;
  var first_search = true;
  var current_hash = null;
  var prev_hash = null;
  var disable_popups = null;

  var gmap_auto_move = null;
  var gmap_center = window.gmap_center ? new google.maps.LatLng(window.gmap_center[0], window.gmap_center[1]) : null;
  var gmap_zoom = window.gmap_zoom || null;
  var coords = {}; // {rel: {x: num, y: num}}
  var info_cache = {}; // {url: data}

  var gmap = null;
  var first_init = true;
  var trigger_movend = false;
  var gmap_updating = false;
  var force_page = false;
  var is_init = false;
  var is_closing = false;
  var markersArray = [];

  var app_settings = JSON.parse($('#settings').attr('data-settings'));

  $(window).bind('beforeunload', function() {is_closing = true});

  if (String(window.location.href).match(/search\/.+/) && !location.hash) {
    if (String(window.location.href).indexOf('/search/index') == -1) {
      location.href += String(window.location.href).replace(/^.+search\//, '#');
      is_init = true;
    }
  }

  if ($('#q').val() && String(window.location.hash).length <= 2 && getcookie('landing_hash') == null) {
    window.location.href = String(window.location.href).replace(/#?$/, '') + '#q/' + $('#q').val();
  }


  set_equal_height_block_view();

  $form.bind('submit', function(e) {
    if (is_closing) return false;

    disable_popups = true;
    window.clearTimeout(search_timeout);
    search_timeout = window.setTimeout(function() {
      if (first_search) restore_regions_types();
      store_regions_types();

      if (first_search) {
        first_search = false;
        if (!current_hash) {
          bind_controls();
          if(!first_init) update_gmap();
          disable_popups = false;
          return false;
        }
      }

      var should_not_be_redirected = false;
      var data = prepare_data($form);
      var hash = prepare_hash(data);
      var r = reset_page(data, hash);
      data = r.data;
      current_hash = r.hash;
      var url_parts = window.location.pathname.replace(/^\//, '').split('/');

      //Do not escape from landing page if changes only sorting, currency or page
      if(!prev_hash) {
        prev_hash = getCookie('landing_hash');
      }
      if(hash && prev_hash) {
        should_not_be_redirected = true;
        var diff_fields = get_diff(hash, prev_hash.replace(/^#/, ''), 'both');
        var not_bad_fields = new Array('c', 's', 'page', 'landing', 'view', 'type');
        for(i in diff_fields) {
          if(!in_array(diff_fields[i], not_bad_fields)) {
            should_not_be_redirected = false
          }
        }
      }

      prev_hash = hash;
      //Redirect from landing to root when hash appears.
      //I dunno why we can't do it now, but we can't. Strange behaviour.
      //See also "else case" for ajax-search approx. in 30 lines below (Ctrl+F "we_need_to_leave_landing_page")
      var we_need_to_leave_landing_page = false;
      if(hash && url_parts.length == 2 && !should_not_be_redirected) {
        we_need_to_leave_landing_page = '/' + url_parts.shift() + '#' + hash.replace(/\/landing\/\d+/, '');
      }

      document.cookie = 'landing_hash=' + current_hash.replace(/\/landing\/\d+/, '') + '; path=/;';
      window.location.href = String(window.location.href).replace(/#.*$/, '') + '#' + current_hash;

      gmap_move_block(0);
      window.loading_text = 'Идет поиск...';
      data += '&no_redir=' + (should_not_be_redirected)*1;
      data += '&curl=' + urldecode(window.location.pathname);

      if (is_init) {
        is_init = false;
      }
      else {
        $.post(
            $form.attr('action'),
            data,
            function(data) {
              _global_inline_blocks_counter = 0;
              window.loading_text = '';

              var redir = $(data).filter('.redirect-from-ajax').attr('data-url') || undefined;
              var pathname = location.pathname.split('/');
              var tail   = pathname.pop();
              var o_type = pathname.pop();

              //Redirect when we have landing page for current params
              //BTW, bad practice. We should send another query.
              if(redir && (redir != tail || (redir == tail && location.hash.length > 1))) {
                if(o_type == '') {
                  o_type = tail;
                }
                to = '/' + o_type + '/' + redir;
                if(to != location.pathname) {
                  location.href = '/' + o_type + '/' + redir;
                  return false;
                }
              }
              else if(we_need_to_leave_landing_page) {
                document.cookie = 'landing_hash=' + current_hash.replace(/\/landing\/\d+/, '') + '; path=/;';
                location.href = we_need_to_leave_landing_page;
                return false;
              }
              
              //find description and put to footer
              var dd_text = $(data).filter('div.district_description').html();
              if (null != dd_text && dd_text.length > 0) {
                $('div.district-description').html(dd_text);
                $('div.district-description').show();
              }
              else {
                $('div.district-description').hide();
              }
              seo_links = $(data).filter('div.seo_links').html();
              if (null != seo_links && seo_links.length > 0) {
                $('div.seo-links').html(seo_links);
                $('div.seo-links').show();
              }
              else {
                $('div.seo-links').hide();
              }
              $('#result').html(data);
              // #17581
              if($('#result li.pageNumber:first').size() || $('#result #res_title').val() != 'не найдено  объектов') {
                var cpage = $form.find('input[name="page"]').val();
                
                if($('#result li.pageNumber:first').size()) { //Paginator is visible
                  var page_num_holder = $('#result li.pageNumber:first').text().trim();
                  var max_page = parseInt(page_num_holder.substr(page_num_holder.indexOf('/') + 1));
                } else {
                  if(!$('#result .searchResultItem[rel]').size()) max_page = 1;
                }
                
                if(cpage > max_page) {
                  $form.find('input[name="page"]').val(1)
                  $form.submit();
                  return false;
                }
              }
                            
              $('#result').find('div.district_description').remove();
              $('#result').find('div.seo_links').remove();
              counterHit();
              bind_controls();              
              if ($('.redirect-from-ajax').length == 0) update_title();
              if(!first_init) update_gmap();
              check_pager_map();
              show_zones();
              disable_popups = false;
            }
        );
      }
    }, 400);
    return false;
  })

  function reset_page(data, hash) {
    var re = /\/(page|currency)\/[^\/]+/g;
    if (!force_page && current_hash && hash.replace(re, '') != current_hash.replace(re, '')) {
      data = data.replace(/page=\d+/, 'page=1');
      hash = hash.replace(/page\/\d+/, 'page/1');
    }
    return {data: data, hash: hash};
  }

  // watch for anchor changes
  function watch_hash() {
    var hash = String(window.location.hash).substr(1).split('/');
    var chash = String(current_hash).split('/');

    if (String(window.location.hash).substr(1) == current_hash) {
      return false;
    }

    var need_update = false;

    for (var i = 0; i < hash.length; i += 2) {
      var encoded = encodeURIComponent(String(hash[i + 1]).split('+').join(' ')).split('%20').join('+').replace('5+-', '5%2B-');
      encoded = encoded.split('(').join('%28').split(')').join('%29');
      if (hash[i] != chash[i] || !(hash[i + 1] == chash[i + 1] || encoded == chash[i + 1])) {
        need_update = true;
      }
      if (hash[i] == 'page') {
        if (chash.length < 2) {
          force_page = true;
        }
        for (var j = 0; j < chash.length; j += 2) {
          if (chash[j] == 'page') {
            force_page = hash[i + 1] != chash[j + 1];
            break
          }
        }
        break;
      }
    }

    if (need_update) {
      current_hash = hash.join('/');
      update_form_from_hash();
      return true;
    }

    return false;
  }

  function update_form_from_hash() {
    var hash = current_hash;
    if(getcookie('landing_hash') != null && window.location.hash == '') {
      hash = getcookie('landing_hash');
    }
    var data = hash.split('/');
    var $inputs = $form.find(':input').not(':submit');

    // reset form
    $inputs.filter('[name=currency]').val('RUR');
    $inputs.filter('select,[type=text]').val('');
    $inputs.filter(':radio[name=location-type][value=form]').attr('checked', true).trigger('click');

    $inputs.filter(':checkbox').removeAttr('checked');
    $('#q_text, #q').val('');
    $('ul.metroList').remove();
    $('.text-search').show();

    var lat = {};
    var lng = {};
    var zoom = null;

    for (var i = 0; i < data.length; i += 2) {
      var key = translate_field(data[i].replace(/^#+/, ''), true);
      var val = String(data[i + 1]).split('+').join(' ').replace(/\?*utm_.+=[^&]+/, '');
      val = translit_field(key, val, true).replace('5 -?', '5+-?');

      if (key == 'map-maximized' && current_hash.indexOf('location-type/map') != -1) { //-1 не считается false
        $('.text-search').hide();
        continue;
      }

      if (key == 'q' || key == 'q_text') {
        $('.text-search').show();
        $('#q_text, #q, #frontstreet').val(decodeURIComponent(val));
        continue;
      }

      if (key == 'latitude[from]')  lat.from = parseFloat(val);
      if (key == 'latitude[to]')    lat.to = parseFloat(val);
      if (key == 'longitude[from]') lng.from = parseFloat(val);
      if (key == 'longitude[to]')   lng.to = parseFloat(val);
      if (key == 'zoom')            zoom = parseInt(val);
      //Корректировка валют
      if(key == 'currency') {
        $('ul.currencyList').find('a').removeClass('active');
        $('ul.currencyList').find('a[rel="' + val + '"]').addClass('active');
      }

      if (val) {
        var inputs = [];
        $inputs.each(function() {
          if (this.name == key) {
            inputs[inputs.length] = this;
            return $(this).is(':checkbox, :radio');
          }
          return true;
        });

        if (inputs.length) {
          var $input = $(inputs);
          if ($input.is(':checkbox') || $input.is(':radio')) {
            $input.each(function() {
              if (this.value == val || this.value == decodeURIComponent(val)) {
                $(this).attr('checked', true);
                (this.name == 'location-type') && $(this).trigger('click');
                return false;
              }
              return true;
            });
          }
          else {
            if($input.is(':select') && !$input.find('option[value="' + val + '"]').size()) {
              var nopt = $('<option />');
              nopt.addClass('custom');
              nopt.attr('value', val);
              nopt.text(val);
              if($input.attr('name').indexOf('price') == 0){
                nopt.attr('base_currency', $('[name=currency]').val());
              }
              $input.append(nopt);
            }
            $input.val(val).trigger('change');
          }
        }
        else if (key == 'regionnode[]' || key == 'field[45][orlike][]') {
          var $box = $(key == 'regionnode[]' ? '.metroBox' : '.commercialtypeBox');

          var $ul = $box.find('ul.metroList');

          if (!$ul.length) {
            if ($box.is('.metroBox')) {
              $ul = $box.find('a.select-regionnode:last').after('<ul class="metroList"/>').next();
            }
            else {
              $ul = $box.find('a.select-commercialtype').before('<ul class="metroList"/>').prev();
            }
          }

          var html = '<input type="hidden" value="' + val + '" name="' + key + '" />' + val;
          html += '<a href="#">&nbsp;</a>';
          $ul.append('<li>' + html + '</li>');

          $ul.find('a:last').bind('click', function() {
            var $list = $(this).closest('ul');
            $(this).closest('li').remove();
            //Обновление списка улиц
            if($('#frontstreet').size()) {
              street_auto_complete.apply( $('#frontstreet') );
            }
            var stName = $(this).closest('li').find('input').val();
            $('ul.regionnode-list li').each(function() {
              var input = $(this).find('input');
              if (input.val() == stName) input.attr('checked', '');
            });
            $form.trigger('submit');
            if ($list.find('li').length == 0) {
              $list.remove();
            }
            return false;
          });
        }
      }
    }

    if (lat.from && lat.to && lng.from && lng.to && zoom != null) {
      lat = lat.from + (lat.to - lat.from) / 2;
      lng = lng.from + (lng.to - lng.from) / 2;
      gmap_center = new google.maps.LatLng(lat, lng);
      gmap_zoom = zoom;
    }

    $('.currencyList a[rel=' + $inputs.filter('[name=currency]').val() + ']').trigger('click');
    $('.currencyType a[rel=' + $inputs.filter('[name=currency_type]').val() + ']').trigger('click');

    if($('#frontstreet').size()) {
      street_auto_complete.apply( $('#frontstreet') );
    }

    $form.trigger('submit');
  }

  $form.find(':input').each(function() {
    var $this = $(this);
    if ($this.closest('.advancedSearch').length == 0) {
      var store_page = {currency: true, 'map-maximized': true};
      $this.not(':checkbox,:radio').bind('change', function() {
        if (store_page[$(this).attr('name')]) {
          $form.trigger('submit', [$('.pagesNaviBox a.active').attr('href')]);
        }
        else {
          $form.trigger('submit');
        }
      });
      $this.filter(':checkbox').bind('click', function() {
        $form.trigger('submit');
      });
    }
  });

  function prepare_data($form) {
    var data = parse_url(serialize_form($form), {}, true);
    delete(data['_base_']);
    delete(data.current_url);
    delete(data.restore_custom);
    delete(data.referrer);

    if (data['location-type'] == 'map') {
      delete(data['regionnode[]']);
    }
    else {
      delete(data['restrict_region']);
      delete(data['latitude[from]']);
      delete(data['latitude[to]']);
      delete(data['longitude[from]']);
      delete(data['longitude[to]']);
      delete(data.zoom);
    }

    var result = '';
    for (i in data) {
      if (typeof data[i] == 'object') {
        for (j in data[i]) {
          result += i + '=' + data[i][j] + '&';
        }
      }
      else {
        result += i + '=' + data[i] + '&';
      }
    }

    return result.replace(/&$/, '');
  }

  function prepare_hash(data) {
    var hash = [];
    var splitted = data.split('&');

    for (i in splitted) {
      if (!splitted[i]) continue;

      var key = translate_field(String(splitted[i]).split('=')[0]);
      var value = translit_field(key, String(splitted[i]).split('=')[1]);

      if (key == 'type') continue;

      hash[hash.length] = key + '/' + value;
    }

    return hash.join('/');
  }

  function check_selects() {
    $form.find('select[name$=\[from\]]').each(function () {
      var $from = $(this);
      var $to = $from.nextAll('select[name$=\[to\]]:first');
      var vals = {from: $from.val(), to: $to.val()};
      if ($to.length && vals.to != '' && unformat_number(vals.from) > unformat_number(vals.to)) {

        if ($from.find('option[value=' + vals.to + ']').length) {
          $from.find('option[value=' + vals.to + ']').attr('selected', true);
        }
        else {
          if (!$from.find('option.custom').length) {
            $from.append('<option class="custom"/>');
          }
          $from.find('option.custom').val(vals.to).text(vals.to).attr('selected', true);
        }

        if ($to.find('option[value=' + vals.from + ']').length) {
          $to.find('option[value=' + vals.from + ']').attr('selected', true);
        }
        else {
          if (!$to.find('option.custom').length) {
            $to.append('<option class="custom"/>');
          }
          $to.find('option.custom').val(vals.from).text(vals.from).attr('selected', true);
        }
      }
    });
  }

  $form.find('select[name$=\[from\]],select[name$=\[to\]]').bind('change', check_selects);  

  function update_title() {
    var title = '';
    if($('.meta-data').attr('data')) {
      var metas = JSON.parse($('.meta-data').attr('data'));
      title = metas.h1;
      $( "meta[name='title']").attr("content", metas.title);
    }    
    else  if ($('a.searchOn').is('.active')) {
      title = $('#result').find('input[name=res_title]').val();
      title = title && title.charAt(0).toUpperCase() + title.substr(1);
    }
    else {
      var loc = [String($('#citySelect').text()).replace(/\s+$/, '')];
      $form.find('.metroBox ul.metroList li').slice(0, 5).each(function() {
        loc[loc.length] = String($(this).text()).replace(/\s+$/, '');
      });
      loc = loc.join(', ')
      if ($form.find('ul.metroList li').length > 5) {
        loc += ' и другие';
      }      
      title = loc;
      var str2replace = ['округ.', 'г.', 'обл.'];
      var replacements = ['округ', '', 'область'];
      for (var i = 0; i < str2replace.length; i++) {
        title = title.replace(str2replace[i], replacements[i]);
      }
      title += ' - ' + $('#result').find('input[name=res_title]').val();
    }
    $('.pageHeader h1').text(title);
  }

  function bind_controls() {
    $('#result .currencyList a').click(function () {
      $form.find('a[rel=' + $(this).attr('rel') + ']').trigger('click');
      return false;
    });
    $('#result .currencyList a[rel=' + $form.find('input[name=currency]').val() + ']')
        .addClass('active');

    $('#result .currencyType a').click(function () {
      $form.find('a[rel=' + $(this).attr('rel') + ']').trigger('click');
      return false;
    });
    $('#result .currencyType a[rel=' + $form.find('input[name=currency_type]').val() + ']')
        .addClass('active');

    $('select[name=sort]').bind('change',
        function() {
          $form.find('input[name=sort]').val($(this).val());
          $form.trigger('submit');
        }).val($form.find('input[name=sort]').val());
    $('.view-switch .action_09').click(function() {
      if ($(this).hasClass('active'))
        return false;
      $form.find('input[name=view]').val('list');
      toggle_active_class(this);
      $form.trigger('submit');
      return false;
    });
    $('.view-switch .action_10').click(function() {
      if ($(this).hasClass('active'))
        return false;
      $form.find('input[name=view]').val('tile');
      toggle_active_class(this);
      $form.trigger('submit');
      return false;
    });
  }

  $('a.advancedOpen, a.advancedClose').bind('click', function() {
    var $this = $(this);
    $this
        .toggleClass('advancedOpen')
        .toggleClass('advancedClose')
        .next().toggle();

    if ($this.is('.advancedClose')) {
      $this.next().find('select').find('option:first').attr('selected', true);
      $this.next().find(':radio,:checkbox').removeAttr('checked');
      $this.next().find('input[type=text],textarea').val('');
      $this.closest('form').trigger('submit');
      $('#restore_advanced').val('0');
    }
    else {
      $('#restore_advanced').val('1');
    }

    return false;
  });

  function store_regions_types() {
    custom = [];
    function map_vals($el) {
      row = [];
      $el.find('option').each(function() {
        row[row.length] = [
          $(this).attr('base_currency') || $('#currency').val() || 'RUR',
          $(this).attr('class'),
          $(this).is('[selected]') ? 1 : 0,
          $(this).val()
        ].join('|');
      });
      if (row.length) return $el.attr('name') + ':' + row.join(',');
      else            return '';
    }

    $('a.set-custom').each(function() {
      custom[custom.length] = map_vals($(this).prevAll('select:eq(0)'));
      custom[custom.length] = map_vals($(this).prevAll('select:eq(1)'));
    });
    $('#restore_custom').val(custom.join("\n"));
  }

  function restore_regions_types() {
    if ($('#restore_custom').val()) {
      data = $('#restore_custom').val().split(/\n+/);
      for (row in data) {
        if (typeof data[row] !== 'string') continue;
        row = data[row].split(':');
        $select = $('select[name=' + row[0].replace(/(\[\])/, '\\$1') + ']');
        empty_name = '';
        $price_name = $select.attr('name');
        if ($select.length) {
          row = row[1].split(',');
          $select.empty();
          for (i in row) {
            if (typeof row[i] !== 'string') continue;
            opt = row[i].split('|');

            $option = $('<option/>');
            $option.attr('value', opt[3]);
            if ($price_name == 'price[to]') {
              empty_name = 'до';
            }
            if ($price_name == 'price[from]') {
              empty_name = 'от';
            }
            $option.text(opt[3] ? format_number(opt[3]) : empty_name);
            if (opt[2] == 1) $option.attr('selected', true);
            if (opt[1]) $option.addClass(opt[1]);
            if (opt[0]) $option.attr('base_currency', opt[0]);
            $select.append($option);
          }
        }
      }
    }
    if ($('#restore_advanced').val() == 1) {
      $('a.advancedClose').trigger('click');
    }
  }

  $('.currencyList a').bind('click', function() {
    var $this = $(this);

    $form.find('input[name=currency]').val($this.attr('rel'));

    //Обновить фильтры цен
    check_price.apply(this);

    $this.closest('ul').find('a').removeClass('active');
    $this.addClass('active');

    $form.find('input[name=currency]').trigger('change');
    return false;
  });
  $('.currencyList a[rel=' + $form.find('input[name=currency]').val() + ']').trigger('click');

  function check_price() {
    var $this = $(this);
    if (window.form_config) {
      $form.find('select[name^=price]').each(function() {
        var $select = $(this);
        var select_value = $select.val();
        var options = [];
        var empty_name = ' ';

        if ($select.attr('name') == 'price[from]') {
          options = form_config['price[from][' + $form.find('input[name=currency]').val().toLowerCase() + ']'];
          empty_name = 'от';
        }
        if ($select.attr('name') == 'price[to]') {
          options = form_config['price[to][' + $form.find('input[name=currency]').val().toLowerCase() + ']'];
          empty_name = 'до';
        }

        var $custom = $select.find('option.custom');
        if (select_value && !$custom.is(':selected')) {
          if (!$custom.length) {
            $custom = $('<option class="custom"/>');
          }
          $custom
              .attr({
            base_currency: $this.closest('div.priceSelectBox').find('ul.currencyList').find('.active').attr('rel'),
            base: select_value,
            value: select_value,
            selected: true
          });
        }

        $select.find('option').not('.custom').remove();
        $select.append('<option value="">' + empty_name + '</option>');
        for (var i in options) {
          $('<option/>')
              .attr('value', options[i])
              .text(format_number(options[i]))
              .appendTo($select);
        }

        $custom.appendTo($select);
      });
    }
    else {
      $form.find('select[name^=price]').append('<option value=""> </option>');
    }

    /**
     *  конвертируем выбранное значение селекта
     */
    $form.find('select[name^=price] option.custom').each(function () {
      var $option = $(this);
      if ($option.val() > 0) {
        if (!$option.attr('base')) {
          $option.attr('base', $option.val());
        }
        if (!$option.attr('base_currency')) {
          $option.attr('base_currency', 'RUR');
        }

        var from = $option.attr('base_currency'),
            to = (function() {
              return $this.closest('ul').hasClass('currencyType') ? from : $this.attr('rel');
            })();

        var amount = exchange($(this).attr('base'), from, to);
        if (amount > 1000) {
          amount -= amount % 1000;
        }
        else {
          amount = Math.round(amount);
        }
        $(this)
            .val(amount)
            .text(format_number(amount));
      }
    });
  }

  $('.currencyType a').bind('click', function () {
    var $this = $(this), that = this;
    if ($this.is('.active')) {
      $form.find('input[name=currency_type]').val('');
      $form.find('input[name=currency_type]').trigger('change');
      $this.closest('ul').find('a').removeClass('active');
      return false;
    }
    $form.find('input[name=currency_type]').val($this.attr('rel'));
    $this.closest('ul').find('a').removeClass('active');
    $this.addClass('active');

    type = $form.find('input[type=hidden][name=type]').val();
    currency_type = $form.find('input[name=currency_type]').val();
    $.get('/form/search.js', {type: type, currency_type: currency_type}, function(data) {
          window.form_config = form_config;
          check_price.apply(that);
        }, 'script');
    $form.find('input[name=currency_type]').trigger('change');
    return false;
  });
  $('.currencyType a[rel=' + $form.find('input[name=currency_type]').val() + ']').trigger('click');

  $('a.set-custom').bind('click', function() {
    var $link = $(this);

    var form =
        '<form action="#"><div><table class="popup-value">' +
            '<tbody><tr>' +
            '<td>От <input class="input-from" /></td>' +
            '<td>До <input class="input-to" /></td>' +
            '</tr></tbody>' +
            '<tfoot><tr><td colspan="1">' +
            '<a href="#" class="popupClose">отменить</a> ' +
            '</td><td colspan="1">' +
            '<span class="formButton"><input type="submit" value="Применить" /></span>' +
            '</td></tr></tfoot>' +
            '</table></div></form>';
    var $popupform = show_popup(form, 'auth', $(this).prevAll('h3:first').text()).find('form');

    $popupform.bind('submit', function() {
      var values = {
        from: $popupform.find('.input-from').val(),
        to: $popupform.find('.input-to').val()
      }
      for (name in values) {
        values[name] = String(values[name]).replace(/(^\s+)|(\s+$)/, '');
        if (values[name] != '') {
          var true_val = unformat_number(values[name]);

          var $select = $link.prevAll('select[name$=\[' + name + '\]]');
          var $option = $select.find('.custom');
          if ($option.length == 0) {
            $option = $('<option/>').addClass('custom').appendTo($select);
          }

          $option
              .text(format_number(true_val))
              .attr({
                value: true_val,
                base: true_val,
                selected: true,
                base_currency: $form.find('input[name=currency]').val()
              });
          $select.trigger('change');
        }
      }
      $(this).closest('.popupWrap').find('.popupClose').trigger('click');
      return false;
    });

    return false;
  });

  $('a.select-regionnode').bind('click', function() {
    var $link = $(this);
    var stage = 'map-popup-' + ($link.hasClass('roadnode') ? 'region' : 'metro')
    /**
     * с сервера получили текущие выбранные регионы/станции метро и загрузили их в разметку попапа
     */
    $.get(
        '/form/current-region-nodes', {'stage': stage, 'region_id': jQuery('#region_id').val()},
        function(data) {

          var $form,
              with_metro = $(data).find('div#metromap-pop').length, //есть ли карта метро
              with_city_regions = $(data).find('div#regionmap').length; //есть ли карта округов


          /**
           * показываем попап с картой или списком регионов
           */

          if (with_metro || with_city_regions) {
            $form = show_popup(data, 'loginwindow metromapbox withTab', 'Метро/район/нас. пункт', true);
          } else {
            $form = show_popup(data, 'loginwindow roadbox', 'Район/нас. пункт', true);
          }

          RegionsSelector.popupBox = $form;
          RegionsSelector.triggerLink = $link;

          /**
           * если есть карта метро
           */
          if (with_metro) {
            var regions = metro.regionNames;

            metro.setInputIds();
            metro.setMetroStations();
            metro.bindCheckBoxClick(regions);
          }

          /**
           * если есть карта районов
           */
          if (with_city_regions) {
            regionMap.container = 'regionmap';
            regionMap.init();
          }

          RegionsSelector.init();

          RegionsSelector.checkSelectedRegions();
          RegionsSelector.checkNodeListVisibility();

        },
        'html'
    );

    return false;
  });

  $('a.select-commercialtype').bind('click', function() {
    var $link = $(this);

    form =
        '<div><table>' +
            '<tbody><tr>' +
            '<td><ul class="regionnode-list"></ul></td>' +
            '</tr></tbody>' +
            '<tfoot><tr><td>' +
            '<a href="#" class="popupClose">отменить</a> ' +
            '<span class="formButton"><input type="submit" value="Применить" /></span>' +
            '</td></tr></tfoot>' +
            '</table></div>';

    $.getJSON('/form/values?id=45', function(data) {
      var $list = $link.prevAll('.metroList');

      nodes = '';
      for (i in data) {
        node = data[i];
        checked = $list.find('[value=' + node + ']').length == 1 ? 'checked="checked"' : '';
        node = '<input type="checkbox" value="' + node + '" ' + checked + ' /> ' + node;
        nodes += '<li><label>' + node + '</label></li>';
      }

      form = $(form).find('ul').append(nodes).end().html()

      var $form = show_popup(form, 'auth', 'Тип недвижимости');

      $form.find('.formButton').bind('click', function() {
        if ($list.length == 0) {
          $link.before('<ul class="metroList"/>');
          $list = $link.prev();
        }
        $list.empty();

        $form.find(':checked').each(function() {
          node = '<input type="hidden" value="' + $(this).val() + '" name="field[45][orlike][]" />';
          node += $(this).parent().text();
          $list.append('<li>' + node + '<a href="#">&nbsp;</a></li>');
        });

        if ($list.find('li').length == 0) {
          $list.remove();
        }
        else {
          $list.find('a').bind('click', function() {
            $(this).closest('li').remove();
            $link.closest('form').trigger('submit');
            if ($list.find('li').length == 0) {
              $list.remove();
            }
            if($('#frontstreet').size()) {
              street_auto_complete.apply( $('#frontstreet') );
            }
            return false;
          });
        }

        $(this).prevAll('.popupClose:first').trigger('click');
        $link.closest('form').trigger('submit');
        return false;
      });
    });
    return false;
  });

  $('.pagesNaviBox a, .resultPageMenu a').live('click', function() {
    $('#page').val($(this).attr('rel'))
    $form.trigger('submit');
    window.scrollTo(0, 0);
    return false;
  });

  function update_gmap_coords() {
    if (parseFloat($('#latitude_from').val()) > 0) {
      gmap_center = new google.maps.LatLng(
          (parseFloat($('#latitude_from').val()) + parseFloat($('#latitude_to').val()) ) / 2,
          (parseFloat($('#longitude_from').val()) + parseFloat($('#longitude_to').val()) ) / 2
      );
    }

    if (parseInt($('#zoom').val()) > 0) {
      gmap_zoom = parseInt($('#zoom').val());
    }
  }

  function init_gmap() {
    if( gmap != null || !first_init ) return gmap;
    if (!tick) var tick = function() {};
    
    update_gmap_coords();
    $info.hide();
    $('#search-gmap').empty().each(function() {
      gmap = new google.maps.Map(this, $.extend(gmap_options, {
        center: gmap_center || new google.maps.LatLng(63.103883, 93.414063),
        maxZoom: 18,
        minZoom: 4,
        zoom: gmap_zoom || ( gmap_center ?
          (
              String($('#citySelect').text()).indexOf('г.') != -1 ?
                  10 :
                  8
              )
          : 2)
      }));
      $(this).data('map', gmap);

      google.maps.event.addListener(gmap, "click", function(overlay) {
        if (!overlay) $('body').trigger('mousedown.map-info-popup');
      });
      
      google.maps.event.addListener(gmap, "dragstart", function() {
        disable_popups = true;
        $('body').trigger('mousedown.map-info-popup');
      });
      
      var gmap_move_timeout = null;
      google.maps.event.addListener(gmap, "dragend", function() {
        window.clearTimeout(gmap_move_timeout);
        gmap_move_timeout = window.setTimeout(function(){
          update_gmap(true);
        }, 400);
      });
      
      google.maps.event.addListener(gmap, "zoom_changed", function() {
        window.clearTimeout(gmap_move_timeout);
        gmap_move_timeout = window.setTimeout(function(){
          update_gmap(true);
        }, 400);
      });
      

      var geocoder = new google.maps.Geocoder();
      google.maps.event.addListener(gmap, 'bounds_changed', function() {
        if(first_init) { //Update when we can get bounds
          first_init = false;
          
          if (!gmap_center) { //Calc center if not defined
            geocoder.geocode({'address':'Россия, ' + $('#citySelect').text()}, function(results, status) {
              if (status == google.maps.GeocoderStatus.OK) {
                gmap_center = results[0].geometry.location;
                if (gmap_auto_move == null) {
                  gmap_auto_move = true;
                  gmap.setCenter(gmap_center, String($('#citySelect').text()).indexOf('г.') != -1 ? 10 : 8);
                  window.setTimeout(function() {
                    gmap_auto_move = false;
                    update_gmap();
                  }, 500);
                }
              } else {
                alert("Geocode was not successful for the following reason: " + status);
              }
            });
          } else
            update_gmap();
        }
      });

      $('.searchAddr form').live('submit',function() {
          address = '';
          if ($('input#ck-restrict-region:checked').val() == '1') {
            address = $('ul.cityScroll .current a').text();
          }
          if ($('#addr-search').val() != '') {
            address += ', ' + $('#addr-search').val();
          }
          geocoder.geocode({'address':address}, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
              var coords = results[0].geometry.location;
              gmap.setZoom(15);
              gmap.panTo(coords);
            } else {
              alert("Geocode was not successful for the following reason: " + status);
            }
          });
        return false;
      });
    });

    return gmap;
  }
  
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

  function update_gmap(no_reposition) {
    if (gmap_updating || !gmap || gmap.getBounds() == undefined || gmap.getBounds() == null) return false;
    gmap_updating = true;
    coords = {};
    var bounds = new google.maps.LatLngBounds();
    
    //clearOverlays
    $.each(markersArray, function(i, val){
      markersArray[i].setMap(null);
    });
    markersArray.length = 0;
    
    try {
      //ReCalc bounds
      $('#result .searchResultItem[rel][latitude][longitude]').each(function(i,val) {
        if (!(parseFloat($(this).attr('latitude')) > 0 && parseFloat($(this).attr('longitude')) > 0)) {
          $(this).removeAttr('latitude').removeAttr('longitude');
          return true;
        }
        
        this.marker = null;
        var point = new google.maps.LatLng($(this).attr('latitude'), $(this).attr('longitude'));
        bounds.extend(point);
      });
      
      if (!bounds.isEmpty() && !no_reposition) {
        gmap_auto_move = true;
        gmap.fitBounds(bounds);
        gmap.setCenter( bounds.getCenter() );
        gmap_auto_move = false;
      }
      
      moveend_gmap();
      $('#result .searchResultItem[rel][latitude][longitude]').each(set_marker);
    }catch(e){
      alert( 'Map rendering error' );
    }
    
    gmap_updating = false;
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

  function moveend_gmap() {
    disable_popups = false;
    sw = gmap.getBounds().getSouthWest();
    ne = gmap.getBounds().getNorthEast();

    $('#latitude_from').val(sw.lat());
    $('#latitude_to').val(ne.lat());
    $('#longitude_from').val(sw.lng());
    $('#longitude_to').val(ne.lng());
    $('#zoom').val(gmap.getZoom());

    if (!gmap_updating && !gmap_auto_move && $('a.searchOn').is('.active')) {
      $form.trigger('submit');
    }
  }

  $('a.searchOn').click(function() {
    if ($(this).is('.active')) {
      $form.find('input[name=location-type][value=form]').attr('checked', true).trigger('click');
    }
    else {
      $form.find('input[name=location-type][value=map]').attr('checked', true).trigger('click');
    }

    return false;
  });
  
  $('#map-maximized').bind('change', function () {
    var $block = $('.searchMap');
    if ($(this).val() == 1) {
      $pager_map.show();
      $block.siblings('div.expandable').andSelf().width(757);
      $block.siblings('div.expandable').andSelf().css('margin-left', '-459px');
      moveAddrSearch('aside');
      $('#result').parent().css('padding-top', '440px');
      toggle_active_class($('a.maximize').text('Свернуть'));
    }
    else {
      $pager_map.hide();
      $block.siblings('div.expandable').andSelf().width(298);
      $block.siblings('div.expandable').andSelf().css('margin-left', '0');
      moveAddrSearch('center');
      $('#result').parent().css('padding-top', '0');
      toggle_active_class($('a.maximize').text('Развернуть'));
    }
    
    if( gmap != null ) {
      var oc = gmap.getCenter();
      google.maps.event.trigger(gmap, 'resize');
      gmap.panTo(oc);
    } 

    map_type_check();
  });

  $form.find('input[name=location-type]').bind('click', function() {
    if ($(this).val() == 'map' && $(this).is(':checked')) {
      $('a.searchOn').addClass('active').text('Выключить поиск по карте');
      $('#map-maximized').val(1).trigger('change');
      $form.find('.metroBox').addClass('metrobox-inactive');
      $form.find('.metroBox').remove('a');
      $('.regionnode-list').closest('div.popupWrap').find('a.popupClose').trigger('click');

      if (String(document.cookie).indexOf('prompt') == -1 || getcookie('prompt') != 'off') {
        $('div.prompt').show();
      }
    }
    else {
      $form.find('.metroBox').removeClass('metrobox-inactive');
      $('a.searchOn').removeClass('active').text('Включить поиск по карте');
      $('#map-maximized').val(0).trigger('change');
      $form.find('.metroBox').css('opacity', null).find('a').css('color', '#06C');
      $('div.prompt').hide();
    }

    map_type_check();
    $form.submit();
  }).filter(':checked').trigger('click');

  $('a.maximize').bind('click', function() {
    $('a.searchOn').trigger('click');
    return false;
  });

  function expandMap() {
    $pager_map.show();
    $block.siblings('div.expandable').andSelf().width(757);
    $block.siblings('div.expandable').andSelf().css('margin-left', '-459px');
    moveAddrSearch('aside');
    $('#result').parent().css('padding-top', '440px');
  }

  $('.restrict-region input').bind('change click', function() {
    if ($('input[name=location-type]:checked').val() == 'map') {
      $('#restrict_region')
          .val(($(this).is(':checked') && $(this).closest('.restrict-region').is(':visible')) ? 1 : 0)
          .trigger('change');
    }
  });
  $('#restrict_region').bind('change', function() {
    $(this).val() == 1 ?
        $('.restrict-region input').attr('checked', true) :
        $('.restrict-region input').removeAttr('checked');
  });

  function map_type_check() {
    var type = $('input[name=location-type]:checked').val();
    gmap && (type == 'map' && $(document).width() > 1010 ?
        gmap.setOptions({scrollwheel: true}) :
        gmap.setOptions({scrollwheel: false}));
    type == 'map' ? $('.restrict-region').show() : $('.restrict-region').hide();
    $('.restrict-region input').trigger('change');
  }

  function gmap_show_info() {
    if (disable_popups) return false;
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
          'left': $('#search_map').css('margin-left'),
          'margin-left': (pos.x - 260) + 'px',
          'margin-top': ($('#search_map').position().top + pos.y + 2) + 'px'
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

    var url = build_query('/lot/info-window', {id: marker.rel, currency: $form.find('#currency').val()});
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

  function gmap_move_block(map_offset, $item) {
    var $items = $('#result .searchResultItem'),
        top_offset = 443;

    if ($item) {
      $items = $items.not($item);
    }

    if ($form.find('#map-maximized').val() == 1 && $item) {

      if (map_offset == 0) {
        $('#result').parent().stop().animate({paddingTop: top_offset}, 'fast');
        $item.siblings('.searchResultItem').css({marginTop: 0});
        $item.stop().animate({marginTop: 0}, 'fast');
      }
      else {
        if (parseInt($('#result').parent().css('padding-top')) > 0) {
          map_offset -= top_offset;
          $('#result').parent().stop().animate({paddingTop: 0}, 'fast');
        }
        else {
          need_fix = false;
          $item.prevAll('.searchResultItem').andSelf().each(function() {
            var offset_top = parseInt($(this).css('margin-top'));
            map_offset -= offset_top;
            need_fix = need_fix || offset_top > 0;
          });
          map_offset += need_fix ? 15 : 0;
        }
        $('#result').attr('style','zoom: 1;');
        var item_offset = $('#search_addr').height()
          + $('#search_map').height()
          + 30;

        $item.stop().animate({marginTop: item_offset}, 'fast', function(){
          $('#result').attr('style','zoom: 0;');
        });
      }
    }
    else if ($form.find('#map-maximized').val() == 1) {
      $('#result').parent().stop().animate({paddingTop: top_offset}, 'fast');
    }

    $items.stop().animate({marginTop: 0}, 'fast');

    if (map_offset === 0) {
      $('.contentRight_02.search').stop().animate({paddingTop: 431}, 'fast');
      $('#search_addr').css({top: '0'});
      $('#search_map').css({top: '33px'});
    } else {

      $('.contentRight_02.search').stop().animate({paddingTop: 0}, 'fast');
      $('#search_addr').stop().animate({top: map_offset}, 'fast');
      $('#search_map').stop().animate({top: map_offset + 33}, 'fast');
    }
  }
  
  $('.itemAction .show-map').live('click', function () {
    var $item = $(this).closest('.searchResultItem');
    var $prev = $item.prevAll('.searchResultItem');
    var map_offset = 0;

    if (!$(this).is('.active') && $prev.length) {
      map_offset += $item.offset().top;
      map_offset -= $('.viewObjects').offset().top;
      $(this).attr('title', 'Вернуть карту на место');
    } else {
      $(this).attr('title', 'Показать на карте');
      $(this).trigger('blur');
    }
    gmap_move_block(map_offset, $item);

    toggle_active_class($('.itemAction .show-map.active').not(this).attr('title', 'Показать на карте'));
    if ($prev.length) {
      toggle_active_class(this);
    }
    return false;
  });

  $('input[name=q_text]').change(function(){
    if(!$('#q_text_enabled').attr('checked')) {
      $('#q_text_enabled]').attr('checked', true);
      is_init = true;
    }
  });

  $('.update-search-text').bind('click', function() {
    $form.find('[name=q]').val($('input[name=q_text]').val());
    if ($('input[name=q_text_enabled]').length && !$('input[name=q_text_enabled]:checked').length) {
      $('input[name=q_text_enabled]').attr('checked', 'checked');
    }
    $form.trigger('submit');
    return false;
  });

  $('input[name=q_text_enabled]').bind('click', function() {
    if ($(this).is(':checked')) {
      $form.find('[name=q]').val($('input[name=q_text]').val());
    }
    else {
      $form.find('[name=q]').val('');
    }
    $form.trigger('submit');
    return true;
  });

  $form.find('input[name=type]').bind('click check', function() {
    var $type = $form.find('input[name=type]:checked');
    if ($type.size()) {
      if (!$('input[name=q_text_enabled]').length || $('input[name=q_text_enabled]:checked').length) {
        window.location.href = build_query($type.attr('href'), {q: $('input[name=q_text]').val()});
      }
      else {
        window.location.href = $type.attr('href');
      }
    }
  }).trigger('check');

  if (String(location.href).match(/map-maximized\/\d/)) {
    $('#map-maximized').val(String(location.href).split('map-maximized/')[1].split('/')[0]);
  }
  if ($('#map-maximized[value="1"]').size()) {
    $('#map-maximized[value="1"]').trigger('change');
  }
  
  google.maps.event.addDomListener(window, 'load', init_gmap);

  function check_pager_map() {
    var page = 0;
    var pages = 0;
    $('input[name=location-type]:checked').val() == 'form' ? $pager_map.hide() : $pager_map.show();
    $('.pageNumber:first').each(function() {
      page = parseInt($(this).text().split('/')[0]);
      pages = parseInt($(this).text().split('/')[1]);
    });

    if (page <= 1) {
      $pager_map.find('[rel=prev]').html('&larr;&nbsp;предыдущие ' + app_settings.max_per_page);
    }
    else {
      $pager_map.find('[rel=prev]')
          .html('<a href="#">&larr;&nbsp;<span>предыдущие ' + app_settings.max_per_page + '</span></a>')
          .find('a').bind('click', function() {
            $('.resultPageMenu a.previous:first').trigger('click');
            $(window).scrollTop(500);
            return false;
          });
    }

    if ((page > 0 && pages == page) || pages < 2) {
      $pager_map.find('[rel=next]').html('следующие ' + app_settings.max_per_page + '&nbsp;&rarr;');
    }
    else {
      $pager_map.find('[rel=next]')
          .html('<a href="#"><span>следующие ' + app_settings.max_per_page + '</span>&nbsp;&rarr;</a>')
          .find('a').bind('click', function() {
            $('.resultPageMenu a.next:first').trigger('click');
            $(window).scrollTop(500);
            return false;
          });
    }
  }

  check_pager_map();

  watch_hash();
  window.setInterval(watch_hash, 300);

  //Scroll to lots
  $('a.top').click(function (){
    window.scrollTo(0, 557);
    return false;
  });

  function get_diff(obj1, obj2, direction) {
    direction = direction || "left";

    var diff = new Array();

    function get_data_object(hash) {
      var hash = String(hash).split('/');
      var obj = {};
      for(i = 0; i < hash.length; i+=2) {
        var key   = hash[i];
        var value = hash[i+1];

        if(obj.hasOwnProperty(key)) {
          if(!(obj[key] instanceof Array)) {
            var mid_value = obj[key];
            obj[key] = new Array();
            obj[key].push(mid_value);
          }
          obj[key].push(value);
          obj[key] = obj[key].sort(function(a,b){return a < b;});
        }
        else {
          obj[key] = value;
        }
      }
      return obj;
    }

    function do_get_diff(left, right) {
      for(property in left) {
        if(!left.hasOwnProperty(property)){
          continue;
        }
        if(!right.hasOwnProperty(property)) {
          diff.push(property);
          continue;
        }
        if(left[property] != right[property]) {
          diff.push(property);
          continue;
        }
      }
    }

    obj1 = get_data_object(obj1);
    obj2 = get_data_object(obj2);

    if(direction == 'left' || direction == 'both') {
      do_get_diff(obj1, obj2);
    }

    if(direction == 'right' || direction == 'both') {
      do_get_diff(obj2, obj1);
    }
    return diff.length > 0 ? diff : false;
  }
});

function in_array(needle, haystack) {
  var key = '';
  for (key in haystack) {
    if (haystack[key] == needle) {
      return true;
    }
  }
  return false;
}

function translate_field(name, reverse) {
  var t_table = window.field_tranlations;
  if (reverse) {
    for (origin in t_table) {
      if (t_table[origin] == name) {
        return origin;
      }
    }
  }
  else {
    if (t_table[name]) {
      return t_table[name];
    }
  }

  return name;
}

function translit_field(key, value, reverse) {
  var passed = false;
  for (i in window.translit_fields) {
    if (window.translit_fields[i] == key || window.translit_fields[i] == translate_field(key, true)) {
      passed = true;
      break;
    }
  }
  if (!passed) return value;

  var t_table = window.translit;

  value = decodeURIComponent(value);

  if (reverse) {
    for (origin in t_table) {
      value = value.split(t_table[origin]).join(origin);
    }
  }
  else {
    for (origin in t_table) {
      value = value.split(origin).join(t_table[origin]);
    }
  }

  return value;
}

//выравнивает по высоте карточки в режиме показа блоками
function set_equal_height_block_view() {
  $('div.block-view').find('div.items-row-wrap').each(function() {

    var h = [];
    h.push($(this).children('div').eq(0).height());
    h.push($(this).children('div').eq(1).height());
    if (h[0] > h[1]) {
      $(this).children('div').eq(1).height(h[0]);
    } else {
      $(this).children('div').eq(0).height(h[1]);
    }
  });
}

/**
 * перемещает форму поиска по адресу из блока над картой в результат поиска и обратно
 */
function moveAddrSearch(location){
  var form = $('#searchAddrForm');

  if (typeof location === 'undefined') {
    location = form.parent().hasClass('searchAddr') ? 'ceter' : 'aside';
  }

  if (location === 'center') {
    form.insertBefore('#result');
    form.hide();
    form.find('#addr-search').attr('value', '');
    $('div.text-search').show();
  } else {
    $('div.text-search').hide();
    $('#q_text, #q').attr('value', '');

    form.prependTo('div.searchAddr-wrap');
    form.show();
  }

}

// возвращает cookie с именем name, если есть, если нет, то undefined
function getCookie(name) {
  var matches = document.cookie.match(new RegExp(
    "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
  ));
  return matches ? decodeURIComponent(matches[1]) : undefined;
}
