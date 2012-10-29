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
 
require 'access.php'; 

class Authorized extends Authorized\Access {
	
	/**
	 * Initialize config rules.
	 *
	 * @param  object  Laravel\Eloquent User
	 * @return void
	 */
	public static function initialize($user) 
	{
		static::reset();
		
		// Get default initialize profile.
		$profile = Config::get('authorized::authorized.default');
		
		// Get initailize callback.
		$initialize = Config::get('authorized::authorized.profiles.'.$profile);
		
		// Callback to set up all roles / rules, pass an auth user to config.
		call_user_func($initialize, $user);
	}
	
	/**
	 * Change to user who need to check. 
	 *
	 * @param  object  Laravel\Eloquent User
	 * @return void
	 */
	public static function as_user($user)
	{
		if (is_null($user))
		{
			return false;
		}
		
		// Get user config.
		$as_user = Config::get('authorized::authorized.as_user');
		
		// Pass a user to config user's roles
		$returned = call_user_func($as_user, $user);		
		
		// If returned true mean current rule force to allowed.
		if ($returned === true) 
		{	
			static::bypass(true);
		}
		
		// If returned false mean current rule force to denined.
		if ($returned === false)
		{
			// Clear all roles for current user
			static::reset();
		}
	}
	
}