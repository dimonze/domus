SET NAMES 'UTF8';

update `agency` set `name` = replace(name, '&nbsp;', ' ');
update `agency` set `name` = replace(name, '&quot;', '\'');

update `agency` set `address` = replace(address, '&nbsp;', ' ');
update `agency` set `address` = replace(address, '&quot;', '\'');

update `agency` set `phones` = replace(phones, '&nbsp;', ' ');
update `agency` set `phones` = replace(phones, '&quot;', '\'');

update `agency` set `description` = replace(description, '&nbsp;', ' ');
update `agency` set `description` = replace(description, '&quot;', '\'');