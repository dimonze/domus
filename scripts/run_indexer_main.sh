#!/bin/bash
lock="/var/run/run_indexer_main"
mains="apartament_sale_main apartament_rent_main house_sale_main house_rent_main commercial_sale_main commercial_rent_main new_building_sale_main cottage_sale_main news_main article_main analytics_main events_main expert_article_main author_article_main qa_main blog_main"

echo "Checking lock"
lockfile-check --use-pid $lock && exit 1

echo "Locking"
lockfile-create --use-pid $lock


echo "Update main indexes"
/usr/bin/indexer $mains --rotate >> /var/log/sphinxsearch/indexer_main.log

lockfile-remove $lock
