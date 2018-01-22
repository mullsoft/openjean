#!/bin/bash
OJ="/var/www/html/openjean"
#> /home/mike/Dropbox/pool/STOP_$HOSTNAME
#sleep 30
#rm /home/mike/Dropbox/pool/STOP_$HOSTNAME
INPOOL=`"$OJ/scripts/get_parameter.php" -h "$HOSTNAME" -c multimedia -p inpool`
while [ ! -f "$INPOOL/STOP_$HOSTNAME" ]
do
	from=`inotifywait -e create,moved_to -q -t 0 "$INPOOL" | cut -d\  -f3`
	inotifywait -e close_write -q -t 0 "$INPOOL/$from"
#echo "from $from"
	if [ -f "$INPOOL/$from" ]
	then
		extension="${from##*.}"
		filename="${from%.*}"
		if [[ "$extension" == "wav" ]]
		then
			mv "$INPOOL/$from" /tmp
		elif [[ "$extension" == "mp3" ]]
		then
			mv "$INPOOL/$from" /tmp
		else
			/usr/bin/sox "$INPOOL/$from" -b 24 /tmp/$filename.wav
			rm "$INPOOL/$from"
		fi
	fi
done
