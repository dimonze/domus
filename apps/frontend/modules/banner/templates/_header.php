<div id="homepage_header">
  <center style="display: block;">
    <script type='text/javascript'><!--// <![CDATA[
      mesto_bind(3);
      // ]]> -->
      function fix_fake_links(){
        var mid_width   = 1002;
        var top_height  = 170;
        var all_h       = $(document).height();
        var side_width  = ($(document).width() - mid_width) / 2;
        //var side_height = all_h - top_height;
        var side_height = 1050;
        var href = $('body div#homepage_header center a:first').attr('href');

        if($('a.commercial-link').attr('class')) {

          $('a.commercial-link')
            .attr('href', href);

          $('a.commercial-link').css({
              position:    'absolute',
              display:    'block',
              'background-color': 'transparent',
              top: 0,
              left: 0,
              width: '100%',
              height: top_height + 'px'
            });

          $('a.commercial-link.side')
            .css({
              top:    top_height + 'px',
              width:  side_width + 'px',
              height: side_height + 'px'
            });

          $('a.commercial-link.side.right')
            .css({
              left: '',
              right: '0px'
            });
        }
      }
      $(function(){
        fix_fake_links();
        $(window).resize(function(){
         fix_fake_links();
        });
      });
    </script>
  </center>
  
  <!-- <a href="#" target="_blank" class="commercial-link"></a>
  <a href="#" target="_blank" class="commercial-link side"></a>
  <a href="#" target="_blank" class="commercial-link side right"></a> -->
  
</div>
