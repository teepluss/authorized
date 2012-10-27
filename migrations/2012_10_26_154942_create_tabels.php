<?php

class Authorized_Create_Tabels {

	/**
	 * Make changes to the database.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function($table) 
		{
			$table->increments('id');
			$table->string('email')->unique();
			$table->string('password');
			$table->string('name');
			$table->integer('age');
			$table->timestamps();
		});

		User::create(array(
			'id'       => 1,
			'email'    => 'admin@domain.com',
			'password' => Hash::make('test'),
			'name'     => 'I am Administrator',
			'age'      => 40
		));
		
		User::create(array(
			'id'       => 2,
			'email'    => 'staff@domain.com',
			'password' => Hash::make('test'),
			'name'     => 'I am Staff',
			'age'      => 30
		));

		User::create(array(
			'id'       => 3,
			'email'    => 'member@domain.com',
			'password' => Hash::make('test'),
			'name'     => 'I am Member',
			'age'      => 25
		));
		
		User::create(array(
			'id'       => 4,
			'email'    => 'mutant@domain.com',
			'password' => Hash::make('test'),
			'name'     => 'I am Member and Staff',
			'age'      => 17
		));


		Schema::create('roles', function($table)
		{
			$table->increments('id');
			$table->string('name');
			$table->timestamps();
		});

		Role::create(array(
			'id'   => 1,
			'name' => 'Admin'
		));
		
		Role::create(array(
			'id'   => 2,
			'name' => 'Staff'
		));

		Role::create(array(
			'id'   => 3,
			'name' => 'Member'
		));
		
		Schema::create('rules', function($table)
		{
			$table->increments('id');
			$table->string('group');
			$table->string('action');
			$table->string('description');
			$table->timestamps();
		});
		
		Rule::create(array(
			'id'          => 1,
			'group'       => 'demo',
			'action'      => '*',
			'description' => 'Can access Demo all actions.'
		));
		
		Rule::create(array(
			'id'          => 2,
			'group'       => 'demo',
			'action'      => 'view',
			'description' => 'Can view Demo.'
		));
		
		Rule::create(array(
			'id'          => 3,
			'group'       => 'demo',
			'action'      => 'create',
			'description' => 'Can create Demo.'
		));
		
		Rule::create(array(
			'id'          => 4,
			'group'       => 'demo',
			'action'      => 'edit',
			'description' => 'Can edit Demo.'
		));
		
		Rule::create(array(
			'id'          => 5,
			'group'       => 'demo',
			'action'      => 'revise',
			'description' => 'Can revise Demo.'
		));
		
		Rule::create(array(
			'id'          => 6,
			'group'       => 'demo',
			'action'      => 'publish',
			'description' => 'Can publish Demo.'
		));
		
		Rule::create(array(
			'id'          => 7,
			'group'       => 'demo',
			'action'      => 'delete',
			'description' => 'Can delete Demo.'
		));

		Schema::create('role_rule', function($table)
		{
			$table->increments('id');
			$table->integer('role_id');
			$table->integer('rule_id');
			$table->timestamps();
		});
		
		Role::find(1)->rules()->sync(array(1));
		Role::find(2)->rules()->sync(array(2, 5, 6, 7));
		Role::find(3)->rules()->sync(array(2, 4, 3));
		
		Schema::create('role_user', function($table)
		{
			$table->increments('id');
			$table->integer('user_id');	
			$table->integer('role_id');					
			$table->timestamps();
		});

		User::find(1)
			->roles()
			->attach(1);

		User::find(2)
			->roles()
			->attach(2);
			
		User::find(3)
			->roles()
			->attach(3);
			
		User::find(4)
			->roles()
			->sync(array(2, 3));
	}

	/**
	 * Revert the changes to the database.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('users');
		Schema::drop('roles');
		Schema::drop('rules');
		Schema::drop('role_user');
		Schema::drop('role_rule');
	}

}