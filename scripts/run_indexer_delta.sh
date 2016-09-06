#!/bin/bash
lock="/var/run/run_indexer_delta"
deltas="apartament_sale_delta apartament_rent_delta house_sale_delta house_rent_delta commercial_sale_delta commercial_rent_delta new_building_sale_delta cottage_sale_delta news_delta article_delta analytics_delta events_delta expert_article_delta author_article_delta qa_delta blog_delta"

echo "Checking lock"
lockfile-check --use-pid $lock && exit 1

echo "Locking"
lockfile-create --use-pid $lock


echo "Update delta indexes"
/usr/bin/indexer $deltas --rotate >> /var/log/sphinxsearch/indexer_delta.log

lockfile-remove $lock
