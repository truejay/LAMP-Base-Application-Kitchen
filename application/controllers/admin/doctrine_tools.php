<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Doctrine_tools extends MY_Controller 
{
    public function __construct()
    {
        parent::__construct();
        
        if ( ! $this->tank_auth->is_admin())
            exit;
    }
    
    public function profiler()
    {
        $content = file_get_contents(BASEPATH . '/logs/doctrine_profiler.php');    
        echo '<pre>' . $content . '</pre>';
    }
    
    /*
    function generate_models_from_db() 
    {
        Doctrine::generateModelsFromDb(APPPATH.'models', array(), array('generateTableClasses' => true));
    }
    */
}

/* End of file tools.php */
/* Location: ./application/controllers/admin/doctrine_tools.php */
