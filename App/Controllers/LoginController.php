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

        if (is_null($data['usuario']) || empty($data['usuario']) || 
            is_null($data['senha']) || empty($data['senha'])){
            $response = $response->withJson([
                "status" => "error",
                "message" => "Usuario não autenticado"
            ], 401); //401 Unauthorized
            return $response;
        }

        $usuarioDAO = new UsuariosDAO();
        $usuario = $usuarioDAO->getUsuario($data['usuario'], $data['senha']);
        
        if (!empty($usuario)){
            $response = $response->withJson([
                "status" => "success",
                "user" => $usuario
            ], 200); //200 OK
        }else{
            $response = $response->withJson([
                "status" => "error",
                "user" => $usuario
            ], 403); //403 Forbidden
        }

        return $response;
    }
}