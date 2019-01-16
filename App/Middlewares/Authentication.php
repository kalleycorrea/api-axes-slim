<?php

/*
 * HTTP Basic Authentication middleware
 */

namespace App\Middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\DAO\MySQL\isupergaus\UsuariosDAO;

class Authentication
{
    /**
     * HTTP Basic Authentication middleware to api-axes-slim
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        $logger = new \Monolog\Logger('API-Axes-Slim');
        $file_handler = new \Monolog\Handler\StreamHandler('logs/app.log');
        $logger->pushHandler($file_handler);
        // Como usar:
        //$logger->addInfo('Teste');
        //$logger->info('INFO', ['Mensagem'=>'Teste']);

        // Usar autenticação através do Header Authorization, 
        // mas não funcionou com o Ionic somente com o Postman  
        $server_params = $request->getServerParams();
        $user = false;
        $password = false;

        $logger->info('server_params', $server_params);

        // If using PHP in CGI mode.
        if (isset($server_params["HTTP_AUTHORIZATION"])) {
            if (preg_match("/Basic\s+(.*)$/i", $server_params["HTTP_AUTHORIZATION"], $matches)) {
                list($user, $password) = explode(":", base64_decode($matches[1]), 2);
            }
        } else {
            if (isset($server_params["PHP_AUTH_USER"])) {
                $user = $server_params["PHP_AUTH_USER"];
            }
            if (isset($server_params["PHP_AUTH_PW"])) {
                $password = $server_params["PHP_AUTH_PW"];
            }
        }
        $params = ["user" => $user, "password" => $password];

        if (is_null($user) || empty($user) || is_null($password) || empty($password)) {
            $response = $response->withJson([
                "status" => "error",
                "message" => "Usuario não autenticado"
            ], 401)
            ->withHeader('Content-type', 'application/json');
            //->withStatus(401)
            // Code 401 Unauthorized
            return $response;
        } else {
            $usuarioDAO = new UsuariosDAO();
            $usuario = $usuarioDAO->getUsuario($user, $password);
            
            if (!empty($usuario)){
                $response = $response->withJson([
                    "status" => "success",
                    "user" => $usuario
                ], 200); //200 OK
            }else{
                $response = $response->withJson([
                    "status" => "error",
                    "message" => "Usuário ou senha incorreto"
                ], 403); //403 Forbidden
            }
            //return $response;
        }

        //return $response->withJson(["status" => "Autenticado", "Authorization" => $params], 200)
        //->withHeader('Content-type', 'application/json');

        return $next($request, $response);
    }
}

/*
<?php
 **
 * https://www.slimframework.com/docs/v3/concepts/middleware.html
 * Example middleware closure
 *
 * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
 * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
 * @param  callable                                 $next     Next middleware
 *
 * @return \Psr\Http\Message\ResponseInterface
 *
function ($request, $response, $next) {
    $response->getBody()->write('BEFORE');
    $response = $next($request, $response);
    $response->getBody()->write('AFTER');

    return $response;
};
*/