<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class MY_Controller extends Controller  
{
    public function __construct()  
    {
        parent::Controller();
        
        $this->load->vars(array(
            'meta_keywords' => '', 
            'meta_description' => '',
            'meta_og_image' => '',
            'meta_og_description' => ''
        ));
        
        $this->form_validation->set_error_delimiters('<div class="form_message rounded_medium">', '</div>');
    }
}

/* End of file MY_Controller.php */
/* Location: ./application/libraries/MY_Controller.php */