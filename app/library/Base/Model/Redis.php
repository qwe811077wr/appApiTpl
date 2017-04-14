<?php

namespace Base\Model;

class Redis extends Common 
{
    
	protected $redis = array();
    
	public function __construct() {
        $this->redis = \Factory::redis();
    }
    
}