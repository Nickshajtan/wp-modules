<?php

namespace HCC\Events;

/**
 * Class that describes a data structure
 */
class CollectionCallbacksStore
{
    public $add;
    public $remove;
    public $execute;

    public function __construct(
        callable $add,
        callable $remove,
        callable $execute
    ) {
        $this->add = $add;
        $this->remove = $remove;
        $this->execute = $execute;
    }
}