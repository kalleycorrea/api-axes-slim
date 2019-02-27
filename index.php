<?php

//Configurar o autoload psr-4 para o diretório "App" no arquivo composer.json
//depois executar o comando (composer dumpautoload -o) na raiz da aplicação para inserir 
//automaticamente os arquivos do diretório "App" no "require_once" desse arquivo index.php
//conforme a configuração autoload do arquivo composer.json 

require_once './vendor/autoload.php';
require_once './env.php';
require_once './routes.php';