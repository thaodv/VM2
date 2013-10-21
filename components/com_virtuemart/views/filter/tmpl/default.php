<?php

// no direct access
defined('_JEXEC') or die;

foreach($this->products as $p){
	print_r($p->product_name);
}
