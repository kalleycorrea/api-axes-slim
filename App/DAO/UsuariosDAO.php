<?php

namespace App\DAO;

class UsuariosDAO extends Conexao
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getUsuario($pUsuario, $pSenha): array
    {
        $urlImgUserRbx = getenv('URL_IMG_USER_RBX');

        $statement = $this->pdoRbx
            ->prepare("SELECT usuario, Terminal senha, Nome, idgrupo, '' as equipe, '' as nomeequipe, MobileDeviceId, 
            if(master='S','G',if(perfil=19,'A','T')) as tipo 
            FROM usuarios 
            WHERE usuario = :usuario AND Terminal = :senha AND situacao = 'A' ;");
        // concat('".$urlImgUserRbx."',if(ifnull(Foto,'')='','contact_default.png',Foto)) as Foto 

        $statement->bindParam(':usuario', $pUsuario, \PDO::PARAM_STR);
        $statement->bindParam(':senha', $pSenha, \PDO::PARAM_STR);
        $statement->execute();
        $usuario = $statement->fetchAll(\PDO::FETCH_ASSOC);

        if (!empty($usuario)){
            for ($i=0; $i < count($usuario); $i++) {
                $equipe = $this->getEquipeByUsuario($usuario[$i]['usuario']);
                if (!empty($equipe)) {
                    $usuario[$i]['equipe'] = $equipe[0]['equipe'];
                    $usuario[$i]['nomeequipe'] = $equipe[0]['nome'];
                }
            }
        }
        return $usuario;

        // Utilizar a senha do banco de dados auxiliar
        /*
        if (!empty($usuario)){
            $statement2 = $this->pdoAxes
            ->prepare("SELECT usuario 
            FROM usuarios 
            WHERE usuario = :usuario AND senha = :senha ;");
            $statement2->bindParam(':usuario', $pUsuario, \PDO::PARAM_STR);
            $statement2->bindParam(':senha', $pSenha, \PDO::PARAM_STR);
            $statement2->execute();
            $usuario2 = $statement2->fetchAll(\PDO::FETCH_ASSOC);
            if (!empty($usuario2)){
                //$usuario = array_merge($usuario, array('setpassword' => 'N'));
                //array_push($usuario, array('setpassword' => 'N'));
                //$usuario += ['setpassword' => 'N'];
                //return $usuario;
                $result = [];
                foreach ($usuario as $campo) {
                    $result += $campo;
                }
                $result += ['setpassword' => 'N'];
                return $result;
            }
            else{
                //$usuario = array_merge($usuario, array('setpassword' => 'S'));
                //array_push($usuario, array('setpassword' => 'S'));
                //$usuario += ['setpassword' => 'S'];
                //return $usuario;
                $result = [];
                foreach ($usuario as $campo) {
                    $result += $campo;
                }
                $result += ['setpassword' => 'S'];
                return $result;
            }
        }
        return [];
        */
    }

    public function updateLocation($data): bool
    {
        $result = FALSE;

        if (!empty($data['equipe'])) {
            $usuarios = $this->getUsuariosByEquipe($data['equipe'], \PDO::FETCH_ASSOC);
            if (!empty($usuarios)) {
                for ($i=0; $i < count($usuarios); $i++) {
                    $statement = $this->pdoRbx
                    ->prepare('UPDATE usuarios SET Latitude = :latitude, Longitude = :longitude, 
                        MobileLastDataReceived = now() WHERE usuario = :usuario');
                    $result = $statement->execute([
                        'usuario' => $usuarios[$i]['usuario'],
                        'latitude' => $data['latitude'],
                        'longitude' => $data['longitude']
                    ]);
                }
            }
        }
        else {
            $statement = $this->pdoRbx
            ->prepare('UPDATE usuarios SET Latitude = :latitude, Longitude = :longitude, 
                MobileLastDataReceived = now() WHERE usuario = :usuario');
            $result = $statement->execute([
                'usuario' => $data['usuario'],
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude']
            ]);
        }
        return $result;
    }

    private function getEquipeByUsuario($usuario)
    {
        $statement = $this->pdoAxes->prepare("select u.equipe, e.nome from usuarios u left join equipes e 
        on u.equipe = e.id where usuario='".$usuario."'");
        $statement->execute();
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
        // if (!empty($result)) {
        //     return $result[0]['equipe'];
        // }
        // return '';
    }

    private function getUsuariosByEquipe($equipe, $fetchStyle): array
    {
        $statement = $this->pdoAxes->prepare("select usuario from usuarios where equipe = ".$equipe);
        $statement->execute();
        // if ($fetchStyle == 'COLUMN'){
        //     $result = $statement->fetchAll(\PDO::FETCH_COLUMN);
        // }
        // else {
        //     $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        // }
        $result = $statement->fetchAll($fetchStyle);
        return $result;
    }

    public function getGrupoUsuarios(): array
    {
        $strSQL = "select id, Grupo as Nome from isupergaus.UsuariosGrupoSetor";
        $statement = $this->pdoRbx->prepare($strSQL);
        $statement->execute();
        $grupoUsuarios = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $grupoUsuarios;
    }

    public function getUsuarios($pGrupo): array
    {
        $strSQL = '';
        if (empty($pGrupo)) {
            $strSQL = "select usuario as Nome, perfil, idgrupo from isupergaus.usuarios 
            where situacao = 'A' order by usuario";
        } else {
            $strSQL = "select usuario as Nome, perfil, idgrupo from isupergaus.usuarios 
            where situacao = 'A' and idgrupo = ".$pGrupo." order by usuario";
        }
        $statement = $this->pdoRbx->prepare($strSQL);
        $statement->execute();
        $usuarios = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $usuarios;
    }

    public function getEquipes(): array
    {
        $statement = $this->pdoAxes->prepare("select id, nome, '' MapsLat, '' MapsLng from equipes order by nome");
        $statement->execute();
        $equipes = $statement->fetchAll(\PDO::FETCH_ASSOC);

        if (!empty($equipes)){
            for ($i=0; $i < count($equipes); $i++) {
                $coordenadas = $this->getCoordenadasEquipe($equipes[$i]['id']);
                if (!empty($coordenadas)) {
                    $usuario[$i]['MapsLat'] = $coordenadas[0]['Latitude'];
                    $usuario[$i]['MapsLng'] = $coordenadas[0]['Longitude'];
                }
            }
        }
        return $equipes;
    }

    private function getCoordenadasEquipe($idEquipe) {
        $usuarios = $this->getUsuariosByEquipe($idEquipe, \PDO::FETCH_COLUMN);
        $strUsuarios = "'".implode("','",$usuarios)."'";
        $strSQL = "select usuario, Latitude, Longitude, MobileLastDataReceived 
            from isupergaus.usuarios where usuario in (".$strUsuarios.") order by MobileLastDataReceived desc limit 1";
        $statement = $this->pdoRbx->prepare($strSQL);
        $statement->execute();
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
    }

    public function getUsuariosEquipe(): array
    {
        $statement = $this->pdoAxes->prepare("select usuario, ifnull(equipe,0) equipe, '' as perfil, '' as quantAtendimentos 
        from usuarios");
        $statement->execute();
        $usuariosEquipe = $statement->fetchAll(\PDO::FETCH_ASSOC);

        if (!empty($usuariosEquipe)){
            for ($i=0; $i < count($usuariosEquipe); $i++) {
                $perfil = $this->getPerfil($usuariosEquipe[$i]['usuario']);
                $atendimentos = $this->getQuantAtendimentos($usuariosEquipe[$i]['usuario']);
                $usuariosEquipe[$i]['perfil'] = $perfil;
                $usuariosEquipe[$i]['quantAtendimentos'] = ($perfil == 'Auxiliar' && $atendimentos == 0 ? '' : $atendimentos);
            }
        }
        // Ordena o array multidimensional pelo campo 'perfil' decrescente
        // Pra sempre mostrar na listagem de equipes primeiramente o técnico
        usort($usuariosEquipe, function($a, $b) {
            return -($a['perfil'] <=> $b['perfil']);
        });
        return $usuariosEquipe;
    }

    private function getQuantAtendimentos($pUsuario) {
        $strSQL = "select Usu_Designado, count(*) as atendimentos from isupergaus.Atendimentos 
            where Usu_Designado = '".$pUsuario."' and Situacao in ('A','E') group by Usu_Designado";
        $statement = $this->pdoRbx->prepare($strSQL);
        $statement->execute();
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        if (!empty($result)) {
            return $result[0]['atendimentos'];
        }
        return 0;
    }

    private function getPerfil($pUsuario) {
        $strSQL = "select if(ifnull(perfil,0) = 7, 'Técnico', if(ifnull(perfil,0) = 19, 'Auxiliar', '')) as perfil 
            from isupergaus.usuarios where usuario='".$pUsuario."'";
        $statement = $this->pdoRbx->prepare($strSQL);
        $statement->execute();
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        if (!empty($result)) {
            return $result[0]['perfil'];
        }
        return '';
    }

    public function addEquipe($data): bool
    {
        $result = FALSE;
        $statement = $this->pdoAxes
        ->prepare("INSERT INTO equipes (Nome) VALUES (:nome)");
        $result = $statement->execute([
            'nome' => $data['nomeEquipe']
            ]);
        return $result;
    }

    public function deleteEquipe($data): bool
    {
        $result = FALSE;
        $statement = $this->pdoAxes
        ->prepare("DELETE FROM equipes WHERE id = :id");
        $result = $statement->execute([
            'id' => $data['idEquipe']
            ]);

        $statement2 = $this->pdoAxes
        ->prepare('UPDATE usuarios SET equipe = DEFAULT WHERE equipe = :equipe');
        $result2 = $statement2->execute([
        'equipe' => $data['idEquipe']
        ]);
        return $result;
    }

    public function getUsuariosSemEquipe(): array
    {
        // idgrupo: 1-Tecnologia da Informação; 4-Infraestrutura
        $strSQL = "select usuario as Nome from isupergaus.usuarios 
                    where situacao = 'A' and idgrupo in (1,4) order by usuario";
        $statement = $this->pdoRbx->prepare($strSQL);
        $statement->execute();
        $usuarios = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $result = array();
        for ($i=0; $i < count($usuarios); $i++) {
            if (!$this->UsuarioTemEquipe($usuarios[$i]['Nome'])) {
                $result[] = array('Nome' => $usuarios[$i]['Nome']);
            }
        }
        return $result;
    }

    private function UsuarioTemEquipe($usuario): bool
    {
        $return = FALSE;
        $statement = $this->pdoAxes->prepare("select usuario from usuarios where equipe is not null and usuario = '".$usuario."'");
        $statement->execute();
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        if (!empty($result)){
            $return = TRUE;
        }
        return $return;
    }

    private function UsuarioExiste($usuario): bool
    {
        $return = FALSE;
        $statement = $this->pdoAxes->prepare("select usuario from usuarios where usuario = '".$usuario."'");
        $statement->execute();
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        if (!empty($result)){
            $return = TRUE;
        }
        return $return;
    }

    public function addUsuarioEquipe($data): bool
    {
        $result = FALSE;
        if ($this->UsuarioExiste($data['membro'])) {
            $statement = $this->pdoAxes
            ->prepare('UPDATE usuarios SET equipe = :equipe WHERE usuario = :usuario');
            $result = $statement->execute([
            'equipe' => $data['idEquipe'],
            'usuario' => $data['membro']
            ]);
        } else {
            $statement = $this->pdoAxes
            ->prepare("INSERT INTO usuarios (usuario, equipe) VALUES (:usuario, :equipe)");
            $result = $statement->execute([
                'usuario' => $data['membro'],
                'equipe' => $data['idEquipe']
                ]);
        }
        return $result;
    }

    public function deleteUsuarioEquipe($data): bool
    {
        $result = FALSE;
        $statement = $this->pdoAxes
        ->prepare('UPDATE usuarios SET equipe = DEFAULT WHERE usuario = :usuario');
        $result = $statement->execute([
        'usuario' => $data['membro']
        ]);
        return $result;
    }
}
