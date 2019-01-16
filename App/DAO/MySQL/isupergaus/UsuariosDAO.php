<?php

namespace App\DAO\MySQL\isupergaus;

use App\Models\MySQL\isupergaus\UsuarioModel;


class UsuariosDAO extends Conexao
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getUsuario($paramUsuario, $paramSenha): array
    {
        /*
        $usuario = $this->pdoRbx
            ->query('SELECT
                    usuario,
                    Nome,
                    situacao,
                    Foto,
                    idgrupo,
                    perfil 
                FROM usuarios WHERE usuario=\'kalley\';')
            ->fetchAll(\PDO::FETCH_ASSOC);
        */
        $statement = $this->pdoRbx
            ->prepare("SELECT usuario, Terminal senha, Nome, idgrupo, 
            if(master='S','G',if(perfil=19,'A','T')) as tipo, 
            MobileDevice, MobileTrackingTrace, MobileDeviceId, Latitude, Longitude, MobileLastDataReceived, MobileLastLogin, 
            concat('https://rbx.axes.com.br/routerbox/file/img/',if(ifnull(Foto,'')='','contact_default.png',Foto)) as Foto 
            FROM usuarios 
            WHERE usuario = :usuario AND Terminal = :senha AND situacao = 'A' ;");

        $statement->bindParam(':usuario', $paramUsuario, \PDO::PARAM_STR);
        $statement->bindParam(':senha', $paramSenha, \PDO::PARAM_STR);
        $statement->execute();
        $usuario = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $usuario;

        // Utilizar a senha do banco de dados auxiliar
        /*
        if (!empty($usuario)){
            $statement2 = $this->pdoAxes
            ->prepare("SELECT usuario 
            FROM usuarios 
            WHERE usuario = :usuario AND senha = :senha ;");
            $statement2->bindParam(':usuario', $paramUsuario, \PDO::PARAM_STR);
            $statement2->bindParam(':senha', $paramSenha, \PDO::PARAM_STR);
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
}
