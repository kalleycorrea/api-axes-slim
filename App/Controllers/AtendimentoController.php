<?php

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\DAO\MySQL\isupergaus\AtendimentosDAO;

final class AtendimentoController
{
    public function getAtendimentoTecnico(Request $request, Response $response, array $args): Response
    {
        if (is_null($request->getQueryParams()['usuario']) || empty($request->getQueryParams()['usuario'])){
            $response = $response->withJson([
                "status" => "error",
                "message" => "Usuario não autenticado"
            ], 401); //401 Unauthorized
            return $response;
        }

        $atendimentosDAO = new AtendimentosDAO();
        $atendimento = $atendimentosDAO->getModuloTecnico($request->getQueryParams()['usuario']);
        
        if (!empty($atendimento)){
            $response = $response->withJson($atendimento, 200); //200 OK
        }else{
            $response = $response->withJson([
                "status" => "error",
                "message" => "no records"
            ], 200);
        }

        return $response;
    }
}