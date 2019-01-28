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
                'message' => "Situação da OS atualizada"
            ], 200);
        }else{
            $response = $response->withJson([
                "status" => "error",
                'message' => "Situação da OS NÃO atualizada"
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

    public function addOcorrencia(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        $atendimentosDAO = new AtendimentosDAO();
        $result = $atendimentosDAO->addOcorrencia($data['usuario'], $data['numAtendimento'], $data['descricao']);

        if ($result == TRUE){
            $response = $response->withJson([
                "status" => "success",
                'message' => "Ocorrência Inserida"
            ], 200);
        }else{
            $response = $response->withJson([
                "status" => "error",
                'message' => "Falha na inserção da Ocorrência"
            ], 502); //502 Bad Gateway
        }
        return $response;
    }

    public function getDadosAdicionais(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        $atendimentosDAO = new AtendimentosDAO();
        $dadosAdicionais = $atendimentosDAO->getDadosAdicionais();
        $dadosAdicionaisVal = $atendimentosDAO->getDadosAdicionaisValores($data['numAtendimento']);

        if (!empty($dadosAdicionais)){
            //$valores = $this->transformaRowsEmColunas($dadosAdicionais, $dadosAdicionaisVal);

            // Atribui valor, se houver, ao array $dadosAdicionais que possui todos os Dados Adicionais do contrato, 
            // mesmo que o dado não tenha sido preenchido.
            // O array $dadosAdicionaisVal possui somente os dados adicionais que já possuem algum valor.
            for ($i=0; $i < count($dadosAdicionais); $i++) { 
                $valorEncontrado = '';
                $idEncontrado = '';
                foreach ($dadosAdicionaisVal as $rowsVal) {
                    if ($rowsVal['Nome'] == $dadosAdicionais[$i]['Nome']) {
                        $valorEncontrado = $rowsVal['Valor'];
                        $idEncontrado = $rowsVal['Id'];
                    }
                }
                $dadosAdicionais[$i]['Valor'] = $valorEncontrado;
                $dadosAdicionais[$i]['Id'] = $idEncontrado;
            }
            $response = $response->withJson($dadosAdicionais, 200); //200 OK
        }else{
            $response = $response->withJson([
                "status" => "error",
                "message" => "no records"
            ], 200);
        }
        return $response;
    }

    private function transformaRowsEmColunas($tableCampos, $tableValores) {
        $result = [];
        foreach ($tableCampos as $rowsCampo) { 
            $valorEncontrado = '';
            foreach ($tableValores as $rowsVal) {
                if ($rowsVal['Nome'] == $rowsCampo['Nome']) {
                    $valorEncontrado = $rowsVal['Valor'];
                }
            }
            $result += [$rowsCampo['Nome'] => $valorEncontrado];            
        }
        return $result;
    }

    public function saveDadosAdicionais(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        $atendimentosDAO = new AtendimentosDAO();
        $result = $atendimentosDAO->saveDadosAdicionais($data);

        if ($result == TRUE){
            $response = $response->withJson([
                "status" => "success",
                'message' => "Dados Adicionais Atualizados"
            ], 200);
        }else{
            $response = $response->withJson([
                "status" => "error",
                'message' => "Falha na inserção da Ocorrência"
            ], 502); //502 Bad Gateway
        }
        return $response;
    }

    public function saveEnderecoInstalacao(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        $atendimentosDAO = new AtendimentosDAO();
        $result = $atendimentosDAO->saveEnderecoInstalacao($data);

        if ($result == TRUE){
            $response = $response->withJson([
                "status" => "success",
                'message' => "Endereço de Instalação atualizado"
            ], 200);
        }else{
            $response = $response->withJson([
                "status" => "error",
                'message' => "Endereço de Instalação NÃO atualizado"
            ], 200);
        }
        return $response;
    }

    public function getAnexos(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        $atendimentosDAO = new AtendimentosDAO();
        $anexos = $atendimentosDAO->getAnexos($data['numAtendimento']);

        if (!empty($anexos)){
            $response = $response->withJson($anexos, 200); //200 OK
        }else{
            $response = $response->withJson([
                "status" => "error",
                "message" => "no records"
            ], 200);
        }
        return $response;
    }

    public function addAnexos(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        $atendimentosDAO = new AtendimentosDAO();
        $result = $atendimentosDAO->addAnexos($data);

        if ($result == TRUE){
            $response = $response->withJson([
                "status" => "success",
                'message' => "Anexo Inserido"
            ], 200);
        }else{
            $response = $response->withJson([
                "status" => "error",
                'message' => "Falha na inserção do Anexo"
            ], 502); //502 Bad Gateway
        }
        return $response;
    }
}