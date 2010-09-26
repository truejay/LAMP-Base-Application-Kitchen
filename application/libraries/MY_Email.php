<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class MY_Email extends CI_Email
{
    public function send_email($to, $subject, $message)
    {
        $ci =& get_instance();
        
        $config['protocol'] = $ci->config->item('email_protocol');
        $config['smtp_host'] = $ci->config->item('email_smtp_host');
        $config['smtp_port'] = $ci->config->item('email_port');
        $config['smtp_timeout'] = $ci->config->item('email_timeout');
        $config['smtp_user'] = $ci->config->item('email_user');
        $config['smtp_pass'] = $ci->config->item('email_pass');
        $config['newline'] = $ci->config->item('email_newline');
        $config['mailtype'] = $ci->config->item('email_mailtype');
        $config['validation'] = $ci->config->item('email_validation');
        
        $this->initialize($config);
        $this->from($ci->config->item('email_user'), $ci->config->item('website_name'));
        $this->to($to); 
        $this->subject($subject);
        $this->message($message);  

        $this->send();
    }
}
