<?php

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\DAO\MySQL\isupergaus\AtendimentosDAO;

final class AtendimentoController
{
    public function listaAtendimentos(Request $request, Response $response, array $args): Response
    {
        //Quando estava fazendo a requisição por GET
        // if (is_null($request->getQueryParams()['usuario']) || empty($request->getQueryParams()['usuario'])){
        //     $response = $response->withJson([
        //         "status" => "error",
        //         "message" => "Usuario não autenticado"
        //     ], 401); //401 Unauthorized
        //     return $response;
        // }

        $data = $request->getParsedBody();
        $atendimentosDAO = new AtendimentosDAO();
        //$atendimento = $atendimentosDAO->getModuloTecnico($request->getQueryParams()['usuario']);
        $atendimento = $atendimentosDAO->getAtendimentos($data['usuario'], $data['tipo'], $data['grupo']);

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