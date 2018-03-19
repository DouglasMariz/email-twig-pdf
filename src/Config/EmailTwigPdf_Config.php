<?php

namespace DouglasMariz\Email\Twig\Pdf;

/**
 * Class EmailTwigPdf_Config
 * @package DouglasMariz\Email\Twig\Pdf
 */
class EmailTwigPdf_Config
{
    private $template_path;

    private $authKeyMap = array(
        'template_path' => 'Diretório dos templates',
    );

    /**
     * EmailTwigPdf_Config constructor.
     * @param array $config
     * @throws \Exception
     */
    public function __construct($config = [])
    {
        $this->setConfig($config);
        $this->validEmailSettings();
    }

    /**
     * Mapear uma chave de autenticação para a variável do objeto Auth e retorna-la.
     * @param $key
     * @return mixed
     * @throws EmailTwigPdf_ConfigException
     */
    public function getAuthKeyVar($key)
    {
        if (!isset($this->authKeyMap[$key])) {
            $msg = "A Chave $key solicitada é inválida";
            throw new EmailTwigPdf_ConfigException($msg);
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
                throw new EmailTwigPdf_ConfigException('Configurações de Email: "' . $value . '" não configurado!', EmailTwigPdf_ErrorsCode::AUTH_CONFIG);
            }
        }
    }

    /**
     * @param $config
     */
    private function setConfig($config)
    {
        foreach ($this->authKeyMap as $property => $value) {
            if(array_key_exists($property, $config)) {
                $this->$property = $config[$property];
            }
        }
    }

    /**
     * @return mixed
     */
    public function getTemplatePath()
    {
        return $this->template_path;
    }

}
