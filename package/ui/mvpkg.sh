#!/bin/bash

TARGET=$1
PACKAGE=$2

if [[ $PACKAGE == "" ]]
then
	echo "Usage: mvpkg Target Package"
	echo "       Target must be like 'volumex' where x is a numeric."
	echo "       Package must be the name of a package."
	exit
fi

if [[ $TARGET != volume[0-9]* ]]
then
	echo "Usage: mvpkg Target Package"
	echo "       Target must be like 'volumex' where x is a numeric."
	echo "       Package [$PACKAGE] must be the name of a package."
	exit
fi

#Check the package and check the result: "enable" (is start), "disable" (is stop) or "does not exist"
output=$(/usr/syno/sbin/synoservicecfg --status "pkgctl-$PACKAGE" | grep Service)

if [[ $output == *"does not exist"* ]]
then
	echo "套件 $PACKAGE 找不到"
	exit
else
	#find the current volume of the package and its link
	output=$( ls -la /var/packages/*/target | grep "/$PACKAGE/")
	
	link=$(echo $output | grep -oP "\/var/packages/.*/target")
	volume=$(echo $output | grep -oP "volume\d*")
	path=$(echo $output | grep -oP "\/volume.*")
	
	if [[ $link != "/var/packages/$PACKAGE/target"* ]]
	then
		echo "套件 $PACKAGE 没有正确安装。"
		exit
	fi
	
	if [[ $volume != "volume"* ]]
	then
		echo "套件 $PACKAGE 找不到"
		exit
	fi

	if [[ $volume == $TARGET ]]
	then
		echo "套件 $PACKAGE 已在 $TARGET 上."
		exit
	fi
	
	if [[ "$path" != "/$volume/@appstore/$PACKAGE" ]]
	then
		echo "套件 $PACKAGE 没有一个标准位置。"
		exit
	fi
		
	#List Packages with dependency on this one
	#/usr/syno/sbin/synoservicecfg --reverse-dependency pkgctl-$PACKAGE
			
	#Stop the package and all its dependencies
	output=$(/usr/syno/sbin/synoservicecfg --hard-stop "pkgctl-$PACKAGE" | grep warn)
	
	if [[ $output != *"have been set"* ]]
	then
		echo "套件 $PACKAGE 无法停用。"
		exit
	fi
	
	if [ -d "/$TARGET/@appstore/$PACKAGE" ]; then
		mv "/$TARGET/@appstore/$PACKAGE" "/$TARGET/@appstore/$PACKAGE-$(date -d "today" +"%Y%m%d%H%M").log"
	fi

	#remove the link on the previous volume
	rm -f "$link"
	
	#move the package
	mv "$path" /$TARGET/@appstore
	
	#link with the  package on the new volume
	ln -s "/$TARGET/@appstore/$PACKAGE" "$link"
	
	#Replace link also in local 
	local="/usr/local/$PACKAGE"
	if [ -L "$local" ]; then
		rm -f "$local"
		ln -s "/$TARGET/@appstore/$PACKAGE" "$local"
	fi
	
	#update settings
	sed -i "s/$volume/$TARGET/" "/usr/syno/etc/packages/$PACKAGE/*" &>/dev/null
	
	#Restart packages depending on the one moved
	output=$(/usr/syno/sbin/synoservicecfg --reverse-dependency "pkgctl-$PACKAGE")

	output="$(echo $output | grep -Po "pkgctl-([^\]]*)")"
	for string in $output
	do
		/usr/syno/sbin/synoservicecfg --start "$string"
	done	

	#Restart the package and all its dependencies
	output=$(/usr/syno/sbin/synoservicecfg --hard-start "pkgctl-$PACKAGE" | grep Service)
	
	#Check if the package has been correctly restarted
	output=$(/usr/syno/sbin/synoservicecfg --is-enabled "pkgctl-$PACKAGE")

	if [[ $output != *"is enabled"* ]]
	then
		echo "套件 $PACKAGE 从 $volume 移至 $TARGET 后无法正确重启."
	else	
		echo "套件 $PACKAGE 已成功从 $volume 移至 $TARGET."
	fi
fi
