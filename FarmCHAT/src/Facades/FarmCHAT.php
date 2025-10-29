<?php

namespace FarmCHAT\Facades;

use Illuminate\Support\Facades\Facade;

class FarmCHATMessenger extends Facade 
{

    protected static function getFacadeAccessor() 
    { 
       return 'FarmCHATMessenger'; 
    }
}