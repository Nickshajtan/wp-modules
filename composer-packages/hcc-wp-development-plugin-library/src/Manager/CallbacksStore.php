<?php

namespace HCC\Plugin\Manager;

/**
 * Class that describes a data structure
 */
class CallbacksStore
{
    public $pathReader;

    public $urlReader;
    public $dataReader;

    public function __construct(callable $pathReader, callable $urlReader, callable $dataReader)
    {
        $this->pathReader = $pathReader;
        $this->urlReader = $urlReader;
        $this->dataReader = $dataReader;
    }
}