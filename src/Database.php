<?php

namespace Fmw;

use mysqli;

class Database extends mysqli
{
    public function __construct(array $config)
    {
        parent::__construct($config['hostname'], $config['username'], $config['password'], $config['database']);
    }
}