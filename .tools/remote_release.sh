#!/usr/bin/env bash
cd "$(dirname "$(readlink -f "$0" || realpath "$0")")"
cd ../
box compile
php .tools/release.php
cd ../gh-pages
git add -A
git commit -m "Bump new phar release"
git push origin gh-pages
read -p "Press any key to resume ..."