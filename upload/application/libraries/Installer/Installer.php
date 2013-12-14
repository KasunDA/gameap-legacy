<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Game AdminPanel (АдминПанель)
 *
 * 
 *
 * @package		Game AdminPanel
 * @author		Nikita Kuznetsov (ET-NiK)
 * @copyright	Copyright (c) 2013, Nikita Kuznetsov (http://hldm.org)
 * @license		http://www.gameap.ru/license.html
 * @link		http://www.gameap.ru
 * @filesource
*/

// ------------------------------------------------------------------------

/**
 * Installer библиотека
 *
 * Позволяет конфигурировать вновь созданные игровые серверы,
 * правит игровые файлы, дает права на необходимые файлы.
 * Содержит небольшую базу данных игровых параметров.
 *
 * @package		Game AdminPanel
 * @category	Driver Libraries
 * @author		Nikita Kuznetsov (ET-NiK)
 * @sinse		0.8.3
*/

class Installer extends CI_Driver_Library {
	
	private $_CI;
	
	private $_os	 			= 'linux';
	private $_game_code 		= '';
	private $_engine 			= '';
	private $_engine_version 	= 1;
	
	private $_parameters_value	= array();
	
	public $server_data 		= array();
	
	// ------------------------------------------------------------------------
	
	function __construct()
    {
		$this->_CI =& get_instance();
        
        $this->_CI->config->load('drivers');
        $this->_CI->load->helper('patterns_helper');
        $this->valid_drivers = array('installer_goldsource', 'installer_source', 'installer_minecraft', 'installer_samp');
    }
    
    // ------------------------------------------------------------------------
	
	/**
	 * Задает значение игры и движка
	*/
    public function set_game_variables($game_code, $engine, $engine_version = 1)
    {
		$this->_game_code 			= $game_code;
		$this->_engine 				= $engine;
		$this->_engine_version 		= $engine_version;
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Задает значение игры и движка
	*/
	public function set_parameters($parameters)
	{
		$this->_parameters = $parameters;
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Задает значение операционной системы
	*/
	public function set_os($os = 'linux') 
	{
		$this->_os = $os;
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Получение параметра запуска игры
	*/
	public function get_start_command()
	{
		$engine = $this->_engine;
		return $this->$engine->get_start_command($this->_game_code, $this->_os);
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Получение настроек по умолчанию
	*/
	public function get_default_parameters()
	{
		$engine = $this->_engine;
		return $this->$engine->get_default_parameters($this->_game_code, $this->_os);
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Устанавливает нужные значения в конфигурации
	*/
	public function change_config()
	{
		$engine = $this->_engine;
		return $this->$engine->change_config();
	}
}