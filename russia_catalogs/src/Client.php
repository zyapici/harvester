<?php

namespace Iprbooks\Ebs\Sdk;

use Exception;
use Firebase\JWT\JWT;
use Iprbooks\Ebs\Sdk\Core\Curl;

final class Client
{
    /*
     * id пользователя
     */
    private $clientId;

    /*
     * Секретный ключ
     */
    private $secretKey;


    /**
     * Конструктор Client
     * @param $clientId
     * @param $secretKey
     * @throws Exception
     */
    public function __construct($clientId, $secretKey)
    {
        if (!is_numeric($clientId)) {
            throw new Exception('$clientId must be numeric');
        }

        $this->clientId = $clientId;
        $this->secretKey = $secretKey;
    }

    public function makeRequest($apiMethod, array $params)
    {
        $json = array(
            "client_id" => $this->clientId,
            "time" => time(),
            "ip" => $_SERVER['SERVER_ADDR']
        );

        $token = JWT::encode($json, $this->secretKey);
        $params = array_merge(array("client_id" => $this->clientId), $params);

        $result = Curl::exec($apiMethod, $token, $params);
        return $result;
    }

}