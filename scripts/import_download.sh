#!/bin/bash
cd "`dirname $0`/.."
target="cache/import"
cdir=`pwd`
project=`basename $cdir`

my_config="config/databases.yml"
my_user=`grep username $my_config | head -n1 | awk -F': *' '{ print $2 }'`
my_password=`grep password $my_config | head -n1 | awk -F': *' '{ print $2 }'`
my_host=`grep host $my_config | head -n1 | awk -F'host=' '{ print $2 }'`
my_db=`grep dbname $my_config | head -n1 | awk -F'dbname=|;' '{print $2}'`

test -d $target || mkdir $target
find "$target" -type f -delete

frequencies="12 24"

echo `date`

for frequency in $frequencies; do
  if [[ `date +%k%$frequency | bc` -eq 0 ]]; then
    echo "Files to download every $frequency hours"
    select="select ul.id, ul.url from user_sources_link ul left join user u on u.id = ul.user_id where u.type = 'company' and ul.status NOT IN ('banned','not-paid') and ul.frequency = $frequency"
    sources=`echo "$select" | mysql --batch --skip-column-names -h$my_host -u$my_user -p$my_password $my_db`

    IFS='
'
    for source in $sources; do
      id=`echo "$source" | awk -F"\t" '{ print $1 }'`
      link=`echo "$source" | awk -F"\t" '{ print $2 }'`
      echo -ne "$id\t$link "

      wget -q --tries=5 --timeout=600 -O "$target/$id.dl" "$link" && (
        echo 'OK'
        mv "$target/$id.dl" "$target/$id"
        echo $id | /usr/local/bin/gearman -bNf "${project}__import_file"
      ) || echo 'FAIL';
    done
  fi
done
