#!/bin/sh
# $Id: pack.sh,v 1.1 2006-09-14 17:29:09 oops Exp $

cp -af package.xml.tmpl package.xml
list=$(grep "md5sum" ./package.xml | sed 's/.*"@\|@".*//g')

for i in $list
do
	md5s=$(md5sum $i | awk '{print $1}')
	perl -pi -e "s!\@${i}\@!${md5s}!g" ./package.xml
done

if [ -z "$1" ]; then
	pear package
fi
