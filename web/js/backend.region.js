$(function() {

  var base_url = String(location.href).replace(/#.*$/, '') + '/';
  $('#tree').tree({
    data: {
      type: 'json',
      url: base_url + 'tree.json',
      async: true,
      async_data: function (node) {
        if ($(node).attr('type') == 'region') {
          return { region_id: $(node).attr('region_id') };
        }
        else if ($(node).attr('id')) {
          return { id: $(node).attr('id') };
        }
        else {
          return {};
        }
      }
    },
    
    ui: {
      theme_path: '/css/jquery.tree_component/',
      theme_name: 'default',
      context: [
        {
          id      : "create",
          label   : "Добавить",
          icon    : "create.png",
          visible : function (node, tree) { if(node.length != 1) return false; return tree.check("creatable", node); },
          action  : function (node, tree) { tree.create(false, tree.get_node(node)); }
        },
        {
          id      : "upload",
          label   : "Загрузить список",
          icon    : "list.png",
          visible : function (node, tree) { if(node.length != 1) return false; return tree.check("creatable", node); },
          action  : function (node, tree_obj) {
            tree_obj.lock(true);
            
            var form =
              '<form action="#"><fieldset style="margin: 0">'+
                '<input type="hidden" name="parent_id" value="'+$(node).attr('id')+'" />' +
                '<input type="hidden" name="parent_region_id" value="'+$(node).attr('region_id')+'" />' +
                '<legend><span class="prependClose">Загрузить список</span></legend>' +
                '<div><p>Формат: '+$('.format_rule').text()+'.<br />По 1 объекту в строку.</p></div>' +
                '<div><textarea name="data" style="width: 88%; height: 12em;"></textarea></div>' +
                '<div><input type="submit" value="Добавить" class="popupSubmit send" /></div>' +
              '</fieldset></form>';
            show_popup(form, 'auth');
            $('.popupClose').bind('click', function() {
              tree_obj.lock(false);
            });

            $('.popupWrap form').bind('submit', function() {
              if (String($('[name=data]', this).val()).replace(/\s+/) == '') {
                return false;
              }
              $.ajax({
                url: base_url + 'importnodes',
                type: 'post',
                dataType: 'json',
                data: $(this).serialize(),
                success: function(data) {
                  if (data.success) {
                    tree_obj.lock(false);
                    tree_obj.close_branch(node);
                    $(node).attr("class","closed");
                    $(node).find('ul:first').remove();
                    tree_obj.open_branch(node);
                  }
                  else {
                    alert(data.error || 'Произошла ошибка');
                  }
                  
                  $('.popupClose').trigger('click');
                },
                error: function() {
                  alert('Произошла ошибка');
                  $('.popupClose').trigger('click');
                }
              });
              
              return false;
            });
          }
        },
        "separator",
        {
          id      : "rename",
          label   : "Переименовать",
          icon    : "rename.png",
          visible : function (node, tree) { if(node.length != 1) return false; return tree.check("renameable", node); },
          action  : function (node, tree) { tree.rename(); }
        },
        {
          id      : "delete",
          label   : "Удалить",
          icon    : "remove.png",
          visible : function (node, tree) { var ok = true; $.each(node, function () { if(tree.check("deletable", this) == false) ok = false; return false; }); return ok; },
          action  : function (node, tree) { $.each(node, function () { tree.remove(this); }); }
        },
        {
          id      : "street",
          label   : "Список улиц",
          icon    : "list.png",
          visible : function (node, tree) {
            if(node.length != 1) {
              return false;
            }
            return $(node).is('[id].leaf') ||
                   String($(node).filter('[region_id]').find('a:first').text()).indexOf('г. ') == 0;
          },
          action  : function (node, tree) {
            $.get(
              base_url + 'street',
              {
                id: $(node).attr('id') || '',
                region_id: $(node).attr('region_id') || ''
              },
              function(data) {
                show_popup(data, 'reg');
              }
            );
          }
        },
        {
          id      : "gmap_center",
          label   : "Центр на карте",
          icon    : "list.png",
          visible : function (node, tree) {
            return $(node).filter('[region_id]').attr('type') == 'region';
          },
          action  : function (node, tree) {
            $.get(
              base_url + 'gmapcenter',
              {
                id: $(node).attr('region_id')
              },
              function(data) {
                show_popup(data, 'reg');
              }
            );
          }
        },
        {
          id      : "seo-text",
          label   : "SEO-текст",
          icon    : "rename.png",
          visible : function (node, tree) {
            return $(node).filter('[region_id]').attr('type') == 'region';
          },
          action  : function (node, tree) {
            $.get(
              base_url + 'seotext',
              {
                id: $(node).attr('region_id')
              },
              function(data) {
                show_popup(data, 'reg');
              }
            );
          }
        },
        {
          id      : "seo-text-rayon",
          label   : "SEO-текст для районов",
          icon    : "rename.png",
          visible : function (node, tree) {
            return $(node).filter('[region_id]').attr('type') == 'region';
          },
          action  : function (node, tree) {
            $.get(
              base_url + 'rayontext',
              {
                id: $(node).attr('region_id')
              },
              function(data) {
                show_popup(data, 'reg');
              }
            );
          }
        },
        {
          id      : "seo-text-shosse",
          label   : "SEO-текст для шоссе",
          icon    : "rename.png",
          visible : function (node, tree) {
            return $(node).filter('[region_id]').attr('type') == 'region';
          },
          action  : function (node, tree) {
            $.get(
              base_url + 'shossetext',
              {
                id: $(node).attr('region_id')
              },
              function(data) {
                show_popup(data, 'reg');
              }
            );
          }
        }
      ]
    },

    rules: {
        multiple    : true,
        createat    : 'top',
        type_attr   : 'type',
        clickable   : 'all',
        renameable  : 'all',
        deletable   : 'all',
        creatable   : 'all',
        draggable   : 'all',
        dragrules   : [
          'regionnode inside *',
          'region before region',
          'region after region'],
        drag_copy   : false
    },

    callback: {
      beforerename: function(node, lang ,tree_obj) {
        var $node = $(node);
        if ($node.attr('real_name')) {
          $node.attr('old_name', $node.find('a:first').text());
          $node.find('a:first').text($node.attr('real_name'));
        }
        return true;
      },
      onrename: function(node, lang, tree_obj, rollback) {
        var $node = $(node);

        if ($node.find('a:first').text() != $node.attr('real_name')) {
          tree_obj.lock(true);
          $.ajax({
            url: base_url + 'rename',
            type: 'post',
            dataType: 'json',
            data: {
              id: $node.attr('id') || '',
              region_id: $node.attr('region_id') || '',
              parent_id: $node.parents('li:first').attr('id') || '',
              parent_region_id: $node.parents('li:first').attr('region_id') || '',
              name: $node.find('a:first').text()
            },
            success: function(data) {
              if (data.success) {
                $node.find('a:first').text(data.name);
                if (data.attributes) {
                  for (a in data.attributes) {
                    $node.attr(a, data.attributes[a]);
                  }
                }
                tree_obj.refresh(node);
              }
              else {
                alert(data.error || 'Произошла ошибка');
                if ($node.attr('old_name')) {
                  $node.find('a:first').text($node.attr('old_name'));
                }
                else {
                  $.tree_rollback(rollback);
                }
              }
              tree_obj.lock(false);
            },
            error: function() {
              alert('Произошла ошибка');
              if ($node.attr('old_name')) {
                $node.find('a:first').text($node.attr('old_name'));
              }
              else {
                $.tree_rollback(rollback);
              }
              tree_obj.lock(false);
            }
          });
        }
        else if ($node.attr('old_name')) {
          $node.find('a:first').text($node.attr('old_name'));
        }

        return true;
      },
      onmove: function(node, ref_node, type, tree_obj, rollback) {
        var $node = $(node);
        tree_obj.lock(true);
        $.ajax({
          url: base_url + 'move',
          type: 'post',
          dataType: 'json',
          data: {
            id: $node.attr('id') || '',
            region_id: $node.attr('region_id') || '',
            parent_id: $node.parents('li:first').attr('id') || '',
            parent_region_id: $node.parents('li:first').attr('region_id') || '',
            type: type,
            ref_region_id: $(ref_node).attr('region_id') || ''
          },
          success: function(data) {
            if (data.success) {
              if (data.attributes) {
                for (a in data.attributes) {
                  $node.attr(a, data.attributes[a]);
                }
              }
              tree_obj.refresh(node);
            }
            else {
              alert(data.error || 'Произошла ошибка');
              $.tree_rollback(rollback);
            }

            tree_obj.lock(false);
          },
          error: function() {
            alert('Произошла ошибка');
            $.tree_rollback(rollback);
            tree_obj.lock(false);
          }
        });

        return true;
      },
      ondelete: function(node, tree_obj, rollback) {
        var $node = $(node);

        var agreed = $node.attr('region_id') ?
            confirm('Вы действительно хотите удалить целый регион? Это может привести к пропаданию объявлений на сайте.') && confirm('И все же?'):
            confirm('Вы действительно хотите удалить объект?') ;

        if (agreed) {
          tree_obj.lock(true);
          $.ajax({
            url: base_url + 'delete',
            type: 'post',
            dataType: 'json',
            data: {
              id: $node.attr('id') || '',
              region_id: $node.attr('region_id') || ''
            },
            success: function(data) {
              if (!data.success) {
                alert(data.error || 'Произошла ошибка');
                $.tree_rollback(rollback);
              }

              tree_obj.lock(false);
            },
            error: function() {
              alert('Произошла ошибка');
              $.tree_rollback(rollback);
              tree_obj.lock(false);
            }
          });

          return true;
        }
        else {
          $.tree_rollback(rollback);
          return false;
        }
      }
    }
  });

  $('body').bind('popup_load', function(e, $popup) {

    $popup.find('#street-list span[real_name]').bind('click', function() {
      $(this)
        .prev().removeAttr('checked').end()
        .after(
          $('<input/>')
            .attr('name', 'street[name]['+$(this).closest('li').attr('rel')+']')
            .addClass('text')
            .val($(this).attr('real_name'))
        )
        .remove();
    });

    $popup.find('.select-all').bind('click', function() {
      $popup.find('#street-list :checkbox').attr('checked', true);
      return false;
    });
    $popup.find('.select-none').bind('click', function() {
      $popup.find('#street-list :checkbox').removeAttr('checked');
      return false;
    });

  });
  
  var region_ids_list = $('.sf_admin_form_field_region_list');
  var main_region_id  = $('.sf_admin_form_field_main_region_id select');
  var parts = main_region_id.attr('name') && main_region_id.attr('name').split('[');
  parts && parts[0] != 'questionnaire' && main_region_id.after('<input type="hidden" name="' + parts[0] + '[region_list][]" id="for_disabled_region">');
  var fdr = $('#for_disabled_region');

  region_ids_list
    .find('input[value="' + main_region_id.val() + '"]')
    .attr('checked', 1)
    .attr('disabled', 1);

  fdr.val(main_region_id.val());

  main_region_id.change(function(){
    region_ids_list
      .find('input[type="checkbox"]')
      .attr('disabled', 0);

    region_ids_list
      .find('input[value="' + $(this).val() + '"]')
      .attr('checked', 1)
      .attr('disabled', 1);

    fdr.val($(this).val());
  });
});