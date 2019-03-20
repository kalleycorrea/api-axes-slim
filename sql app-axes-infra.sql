-- SET SQL_SAFE_UPDATES = 0; -- para executar delete
select u.usuario, u.Terminal senha, u.Nome, u.idgrupo, u.master, u.perfil, if(u.master='S','G',if(u.perfil=19,'A','T')) as tipo, 
u.situacao, MobileDevice, MobileTrackingTrace, MobileDeviceId, Latitude, Longitude, MobileLastDataReceived, MobileLastLogin, 
concat('https://rbx.axes.com.br/routerbox/file/img/',if(ifnull(Foto,'')='','contact_default.png',Foto)) as Foto 
from isupergaus.usuarios u 
where 
-- u.idgrupo = 4 
u.usuario in ('antonio.giliard', 'escossio.farias', 'amandabonfim') -- 'kalley' 
-- (u.idgrupo = 4 and u.perfil in (7,19)) or (u.idgrupo = 4 and u.master = 'S') or (u.usuario='kalley')
order by u.Nome, u.perfil;

select a.usuario from (select usuario, if(master='S','G',if(perfil=19,'A','T')) as tipo FROM usuarios WHERE usuario in ('matheus.henrique','james.marques')) as a where a.tipo = 'T' limit 1;

select * from isupergaus.MobileDevice;
select * from isupergaus.MobileAppWorkforce;
select * from isupergaus.MobileDeviceLog;

select * from isupergaus.LogGeoMobile;

select usuario, Latitude, Longitude, MobileLastDataReceived from isupergaus.usuarios 
where usuario in ('antonio.giliard', 'escossio.farias', 'amandabonfim') order by MobileLastDataReceived desc limit 1;

-- update isupergaus.usuarios set Latitude=NULL, MobileLastDataReceived=DEFAULT where usuario='kalley';
-- SET SQL_SAFE_UPDATES = 0;
-- update isupergaus.usuarios set Terminal='' where idgrupo = 4 and Terminal='9999';
-- update isupergaus.usuarios set AccessRFID='' where usuario='kalley';

-- u.usuario='kalley'
-- select ug.id from isupergaus.UsuariosGrupoSetor ug where ug.Grupo = 'Infraestrutura';
-- 4 Infraestrutura (idgrupo -> UsuariosGrupoSetor)
-- 16 Desenvolvimento (idgrupo -> UsuariosGrupoSetor)
-- 7 Tecnico de Campo (perfil -> perfis_usuarios)
-- 19 Auxiliar de Campo (perfil -> perfis_usuarios)
/*
/var/www/routerbox/file/img/perfil_routerbox.jpg
https://rbx.axes.com.br/routerbox/file/img/perfil_routerbox.jpg
*/
select * from isupergaus.UsuariosGrupoSetor;
select * from isupergaus.perfis_usuarios;

/*
Situação OS
	'': Não Criada
	F: Na fila
    C: Concluída
    A: A Caminho
    E: Em Execução
    P: Pausada
    B: Abortada
*/

SELECT a.Numero NumAtendimento, a.Protocolo, c.Codigo CodCliente, c.Nome Cliente, c.Sigla Apelido, c.Tipo TipoPessoa, a.Tipo, 
c.TelCelular, c.TelComercial, c.TelResidencial, a.Contrato, p.DescricaoComercial Plano, p.Descricao Plano2, 
t.Descricao DescTopico, a.Topico, a.Prioridade, a.Assunto, a.Solucao, a.Causa, 
date_format(concat(a.Data_AB," ",a.Hora_AB), "%d/%m/%Y %H:%i") Abertura,
date_format(concat(a.Data_BX," ",a.Hora_BX), "%d/%m/%Y %H:%i") Fechamento,
date_format(concat(a.Data_Prox," ",a.Hora_Prox), "%d/%m/%Y %H:%i") Agendamento,
replace(isupergaus.rbx_sla(a.Numero, 'N'),'?','ú') SLA,  
if (a.SLATipo = 'C', 'Corridos', if (a.SLATipo = 'U', 'Úteis', '')) as SLATipo, 
if (a.SLA > 0, SEC_TO_TIME(a.SLA*60), 0) DuracaoSLA_HHMMSS,
a.SLA DuracaoSLA2, a.SLATipo SLATipo2, a.Duracao, 
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
WHERE a.Data_Prox is not null 
-- a.Numero = 107415 
-- a.Situacao = 'A' -- and a.Topico in (36, 46, 47, 112, 113, 155) 
-- c.Nome like '%Rita de Cássia%'
 -- visualização técnico
-- and (a.Usu_Designado = 'amandabonfim' or a.Grupo_Designado = 4) 
-- visualização gestor
-- and (a.Usu_Designado in (select u2.usuario from isupergaus.usuarios u2 where u2.idgrupo = 4) or a.Grupo_Designado = 4)
-- and (a.Usu_Designado = 'kalley' or (a.Grupo_Designado = 1 and u.master = 'S'))
/*and (a.Usu_Designado in (select u1.usuario from isupergaus.usuarios u1 
							where (u1.idgrupo = 4 and u1.perfil in (7,19)) or (u1.idgrupo = 4 and u1.master = 'S') or (u1.usuario='kalley')) 
    or a.Grupo_Designado = 4
    )*/
order by a.Prioridade, date_format(concat(a.Data_AB," ",a.Hora_AB), "%d/%m/%Y %H:%i:%s");

select * from isupergaus.Atendimentos where Grupo_Designado = 0 && Usu_Designado = '';
-- update Atendimentos set situacao = '', Usuario_BX='', Data_BX = default, Hora_BX = default, Solucao = '' where numero=107415;
-- update Atendimentos set Grupo_Designado = 4, Usu_Designado='' where numero=107415;

-- SLA
select CONVERT(isupergaus.rbx_sla(96833, 'N') USING utf8) SLA;

-- ANEXOS
select a.Numero, a.Situacao from isupergaus.Atendimentos a where a.Situacao = 'A' and a.Numero=100903;
select * from isupergaus.Arquivo aq where aq.Codigo=99758; -- 100903 or aq.Tipo='A';
-- Arquivo.Codigo = Atendimento.Numero
-- Endereço web arquivos: https://rbx.axes.com.br/routerbox/file/docarquivos/Screenshot_20190102-183729.png
-- Local salvamento arquivos: /var/www/routerbox/file/docarquivos/
-- tipos de arquivo
select aq.Tipo, count(*) from isupergaus.Arquivo aq group by aq.Tipo;
-- atendimentos abertos com anexo
select * from isupergaus.Arquivo aq where aq.Tipo='A' and aq.Codigo in (select a.Numero from isupergaus.Atendimentos a 
	where a.Situacao = 'A'); -- and a.Usu_Designado = 'amandabonfim'

select concat('https://rbx.axes.com.br/routerbox/file/docarquivos/', Arquivo) as imagem, descricao from isupergaus.Arquivo aq where aq.Tipo='A' and aq.Codigo=101025;
-- delete from Arquivo where Tipo='A' and Id in (16585); -- and Codigo = 100903;

-- ASSINATURA
select * from Arquivo where Tipo='A' and NomeArquivo='100903-Assinatura.jpg' and Codigo=100903;

-- OCORRÊNCIAS
select * from isupergaus.AtendUltAlteracao where atendimento=107415; -- 100903 93639; -- 94982; -- 98762; -- 101025;
select * from isupergaus.AtendUltAlteracao where descricao like '%Capturado%';
select descricao, count(*) from isupergaus.AtendUltAlteracao where modo='M' and descricao like '%<br />%' group by descricao;
select Id, Atendimento, Usuario, Data, Descricao, Modo from isupergaus.AtendUltAlteracao where Atendimento = 94982;
-- delete from isupergaus.AtendUltAlteracao where 
	-- id in (698515,698514);
	-- Atendimento=107415 and Descricao='Novos itens de checklist marcados: Verificar Pontência do Sinal; Verificar Alcance do Wifi';

-- DADOS TÉCNICOS (Dados Adicionais)
-- CHAVE = Numero Contrato 
-- COMPLEMENTO = Código do Campo Adicional
select * from isupergaus.CamposComplementares c where c.Tabela = 'Contratos';
select * from isupergaus.CamposComplementaresValores c 
where c.Tabela = 'Contratos' and c.Complemento < 45 and Chave = 19623;

-- Somente os atendimentos em aberto e que os contratos possuem Dados Adicionais
select * from isupergaus.Atendimentos a 
where a.Situacao = 'A' and a.Usu_Designado = 'amandabonfim' 
and a.Contrato in (select distinct c.Chave Contrato from isupergaus.CamposComplementaresValores c 
					where c.Tabela = 'Contratos' and c.Complemento < 45 
				  );

select a.Codigo CodigoCampo, a.Nome, a.TipoDado, a.Lista, a.ListaDesc, a.Tabela, a.Obrigatorio, a.Tamanho, a.Ajuda, '' Valor 
from isupergaus.CamposComplementares a where a.Tabela = 'Contratos' and a.Codigo < 45 order by a.Codigo;

select a.Codigo CodigoCampo, a.Nome, d.Numero NumAtendimento, b.Id, b.Chave Contrato, b.Valor, 
a.TipoDado, a.Lista, a.ListaDesc, a.Tamanho, a.Obrigatorio, a.Ajuda 
from CamposComplementares a 
left join CamposComplementaresValores b on a.Codigo = b.Complemento 
left join Contratos c on b.Chave = c.Numero 
left join Atendimentos d on b.Chave = d.Contrato 
where a.Tabela = 'Contratos' and a.Codigo in (31,32,33,41,42,39,18,19,25,40,22,23,43,44) and d.Numero = 101248 
order by a.Codigo;

select * from CamposComplementaresValores where ifnull(Chave, '0') = '0';
select TipoDado, count(*) from CamposComplementares group by TipoDado;
select * from CamposComplementares where TipoDado='M';
/*
Código dos Dados Adicionais do Contrato
*31 CTO
*32 PORTA CTO
*33 PORTA SPLITTER
*41 POTÊNCIA SPLITTER (dBm)
*42 POTÊNCIA CLIENTE (dBm)
*39 CENTRAL
*18 SLOT
*19 PON
*25 MARCA ONU
*40 TIPO ONU
*22 SN
*23 MAC
*43 TIPO CABO
*44 METRAGEM TOTAL

46 ID TALK VSC
48 SENHA TALK VSC
50 Numero de Controle - VSC
*/

-- ENDEREÇO DE INSTALAÇÃO
select cliente from isupergaus.Contratos where numero=9234;
select * from isupergaus.ContratosEndereco where Cliente=3776;
select * from isupergaus.ContratosEndereco where Contrato=5934;
-- delete from isupergaus.ContratosEndereco where Contrato=5934;
select MapsLat, MapsLng from isupergaus.Clientes where Codigo=5980;
-- Cliente,Contrato,Tipo,Cobranca,Pais,Endereco,Numero,Bairro,Complemento,Cidade,UF,CEP,MapsLat,MapsLng
select * from isupergaus.AtendUltAlteracao a where a.Atendimento = 100903;
-- delete from isupergaus.AtendUltAlteracao where id=688408;

-- RATING
/*
CHAVE = Numero do Atendimento 
COMPLEMENTO = Código do Campo Adicional
Código dos Dados Adicionais do Atendimento
51 Rating Atendimento
52 Comentário Rating Atendimento
53 Rating Produto
54 Comentário Rating Produto
*/
select * from isupergaus.CamposComplementaresValores c where c.Complemento in (51,52,53,54) and Chave = 100903;
-- delete from isupergaus.CamposComplementaresValores where Tabela = 'Atendimentos' and chave = 100903;

-- CHECK LIST | DESIGNAÇÃO | ENCERRAMENTO
SELECT * FROM isupergaus.AtendimentoChecklist ck where ck.Atendimento=107415;
select * from AtendTopicoChecklist;
-- delete from AtendimentoChecklist where Descricao = 'Todos Equipamentos Presentes' or Descricao = 'Equipamentos Testados';

-- CheckList marcados
SELECT a.Checklist, a.Grupo_Designado, a.Usu_Designado, a.Causa, a.Solucao, a.Usuario_BX, a.Data_BX, a.Situacao 
FROM isupergaus.Atendimentos a where a.Numero=100903;

-- atendimentos abertos com CHECK LIST
select Id, Atendimento, Descricao from isupergaus.AtendimentoChecklist ck 
where ck.Atendimento in (select a.Numero from isupergaus.Atendimentos a where a.Situacao = 'A'); -- and a.Usu_Designado = 'amandabonfim'

select c.Id, c.Descricao, a.Checklist Marcados, 'false' Checked 
from AtendimentoChecklist c left join Atendimentos a on c.Atendimento = a.Numero where Atendimento = 100903 order by c.Id;

select id, Grupo as Nome from isupergaus.UsuariosGrupoSetor;
select u.usuario as Nome, u.perfil, u.idgrupo from isupergaus.usuarios u where situacao = 'A' and u.idgrupo = 4 order by usuario;
select Codigo, Descricao, Grupo from isupergaus.AtendCausas where Situacao='A' and Tipo='T';

-- Grupo_Designado 0 (Vazio)
-- Causa 0 (Vazio)

SELECT * FROM isupergaus.Atendimentos a where a.Situacao <> 'A' order by numero desc limit 10;

SELECT Data_AB, Hora_AB, Data_BX, Hora_BX FROM isupergaus.Atendimentos a where a.Numero=104186;
-- date_format(concat(a.Data_BX," ",a.Hora_BX), "%d/%m/%Y %H:%i") Fechamento,
-- 2019-02-05 09:31:30

-- MTBF
select * from isupergaus.CamposComplementares c where c.Tabela = 'Atendimentos' and Codigo=38;
select * from isupergaus.CamposComplementaresValores c 
where c.Tabela = 'Atendimentos' and c.Complemento = 38 and Chave in (107415,107281, 107030); -- and Valor = 'S'
-- delete from CamposComplementaresValores where id in (40736,40737);

select * from isupergaus.Atendimentos a 
left join isupergaus.CamposComplementaresValores c on a.Numero = c.Chave and c.Tabela = 'Atendimentos' 
where a.Topico in (120,95,27,6,41,10,11,12,13,94,119,35,36,38,45,46,18,19) and c.Complemento = 38;

-- ESTATÍSTICA
SELECT * FROM isupergaus.AtendimentoEstatistica es where es.Atendimento=107415; -- 105716 100903 100905 104186 100905 100903 100776;
SELECT * FROM isupergaus.AtendimentoEstatistica es where es.Grupo <> 0;
select * from isupergaus.UsuariosGrupoSetor;
-- Id,Atendimento,Topico,SituacaoOS,UsuarioOS,Grupo,Inicio,Fim,Duracao,UsuarioInicio,UsuarioFim
-- delete from AtendimentoEstatistica where Id in (154397);
-- update AtendimentoEstatistica set Fim = default, Duracao = default, UsuarioFim = '' where Id=144431;

-- Ultima Estatistica
SELECT Id FROM AtendimentoEstatistica WHERE Atendimento = 100903 order by Id desc limit 1;

SELECT a.Checklist, a.Grupo_Designado, a.Usu_Designado, a.Causa, a.Solucao, a.Usuario_BX, a.Data_BX, a.Hora_BX, a.Situacao, a.SituacaoOS 
FROM isupergaus.Atendimentos a where a.Numero=100903;
-- update Atendimentos set Usu_Designado='remocao', Solucao='', causa=0, Usuario_BX='', Data_BX=default, Hora_BX=default, Situacao='A', SituacaoOS='F' where Numero=100903;

select now(), date_format(now(), '%Y-%m-%d'), date_format(now(), '%H:%i:%s');
-- now() => 2019-02-06 01:15:08
SELECT TIME_TO_SEC(TIMEDIFF('2010-08-20 12:01:00', '2010-08-20 12:00:00')) diff;


-- OS
SELECT * FROM isupergaus.AtendimentoOS os where os.Atendimento=100903; -- 98762;
SELECT atendimento, count(*) FROM isupergaus.AtendimentoOS group by atendimento;

-- EQUIPES
select usuario, Nome, perfil, master from isupergaus.usuarios where idgrupo = 4 and situacao='A' order by Nome;
-- Atendimentos Infraestrutura
select a.numero, Usu_Designado, a.Topico, a.Cliente from isupergaus.Atendimentos a where a.Situacao='A' 
and a.Usu_Designado in (select usuario from usuarios where situacao='A' and idgrupo=4 and perfil=7);

select situacao, count(*) from isupergaus.Atendimentos group by situacao;
select numero,situacao, Usu_Designado, Grupo_Designado from isupergaus.Atendimentos where Situacao='';


-- DATABASE EXTERNO DEV.AXES.COM.BR
select * from equipes;
insert into equipes (nome) values('Equipe 1');
insert into equipes (nome) values('Equipe 2');

select * from usuarios;
insert into usuarios (usuario, senha, equipe) values('leandro.bezerra','9999',3);
insert into usuarios (usuario, senha, equipe) values('escossio.farias','9999',3);
insert into usuarios (usuario, senha, equipe) values('james.marques','9999',4);
insert into usuarios (usuario, senha, equipe) values('matheus.henrique','9999',4);

select usuario, equipe, 'Técnico' as perfil, 0 as quantAtendimentos from usuarios where equipe=3;
UPDATE usuarios SET equipe = 3 WHERE equipe IS NULL;
select usuario from usuarios where equipe is not null; -- usuario = 'leandro.bezerra'
select u.usuario from usuarios u where u.equipe = (select equipe from usuarios where usuario='leandro.bezerra');  -- 'leandro.bezerra'

-- DATABASE ROUTERBOX
select Usu_Designado, count(*) as atendimentos from isupergaus.Atendimentos 
where Usu_Designado = 'antonio.giliard' and Situacao in ('A','E') group by Usu_Designado;

-- Grupoif(u.master='S','G',if(u.perfil=19,'A','T')) as tipo
select usuario, terminal senha, if(ifnull(perfil,0) = 7, 'Técnico', if(ifnull(perfil,0) = 19, 'Auxiliar', '')) as perfil 
from isupergaus.usuarios where usuario in ('kalley', 'amandabonfim', 'antonio.giliard', 'escossio.farias', 'james.marques', 'leandro.bezerra', 'matheus.henrique');
-- update usuarios set terminal = '9999' where usuario in ('antonio.giliard', 'escossio.farias', 'james.marques', 'leandro.bezerra', 'matheus.henrique');
-- update usuarios set terminal = '9999' where perfil in (7,19);

select * from UsuariosGrupoSetor