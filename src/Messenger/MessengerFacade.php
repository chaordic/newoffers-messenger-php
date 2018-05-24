<?php

namespace Linx\Messenger;

use Illuminate\Support\Facades\Facade;
use Linx\Messenger\Messenger;

class MessengerFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Messenger::class;
    }
}
