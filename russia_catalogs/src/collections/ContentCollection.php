<?php

namespace Iprbooks\Ebs\Sdk\collections;

use Iprbooks\Ebs\Sdk\Models\Content;

class ContentCollection
{

    /*
     * Содержание, массив
     */
    private $contentList;


    /**
     * Конструктор ContentCollection.
     * @param array $content - содержание
     */
    public function __construct(array $content)
    {
        $this->contentList = $content;
        return $this;
    }

    /**
     * Возвращает количество элементов в оглавлении
     * @return int
     */
    public function getContentCount()
    {
        if ($this->contentList) {
            return count($this->contentList);
        } else {
            return 0;
        }
    }

    /**
     * Получить элемент содержания
     * @param $index - индекс
     * @return Content - элемент сдержания
     * @throws \Exception
     */
    public
    function get($index)
    {
        if (0 <= $index && $index < $this->getContentCount()) {
            return new Content($this->contentList[$index]);
        } else if ($this->getContentCount() == 0) {
            throw new \Exception('content is empty');
        } else {
            throw new \Exception('out of bounds');
        }
    }

}