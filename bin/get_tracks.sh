#!/bin/bash
OJ="/var/www/html/openjean"
INPOOL=`"$OJ/scripts/get_parameter.php" -h "$HOSTNAME" -c multimedia -p inpool`
OUTPOOL=`"$OJ/scripts/get_parameter.php" -h "$HOSTNAME" -c multimedia -p outpool`
U="$USER"
#> /home/mike/Dropbox/pool/STOP_$HOSTNAME
#sleep 30
#rm /home/mike/Dropbox/pool/STOP_$HOSTNAME
while [ ! -f $INPOOL/STOP_$HOSTNAME ]
do
	from=`inotifywait -e create,move -q -t 0 "$INPOOL" | cut -d\  -f3`
echo `date` "file arrived $from"
	if [ -f "$INPOOL/$from" ]
	then
		rfrom=`echo $from | rev`
		trackid=`echo $rfrom | cut -d_ -f1 | rev`
		rhost=`echo $rfrom | cut -d_ -f2- | rev`
echo `date` "removing $from"
		rm "$INPOOL/$from"
		trackpath=`$OJ/scripts/get_attribute.php -p detail -a track -e $trackid`
		filename=$(basename "$trackpath")
		extension="${filename##*.}"
#		filename="${filename%.*}"echo "host $rhost, track $trackpath"
		if [ "$trackpath" ]
		then
#			cp "$trackpath" /home/mike/Dropbox/pool/$rhost/$trackid.$extension
			HOST=`/var/www/html/openjean/scripts/get_host.php -h "$rhost"`
			PASS=`/var/www/html/openjean/scripts/get_password.php -u $U -p "$rhost"`
			echo "host $HOST, user $U, password $PASS, file $trackpath, id $trackid"
			ftp -inv $HOST << EOF
user $U $PASS
cd "$OUTPOOL"
put "$trackpath" "$trackid.$extension"
bye
EOF
		fi
	fi
done
#rm /home/mike/Dropbox/pool/STOP_$HOSTNAME
