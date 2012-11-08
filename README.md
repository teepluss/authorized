# Laravel Authorized

**This is a Role Based Access Control bundle for Laravel using Zend_Acl by Tee++**

Thanks to https://github.com/Vespakoen/authority-laravel for inspiration.

Authorized is almost the same as authority-laravel by Vespakoen. 
I just change the core to be Zend_Acl.


## Installation

Authorized required Zend installed you can easy use Zend Framework Laravel bundle by Isimkins.

https://github.com/lsimkins/laravel-zend-bundle

Install this bundle by running the following CLI command:

	php artisan bundle:install authorized

Enter your database settings in config/database.php

Add the following line to application/bundles.php
	
	'authorized' => array('auto' => true),
	
Add user model

*application/models/user.php*

```php
class User extends Eloquent {

	/**
	 * User has many and belongs to roles.
	 * 
	 * @return Role
	 */
	public function roles()
	{
		return $this->has_many_and_belongs_to('Role');
	}
	
	/**
	 * Get only name of user roles with memory cache.
	 *
	 * @return array()
	 */
	public function get_roles_list()
	{
		$ckey = 'has_roles_'.$this->id;
		$cache = Cache::driver('memory');
		
		if ( ! $roles = $cache->get($ckey)) 
		{		
			$roles = $this->roles()->lists('name');
			$cache->forever($ckey, $roles);
		}
		
		return $roles;
	}
	
	/**
	 * Check has exact role?
	 * 
	 * @param  string  $key
	 * @return bool
	 */
	public function has_role($key = null)
	{
		if ( ! is_null($key))
		{
			return in_array($key, $this->roles_list);
		}
		
		return false;
	}
	
}
```

Add role model

*application/models/role.php*

```php
class Role extends Eloquent {

	/**
	 * Role has many and blongs to rules.
	 * 
	 * @return  Rule
	 */
	public function rules()
	{
		return $this->has_many_and_belongs_to('Rule');
	}
	
	/**
	 * Role has many and blongs to user.
	 * 
	 * @return  User
	 */
	public function users()
	{
		return $this->has_many_and_belongs_to('User');
	}
	
}
```

Add rule model 

*application/models/rule.php*

```php
class Rule extends Eloquent {

	/**
	 * Rule has many and blongs to roles
	 * 
	 * @return Role
	 */
	public function roles()
	{
		return $this->has_many_and_belongs_to('Role');
	}
	
}
```

Install migrations using Artisan CLI:

	php artisan migrate:install
	
Installing the tables for authorized is as simple as running its migration.

	php artisan migrate authorized
	
## Configuration

*bundles/authorized/config/authorized.php*

Code example in configuration. 

```php
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
	| Default initialize profile.
	|--------------------------------------------------------------------------
	|
	| You can have many profile to set up role base.
	|
	*/
	
	'default' => 'database',
	
	/*
	|--------------------------------------------------------------------------
	| Initialize access permissions profiles.
	|--------------------------------------------------------------------------
	|
	| Setup access list control base roles / rules.
	|
	*/
	
	'profiles' => array(
	
		'manual' => function($user)
		{
			// Instance access 
			$acl = Authorized::instance();
			
			// Add Member to roles list.
			$acl->add_role('Member');
			$acl->add_role('Contributor');
			
			// This is mean Author inherit from "Member" and "Contributor"
			// Author can do anything the same as "Member" and "Contributor" can.
			$acl->add_role('Author', array('Member', 'Contributor'));
			
			// Add Staff inherit from "Author"
			// Staff can do anything the same as "Member", "Contrubutor" and Author can.
			$acl->add_role('Staff', 'Author');
			
			// Add Editor to roles list.
			$acl->add_role('Editor');
			
			// Add Admin to roles list.
			$acl->add_role('Admin');
			
			// Add a resource Blog.
			$acl->add_rule('Blogs');
			
			// Add a resource Photos.
			$acl->add_rule('Photos');
			
			// Allow "Member" access "Blogs" in action "read".
			$acl->allow('Member', 'Blogs', 'read');
			
			// Allow "Contributor" access "Blogs" in actions "read" and "write".
			$acl->allow('Contributor', 'Blogs', 'read');
			$acl->allow('Contributor', 'Blogs', 'write');
			
			// Allow "Author" access "Blogs" in actions "delete" and "publish".
			// Allow "Author" access "Photos" in action "upload",
			// Author inherit permmsion from "Contributor", so The Author also access "Blogs" in actions read and write.
	 		$acl->allow('Author', 'Blogs', 'delete');
			$acl->allow('Author', 'Blogs', 'publish');
			$acl->allow('Author', 'Photos', 'upload');
			
			// But sometimes we need to force deny for some resource inherited from parents.
			$acl->deny('Author', 'Blogs', 'write');
			
			// Allow "Editor" do any actions in "Blogs" and "Photos" except "delete".
			$acl->allow('Editor', 'Blogs', '*');
			$acl->allow('Editor', 'Photos', '*');
			$acl->deny('Editor', 'Blogs', 'delete');
			$acl->deny('Editor', 'Photos', 'delete');
			
			// Admin can access anything
			$acl->allow('Admin', '*', '*');
			
			// Set current auth user to access list
			Authorized::as_user($user);
		},
		
		'database' => function($user)
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
		}
	
	),
	
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
		$user_roles = $user->roles_list;
		
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
```
	
## Example Usage 
	
Check user authenticated permission

	Authorized::can('Blog', 'Add');
	Authorized::can('Blog', 'Edit');
	Authorized::can('Blog', 'Delete');
	
Check specific user permission

	$user = User::find($id);
	Authorized::can('Blog', 'Add', $user);
	
Get access roles list

	Authorized::roles();
	
Get access rules list

	Authorized::rules();
	
## Examples

You can run example at:
	
	http://domain.com/acl_examples

Move example.php in you controller and run it!
	
## Support or Contact

If you have some problem, Contact teepluss@gmail.com 
	
## Useful links:
- Zend_Acl documentation:      http://framework.zend.com/manual/1.12/en/zend.acl.html
