-- delete from domus.lot_statistic
-- where new + active + deleted = 0;
-- CREATE TABLE  `domus`.`_lot_previous_status` (
-- `lot_id` INT UNSIGNED NOT NULL ,
-- `status` ENUM(  'active',  'inactive',  'restricted',  'moderate' ) NOT NULL ,
-- PRIMARY KEY (  `lot_id` )
-- ) ENGINE = INNODB COMMENT =  'DAILY';

delimiter //
drop procedure if exists domus.update_stat//
create procedure domus.update_stat (in lot_id int)
begin
  declare not_found int default 0;
  declare stat_id, current_nb_active, lot_region_id int;
  declare lot_deleted datetime;
  declare stat_type, lot_type, user_type, lot_status, lot_prev_status varchar(30) default null;
  declare cdate date default date(now());

  declare continue handler for not found set not_found = 1;

  -- select lot info
  select
      l.region_id, l.type, u.type, l.status, l.deleted_at
    into
      lot_region_id, lot_type, user_type, lot_status, lot_deleted
    from domus.lot l
    inner join domus.user u on u.id = l.user_id
    where l.id = lot_id
      and (u.deleted_at = 0  or u.deleted_at is null)
      and (u.inactive = 0 or u.inactive is null);

  -- get stat type or exit
  if lot_status in ('inactive', 'restricted') then
    set stat_type = 'deleted';
  elseif lot_deleted is not null and lot_status = 'active' then
    set stat_type = 'deleted';
  elseif lot_status = 'active' then
    set stat_type = 'new';
  end if;

  -- do update/insert
  if stat_type is not null then
    select id
      into stat_id
      from domus.lot_statistic s
      where s.stat_at = cdate and s.region_id = lot_region_id
        and s.type = lot_type and s.user_type = user_type;

    -- if exists
    if stat_id > 0 then
      if stat_type = 'new' then
        update domus.lot_statistic set new = new + 1 where id = stat_id;
      else
        update domus.lot_statistic set deleted = deleted + 1 where id = stat_id;
      end if;

    -- create new row
    else
      if stat_type = 'new' then
        insert into domus.lot_statistic (stat_at, region_id, type, user_type, active, new, deleted)
          values (cdate, lot_region_id, lot_type, user_type, 0, 1, 0);
      else
        insert into domus.lot_statistic (stat_at, region_id, type, user_type, active, new, deleted)
          values (cdate, lot_region_id, lot_type, user_type, 0, 0, 1);
      end if;

      set stat_id = last_insert_id();
    end if;

    -- check today's status changes
    select s.status into lot_prev_status from domus._lot_previous_status s where s.lot_id = lot_id;
    if lot_prev_status is not null then
      if (lot_status = 'active' and lot_prev_status in ('inactive', 'restricted')) or (lot_status in ('inactive', 'restricted') and lot_prev_status = 'active') then
        update domus.lot_statistic set new = new - 1, deleted = deleted - 1 where id = stat_id;
        DELETE FROM `domus`.`_lot_previous_status` WHERE `_lot_previous_status`.`lot_id` = lot_id LIMIT 1;
      end if;
    else
      insert into domus._lot_previous_status (`lot_id`, `status`) values (lot_id, lot_status) on duplicate key update `status` = lot_status;
    end if;
  end if;
end//


drop function if exists domus.get_nb_active_lots//
create function domus.get_nb_active_lots (region_id int, user_type varchar(15), lot_type varchar(25))
  returns int
  reads sql data
begin
  declare nb_lots integer;
  declare cdate date default date(now());

  select count(*) into nb_lots
    from domus.lot l
    left join user u on l.user_id = u.id
    where l.region_id = region_id
      and u.type = user_type
      and l.type = lot_type
      and l.status = 'active'
      and l.created_at < cdate
      and l.active_till > cdate
      and (l.deleted_at = 0 or l.deleted_at is null)
      and (u.deleted_at = 0 or u.deleted_at is null)
      and (u.inactive = 0 or u.inactive is null);

  return nb_lots;
end//

drop trigger if exists domus.lot_stat_on_insert//
create trigger domus.lot_stat_on_insert after insert on domus.lot
for each row begin
  if NEW.status = 'active' then
    call domus.update_stat(NEW.id);
  end if;
end//

drop trigger if exists domus.lot_stat_on_update//
create trigger domus.lot_stat_on_update after update on domus.lot
  for each row
begin
  if (OLD.status <> NEW.status and 'active' in (OLD.status, NEW.status))
    or (NEW.deleted_at is not null and (OLD.status not in ('inactive', 'restricted') and (OLD.deleted_at is null or OLD.deleted_at = 0))) then
    call domus.update_stat(NEW.id);
  end if;
end//

drop event if exists domus.init_daily_stat//
create event domus.init_daily_stat
  on schedule every 1 day starts '2011-09-12 00:00:00'
  on completion preserve
  comment 'set nb active lots for today'
  do
begin
  truncate domus._lot_previous_status;
  insert HIGH_PRIORITY ignore
    into domus.lot_statistic (stat_at, region_id, type, user_type, new, deleted, active)
    select date(now()), region_id, type, user_type, 0, 0,
            domus.get_nb_active_lots(region_id, user_type, type) active
    from domus.lot_statistic
    where stat_at = subdate(date(now()), interval 1 day)
    having active > 0;
end//

