# PHP function ssh2_connect is not working
https://stackoverflow.com/questions/14050231/php-function-ssh2-connect-is-not-working

# phpseclib
Solução Alternativa para SSH no PHP
Pure PHP SSH2 implementation
http://phpseclib.sourceforge.net/
http://phpseclib.sourceforge.net/documentation/net.html

php.ini
include_path=C:\xampp\php\PEAR

local save phpseclib
C:\xampp\php\pear\phpseclib

# Outra forma de usar o phpseclib (importando através composer)

If you are using Laravel 5 or similar, you can use phpseclib much simpler like this:
Run composer require phpseclib/phpseclib ~2.0
use phpseclib\Net\SSH2;


# SSH2 PECL Extension
Solução Nativa para SSH no PHP
http://php.net/book.ssh2

CentOS 6.4 - Install SSH2 extension for PHP
http://programster.blogspot.com/2013/06/centos-64-install-ssh2-extension-for-php.html

Instalação:
http://php.net/manual/pt_BR/ssh2.installation.php

Instalação no Windows
Instalação das extensões PECL
http://php.net/manual/pt_BR/install.pecl.php
http://php.net/manual/pt_BR/install.pecl.windows.php
Download Extensão para Windows:
The SSH2 binary for Windows (php_ssh2.dll) can be found here: http://pecl.php.net/package/ssh2/1.1.2/windows

php.ini
extension_dir="C:\xampp\php\ext"
extension=php_ssh2.dll