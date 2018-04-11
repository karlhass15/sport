#! /bin/bash

DB_OBJECT=`php -r 'print_r(json_decode(base64_decode($_ENV["MAGENTO_CLOUD_RELATIONSHIPS"]))->database);'`
DB_HOST=`echo $DB_OBJECT | sed -r 's/.*\[host\]\s=>\s(\S+).*/\1/'`
DB_USER=`echo $DB_OBJECT | sed -r 's/.*\[username\]\s=>\s(\S+).*/\1/'`
DB_PASS=`echo $DB_OBJECT | sed -r 's/.*\[password\]\s=>\s(\S+).*/\1/'`

mysqldump -h $DB_HOST -u $DB_USER -p$DB_PASS --single-transaction $DB_USER | sed 's/CREATE\*\/.*TRIGGER/CREATE TRIGGER/g' | gzip > ~/var/sport_dog_food.sql.gz
echo "Dumped to ~/var/sport_dog_food.sql.gz"