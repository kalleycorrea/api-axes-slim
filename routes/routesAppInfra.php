<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use function src\slimConfiguration;
use App\Controllers\LoginController;
use App\Controllers\UsuarioController;
use App\Controllers\AtendimentoController;

$container = slimConfiguration();

// Serviço de Logging em Arquivo
$container['logger'] = function($container) {
    $logger = new \Monolog\Logger('API-Axes-Slim');
    $file_handler = new \Monolog\Handler\StreamHandler('logs/app.log');
    $logger->pushHandler($file_handler);
    return $logger;
};
// Como usar:
//$this->logger->addInfo('Teste');
//$this->logger->info('INFO', ['Mensagem'=>'Teste']);
//OR
//$logger = $this->get('logger');
//$logger->info('GET', ['Mensagem'=>'Requisição GET']);

/**
 * Application Instance
 */
$app = new \Slim\App($container);

/**
 * Serviço de Logging em Arquivo
 */
/*
$containerAux = $app->getContainer();
$containerAux['logger'] = function($container) {
    $logger = new \Monolog\Logger('API-Axes-Slim');
    $logfile = __DIR__ . '/logs/app.log';
    $stream = new \Monolog\Handler\StreamHandler($logfile, \Monolog\Logger::DEBUG);
    $fingersCrossed = new \Monolog\Handler\FingersCrossedHandler(
        $stream, \Monolog\Logger::INFO);
    $logger->pushHandler($fingersCrossed);
    return $logger;
};
// OR
$containerAux = $app->getContainer();
$containerAux['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], Monolog\Logger::WARNING));
    return $logger;
};

*/

//MIDDLEWARES
// https://www.slimframework.com/docs/v3/concepts/middleware.html

/**
 * Autenticação: HTTP Basic Authentication
 */
//$app->add(new \App\Middlewares\Authentication());

$mwAuthPost = function($request, $response, $next){
    $data = $request->getParsedBody();

    if (is_null($data['usuario']) || empty($data['usuario']) || 
        is_null($data['senha']) || empty($data['senha'])){
        $response = $response->withJson([
            "status" => "error",
            "message" => "Usuario não autenticado"
        ], 401) //401 Unauthorized
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
        return $response;
    }
    return $next($request, $response);
};

$mwAuthGet = function($request, $response, $next){
    $user = $request->getQueryParams()['usuario'];

    if (is_null($user) || empty($user)) {
        $response = $response->withJson([
            "status" => "error",
            "message" => "Usuario não autenticado"
        ], 401) //401 Unauthorized
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
        return $response;
    }
    return $next($request, $response);
};

// Enable CORS
// https://www.slimframework.com/docs/v3/cookbook/enable-cors.html
$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

// ROUTES
/**
 * HTTP Auth - Autenticação minimalista para retornar um JWT
 */
$app->get('/auth', function (Request $request, Response $response) use ($app) {
    $response->getBody()->rewind();
    $bodyString = $response->getBody()->getContents();
    $this->logger->addInfo($bodyString);

    $body = $response->getBody();
    if(empty(json_decode($body))) {
        return $response->withJson(["status" => "Autenticado!"], 200)
        ->withHeader('Content-type', 'application/json');
    } else {
        return $response->withJson(json_decode($body), 200)
        ->withHeader('Content-type', 'application/json');
    }
});

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

$app->post('/loginappaxesos', LoginController::class . ':loginAppAxesOS')->add($mwAuthPost);
$app->post('/getatendimentos', AtendimentoController::class . ':getAtendimentos')->add($mwAuthPost);
$app->post('/updatesituacaoos', AtendimentoController::class . ':updateSituacaoOS')->add($mwAuthPost);
$app->post('/getocorrencias', AtendimentoController::class . ':getOcorrencias')->add($mwAuthPost);
$app->post('/addocorrencia', AtendimentoController::class . ':addOcorrencia')->add($mwAuthPost);
$app->post('/getdadosadicionais', AtendimentoController::class . ':getDadosAdicionais')->add($mwAuthPost);
$app->post('/getdadosadicionaisvalores', AtendimentoController::class . ':getDadosAdicionaisValores')->add($mwAuthPost);
$app->patch('/usuariolocalizacao', UsuarioController::class . ':updateLocalizacao');
// =========================================

// Catch-all route to serve a 404 Not Found page if none of the routes match
// NOTE: make sure this route is defined last
$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function($req, $res) {
    $handler = $this->notFoundHandler; // handle using the default Slim page not found handler
    return $handler($req, $res);
});

$app->run();
