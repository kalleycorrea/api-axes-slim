<?php

//namespace Middlewares;

$authAppInfra = function($request, $response, $next){
    //primeira execução: autorização pra acessar a api
    $response->getBody()->write("<br>Autenticando...");
    //executa a função da rota
    $response = $next($request, $response);
    //executa a resposta da requisição se necessário, por exemplo em caso de falha na autorização
    $response->getBody()->write("<br>Falha no Login");

    return $response;
};