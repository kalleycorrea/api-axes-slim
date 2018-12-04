<?php

namespace App\DAO\MySQL\isupergaus;

use App\Models\MySQL\isupergaus\UsuarioModel;


class UsuariosDAO extends Conexao
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getUsuario($paramUsuario): array
    {
        /*
        $usuario = $this->pdo
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
        $statement = $this->pdo
            ->prepare("SELECT usuario, Nome, situacao, idgrupo, perfil, 
            MobileDevice, MobileTrackingTrace, MobileDeviceId, Latitude, Longitude, 
            MobileLastDataReceived, MobileLastLogin, 
            concat('https://rbx.axes.com.br/routerbox/file/img/',if(ifnull(Foto,'')='','contact_default.png',Foto)) as Foto 
            FROM usuarios 
            WHERE usuario = :usuario;");

        $statement->bindParam(':usuario', $paramUsuario, \PDO::PARAM_STR);
        $statement->execute();
        $usuario = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $usuario;
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

        $statement = $this->pdo->prepare($query);
        $result = $statement->execute($params);

        /*
        $statement = $this->pdo
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
}
