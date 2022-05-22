#!/usr/bin/env bash
cd "$(dirname "$(readlink -f "$0" || realpath "$0")")"
cd ../
box compile
php .tools/release.php
read -p "Press any key to resume ..."