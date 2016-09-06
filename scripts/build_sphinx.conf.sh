#!/bin/bash
cd "`dirname $0`/.."
cdir=`pwd`
project=`basename $cdir`

crontab="/etc/cron.d/sphinxsearch"
config="/etc/sphinxsearch/sphinx.conf"
indexer="/usr/bin/indexer"

if [[ "domus" == "$project" ]]; then
  daemon_listen="localhost:9312"
else
  daemon_listen="192.168.1.2:9312"
fi

my_config="config/databases.yml"
my_user=`grep username $my_config | head -n1 | awk -F': *' '{ print $2 }'`
my_password=`grep password $my_config | head -n1 | awk -F': *' '{ print $2 }'`
my_host=`grep host $my_config | head -n1 | awk -F'host=' '{ print $2 }'`
my_db=`grep dbname $my_config | head -n1 | awk -F'dbname=|;' '{print $2}'`

delta_query=" AND updated_at >= (SELECT last_updated_at FROM sph_counter WHERE type = \"%type%\")"


function header
{
  echo   "#######################"
  printf "# %-18s ##\n" $1
  echo   "#######################"
}

function print_base
{
  echo "source source_tpl
{
  type        = mysql
  sql_host    = $my_host
  sql_user    = $my_user
  sql_pass    = $my_password
  sql_db      = $my_db
  sql_port    = 3306
}

indexer
{
  mem_limit = 512M
}

searchd
{
  listen          = $daemon_listen
  pid_file        = /var/run/searchd.pid
  max_children		= 30
  client_timeout  = 30
  log             = /var/log/sphinxsearch/searchd.log
  max_matches     = 10000
  compat_sphinxql_magics = 0
  binlog_path     = # disable logging

  # received zero-sized searchd response:
  dist_threads    = 8
  workers         = threads
  preopen_indexes = 1
  read_timeout    = 10
  seamless_rotate = 1
  unlink_old      = 1
}
"
}

function print_lots
{
  header "lots"

  types="apartament-sale apartament-rent house-sale house-rent commercial-sale commercial-rent new_building-sale cottage-sale"
  query="
    SET @s = CONCAT('SELECT
      l.id,
      l.id as lot_id,
      l.region_id,
      l.description,
      l.address1,
      l.address2,
      l.address_info,
      l.status+0 as status,
      l.type+0 as type,
      l.rating,
      l.latitude,
      l.longitude,
      l.slug,
      u.type as user_type,
      l.user_id,
      l.organization_link,
      l.auto_description,
      l.price*l.exchange as formated_price,
      if(l.images=\"\" || l.images IS NULL, 0, l.images) as images,
      if(l.thumb=\"\" || l.thumb IS NULL, 0, l.thumb) as thumb,
      if(u.company_name=\"\", null, u.company_name) as company_name,
      UNIX_TIMESTAMP(l.created_at) as created_at_ts,
      UNIX_TIMESTAMP(l.updated_at) as updated_at_ts,
      UNIX_TIMESTAMP(l.active_till) as active_till_ts,
      if ((SELECT value FROM lot_info WHERE field_id = 68 and lot_id = l.id) = \"сутки\", l.price*l.exchange*30, l.price*l.exchange) as price_apartament_day,
      if ((SELECT value FROM lot_info WHERE field_id = 68 and lot_id = l.id) = \"месяц\", l.price*l.exchange/30, l.price*l.exchange) as price_apartament_month,
      if ((SELECT value FROM lot_info WHERE field_id = 69 and lot_id = l.id) = \"месяц\", l.price*l.exchange*12, l.price*l.exchange) as price_year,
      if ((SELECT value FROM lot_info WHERE field_id = 69 AND lot_id = l.id ) = \"год\", l.price*l.exchange/12, l.price*l.exchange) as price_month,
    ',
    (
      SELECT CAST(GROUP_CONCAT('max(if(i.field_id=', field_id, ', if(i.field_id IN (70,71,96,97,100,101,104,105), SUBSTR(i.value, 4)*l.exchange, i.value), null)) as f', field_id) as char)
      FROM form_item WHERE type = '%type%'
      GROUP BY type
    ),
    '
      FROM lot_info i
      LEFT JOIN lot l ON l.id = i.lot_id
      LEFT JOIN user u ON u.id = l.user_id
      WHERE l.type = \"%type%\" AND l.status = \"active\"
        AND (l.deleted_at = 0 OR l.deleted_at IS NULL) AND (u.deleted_at = 0 OR u.deleted_at IS NULL) AND (u.inactive = 0 OR u.inactive IS NULL)
        %delta_query%
      GROUP BY l.id
    ');"
  kill_query="
    SELECT l.id FROM lot l
    LEFT JOIN user u ON u.id = l.user_id
    where l.type = \"%type%\" AND (l.status <> \"active\" OR l.deleted_at IS NOT NULL OR u.deleted_at IS NOT NULL OR u.inactive = 1) %delta_query%;"

  lot_attrs[1]="
  sql_attr_float        = formated_price
  sql_attr_uint         = f1
  sql_attr_uint         = f2
  sql_attr_uint         = f3
  sql_attr_uint         = f4
  sql_field_string      = f54
  sql_field_string      = auto_description
  sql_attr_uint         = f5"
  lot_attrs[2]="
  sql_attr_float        = formated_price
  sql_attr_float        = price_apartament_month
  sql_attr_float        = price_apartament_day
  sql_attr_uint         = f1
  sql_attr_uint         = f3
  sql_attr_uint         = f4
  sql_field_string       = f55
  sql_field_string      = auto_description
  sql_field_string       = f68"
  lot_attrs[3]="
  sql_attr_float        = formated_price
  sql_field_string      = auto_description
  sql_attr_uint         = f26
  sql_attr_uint         = f27
  sql_attr_uint         = f5
  sql_attr_uint         = f4
  sql_field_string      = f64
  sql_attr_uint         = f35"
  lot_attrs[4]="
  sql_attr_float        = formated_price
  sql_field_string      = auto_description
  sql_attr_uint         = f26
  sql_field_string      = f64
  sql_attr_uint         = f27
  sql_attr_uint         = f5
  sql_attr_uint         = f4
  sql_attr_uint         = f35"
  lot_attrs[5]="
  sql_attr_float         = formated_price
  sql_field_string      = auto_description
  sql_attr_uint          = f46
  sql_field_string       = f45
  sql_attr_uint          = f47
  sql_attr_uint          = f5"
  lot_attrs[6]="
  sql_attr_float        = formated_price
  sql_field_string      = auto_description
  sql_attr_float        = price_month
  sql_attr_float        = price_year
  sql_attr_uint         = f46
  sql_attr_uint         = f47
  sql_field_string      = f45
  sql_attr_uint         = f5
  sql_field_string      = f69"
  lot_attrs[7]="
  sql_field_string      = auto_description
  sql_attr_float        = f70
  sql_attr_float        = f71
  sql_attr_float        = f72
  sql_attr_float        = f73
  sql_attr_uint         = f75
  sql_field_string      = f91"
  lot_attrs[8]="
  sql_field_string      = auto_description
  sql_attr_float        = f92
  sql_attr_uint         = f93
  sql_attr_float        = f94
  sql_attr_float        = f95
  sql_attr_float        = f96
  sql_attr_float        = f97
  sql_attr_float        = f98
  sql_attr_float        = f99
  sql_attr_float        = f100
  sql_attr_float        = f101
  sql_attr_float        = f102
  sql_attr_float        = f103
  sql_attr_float        = f104
  sql_attr_float        = f105
  sql_field_string      = f106
  sql_field_string      = f107
  sql_field_string      = address_info"

  i=1
  for type in $types; do
    for delta in "0" "1"; do
      if [[ $delta == "0" ]]; then
        name="${type//-/_}_main"
        delta_q=""
        pre_q="sql_query_pre         = REPLACE INTO sph_counter SELECT '%type%', MAX(updated_at) FROM lot where type = '%type%'"
        kill_q=""
      else
        name="${type//-/_}_delta"
        delta_q="$delta_query"
        pre_q=""
        kill_q="sql_query_killlist    = `echo -n ${kill_query//%delta_query%/$delta_q}`"
      fi

      echo -n "
source $name : source_tpl
{
  sql_query_pre         = SET NAMES utf8
  sql_query_pre         = SET group_concat_max_len=4096
  sql_query_pre         = "

      q=${query//%delta_query%/$delta_q}
      q=${q//%type%/$type}
      echo -n $q

      echo "
  sql_query_pre         = PREPARE stmt FROM @s
  ${pre_q//%type%/$type}
  sql_query             = EXECUTE stmt

  ${kill_q//%type%/$type}

  sql_field_string      = address1
  sql_field_string      = address2

  sql_field_string      = slug
  sql_attr_uint         = lot_id
  sql_attr_uint         = region_id
  sql_attr_uint         = rating
  sql_attr_uint         = type
  sql_attr_uint         = status
  sql_attr_float        = latitude
  sql_attr_float        = longitude
  sql_attr_string       = company_name
  sql_attr_string       = images
  sql_attr_string       = thumb
  sql_attr_uint         = user_id
  sql_attr_string       = user_type
  sql_attr_timestamp    = created_at_ts
  sql_attr_timestamp    = updated_at_ts
  sql_attr_timestamp    = active_till_ts
${lot_attrs[i]}
}
"

    echo "
index $name
{
  docinfo               = extern
  charset_type          = utf-8
  ignore_chars          = U+2D, U+2F
  source                = $name
  enable_star           = 1
  morphology            = stem_enru
  path                  = /var/lib/sphinxsearch/data/$name
}
"
    done
    let i++
  done
}


function print_posts
{
  header "posts"


  echo -n "
source posts : source_tpl
{
  sql_query_pre         = SET NAMES utf8
  sql_query             = SELECT id post_id, title, post_type, GROUP_CONCAT(post_region.region_id) region_ids, UNIX_TIMESTAMP(created_at) as created_at_ts, UNIX_TIMESTAMP(updated_at) as updated_at_ts FROM post LEFT JOIN post_region ON post_region.post_id = post.id WHERE status = 'publish' AND (deleted_at IS NULL OR deleted_at = 0) GROUP BY post.id

  sql_field_string      = title
  sql_attr_uint         = post_id
  sql_attr_string       = post_type
  sql_attr_multi        = uint region_ids from field;
  sql_attr_timestamp    = created_at_ts
  sql_attr_timestamp    = updated_at_ts
}
"

  echo "
index posts
{
  docinfo               = extern
  charset_type          = utf-8
  ignore_chars          = U+2D, U+2F
  source                = posts
  enable_star           = 1
  morphology            = stem_enru
  path                  = /var/lib/sphinxsearch/data/posts
}
"



  types="news article analytics events expert_article author_article qa"

  i=1
  for type in $types; do
    for delta in "0" "1"; do
      if [[ $delta == "0" ]]; then
        name="${type//-/_}_main"
        delta_q=""
        pre_q="sql_query_pre         = REPLACE INTO sph_counter SELECT '%type%', MAX(updated_at) FROM post WHERE post_type = '%type%'"
      else
        name="${type//-/_}_delta"
        delta_q="$delta_query"
        pre_q=""
      fi

      echo -n "
source $name : source_tpl
{
  sql_query_pre         = SET NAMES utf8
  ${pre_q//%type%/$type}
  sql_query             = SELECT id, title, post_text, lid FROM post WHERE status = 'publish' AND post_type = '$type' AND (deleted_at IS NULL OR deleted_at = 0) ${delta_q//%type%/$type}
}
"

    echo "
index $name
{
  docinfo               = extern
  charset_type          = utf-8
  ignore_chars          = U+2D, U+2F
  source                = $name
  morphology            = stem_enru
  enable_star           = 1
  min_prefix_len        = 1
  path                  = /var/lib/sphinxsearch/data/$name
}
"
    done
    let i++
  done
}

function print_blog
{
  header "blog"

  echo "
source blog_main: source_tpl
{
  sql_query_pre        = SET NAMES utf8
  sql_query_pre        = REPLACE INTO sph_counter SELECT 'blog', MAX(updated_at) FROM blog_post
  sql_query            = SELECT bp.id, bp.title, bp.body, bp.lid FROM blog_post bp LEFT JOIN blog b ON bp.blog_id = b.id WHERE bp.status = 'publish' AND (bp.deleted IS NULL OR bp.deleted = 0) AND (b.deleted IS NULL OR b.deleted = 0)
}

index blog_main
{
  docinfo               = extern
  charset_type          = utf-8
  ignore_chars          = U+2D, U+2F
  source                = blog_main
  morphology            = stem_enru
  enable_star           = 1
  min_prefix_len        = 1
  path                  = /var/lib/sphinxsearch/data/blog_main
}

source blog_delta: source_tpl
{
  sql_query_pre        = SET NAMES utf8
  sql_query            = SELECT bp.id, bp.title, bp.body, bp.lid FROM blog_post bp LEFT JOIN blog b ON bp.blog_id = b.id WHERE bp.status = 'publish' AND (bp.deleted IS NULL OR bp.deleted = 0) AND (b.deleted IS NULL OR b.deleted = 0) AND bp.updated_at >= (SELECT last_updated_at FROM sph_counter WHERE type = 'blog')
}

index blog_delta
{
  docinfo               = extern
  charset_type          = utf-8
  ignore_chars          = U+2D, U+2F
  source                = blog_delta
  morphology            = stem_enru
  enable_star           = 1
  min_prefix_len        = 1
  path                  = /var/lib/sphinxsearch/data/blog_delta
}
"
}

function print_streets
{
  header "streets"

  echo "
source streets : source_tpl
{
  sql_query_pre         = SET NAMES utf8
  sql_query_pre         = SET @row_id=0
  sql_query	            = SELECT @row_id:=@row_id+1 as street_id, r.region_id, r.list, r.name as regionnode_name, s.regionnode_id, s.name, s.name as street, s.socr, r.socr as regionnode_socr, radians(s.latitude) as latitude, radians(s.longitude) as longitude FROM street s LEFT JOIN regionnode r ON r.id = s.regionnode_id

  sql_attr_float        = latitude
  sql_attr_float        = longitude
  sql_attr_uint         = regionnode_id
  sql_attr_uint         = region_id
  sql_field_string      = socr
  sql_attr_bool         = list
  sql_field_string      = street
  sql_field_string      = regionnode_name
  sql_field_string      = regionnode_socr
}

index streets
{
  docinfo               = extern
  charset_type          = utf-8
  source                = streets
  morphology            = stem_enru
  enable_star           = 1
  min_prefix_len        = 1
  path                  = /var/lib/sphinxsearch/data/streets
}"
}


function print_landing_pages
{
  header "landing_pages"

  echo "
source landing_pages : source_tpl
{
  sql_query_pre         = SET NAMES utf8
  sql_query             = SELECT id, id as landing_id, if(url='','root',url) as url, h1, title, description, keywords, seo_text, type, params, query, md5(params) as hash, lot_title_prefix, region_id, radians(latitude) as latitude, radians(longitude) as longitude FROM landing_page

  sql_attr_uint         = landing_id
  sql_field_string      = url
  sql_field_string      = hash
  sql_attr_uint         = region_id
  sql_field_string      = params
  sql_field_string      = type
  sql_attr_string       = h1
  sql_attr_string       = title
  sql_attr_string       = description
  sql_attr_string       = keywords
  sql_attr_string       = seo_text
  sql_attr_string       = query
  sql_attr_string       = lot_title_prefix
  sql_attr_float        = latitude
  sql_attr_float        = longitude
}

index landing_pages
{
  docinfo               = extern
  charset_type          = utf-8
  blend_chars           = U+3A, U+3B
  source                = landing_pages
  morphology            = stem_enru
  path                  = /var/lib/sphinxsearch/data/landing_pages
  enable_star           = 1
}"
}

function print_leads
{
  header "leads_main"

  echo "
source leads_main : source_tpl
{
  sql_query_pre         = SET NAMES utf8
  sql_query             = SELECT id, lid, title, slug, created_at, post_type, main_region_id, GROUP_CONCAT(post_region.region_id) region_ids FROM post LEFT JOIN post_region ON post_region.post_id = id WHERE status = 'publish' AND (deleted_at IS NULL OR deleted_at = 0) GROUP BY id

  sql_field_string      = lid
  sql_field_string      = post_type
  sql_attr_string       = title
  sql_attr_string       = slug
  sql_attr_string       = created_at
  sql_attr_uint         = main_region_id
  sql_attr_multi        = uint region_ids from field;
}

index leads_main
{
  docinfo               = extern
  charset_type          = utf-8
  blend_chars           = U+3A, U+3B
  source                = leads_main
  morphology            = stem_enru
  path                  = /var/lib/sphinxsearch/data/leads_main
  enable_star           = 1
}"
}

function print_regionnodes
{
  header "regionnodes_main"

  echo "
source regionnodes_main : source_tpl
{
  sql_query_pre         = SET NAMES utf8
  sql_query             = SELECT id, region_id, name, name AS street, socr, socr AS regionnode_socr, parent, has_children, has_street, list, description, latitude, longitude FROM regionnode WHERE 1 = 1 GROUP BY id
  
  sql_attr_uint         = region_id
  sql_field_string      = name
  sql_field_string      = socr
  sql_attr_uint         = parent
  sql_attr_bool         = has_children
  sql_attr_bool         = has_street
  sql_attr_bool         = list
  sql_attr_string       = description
  sql_attr_float        = latitude
  sql_attr_float        = longitude
  sql_field_string      = street
  sql_field_string      = regionnode_socr
}

index regionnodes_main
{
  docinfo               = extern
  charset_type          = utf-8
  source                = regionnodes_main
  morphology            = stem_enru
  enable_star           = 1
  min_prefix_len        = 1
  path                  = /var/lib/sphinxsearch/data/regionnodes_main
}"
}

echo "# Config was generated at `date`" > $config

print_base >> $config
print_lots >> $config
print_posts >> $config
print_blog >> $config
print_streets >> $config
print_landing_pages >> $config
print_leads >> $config
print_regionnodes >> $config

deltas=`cat $config | grep index | grep delta | awk '{ print $2 }'`
deltas=`echo -n $deltas`

echo "# Crontab was generated at `date`" > $crontab
echo "SHELL=/bin/bash" >> $crontab
echo "*/15 * * * * root /var/www/mesto.ru/scripts/run_indexer_delta.sh > /dev/null" >> $crontab
echo "03  */3 * * * root /var/www/mesto.ru/scripts/run_indexer_main.sh > /dev/null" >> $crontab
echo "23  */3 * * * root /usr/bin/indexer landing_pages leads_main --rotate > /dev/null" >> $crontab


echo "Config and crontab updated. Reindex if needed: "
echo "$indexer --all --rotate"

