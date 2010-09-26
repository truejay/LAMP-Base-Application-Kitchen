<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends MY_Controller
{
	function __construct()
	{
		parent::__construct();
	}

	function index()
	{
        if ($this->tank_auth->is_logged_in())
        {
            $r_user = Doctrine_Core::getTable('Users')->get_user_by_id($this->tank_auth->get_user_id(), 1);
            $data['user'] = $r_user;
        }     
                
        $data['title'] = $this->config->item('website_name');
        $data['meta_description'] = '';
        $data['content'] = 'welcome';
        $this->load->view('layouts/default', $data);
        
        /*
        // GET example
        parse_str($_SERVER['QUERY_STRING'], $_GET); //converts query string into global GET array variable
        print_r($_GET); //test the $_GET variables
        */
        
        /*
        // Doctrine examples
        // The save() method will happen on the master connection because it is a write
        $user = new User();
        $user->username = 'jwage';
        $user->password = 'changeme';
        $user->save();

        // This query goes to one of the slaves because it is a read
        $q = new Doctrine_MyQuery();
        $q->from('User u');
        $users = $q->execute();

        print_r($users->toArray(true));

        // This query goes to the master connection because it is a write
        $q = new Doctrine_MyQuery();
        $q->delete('User')
          ->from('User u')
          ->execute();
        */
        
        /*
        // Tank auth examples
		if (!$this->tank_auth->is_logged_in()) {
			redirect('/auth/login/');
		} else {
			$data['user_id'] = $this->tank_auth->get_user_id();
			$data['username'] = $this->tank_auth->get_username();
			$this->load->view('welcome', $data);
		}
        */
        
        /*
        // Email example
        $this->load->library('email');
        $to = 'email@email.com';
        $subject = 'Hello';
        $email_msg = 'Goodbye';
        $this->email->send_email($to, $subject, $email_msg);
        */
        
        /*
        // Zend example
        $this->ci->load->library('zend', array('class' => 'Zend/Http/Client'));
        $client = new Zend_Http_Client();
        */
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */