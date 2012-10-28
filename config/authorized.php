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
 
return array(
	
	/*
	|--------------------------------------------------------------------------
	| Allow on non-group
	|--------------------------------------------------------------------------
	|
	| Allow anybody to access if group not exists in  ACL 
	| this is mean even Guest can access, if you not define the group.
	|
	*/
    
	'allow_nongroup' => false,
	
	/*
	|--------------------------------------------------------------------------
	| Initialize Access Permissions 
	|--------------------------------------------------------------------------
	|
	| Setup access list control base roles / rules.
	|
	*/
    
	'initialize' => function($user)
	{	
		// Instance access 
		$acl = Authorized::instance();
		
		// Get all roles with rules
		$roles = Role::with('rules')->get();

		foreach ($roles as $role)
		{
			// Add roles to access list
			$acl->add_role($role->name);
			
			foreach ($role->rules as $rule)
			{
				// Add rules to access list, then give permisstion to role
				// $acl->add_rule($rule->group, $rule->action);
				// $acl->allow($role->name, $rule->group, $rule->action);
				
				// This is a short way to do things above
				$acl->allow($role->name, $rule->group, $rule->action, true);
			}
		}
		
		// Set current auth user to access list
		Authorized::as_user($user);
		
		// This is mean you allow "Unauthorized" user to access all the things.
		// $acl->allow('Guest', null, null);
	},
	
	/*
	|--------------------------------------------------------------------------
	| Condition per user who is in application.
	|--------------------------------------------------------------------------
	|
	| Assign user roles. Detailed special conditons for each user.
	|
	| return "true" to give a magic passport to user
	| return "false" to deny all group 
	|
	*/
    
	'as_user' => function($user)
	{
		// Get user roles
		$user_roles = $user->has_roles();
		
		// Set user roles to access list 
		Authorized::set_user_roles($user_roles);
		
		// Hard code some role to allow/deny somewhere for some user
		if ($user->id == 1 and in_array('Father', $user_roles))
		{
			// Force allow group "massage" acion "go" to the role "Father"
			$acl->allow('Father', 'massage', 'go');
			
			// Force deny group "massage" acion "follow" to the role "Mother"
			$acl->deny('Mother', 'massage', 'follow');
		}
		
		// Allow any rule to some user
		if ($user->email == 'mryes@domain.com')
		{
			return true;
		}
		
		// Deny any rule for some user
		if ($user->email == 'myno@domain.com')
		{
			return false;
		}
	
	}

);