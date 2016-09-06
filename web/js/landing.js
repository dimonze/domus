/**
 * Скрипты для администраторской части модуля "посадочных" страниц.
 *
 * @package    domus
 * @subpackage landing
 * @author     Garin Studio
 * @version    1.0
 */

if (!window.console) console = {log: function() {}}; //IE special

jQuery(document).ready(function($) {
  var prepareNodesSelect = function(source){
    source = (source == undefined || typeof(source) == 'number') ? $(this) : source;
    if(source == undefined) return false;
    
    if(!source.closest('.nodes_select_wrapper').size()){
      source.wrap('<div class="nodes_select_wrapper" />');
    }
    var wrapper = source.closest('.nodes_select_wrapper');
    if(!wrapper.index(".nodes_select_wrapper") > 0){
      return wrapper;
    }

    //Кнопка "Удалить"
    var nodes_remove = $('<a />').attr({
      'class': 'remove_node'
      ,'href': 'javascript://'
    })
    .html("Удалить")
    .click(function(){
      var wrap_cnt = $(".nodes_select_wrapper").size();
      if(wrap_cnt > 1){
        wrapper.remove();
      }else{
        $(".nodes_select_wrapper select option:first").attr('selected','selected');
      }
      
      return false;
    });
    nodes_remove.insertAfter(source);
    
    return wrapper;
  }
  
  var addNewSelect = function(){
    var nodes_select = nodes_box.parent().find('select[binded="1"]:last');
    if(!nodes_select.size()){
        return false;
    }
    
    var id_prefix = new Date().getTime();
    var nodes_new_select = nodes_select.clone();
    nodes_new_select.attr('id', nodes_new_select.attr('id') + id_prefix);
    nodes_new_select.insertAfter(nodes_select.parent());
    return prepareNodesSelect(nodes_new_select);
  }
  
  $('#landing_select_region').change(function(){
    $("#global-loading").show();
        
    $.getJSON(build_query($('#landing_region_node').attr('source'), {'region_id': $(this).val()}), function(data) {
        $('#landing_region_node').parent().siblings('.nodes_select_wrapper').remove();
        $('#landing_region_node option:gt(0)').remove();
        
        $.each(data, function(index, value){
          var opt = $('<option />').val(value.value).text(value.text);
          $('#landing_region_node').append(opt);
        });
        $('#landing_region_node').attr('binded', 1);
        fillNodes();
        $("#global-loading").hide();
    });
  });
  $('#landing_select_region').change();
  
  $('select#landing_page_type').change(function() {
    $("#global-loading").show();
    
    var form = $(this).closest('form');
    var params = form.serializeArray();
    var url = $('.sf_admin_action_list a').attr('href') + '/new';
    var data = {
      'ajax': true,
      'type': $(this).val()
    };
    
    $.post(url, data, function(response){
      var line = $('#landing_page_params_q_text_enabled').closest('tr');
      var elems = $(response).find('#landing_page_params_q_text_enabled').closest('tr').nextAll();
      line.nextAll().remove();
      elems.insertAfter(line);
      
      $("#global-loading").hide();
    }, 'html');
  });
  
  /* "Пентаграммы" городов */
  var nodes_box = $('input[type="hidden"][id$="params_regionnode_value"]');
  if(!nodes_box.size()){
    nodes_box = $('<input />').attr({
      'id': 'params_regionnode_value',
      'type': 'hidden'
    }).insertBefore('#landing_region_node');
  }
  //Кнопка "Добавить"
  var nodes_new = $('<a />').attr({
    'href': 'javascript://',
    'id': 'nodes_new'
  })
  .html('Добавить')
  .click(function(){
    addNewSelect();
    return false;
  })
  nodes_new.insertAfter(nodes_box).wrap('<div />');
  //Переподготовка существующих списков
  nodes_box.siblings('select').each(prepareNodesSelect);
  
  var fillNodes = function(){
    //Дозаполнение
    var nodes = nodes_box.size() ? nodes_box.val().split(',') : [];
    var nodes_select = nodes_box.parent().find('select[binded="1"]:first');
    $.each(nodes, function(index,value){
      if(nodes_select.find('option:contains("' + value + '")').size()){
        var curr = (!index) ? nodes_select.parent() : addNewSelect();
        curr.find('select option:contains("' + value + '"):first').attr('selected','selected');
      }
    });
  }
});