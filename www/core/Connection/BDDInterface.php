<?php

namespace cms\core\Connection;

interface BDDInterface{

    public function connect();
    
    public function query(string $query, array $parameters = null);

}