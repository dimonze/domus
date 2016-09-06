#!/bin/bash

ARGV="$@"
sf="cd mesto.ru; ./symfony"
ssh_o='-o BatchMode yes'

function arg_exists
{
  for i in $ARGV; do
    if [[ $i == $1 ]]; then return 1; fi
  done
  return 0
}

arg_exists --help
if [[ 1 -eq $? ]]; then
  echo "Usage: $0"
  echo "  --help       show this help"
  echo "  --rebuild    rebuild all models after deploy, invokes --gearman"
  echo "  --gearman-c  update gearman-monitor config, invokes --gearman"
  echo "  --gearman    restart gearman workers"
  echo "  --crontab    update crontab"
  exit
fi

if [[ "deploy" != `whoami` ]]; then
  echo "Script should be executed from deploy@server.garin"
  exit 1
fi

cd `dirname $0`

echo "## Updating repo"
gitpull=`git pull`
echo "$gitpull"


if [[ `echo $gitpull | grep -E "lib/"` || `echo $gitpull | grep -E "config/"` ]]; then
  gearman=1
else
  arg_exists --gearman
  gearman=$?
fi


if [[ `echo $gitpull | grep "config/doctrine"` ]]; then
  rebuild=1
  gearman=1
else
  arg_exists --rebuild
  if [[ 1 -eq $? ]]; then
    rebuild=1
    gearman=1
  else
    rebuild=0
  fi
fi


if [[ `echo $gitpull | grep "config/workers"` ]]; then
  gearmanc=1
  gearman=1
else
  arg_exists --gearman-c
  if [[ 1 -eq $? ]]; then
    gearmanc=1
    gearman=1
  else
    gearmanc=0
  fi
fi


if [[ `echo $gitpull | grep "config/crontab"` ]]; then
  crontab=1
else
  arg_exists --crontab
  crontab=$?
fi


echo "#### SUMMARY: "
for opt in rebuild gearmanc gearman crontab; do
  echo "$opt: ${!opt}"
done

sleep 2


if [[ 1 -eq $rebuild ]]; then
  echo "## Rebuilding local"
  ./rebuild.sh > /dev/null || exit 1
fi



echo "## Deploying code:"
echo "#* prod: "
./symfony project:deploy prod --go  || exit 1
echo "#* helper: "
./symfony project:deploy helper --go  || exit 1
echo "#* openx: "
./symfony project:deploy openx --go || exit 1


if [[ 1 -eq $rebuild ]]; then
  echo "## Rebuilding remote:"
  echo "#* prod: "
  ssh "$ssh_o" domus1 'cd mesto.ru; ./rebuild.sh > /dev/null' || exit 1
  echo "#* helper: "
  ssh "$ssh_o" domus3 'cd mesto.ru; ./rebuild.sh > /dev/null' || exit 1
  echo "#* openx: "
  ssh "$ssh_o" domus2 'cd mesto.ru; ./rebuild.sh > /dev/null' || exit 1

#else
#  ssh "$ssh_o" domus1 "$sf cc > /dev/null"
#  ssh "$ssh_o" domus3 "$sf cc > /dev/null"
#  ssh "$ssh_o" domus2 "$sf cc > /dev/null"

fi

echo "## Cleaning config cache"
ssh "$ssh_o" domus3 "rm -rf /var/www/mesto.ru_data/config_cache/*"


if [[ 1 -eq $gearmanc ]]; then
  echo "## Updating gearman config:"

  echo "#* domus1"
  ssh "$ssh_o" domus1 "cat /var/www/mesto.ru/config/workers.1.yml > /etc/gearman-monitor/workers.yml"

  echo "#* domus3"
  ssh "$ssh_o" domus3 "cat /var/www/mesto.ru/config/workers.3.yml > /etc/gearman-monitor/workers.yml"

  echo "#* domus2"
  ssh "$ssh_o" domus2 "cat /var/www/mesto.ru/config/workers.2.yml > /etc/gearman-monitor/workers.yml"
fi


if [[ 1 -eq $gearman ]]; then
  echo "## Restarting gearman workers:"
  for server in domus1 domus3 domus2; do
    echo "#* $server"
    ssh "$ssh_o" $server "ps aux | grep Gearman | grep mesto.ru | grep -v grep | awk '{ print \$2 }' | xargs kill &> /dev/null"
    ssh "$ssh_o" $server 'sudo /usr/local/bin/gearman-monitor.sh'
  done
fi


if [[ 1 -eq $crontab ]]; then
  echo "## Updating crontab:"

  echo "#* domus1"
  ssh "$ssh_o" domus1 "cat /var/www/mesto.ru/config/crontab.1 | crontab"

  echo "#* domus3"
  ssh "$ssh_o" domus3 "cat /var/www/mesto.ru/config/crontab.3 | crontab"

  echo "#* domus2"
  ssh "$ssh_o" domus2 "cat /var/www/mesto.ru/config/crontab.2 | crontab"
fi


curl -q -d "api_key=5b8deb323936fd0c1b685bc1e22e9e59&deploy[rails_env]=production&deploy[local_username]=`whoami`&deploy[scm_revision]=`git rev-parse HEAD`" https://errbit.garin.su/deploys.txt


# ~/.ssh/config
# Host domus1
#   HostName 91.218.230.73
#   User www-data
#
# Host domus2
#   HostName 91.218.231.114
#   User www-data
#
# Host domus3
#   HostName 91.218.230.171
#   User www-data
