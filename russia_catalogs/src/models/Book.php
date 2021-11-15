<?php

namespace Iprbooks\Ebs\Sdk\Models;

use Exception;
use Iprbooks\Ebs\Sdk\Client;
use Iprbooks\Ebs\Sdk\collections\ContentCollection;
use Iprbooks\Ebs\Sdk\Core\Model;

class Book extends Model
{

    private $apiMethod = '/2.0/resources/books/get/{id}';


    /**
     * Конструктор Book
     * @param Client $client
     * @param null $response
     * @throws Exception
     */
    public function __construct(Client $client, $response = null)
    {
        parent::__construct($client, $response);
        return $this;
    }

    /**
     * Возвращает метод апи для вызова
     */
    protected function getApiMethod()
    {
        return $this->apiMethod;
    }


    /**
     * Возвращает список содержания
     * @return ContentCollection
     * @throws Exception
     */
    public function getContent()
    {
        if (!array_key_exists('content', $this->data)) {
            $content = array();
        } else {
            $content = $this->data['content'];
        }
        return new ContentCollection($content);
    }

    /**
     * Возрващает id книги
     * @return mixed
     */
    public function getId()
    {
        return $this->getValue('id');
    }

    /**
     * Возвращает название книги
     * @return mixed
     */
    public function getTitle()
    {
        return $this->getValue('title');
    }

    /**
     * Возвращает дополнительное название книги
     * @return mixed
     */
    public function getTitleAdditional()
    {
        return $this->getValue('title_additional');
    }

    /**
     * Возвращает издательство
     * @return mixed
     */
    public function getPubhouse()
    {
        return $this->getValue('pubhouse');
    }

    /**
     * Возвращает список авторов
     * @return mixed
     */
    public function getAuthors()
    {
        return $this->getValue('authors');
    }

    /**
     * @return mixed
     */
    public function getLiability()
    {
        return $this->getValue('liability');
    }

    /**
     * Возвращает год издания
     * @return mixed
     */
    public function getPubyear()
    {
        return $this->getValue('pubyear');
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
     * Возвращает ключевые слова для поиска
     * @return mixed
     */
    public function getKeywords()
    {
        return $this->getValue('keywords');
    }

    /**
     * Возвращает тип издания
     * @return mixed
     */
    public function getPubtype()
    {
        return $this->getValue('pubtype');
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
     * Возвращает ссылку на книгу
     * @return mixed
     */
    public function getUrl()
    {
        return $this->getValue('url');
    }

    /**
     * Возвращает ссылку на обложку книги
     * @return mixed
     */
    public function getImage()
    {
        return $this->getValue('image');
    }

}