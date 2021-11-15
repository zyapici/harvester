<?php

namespace Iprbooks\Ebs\Sdk\collections;


use Iprbooks\Ebs\Sdk\Client;
use Iprbooks\Ebs\Sdk\Core\Collection;
use Iprbooks\Ebs\Sdk\Models\Journal;

class JournalsCollection extends Collection
{
    /*
     * фильтрация по заглавию
     */
    const TITLE = 'title';

    /*
     * по издательству
     */
    const PUBHOUSE = 'pubhouse';

    private $apiMethod = '/2.0/resources/journals/';


    /**
     * Конструктор JournalsCollection
     * @param Client $client
     * @return JournalsCollection
     * @throws \Exception
     */
    public function __construct(Client $client)
    {
        parent::__construct($client);
        return $this;
    }


    /**
     * Возвращает метод api
     * @return string
     */
    protected function getApiMethod()
    {
        return $this->apiMethod;
    }

    /**
     * Проверка значений фильтра
     * @param $field
     * @return boolean
     */
    protected function checkFilterFields($field)
    {
        if ($field == self::TITLE || $field == self::PUBHOUSE) {
            return true;
        }
        return false;
    }

    /**
     * Возвращает элемент выборки
     * @param $index
     * @return Journal
     * @throws \Exception
     */
    public function getItem($index)
    {
        parent::getItem($index);
        $response = $this->createModelWrapper($this->data[$index]);
        $item = new Journal($this->getClient(), $response);
        return $item;
    }

}