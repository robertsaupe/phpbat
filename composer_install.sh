#!/usr/bin/env bash
cd "$(dirname "$(readlink -f "$0" || realpath "$0")")"
composer install
read -p "Press any key to resume ..."
