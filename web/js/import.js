$(function(){
  $('div.custom-select select').sb();
  $('div.custom-select select[name=import\[type\]]').change(function (){
    $.post('/import/getHelp', { type: $(this).val() }, function(data){
      $('div[id=import-help]').html(data);
    });
  });

  $('select[name=import\[type\]]').trigger('change');
});