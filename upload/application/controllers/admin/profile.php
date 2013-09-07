<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Game AdminPanel (АдминПанель)
 *
 * 
 *
 * @package		Game AdminPanel
 * @author		Nikita Kuznetsov (ET-NiK)
 * @copyright	Copyright (c) 2013, Nikita Kuznetsov (http://hldm.org)
 * @license		http://gameap.ru/license.html
 * @link		http://gameap.ru
 * @filesource
*/
class Profile extends CI_Controller {
	
	var $tpl_data = array();
	var $user_servers_count = 0;
	
	public function __construct()
    {
        parent::__construct();
	
		$this->load->database();
        $this->load->model('users');
		$this->lang->load('profile');
        $this->lang->load('main');
        
        if($this->users->check_user()){
			//Base Template
			$this->tpl_data = $this->users->tpl_userdata();
			$this->tpl_data['title'] 	= lang('profile_title_index');
			$this->tpl_data['heading'] 	= lang('profile_header_index');
			$this->tpl_data['content'] = '';
			$this->tpl_data['menu'] = $this->parser->parse('menu.html', $this->tpl_data, TRUE);
			$this->tpl_data['profile'] = $this->parser->parse('profile.html', $this->users->tpl_userdata(), TRUE);
        
        }else{
			redirect('auth');
        }
    }
    
    // Отображение информационного сообщения
    function _show_message($message = FALSE, $link = FALSE, $link_text = FALSE)
    {
        
        if (!$message) {
			$message = lang('error');
		}
		
        if (!$link) {
			$link = 'javascript:history.back()';
		}
		
		if (!$link_text) {
			$link_text = lang('back');
		}

        $local_tpl_data['message'] = $message;
        $local_tpl_data['link'] = $link;
        $local_tpl_data['back_link_txt'] = $link_text;
        $this->tpl_data['content'] = $this->parser->parse('info.html', $local_tpl_data, TRUE);
        $this->parser->parse('main.html', $this->tpl_data);
    }
	
	public function index()
    {
		// Загрузка модели работы с серверами
		$this->load->model('servers');
		
		// Получение списка серверов юзера
		$this->servers->get_server_list($this->users->auth_id);
		$local_tpl_data['servers_list'] = $this->servers->tpl_data();
		
		if($local_tpl_data['servers_list']){
			$this->user_servers_count = 1;
		}
		
		$this->tpl_data['content'] .= $this->parser->parse('profile/profile_main.html', array_merge($this->tpl_data, $local_tpl_data), TRUE);

        $this->parser->parse('main.html', $this->tpl_data);
    }
    
    /* Редактирование профиля */
    public function edit()
    {
		if($this->users->auth_id){
			
			if(!$this->input->post('profile_edit_submit')){
				$this->tpl_data['content'] .= $this->parser->parse('profile/profile_edit.html', $this->tpl_data, TRUE);
			}else{
				$this->load->library('form_validation');
				$this->load->model('password');
				
				$this->form_validation->set_rules('name', 'Имя', 'trim|xss_clean');
				$this->form_validation->set_rules('email', 'E-Mail', 'trim|required|valid_email');
				
				if (!$this->form_validation->run()){
					$this->tpl_data['content'] .= lang('profile_form_unavailable');
				}else{
					$user_new_data['name'] = $this->input->post('name', TRUE);
					$user_new_data['email'] = $this->input->post('email', TRUE);
					
					$this->users->update_user($user_new_data, $this->users->auth_data['id']);

					$local_tpl_data = array();
					$local_tpl_data['message'] = lang('profile_data_changed');
					$local_tpl_data['link'] = site_url() . 'admin/profile';
					$local_tpl_data['back_link_txt'] = lang('profile');
					$this->tpl_data['content'] = $this->parser->parse('info.html', $local_tpl_data, TRUE);
				}
				
			}
			
        }

        $this->parser->parse('main.html', $this->tpl_data);
	}
	
	/* Смена пароля */
	public function change_password()
    {
		if($this->users->auth_id){
			
			if(!$this->input->post('profile_edit_submit')){
				$this->tpl_data['content'] .= $this->parser->parse('profile/profile_change_password.html', $this->tpl_data, TRUE);
			}else{
				$this->load->library('form_validation');
				$this->load->model('password');
				
				$this->form_validation->set_rules('old_password', 'Текущий пароль', 'trim|required|md5');
				$this->form_validation->set_rules('new_password', 'Пароль', 'trim|required|matches[new_password_confirm]|md5');
				$this->form_validation->set_rules('new_password_confirm', 'Подтверждение пароля', 'trim|required|md5');
				
				if (!$this->form_validation->run()){
							$this->tpl_data['content'] .= lang('profile_form_unavailable');
				}else{
					
					$password_encrypt = $this->input->post('old_password', TRUE);
					$password_encrypt = $this->password->encryption($password_encrypt, $this->users->auth_data);

					$query = $this->db->get_where('users', array('login' => $this->users->auth_data['login'], 'password' => $password_encrypt));
					
					if($query->num_rows == 1){
							$new_password = $this->input->post('new_password', TRUE);
							$new_password = $this->password->encryption($new_password, $this->users->auth_data);
							
							$this->users->update_user(array('password' => $new_password), $this->users->auth_data['id']);
							
							$local_tpl_data = array();
							$local_tpl_data['message'] = lang('profile_password_changed');
							$local_tpl_data['link'] = site_url();
							$local_tpl_data['back_link_txt'] = lang('profile');
							$this->tpl_data['content'] = $this->parser->parse('info.html', $local_tpl_data, TRUE);
							
					}else{
						$this->tpl_data['content'] .= lang('profile_password_unavailable');
					}
					
					//Смена пароля закончена
				}
				
			}
			
        }

        $this->parser->parse('main.html', $this->tpl_data);
	}
	
	
	public function server_privileges($server_id = FALSE)
    {
		$this->load->model('servers');
		
		$server_id = (int)$server_id;
		
		$this->tpl_data['heading'] = lang('profile_server_privileges');
			
		if(!$server_id) {
			$this->_show_message(lang('profile_empty_server_id'), site_url('admin/profile'));
			return FALSE;
		}
		
		$this->servers->get_server_data($server_id, TRUE, TRUE, TRUE);
		
		if(!$this->servers->server_data) {
			$this->_show_message(lang('profile_server_not_found'), site_url('admin/profile'));
			return FALSE;
		}

		$user_privileges = $this->users->get_server_privileges($server_id, FALSE);
		
		if(!$this->users->auth_servers_privileges['VIEW']) {
			$this->_show_message(lang('profile_server_not_found'), site_url('admin/profile'));
			return FALSE;
		}

		$num = -1;
		foreach ($user_privileges as $privilege_name => $privilege_value)
		{
			$num++;
			if($privilege_value == 1){
				$local_tpl_data['privilege_list'][$num]['privilege_value'] = '<img src="' . site_url('themes/' . $this->config->config['template'] . '/' . $this->config->config['style']) . '/images/yes.png">';
			}else{
				$local_tpl_data['privilege_list'][$num]['privilege_value'] = '<img src="' . site_url('themes/' . $this->config->config['template'] . '/' . $this->config->config['style']) . 'images/no.png">';
			}
			
			$local_tpl_data['privilege_list'][$num]['human_name'] = $this->users->all_privileges[$privilege_name];
		}
		
		$this->tpl_data['content'] .= $this->parser->parse('profile/server_privileges.html', $local_tpl_data, TRUE);
		
        $this->parser->parse('main.html', $this->tpl_data);
    }
}

/* End of file profile.php */
/* Location: ./application/controllers/admin/profile.php */
