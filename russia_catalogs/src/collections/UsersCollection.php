<?php

namespace Iprbooks\Ebs\Sdk\collections;

use Iprbooks\Ebs\Sdk\Client;
use Iprbooks\Ebs\Sdk\Core\Collection;
use Iprbooks\Ebs\Sdk\Models\User;

class UsersCollection extends Collection
{

    /*
     * фильтрация по email-адресу
     */
    const EMAIL = 'email';

    /*
     * фильтрация по логину пользователя
     */
    const USERNAME = 'username';

    /*
     * фильтрация по полному имени пользователя
     */
    const FULLNAME = 'fullname';

    private $apiMethod = '/2.0/security/users';


    /**
     * Конструктор UsersCollection
     * @param Client $client
     * @return UsersCollection
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
        if ($field == self::EMAIL || $field == self::USERNAME || $field == self::FULLNAME) {
            return true;
        }
        return false;
    }

    /**
     * Возвращает элемент выборки
     * @param $index
     * @return User
     * @throws \Exception
     */
    public function getItem($index)
    {
        parent::getItem($index);
        $response = $this->createModelWrapper($this->data[$index]);
        $item = new User($this->getClient(), $response);
        return $item;
    }

}