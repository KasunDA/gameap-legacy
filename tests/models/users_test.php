<?php

/**
 * @group Model
 */

class Users_test extends CIUnit_TestCase
{
    public function setUp()
    {
		$this->CI->load->database();
		$this->CI->load->model('users');
    }

    public function test_add_user()
    {			
		$sql_data['reg_date'] 	= time();
		$sql_data['login'] 		= 'test';
		$sql_data['password'] 	= hash_password('password');
			
		$this->assertTrue($this->CI->users->add_user($sql_data));
    }
    
    public function test_update_user()
    {
		$sql_data['password'] 	= hash_password('new_password');
		$sql_data['name'] 		= 'username';
		$sql_data['email'] 		= 'nikita.hldm@gmail.com';
			
		$this->assertTrue($this->CI->users->update_user($sql_data, 1));
	}
	
	public function test_get_users_list()
    {
		$this->CI->users->get_users_list();
		//~ var_dump($this->CI->users->users_list);
	}
    
    public function test_get_user_data()
    {			
		$this->CI->users->get_user_data(1);

        $this->assertEquals('test', $this->CI->users->user_data['login']);
        $this->assertEquals('username', $this->CI->users->user_data['name']);
        $this->assertEquals('nikita.hldm@gmail.com', $this->CI->users->user_data['email']);
        $this->assertEquals(hash_password('new_password', $this->CI->users->user_data['password']), $this->CI->users->user_data['password']);
    }
    
    public function test_user_live()
    {	
		$this->assertTrue($this->CI->users->user_live(1));
		$this->assertFalse($this->CI->users->user_live(99990));
		
		$this->assertTrue($this->CI->users->user_live('test', 'login'));
		$this->assertFalse($this->CI->users->user_live('false_user', 'login'));
		
		$this->assertTrue($this->CI->users->user_live('nikita.hldm@gmail.com', 'email'));
		$this->assertFalse($this->CI->users->user_live('1234@gmail.com', 'email'));
	}
}