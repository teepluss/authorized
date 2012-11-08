<?php

class Authorized_Examples_Controller extends Controller {

	public $restful = true;
	
	public function get_index()
	{
		$all_rules = Authorized::rules();
		
		/*
		Auth::login(1);
		$user = Auth::user();
		echo Authorized::can('demo', 'delete') ? 'I can' : 'I cannot';
		*/
		
		for ($i = 1; $i <= 4; $i++)
		{
			$user = User::find($i);
			
			echo '<h2>'.$user->name.' ('. implode(', ', $user->roles_list) .')</h2>';
			foreach ($all_rules as $group => $actions)
			{
				echo '<ul>';
				foreach ($actions as $action)
				{
					$ability = Authorized::can($group, $action, $user) 
								? '<span style="color:green;">can</span>' 
								: '<span style="color:red;">cannot</span>';
				
					echo '<li>I '. $ability .' access <strong>'. $group .'</strong> to <strong>'. $action .'</strong></li>';
				}	
				echo '</ul>';
			}
		}
	}
	
}