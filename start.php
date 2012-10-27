<?php

/**
 * Authorized for Laravel
 * 
 * @package     Bundles
 * @subpackage  Zend_Acl
 * @author      Teepluss <teepluss@gmail.com>
 * 
 * @see  http://framework.zend.com/manual/1.12/en/zend.acl.html
 */
 
if ( ! class_exists('Zend_Acl')) 
{
	throw new Exception('This bundle required Zend installed.');
}

Autoloader::map(array(
    'Authorized' => __DIR__.DS.'authorized'.EXT
));

Authorized::initialize(Auth::user());