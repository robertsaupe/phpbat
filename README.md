![phpBAT](https://raw.githubusercontent.com/robertsaupe/phpbat/master/.github/phpbat_mini.png)

# phpBAT - a PHP based Backup &amp; Admin Tool

[Supporting](https://github.com/robertsaupe/phpbat#supporting) |
[Features](https://github.com/robertsaupe/phpbat#features) |
[License](https://github.com/robertsaupe/phpbat#license) |
[Requirements](https://github.com/robertsaupe/phpbat#requirements) |
[Installing](https://github.com/robertsaupe/phpbat#installing) |
[Getting started](https://github.com/robertsaupe/phpbat#getting-started) |
[Credits](https://github.com/robertsaupe/phpbat#credits) |
[Changelog](https://github.com/robertsaupe/phpbat#changelog) |
[History](https://github.com/robertsaupe/phpbat#history)

## Supporting
[Patreon](https://www.patreon.com/robertsaupe) 
[PayPal](https://www.paypal.com/donate?hosted_button_id=SQMRNY8YVPCZQ)

## Features
- full server backups
- server updates
- server cleanup
- backup individual folders to archive (tar, tar.gz)
- dump mysql/mariadb
- access rights adjustment (chmod)
- delete old backups and logs
- compression (optional)
- encrypt and decrypt backups (optional)
- synchronisation/replication from or to rsync/ftp/ftps/sftp
- logging (with levels) by output, log-file and mail
- automatic selfupdates
- custom and easy configuration

## License
This software is distributed under the MIT license. Please read [LICENSE](LICENSE) for information.

## Requirements
- PHP 8.0.0 or higher
- fully supported OS: Debian, Rasbian, Ubuntu, Arch, Manjaro
- partially supported: other Linux, Mac OS, Windows
- complete requirements documented in [Configuration.Default.jsonc](Configuration.Default.jsonc)

## Installing
download latest [Release](https://github.com/robertsaupe/phpbat/releases)
```
$ tar xvz -f x.x.x.tar.gz
$ rm x.x.x.tar.gz
```

## Getting started
copy [Configuration.Default.jsonc](Configuration.Default.jsonc) to Configuration.jsonc and editing it.

using php-cli
```
$ php phpBAT.php
```

using cron: daily
```
$ crontab -e

0 2 * * * php /root/phpbat/phpBAT.php -cron
```

using php-cli: decrypt all encrypted backups
```
$ php phpBAT.php -d
```

using php-cli: decrypt a encrypted backup-file
```
$ php phpBAT.php -df file.enc
```

## Credits
- https://github.com/splitbrain/php-archive
- https://github.com/ifsnop/mysqldump-php
- https://github.com/PHPMailer/PHPMailer
- https://github.com/phpseclib/phpseclib
- https://github.com/paragonie/constant_time_encoding

## Changelog
See [CHANGELOG](CHANGELOG.md).

## History
- originally written in 2018 as a private tool for my server
- complete rewritten (2.x) and published in 2021 on github
