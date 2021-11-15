<?php

namespace Iprbooks\Ebs\Sdk\Core;

class Curl
{

    const HOST = 'https://api.iprbooks.ru';

    const X_API_KEY = 'pG7xLieVraKMHksK';

    /**
     * Отправка запроса
     * @param $host
     * @param $apiMethod
     * @param $token
     * @param array $params
     * @return array|mixed
     */
    public static function exec($apiMethod, $token, array $params)
    {

        if (!empty($params)) {
            $apiMethod = sprintf("%s?%s", $apiMethod, http_build_query($params));
        };

        $headers = array(
            'Authorization: Bearer ' . $token,
            'X-APIKey: ' . self::X_API_KEY,
            'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
            'Accept: application/json'
        );

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_URL, self::HOST . $apiMethod);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $curlResult = curl_exec($curl);


        if (curl_errno($curl)) {
            return Curl::getError('Curl error ' . curl_errno($curl) . ': ' . curl_error($curl), 500);
        }

        $response = json_decode($curlResult, true);
        return $response;
    }


    /**
     * Вормирование сообщения в случае ошибки
     * @param $message
     * @param $code
     * @return array
     */
    private static function getError($message, $code)
    {
        return array(
            'success' => false,
            'message' => $message,
            'total' => 0,
            'status' => $code,
            'data' => null,
        );
    }
}