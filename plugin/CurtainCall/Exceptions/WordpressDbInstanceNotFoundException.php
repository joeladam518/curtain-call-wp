<?php

declare(strict_types=1);

namespace CurtainCall\Exceptions;

final class WordpressDbInstanceNotFoundException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('The WordPress database instance was not found.');
    }
}
