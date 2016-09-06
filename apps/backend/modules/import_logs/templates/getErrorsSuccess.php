<?=iconv('utf-8', 'cp1251', "Номер объявления в файле;Сообщение об ошибке\n")?>
<?php while ($error = $errors->fetch()): ?>
<?= $error['internal_lot_id'] ?>;<?= iconv('utf-8', 'cp1251', str_replace(';', '.', $error['message'])) ?> 
<?php endwhile ?>