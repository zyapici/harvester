<?php

namespace Iprbooks\Ebs\Sdk\Core;

use Exception;
use Iprbooks\Ebs\Sdk\Client;

class Response
{

    /*
     * Инстанс клиента
     */
    private $client;

    /*
     * Ответ
     */
    protected $response;

    /*
     * Данные ответа
     */
    protected $data;


    /**
     * Конструктор Response
     * @param Client $client
     * @param $response
     * @throws Exception
     */
    public function __construct(Client $client, $response = null)
    {
        if (!$client) {
            throw new Exception('client is not init');
        }

        $this->client = $client;
        $this->response = $response;
        $this->data = $response['data'];
        return $this;
    }


    /**
     * Получить клиент
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Получить значение по ключу
     * @param $name
     * @return string
     */
    protected function getValue($name)
    {
        if (is_array($this->data) && array_key_exists($name, $this->data)) {
            return $this->data[$name];
        } else {
            return '';
        }
    }


    /**
     * Возвращает статус запроса
     * @return mixed
     */
    public function getSuccess()
    {
        return $this->response['success'];
    }

    /**
     * Возвращает текстовое сообщение ошибки
     * @return mixed
     */
    public function getMessage()
    {
        return $this->response['message'];
    }

    /**
     * Возвращает общее кол-во элементов ответа
     * @return mixed
     */
    public function getTotalCount()
    {
        return $this->response['total'];
    }

    /**
     * Возвращает статус ответа
     * @return mixed
     */
    public function getStatus()
    {
        return $this->response['status'];
    }

}