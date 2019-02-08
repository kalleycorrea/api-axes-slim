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
                "status" => "error",
                "message" => "Usuario não autenticado"
            ], 401); //401 Unauthorized
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

        $result = $usuarioDAO->updateUsuario($usuario);
        if ($result == TRUE){
            $response = $response->withJson([
                "status" => "success",
                'message' => 'Localizacao do usuario atualizada'
            ]);
        }else{
            $response = $response->withJson([
                "status" => "error",
                'message' => 'Localizacao do usuario NAO atualizada'
            ]);
        }
        
        return $response;
    }

    public function getGrupoUsuarios(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        $usuarioDAO = new UsuariosDAO();
        $grupoUsuarios = $usuarioDAO->getGrupoUsuarios();

        if (!empty($grupoUsuarios)){
            $response = $response->withJson($grupoUsuarios, 200); //200 OK
        }else{
            $response = $response->withJson([
                "status" => "error",
                "message" => "no records"
            ], 200);
        }
        return $response;
    }

    public function getUsuarios(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        $usuarioDAO = new UsuariosDAO();
        $usuarios = $usuarioDAO->getUsuarios($data['grupo']);

        if (!empty($usuarios)){
            $response = $response->withJson($usuarios, 200); //200 OK
        }else{
            $response = $response->withJson([
                "status" => "error",
                "message" => "no records"
            ], 200);
        }
        return $response;
    }

    public function getEquipes(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        $usuarioDAO = new UsuariosDAO();
        $equipes = $usuarioDAO->getEquipes();

        if (!empty($equipes)){
            $response = $response->withJson($equipes, 200); //200 OK
        }else{
            $response = $response->withJson([
                "status" => "error",
                "message" => "no records"
            ], 200);
        }
        return $response;
    }

    public function getUsuariosEquipe(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        $usuarioDAO = new UsuariosDAO();
        $usuariosEquipe = $usuarioDAO->getUsuariosEquipe();

        if (!empty($usuariosEquipe)){
            $response = $response->withJson($usuariosEquipe, 200); //200 OK
        }else{
            $response = $response->withJson([
                "status" => "error",
                "message" => "no records"
            ], 200);
        }
        return $response;
    }
}