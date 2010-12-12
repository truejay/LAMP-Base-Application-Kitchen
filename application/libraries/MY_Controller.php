<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class MY_Controller extends Controller  
{
    public function __construct()  
    {
        parent::Controller();
        
        // init cache
        $this->load->library('zend');
        $this->zend->load('Zend/Cache');
        $frontend_options = array(
           'lifetime' => 3600,
           'automatic_serialization' => TRUE
        );
        $backend_options = array(
            'cache_dir' => BASEPATH . 'cache'
        );
        $this->cache = Zend_Cache::factory(
            'Output',
            'File',
            $frontend_options,
            $backend_options
        );
        
        $this->load->vars(array(
            'meta_keywords' => '', 
            'meta_description' => '',
            'meta_og_image' => '',
            'meta_og_description' => ''
        ));
            
        if ($this->tank_auth->is_logged_in())
            $this->user = Doctrine_Core::getTable('Users')->get_user_by_id($this->tank_auth->get_user_id(), 1);
            
        // get FB session
        $this->fb_session = $this->get_facebook_session();
        
        // set error delimiters
        $this->form_validation->set_error_delimiters('<div class="form_message rounded_medium">', '</div>');  
    }
    
    public function get_facebook_session()
    {
        $this->load->library('facebook_graph', array(
            'appId'  => $this->config->item('facebook_app_id'),
            'secret' => $this->config->item('facebook_secret_key'),
            'cookie' => TRUE,
        ));
        
        $session = $this->facebook_graph->getSession();
            
        return $session;
    }
}

/* End of file MY_Controller.php */
/* Location: ./application/libraries/MY_Controller.php */