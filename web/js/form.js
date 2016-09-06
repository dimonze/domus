var street_auto_complete = function() {};

$(function() {

  if(location.pathname == '/lot/add' && location.search.indexOf('new_building')) {
    var rating_field     = $('#dynamicform_rating');
    var submit_button    = $('.addButton .formButton input[type="submit"]');
    submit_button.click(function(){
      if(!rating_field.val()){
        rating_field.val($('#rate-diag .rate strong').text());
      }
    });
  }

  if ($('#dynamicform_region_id').size() && $('#dynamicform_region_id').val() == '') {
    $('#dynamicform_region_id').val($('.cityScroll .current a').text())
  }

  if($('#dynamicform_field68').size() && $('#dynamicform_field68').val() == ''){
    $('#dynamicform_field68').find('option[value="месяц"]').attr('selected','selected');
  }
  
  $('#dynamicform_field69').find('option[value="год"]').attr('selected','selected');
  
  function isNewBuilding(){
    return ( ($('#dynamicform_type').size() && $('#dynamicform_type').val() == 'new_building-sale')
      || ($('#dynamicform_type_new_building-sale').size() && $('#dynamicform_type_new_building-sale').is(':checked') )
    );
  }

  $('#dynamicform_description').keypress(function(e){
    if (!isNewBuilding() && $(this).val().length > 1000 && letternumber(e)) {
      return false;
    }
  });

  $('#dynamicform_description').keyup(function(e){
    if(!isNewBuilding())
      $(this).val($(this).val().substr(0,1000));
  });

  $('.update_captcha').live('click', function() {
    $('img.captcha').attr('src', '/kcaptcha.png?' + (new Date).getTime());

    return false;
  })

  $('.update_qa_captcha').live('click', function() {
    $('img.captcha').attr('src', '/qa-kcaptcha.png?' + (new Date).getTime());

    return false;
  })

  $('textarea[max]').bind('keyup', function() {
    var $this = $(this);
    var $label = $this.nextAll('.field-help:first')
    
    var lbs = $this.val().match(/\r\n/g);
    if(!lbs) lbs = $this.val().match(/\n/g);
    if(!lbs) lbs = '';
    
    var left = $this.attr('max') - (String($this.val()).length + lbs.length);
    label  = 'Остал' + ending(left, 'ся', 'ось', 'ось');
    label += ' '       + left;
    label += ' символ' + ending(left, '', 'а', 'ов');
    $label.text(label);

    if (left <= 0) {
      $this.val($this.val().substring(0, $this.attr('max')));
    }

  });

  $('.field .phone-row:first').each(function() {
    var $row = $(this);

    if ($row.find('a.add').length) return true;

    $addphonerow = $('<a/>')
      .attr('href', '#').addClass('inner add')
      .text('Добавить номер')
      .bind('click', function() {
        $row.clone()
          .find('a.add').remove().end()
          .find('input').val('').removeAttr('id').end()
          .appendTo($row.parent());
        return false;

      });
    $row.append('&nbsp;').append($addphonerow);
  });


  $('input[type=radio][name=dynamicform\\[type\\]]').bind('change.user click.user', function() {
    if ($(this).is(':checked')) {
      location.href = build_query(location.href, {type: $(this).val()});
    }
  });


  function show_hide_city() {
    var $city_region = $('input[id$=city_region]').closest('tr');
    var cond1 = $('select.regionnode:first option:selected').attr('has_street') == 1;
    var cond2 = is_city($('.form-region_id select').val());
    if (cond1 || cond2) $city_region.hide();
    else $city_region.show();
  }
  $('.form-region_id select').bind('change', show_hide_city);


  $('label.radiocombo').each(function() {
    $this = $(this);
    if (/select:[0-9,]+/.test($this.html())) {
      data = $this.html().split('|');
      if (data.length < 2) return true;

      $input = $(document.getElementById($this.attr('for')));

      values = data[0].replace('select:', '').split(',');
      if (data.length > 2) {
        suffixes = data[1].split(',');
      }
      else {
        suffixes = ['', '', ''];
      }
      $this.html(data[data.length - 1]);

      var $select = $('<select/>');
      $select.bind('change', function() {
        id = $(this).next().attr('for');
        $input = $(document.getElementById(id));
        $input.val(
            $(this).val() +
            ending($(this).val(), suffixes[0], suffixes[1], suffixes[2], suffixes[3]) +
            $input.attr('oldvalue'))
          .attr('checked', true).trigger('change');
      });
      for (i in values) {
        $('<option/>').val(values[i]).text(values[i]).appendTo($select);
      }

      $select.hide().insertBefore($this);

      $input.attr('oldvalue', $input.val());
      if (set_val = document.getElementById($input.attr('name') + '_value')) {
        val =
          $(set_val).remove().val()
          .replace($input.attr('oldvalue'), '')
          .replace(new RegExp('(' + suffixes.join('|') + ')$'), '')

        if (val in values) {
          $input.attr('checked', true);
          $select.val(val).trigger('change');
        }
      }
      $select.css('display', 'inline').trigger('change');
    }
  });

  var $regionnode = $('select.regionnode');
  $('select.regionnode').each(function() {
    if ($(this).is('.dupe') || $(this).attr('binded') == 1) return true;
    $(this).attr('binded', 1);
    $regionnode = $(this);
    var $region = $(document.getElementById($regionnode.attr('id').replace('region_node', 'region_id')));

    var clear_citynode = function() {
      var $city_region = $(document.getElementById($region.attr('id').replace('region_id', 'city_region')));
      $city_region.val('');
      
      //#18427
      if($(this).attr('id') == 'dynamicform_region_id' ) return true; //Regionnodes only
      var csnd = $(this).children(':selected');
      if(!csnd || !csnd.text().trim()) return true;
      
      var crfrxp = null;
      var crxp = [/^г\.\s+|^пгт\s+/,/\s+р-н$|\s+горсовет$/];
      for(var i in crxp) {
        crfrxp = csnd.text().match(crxp[i]);
        if(crfrxp) {
          crxp = crxp[i]; //Current RegExp
          break;
        } 
      }
      
      if(!crfrxp //No limits
        || ( //RegExp was changed
          typeof($(this).data('crxp')) != 'undefined'
          && crxp.toString() != $(this).data('crxp').toString()
        )) {
        
        var clrxp = typeof($(this).data('crxp')) != 'undefined' ? 
          $(this).data('crxp') : false;
        
        if(clrxp) { //Clear previous RegExp limits
          $(this).siblings('select.regionnode').children('option').filter(function(){
            return $(this).text().match(clrxp);
          }).removeAttr('disabled');
        }
        
        if(!crfrxp) {
          $(this).removeData('crxp');
          return true;
        }
      }
      
      $(this).siblings('select.regionnode').children('option').filter(function(){
        return $(this).text().match(crxp);
      }).attr('disabled','disabled');
      $(this).data('crxp',crxp);
    };

    $region.bind('change.noauto', clear_citynode);

    $region.bind('change.auto', function() {
      if (!$(this).val()) return;
      $regionnode.empty().append('<option>Загружается список...</option>').attr('disabled', true);

      var $regionnodes = $('select.regionnode', $(this).closest('form'));

      var labelForSelect = function(region_id) {
        var first_label, second_label;
        if (77 == region_id || 78 == region_id) {
          first_label = '* Район';
          second_label = '* Метро';
        }
        else {
          first_label = '* Город/Район';
          second_label = '* Шоссе';
        }

        if($('.form-region_node label').size() == 1) {
          $('.form-region_node label').html(first_label);
          $('.form-region_node label')
            .after('</label><br><br><label for="dynamicform_region_node_1">' + second_label + '</label>')
            .after('</label><br><br><label for="dynamicform_region_node_2">' + second_label + '</label>');
        }
        
        $('.form-region_node label').eq(0).html(first_label);
        $('.form-region_node label').eq(1).html(second_label);
        $('.form-region_node label').eq(2).html(second_label);
      };

      labelForSelect($(this).val());
      
      if ($regionnodes.length == 1) {
        for (var i = 1; i <= 2; i++) {
          $regionnodes.after('<br />')
          $regionnodes.clone()
            .addClass('dupe')
            .attr('id', $regionnodes.attr('id') + '_' + i)
            .insertAfter($regionnodes.next());
        }
        $regionnode = $('select.regionnode', $(this).closest('form'));
      }

      $regionnode.bind('change.noauto', clear_citynode);

      $.getJSON(
        build_query($regionnode.attr('source'), {'region_id': $(this).val()}),
        function(data) {
          $regionnode
            .empty()
            .eq(0).append('<option value="">Выберите из списка</option>')
            .end()
            .slice(1).append('<option value="">Выберите из списка</option>');

          var bind_by_label = false;
          var set_val = $('#' + $regionnode.attr('id') + '_value');
          if (!set_val.size()) {
            set_val = $('#' + $regionnode.attr('name').replace(/[\[,\]]+/g, '_') + 'value');
            bind_by_label = true;
          }
          
          var value = [];
          if (set_val.size()) {
            value = $(set_val).val().split(','); //.remove()
          }
          else {
            for (var i = $regionnode.length; i > 0; i--) {
              value[value.length] = -1;
            }
          }
          
          for(var rnb in data){
            for(var cn in data[rnb]) {
              $regionnode.eq(rnb).show();
              $('.form-region_node td label').eq(rnb).show();
              
              var row = data[rnb][cn];
              if (typeof row.text == 'undefined') continue;
              
              var $option = $('<option/>');
              $option.text(row.text);
              for (var attr in row) {
                $option.attr(attr, row[attr])
              }
              
              i = $.inArray($option.val(), value);
              if( i !== -1 && !$regionnode.eq(rnb).children('option[value]:selected').size() ){
                $option.attr('selected', true);
                value.splice(i,1);
              }

              $regionnode.eq(rnb).append($option);
            }
            if( !data[rnb].length ) {
              $regionnode.eq(rnb).hide();
              $('.form-region_node td label').eq(rnb).hide();
            }
          }

          $regionnode.unbind('change.city_visibility').bind('change.auto.city_visibility', show_hide_city);
          $regionnode.removeAttr('disabled').trigger('change.auto');
        }
      );
    }).trigger('change.auto');
  });



  $('.autocomplete-city[source]').each(function() {
    var $input = $(this);

    if (typeof $input.autocomplete != 'function') {
      return false;
    }

    $input.autocomplete(
      $input.attr('source'),
      {
        delay: 300,
        minChars: 1,
        matchSubset: 1,
        matchContains: 1,
        cacheLength: 35,
        maxItemsToShow: 100,
        autoFill: false,
        onItemSelect: function(item) {
          if (item.extra) {
            $input.attr('node', item.extra[0]).attr('node_value', $input.val());
          }
          else {
            $input.removeAttr('node');
          }
          $input.trigger('change.auto');
        }
      }).change(function() {
        if ($input.val() != $input.attr('node_value')) {
          $input.removeAttr('node');
        }
      });

    $(document.getElementById($input.attr('id').replace('city_region', 'region_node')))
      .bind('change.auto', function() {
        $input[0].autocompleter.flushCache();
        $input[0].autocompleter.setExtraParams({regionnode: $(this).val()});

        if ($('option[value='+$(this).val()+']', this).attr('has_children') != 1) {
          $input.attr('dontcomplete', true);
        }
        else {
          $input.removeAttr('dontcomplete');
        }
      }).trigger('change.auto');

  });

  $('.autocomplete-street[source]').each(function() {
    var $input = $(this);
    
    if (typeof $input.autocomplete != 'function') {
      return false;
    }
    
    $input.autocomplete(
      $input.attr('source'),
      {
        delay: 300,
        minChars: 1,
        matchSubset: 1,
        matchContains: 1,
        cacheLength: 15,
        maxItemsToShow: 100,
        autoFill: false
      });
    
    street_auto_complete = function() {
      $input[0].autocompleter.flushCache();

      if($(this).attr('id') == undefined){
        return true;
      }
      
      var $region =  $('#region_id');
      var $cityregion = $region;
      //Форма создания лота и админка "посадочных" страниц
      if($(this).attr('id').indexOf('dynamicform') > -1) {
        $region = $(document.getElementById($(this).attr('id').replace('region_node', 'region_id')));
        $cityregion = $(document.getElementById($(this).attr('id').replace('region_node', 'city_region')));
      }
      
      if ($cityregion.attr('node')) {
        $input[0].autocompleter.setExtraParams({regionnode: $cityregion.attr('node')});
        $input.removeAttr('dontcomplete');
      }
      else if (is_city($region.val())) {
        $input[0].autocompleter.setExtraParams({region: $region.val()});
        $input.removeAttr('dontcomplete');
      }
      else {
        //"Лицевой" поиск по улицам
        if($(this).attr('id').indexOf('frontstreet') > -1) {
          var nodes = [];
          $('ul.metroList input[value]').each(function(){
            nodes.push( $(this).val() );
          });
          
          if( nodes.length )
            $input[0].autocompleter.setExtraParams({frontstreet: true, regionnode: nodes.join(',')});
          else
            $input[0].autocompleter.setExtraParams({region: $('#region_id').val()});
        } else {
          $input[0].autocompleter.setExtraParams({regionnode: $(this).val()});
          if ($('option[value='+$(this).val()+']', this).attr('has_street') != 1) {
            $input.attr('dontcomplete', true);
          }
          else {
            $input.removeAttr('dontcomplete');
          }
        }
      }

      return true;
    }
        
    if($(this).attr('id').indexOf('dynamicform') > -1) {
      $(
        [
          document.getElementById($input.attr('id').replace('street', 'region_node')),
          document.getElementById($input.attr('id').replace('street', 'city_region'))
        ]
        )
        .bind('change.auto', street_auto_complete).trigger('change.auto');
    }
    
    //"Лицевой" поиск по улицам
    if($(this).attr('id').indexOf('frontstreet') > -1) {
      return street_auto_complete.apply($input);
    }
  });

  //ReGMap START
  $('.input-gmap').each(function() {
    var $div = $(this);
    var $input = $div.prev();

    var default_coords = new google.maps.LatLng(63.103883,93.414063);
    var default_zoom = 2;
    if ($input.val()) {
      default_coords = new google.maps.LatLng($input.val().split(',')[0], $input.val().split(',')[1]);
      default_zoom = 14;
    }

    var map = new google.maps.Map($div.get(0), $.extend(gmap_options,{
      center: default_coords,
      zoom: default_zoom
    }));
    var geocoder = new google.maps.Geocoder();
    
    var marker_opts = gmap_marker_options;
    marker_opts.map = map;
    marker_opts.position = default_coords;
    marker_opts.draggable = true;
    var marker = new google.maps.Marker(marker_opts);
    google.maps.event.addListener(marker, "dragend", function() {
      $input.val(marker.getPosition().lat()+','+marker.getPosition().lng())
    });

    var prev_addr = '';
    var gmap_do_search = function() {
      var address = '';
      $address.each(function() {
        if (this.tagName.toLowerCase() == 'select') {
          if ($(this).val()) {
            val = $('option[selected]', this).text();
          }
          else {
            val = null;
          }
        }
        else {
          val =$(this).val();
        }

        if ($(this).attr('name').indexOf('region_node') != -1 && !$('option[selected]', this).attr('has_children')) {
          val = null;
        }
        else if ($(this).attr('name').indexOf('region_node') != -1 && val.indexOf('м. ') == 0) {
          if ($address.eq(3).val() != '')
          val = null;
        }
        else if ($(this).attr('name').indexOf('region_node') != -1 && val.indexOf('район ') == 0) {
          val = val.substr(6);
        }
        else if ($(this).attr('name').indexOf('region_node') != -1 && val.length <= 4) {
          val = null;
        }
        else if (val && $(this).attr('name').indexOf('building') != -1) {
          val = 'корп. ' + val;
        }
        else if (val && $(this).attr('name').indexOf('structure') != -1) {
          val = 'стр. ' + val;
        }

        address += test_and_append(val);
      });
      if (address && address != prev_addr) {
        address = address.replace('м. ', 'метро ');
        prev_addr = address;
        geocoder.geocode({'address':address}, function(results, status) {
          if (status == google.maps.GeocoderStatus.OK) {
            var coords = results[0].geometry.location;
            marker.setPosition(coords);
            google.maps.event.trigger(marker, "dragend");
            map.setZoom(get_zoom_for_address($address));
            map.panTo(coords);
          } else {
            alert("Geocode was not successful for the following reason: " + status);
          }
        });
      }
    };
    var gmap_search_timeout = null;

    var $address = $([
      document.getElementById($input.attr('id').replace('coords', 'region_id')),
      document.getElementById($input.attr('id').replace('coords', 'region_node')),
      document.getElementById($input.attr('id').replace('coords', 'city_region')),
      document.getElementById($input.attr('id').replace('coords', 'street')),
      document.getElementById($input.attr('id').replace('coords', 'address_house')),
      document.getElementById($input.attr('id').replace('coords', 'address_building')),
      document.getElementById($input.attr('id').replace('coords', 'address_structure'))
    ])
    .bind('change', function() {
      var $input = $(this);
      $address.each(function(i) {
        if (i < 3 && $input.attr('id') == $(this).attr('id')) {
          $address.slice(i + 1).val('');
          return false;
        }
      });
      window.clearTimeout(gmap_search_timeout);
      if (!$address.find('[disabled]').length) {
        gmap_search_timeout = window.setTimeout(gmap_do_search, 250);
      }
    });
  });
  //ReGMap end
  
  
  bindButtons();

  $('select.inputprice').each(function() {
    var $input = $(this).prev();
    var $select = $(this);

    $(this).closest('form')
      .bind('pre-submit', function() {
        if ($select.attr('treaged')) return;

        $input.val() && $input.val($select.val() + $input.val());
        $select.attr('treaged', true);
      })
      .bind('post-submit', function() {
        if (!$select.attr('treaged')) return;

        $input.val() && $input.val($input.val().substr(3));
        $select.removeAttr('treaged');
      });
  });


  var bold_checkboxes = function() {
    var $this = $(this);
    var $label = $('label[for=' + $this.attr('id') + ']');
    // nice :P
    if ($label.length == 0) {
      $label = $this.closest('label');
    }
    if ($label.length == 0) {
      $label = $this.next('label');
    }
    if ($label.length == 0) {
      $label = $this.next().next('label');
    }

    if ($label.length == 0) {
      return;
    }

    if ($this.is(':checked')) {
      $label.addClass('selected');
    }
    else {
      $label.removeClass('selected');
    }
  }

  var $checkboxesandradios = $('.addForm').find(':checkbox,:radio');
  $checkboxesandradios.bind('change click init', function() {
      if ($(this).is(':radio')) {
        var name = String($(this).attr('name')).replace(/\[/g, '\[').replace(/\]/g, '\]');
        $checkboxesandradios.filter('[name='+name+']').each(bold_checkboxes);
      }
      else {
        $(this).each(bold_checkboxes);
      }
    })
    .filter(':checked').trigger('init');


  /*
   * live() emulatiion :P
   * 'cause jquery 1.3.2 does'n support live() for change and submit events
   */

  var update_captcha_post = function() {
    $(this).find('.update_captcha').click();
    $(this).find(':input.captcha').val('');
  };
  var radio_checkbox_change = function() {
    if ($(this).closest('div').hasClass('user-type')) {
      var index = $(this).closest('li').prevAll().length;
      var div =$(this).closest('div');
      $(this).closest('ul').get(0).className = 'selected'+index;
      div.find('span.selected').removeClass('selected');
      div.find('span.desc').hide().eq(index).css('display','block');
      $(this).next('span').addClass('selected');
    }

    if ($(this).attr('hide')) $(this).closest('form').find($(this).attr('hide')).hide();
    if ($(this).attr('show')) $(this).closest('form').find($(this).attr('show')).show();
  };
  var init_values_init = function() {
    $(this).find(':input.area-code').attr('init-value', 'код');
    $(this).find(':input.phone').attr('init-value', 'номер');
  };
  var init_values_blur = function() {
    if ($(this).val().replace(/\s+/, '') == '') $(this).val($(this).attr('init-value'));
  };
  var init_values_focus = function() {
    if ($(this).val() == $(this).attr('init-value')) $(this).val('');
  };



  $('body').bind("ajaxComplete", function(){
    var $popup = $(this).find('.popupCenter');
    if ($popup.attr('binded')) return;
    $popup.attr('binded', true);

    $popup.find('form:has(.captcha)')
      .bind('post-submit', update_captcha_post);

    $popup.find(':radio, :checkbox')
      .bind('change click', radio_checkbox_change)
      .filter(':checked').trigger('change');

    $popup.each(init_values_init);
    $popup.find('[init-value]')
      .bind('blur', init_values_blur)
      .bind('focus', init_values_focus);

  });

  $('form .text-select[rel]').each(function() {
    var $input = $(this).hide().after('<div class="regions-text"/>').removeClass('text-select');
    var $text = $input.next().html('<div/><a href="#" class=popup>Выбрать</a>');
    var type = $(this).attr('rel');

    var getItems = function() {
      var _data = String($input.val()).split(', ');
      var data = {};
      for (i in _data) {
        data[_data[i]] = true;
      }
      return data;
    }

    $text.children('div').text($input.val());
    $text.children('a').bind('click', function() {
      var form =
        '<div><table>'+
          '<tbody><tr>' +
            '<td><ul class="regionnode-list"></ul></td>' +
          '</tr></tbody>' +
          '<tfoot><tr><td>' +
            '<a href="#" class="popupClose">отменить</a> ' +
            '<span class="formButton"><input type="submit" value="Выбрать" /></span>' +
          '</td></tr></tfoot>' +
        '</table></div>';

      $.getJSON('/form/' + type, function(data) {
        var nodes = '';
        var items = getItems();
        for (i in data) {
          var node = data[i];
          var checked = items[node.text] ? 'checked="checked"' : '';
          node = '<input type="checkbox" value="'+node.text+'" ' + checked + ' /> ' + node.text;
          nodes += '<li><label>'+node+'</label></li>';
        }

        form = $(form).find('ul').append(nodes).end().html()

        var title = '';
        switch (type) {
          case 'regions':title = 'Выберите регион';break;
          case 'specialities':title = 'Выберите специализацию';break;
        }
        var $form = show_popup(form, 'auth', title);

        $form.find('.formButton').bind('click', function() {
          var data = [];
          $form.find(':checked').each(function() {
            data[data.length] = String($(this).parent().text()).replace(/^\s+|\s+$/, '');
          });
          data = data.join(', ');

          $input.val(data);
          $text.children('div').text(data);

          $(this).prevAll('.popupClose:first').trigger('click');
          return false;
        });
      });
      return false;
    });
  });


  $('#rate-diag').each(function() {

    var initTop = $(this).offset().top;

    var $box = $(this).hide();
    var $inputs = $('form.addForm').find(':input[name][rating], button[rating]').not('select.inputprice');

    if (!$inputs.length) {
      return true;
    }

    var nb_total = 0;
    var nb_filled = 0;
    var cur_rating = 0;
    var max_rating = 0;


    $('a.show', this).click(function(){
      $(this).siblings('.hidden').toggle();
      return false;
    });

    $(window).scroll(function(){
      var msie6 = false;
      if ($.browser == 'msie' && $.browser.version == '6.0') {
        msie6 = true;
      }

      var scrollTop = window.scrollY ? window.scrollY : document.body.parentNode.scrollTop;

      if (scrollTop >= initTop - 80) {
        if (msie6) {
          $box.addClass('rate-diag-abs');
          $box.css({top: scrollTop + 'px'});
        } else {
          $box.addClass('rate-diag-fixed');
          $box.css({top: '70px'});
        }
      } else {
        $box.removeClass('rate-diag-abs');
        $box.removeClass('rate-diag-fixed');
        $box.css({top: 0});
      }
    });

    function rate_check_input_filled($input, update) {

      var user_rating = $('#dynamicform_rating').val();

      if (update && $input.attr('filled') == 1) {
        nb_filled--;
        cur_rating -= parseInt($input.attr('crate') || $input.attr('rating'));
      }

      var filled = false;

      if ($input.is(':checkbox, :radio')) {
        $input = $inputs.filter('[name=' + $.escapeAttr($input.attr('name')) + ']');
        filled = $input.filter(':checked').length > 0;
      } else if ($input.is('.inputprice')) {
        filled = $input.is('input') && $input.val() != '';
      } else if ($input.is('.ajax-upload')) {
        var rates = $input.attr('rating').split('|');
        var crate = 0;
        $(':hidden[name=' + $.escapeAttr($input.attr('rel') + '[]') + ']').each(function() {
          crate += parseInt(crate == 0 ? rates[0] : rates[1]);
        });

        $input.attr('crate', crate);
        filled = crate > 0;
      } else if ($input.is('select, input[type=text], textarea')) {
        filled = $input.val() != '';
      }

      if (filled) {
        nb_filled++;
        cur_rating += parseInt($input.attr('crate') || $input.attr('rating'));
        $input.attr('filled', 1);
      } else {
        $input.removeAttr('filled');
      }      
    }

    function rate_update_box() {
      $box.find('[rel=total]').text(nb_total);
      $box.find('[rel=filled]').text(nb_filled);
      $box.find('[rel=rating]').text(cur_rating);
      var rating_width = Math.round(cur_rating / max_rating * 100);
      if (rating_width <= 100) {
        $box.find('[rel=bar]').stop().animate({width: rating_width + '%'});
      }      
    }

    // init
    var checked_names = {};
    $inputs.each(function() {
      var $this = $(this);
      if (checked_names[$this.attr('name')]) {
        return true;
      }

      if ($this.is('.ajax-upload')) {
        nb_total++;
        var rates = $this.attr('rating').split('|');
        if ($this.attr('max')) {
          max_rating += parseInt(rates[0]) + parseInt(rates[1]) * (parseInt($this.attr('max')) - 1);
        }
        else {
          max_rating += parseInt(rates[0]) + parseInt(rates[1]);
        }
      }
      else {
        max_rating += parseInt($this.attr('rating'));
        // console.log('rating - ' + parseInt($this.attr('rating')));
        // console.log('max_rating - ' + max_rating);
        nb_total++;
      }

      rate_check_input_filled($this, false);
      checked_names[$this.attr('name')] = true;
    });
    // console.log('max_rating_itog - ' + max_rating);
    rate_update_box();
    $box.show();

    // bind

    $inputs.bind('change click rerate', function(e) {
      rate_check_input_filled($(this), true);
      rate_update_box();
    });
  });
  // Company without logo
  $('input[type=checkbox][name=settings\[show_rating\]]').each(function(){
    if ($('.userpic .image img').attr('src') == '/images/pict_company.png'){
      $(this).removeAttr('checked');
    }
  });


  $('.employees a.delete').click(function() {
    var href = $(this).attr('href');
    var body = '<div><table class="popup-value"><tr><td colspan="2">' + '<p>Вы уверены, что хотите открепить пользователя <b>' + $(this).parent().next().html() + '</b> от компании?</p>' + '</td></tr><tr><td colspan="1"><a href="#" class="popupClose">отменить</a> </td><td colspan="1"><span class="formButton"><input type="submit" value="Применить"></span></td></tr></table></div>';
    show_popup(body, 'auth employee', 'Открепление пользователя');
    $('.employee').find('input[type="submit"]').bind('click', function() {
      window.location = href;
    });
    return false;

  });

  $('input[type=checkbox][name=settings\[show_rating\]]').bind('click', function(){
    if ($('.userpic .image img').attr('src') == '/images/pict_company.png'){
      var form =
        '<div class="body">'
        + '<p>Загрузите логотип компании. Компании без загруженного логотипа не могут участвовать в рейтинге.</p>'
        +'</div>';
      var title = '<span style="padding-left:0px; color:red;">Внимание!</span>';
      $form = show_popup(form, 'auth', title);
      return false;
    }
  });

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

  $('a[name=stop-active-pm]').bind('click', send_message_restrict);
  
  $('.checkbox_list input[name="dynamicform[field45][]"]').click(function() {
    var all = $(this).closest('.checkbox_list').find('input[name="dynamicform[field45][]"]:checkbox');
    if(all.filter(':checked').size() >= 3) {
      all.filter(':not(:checked)').attr('disabled', true);
    } else {
      all.filter(':disabled').attr('disabled', false);
    }
  });
});

function get_zoom_for_address($inputs) {
  zoom = 0;

  if ( $($inputs[0]).val() ) { //region_id
    zoom = 8;
  }

  if ( $($inputs[1]).val() ) { //region_node
    zoom = 10;
  }

  if ($($inputs[1]).val() ) // and :)
  if ( $('option[selected]', $inputs[1]).attr('has_children') != 1) { // <city> in region_node
    zoom = 14;
  }

  if ( is_city($($inputs[0]).val()) ) { // <city> in region_id
    zoom = 14;
  }

  if ( $($inputs[2]).val() ) { //city
    zoom = 14;
  }

  if ( $($inputs[3]).val() ) { // street
    zoom = 16;
  }

  if ( $($inputs[4]).val() ) { // house
    zoom = 17;
  }

  return zoom;
}

function bindButtons(rebind) {
  rebind == null ? rebind = false : rebind = true;

  $('button.ajax-upload').each(function() {
    var $button = $(this);
    var $input  = $button.closest('tr,fieldset').find('input[type=file]');
    var $inputlink  = $button.closest('tr,fieldset').find('input[type=text][need_colspan=1]');
    var $result  = $button.closest('tr,fieldset').find('.ajax-uploaded');
    var $help  = $button.closest('tr,fieldset').find('.field-help');
    var $thumb  = $('input[name=' + $button.attr('rel').replace('image', 'thumb') + ']');

    var check_max = function() {
      if (!$button.attr('max')) return true;

      var uploaded = $('.ajax-uploaded').find('[type=hidden]').length,
          left = parseInt($button.attr('max')) - uploaded;

      /*if (uploaded) $button.attr('filled','1');
      else $button.removeAttr('filled');*/


      if (left == 0) {
        $input.attr('disabled', true);
        $inputlink.attr('disabled', true);
        $button.attr('disabled', true);
        label = '<span style="color:red">Загружено максимум изображений</span>';
      }
      else {
        label = 'Еще можно загрузить ' + left + ' изображен' + ending(left, 'ие', 'ия', 'ий');
        $input.removeAttr('disabled');
        $inputlink.removeAttr('disabled');
        $button.removeAttr('disabled');
      }

      $help.html(label);
    }

    $button.closest('tr,fieldset').find('span[rel]:has(a)').bind('click', function() {
      var $this = $(this);

      $this.html($this.text()).removeClass('wlink');
      $this.siblings().each(function() {
        $(this).html('<a href="#" class="inner">' + $(this).text() + '</a>').addClass('wlink');
      });

      if ($this.attr('rel') == 'file') {
        $inputlink.hide();
        $input.show();
      }
      else {
        $inputlink.show();
        $input.hide();
      }

      return false;
    });

    if ($button.attr('default-type') == 'link') {
      $button.closest('tr,fieldset').find('span[rel=link]').trigger('click');
    }
    else {
      $button.closest('tr,fieldset').find('span[rel=file]').trigger('click');
    }

    var select_thumb = function() {
      $div = $(this).addClass('selected').closest('.uploaded-item');
      $div.siblings().find('img').removeClass('selected');
      $thumb.val($div.prevAll('div.uploaded-item').length);
      return false;
    };

    var append_thumb = function(file, response) {
      if (response == null) return false;

      var $div = $('<div class="uploaded-item"/>');
      $div.append(response);
      $div.find('img')
        .bind('click', select_thumb)
        .css('cursor', 'pointer')
        .attr('title', 'Сделать изображением для превью');
      $('<a/>')
        .attr('href', '#')
        .addClass('delete')
        .text('Удалить')
        .bind('click', function() {
          if (confirm('Вы действительно хотите удалить загруженный файл?')) {
            $(this).closest('.uploaded-item').remove();

            check_max();
            $button.trigger('rerate');
          }
          return false;
        })
        .appendTo($div);

      $div.find('input[type=hidden]').attr('name', $button.attr('rel') + '[]').removeAttr('id');

      $result.append($div);
      $result.find('.uploaded-item').eq($thumb.val()).find('img').trigger('click');
      check_max();
      $button.trigger('rerate');
    }

    $input.ajaxUpload({
      action: $button.attr('url'),
      name: 'file',
      onSubmit: function() {
        $button.attr('disabled', true);
      },
      onError: function(file) {
        alert('Файл "'+file+'" не удалось загрузить.')
      },
      onSuccess: append_thumb,
      onComplete: function(file, response) {
        $button.removeAttr('disabled');
        $button.trigger('ajax-loaded');
      }
    });

    $button.bind('click', function() {

      if ($button.is('[disabled]')) {
        return false;
      }

      if ($input.is(':hidden')) {
        if ($inputlink.val()) {
          $.ajax({
            url: $button.attr('url'),
            type: 'get',
            data: {url: $inputlink.val()},
            beforeSend: function() {
              $button.attr('disabled', true);
            },
            complete: function() {
              $button.removeAttr('disabled');
              $button.trigger('ajax-loaded');
            },
            error: function() {
              alert('Файл "' + $inputlink.val() + '" не удалось загрузить.');
              $inputlink.val('');
            },
            success: function(data) {
              var $data = $(data);
              if ($data.is('.success')) {
                $response = $data;
              }
              else {
                $response = $(data).find('.success:first');
              }

              if ($response.length) {
                append_thumb($inputlink.val(), $response.html());
              }
              else if($inputlink.val()) {
                alert('Файл "' + $inputlink.val() + '" не удалось загрузить.');
              }
              $inputlink.val('');
            }
          });
        }
      }
      else {
        $input.ajaxUpload('submit');
      }
      return false;
    });

    if (!rebind) {
      $button.closest('tr,fieldset').find('[type=hidden]').each(function() {
        var $hidden = $(this);
        if ($hidden.attr('loading')) return true;
        $hidden.attr('loading', true);
        if ($hidden.val()) {
          $.get($button.attr('url'), {name: $hidden.val()} , function(data) {
            append_thumb($hidden.val(), $('<div>' + data + '</div>').find('.success:first').html() || null);
            $hidden.remove();
          });
        }
      });
    }
    check_max();

  });

  var fix_button_actions = function() {
    $('.flat .buttons span').addClass('formButtonDelete').children('input').removeClass('flat_add_button').val('Убрать квартиру');
    $('.flat .buttons span:last').removeClass('formButtonDelete').children('input').addClass('flat_add_button').val('Добавить квартиру');
    
    $('.cottage .buttons span').addClass('formButtonDelete').children('input').removeClass('cottage_add_button').val('Убрать коттедж');
    $('.cottage .buttons span:last').removeClass('formButtonDelete').children('input').addClass('cottage_add_button').val('Добавить коттедж');
    
    $('.townhouse .buttons span').addClass('formButtonDelete').children('input').removeClass('townhouse_add_button').val('Убрать таунхаус');
    $('.townhouse .buttons span:last').removeClass('formButtonDelete').children('input').addClass('townhouse_add_button').val('Добавить таунхаус');
  };
  fix_button_actions();

  $('.flat .buttons span input').live('click', function() {
    if ($(this).hasClass('flat_add_button')) {
      $.get('/lot/addFlat', function(data) {
        $('.same-house').append(data);
        fix_button_actions();
      });
    } else {
      $(this).parents('.flat').remove();
    }
    return false;
  });
  
  $('.cottage .buttons span input').live('click', function() {
    var btn = $(this);
    if (btn.hasClass('cottage_add_button')) {
      $.get('/lot/addCottage', function(data) {
        btn.closest('.same-house').append(data);
        fix_button_actions();
      });
    } else {
      btn.parents('.cottage').remove();
    }
    return false;
  });
  
  $('.townhouse .buttons span input').live('click', function() {
    var btn = $(this);
    if (btn.hasClass('townhouse_add_button')) {
      $.get('/lot/addTownhouse', function(data) {
        btn.closest('.same-house').append(data);
        fix_button_actions();
      });
    } else {
      btn.parents('.townhouse').remove();
    }
    return false;
  });
}
