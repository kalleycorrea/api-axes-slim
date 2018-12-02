<?php

use function src\slimConfiguration;
use App\Controllers\LoginController;
use App\Controllers\UsuarioController;

$app = new \Slim\App(slimConfiguration());

//$app->add($authAppInfra);
//or
/*
$app->add(function($request, $response, $next){
    //primeira execução: autorização pra acessar a api
    $response->getBody()->write("<br>Autenticando...");
    //executa a função da rota
    $response = $next($request, $response);
    //executa a resposta da requisição se necessário, por exemplo em caso de falha na autorização
    $response->getBody()->write("<br>Falha no Login");
    return $response;
});
*/

// =========================================

$app->get('/', function($request, $response, $args){
    //Psr\Http\Message\ServerRequestInterface as Request;
    //Request $request
    $param = $_GET['param'] ?? ''; // url: api-axes-slim?param=10&param2=5
    //$param = $request->getQueryParams()['param'] ?? 0; // url: api-axes-slim?param=10&param2=5
    //$param = $args['param'] ?? ''; // url: api-axes-slim/{param}
    return $response->getBody()->write("API Axes com Slim Framework {$param}");
});

$app->post('/', function($request, $response, $args){
    //$params = $request->getParsedBody();
    //$param = $params['param'] ?? '';
    //or
    $param = $request->getParam('param') ?? '...';
    
    $response = $response->withJson([
        "message" => "API Axes com Slim Framework"
    ]);

    return $response;
});

$app->post('/loginappinfra', LoginController::class . ':loginAppInfra');
$app->patch('/usuariolocalizacao', UsuarioController::class . ':updateLocalizacao');

// =========================================

$app->run();
