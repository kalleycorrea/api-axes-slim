<?php

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\DAO\MySQL\isupergaus\UsuariosDAO;
use App\Models\MySQL\isupergaus\UsuarioModel;

final class UsuarioController
{
    public function updateLocalizacao(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        
        if (is_null($data['usuario']) || empty($data['usuario'])){
            $response = $response->withJson([
                "erro" => "true",
                "message" => "Usuario não informado"
            ], 
            400); //400 Bad Request
            return $response;
        }
        
        $usuarioDAO = new UsuariosDAO();
        $usuario = new UsuarioModel();
        $usuario->setUsuario($data['usuario']);

        if (!is_null($data['mobileDevice'])){
            $usuario->setMobileDevice($data['mobileDevice']);
        }
        if (!is_null($data['mobileTrackingTrace'])){
            $usuario->setMobileTrackingTrace($data['mobileTrackingTrace']);
        }
        if (!is_null($data['mobileDeviceId'])){
            $usuario->setMobileDeviceId($data['mobileDeviceId']);
        }
        if (!is_null($data['latitude'])){
            $usuario->setLatitude($data['latitude']);
        }
        if (!is_null($data['longitude'])){
            $usuario->setLongitude($data['longitude']);
        }
        if (!is_null($data['mobileLastDataReceived'])){
            $usuario->setMobileLastDataReceived($data['mobileLastDataReceived']);
        }
        if (!is_null($data['mobileLastLogin'])){
            $usuario->setMobileLastLogin($data['mobileLastLogin']);
        }

        $usuarioDAO->updateUsuario($usuario);

        $response = $response->withJson([
            "erro" => "false",
            'message' => 'Localizacao do usuario atualizada'
        ]);
        
        return $response;
    }
}