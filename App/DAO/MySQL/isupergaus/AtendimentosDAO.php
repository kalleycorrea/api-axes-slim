<?php

namespace App\DAO\MySQL\isupergaus;

class AtendimentosDAO extends Conexao
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getAtendimentosModuloTecnico($paramUsuario): array
    {
        $statement = $this->pdo
            ->prepare("SELECT a.Protocolo, c.Codigo CodCliente, c.Nome Cliente, a.Contrato, t.Descricao Topico, 
            concat(a.Data_AB,' ',time_format(a.Hora_AB, '%H:%i')) Abertura, 
            replace(isupergaus.rbx_sla(a.Numero, 'N'),'?','ú') SLA,  
            g.Nome GrupoCliente, 
            if (e.Endereco is null, c.Endereco, e.Endereco) as Endereco, 
            if (e.Endereco is null, c.Numero, e.Numero) as Numero,
            if (e.Endereco is null, c.Complemento, e.Complemento) as Complemento,
            if (e.Endereco is null, c.Bairro, e.Bairro) as Bairro,
            if (e.Endereco is null, c.CEP, e.CEP) as CEP,
            if (e.Endereco is null, c.Cidade, e.Cidade) as Cidade,
            if (e.Endereco is null, c.MapsLat, e.MapsLat) as MapsLat,
            if (e.Endereco is null, c.MapsLng, e.MapsLng) as MapsLng,
            a.Usu_Designado, 
            a.Grupo_Designado,
            case ct.Situacao 
                    when 'A' then 'Ativo' 
                    when 'B' then 'Bloqueado' 
                    when 'C' then 'Cancelado' 
                    when 'E' then 'Aguard. Instalacao' 
                    when 'I' then 'Em Instalacao' 
                    when 'S' then 'Suspenso'
                    else 'Inativo' END as Situacao 
            FROM isupergaus.Atendimentos a 
            left join isupergaus.Clientes c on a.Cliente = c.Codigo 
            left join isupergaus.ClienteGrupo g on c.Grupo = g.Codigo 
            left join isupergaus.Contratos ct on a.Contrato = ct.Numero 
            left join isupergaus.Planos p on ct.Plano = p.Codigo
            left join isupergaus.ContratosEndereco e on ct.Numero = e.Contrato and e.Tipo = 'I' 
            left join isupergaus.usuarios u on a.Usu_Designado = u.usuario 
            left join isupergaus.UsuariosGrupoSetor ug on a.Grupo_Designado = ug.id 
            left join isupergaus.AtendTopicos t on a.Topico = t.Codigo 
            WHERE a.Situacao = 'A' and a.Topico in (36, 46, 47, 112, 113, 155) 
            and a.Usu_Designado = :usuario 
            and (a.Usu_Designado in (select u1.usuario from isupergaus.usuarios u1 
                                        where (u1.idgrupo = 4 and u1.perfil in (7,19)) or (u1.idgrupo = 4 and u1.master = 'S') or (u1.usuario='kalley')) 
                or a.Grupo_Designado = 4
                );");

        $statement->bindParam(':usuario', $paramUsuario, \PDO::PARAM_STR);
        $statement->execute();
        $atendimentos = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $atendimentos;
    }
}
