# Laravel Authorized

**This is a Role Based Access Control bundle for Laravel using Zend_Acl by Tee++**

Thanks to https://github.com/Vespakoen/authority-laravel for inspiration.

Authorized is almost the same as authority-laravel by Vespakoen. 
I just change the core to be Zend_Acl, so you need Zend installed.


## Installation

Download Authorized into your Laravel installation's bundles directory.

	git clone -b master git@github.com:teepluss/authorized-laravel.git

Enter your database settings in config/database.php

Add the following line to application/bundles.php
	
	return array(
		'authorized' => array(
			'auto' => true
		)
	);
	
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
	 * Has roles implement to reduce duplicated query
	 * 
	 * @param  string  $key
	 * @return array|string
	 */
	public function has_roles($key = null)
	{
		$ckey = 'has_roles_'.$this->id;
		$cache = Cache::driver('memory');
		
		if ( ! $roles = $cache->get($ckey)) 
		{		
			$roles = $this->roles()->lists('name');
			$cache->forever($ckey, $roles);
		}
		
		if ( ! is_null($key))
		{
			return $roles[$key];
		}
		
		return $roles;
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
	
## To Config special rule 

*bundles/authorized/config/authorized.php*

```php
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
```
	
## Example Usage
	
Check user authenticate permission

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