SET NAMES 'UTF8';
-- #18410
INSERT INTO `region` (`id`, `name`, `position`, `latitude`, `longitude`, `zoom`, `description`, `in_menu`, `seotext`, `rajontext`, `shossetext`) VALUES
(90, 'Крым', 37, 45.3078004, 34.5630057, 9, NULL, 1, NULL, NULL, NULL);
-- #18423
INSERT INTO `regionnode` (`name`, `region_id`, `socr`, `parent`, `has_children`, `has_street`, `list`, `description`, `latitude`, `longitude`) VALUES
('Бахчисарайский', 90, 'р-н', NULL, 1, 0, 1, NULL, NULL, NULL),
('Белогорский', 90, 'р-н', NULL, 1, 0, 1, NULL, NULL, NULL),
('Джанкойский', 90, 'р-н', NULL, 1, 0, 1, NULL, NULL, NULL),
('Кировский', 90, 'р-н', NULL, 1, 0, 1, NULL, NULL, NULL),
('Красногвардейский', 90, 'р-н', NULL, 1, 0, 1, NULL, NULL, NULL),
('Красноперекопский', 90, 'р-н', NULL, 1, 0, 1, NULL, NULL, NULL),
('Ленинский', 90, 'р-н', NULL, 1, 0, 1, NULL, NULL, NULL),
('Нижнегорский', 90, 'р-н', NULL, 1, 0, 1, NULL, NULL, NULL),
('Первомайский', 90, 'р-н', NULL, 1, 0, 1, NULL, NULL, NULL),
('Раздольненский', 90, 'р-н', NULL, 1, 0, 1, NULL, NULL, NULL),
('Сакский', 90, 'р-н', NULL, 1, 0, 1, NULL, NULL, NULL),
('Симферопольский', 90, 'р-н', NULL, 1, 0, 1, NULL, NULL, NULL),
('Советский', 90, 'р-н', NULL, 1, 0, 1, NULL, NULL, NULL),
('Черноморский', 90, 'р-н', NULL, 1, 0, 1, NULL, NULL, NULL),
('Алуштинский', 90, 'горсовет', NULL, 1, 0, 1, NULL, NULL, NULL),
('Армянский', 90, 'горсовет', NULL, 1, 0, 1, NULL, NULL, NULL),
('Евпаторийский', 90, 'горсовет', NULL, 1, 0, 1, NULL, NULL, NULL),
('Симферопольский', 90, 'горсовет', NULL, 1, 0, 1, NULL, NULL, NULL),
('Судакский', 90, 'горсовет', NULL, 1, 0, 1, NULL, NULL, NULL),
('Феодосийский', 90, 'горсовет', NULL, 1, 0, 1, NULL, NULL, NULL),
('Севастополь', 90, 'г', NULL, 0, 1, 1, NULL, NULL, NULL),
('Ялтинский', 90, 'горсовет', NULL, 1, 0, 1, NULL, NULL, NULL),
('Севастопольский', 90, 'горсовет', NULL, 1, 0, 1, NULL, NULL, NULL),
('Бахчисарай', 90, 'г', NULL, 0, 1, 1, NULL, NULL, NULL),
('Белогорск', 90, 'г', NULL, 0, 1, 1, NULL, NULL, NULL),
('Джанкой', 90, 'г', NULL, 0, 1, 1, NULL, NULL, NULL),
('Кировское', 90, 'пгт', NULL, 0, 1, 1, NULL, NULL, NULL),
('Красногвардейское', 90, 'пгт', NULL, 0, 1, 1, NULL, NULL, NULL),
('Красноперекопск', 90, 'г', NULL, 0, 1, 1, NULL, NULL, NULL),
('Ленино', 90, 'пгт', NULL, 0, 1, 1, NULL, NULL, NULL),
('Нижнегорский', 90, 'пгт', NULL, 0, 1, 1, NULL, NULL, NULL),
('Первомайское', 90, 'пгт', NULL, 0, 1, 1, NULL, NULL, NULL),
('Раздольное', 90, 'пгт', NULL, 0, 1, 1, NULL, NULL, NULL),
('Саки', 90, 'г', NULL, 0, 1, 1, NULL, NULL, NULL),
('Симферополь', 90, 'г', NULL, 0, 1, 1, NULL, NULL, NULL),
('Советский', 90, 'пгт', NULL, 0, 1, 1, NULL, NULL, NULL),
('Черноморское', 90, 'пгт', NULL, 0, 1, 1, NULL, NULL, NULL),
('Алушта', 90, 'г', NULL, 0, 1, 1, NULL, NULL, NULL),
('Армянск', 90, 'г', NULL, 0, 1, 1, NULL, NULL, NULL),
('Евпатория', 90, 'г', NULL, 0, 1, 1, NULL, NULL, NULL),
('Керчь', 90, 'г', NULL, 0, 1, 1, NULL, NULL, NULL),
('Судак', 90, 'г', NULL, 0, 1, 1, NULL, NULL, NULL),
('Феодосия', 90, 'г', NULL, 0, 1, 1, NULL, NULL, NULL),
('Ялта', 90, 'г', NULL, 0, 1, 1, NULL, NULL, NULL),
('Симферопольское', 90, 'ш', NULL, 0, 0, 1, NULL, NULL, NULL),
('Херсонское', 90, 'ш', NULL, 0, 0, 1, NULL, NULL, NULL),
('Ялтинское', 90, 'ш', NULL, 0, 0, 1, NULL, NULL, NULL),
('Судакское', 90, 'ш', NULL, 0, 0, 1, NULL, NULL, NULL),
('Обьездное', 90, 'ш', NULL, 0, 0, 1, NULL, NULL, NULL),
('Феодосийское', 90, 'ш', NULL, 0, 0, 1, NULL, NULL, NULL),
('Черноморское', 90, 'ш', NULL, 0, 0, 1, NULL, NULL, NULL),
('Вокзальное', 90, 'ш', NULL, 0, 0, 1, NULL, NULL, NULL),
('Героев Сталинграда', 90, 'ш', NULL, 0, 0, 1, NULL, NULL, NULL),
('Индустриальное', 90, 'ш', NULL, 0, 0, 1, NULL, NULL, NULL),
('Советское', 90, 'ш', NULL, 0, 0, 1, NULL, NULL, NULL),
('Евпаторийское', 90, 'ш', NULL, 0, 0, 1, NULL, NULL, NULL),
('Красноперекопское', 90, 'ш', NULL, 0, 0, 1, NULL, NULL, NULL),
('Михайловское', 90, 'ш', NULL, 0, 0, 1, NULL, NULL, NULL),
('Новоселовское', 90, 'ш', NULL, 0, 0, 1, NULL, NULL, NULL),
('Лабораторное', 90, 'ш', NULL, 0, 0, 1, NULL, NULL, NULL),
('Фиолентовское', 90, 'ш', NULL, 0, 0, 1, NULL, NULL, NULL),
('Камышовое', 90, 'ш', NULL, 0, 0, 1, NULL, NULL, NULL),
('Московское', 90, 'ш', NULL, 0, 0, 1, NULL, NULL, NULL),
('Восточное', 90, 'ш', NULL, 0, 0, 1, NULL, NULL, NULL),
('Керченское', 90, 'ш', NULL, 0, 0, 1, NULL, NULL, NULL),
('Алупкинское', 90, 'ш', NULL, 0, 0, 1, NULL, NULL, NULL),
('Бахчисарайское', 90, 'ш', NULL, 0, 0, 1, NULL, NULL, NULL),
('Дражинского', 90, 'ш', NULL, 0, 0, 1, NULL, NULL, NULL),
('Иссарское', 90, 'ш', NULL, 0, 0, 1, NULL, NULL, NULL),
('Севастопольское', 90, 'ш', NULL, 0, 0, 1, NULL, NULL, NULL),
('Южнобережное', 90, 'ш', NULL, 0, 0, 1, NULL, NULL, NULL);