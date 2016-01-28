<?php

namespace Buzz\Entity;

class Header
{
    private $size = 0;
    private $dataList = array();

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

    /**
     * Returns the last headers blocks despite the number of redirections
     *
     * @return array
     */
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