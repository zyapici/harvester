<?php

namespace Iprbooks\Ebs\Sdk\Models;

use Iprbooks\Ebs\Sdk\Client;
use Iprbooks\Ebs\Sdk\Core\Model;

class Journal extends Model
{

    private $apiMethod = '/2.0/resources/journals/get/{id}';


    /**
     * Конструктор Journal
     * @param Client $client
     * @param $response
     * @throws \Exception
     */
    public function __construct(Client $client, $response = null)
    {
        parent::__construct($client, $response);
        return $this;
    }

    /**
     * Получить метод api для вызова, опеределяется в потомках
     */
    protected function getApiMethod()
    {
        return $this->apiMethod;
    }

    /**
     * Возвращает id журнала
     * @return mixed
     */
    public function getId()
    {
        return $this->getValue('id');
    }

    /**
     * Возвращает заголовок журнала
     * @return mixed
     */
    public function getTitle()
    {
        return $this->getValue('title');
    }

    /**
     * Возвращает издательство журнала
     * @return mixed
     */
    public function getPubhouse()
    {
        return $this->getValue('pubhouse');
    }

    /**
     * Возвращает описание журнала
     * @return mixed
     */
    public function getDescription()
    {
        return $this->getValue('description');
    }

    /**
     * Возвращает список ключевых слов для поиска
     * @return mixed
     */
    public function getKeywords()
    {
        return $this->getValue('keywords');
    }

    /**
     * Возвращает флаг доп подписки
     * @return mixed
     */
    public function getAdditSubscribe()
    {
        return $this->getValue('addit_subscribe');
    }

    /**
     * Возвращает ссылку на журнал
     * @return mixed
     */
    public function getUrl()
    {
        return $this->getValue('url');
    }

    /**
     * Возвращает ссылку на обложку
     * @return mixed
     */
    public function getImage()
    {
        return $this->getValue('image');
    }

}