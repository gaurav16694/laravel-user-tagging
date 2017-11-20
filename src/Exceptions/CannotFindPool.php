<?php

namespace gaurav\tagging\Exceptions;

use Exception;

class CannotFindPool extends Exception
{
    public static function create($name)
    {
        return new static('Cannot find the mention pool for '.$name);
    }
}
