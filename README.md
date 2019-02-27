# api-axes-slim
API Axes com Slim Framework

::API::
https://apigility.org (Apigility is an API Builder)
JSON Server: https://github.com/typicode/json-server (Simular API REST)
Simulando uma API REST com JSON Server de maneira simples	http://www.fabricadecodigo.com/json-server/
	npm install -g json-server
Client API REST: Postman	https://www.getpostman.com/
Client API REST: Insomnia	https://insomnia.rest/
Test your front-end against a real API	https://reqres.in
The Movie Database API (Exemplo)	https://developers.themoviedb.org/
API Builder	https://appery.io/
HTTP response status codes	https://developer.mozilla.org/pt-BR/docs/Web/HTTP/Status

[APIGILITY]
$ php -r "readfile('https://apigility.org/install');" | php
local instalação no routerbox (179.191.232.6): /var/local/apigility
You need to configure a web server to point the 'apigility/public' folder
$ cd /var/local/apigility/public/
Running PHP Built-in web server (Servidor web PHP embutido)
$ php -S 0.0.0.0:8888
or
$ cd /var/local/apigility/
php -S 0.0.0.0:8888 -ddisplay_errors=0 -t public public/index.php
php -S 0.0.0.0:8888 -t public index.php
php -S 0.0.0.0:8080 -t public/index.php
http://179.191.232.6:8888

Htpasswd Generator – Create htpasswd	http://www.htaccesstools.com/htpasswd-generator/
user: axesdev	password: axesdevelopment

Apigility Documentation
https://apigility.org/documentation/intro/getting-started
https://apigility.org/documentation/intro/first-rest-service
https://apigility.org/documentation/content-validation/basic-usage
https://apigility.org/documentation/modules/zf-rpc
https://apigility.org/documentation/recipes/apigility-in-an-existing-zf2-application
https://apigility.org/documentation/recipes/hal-from-rpc
Providing REST endpoints that JOIN table data	https://apigility.org/documentation/recipes/join-tables
https://apigility.org/documentation/recipes/upload-files-to-api
https://apigility.org/documentation/recipes/integrate-social-logins
Apigility Entity & Mapper Tips	https://sobo.red/2017/05/01/apigility-entity-mapper-tips/
Building an API with Apigility	https://akrabat.com/wp-content/uploads/2015-02-20-phpuk-apigility.pdf
apigility-blog-example	https://github.com/n1te1337/apigility-blog-example/tree/master/module/Auth/src/Auth/V1/Rest/Registration

Listas Youtube Apigility
https://www.youtube.com/watch?v=ushsUYcYEsI&index=2&list=PLt3tq0MBSMpmw5H8OFa2E9T1Ny9xQSbNC
https://www.youtube.com/watch?v=usO9aUPr1zo&list=PLU4wSpl2v2UvLFE3sF6ZJ4tnAkMYScO-J&index=7

Servidor web embutido
https://secure.php.net/manual/pt_BR/features.commandline.webserver.php
Aproveitando o servidor embutido do PHP
https://www.sitepoint.com/taking-advantage-of-phps-built-in-server/

Serie Apigility com Ionic
https://sites.code.education/apigility-ionic-sv01/
Iniciando com ZF 2 Apigility
https://player.vimeo.com/video/133951662?title=0&byline=0&portrait=0;;autoplay=1
Iniciando com Ionic
https://player.vimeo.com/video/134200885?title=0&byline=0&portrait=0;autoplay=1
Ionic integrado ao Apigility
https://player.vimeo.com/video/134209669?title=0&byline=0&portrait=0;autoplay=1

http://blog.thiagobelem.net/criptografando-senhas-no-php-usando-bcrypt-blowfish

Problemas
Autenticação básica com Apigility e Apache	https://groups.google.com/a/zend.com/forum/#!topic/apigility-users/ulXQe_-EYm4
apigility version: 1.6.0 — zfcampus/statuslib-example installation fails	https://github.com/zfcampus/statuslib-example/issues/11
Why are $_SERVER[“PHP_AUTH_USER”] and $_SERVER[“PHP_AUTH_PW”] not set?	https://stackoverflow.com/questions/14724127/why-are-serverphp-auth-user-and-serverphp-auth-pw-not-set 


[SLIM FRAMEWORK]
Slim is a PHP micro framework that helps you quickly write simple yet powerful web applications and APIs
https://www.slimframework.com/
https://www.slimframework.com/docs/v2/response/helpers.html
https://www.php-fig.org/psr/	PHP Standards Recommendations
cd C:\xampp\htdocs\api-axes-slim
git clone https://github.com/kalleycorrea/api-axes-slim.git .
composer require slim/slim "^3.0"

Como usar o Composer
https://getcomposer.org/doc/01-basic-usage.md
https://getcomposer.org/doc/03-cli.md
https://getcomposer.org/doc/03-cli.md#require
https://desenvolvimentoparaweb.com/php/composer-php-5-macetes/	5 macetes do Composer

configurar o arquivo .htaccess na raiz da aplicação
configurar o autoload psr-4: composer dumpautoload -o

Youtube Cursos
Criando um Web Service Rest em PHP com Slim Framework	https://www.youtube.com/watch?v=nzWqzcNuLgQ
APIS RESTFUL COM PHP E SLIM	https://www.youtube.com/watch?v=nEwSE4PJmII&list=PLZ8kYL6LBgg6W3rmqvK58oLom9lFf4Nag
APIs REST com PHP 7 e Slim Framework	https://www.youtube.com/watch?v=vVkOUXpuuJg&index=1&list=PLZ8kYL6LBgg62kzIa6Io42Ccz_rWJBS-l
										https://github.com/codeeasy-dev/apis-rest-com-php-7-e-slim-framework

https://codeeasy.com.br/	https://github.com/codeeasy-dev		https://gitter.im/frv-dev/CodeEasy

Create a quick REST API using Slim framework	https://www.codediesel.com/php/create-a-quick-rest-api-using-slim-framework/
Creating a Simple REST API With Slim Framework	https://www.cloudways.com/blog/simple-rest-api-with-slim-micro-framework/
Creating an API FAST with Slim Framework	https://www.14oranges.com/2016/05/creating-an-api-fast-with-slim-framework/

Google: criptografando codigo fonte php	https://imasters.com.br/back-end/solucoes-de-protecao-para-codigo-fonte-php-php-application-packer-package
Google: slim framework with json hal	https://github.com/nilportugues/php-hal (HAL+JSON & HAL+XML API transformer outputting valid (PSR-7) API Responses)
Google: use hal json with slim framework	https://github.com/brandonlamb/php-hal (PHP library for representing HAL resources for REST API)

Log:
https://www.slimframework.com/docs/v3/tutorial/first-app.html
https://medium.com/@fidelissauro/slim-framework-criando-microservices-06-middlewares-logging-e-http-errors-fallback-8b45bd6ce85c

Slim -> Autenticação e Persistência(Doctrine)

Autenticação básica com HTTP Auth
	$ composer require tuupola/slim-basic-auth
	https://github.com/tuupola/slim-basic-auth (PSR-7 and PSR-15 HTTP Basic Authentication Middleware)
	https://appelsiini.net/projects/slim-basic-auth/ (Basic Authentication Middleware)
	https://appelsiini.net/2014/slim-database-basic-authentication/ (HTTP Basic Authentication from Database for Slim)
		HTTP Basic Authentication fails with Slim 3 using PDO Authenticator
		https://stackoverflow.com/questions/40080174/http-basic-authentication-fails-with-slim-3-using-pdo-authenticator
	https://www.slimframework.com/docs/v3/objects/request.html (Slim Request)
	https://www.slimframework.com/docs/v3/cookbook/environment.html	(Slim Getting and Mocking the Environment -> $_SERVER superglobal array)
	https://www.php-fig.org/psr/psr-7/	(PSR-7: HTTP message interface)
	
5 etapas fáceis para entender os JSON Web Tokens (JWT)
	https://medium.com/vandium-software/5-easy-steps-to-understanding-json-web-tokens-jwt-1164c0adfcec

PHP password_hash
PHP	password_verify

Google: slim framework auth
		slim auth jwt

Slim Framework Criando Microservices 07— Implementando Segurança Básica com HTTP Auth, JWT, e Proxy Scheme
***	https://medium.com/@fidelissauro/slim-framework-criando-microservices-07-implementando-seguran%C3%A7a-b%C3%A1sica-com-http-auth-e-proxy-ed6dd6d517f4
https://medium.com/@fidelissauro/slim-framework-criando-microservices-08-implementando-versionamento-e-controllers-para-as-routes-4572b67716cc

Slim Framework 3 skeleton application has authentication MVC construction
https://discourse.slimframework.com/t/slim-framework-3-skeleton-application-has-authentication-mvc-construction/2088

Slim Framework - Autenticação (com Doctrine)	https://www.webdevbr.com.br/slim-framework-autenticacao
Instalando o Doctrine ORM - Como criar um CRUD com PHP	https://www.webdevbr.com.br/instalando-o-doctrine-orm-como-criar-um-crud-com-php


JSON -> localhost/api-axes-slim/loginappinfra
{
	"usuario":"kalley",
	"senha":"9999"
}
JSON -> localhost/api-axes-slim/usuariolocalizacao
{
	"usuario":"kalley",
	"mobileDevice":"0",
	"mobileTrackingTrace":"0",
	"mobileDeviceId":"0",
	"latitude":123.45678,
	"mobileLastDataReceived":"2018-11-30 18:30:01"
}

Problemas
Dynamically made SQL queries using PDO bound parameters	https://www.sitepoint.com/community/t/dynamically-made-sql-queries-using-pdo-bound-parameters/44550
PDO Dynamic query builder	https://codereview.stackexchange.com/questions/198281/pdo-dynamic-query-builder
How to create a WHERE clause for PDO dynamically	https://phpdelusions.net/pdo_examples/dynamical_where

No Access-Control-Allow-Origin’ header is present on the requested resource. 
-> Setting up CORS	
https://www.slimframework.com/docs/v3/cookbook/enable-cors.html

PHP: upload de um arquivo de um servidor para outro servidor
	solução: fopen ()
	https://stackoverflow.com/questions/35311051/php-upload-file-from-one-server-to-another-server
	http://php.net/manual/pt_BR/function.fopen.php

PHP com SSH
	SSH2 PECL extension ou phpseclib (phpseclib foi o que funcionou, importando pelo composer)
	https://stackoverflow.com/questions/14050231/php-function-ssh2-connect-is-not-working


::Configuração do Servidor

Como configurar Apache Virtual Hosts no Ubuntu 14.04 LTS
https://www.digitalocean.com/community/tutorials/como-configurar-apache-virtual-hosts-no-ubuntu-14-04-lts-pt

Apache vários hosts virtuais no mesmo ip (diferente do url)
https://stackoverflow.com/questions/7660070/apache-multiple-virtual-hosts-on-the-same-same-ipdiffrent-urls

Como Instalar PHP 7 no Ubuntu [via apt-get]
https://gilbertoalbino.com/como-instalar-php-7-no-ubuntu-via-apt-get/

sudo apt-get update
sudo apt-get install curl php-cli php-mbstring git unzip

How To Install and Use Composer on Ubuntu 14.04
https://www.digitalocean.com/community/tutorials/how-to-install-and-use-composer-on-ubuntu-14-04

git clone https://github.com/kalleycorrea/api-axes-slim.git
composer install
composer dumpautoload -o

Apache configuration .htaccess
	http://www.slimframework.com/docs/v3/start/web-servers.html
	Apache e mod_rewrite
	https://docs.slimframework.com/routing/rewrite/
	https://stackoverflow.com/questions/44092907/slim-3-error-500-when-calling-routes-in-parallel
	https://forum.imasters.com.br/topic/561769-configurar-rotas-slim-htaccess/
	https://discourse.slimframework.com/t/routes-and-container-resolution/2453

	https://stackoverflow.com/questions/44897594/last-require-in-index-php-only-work-for-routes
	https://stackoverflow.com/questions/42174121/organize-routes-into-separate-files-not-working-properly-in-slim
	https://github.com/slimphp/Slim/issues/1941



${APACHE_LOG_DIR}
By default, /var/log/apache2/error.log
This can be configured in /etc/php5/apache2/php.ini

How To Set Up mod_rewrite for Apache on Ubuntu 14.04
https://www.digitalocean.com/community/tutorials/how-to-set-up-mod_rewrite-for-apache-on-ubuntu-14-04

Erro:
Uncaught exception 'RuntimeException' with message 'Unexpected data in output buffer. Maybe you have characters before an opening <?php tag?'
	solução: https://stackoverflow.com/questions/37293280/unreasonable-errors-on-php-slim-3-middleware
	added the attribute addContentLengthHeader with the value false in the settings array.
	'addContentLengthHeader' => false,