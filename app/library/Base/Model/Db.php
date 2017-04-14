<?php

namespace Base\Model;

class Db extends Common 
{
    
	protected $db = array();
    
	public function __construct() {
        $this->db = \Factory::db();
    }
    
}