DROP TRIGGER IF EXISTS domus.log_err_on_insert;
CREATE TRIGGER domus.log_err_on_insert AFTER INSERT ON import_errors_log
FOR EACH ROW 
BEGIN
 UPDATE import_log SET import_log.errors = import_log.errors + 1 WHERE import_log.id = NEW.log_id;
END