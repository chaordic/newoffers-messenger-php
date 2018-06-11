<?php

namespace Linx\Messenger\Exceptions;

class NoResourceFoundException extends \Exception {
    public function __construct()
    {
        parent::__construct('No resource found', 404);
    }
}
