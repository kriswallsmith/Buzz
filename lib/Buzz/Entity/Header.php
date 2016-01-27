<?php

namespace Buzz\Entity;

use Buzz\Exception\InvalidArgumentException;

class Header
{
    private $resource = null;
    private $size = 0;
    private $dataList = array();

    public function __construct($resource)
    {
        $this->setResource($resource);
    }

    /**
     * @return mixed
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param mixed $resource
     */
    private function setResource($resource)
    {
        if (is_resource($resource)) {
            $this->resource = $resource;
        } else {
            throw new InvalidArgumentException('The Header object expects a curl resource.');
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return mixed
     */
    public function getDataList()
    {
        return $this->dataList;
    }

    public function getLastRedirectionDataList()
    {
        $lastRedirectionDataList = array();
        for ($i = 0, $max = count($this->dataList) - 1; $i < $max; $i++)
        {
            $header = $this->dataList[$i];
            if (trim($header) == '') {
                $lastRedirectionDataList = array();
            } else {
                $lastRedirectionDataList[] = $header;
            }
        }

        return $lastRedirectionDataList;
    }

    /**
     * @param mixed $dataList
     */
    public function addDataList($dataList)
    {
        $this->dataList[] = $dataList;
        $this->size += strlen($dataList);
    }

    /**
     * @return mixed
     */
    public function getDataString()
    {
        return explode("\n", $this->dataList);
    }

}