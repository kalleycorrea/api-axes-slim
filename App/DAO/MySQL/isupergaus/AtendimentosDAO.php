<?php

namespace App\DAO\MySQL\isupergaus;

class AtendimentosDAO extends Conexao
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getAtendimentos($paramUsuario, $paramTipo, $paramGrupo): array
    {
        $strSQL = "SELECT a.Numero NumAtendimento, a.Protocolo, c.Codigo CodCliente, 
        c.Nome Cliente, c.Sigla Apelido, a.Contrato, t.Descricao Topico, a.Prioridade, 
        date_format(concat(a.Data_AB,' ',a.Hora_AB), '%d/%m/%Y %H:%i') Abertura,
        replace(isupergaus.rbx_sla(a.Numero, 'N'),'?','ú') SLA,  
        g.Nome GrupoCliente, 
        if (e.Endereco is null, c.Endereco, e.Endereco) as Endereco, 
        if (e.Endereco is null, c.Numero, e.Numero) as Numero,
        if (e.Endereco is null, c.Complemento, e.Complemento) as Complemento,
        if (e.Endereco is null, c.Bairro, e.Bairro) as Bairro,
        if (e.Endereco is null, c.CEP, e.CEP) as CEP,
        if (e.Endereco is null, c.Cidade, e.Cidade) as Cidade,
        if (e.Endereco is null, c.UF, e.UF) as Estado,
        if (e.Endereco is null, c.MapsLat, e.MapsLat) as MapsLat,
        if (e.Endereco is null, c.MapsLng, e.MapsLng) as MapsLng,
        a.Usu_Designado, 
        a.Grupo_Designado,
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
                when 'F' then 'Na fila' 
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
        $whereTecnico = " and (a.Usu_Designado = ".$paramUsuario." or a.Grupo_Designado = ".$paramGrupo.")";
        //Atendimentos visualizados pelo Gestor
        $whereGestor = " and (a.Usu_Designado in (select u2.usuario from isupergaus.usuarios u2 where u2.idgrupo = ".$paramGrupo.") or a.Grupo_Designado = ".$paramGrupo.")";
        $where = ($paramTipo == 'T') ? $whereTecnico : $whereGestor;

        $orderBy = " order by a.Prioridade, date_format(concat(a.Data_AB,' ',a.Hora_AB), '%d/%m/%Y %H:%i:%s')";

        $statement = $this->pdoRbx
            ->prepare($strSQL . $where . $orderBy);
        //$statement->bindParam(':usuario', $paramUsuario, \PDO::PARAM_STR);
        //$statement->bindParam(':grupo', $paramGrupo, \PDO::PARAM_STR);
        $statement->execute();
        $atendimentos = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $atendimentos;
    }
}
