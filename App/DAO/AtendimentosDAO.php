<?php

namespace App\DAO;
use phpseclib\Net\SSH2;
use phpseclib\Net\SFTP;
use App\DAO\UsuariosDAO;

class AtendimentosDAO extends Conexao
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getAtendimentos($pUsuario, $pTipo, $pGrupo, $pFiltroBusca): array
    {
        $strSQL = "SELECT a.Numero NumAtendimento, a.Protocolo, c.Codigo CodCliente, 
        c.Nome Cliente, c.Sigla Apelido, c.Tipo TipoPessoa, a.Tipo, c.TelCelular, c.TelComercial, c.TelResidencial, a.Contrato, 
        p.DescricaoComercial Plano, t.Descricao DescTopico, a.Topico, a.Prioridade, a.Assunto, a.Solucao, 
        date_format(concat(a.Data_AB,' ',a.Hora_AB), '%d/%m/%Y %H:%i') Abertura,
        date_format(concat(a.Data_Prox,' ',a.Hora_Prox), '%d/%m/%Y %H:%i') Agendamento,
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
        ug.Grupo DescGrupoDesignado,
        '' as equipe,
        '' as nomeequipe,
        ct.Situacao as SituacaoCliente,
        case ct.Situacao 
                when 'A' then 'Ativo' 
                when 'B' then 'Bloqueado' 
                when 'C' then 'Cancelado' 
                when 'E' then 'Aguard. Instalacao' 
                when 'I' then 'Em Instalacao' 
                when 'S' then 'Suspenso'
                else 'Inativo' END as DescSituacaoCliente, 
        ifnull(a.SituacaoOS,' ') SituacaoOS, 
        case ifnull(a.SituacaoOS,' ') 
                when ' ' then 'Não Criada'
                when 'A' then 'A Caminho' 
                when 'B' then 'Abortada' 
                when 'C' then 'Concluída' 
                when 'E' then 'Em Execução' 
                when 'F' then 'Na Fila' 
                when 'P' then 'Pausada'
                else ' ' END as DescSituacaoOS, 
        a.Situacao as SituacaoAtendimento,
        case a.Situacao 
                when 'A' then 'Em Andamento' 
                when 'E' then 'Em Espera' 
                when 'F' then 'Encerrado' 
                else 'Situação Não Informada' END as DescSituacaoAtendimento, 
        '' as MTBFObrigatorio 
        FROM isupergaus.Atendimentos a 
        left join isupergaus.Clientes c on a.Cliente = c.Codigo 
        left join isupergaus.ClienteGrupo g on c.Grupo = g.Codigo 
        left join isupergaus.Contratos ct on a.Contrato = ct.Numero 
        left join isupergaus.Planos p on ct.Plano = p.Codigo
        left join isupergaus.ContratosEndereco e on ct.Numero = e.Contrato and e.Tipo = 'I' 
        left join isupergaus.usuarios u on a.Usu_Designado = u.usuario 
        left join isupergaus.UsuariosGrupoSetor ug on a.Grupo_Designado = ug.id 
        left join isupergaus.AtendTopicos t on a.Topico = t.Codigo 
        WHERE ";
        
        $where = '';
        if (empty($pFiltroBusca)) {
            $where = "a.Situacao IN ('A','E','') and ";
            //Atendimentos visualizados pelo Gestor (todos os atendimentos do grupo e dos usuários do grupo)
            if ($pTipo == 'G') {
                $where = $where . "(a.Usu_Designado in (select u2.usuario from isupergaus.usuarios u2 where u2.idgrupo = ".$pGrupo.") or a.Grupo_Designado = ".$pGrupo.")";
            } else {
                $usuarios = $this->getUsuariosByEquipe($pUsuario);
                $strUsuarios = "'".implode("','",$usuarios)."'";
                if (!empty($usuarios)) {
                    //Atendimentos visualizados pela equipe (atendimentos designados aos membros da equipe e ao grupo)
                    $where = $where . "(a.Usu_Designado in (".$strUsuarios.") or a.Grupo_Designado = ".$pGrupo.")";
                } else {
                    //Atendimentos visualizados por usuários sem equipe (atendimentos designados a esse usuário e ao grupo)
                    $where = $where ."(a.Usu_Designado = '".$pUsuario."' or a.Grupo_Designado = ".$pGrupo.")";
                }
            }
        } else {
            if (ctype_digit($pFiltroBusca)) {
                $where = "a.Numero = ".$pFiltroBusca; 
            } else {
                $tipoBusca = substr($pFiltroBusca, 0, 1);
                switch ($tipoBusca) {
                    case '@':
                        // Nome Cliente
                        $where="c.Nome like '%".substr($pFiltroBusca, 1)."%' OR c.Sigla like '%".substr($pFiltroBusca, 1)."%'";
                        break;
                    case '#':
                        // Tópico
                        $where="a.Situacao IN ('A','E','') AND t.Descricao like '%".substr($pFiltroBusca, 1)."%'";
                        break;
                    case '$':
                        // Nome Usuário (Atendimentos Abertos)
                        $where="a.Situacao IN ('A','E','') AND (a.Usu_Designado like '".substr($pFiltroBusca, 1)."%' OR a.Usuario_BX like '".substr($pFiltroBusca, 1)."%')";
                        break;
                    case '%':
                        // Nome Usuário (Atendimentos Encerrados)
                        $where="a.Situacao = 'F' AND (a.Usu_Designado like '".substr($pFiltroBusca, 1)."%' OR a.Usuario_BX like '".substr($pFiltroBusca, 1)."%')";
                        break;
                    case '*':
                        // Não Designado ou Designado somente para o grupo
                        $where="a.Situacao IN ('A','E','') AND a.Usu_Designado = ''";
                        break;
                    case '?':
                        // Situação Não Informada
                        $where="a.Situacao = ''";
                        break;
                    default:
                        return [];
                }
            }
        }

        $orderBy = " order by a.Prioridade, date_format(concat(a.Data_AB,' ',a.Hora_AB), '%d/%m/%Y %H:%i:%s')";

        $statement = $this->pdoRbx->prepare($strSQL . $where . $orderBy);
        //$statement->bindParam(':usuario', $pUsuario, \PDO::PARAM_STR);
        //$statement->bindParam(':grupo', $pGrupo, \PDO::PARAM_STR);
        $statement->execute();
        $atendimentos = $statement->fetchAll(\PDO::FETCH_ASSOC);

        // Set Equipe do Usuário Designado e MTBF
        if (!empty($atendimentos)){
            $usuarioDAO = new UsuariosDAO();
            for ($i=0; $i < count($atendimentos); $i++) {
                $equipe = $usuarioDAO->getEquipeByUsuario($atendimentos[$i]['Usu_Designado']);
                if (!empty($equipe)) {
                    $atendimentos[$i]['equipe'] = $equipe[0]['equipe'];
                    $atendimentos[$i]['nomeequipe'] = $equipe[0]['nome'];
                }
                // MTBF
                $atendimentos[$i]['MTBFObrigatorio'] = $this->getObrigatoriedadeMTBF($atendimentos[$i]['Topico']);
            }
        }
        return $atendimentos;
    }

    private function getUsuariosByEquipe($usuario): array
    {
        $statement = $this->pdoAxes->prepare("select u.usuario from usuarios u 
            where u.equipe = (select equipe from usuarios where usuario='".$usuario."')");
        $statement->execute();
        $result = $statement->fetchAll(\PDO::FETCH_COLUMN);
        return $result;
    }

    private function getObrigatoriedadeMTBF($topico)
    {
        $result = 'N';
        $statement = $this->pdoRbx->prepare("select Filtro from CamposComplementares 
        where Tabela='Atendimentos' and Codigo=38");
        $statement->execute();
        $topicos = $statement->fetchAll(\PDO::FETCH_COLUMN);
        if (!empty($topicos)) {
            $arrayTopicos = explode(",", $topicos[0]);
            if (in_array($topico, $arrayTopicos)){
                $result = 'S';
            }
        }
        return $result;
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

    public function updateSituacaoAtendimento($data): bool
    {
        $result = FALSE;

        $statement = $this->pdoRbx
            ->prepare('UPDATE Atendimentos SET 
                Situacao = :situacao 
                WHERE Numero = :numAtendimento;');
        $result = $statement->execute([
            'situacao' => $data['situacao'],
            'numAtendimento' => $data['numAtendimento'] 
            ]);
        return $result;
    }

    public function capturarAtendimento($data): bool
    {
        $result = FALSE;
        $strSQL = '';
        $usuario = $data['usuario'];
        // Pega o técnico da equipe
        $usuarioDAO = new UsuariosDAO();
        if ($usuarioDAO->getPerfil($usuario) == 'Auxiliar'){
            $equipe = $usuarioDAO->getEquipeByUsuario($usuario);
            if (!empty($equipe)) {
                $usuario = $usuarioDAO->getTecnicoEquipe($equipe[0]['equipe']);
            }
        }

        $strSQL = "UPDATE Atendimentos SET Usu_Designado = '".$usuario."', Grupo_Designado=0, Situacao = 'A' 
        WHERE Numero = ".$data['numAtendimento'];
        $statement = $this->pdoRbx->prepare($strSQL);
        $result = $statement->execute();

        if ($result == TRUE) {
            // Adiciona Ocorrência
            $descricao = "Atendimento capturado";
            $statement2 = $this->pdoRbx
            ->prepare("INSERT INTO AtendUltAlteracao (Atendimento, Usuario, Descricao, Data, Modo) 
                        VALUES (:atendimento, :usuario, :descricao, now(), 'A')");
            $result2 = $statement2->execute([
                'atendimento' => $data['numAtendimento'],
                'usuario' => $usuario,
                'descricao' => $descricao
                ]);

            // Estatísticas Routerbox
            // Primeiro atualiza o último registro de estatística
            $ultimaEstatistica = $this->getUltimaEstatistica($data['numAtendimento']);
            $idEstatistica = '';
            $dataInicio = '';
            if (!empty($ultimaEstatistica)) {
                $idEstatistica = $ultimaEstatistica[0]['Id'];
                $dataInicio = $ultimaEstatistica[0]['Inicio'];
            }
            if (!empty($idEstatistica)) {
                $statement2 = $this->pdoRbx
                ->prepare("UPDATE AtendimentoEstatistica 
                            SET Fim = now(), 
                                Duracao = TIME_TO_SEC(TIMEDIFF(now(), Inicio)), 
                                UsuarioFim = :usuariofim 
                            WHERE Id = :id");
                $result2 = $statement2->execute([
                    'usuariofim' => $usuario,
                    'id' => $idEstatistica
                ]);
            }
            // Em seguida cria um novo registro
            $statement2 = $this->pdoRbx
            ->prepare("INSERT INTO AtendimentoEstatistica 
                        (Atendimento,Topico,SituacaoOS,UsuarioOS,Grupo,Inicio,UsuarioInicio) 
                        VALUES (:atendimento,:topico,:situacaoOS,:usuarioOS,:grupo,now(),:usuarioInicio)");
            $result2 = $statement2->execute([
                'atendimento' => $data['numAtendimento'],
                'topico' => $data['topico'],
                'situacaoOS' => $data['situacaoOS'],
                'usuarioOS' => $usuario,
                'grupo' => 0,
                'usuarioInicio' => $usuario
                ]);

            // Estatísticas Axes
            $usuarios = $this->getUsuariosByEquipe($usuario);
            if (!empty($usuarios)) {
                for ($i=0; $i < count($usuarios); $i++) {
                    $statement2 = $this->pdoAxes
                    ->prepare("INSERT INTO estatistica_usuarios 
                                (usuario,atendimento,topico,inicio,fim,finalizado) 
                                VALUES (:usuario,:atendimento,:topico,:inicio,now(),'N')");
                    $result2 = $statement2->execute([
                        'usuario' => $usuarios[$i],
                        'atendimento' => $data['numAtendimento'],
                        'topico' => $data['topico'],
                        'inicio' => $dataInicio
                        ]);
                }
            } else {
                $statement2 = $this->pdoAxes
                ->prepare("INSERT INTO estatistica_usuarios 
                            (usuario,atendimento,topico,inicio,fim,finalizado) 
                            VALUES (:usuario,:atendimento,:topico,:inicio,now(),'N')");
                $result2 = $statement2->execute([
                    'usuario' => $usuario,
                    'atendimento' => $data['numAtendimento'],
                    'topico' => $data['topico'],
                    'inicio' => $dataInicio
                    ]);
            }
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
        $strSQL = "select a.Codigo CodigoCampo, a.Nome, a.TipoDado, a.Lista, a.Tabela, a.Obrigatorio, 
        '' Valor, '' Id 
        from isupergaus.CamposComplementares a 
        where a.Tabela = 'Contratos' and a.Codigo in (31,32,33,41,42,39,18,19,25,40,22,23,43,44) order by a.Codigo";

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
        $atendimento = $pDadosAdicionais[0]['numAtendimento'] ?? '';
        $usuario = $pDadosAdicionais[0]['usuario'] ?? '';

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
                // Adiciona Ocorrência
                if ($result == TRUE) {
                    $descricao = 'Dados Adicionais do Contrato: <b>'.$rows['Nome'] .' '.$rows['Valor'].'</b>';
                    $statement2 = $this->pdoRbx
                    ->prepare("INSERT INTO AtendUltAlteracao (Atendimento, Usuario, Descricao, Data, Modo) 
                                VALUES (:atendimento, :usuario, :descricao, now(), 'M')");
                    $result2 = $statement2->execute([
                        'atendimento' => $atendimento,
                        'usuario' => $usuario,
                        'descricao' => $descricao
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

    public function addRating($data): bool
    {
        $result = FALSE;
        $atendimento = $data['numAtendimento'];

        // Rating Atendimento
        $strSQL = "SELECT id FROM CamposComplementaresValores WHERE Complemento = 51 and Chave = ".$atendimento;
        $statement = $this->pdoRbx->prepare($strSQL);
        $statement->execute();
        $query = $statement->fetchAll(\PDO::FETCH_ASSOC);

        if (!isset($query[0]['id']) || empty($query[0]['id'])) {
            $statement = $this->pdoRbx
            ->prepare("INSERT INTO CamposComplementaresValores (Complemento, Tabela, Chave, Valor) 
                        VALUES (51, 'Atendimentos', :chave, :valor)");
            $result = $statement->execute([
                'chave' => $atendimento, 
                'valor' => $data['ratingAtendimento']
                ]);
        } else {
            $statement = $this->pdoRbx
            ->prepare('UPDATE CamposComplementaresValores SET Valor = :valor WHERE id = :id');
            $result = $statement->execute([
            'valor' => $data['ratingAtendimento'],
            'id' => $query[0]['id'] 
            ]);
        }
        // Comentário Rating Atendimento
        $strSQL = "SELECT id FROM CamposComplementaresValores WHERE Complemento = 52 and Chave = ".$atendimento;
        $statement = $this->pdoRbx->prepare($strSQL);
        $statement->execute();
        $query = $statement->fetchAll(\PDO::FETCH_ASSOC);

        if (!isset($query[0]['id']) || empty($query[0]['id'])) {
            $statement = $this->pdoRbx
            ->prepare("INSERT INTO CamposComplementaresValores (Complemento, Tabela, Chave, Valor) 
                        VALUES (52, 'Atendimentos', :chave, :valor)");
            $result = $statement->execute([
                'chave' => $atendimento, 
                'valor' => $data['commentRatingAtendimento']
                ]);
        } else {
            $statement = $this->pdoRbx
            ->prepare('UPDATE CamposComplementaresValores SET Valor = :valor WHERE id = :id');
            $result = $statement->execute([
            'valor' => $data['commentRatingAtendimento'],
            'id' => $query[0]['id'] 
            ]);
        }
        // Rating Produto
        $strSQL = "SELECT id FROM CamposComplementaresValores WHERE Complemento = 53 and Chave = ".$atendimento;
        $statement = $this->pdoRbx->prepare($strSQL);
        $statement->execute();
        $query = $statement->fetchAll(\PDO::FETCH_ASSOC);

        if (!isset($query[0]['id']) || empty($query[0]['id'])) {
            $statement = $this->pdoRbx
            ->prepare("INSERT INTO CamposComplementaresValores (Complemento, Tabela, Chave, Valor) 
                        VALUES (53, 'Atendimentos', :chave, :valor)");
            $result = $statement->execute([
                'chave' => $atendimento, 
                'valor' => $data['ratingProduto']
                ]);
        } else {
            $statement = $this->pdoRbx
            ->prepare('UPDATE CamposComplementaresValores SET Valor = :valor WHERE id = :id');
            $result = $statement->execute([
            'valor' => $data['ratingProduto'],
            'id' => $query[0]['id'] 
            ]);
        }
        // Comentário Rating Produto
        $strSQL = "SELECT id FROM CamposComplementaresValores WHERE Complemento = 54 and Chave = ".$atendimento;
        $statement = $this->pdoRbx->prepare($strSQL);
        $statement->execute();
        $query = $statement->fetchAll(\PDO::FETCH_ASSOC);

        if (!isset($query[0]['id']) || empty($query[0]['id'])) {
            $statement = $this->pdoRbx
            ->prepare("INSERT INTO CamposComplementaresValores (Complemento, Tabela, Chave, Valor) 
                        VALUES (54, 'Atendimentos', :chave, :valor)");
            $result = $statement->execute([
                'chave' => $atendimento, 
                'valor' => $data['commentRatingProduto']
                ]);
        } else {
            $statement = $this->pdoRbx
            ->prepare('UPDATE CamposComplementaresValores SET Valor = :valor WHERE id = :id');
            $result = $statement->execute([
            'valor' => $data['commentRatingProduto'],
            'id' => $query[0]['id'] 
            ]);
        }

        // Adiciona Ocorrência
        if ($result == TRUE) {
            $statement2 = $this->pdoRbx
            ->prepare("INSERT INTO AtendUltAlteracao (Atendimento, Usuario, Descricao, Data, Modo) 
                        VALUES (:atendimento, :usuario, 'Avaliação do Cliente Registrada', now(), 'M')");
            $result2 = $statement2->execute([
                'atendimento' => $data['numAtendimento'],
                'usuario' => $data['usuario']
                ]);
        }
        return $result;
    }

    public function getCheckList($pNumAtendimento): array
    {
        $strSQL = "select c.Id, c.Descricao, a.Checklist Marcados, 'false' Checked 
        from AtendimentoChecklist c left join Atendimentos a on c.Atendimento = a.Numero 
        where c.Atendimento = ".$pNumAtendimento." order by c.Id";

        $statement = $this->pdoRbx->prepare($strSQL);
        $statement->execute();
        $checklist = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $checklist;
    }

    public function getMTBF($pNumAtendimento): array
    {
        $strSQL = "select id, Valor from CamposComplementaresValores 
            where Tabela = 'Atendimentos' and Complemento = 38 and Chave = ".$pNumAtendimento;

        $statement = $this->pdoRbx->prepare($strSQL);
        $statement->execute();
        $checklist = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $checklist;
    }

    public function getAtendimentoCausas($pTipo): array
    {
        $strSQL = "select Codigo, Descricao, Grupo from isupergaus.AtendCausas 
        where Situacao='A' and Tipo='".$pTipo."'";

        $statement = $this->pdoRbx->prepare($strSQL);
        $statement->execute();
        $causas = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $causas;
    }

    public function saveCheckList($data): bool
    {
        $result = FALSE;
        $statement = $this->pdoRbx
        ->prepare('UPDATE Atendimentos SET Checklist = :checklist WHERE Numero = :numero');
        $result = $statement->execute([
            'checklist' => $data['strChecklist'],
            'numero' => $data['numAtendimento'] 
        ]);

        // MTBF
        if ($data['mtbfObrigatorio'] == 'S'){
            if (empty($data['idMTBF'])){
                $statement2 = $this->pdoRbx
                ->prepare("INSERT INTO CamposComplementaresValores (Complemento,Tabela,Chave,Valor) 
                            VALUES (38, 'Atendimentos', :chave, :valor)");
                $result2 = $statement2->execute([
                    'chave' => $data['numAtendimento'],
                    'valor' => $data['valorMTBF']
                    ]);
            }
            else {
                $statement2 = $this->pdoRbx
                ->prepare('UPDATE CamposComplementaresValores SET Valor = :valor WHERE id = :id');
                $result2 = $statement2->execute([
                    'id' => $data['idMTBF'],
                    'valor' => $data['valorMTBF'] 
                ]);
            }
        }

        // Adiciona Ocorrência
        if ($result == TRUE) {
            $descricao = '';
            if (empty($data['descChecklist'])) {
                $descricao = 'Todos os itens do checklist foram desmarcados';
            } else {
                $descricao = 'Novos itens de checklist marcados: '.$data['descChecklist'];
            }
            $statement2 = $this->pdoRbx
            ->prepare("INSERT INTO AtendUltAlteracao (Atendimento, Usuario, Descricao, Data, Modo) 
                        VALUES (:atendimento, :usuario, :descricao, now(), 'A')");
            $result2 = $statement2->execute([
                'atendimento' => $data['numAtendimento'],
                'usuario' => $data['usuario'],
                'descricao' => $descricao
                ]);
        }
        return $result;
    }

    public function saveDesignacao($data): bool
    {
        $result = FALSE;
        $strSQL = '';
        $usuariodesignado = $data['usuarioDesignado'];
        // Pega o técnico da equipe
        $usuarioDAO = new UsuariosDAO();
        if ($usuarioDAO->getPerfil($usuariodesignado) == 'Auxiliar'){
            $equipe = $usuarioDAO->getEquipeByUsuario($usuariodesignado);
            if (!empty($equipe)) {
                $usuariodesignado = $usuarioDAO->getTecnicoEquipe($equipe[0]['equipe']);
            }
        }

        if (empty($data['usuarioDesignado'])) {
            $strSQL = "UPDATE Atendimentos SET Grupo_Designado = ".$data['grupoDesignado'].", Usu_Designado='' 
            WHERE Numero = ".$data['numAtendimento'];
        } else {
            $strSQL = "UPDATE Atendimentos SET Usu_Designado = '".$usuariodesignado."', Grupo_Designado=0 
            WHERE Numero = ".$data['numAtendimento'];
        }
        $statement = $this->pdoRbx->prepare($strSQL);
        $result = $statement->execute();

        if ($result == TRUE) {
            // Adiciona Ocorrência
            $descricao = '';
            if (empty($data['usuarioDesignado'])) {
                $descGrupoDesignado = $this->getDescricaoGrupo($data['grupoDesignado']);
                $descricao = "Atendimento designado de <b>".$data['UltimoUsuarioDesignado']."</b> para <b>".$descGrupoDesignado."</b><BR>";
            } else {
                $descricao = "Atendimento designado de <b>".$data['UltimoUsuarioDesignado']."</b> para <b>".$usuariodesignado."</b><BR>";
            }
            $statement2 = $this->pdoRbx
            ->prepare("INSERT INTO AtendUltAlteracao (Atendimento, Usuario, Descricao, Data, Modo) 
                        VALUES (:atendimento, :usuario, :descricao, now(), 'A')");
            $result2 = $statement2->execute([
                'atendimento' => $data['numAtendimento'],
                'usuario' => $data['usuario'],
                'descricao' => $descricao
                ]);

            // Estatísticas Routerbox
            // Primeiro atualiza o último registro de estatística
            $ultimaEstatistica = $this->getUltimaEstatistica($data['numAtendimento']);
            $idEstatistica = '';
            $dataInicio = '';
            if (!empty($ultimaEstatistica)) {
                $idEstatistica = $ultimaEstatistica[0]['Id'];
                $dataInicio = $ultimaEstatistica[0]['Inicio'];
            }
            //$idEstatistica = $this->getUltimaEstatistica($data['numAtendimento']);
            if (!empty($idEstatistica)) {
                $statement2 = $this->pdoRbx
                ->prepare("UPDATE AtendimentoEstatistica 
                            SET Fim = now(), 
                                Duracao = TIME_TO_SEC(TIMEDIFF(now(), Inicio)), 
                                UsuarioFim = :usuariofim 
                            WHERE Id = :id");
                $result2 = $statement2->execute([
                    'usuariofim' => $data['UltimoUsuarioDesignado'],
                    'id' => $idEstatistica
                ]);
            }
            // Em seguida cria um novo registro
            $grupo = 0;
            if (empty($data['usuarioDesignado'])) {
                $grupo = $data['grupoDesignado'];
            }
            $statement2 = $this->pdoRbx
            ->prepare("INSERT INTO AtendimentoEstatistica 
                        (Atendimento,Topico,SituacaoOS,UsuarioOS,Grupo,Inicio,UsuarioInicio) 
                        VALUES (:atendimento,:topico,:situacaoOS,:usuarioOS,:grupo,now(),:usuarioInicio)");
            $result2 = $statement2->execute([
                'atendimento' => $data['numAtendimento'],
                'topico' => $data['topico'],
                'situacaoOS' => $data['situacaoOS'],
                'usuarioOS' => $data['UltimoUsuarioDesignado'],
                'grupo' => $grupo,
                'usuarioInicio' => $data['UltimoUsuarioDesignado']
                ]);

            // Estatísticas Axes
            $usuarios = $this->getUsuariosByEquipe($data['UltimoUsuarioDesignado']);
            if (!empty($usuarios)) {
                for ($i=0; $i < count($usuarios); $i++) {
                    $statement2 = $this->pdoAxes
                    ->prepare("INSERT INTO estatistica_usuarios 
                                (usuario,atendimento,topico,inicio,fim,finalizado) 
                                VALUES (:usuario,:atendimento,:topico,:inicio,now(),'N')");
                    $result2 = $statement2->execute([
                        'usuario' => $usuarios[$i],
                        'atendimento' => $data['numAtendimento'],
                        'topico' => $data['topico'],
                        'inicio' => $dataInicio
                        ]);
                }
            } else {
                $statement2 = $this->pdoAxes
                ->prepare("INSERT INTO estatistica_usuarios 
                            (usuario,atendimento,topico,inicio,fim,finalizado) 
                            VALUES (:usuario,:atendimento,:topico,:inicio,now(),'N')");
                $result2 = $statement2->execute([
                    'usuario' => $data['UltimoUsuarioDesignado'],
                    'atendimento' => $data['numAtendimento'],
                    'topico' => $data['topico'],
                    'inicio' => $dataInicio
                    ]);
            }
        }
        return $result;
    }

    public function saveEncerramento($data): bool
    {
        $result = FALSE;
        $usuariodesignado = $data['usuarioDesignado'];
        // Pega o técnico da equipe
        $usuarioDAO = new UsuariosDAO();
        if ($usuarioDAO->getPerfil($usuariodesignado) == 'Auxiliar'){
            $equipe = $usuarioDAO->getEquipeByUsuario($usuariodesignado);
            if (!empty($equipe)) {
                $usuariodesignado = $usuarioDAO->getTecnicoEquipe($equipe[0]['equipe']);
            }
        }

        $statement = $this->pdoRbx
        ->prepare("UPDATE Atendimentos 
                    SET Causa = :causa, 
                        Solucao = :solucao, 
                        Usuario_BX = :usuario, 
                        Data_BX = date_format(now(), '%Y-%m-%d'), 
                        Hora_BX = date_format(now(), '%H:%i:%s'), 
                        Situacao = 'F',
                        SituacaoOS = 'C' 
                    WHERE Numero = :numero");
        $result = $statement->execute([
            'causa' =>  $data['causa'],
            'solucao' =>  $data['solucao'],
            'usuario' => $usuariodesignado,
            'numero' => $data['numAtendimento']
        ]);

        if ($result == TRUE) {
            // Adiciona Ocorrência
            $descricao = 'Assunto/Solução alterado(s)<BR>';
            $statement2 = $this->pdoRbx
            ->prepare("INSERT INTO AtendUltAlteracao (Atendimento, Usuario, Descricao, Data, Modo) 
                        VALUES (:atendimento, :usuario, :descricao, now(), 'A')");
            $result2 = $statement2->execute([
                'atendimento' => $data['numAtendimento'],
                'usuario' => $usuariodesignado,
                'descricao' => $descricao
                ]);
            // Adiciona Ocorrência
            $descricao = 'Atendimento encerrado<BR>';
            $statement2 = $this->pdoRbx
            ->prepare("INSERT INTO AtendUltAlteracao (Atendimento, Usuario, Descricao, Data, Modo) 
                        VALUES (:atendimento, :usuario, :descricao, now(), 'A')");
            $result2 = $statement2->execute([
                'atendimento' => $data['numAtendimento'],
                'usuario' => $usuariodesignado,
                'descricao' => $descricao
                ]);
            
            // Estatísticas Routerbox
            // Atualiza o último registro de estatística
            $ultimaEstatistica = $this->getUltimaEstatistica($data['numAtendimento']);
            $idEstatistica = '';
            $dataInicio = '';
            if (!empty($ultimaEstatistica)) {
                $idEstatistica = $ultimaEstatistica[0]['Id'];
                $dataInicio = $ultimaEstatistica[0]['Inicio'];
            }
            //$idEstatistica = $this->getUltimaEstatistica($data['numAtendimento']);
            if (!empty($idEstatistica)) {
                $statement2 = $this->pdoRbx
                ->prepare("UPDATE AtendimentoEstatistica 
                            SET Fim = now(), 
                                Duracao = TIME_TO_SEC(TIMEDIFF(now(), Inicio)), 
                                UsuarioFim = :usuariofim 
                            WHERE Id = :id");
                $result2 = $statement2->execute([
                    'usuariofim' => $usuariodesignado,
                    'id' => $idEstatistica
                ]);
            }

            // Estatísticas Axes
            $usuarios = $this->getUsuariosByEquipe($usuariodesignado);
            if (!empty($usuarios)) {
                for ($i=0; $i < count($usuarios); $i++) {
                    $statement2 = $this->pdoAxes
                    ->prepare("INSERT INTO estatistica_usuarios 
                                (usuario,atendimento,topico,inicio,fim,finalizado) 
                                VALUES (:usuario,:atendimento,:topico,:inicio,now(),'S')");
                    $result2 = $statement2->execute([
                        'usuario' => $usuarios[$i],
                        'atendimento' => $data['numAtendimento'],
                        'topico' => $data['topico'],
                        'inicio' => $dataInicio
                        ]);
                }
            } else {
                $statement2 = $this->pdoAxes
                ->prepare("INSERT INTO estatistica_usuarios 
                            (usuario,atendimento,topico,inicio,fim,finalizado) 
                            VALUES (:usuario,:atendimento,:topico,:inicio,now(),'S')");
                $result2 = $statement2->execute([
                    'usuario' => $usuariodesignado,
                    'atendimento' => $data['numAtendimento'],
                    'topico' => $data['topico'],
                    'inicio' => $dataInicio
                    ]);
            }
        }
        return $result;
    }

    private function getDescricaoGrupo($idGrupo): string
    {
        $desc = '';
        $strSQL = "SELECT Grupo FROM UsuariosGrupoSetor WHERE id = ".$idGrupo;
        $statement = $this->pdoRbx->prepare($strSQL);
        $statement->execute();
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        if (isset($result[0]['Grupo']) && !empty($result[0]['Grupo'])) {
            $desc = $result[0]['Grupo'];
        }
        return $desc;
    }

    private function getUltimaEstatistica($pNumAtendimento): array
    {
        // $id = '';
        $strSQL = "SELECT Id, Inicio FROM AtendimentoEstatistica 
                    WHERE Atendimento = ".$pNumAtendimento." order by Id desc limit 1";
        $statement = $this->pdoRbx->prepare($strSQL);
        $statement->execute();
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
        // if (isset($result[0]['Id']) && !empty($result[0]['Id'])) {
        //     $id = $result[0]['Id'];
        // }
        // return $id;
    }
}
