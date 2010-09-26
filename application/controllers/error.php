<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Error extends MY_Controller 
{
    public function __construct()
    {
        parent::__construct();
    }
    
    function error_404()
    {
        $this->output->set_status_header('404');
        
        $data['title'] = $this->config->item('website_name') . ' | 404 Page Not Found!';
        $data['content'] = 'error/error_404';
        $this->load->view('layouts/default', $data);
    }
}