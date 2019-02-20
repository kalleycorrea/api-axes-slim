<?php

namespace App\DAO\MySQL\isupergaus;

use App\Models\MySQL\isupergaus\UsuarioModel;


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
            ->prepare("SELECT usuario, Terminal senha, Nome, idgrupo, 
            if(master='S','G',if(perfil=19,'A','T')) as tipo, '' as equipe, 
            MobileDeviceId, Latitude, Longitude, MobileLastDataReceived 
            FROM usuarios 
            WHERE usuario = :usuario AND Terminal = :senha AND situacao = 'A' ;");
        // concat('".$urlImgUserRbx."',if(ifnull(Foto,'')='','contact_default.png',Foto)) as Foto 

        $statement->bindParam(':usuario', $pUsuario, \PDO::PARAM_STR);
        $statement->bindParam(':senha', $pSenha, \PDO::PARAM_STR);
        $statement->execute();
        $usuario = $statement->fetchAll(\PDO::FETCH_ASSOC);

        if (!empty($usuario)){
            for ($i=0; $i < count($usuario); $i++) {
                $equipe = $this->getEquipe($usuario[$i]['usuario']);
                $usuario[$i]['equipe'] = $equipe;
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

    private function getEquipe($usuario)
    {
        $statement = $this->pdoAxes->prepare("select equipe from usuarios where usuario='".$usuario."'");
        $statement->execute();
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        if (!empty($result)) {
            return $result[0]['equipe'];
        }
        return '';
    }

    public function updateUsuario(UsuarioModel $usuario): bool
    {
        $result = FALSE;
        $fields = $params = array();

        if (!is_null($usuario->getMobileDevice())){
            $fields[] = "MobileDevice = :mobileDevice";
            $params = array_merge($params, array(':mobileDevice' => $usuario->getMobileDevice()));
        }
        if (!is_null($usuario->getMobileTrackingTrace())){
            $fields[] = "MobileTrackingTrace = :mobileTrackingTrace";
            $params = array_merge($params, array(':mobileTrackingTrace' => $usuario->getMobileTrackingTrace()));
        }
        if (!is_null($usuario->getMobileDeviceId())){
            $fields[] = "MobileDeviceId = :mobileDeviceId";
            $params = array_merge($params, array(':mobileDeviceId' => $usuario->getMobileDeviceId()));
        }
        if (!is_null($usuario->getLatitude())){
            $fields[] = "Latitude = :latitude";
            $params = array_merge($params, array(':latitude' => $usuario->getLatitude()));
        }
        if (!is_null($usuario->getLongitude())){
            $fields[] = "Longitude = :longitude";
            $params = array_merge($params, array(':longitude' => $usuario->getLongitude()));
        }
        if (!is_null($usuario->getMobileLastDataReceived())){
            $fields[] = "MobileLastDataReceived = :mobileLastDataReceived";
            $params = array_merge($params, array(':mobileLastDataReceived' => $usuario->getMobileLastDataReceived()));
        }
        if (!is_null($usuario->getMobileLastLogin())){
            $fields[] = "MobileLastLogin = :mobileLastLogin";
            $params = array_merge($params, array(':mobileLastLogin' => $usuario->getMobileLastLogin()));
        }

        $params = array_merge($params, array(':usuario' => $usuario->getUsuario()));

        $query = "UPDATE usuarios SET ";
        if (!empty($fields)) {
            $query .=  implode(', ', $fields) . ' WHERE usuario = :usuario';
        }

        $statement = $this->pdoRbx->prepare($query);
        $result = $statement->execute($params);

        /*
        $statement = $this->pdoRbx
            ->prepare('UPDATE usuarios SET 
                MobileDevice = :mobileDevice, 
                MobileTrackingTrace = :mobileTrackingTrace, 
                MobileDeviceId = :mobileDeviceId, 
                Latitude = :latitude, 
                Longitude = :longitude, 
                MobileLastDataReceived = :mobileLastDataReceived, 
                MobileLastLogin = :mobileLastLogin 
                WHERE usuario = :usuario;');
        $statement->execute([
            'mobileDevice' => $usuario->getMobileDevice(),
            'mobileTrackingTrace' => $usuario->getMobileTrackingTrace(),
            'mobileDeviceId' => $usuario->getMobileDeviceId(),
            'latitude' => $usuario->getLatitude(),
            'longitude' => $usuario->getLongitude(),
            'mobileLastDataReceived' => $usuario->getMobileLastDataReceived(),
            'mobileLastLogin' => $usuario->getMobileLastLogin(),
            'usuario' => $usuario->getUsuario() 
        ]);
        */
        return $result;
    }

    private function setPassword(){

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
        $statement = $this->pdoAxes->prepare('select id, nome from equipes order by nome');
        $statement->execute();
        $equipes = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $equipes;
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

        //$usuario = array_merge($usuario, array('setpassword' => 'N'));
        //array_push($usuario, array('setpassword' => 'N'));
        //$usuario += ['setpassword' => 'N'];

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
