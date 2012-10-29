<?php namespace Authorized;

/**
 * Authorized for Laravel
 * 
 * @package     Bundles
 * @subpackage  Zend_Acl
 * @author      Teepluss <teepluss@gmail.com>
 * 
 * @see  http://framework.zend.com/manual/1.12/en/zend.acl.html
 */

use Config;
use Zend_Exception;
use Zend_Acl;
use Zend_Acl_Role;
use Zend_Acl_Resource;

abstract class Access {

	/**
	 * The default role to given unauthenticated user.
	 *
	 * @var string
	 */
	public static $default_role = 'Guest';
	
	/**
	 * Static variable to instance of Zend_Acl.
	 *
	 * @var Zend_Acl
	 */
	public static $acl;
	
	/**
	 * All roles in access list.
	 *
	 * @var array
	 */
	public static $roles = array();
	
	/**
	 * All rules in access list.
	 *
	 * @var array
	 */
	public static $rules = array();
	
	/**
	 * Current user roles.
	 *
	 * @var array
	 */
	public static $user_roles = array();
	
	/**
	 * Bypass checking for any rules.
	 *
	 * @var bool
	 */
	public static $bypass = false;

	/**
	 * Create a new Zend_Acl instance.
	 *
	 * @return Access
	 */
	public static function instance()
	{
		// Store Zend_Acl to static variable
		if ( ! static::$acl instanceof Zend_Acl)
		{
			static::$acl = new Zend_Acl;
			
			// Auto add default role "Guest"
			static::$roles[] = static::$default_role;
			static::$acl->addRole(static::$default_role);
		} 
		
		return new static();
	}
	
	/**
	 * Just a abstract method.
	 *
	 * @see Authorized\as_user
	 */
	public static function as_user($user)
	{
		return true;
	}
	
	/**
	 * Set bypass to allow current rule.
	 * 
	 * @param  bool  $val
	 * @return void
	 */
	public static function bypass($val)
	{
		static::$bypass = $val;
	}
	
	/**
	 * Set user roles.
	 * 
	 * @param  array  $user_roles
	 * @return void
	 */
	public static function set_user_roles(array $user_roles)
	{
		static::$user_roles = $user_roles;
	}
	
	/**
	 * Add role to access list.
	 * 
	 * @param  string  $role
	 * @param  array   $inherit ( inherit permission from parent roles )
	 * @return void
	 */
	public function add_role($role, $inherit = array())
	{
		if ( ! static::$acl->hasRole($role))
		{
			static::$roles[] = $role;
			static::$acl->addRole(new Zend_Acl_Role($role), $inherit);
		}
	}
	
	/**
	 * Add rule group to access list.
	 * 
	 * @param  string  $rule
	 * @return void
	 */
	public function add_rule($rule)
	{
		if ( ! static::$acl->has($rule))
		{
			static::$rules[$rule] = array();
			static::$acl->addResource(new Zend_Acl_Resource($rule));
		}
	}
	
	/**
	 * Set allow rule to the role with action. 
	 *
	 * $action can be *, if you want to allow all actions.
	 * $force_rule can be true, if you want to auth add unexists rule group.
	 * 
	 * @param  string  $role
	 * @param  string  $group
	 * @param  string  $action
	 * @param  bool    $force_rule
	 * @return void
	 */
	public function allow($role, $group = null, $action = null, $force_rule = true)
	{
		$this->permission('allow', $role, $group, $action, $force_rule);
	}
	
	/**
	 * Set deny rule to the role with action. 
	 *
	 * $action can be *, if you want to deny all actions.
	 * $force_rule can be true, if you want to auth add unexists rule group.
	 * 
	 * @param  string  $role
	 * @param  string  $group
	 * @param  string  $action
	 * @param  bool    $force_rule
	 * @return void
	 */
	public function deny($role, $group, $action = null, $force_rule = true)
	{		
		$this->permission('deny', $role, $group, $action, $force_rule);
	}
	
	/**
	 * Parent method to work for aliases (add_allow / add_deny)
	 * 
	 * @param  string  $role
	 * @param  string  $group
	 * @param  string  $action
	 * @param  bool    $force_rule
	 * @return void
	 */
	private function permission($type, $role, $group, $action, $force_rule)
	{
		if ( ! in_array($type, array('allow', 'deny')))
		{
			throw new Zend_Exception('Permission type is invalid.');
		}
		
		// Auto add rule, if not exists.
		if ($force_rule === true)
		{
			$this->add_rule($group);
		}
		
		// Add all actions to group.
		if (isset(static::$rules[$group]))
		{
			static::$rules[$group][] = $action;
		}
		
		// If grouo is "*" allow any groups by given NULL.
		if ($group == '*')
		{
			$group = null;
		}
		
		// If action is "*" allow any actions by given NULL.
		if ($action == '*')
		{
			$action = null;
		}
		
		// For more detail see the official manual of Zend_Acl allow|deny
		call_user_func(array(static::$acl, $type), $role, $group, $action);
	}
	
	/**
	 * Get all roles in access list.
	 * 
	 * @return array
	 */
	public static function roles()
	{
		return static::$roles;
	}
	
	/**
	 * Get all rules in access list.
	 * 
	 * @return array
	 */
	public static function rules()
	{
		return static::$rules;
	}
	
	/**
	 * Checking a permision for specific role(s).
	 *
	 * This method is permision checker
	 * you need to use "can" or "cannot" to check instead of this.
	 * 
	 * @param  string|array  $roles 
	 * @param  string  $group
	 * @param  string  $action
	 * @return bool
	 */
	private static function is_allowed($roles, $group, $action)
	{		
		// If the roles is string typecast to be array.
		if ( ! is_array($roles))
		{
			$roles = array($roles);
		}
		
		// If the specific $group not found in access list.
		if ( ! static::$acl->has($group))
		{	
			// Get a config to check the default allow of deny on rule group doesn't exists.
			$allow_nongroup = Config::get('authorized::authorized.allow_nongroup');
			return ($allow_nongroup === true) ? true : false;
		}
	
		foreach ($roles as $role)
		{
			// If the role specific $role not found in access list, throw an error.
			if ( ! static::$acl->hasRole($role))
			{
				throw new Zend_Exception('The role "'.$role.'" does\'t not exists.');
			}
			
			// If allowed retured, stop the loop and return true.
			if (static::$acl->isAllowed($role, $group, $action))
			{
				return true;
			}
		}
		
		return false;
	}

	/**
	 * Check ability for user
	 *
	 * Normally this method will ckeck authenticated user,
	 * but if you need to change user pass an Eloquent User to $another.
	 * 
	 * @param  string  $role
	 * @param  string  $action
	 * @param  object  $another This is Laravel\Eloquent User
	 * @return bool
	 */
	public static function can($group, $action, $another = null)
	{
		// Change to be another user not authenticated user.
		if (is_object($another))
		{
			static::as_user($another);
		}
		
		// If bypass set to true always allow
		// see \Authorized as_user to check how it works.
		if (static::$bypass === true)
		{
			return true;
		}
		
		// If cannot found any roles for the user set to be a default 
		// normally is "Guest".
		if (empty(static::$user_roles))
		{
			static::$user_roles = array(static::$default_role);
		}
		
		return static::is_allowed(static::$user_roles, $group, $action);
	}
	
	/**
	 * This method "cannot" is an opposite of "can".
	 *
	 * @return bool
	 */
	public static function cannot($group, $action, $another = null)
	{
		return ! static::can($group, $action, $another);
	}
	
	/**
	 * Reset current user roles, this is mean switch to be a "Guest".
	 *
	 * @return void
	 */
	public static function reset()
	{
		static::$user_roles = array();
		static::$roles = array();
		static::$rules = array();
	}

}