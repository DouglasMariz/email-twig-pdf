<?php

namespace DouglasMariz\Email\Twig\Pdf;

class EmailTwigPdf_Auth
{
    private $userName;
    private $password;
    private $from;
    private $fromName;

    private $smtp;
    private $port;
    private $encryption;

    private $authKeyMap = array(
        'userName' => 'Nome de Usuário',
        'password' => 'Senha',
        'from' => 'E-mail do Remetente',
        'fromName' => 'Nome do Remetente',
        'smtp' => 'Endereço do Servidor',
        'port' => 'Porta do Servidor',
        'encryption' => 'Criptografia do Servidor'
    );

    /**
     * EmailTwigPdf_Auth constructor.
     * @param array $credentials
     * @throws \Exception
     */
    public function __construct($credentials = [])
    {
        $this->setCredentials($credentials);
        $this->validEmailSettings();
    }

    /**
     * Mapear uma chave de autenticação para a variável do objeto Auth e retorna-la.
     * @param $key
     * @return mixed
     * @throws EmailTwigPdf_AuthException
     */
    public function getAuthKeyVar($key)
    {
        if (!isset($this->authKeyMap[$key])) {
            $msg = "A Chave de Autenticação $key solicitada é inválida";
            throw new EmailTwigPdf_AuthException($msg);
        }
        $property = $key;
        return $this->$property;
    }

    /**
     * Validates the email Settings
     *
     * @author Douglas Mariz <douglasmariz.developer@gmail.com>
     *
     * @throws \Exception
     */
    private function validEmailSettings()
    {
        foreach ($this->authKeyMap as $property => $value) {
            if (!isset($this->$property)) {
                throw new EmailTwigPdf_AuthException('Configurações de Email: "' . $value . '" não configurado!', EmailTwigPdf_ErrorsCode::AUTH_CONFIG);
            }
        }
    }

    /**
     * @param $credentials
     */
    private function setCredentials($credentials)
    {
        foreach ($this->authKeyMap as $property => $value) {
            if(array_key_exists($property, $credentials)) {
                $this->$property = $credentials[$property];
            }
        }
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return string
     */
    public function getFromName()
    {
        return $this->fromName;
    }

    /**
     * @return string
     */
    public function getSmtp()
    {
        return $this->smtp;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getEncryption()
    {
        return $this->encryption;
    }


}