/*
 * jQuery Scrollable plugin 1.0
 *
 * http://zhekanax.ru/
 * Copyright (c) Eugeniy Belyaev
 *
 */

(function($) {

  $.fn.scrollable = function (options) {
    return $(this).each(function() {
      var $this = $(this);
      var el = this;

      if (el.settings && options == 'update') {
        debug('Updating scrollable');
        el.settings.max_scroll = el.settings.get_max_scroll($this, el.settings.direction);
        el.settings.init();
        do_scroll(0);
        return true;
      }

      if (el.settings) {
        debug('Object already scrollable');
        debug('id:' + $this.attr('id'));
        debug('class:' + $this.attr('class'));
        return true;
      }

      el.settings = $.extend({}, $.fn.scrollable.defaults, options);

      debug('Iniatilizing scrollable');
      debug(el.settings);

      el.settings.init();

      el.settings.max_scroll = el.settings.get_max_scroll(el, el.settings.direction);
      var next_interval, prev_interval;
      set_button_events();

      do_scroll(0);


      function set_button_events () {
        if (el.settings.next != null ) {
          $(el.settings.next)
            .bind('mousedown.scrollable', function(){
              next();
              next_interval = window.setInterval(
                next,
                el.settings.speed
              );
            })
            .bind('mouseup.scrollable', function(){
              window.clearInterval(next_interval);
            });
        }
        if (el.settings.prev != null ) {
          $(el.settings.prev)
            .bind('mousedown.scrollable', function(){
              prev();
              prev_interval = window.setInterval(
                prev,
                el.settings.speed
              );
            })
            .bind('mouseup.scrollable', function(){
              window.clearInterval(prev_interval);
            });
        }
      };


      function next() { do_scroll(+1) }
      function prev() { do_scroll(-1) }

      function do_scroll(direction) {
        scrl = el.settings.direction == 'v' ? $(el).scrollTop() : $(el).scrollLeft() ;
        debug('current scroll: ' + scrl + ' + '+(direction * el.settings.step)+', max: ' + el.settings.max_scroll);

        if (el.settings.max_scroll < 1) {
          debug('Nothing to scroll');
          el.settings.noscroll();
        }

        if (scrl * direction <= el.settings.max_scroll + el.settings.step) {
          if (el.settings.direction == 'v' ) {
            if (el.settings.animate) {
              $(el).stop().animate(
                { scrollTop: scrl + direction * el.settings.step },
                el.settings.speed
              );
            }
            else {
              $(el).scrollTop(scrl + direction * el.settings.step);
            }
          }
          else {
            if (el.settings.animate) {
              $(el).stop().animate(
                { scrollLeft: scrl + direction * el.settings.step },
                el.settings.speed
              );
            }
            else {
              $(el).scrollLeft(scrl + direction * el.settings.step);
            }
          }

          scrl += direction * el.settings.step;

          at_start = scrl <= 0;
          at_end = scrl >= (el.settings.max_scroll + el.settings.step);

          if (at_start || at_end) {
            if (at_start) {
              debug('at start');
              $(el.settings.prev).trigger('mouseup.scrollable');
              el.settings.atStart();
            }
            if (at_end) {
              debug('at end');
              $(el.settings.next).trigger('mouseup.scrollable');
              el.settings.atEnd();
            }
            
          }
          else {
            debug('in middle');
            el.settings.inMiddle();
          }

        }
      };

      function debug(msg) {
        if (el.settings.debug && window.console) {
          console.log(msg);
        }
      };

      return true;
    });
  };

  $.fn.scrollable.get_max_scroll = function(container, direction) {
    max_scroll = 0;
    $c = $(container);

    $c.children().filter(':visible').each(function() {
      if (direction == 'v') {
        max_scroll +=
          $(this).outerHeight()
          + parseInt($(this).css('margin-top'))
          + parseInt($(this).css('margin-bottom'));
      }
      else {
        max_scroll +=
          $(this).outerWidth()
          + parseInt($(this).css('margin-left'))
          + parseInt($(this).css('margin-right'));
      }
    });
    
    if (direction == 'v') {
      max_scroll -= $c.innerHeight();
    }
    else {
      max_scroll -= $c.innerWidth();
    }

    return max_scroll;
  };


  $.fn.scrollable.defaults = {
    get_max_scroll: $.fn.scrollable.get_max_scroll,
    init: function() { },
    noscroll: function() { },
    atStart: function() { },
    inMiddle: function() { },
    atEnd: function() { },
    debug: false,
    step: 15, //px
    speed: 100, // timeout :o)
    direction: 'h', // [h]orizontal or [v]ertical?
    next: null, // jQuery button selectors
    prev: null,
    animate: false
  };


})(jQuery);