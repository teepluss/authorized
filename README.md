# Laravel Authorized

**This is a Role Based Access Control bundle for Laravel using Zend_Acl by Tee++**

Thanks to https://github.com/Vespakoen/authority-laravel for inspiration.

Authorized is almost the same as authority-laravel by Vespakoen. 
I just change the core to be Zend_Acl, so you need Zend installed.


## Usage Example

Enter your database settings in config/database.php

Add the following line to application/bundles.php
	
	return array(
		'authorized' => array('auto' => true)
	)

	php artisan migrate authorized

```php
public function action_session($provider)
{
	Bundle::start('laravel-oauth2');
	
	$provider = OAuth2::provider($provider, array(
		'id' => 'your-client-id',
		'secret' => 'your-client-secret',
	));

	if ( ! isset($_GET['code']))
	{
		// By sending no options it'll come back here
		return $provider->authorize();
	}
	else
	{
		// Howzit?
		try
		{
			$params = $provider->access($_GET['code']);
			
        		$token = new OAuth2_Token_Access(array('access_token' => $params->access_token));
        		$user = $provider->get_user_info($token);

			// Here you should use this information to A) look for a user B) help a new user sign up with existing data.
			// If you store it all in a cookie and redirect to a registration page this is crazy-simple.
			echo "<pre>";
			var_dump($user);
		}
		
		catch (OAuth2_Exception $e)
		{
			show_error('That didnt work: '.$e);
		}
		
	}
}
```