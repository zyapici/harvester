<?php

namespace Iprbooks\Ebs\Sdk;

use Exception;
use Iprbooks\Ebs\Sdk\Core\Response;
use Iprbooks\Ebs\Sdk\Models\User;

class UserManager extends Response
{

    /**
     * Конструктор UserManager
     * @param $client
     * @return UserManager
     * @throws Exception
     */
    public function __construct(Client $client)
    {
        parent::__construct($client);
        if (!$client) {
            throw new Exception('client is not init');
        }
        return $this;
    }


    /**
     * Создает нового пользователя в ЭБС
     * @param $email
     * @param $fullname
     * @param $pass
     * @param $userType
     * @return User
     * @throws Exception
     */
    public function registerNewUser($email, $fullname, $pass, $userType = User::OTHER)
    {
        $apiMethod = '/2.0/security/users/add';
        $params = array(
            'email' => $email,
            'fullname' => $fullname,
            'password' => $pass,
            'user_type' => $userType
        );

        $this->response = $this->getClient()->makeRequest($apiMethod, $params);
        $this->data = $this->response['data'];
        $user = new User($this->getClient(), $this->response);
        return $user;
    }

    /**
     * Блокировка пользователя
     * @param $id
     * @return bool|mixed
     */
    public function deleteUser($id)
    {
        if (!$id) {
            return false;
        }

        $apiMethod = '/2.0/security/users/delete/{id}';
        $apiMethod = str_replace('{id}', $id, $apiMethod);

        $this->response = $this->getClient()->makeRequest($apiMethod, array());
        $this->data = $this->response['data'];
        return $this->getSuccess();
    }

    /**
     * Восстановление пользователя
     * @param $id
     * @return bool|mixed
     */
    public function restoreUser($id)
    {
        if (!$id) {
            return false;
        }

        $apiMethod = '/2.0/security/users/restore/{id}';
        $apiMethod = str_replace('{id}', $id, $apiMethod);

        $this->response = $this->getClient()->makeRequest($apiMethod, array());
        $this->data = $this->response['data'];
        return $this->getSuccess();
    }

}