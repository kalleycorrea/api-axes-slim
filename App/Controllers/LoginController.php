<?php

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\DAO\MySQL\isupergaus\UsuariosDAO;

final class LoginController
{
    public function loginAppInfra(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        $usuarioDAO = new UsuariosDAO();
        $usuario = $usuarioDAO->getUsuario($data['usuario']);
        $response = $response->withJson($usuario);
        return $response;
    }
}