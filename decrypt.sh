#!/usr/bin/env bash
cd "$(dirname "$(readlink -f "$0" || realpath "$0")")"
php phpBAT.php -d
read -p "Press any key to resume ..."