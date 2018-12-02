<?php

namespace App\Models\MySQL\isupergaus;

final class UsuarioModel
{
    /**
     * @var string
     */
    private $usuario;
    /**
     * @var int
     */
    private $mobileDevice;
    /**
     * @var int
     */
    private $mobileTrackingTrace;
    /**
     * @var int
     */
    private $mobileDeviceId;
    /**
     * @var double
     */
    private $latitude;
    /**
     * @var double
     */
    private $longitude;
    /**
     * @var string
     */
    private $mobileLastDataReceived;
    /**
     * @var string
     */
    private $mobileLastLogin;

    /**
     * @return string
     */
    public function getUsuario()
    {
        return $this->usuario;
    }
    /**
     * @param string $usuario
     * @return UsuarioModel
     */
    public function setUsuario(string $usuario): UsuarioModel
    {
        $this->usuario = $usuario;
        return $this;
    }

    /**
     * @return int
     */
    public function getMobileDevice()
    {
        return $this->mobileDevice;
    }
    /**
     * @param int $mobileDevice
     * @return UsuarioModel
     */
    public function setMobileDevice(int $mobileDevice): UsuarioModel
    {
        $this->mobileDevice = $mobileDevice;
        return $this;
    }

    /**
     * @return int
     */
    public function getMobileTrackingTrace()
    {
        return $this->mobileTrackingTrace;
    }
    /**
     * @param int $mobileTrackingTrace
     * @return UsuarioModel
     */
    public function setMobileTrackingTrace(int $mobileTrackingTrace): UsuarioModel
    {
        $this->mobileTrackingTrace = $mobileTrackingTrace;
        return $this;
    }

    /**
     * @return int
     */
    public function getMobileDeviceId()
    {
        return $this->mobileDeviceId;
    }
    /**
     * @param int $mobileDeviceId
     * @return UsuarioModel
     */
    public function setMobileDeviceId(int $mobileDeviceId): UsuarioModel
    {
        $this->mobileDeviceId = $mobileDeviceId;
        return $this;
    }

    /**
     * @return double
     */
    public function getLatitude()
    {
        return $this->latitude;
    }
    /**
     * @param double $latitude
     * @return UsuarioModel
     */
    public function setLatitude($latitude): UsuarioModel
    {
        $this->latitude = $latitude;
        return $this;
    }

    /**
     * @return double
     */
    public function getLongitude()
    {
        return $this->longitude;
    }
    /**
     * @param double $longitude
     * @return UsuarioModel
     */
    public function setLongitude($longitude): UsuarioModel
    {
        $this->longitude = $longitude;
        return $this;
    }

    /**
     * @return string
     */
    public function getMobileLastDataReceived()
    {
        return $this->mobileLastDataReceived;
    }
    /**
     * @param string $mobileLastDataReceived
     * @return UsuarioModel
     */
    public function setMobileLastDataReceived(string $mobileLastDataReceived): UsuarioModel
    {
        $this->mobileLastDataReceived = $mobileLastDataReceived;
        return $this;
    }

    /**
     * @return string
     */
    public function getMobileLastLogin()
    {
        return $this->mobileLastLogin;
    }
    /**
     * @param string $mobileLastLogin
     * @return UsuarioModel
     */
    public function setMobileLastLogin(string $mobileLastLogin): UsuarioModel
    {
        $this->mobileLastLogin = $mobileLastLogin;
        return $this;
    }
}
