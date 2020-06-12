#!/bin/sh
bak="/data/backup"
http="/var/www"
wk=`date +%U`
mo=`date +%m`
dy=`date +%w`

# just do a single monthly snapshot since we don't need to take weekly ones and we have 7 dailies
for i in drupal.live; do
  rm "$bak/db-$i-month-$mo.gz"
  echo "pg_dump --compress=9 -Fc -b -v -f $bak/db-$i-month-$mo.gz $i"
  pg_dump --compress=9 --exclude-table="dh_timeseries_weather" -Fc -b -v -f "$bak/db-$i-month-$mo.gz" $i
  pg_dump --compress=9 --table="dh_timeseries_weather" -Fc -b -v -f "$bak/db-$i-weather-month-$mo.gz" $i

done

# copy the drupal code base with modules
for i in d.live; do
  rm "$http/www-$i-week.tar.gz"
  echo "tar -cf $bak/www-$i-week.tar $http/$i"
  tar -cf "$bak/www-$i-week.tar" "$http/$i"
  gzip "$bak/www-$i-week.tar"
  echo "gzip $bak/www-$i-week.tar"
done
#sudo mv gp.live.tar.gz /data/backup/ -f
#sudo tar -cf gp.dev.tar www/d.dev
#sudo gzip gp.dev.tar
#sudo mv gp.dev.tar.gz /data/backup/ -f
