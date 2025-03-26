<?php

namespace HCC\Core\Hook;

/**
 * Class that describes a data structure
 */
class CallbacksStore
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