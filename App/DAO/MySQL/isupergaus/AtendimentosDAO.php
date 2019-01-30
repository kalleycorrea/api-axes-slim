<?php

namespace App\DAO\MySQL\isupergaus;
use phpseclib\Net\SSH2;
use phpseclib\Net\SFTP;

class AtendimentosDAO extends Conexao
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getAtendimentos($pUsuario, $pTipo, $pGrupo): array
    {
        $strSQL = "SELECT a.Numero NumAtendimento, a.Protocolo, c.Codigo CodCliente, 
        c.Nome Cliente, c.Sigla Apelido, a.Contrato, p.DescricaoComercial Plano, t.Descricao Topico, 
        a.Topico CodTopico, a.Prioridade, a.Assunto, a.Solucao, 
        date_format(concat(a.Data_AB,' ',a.Hora_AB), '%d/%m/%Y %H:%i') Abertura,
        replace(isupergaus.rbx_sla(a.Numero, 'N'),'?','ú') SLA, 
        g.Nome GrupoCliente, 
        if (e.Endereco is null, 'N', 'S') as PossuiEnderecoInstalacao, 
        if (e.Endereco is null, c.Endereco, e.Endereco) as Endereco, 
        if (e.Endereco is null, c.Numero, e.Numero) as Numero,
        if (e.Endereco is null, c.Complemento, e.Complemento) as Complemento,
        if (e.Endereco is null, c.Bairro, e.Bairro) as Bairro,
        if (e.Endereco is null, c.CEP, e.CEP) as CEP,
        if (e.Endereco is null, c.Cidade, e.Cidade) as Cidade,
        if (e.Endereco is null, c.UF, e.UF) as Estado,
        if (e.Endereco is null, c.MapsLat, e.MapsLat) as MapsLat,
        if (e.Endereco is null, c.MapsLng, e.MapsLng) as MapsLng,
        a.Usu_Abertura, 
        a.Usu_Designado, 
        a.Usuario_BX, 
        a.Grupo_Designado,
        if (a.Usu_Designado is null, 'Técnico', a.Usu_Designado) as Equipe, 
        ct.Situacao,
        case ct.Situacao 
                when 'A' then 'Ativo' 
                when 'B' then 'Bloqueado' 
                when 'C' then 'Cancelado' 
                when 'E' then 'Aguard. Instalacao' 
                when 'I' then 'Em Instalacao' 
                when 'S' then 'Suspenso'
                else 'Inativo' END as DescSituacao, 
        ifnull(a.SituacaoOS,' ') SituacaoOS, 
        case ifnull(a.SituacaoOS,' ') 
                when ' ' then 'Não Criada'
                when 'A' then 'A Caminho' 
                when 'B' then 'Abortada' 
                when 'C' then 'Concluída' 
                when 'E' then 'Em Execução' 
                when 'F' then 'Na Fila' 
                when 'P' then 'Pausada'
                else ' ' END as DescSituacaoOS 
        FROM isupergaus.Atendimentos a 
        left join isupergaus.Clientes c on a.Cliente = c.Codigo 
        left join isupergaus.ClienteGrupo g on c.Grupo = g.Codigo 
        left join isupergaus.Contratos ct on a.Contrato = ct.Numero 
        left join isupergaus.Planos p on ct.Plano = p.Codigo
        left join isupergaus.ContratosEndereco e on ct.Numero = e.Contrato and e.Tipo = 'I' 
        left join isupergaus.usuarios u on a.Usu_Designado = u.usuario 
        left join isupergaus.UsuariosGrupoSetor ug on a.Grupo_Designado = ug.id 
        left join isupergaus.AtendTopicos t on a.Topico = t.Codigo 
        WHERE a.Situacao = 'A'";

        //Atendimentos visualizados pelo técnico
        $whereTecnico = " and (a.Usu_Designado = ".$pUsuario." or a.Grupo_Designado = ".$pGrupo.")";
        //Atendimentos visualizados pelo Gestor
        $whereGestor = " and (a.Usu_Designado in (select u2.usuario from isupergaus.usuarios u2 where u2.idgrupo = ".$pGrupo.") or a.Grupo_Designado = ".$pGrupo.")";
        $where = ($pTipo == 'T') ? $whereTecnico : $whereGestor;

        $orderBy = " order by a.Prioridade, date_format(concat(a.Data_AB,' ',a.Hora_AB), '%d/%m/%Y %H:%i:%s')";

        $statement = $this->pdoRbx->prepare($strSQL . $where . $orderBy);
        //$statement->bindParam(':usuario', $pUsuario, \PDO::PARAM_STR);
        //$statement->bindParam(':grupo', $pGrupo, \PDO::PARAM_STR);
        $statement->execute();
        $atendimentos = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $atendimentos;
    }

    public function updateSituacaoOS($pUsuario, $pNumAtendimento, $pSituacaoOS, $pSituacaoAnterior): bool
    {
        $result = FALSE;

        $statement = $this->pdoRbx
            ->prepare('UPDATE Atendimentos SET 
                SituacaoOS = :situacaoOS 
                WHERE Numero = :numAtendimento;');
        $result = $statement->execute([
            'situacaoOS' => $pSituacaoOS,
            'numAtendimento' => $pNumAtendimento 
            ]);
        if ($result == TRUE) {
            // Update Ocorrências
            $descricao='';
            $descSituacao = $this->getSituacaoOS($pSituacaoOS);
            $descSituacaoAnterior = $this->getSituacaoOS($pSituacaoAnterior);
            if (empty($descSituacaoAnterior)) {
                if (!empty($descSituacao)) {
                    $descricao = "Situação da OS alterada para ".$descSituacao;
                }
            } else {
                if (!empty($descSituacao)) {
                    $descricao = "Situação da OS alterada de ".$descSituacaoAnterior." para ".$descSituacao;
                }
            }
            $statement2 = $this->pdoRbx
            ->prepare("INSERT INTO AtendUltAlteracao (Atendimento, Usuario, Descricao, Data, Modo) 
                        VALUES (:atendimento, :usuario, :descricao, now(), 'A')");
            $result2 = $statement2->execute([
                'atendimento' => $pNumAtendimento,
                'usuario' => $pUsuario, 
                'descricao' => $descricao 
                ]);
        }
        return $result;
    }

    private function getSituacaoOS($pSituacaoOS)
    {
        $result='';
        switch ($pSituacaoOS) {
            case '':
            case ' ':
                $result='';
                break;
            case 'A':
                $result='A Caminho';
                break;
            case 'B':
                $result='Abortada';
                break;
            case 'C':
                $result='Concluída';
                break;
            case 'E':
                $result='Em Execução';
                break;
            case 'F':
                $result='Na Fila';
                break;
            case 'P':
                $result='Pausada';
                break;
        }
        return $result;
    }

    public function getOcorrencias($pNumAtendimento): array
    {
        $strSQL = "select Usuario, Modo, Descricao, 
        -- replace(replace(replace(Descricao, '<b>', ''),'</b>',''),'<BR>','') Descricao,
        if(Modo='A', 'Automático', 'Manual') as DescModo, 
        date_format(Data, '%d/%m/%Y %H:%i:%s') Data 
        from AtendUltAlteracao where Atendimento = ".$pNumAtendimento." order by Id desc";

        $statement = $this->pdoRbx->prepare($strSQL);
        $statement->execute();
        $ocorrencias = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $ocorrencias;
    }

    public function addOcorrencia($pUsuario, $pNumAtendimento, $pDescricao): bool
    {
        $result = FALSE;
        $statement = $this->pdoRbx
        ->prepare("INSERT INTO AtendUltAlteracao (Atendimento, Usuario, Descricao, Data, Modo) 
                    VALUES (:atendimento, :usuario, :descricao, now(), 'M')");
        $result = $statement->execute([
            'atendimento' => $pNumAtendimento,
            'usuario' => $pUsuario, 
            'descricao' => $pDescricao 
            ]);
        return $result;
    }

    public function getDadosAdicionais(): array
    {
        $strSQL = "select a.Codigo CodigoCampo, a.Nome, a.TipoDado, a.Lista, a.Tabela, 
        '' Valor, '' Id 
        -- a.ListaDesc, a.Tamanho, a.Obrigatorio, a.Ajuda 
        from isupergaus.CamposComplementares a 
        where a.Tabela = 'Contratos' and a.Codigo < 45 order by a.Codigo";

        $statement = $this->pdoRbx->prepare($strSQL);
        $statement->execute();
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
    }

    public function getDadosAdicionaisValores($pNumAtendimento): array
    {
        $strSQL = "select a.Codigo CodigoCampo, a.Nome, b.Id, b.Chave Contrato, b.Valor 
        from CamposComplementares a 
        left join CamposComplementaresValores b on a.Codigo = b.Complemento 
        left join Contratos c on b.Chave = c.Numero 
        left join Atendimentos d on b.Chave = d.Contrato 
        where a.Tabela = 'Contratos' and a.Codigo < 45 and d.Numero = ".$pNumAtendimento." 
        order by a.Codigo";

        $statement = $this->pdoRbx->prepare($strSQL);
        $statement->execute();
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
    }

    public function saveDadosAdicionais($pDadosAdicionais): bool
    {
        $result = FALSE;
        $contrato = $pDadosAdicionais[0]['contrato'] ?? '';

        if (is_null($contrato) || empty($contrato)) {
            return FALSE;
        }

        foreach ($pDadosAdicionais as $rows) { 
            if (isset($rows['CodigoCampo']) && !empty($rows['CodigoCampo'])) {

                if (empty($rows['Id'])) {
                    $statement = $this->pdoRbx
                    ->prepare("INSERT INTO CamposComplementaresValores (Complemento, Tabela, Chave, Valor) 
                                VALUES (:complemento, :tabela, :chave, :valor)");
                    $result = $statement->execute([
                        'complemento' => $rows['CodigoCampo'], 
                        'tabela' => $rows['Tabela'], 
                        'chave' => $contrato, 
                        'valor' => $rows['Valor'] 
                        ]);
                } else {
                    $statement = $this->pdoRbx
                    ->prepare('UPDATE CamposComplementaresValores SET 
                        Valor = :valor 
                        WHERE id = :id;');
                    $result = $statement->execute([
                    'valor' => $rows['Valor'],
                    'id' => $rows['Id'] 
                    ]);
                }
            }
        }
        return $result;
    }

    public function saveEnderecoInstalacao($data): bool
    {
        $result = FALSE;
        $contrato = $data['contrato'] ?? '';
        $operacao = $data['operacao'];

        if (is_null($contrato) || empty($contrato)) {
            return FALSE;
        }
        if ($operacao == 'insert') {
            $statement = $this->pdoRbx
            ->prepare("INSERT INTO ContratosEndereco (Cliente,Contrato,Tipo,Cobranca,Pais,Endereco,Numero,Bairro,Complemento,Cidade,UF,CEP,MapsLat,MapsLng) 
                        VALUES (:cliente,:contrato,:tipo,:cobranca,:pais,:endereco,:numero,:bairro,:complemento,:cidade,:uf,:cep,:mapsLat,:mapsLng)");
            $result = $statement->execute([
                'cliente' => $data['cliente'],
                'contrato' => $data['contrato'],
                'tipo' => $data['tipo'],
                'cobranca' => $data['cobranca'],
                'pais' => $data['pais'],
                'endereco' => $data['endereco'],
                'numero' => $data['numero'],
                'bairro' => $data['bairro'],
                'complemento' => $data['complemento'],
                'cidade' => $data['cidade'],
                'uf' => $data['uf'],
                'cep' => $data['cep'],
                'mapsLat' => $data['mapsLat'],
                'mapsLng' => $data['mapsLng']
                ]);
        } else {
            $statement = $this->pdoRbx
            ->prepare("UPDATE ContratosEndereco SET 
                MapsLat = :mapsLat, 
                MapsLng = :mapsLng 
                WHERE Tipo='I' and Contrato = :contrato");
            $result = $statement->execute([
            'mapsLat' => $data['mapsLat'],
            'mapsLng' => $data['mapsLng'],
            'contrato' => $contrato 
            ]);
        }
        return $result;
    }

    public function getAnexos($pNumAtendimento): array
    {
        $urlDocRbx = getenv('URL_IMG_DOC_RBX');
        $strSQL = "select concat('".$urlDocRbx."', Arquivo) as imagem, 
            descricao from Arquivo where Tipo='A' and Codigo=".$pNumAtendimento." order by Id desc";

        $statement = $this->pdoRbx->prepare($strSQL);
        $statement->execute();
        $anexos = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $anexos;
    }

    public function addAnexos($data): bool
    {
        $result = FALSE;
        $statement = $this->pdoRbx
        ->prepare("INSERT INTO Arquivo (Codigo, Usuario, Arquivo, NomeArquivo, Descricao, Tipo, Visivel) 
                    VALUES (:codigo, :usuario, :arquivo, :nomeArquivo, :descricao, 'A', 'N')");
        $result = $statement->execute([
            'codigo' => $data['numAtendimento'],
            'usuario' => $data['usuario'], 
            'arquivo' => $data['nomeArquivo'],
            'nomeArquivo' => $data['nomeArquivo'], 
            'descricao' => $data['descricao']
            ]);
        if ($result == TRUE) {
            // Salva o arquivo no servidor do Routerbox
            $hostRbx = getenv('MYSQL_HOST_RBX');
            $userSSHRbx = getenv('SSH_USER_RBX');
            $passSSHRbx = getenv('SSH_PASSWORD_RBX');
            $dirDocRbx = getenv('DIR_IMG_DOC_RBX');

            $sftp = new SFTP($hostRbx);
            if (!$sftp->login($userSSHRbx, $passSSHRbx)) {
                return FALSE;
            }
            // Decodifica dados codificados com MIME base64 para binário
            $binaryImage = base64_decode($data['base64Image']);
            $sftp->chdir($dirDocRbx);
            $sftp->put($data['nomeArquivo'], $binaryImage);

            // Adiciona Ocorrência
            $statement2 = $this->pdoRbx
            ->prepare("INSERT INTO AtendUltAlteracao (Atendimento, Usuario, Descricao, Data, Modo) 
                        VALUES (:atendimento, :usuario, 'Inclusão de anexo', now(), 'A')");
            $result2 = $statement2->execute([
                'atendimento' => $data['numAtendimento'],
                'usuario' => $data['usuario']
                ]);
        }
        return $result;
    }

    private function getAssinatura($pNumAtendimento, $nomeArquivo): array
    {
        $strSQL = "select Id from Arquivo where Tipo='A' and NomeArquivo='".$nomeArquivo."' and Codigo=".$pNumAtendimento;
        $statement = $this->pdoRbx->prepare($strSQL);
        $statement->execute();
        $assinatura = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $assinatura;
    }

    public function addAssinatura($data): bool
    {
        $result = FALSE;
        $nomeArquivo = $data['numAtendimento'].'-Assinatura.jpg';
        $assinatura = $this->getAssinatura($data['numAtendimento'], $nomeArquivo);

        if (empty($assinatura)) {
            // Insert Assinatura
            $statement = $this->pdoRbx
            ->prepare("INSERT INTO Arquivo (Codigo, Usuario, Arquivo, NomeArquivo, Descricao, Tipo, Visivel) 
                        VALUES (:codigo, :usuario, :arquivo, :nomeArquivo, 'Assinatura do Cliente', 'A', 'N')");
            $result = $statement->execute([
                'codigo' => $data['numAtendimento'],
                'usuario' => $data['usuario'], 
                'arquivo' => $nomeArquivo,
                'nomeArquivo' => $nomeArquivo
                ]);
        }else {
            // Update Assinatura
            $result = TRUE;
        }        

        if ($result == TRUE) {
            // Salva o arquivo no servidor do Routerbox
            $hostRbx = getenv('MYSQL_HOST_RBX');
            $userSSHRbx = getenv('SSH_USER_RBX');
            $passSSHRbx = getenv('SSH_PASSWORD_RBX');
            $dirDocRbx = getenv('DIR_IMG_DOC_RBX');

            $sftp = new SFTP($hostRbx);
            if (!$sftp->login($userSSHRbx, $passSSHRbx)) {
                return FALSE;
            }
            // Decodifica dados codificados com MIME base64 para binário
            $binaryImage = base64_decode($data['base64Image']);
            $sftp->chdir($dirDocRbx);
            $sftp->put($nomeArquivo, $binaryImage);

            // Adiciona Ocorrência
            if (empty($assinatura)) {
                $statement2 = $this->pdoRbx
                ->prepare("INSERT INTO AtendUltAlteracao (Atendimento, Usuario, Descricao, Data, Modo) 
                            VALUES (:atendimento, :usuario, 'Inclusão de Assinatura', now(), 'M')");
                $result2 = $statement2->execute([
                    'atendimento' => $data['numAtendimento'],
                    'usuario' => $data['usuario']
                    ]);
            } else {
                $statement2 = $this->pdoRbx
                ->prepare("INSERT INTO AtendUltAlteracao (Atendimento, Usuario, Descricao, Data, Modo) 
                            VALUES (:atendimento, :usuario, 'Alteração de Assinatura', now(), 'M')");
                $result2 = $statement2->execute([
                    'atendimento' => $data['numAtendimento'],
                    'usuario' => $data['usuario']
                    ]);
            }
        }
        return $result;
    }

}
