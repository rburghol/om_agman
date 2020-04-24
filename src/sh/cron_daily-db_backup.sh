#!/bin/sh
bak="/data/backup"
http="/var/www"
wk=`date +%U`
mo=`date +%m`
dy=`date +%w`

for i in drupal.live; do
  rm "$bak/db-$i-day-$dy.gz"
  echo "pg_dump --compress=9 -Fc -b -v -f $bak/db-$i-day-$dy.gz $i"
  pg_dump --compress=9 --exclude-table="dh_timeseries_weather" -Fc -b -v -f "$bak/db-$i-day-$dy.gz" $i
  pg_dump --compress=9 --table="dh_timeseries_weather" -Fc -b -v -f "$bak/db-$i-weather-day-$dy.gz" $i
done


# copy the drupal code base with modules
for i in d.live d.dev; do
  rm "$bak/www-$i-day-$dy.tar.gz"
  echo "tar -cf $bak/www-$i-day-$dy.tar $http/$i"
  tar -cf "$bak/www-$i-day-$dy.tar" "$http/$i"
  echo "gzip $bak/www-$i-day-$dy.tar"
  gzip "$bak/www-$i-day-$dy.tar"
done

# copy home directories
for i in ubuntu; do
  rm "$bak/home-$i.tar.gz"
  echo "tar -cf $bak/home-$i.tar /home/$i"
  tar -cf "$bak/home-$i.tar" "/home/$i"
  echo "gzip $bak/home-$i.tar"
  gzip "$bak/home-$i.tar"
done
