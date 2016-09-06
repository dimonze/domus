DROP TRIGGER IF EXISTS domus.import_order_on_update;
DELIMITER ||
CREATE TRIGGER domus.import_order_on_update AFTER UPDATE ON import_order
  FOR EACH ROW
  BEGIN
    UPDATE import_order_options SET import_order_options.status = NEW.status
    WHERE import_order_options.order_id = NEW.id;

    UPDATE lot SET lot.status = IF(NEW.status = 'active', 'active', 'not-paid')
    WHERE NEW.date_to   > NOW()
    AND   NEW.date_from <= NOW()
    AND   lot.updated_at > NEW.date_from
    AND   lot.status = IF(NEW.status = 'active', 'not-paid', 'active')
    AND   lot.user_id = NEW.user_id
    AND   lot.imported = 1
    AND   lot.type IN (SELECT type FROM import_order_options WHERE order_id = NEW.id);
  END
||
DELIMITER ;