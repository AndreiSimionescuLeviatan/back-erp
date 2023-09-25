#!/bin/bash

# you need to add ecf_backup_server configuration in the ~/.ssh/config
# Variables to insert in instance-local.conf INSTANCE_NAME, DBHOST, MYSQL_USER, MYSQL_PASSWORD, SQL_PATH


# The string_in_list function is used to determine whether a given string exists in a list of strings.
# Parameters:
#  * SEARCH_STRING: The string you want to search for in the list.
#  * STRING_LIST...: One or more strings forming the list to search within.

string_in_list() {
    local search_string="$1"
    shift
    local string_list=("$@")

    for item in "${string_list[@]}"; do
        if [ "$item" = "$search_string" ]; then
            return 0  # Return success (string found)
        fi
    done

    return 1  # Return failure (string not found)
}

# get PATH of executed file
MY_PATH="$(dirname -- "${BASH_SOURCE[0]}")"


# import INSTANCE_NAME, DBHOST, SQL_PATH, MYSQL_USER, MYSQL_PASSWORD
if [ ! -f $MY_PATH/instance-local.conf ]
then
    echo "$MY_PATH/instance-local.conf file doesn't exist"
    echo "Variables to insert INSTANCE_NAME, DBHOST, MYSQL_USER, MYSQL_PASSWORD, SQL_PATH"
    exit 1;
fi

source $MY_PATH/instance-local.conf

if  [ "$INSTANCE_NAME" ==  "" ]; then
    echo "Add INSTANCE_NAME variabile in $MY_PATH/instance-name.conf "
    exit 1
fi

echo "INSTANCE_NAME=$INSTANCE_NAME"
echo "DBHOST=$DBHOST"
echo "SQL_PATH=$SQL_PATH"

# set -e
COMMIT_COUNT=0
COMMIT_LIMIT=10
COMMIT_ZERO=0
START_TIME=$(date +%s)
BEGIN_TIME=$(date +%Y-%m-%d)
echo 'The script begin on '$BEGIN_TIME

current_hour=$(date +%Y_%m_%d_%H_%M)
echo 'Doing the backup for the hour '$current_hour

file='list-of-tables.txt'
databases_file='list-of-databases.txt'

bkup_dir="$SQL_PATH/$current_hour"
# bkup_dir_schemas="$bkup_dir/schemas"
# bkup_dir_data="$bkup_dir/data"
bkup_zip_path="$SQL_PATH/$INSTANCE_NAME-hourly"
bkup_zip="${bkup_zip_path}/${current_hour}.zip"
mkdir -p $bkup_zip_path

echo 'The mysql command insert into list-of-table.txt file all the tables'

if [ -d $bkup_dir ]
then
    rm -rf $bkup_dir
    echo 'The directory where the script writes was deleted '$bkup_dir
fi

if [ ! -d $bkup_dir ]
then
    mkdir -p $bkup_dir
    echo 'The directory was created '$bkup_dir
fi


cd $bkup_dir
echo 'Inside of directory '$bkup_dir
# Gets all erp databases
mysql -h $DBHOST -u $MYSQL_USER -p$MYSQL_PASSWORD -A --skip-column-names -e "SELECT DISTINCT(table_schema) FROM information_schema.tables WHERE table_schema NOT IN (${IGNORE_DATABASES})" > $bkup_dir/$databases_file
# $bkup_dir/databases.sql = run this to import databases
echo "SET foreign_key_checks = 0;" >> $bkup_dir/databases.sql
for DB in `cat ${bkup_dir}/${databases_file}`
do
    echo "database: $DB"
    # prepare db directories
    backup_db_dir="$bkup_dir/${DB}"
    mkdir -p $backup_db_dir $backup_db_dir/data $backup_db_dir/schemas

    # run database script
    echo "source $DB/database.sql;" >> $bkup_dir/databases.sql
    echo "SET foreign_key_checks = 0;" >> $backup_db_dir/database.sql

    # create DB
    echo "DROP DATABASE IF EXISTS \`${DB}\`;" >> $backup_db_dir/database.sql
    echo "CREATE DATABASE IF NOT EXISTS \`${DB}\` DEFAULT CHARACTER SET utf8 COLLATE utf8mb3_general_ci;" >> $backup_db_dir/database.sql
    echo "USE $DB;" >> $backup_db_dir/database.sql
    echo "SET NAMES utf8;" >> $backup_db_dir/database.sql
    # get all tables from a database
    mysql -h $DBHOST -u $MYSQL_USER -p$MYSQL_PASSWORD -A --skip-column-names -e"SELECT table_name FROM information_schema.tables WHERE table_schema='${DB}'" > $backup_db_dir/list-of-tables.txt
    for TB in `cat $backup_db_dir/list-of-tables.txt`
    do
        if string_in_list "${DB}.${TB}" "${IGNORE_TABLES[@]}" ; then
            echo "Table ${DB}.${TB} ignored"
            continue
        fi
        echo "table: $TB"
        echo "source ${DB}/schemas/${TB}.sql;" >> $backup_db_dir/database.sql
        echo "source ${DB}/data/${TB}.sql;" >> $backup_db_dir/database.sql
        # get table schema
        mysqldump -h $DBHOST -u $MYSQL_USER -p$MYSQL_PASSWORD --skip-comments --triggers --no-data ${DB} ${TB} > $backup_db_dir/schemas/${TB}.sql
        # get table data
        mysqldump -h $DBHOST -u $MYSQL_USER -p$MYSQL_PASSWORD --skip-comments --no-create-info --triggers --compact ${DB} ${TB} > $backup_db_dir/data/${TB}.sql
    done

    echo "SET foreign_key_checks = 1;" >> $backup_db_dir/database.sql

done
echo "SET foreign_key_checks = 1;" >> $bkup_dir/databases.sql

if [ -f $bkup_zip ]
then
    rm -rf $bkup_zip
    echo 'If the file witch contains the econfaire database exists, will be deleted'
fi

echo 'Creating the econfaire databases backup archive'
cd $SQL_PATH
zip -9 -r -q $bkup_zip $bkup_dir

# you need to add ecf_backup_server configuration in the ~/.ssh/config
scp $bkup_zip ecf_backup_server:/home/ecf/$INSTANCE_NAME-hourly/
rm -rf $bkup_dir

########## Deleting backups older than 7 days
echo 'Deleting backups older than 7 days'
printf '%s\n' "$bkup_zip_path"/*.zip | head -n -7 | while read -r file; do rm -f -- "$file"; done


ENDED_TIME=$(date +%Y-%m-%d-%H-%M)
echo 'The script finish on '$ENDED_TIME
END_TIME=$(date +%s)
echo 'The script duration: '$((${END_TIME}-${START_TIME}))'seconds'