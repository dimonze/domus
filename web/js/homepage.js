$(function(){
  $(".globalMenu").click(function(){
    $(this).find(":first-child").addClass("first-active")
  });
  
  $(".globalMenu").trigger("click");
  $(".globalMenu a","#wrapper").hover(function(){
    var a = $(this).parent(), b = a.prevAll().length;
    a.addClass("active").siblings().removeClass("active").removeClass("first-active").removeClass("last-active");
    a.is(":first-child") ? a.addClass("first-active") : a.is(":last-child") && a.addClass("last-active");
    $("div.tab").hide().eq(b).show();
    $.browser.msie && ($("#wrapper").css({zoom:0}), $("#wrapper").css({zoom:1}));
  }, function(){});
  
  if (window.form_config) {
    var f = $('#nbmp_form');
    f.find('select[name^=price]').each(function() {
      var options = window.form_config[$(this).attr('name')+'[rur]'];
      for (var i in options) {
        $('<option/>')
          .attr('value', options[i])
          .text(format_number(options[i]))
          .appendTo(this);
      }
    });
  }
  
  $('select#sel_region_id').change(function(){
    var region_id = parseInt($(this).val());
    var nodes_list = $('#sel_regionnode');
    
    nodes_list.find('option:gt(0)').remove();
    if(region_id == 77) 
      nodes_list.children(0).html('Выберите метро');
    else
      nodes_list.children(0).html('Выберите город');
    
    $.get(
        '/form/regionnode',
        {"region_id":region_id},
        function(data) {
          if(!data.length) return false;
          $.each(data, function(k,v){
            var no = $('<option />');
            no.attr('value',v.text).html(v.text);
            no = no.filter(function( index ) {
              var expr = null;
              if(region_id == 77) expr = new RegExp('^(м\\.|Новая)');

              if(expr != null)
                return expr.test($(this).val());
              
              return true;
            });
            nodes_list.append(no);
          });
        },'json'
     );
  });
  $('select#sel_region_id').change();
  
  $('#nbmp_form').submit(function(e){
    var region_id = $('#sel_region_id').val();
    var link = $('#nb-nav').find('a.nb'+region_id).attr('href');

    var data = prepare_data_homepage($(this));
    var hash = prepare_hash_homepage(data);
    window.location = link+'#'+hash;
    
    return false;
  });
  
  function prepare_data_homepage($form) {
    var result = [];
    var data = serialize_form($form).split('&');
    //Clean from default values
    for (var i in data) {
      var cv = data[i].split('=');
      if(cv.length > 1 && $.inArray(urldecode(cv[1]),['Выберите%20метро','Выберите%20город','от','до']) == -1){
        result.push(data[i]);
      }
    }

    data = parse_url(result.join('&'), {}, true);
    result = '';
    for (var i in data) {
      if (typeof data[i] == 'object') {
        for (var j in data[i]) {
          result += i + '=' + data[i][j] + '&';
        }
      }
      else {
        result += i + '=' + data[i] + '&';
      }
    }

    return result.replace(/&$/, '');
  }
  
  function prepare_hash_homepage(data) {
    var hash = [];
    var splitted = data.split('&');

    for (i in splitted) {
      if (!splitted[i]) continue;

      var key = translate_field_homepage(String(splitted[i]).split('=')[0]);
      var value = translit_field_homepage(key, String(splitted[i]).split('=')[1]);

      if (key == 'type') continue;

      hash[hash.length] = key + '/' + value;
    }

    return hash.join('/');
  }
  
  function translate_field_homepage(name, reverse) {
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

  function translit_field_homepage(key, value, reverse) {
    var passed = false;
    for (i in window.translit_fields) {
      if (window.translit_fields[i] == key || window.translit_fields[i] == translate_field_homepage(key, true)) {
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
});