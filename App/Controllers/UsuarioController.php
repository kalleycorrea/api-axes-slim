<?php

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\DAO\UsuariosDAO;

final class UsuarioController
{
    public function updateLocation(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        $usuarioDAO = new UsuariosDAO();
        $result = $usuarioDAO->updateLocation($data);

        if ($result == TRUE){
            $response = $response->withJson([
                "status" => "success"
            ], 200);
        }else{
            $response = $response->withJson([
                "status" => "error"
            ], 502); //502 Bad Gateway
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

    public function addEquipe(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        $usuarioDAO = new UsuariosDAO();
        $result = $usuarioDAO->addEquipe($data);

        if ($result == TRUE){
            $response = $response->withJson([
                "status" => "success",
                'message' => "Equipe Adicionada"
            ], 200);
        }else{
            $response = $response->withJson([
                "status" => "error",
                'message' => "Falha na inserção da Equipe"
            ], 502); //502 Bad Gateway
        }
        return $response;
    }

    public function deleteEquipe(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        $usuarioDAO = new UsuariosDAO();
        $result = $usuarioDAO->deleteEquipe($data);

        if ($result == TRUE){
            $response = $response->withJson([
                "status" => "success",
                'message' => "Equipe Deletada"
            ], 200);
        }else{
            $response = $response->withJson([
                "status" => "error",
                'message' => "Falha na remoção da Equipe"
            ], 502); //502 Bad Gateway
        }
        return $response;
    }

    public function getUsuariosSemEquipe(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        $usuarioDAO = new UsuariosDAO();
        $usuariosSemEquipe = $usuarioDAO->getUsuariosSemEquipe();

        if (!empty($usuariosSemEquipe)){
            $response = $response->withJson($usuariosSemEquipe, 200); //200 OK
        }else{
            $response = $response->withJson([
                "status" => "error",
                "message" => "no records"
            ], 200);
        }
        return $response;
    }

    public function addUsuarioEquipe(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        $usuarioDAO = new UsuariosDAO();
        $result = $usuarioDAO->addUsuarioEquipe($data);

        if ($result == TRUE){
            $response = $response->withJson([
                "status" => "success",
                'message' => "Usuário Adicionado na Equipe"
            ], 200);
        }else{
            $response = $response->withJson([
                "status" => "error",
                'message' => "Falha na inserção do Usuário"
            ], 502); //502 Bad Gateway
        }
        return $response;
    }

    public function deleteUsuarioEquipe(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        $usuarioDAO = new UsuariosDAO();
        $result = $usuarioDAO->deleteUsuarioEquipe($data);

        if ($result == TRUE){
            $response = $response->withJson([
                "status" => "success",
                'message' => "Usuário Removido da Equipe"
            ], 200);
        }else{
            $response = $response->withJson([
                "status" => "error",
                'message' => "Falha na remoção do Usuário"
            ], 502); //502 Bad Gateway
        }
        return $response;
    }
}