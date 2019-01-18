<?php

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\DAO\MySQL\isupergaus\AtendimentosDAO;

final class AtendimentoController
{
    public function getAtendimentos(Request $request, Response $response, array $args): Response
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
        //$atendimentos = $atendimentosDAO->getModuloTecnico($request->getQueryParams()['usuario']);
        $atendimentos = $atendimentosDAO->getAtendimentos($data['usuario'], $data['tipo'], $data['grupo']);

        if (!empty($atendimentos)){
            $response = $response->withJson($atendimentos, 200); //200 OK
        }else{
            $response = $response->withJson([
                "status" => "error",
                "message" => "no records"
            ], 200);
        }
        return $response;
    }

    public function updateSituacaoOS(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        $atendimentosDAO = new AtendimentosDAO();
        $result = $atendimentosDAO->updateSituacaoOS($data['usuario'], $data['numAtendimento'], 
            $data['situacaoOS'], $data['situacaoOSAnterior']);

        if ($result == TRUE){
            $response = $response->withJson([
                "status" => "success",
                'message' => 'Situação da OS atualizada'
            ], 200);
        }else{
            $response = $response->withJson([
                "status" => "error",
                'message' => 'Situação da OS NAO atualizada'
            ], 200);
        }
        return $response;
    }

    public function getOcorrencias(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        $atendimentosDAO = new AtendimentosDAO();
        $ocorrencias = $atendimentosDAO->getOcorrencias($data['numAtendimento']);

        if (!empty($ocorrencias)){
            $response = $response->withJson($ocorrencias, 200); //200 OK
        }else{
            $response = $response->withJson([
                "status" => "error",
                "message" => "no records"
            ], 200);
        }
        return $response;
    }
}