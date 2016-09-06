$(function() {

  function exit() {
    $resultBox.remove();
    return false;
  }
  function length(obj) {
    var i = 0;
    for (v in obj) { i++; }
    return i;
  }

  var $resultBox = $('.searchResultBox');
  var $prevBt = $resultBox.find('.scrollLeft');
  var $nextBt = $resultBox.find('.scrollRight');
  var $resultList = $resultBox.find('.resultList');

  var url = $resultBox.attr('url');
  var cache = {};
  var loading = false;
  var start_offset = parseInt($resultBox.attr('offset'));
  var current_offset = start_offset -1;
  var max_offset = 0;

  if (start_offset == 1) {
    current_offset = 2;
  }

  if (isNaN(start_offset) || start_offset <= 0) {
    return exit();
  }

  function get (offset, callback, callback_arg) {
    if (max_offset && offset > max_offset) {
      current_offset--;
      get(current_offset, callback, 0);
      return false;
    }

    if (cache[offset]) {
      callback(cache[offset], callback_arg);
    }
    else {
      if (loading) {
        return false;
      }
      loading = true;

      var from_offset = offset;
      var q_offset = from_offset;
      var limit = 5;

      if (from_offset == start_offset) {
        q_offset -= 5;
        limit = 10;
      }
      else if (from_offset < start_offset) {
        q_offset -= 4;
      }

      if (q_offset < 1) {
        q_offset = 1;
      }

      $.getJSON(url, {offset: q_offset - 1, limit: limit}, function (data) {
        loading = false;
        data = data.lots;

        if (data.length < 2 && !length(cache)) {
          return exit();
        }

        var add = 0;
        for (i in data) {
          cache[q_offset + add] = data[i];
          ++add;
        }

        if (data.length != limit) {
          max_offset = q_offset + data.length -1;
        }

        if (!length(cache) && data.length == 2) {
          get(from_offset == 2 ? 1 : 2, callback, -1);
        }
        else {
          get(from_offset, callback, callback_arg);
        }
        
      });
    }
    return true;
  }

  function show_item(html, position) {
    init_list();
    if (position == 0) {
      var add = current_offset == start_offset -1 ? +1 : 0;
      get(current_offset, show_item, -1);
      get(current_offset + 1 + add, show_item, 1);
    }
    else if (position == -1) {
      $resultList.children('li:first').html(html);
    }
    else {
      $resultList.children('li:last').html(html);
    }
    
    toggle_buttons();
    return true;
  }

  function init_list() {
    if ($resultList.children('li').length) return;
    $resultList.html('<li class="resultItem"/><li class="resultItem last"/>');
  }

  get(start_offset, show_item, 0)


  function toggle_buttons() {
    if (current_offset == 1 || (current_offset == 2 && start_offset == 1))
      $prevBt.addClass('scrollLeft-disabled');
    else
      $prevBt.removeClass('scrollLeft-disabled');

    if (max_offset && current_offset +1 + (start_offset +1 >= max_offset ? 1:0) >= max_offset)
      $nextBt.addClass('scrollRight-disabled');
    else
      $nextBt.removeClass('scrollRight-disabled');
  }

  $prevBt.bind('click', function() {
    if ($(this).is('[class*=disabled]')) return false;

    --current_offset;
    current_offset -= current_offset == start_offset ? 1 : 0;
    get(current_offset, show_item, 0);
    toggle_buttons();
  });
  $nextBt.bind('click', function() {
    if ($(this).is('[class*=disabled]')) return false;
    toggle_buttons();

    ++current_offset;
    current_offset += current_offset == start_offset ? 1 : 0;
    get(current_offset, show_item, 0);
  });

});