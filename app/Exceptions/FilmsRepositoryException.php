<?php

namespace App\Exceptions;

class FilmsRepositoryException extends \Exception
{
    public function getStatusCode()
    {
        return 500;
    }
}
