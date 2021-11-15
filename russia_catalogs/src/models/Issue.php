<?php

namespace Iprbooks\Ebs\Sdk\Models;

use Iprbooks\Ebs\Sdk\Client;
use Iprbooks\Ebs\Sdk\Core\Model;

class Issue extends Model
{

    private $apiMethod = '/2.0/resources/journals/issues/get/{id}';

    /**
     * Конструктор Issue
     * @param Client $client
     * @param null $response
     * @return Issue
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
     * Возвращает id
     * @return mixed
     */
    public function getId()
    {
        return $this->getValue('id');
    }

    /**
     * Возвращает описание
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
     * Возвращает название
     * @return mixed
     */
    public function getTitle()
    {
        return $this->getValue('title');
    }

    /**
     * Возвращает год публикации
     * @return mixed
     */
    public function getIssuePubyear()
    {
        return $this->getValue('issue_pubyear');
    }

    /**
     * Возвращает ссылку
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