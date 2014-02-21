<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Game AdminPanel (АдминПанель)
 *
 * 
 *
 * @package		Game AdminPanel
 * @author		Nikita Kuznetsov (ET-NiK)
 * @copyright	Copyright (c) 2014, Nikita Kuznetsov (http://hldm.org)
 * @license		http://www.gameap.ru/license.html
 * @link		http://www.gameap.ru
 * @filesource
*/

/**
 * Работа с SSH
 *
 * Библиотека для работы с удаленными серверами
 * через SSH
 *
 * @package		Game AdminPanel
 * @category	Drivers
 * @author		Nikita Kuznetsov (ET-NiK)
*/
 
class Control_ssh extends CI_Driver {
	
	var $ip				= false;
	var $port 			= 22;
	
	var $_connection 	= false;
	var $errors 		= '';
	
	private $_auth		= false;
	
	// ---------------------------------------------------------------------
	
	public function check()
	{
		if(!in_array('ssh2', get_loaded_extensions())){
			throw new Exception(lang('server_command_ssh_not_module'));
		}
	}
	
	// ---------------------------------------------------------------------
	
	/**
	 * Проверяет необходимые права на файл
	 * 
	 * @param str	файл
	 * @param str 	строка с правами (rwx)
	 */
	public function check_file($file, $privileges = '')
	{
		if ($this->os == 'windows') {
			// Не проверяется
			return true;
		}
		
		$file_perm['exists'] 		= false;
		$file_perm['readable'] 		= false;
		$file_perm['writable'] 		= false;
		$file_perm['executable'] 	= false;
		
		$file_name = basename($file);
		$result = $this->command('ls ' . dirname($file) . ' --color=none -l | grep ^- | grep ' . $file_name);
		
		$result = explode("\n", $result);
		
		foreach($result as &$values) {
			if ($values == '') {
				continue;
			}
			
			$values_exp = explode(" ", $values);
			// Удаление пустых значений
			$values_exp = array_values(array_filter($values_exp,function($el){ return ($el !== '');}));
			
			if ($values_exp[8] != $file_name) {
				continue;
			}
			
			/* С побитовыми операциями не дружу, поэтому способ извращенский =) */
			$file_perm['exists'] 		= true;
			$file_perm['readable'] 		= preg_match('/^\-r..r..r..$/i', $values_exp[0]);
			$file_perm['writable'] 		= preg_match('/^\-.w..w..w.$/i', $values_exp[0]);
			$file_perm['executable'] 	= preg_match('/^\-..x..x..x$/i', $values_exp[0]);
			break;
		}
		
		if (!$file_perm['exists']) {
			throw new Exception(lang('server_command_file_not_found'), $file_name);
		}
		
		if (strpos($privileges, 'r') !== false && !$file_perm['readable']) {
			throw new Exception(lang('server_command_file_not_readable', $file_name));
		}
		
		if (strpos($privileges, 'w') !== false && !$file_perm['writable']) {
			throw new Exception(lang('server_command_file_not_writable', $file_name));
		}
		
		if (strpos($privileges, 'x') !== false && !$file_perm['executable']) {
			throw new Exception(lang('server_command_file_not_executable', $file_name));
		}

		return true;
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Соединение с SSH
	*/
	function connect($ip = false, $port = 22)
	{
		if ($this->_connection && $this->ip == $ip) {
			/* Уже соединен с этим сервером, экономим электроэнергию */
			return;
		} elseif ($this->_connection) {
			// Разрываем соединение со старым сервером
			$this->disconnect();
		}
		
		if (!$ip OR !$port) {
			throw new Exception(lang('server_command_empty_connect_data'));
		}
		
		$this->ip 	= $ip;
		$this->port = $port;
		$this->_auth = false;
		@$this->_connection = ssh2_connect($ip, $port);
		
		if (!$this->_connection) {
			throw new Exception(lang('server_command_connection_failed'));
		}

		return $this->_connection;
	}
	
	// ----------------------------------------------------------------
	
	function auth($login, $password)
	{
		if ($this->auth) {
			return true;
		}
		
		if (!$this->_connection) {
			throw new Exception(lang('server_command_not_connected'));
		}
		
		if(!$login) {
			throw new Exception(lang('server_command_empty_auth_data'));
		}

		if (!@ssh2_auth_password($this->_connection, $login, $password)) {
			throw new Exception(lang('server_command_login_failed'));
		}
		
		$this->_auth = true;
		return true;
		
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Выполнение команды
	*/
	function command($command)
	{
		if (!$this->_connection OR !$this->_auth) {
			throw new Exception(lang('server_command_not_connected'));
		}
		
		if (!$command) {
			throw new Exception(lang('server_command_empty_command'));
		}
		
		$stream = ssh2_exec($this->_connection, $command);

		stream_set_blocking($stream, true);
		$data = stream_get_contents($stream);	
		
		return $data;
	}
	
	// ----------------------------------------------------------------

	/**
	 * Выполнение команды
	*/
	function exec($command) 
	{
		return $this->command($command);
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Отключение
	*/
	function disconnect()
	{
		if ($this->_connection && $this->_auth) {
			$this->command('exit');
		}
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * 
	*/
	function __destruct()
	{
		$this->disconnect();
	}
}


/* End of file Control_ssh.php */
/* Location: ./application/libraries/Control/drivers/Control_ssh.php */
