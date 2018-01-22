#!/bin/bash

H="$HOSTNAME"
U="$USER"
ID=""

while getopts ":h:u:i:" opt; do
  case $opt in
	h)
		H="$OPTARG"
		;;
	u)
		U="$OPTARG"
		;;
	i)
		ID="$OPTARG"
		;;
    \?)
      echo "Invalid option: -$OPTARG" >&2
      exit 1
      ;;
  esac
done
shift $(($OPTIND-1))
FILE="$1"
EXT="${FILE##*.}"
HOST=`/var/www/html/openjean/scripts/get_host.php -h "$H"`
PASS=`/var/www/html/openjean/scripts/get_password.php -u $U -p "$H"`
#echo "host $HOST, user $U, password $PASS, file $FILE, id $ID"
ftp -inv $HOST << EOF
user $U $PASS
cd tmp/pool
put "$FILE" "$ID.$EXT"
bye
EOF
