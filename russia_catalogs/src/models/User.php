<?php

namespace Iprbooks\Ebs\Sdk\Models;

use Iprbooks\Ebs\Sdk\Client;
use Iprbooks\Ebs\Sdk\Core\Model;

class User extends Model
{

    /*
     * Студент
     */
    const STUDENT = 1;

    /*
     * Аспирант
     */
    const PG = 2;

    /*
     * Преподаватель
     */
    const PROFESSOR = 3;

    /*
     * Отсутствует определение типа пользователя
     */
    const OTHER = 4;

    private $apiMethod = '/2.0/security/users/get/{id}';


    /**
     * User constructor.
     * @param Client $client
     * @param null $response
     * @return User
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
     * Возвращает имя пользователя
     * @return mixed
     */
    public function getUsername()
    {
        return $this->getValue('username');
    }

    /**
     * Возвращает полное имя
     * @return mixed
     */
    public function getFullName()
    {
        return $this->getValue('fullname');
    }

    /**
     * Возвращает email
     * @return mixed
     */
    public function getEmail()
    {
        return $this->getValue('email');
    }

    /**
     * Возвращает статус
     * @return mixed
     */
    public function getBlocked()
    {
        return $this->getValue('blocked');
    }

    /**
     * Возвращает тип пользователя
     * @return mixed
     */
    public function getUserType()
    {
        return $this->getValue('user_type');
    }

    /**
     * Возвращает класс
     * @return mixed
     */
    public function getClass()
    {
        return $this->getValue('class');
    }

    /**
     * Возвращает специальность
     * @return mixed
     */
    public function getSpecialty()
    {
        return $this->getValue('specialty');
    }

    /**
     * Возвращает группу
     * @return mixed
     */
    public function getGroup()
    {
        return $this->getValue('group');
    }

    /**
     * Возвращает факультет
     * @return mixed
     */
    public function getFacultet()
    {
        return $this->getValue('facultet');
    }

    /**
     * Возвращает отделение
     * @return mixed
     */
    public function getDepartment()
    {
        return $this->getValue('department');
    }

    /**
     * Возвращает дату регистрации
     * @return mixed
     */
    public function getRegistrationDate()
    {
        return $this->getValue('registration_date');
    }

    /**
     * Возвращает дату блокировки
     * @return mixed
     */
    public function getBlockedAfter()
    {
        return $this->getValue('blockedafter');
    }

}