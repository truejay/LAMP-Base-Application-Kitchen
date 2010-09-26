<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class My_Upload extends CI_Upload
{
    private $_bucket_name;
    private $_tmp_dir;
    private $_s3_dir;
    private $_dest_s3_file_path;
    private $_dest_file_path;
    private $_src_file_path;
    
    public function __construct($props = array())
    {
        parent::CI_Upload($props);
        
        $this->ci =& get_instance();
        
        $this->ci->load->library('s3');
        
        $this->_bucket_name = (isset($props['bucket_name'])) ? $props['bucket_name'] : $this->ci->config->item('aws_bucket_name');
        $this->_tmp_dir = (isset($props['tmp_dir'])) ? $props['tmp_dir'] : $this->ci->config->item('tmp_dir');
        $this->_s3_dir = (isset($props['s3_dir'])) ? $props['s3_dir'] : $this->ci->config->item('aws_s3_dir');
    }
    
    public function set_src_path($file_name)
    {
        $this->_src_file_path = $this->_tmp_dir . $file_name;        
    }
    
    public function set_dest_path($file_name)
    {
        $this->_dest_file_path = $this->_tmp_dir . $file_name;        
    }
    
    public function set_dest_s3_path($file_name)
    {
        $this->_dest_s3_file_path = $this->_s3_dir . $file_name;        
    }
    
    public function resize_crop_upload($width, $height)
    {
        $this->file_name = $width . 'x' . $height . '_' . $this->file_name;
        
        $this->set_dest_path($this->file_name);
        
        $this->convert_crop($width, $height);
        
        return $this->move_to_S3();
    }
    
    public function web_upload($url)
    {
        $this->set_dest_path($this->file_name);
        
        $this->convert_web($url);
    
        return $this->move_to_S3();
    }
    
    public function convert_web($url)
    {
        $convert = 'convert ' . $url . ' ' . $this->_dest_file_path;
        exec($convert);
    }
    
    public function convert_crop($width, $height)
    {
        $convert = 'convert ' . $this->_src_file_path . ' -thumbnail x' . ($height * 2) . ' -resize "' . ($width * 2) . 'x<" -resize 50% -gravity center -crop ' . $width . 'x' . $height . '+0+0 +repage ' . $this->_dest_file_path;
        exec($convert);
    }
    
    public function move_to_S3()
    {  
        $this->set_dest_s3_path($this->file_name);
        $this->set_src_path($this->file_name);
    
        if (@$this->ci->s3->putObjectFile($this->_src_file_path, $this->_bucket_name, $this->_dest_s3_file_path, S3::ACL_PUBLIC_READ))
            return TRUE;
        else
            return FALSE;        
    }
}

/* End of file Upload.php */
/* Location: ./application/libraries/Upload.php */