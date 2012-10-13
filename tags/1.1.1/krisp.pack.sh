#!/bin/sh

ver=$(cat version)
tpp=$$

tmp=/tmp/krisp.${tpp}

mkdir -p ${tmp}/krisp-${ver}
cp -rf krisp/ krisp.php ${tmp}/krisp-${ver}/
cp -rf package.xml ${tmp}/

pushd ${tmp}/krisp-${ver}
	list=$(grep "md5sum" ../package.xml | sed 's/.*"@\|@".*//g')

	for i in $list
	do
		md5s=$(md5sum $i | awk '{print $1}')
		perl -pi -e "s!\@${i}\@!${md5s}!g" ../package.xml
	done
popd

pushd ${tmp}
	find ./ -name CVS -exec rm -rf {} \;
	tar cvfpz krisp-${ver}.tgz krisp-${ver} package.xml
popd

mv ${tmp}/krisp-${ver}.tgz ./
rm -rf ${tmp}
