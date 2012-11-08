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
 
/**
 * Check zend acl component.
 */
if ( ! class_exists('Zend_Acl')) 
{
	throw new Exception('This bundle required Zend installed.');
}

/**
 * Autoload Authorized.
 */
Autoloader::map(array(
    'Authorized' => __DIR__.DS.'authorized'.EXT
));

/**
 * Start using Authorized with authenticated user.
 */
Authorized::initialize(Auth::user());

/**
 * Auto route example to url /acl_examples.
 */
Route::any('acl_examples/(:any?)', array(
	'as'       => 'acl_examples',
	'uses'     => 'authorized::examples@(:1)',
	'defaults' => 'index'
));