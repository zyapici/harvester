<?php

namespace Iprbooks\Ebs\Sdk\Models;

class Content
{

    /*
     * Элемент содержания
     */
    private $content;


    /**
     * Конструктор Content
     * @param $content
     */
    public function __construct($content)
    {
        $this->content = $content;
    }

    /**
     * Возвращает номер страницы оглавления
     * @return mixed
     */
    public function getPage()
    {
        if ($this->content) {
            $keys = array_keys($this->content);
            return $keys[0];
        } else {
            return '';
        }
    }

    /**
     * Возвращает заголовок оглвления
     * @return mixed
     */
    public function getDescription()
    {
        if ($this->content) {
            return $this->content[$this->getPage()];
        } else {
            return '';
        }
    }

}