$(document).ready(function() {
  $('.callback-form input[type="text"]').mask("+7 (999) 999-9999",{placeholder:"_"});
  $('#phoneForm').live('submit', function(e){
    if ($(this).serialize('phone') == "phone="){
      e.preventDefault();
      alert('Заполните номер телефона!')
    }
    else {
      $.post('/form/consultByPhone', $('#phoneForm').serialize(), function(data){
        console.log(data);
        console.log(data.send);
        if (data.send != undefined) {
          $('input[name=phone]').hide();
          $('input[type=submit]').hide();
          $('#consult_success').show();
        }
      }, 'json');
    }
    return false;
  });
});