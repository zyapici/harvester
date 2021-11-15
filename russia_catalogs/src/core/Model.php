<?php

namespace Iprbooks\Ebs\Sdk\Core;

use Exception;
use Iprbooks\Ebs\Sdk\Client;

abstract class Model extends Response
{

    /**
     * Конструктор Model
     * @param Client $client
     * @param $response
     * @throws Exception
     */
    public function __construct(Client $client, $response = null)
    {
        parent::__construct($client, $response);
        return $this;
    }


    /**
     * Получить метод api для вызова, опеределяется в потомках
     */
    abstract protected function getApiMethod();

    /**
     * Отправка запроса
     * @param $id
     */
    public function get($id)
    {
        $apiMethod = $this->getApiMethod();

        if ($id) {
            $apiMethod = str_replace('{id}', $id, $apiMethod);
        }

        $this->response = $this->getClient()->makeRequest($apiMethod, array());
        if (array_key_exists('data', $this->response)) {
            $this->data = $this->response['data'];
        } else {
            $this->data = array();
        }
    }

}