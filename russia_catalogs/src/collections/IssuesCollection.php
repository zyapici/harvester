<?php

namespace Iprbooks\Ebs\Sdk\collections;

use Iprbooks\Ebs\Sdk\Client;
use Iprbooks\Ebs\Sdk\Core\Collection;
use Iprbooks\Ebs\Sdk\Models\Issue;

class IssuesCollection extends Collection
{

    private $apiMethod = '/2.0/resources/journals/{id}/issues';


    /**
     * Конструктор IssuesCollection
     * @param Client $client
     * @return IssuesCollection
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
        return false;
    }

    /**
     * Возвращает элемент выборки
     * @param $index
     * @return Issue
     * @throws \Exception
     */
    public function getItem($index)
    {
        parent::getItem($index);
        $response = $this->createModelWrapper($this->data[$index]);
        $item = new Issue($this->getClient(), $response);
        return $item;
    }

}