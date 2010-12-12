<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Auth extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
        
        $this->lang->load('tank_auth');
    }

    function index()
    {
        redirect('auth/login');
    }

    /**
     * Login user on the site
     *
     * @return void
     */
    function login()
    {
        if ($this->tank_auth->is_logged_in()) // logged in
            redirect();
        else if ($this->tank_auth->is_logged_in(FALSE)) // logged in, not activated
            redirect('auth/send_again'); 
        else 
        {
            $this->form_validation->set_rules('login', 'Login', 'trim|required|xss_clean');
            $this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');
            $this->form_validation->set_rules('remember', 'Remember me', 'integer');

            // Get login for counting attempts to login
            if ($this->config->item('login_count_attempts', 'tank_auth') && ($login = $this->input->post('login')))
                $login = $this->input->xss_clean($login);
            else
                $login = '';

            $data['use_recaptcha'] = $this->config->item('use_recaptcha', 'tank_auth');
            if ($this->tank_auth->is_max_login_attempts_exceeded($login)) 
            {
                if ($data['use_recaptcha'])
                    $this->form_validation->set_rules('recaptcha_response_field', 'Confirmation Code', 'trim|xss_clean|required|callback__check_recaptcha');
                else
                    $this->form_validation->set_rules('captcha', 'Confirmation Code', 'trim|xss_clean|required|callback__check_captcha');
            }
            $data['errors'] = array();

            if ($this->form_validation->run()) // validation ok
            {
                if ($this->tank_auth->login($this->form_validation->set_value('login'), $this->form_validation->set_value('password'), $this->form_validation->set_value('remember'))) // success
                    redirect();
                else 
                {
                    $errors = $this->tank_auth->get_error_message();
                    if (isset($errors['banned'])) // banned user
                    {
                        $this->_show_message($this->lang->line('auth_message_banned').' '.$errors['banned']);
                        return;
                    } 
                    else if (isset($errors['not_activated'])) // not activated user
                        redirect('auth/send_again');
                    else // fail
                        foreach ($errors as $k => $v)    $data['errors'][$k] = '<div class="form_message rounded_medium">' . $this->lang->line($v) . '</div>';
                }
            }
            
            $data['show_captcha'] = FALSE;
            
            if ($this->tank_auth->is_max_login_attempts_exceeded($login)) 
            {
                $data['show_captcha'] = TRUE;
                if ($data['use_recaptcha'])
                    $data['recaptcha_html'] = $this->_create_recaptcha();
                else
                    $data['captcha_html'] = $this->_create_captcha();
            }
            
            $data['title'] = $this->config->item('website_name') . ' | Login';
            $data['content'] = 'auth/login_form';
            $this->load->view('layouts/default', $data);
        }
    }

    /**
     * Login / register FB user on the site
     *
     * @return void
     */
    function facebook()
    {
        $redirect = (isset($_GET['r'])) ? $_GET['r'] : '';
        
        if ( ! $this->tank_auth->is_logged_in() && $this->fb_session) 
        {
            // Get user info from FB
            try 
            {
                $me = $this->facebook_graph->api('/me');
            } 
            catch (FacebookApiException $e) 
            {
                error_log($e);
            }
                
            // Check if user exists
            if ( ! $user_data = Doctrine_Core::getTable('Users')->get_user_by_fb_user_id($me['id']))
            {
                // Check if user email exists
                if ($user_data = Doctrine_Core::getTable('Users')->get_user_by_email($me['email']))
                {
                    // New FB user with an existing account
                    $user_data->fb_user_id = $me['id'];
                    $user_data->activated = 1;
                    $user_data->save();
                }
                else
                {
                    // Create new user
                    $user_data = array(
                        'fb_user_id' => $me['id'],
                        'username' => $me['first_name'],
                        'first_name' => $me['first_name'],
                        'last_name' => $me['last_name'],
                        'password' => NULL,
                        'email' => $me['email'],
                        'gender' => $me['gender'],
                        'user_level' => 0
                    );
                    
                    if (isset($me['birthday']))
                    {
                        $ary_birthday = explode('/', $me['birthday']);
                        $user_data['dob'] = date('Y-m-d', mktime(0, 0, 0, $ary_birthday[0], $ary_birthday[1], $ary_birthday[2]));
                    }
                    else
                        $user_data['dob'] = NULL;
                    
                    $user_data = $this->tank_auth->create_user($user_data, FALSE);

                    // Set profile data
                    $u = Doctrine_Core::getTable('Users')->get_user_by_id($user_data['id'], 1);
                    $u->UserProfiles['first_name'] = (isset($me['first_name'])) ? $me['first_name'] : NULL;
                    $u->UserProfiles['last_name'] = (isset($me['last_name'])) ? $me['last_name'] : NULL;
                    $u->UserProfiles['gender'] = (isset($me['gender'])) ? $me['gender'] : NULL;
                    $u->save();
                }
            }
        
            // Update user
            Doctrine_Core::getTable('Users')->update_login_info(
                $user_data['id'],
                $this->config->item('login_record_ip', 'tank_auth'),
                $this->config->item('login_record_time', 'tank_auth'),
                $this->input
            );
            
            // Set user session
            $this->session->set_userdata(array(
                'id' => $user_data['id'],
                'fb_user_id' => $user_data['fb_user_id'],
                'user_level' => $user_data['user_level'],
                'username' => $user_data['username'],                 
                'status' => '1'
            )); 
        }    
            
        redirect($redirect);
    }
    
    /**
     * Logout user
     *
     * @return void
     */
    function logout()
    {
        $this->tank_auth->logout();

        redirect();
    }

    /**
     * Register user on the site
     *
     * @return void
     */
    function register()
    {
        if ($this->tank_auth->is_logged_in()) // logged in
            redirect();
        else if ($this->tank_auth->is_logged_in(FALSE)) // logged in, not activated
            redirect('auth/send_again');
        else if ( ! $this->config->item('allow_registration', 'tank_auth')) // registration is off
        {
            $this->_show_message($this->lang->line('auth_message_registration_disabled'));
            return;
        } 
        else 
        {
            $this->form_validation->set_rules('email', 'Email', 'trim|required|xss_clean|valid_email');
            $this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean|min_length['.$this->config->item('password_min_length', 'tank_auth').']|max_length['.$this->config->item('password_max_length', 'tank_auth').']|alpha_dash');
            $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'trim|required|xss_clean|matches[password]');
            $this->form_validation->set_rules('first_name', 'First name', 'trim|required|xss_clean');
            $this->form_validation->set_rules('last_name', 'Last name', 'trim|required|xss_clean');
            $this->form_validation->set_rules('gender', 'Gender', 'trim|required|xss_clean');
            $this->form_validation->set_rules('dob_m', 'Month', 'trim|required|xss_clean');
            $this->form_validation->set_rules('dob_d', 'Day', 'trim|required|xss_clean');
            $this->form_validation->set_rules('dob_y', 'Year', 'trim|required|xss_clean');
            $this->form_validation->set_rules('dob', 'Date of birth', 'trim|xss_clean|callback__check_dob');
            
            $captcha_registration = $this->config->item('captcha_registration', 'tank_auth');
            $use_recaptcha = $this->config->item('use_recaptcha', 'tank_auth');
            if ($captcha_registration) 
            {
                if ($use_recaptcha)
                    $this->form_validation->set_rules('recaptcha_response_field', 'Confirmation Code', 'trim|xss_clean|required|callback__check_recaptcha');
                else
                    $this->form_validation->set_rules('captcha', 'Confirmation Code', 'trim|xss_clean|required|callback__check_captcha');
            }
            
            $data['errors'] = array();
            $data['user'] = array();
            
            $email_activation = $this->config->item('email_activation', 'tank_auth');

            if ($this->form_validation->run()) // validation ok
            {
                $data['user']['username'] = $this->form_validation->set_value('first_name');
                $data['user']['first_name'] = $this->form_validation->set_value('first_name');
                $data['user']['last_name'] = $this->form_validation->set_value('last_name');
                $data['user']['email'] = $this->form_validation->set_value('email');
                $data['user']['password'] = $this->form_validation->set_value('password');
                $data['user']['dob'] = $this->form_validation->set_value('dob_y') . '-' . $this->form_validation->set_value('dob_m') . '-' . $this->form_validation->set_value('dob_d'); 
                $data['user']['gender'] = $this->form_validation->set_value('gender');
                $data['user']['fb_user_id'] = NULL;
                
                if ( ! is_null($data = $this->tank_auth->create_user($data['user'], $email_activation))) // success
                {
                    $data['site_name'] = $this->config->item('website_name', 'tank_auth');
                    
                    if ($email_activation) // send "activate" email
                    {
                        $data['activation_period'] = $this->config->item('email_activation_expire', 'tank_auth') / 3600;

                        $this->_send_email('activate', $data['email'], $data);

                        unset($data['password']); // Clear password (just for any case)

                        $this->_show_message($this->lang->line('auth_message_registration_completed_1'));
                        return;

                    } 
                    else 
                    {
                        if ($this->config->item('email_account_details', 'tank_auth')) // send "welcome" email
                            $this->_send_email('welcome', $data['email'], $data);
                            
                        unset($data['password']); // Clear password (just for any case)

                        redirect();
                        return;
                    }
                } 
                else 
                {
                    $errors = $this->tank_auth->get_error_message();
                    foreach ($errors as $k => $v)    $data['errors'][$k] = '<div class="form_message rounded_medium">' . $this->lang->line($v) . '</div>';
                }
            }
            if ($captcha_registration) 
            {
                if ($use_recaptcha)
                    $data['recaptcha_html'] = $this->_create_recaptcha();
                else
                    $data['captcha_html'] = $this->_create_captcha();
            }
            
            $data['captcha_registration'] = $captcha_registration;
            $data['use_recaptcha'] = $use_recaptcha;
            $data['title'] = $this->config->item('website_name') . ' | Register';
            $data['content'] = 'auth/register_form';
            $this->load->view('layouts/default', $data);
        }
    }

    /**
     * Send activation email again, to the same or new email address
     *
     * @return void
     */
    function send_again()
    {
        if (!$this->tank_auth->is_logged_in(FALSE)) // not logged in or activated
            redirect('auth/login');
        else 
        {
            $this->form_validation->set_rules('email', 'Email', 'trim|required|xss_clean|valid_email');

            $data['errors'] = array();

            if ($this->form_validation->run()) // validation ok
            {
                if ( ! is_null($data = $this->tank_auth->change_email($this->form_validation->set_value('email')))) // success
                {
                    $data['site_name']    = $this->config->item('website_name', 'tank_auth');
                    $data['activation_period'] = $this->config->item('email_activation_expire', 'tank_auth') / 3600;

                    $this->_send_email('activate', $data['email'], $data);

                    $this->_show_message(sprintf($this->lang->line('auth_message_activation_email_sent'), $data['email']));
                    return;
                } 
                else 
                {
                    $errors = $this->tank_auth->get_error_message();
                    foreach ($errors as $k => $v)    $data['errors'][$k] = $this->lang->line($v);
                }
            }
            
            $data['title'] = $this->config->item('website_name') . ' | Activation Email';
            $data['content'] = 'auth/send_again_form';
            $this->load->view('layouts/default', $data);
        }
    }

    /**
     * Activate user account.
     * User is verified by user_id and authentication code in the URL.
     * Can be called by clicking on link in mail.
     *
     * @return void
     */
    function activate()
    {
        $user_id = $this->uri->segment(3);
        $new_email_key = $this->uri->segment(4);

        // Activate user
        if ($this->tank_auth->activate_user($user_id, $new_email_key)) // success
        {
            $this->tank_auth->logout();
            $this->_show_message($this->lang->line('auth_message_activation_completed').' '.anchor('/auth/login/', 'Login'));

        } 
        else // fail
            $this->_show_message($this->lang->line('auth_message_activation_failed'));
    }
    
    /**
     * Deactivate user account.
     * User account is deactivated via settings.
     *
     * @return void
     */
    function deactivate()
    {
        if (!$this->tank_auth->is_logged_in()) // not logged in or not activated
            redirect('auth/login');
        
        $user_id = $this->tank_auth->get_user_id();

        // Dectivate user
        if ($this->tank_auth->deactivate_user($user_id)) // success
        {
            $this->tank_auth->logout();
            $this->_show_message($this->lang->line('auth_message_deactivation_completed'));
        } 
    }

    /**
     * Generate reset code (to change password) and send it to user
     *
     * @return void
     */
    function forgot_password()
    {
        if ($this->tank_auth->is_logged_in()) // logged in
            redirect();
        else if ($this->tank_auth->is_logged_in(FALSE)) // logged in, not activated
            redirect('auth/send_again'); 
        else 
        {
            $this->form_validation->set_rules('login', 'Email or login', 'trim|required|xss_clean');

            $data['errors'] = array();

            if ($this->form_validation->run()) // validation ok
            {
                if ( ! is_null($data = $this->tank_auth->forgot_password($this->form_validation->set_value('login')))) 
                {
                    $data['site_name'] = $this->config->item('website_name', 'tank_auth');

                    // Send email with password activation link
                    $this->_send_email('forgot_password', $data['email'], $data);

                    $this->_show_message($this->lang->line('auth_message_new_password_sent'));
                    return;
                } 
                else 
                {
                    $errors = $this->tank_auth->get_error_message();
                    foreach ($errors as $k => $v)    $data['errors'][$k] = $this->lang->line($v);
                }
            }
            
            $data['title'] = $this->config->item('website_name') . ' | Forgot Password';
            $data['content'] = 'auth/forgot_password_form';
            $this->load->view('layouts/default', $data);
        }
    }

    /**
     * Replace user password (forgotten) with a new one (set by user).
     * User is verified by user_id and authentication code in the URL.
     * Can be called by clicking on link in mail.
     *
     * @return void
     */
    function reset_password()
    {
        $user_id = $this->uri->segment(3);
        $new_pass_key = $this->uri->segment(4);

        $this->form_validation->set_rules('new_password', 'New Password', 'trim|required|xss_clean|min_length['.$this->config->item('password_min_length', 'tank_auth').']|max_length['.$this->config->item('password_max_length', 'tank_auth').']|alpha_dash');
        $this->form_validation->set_rules('confirm_new_password', 'Confirm new Password', 'trim|required|xss_clean|matches[new_password]');

        $data['errors'] = array();

        if ($this->form_validation->run()) // validation ok
        {
            if ( ! is_null($data = $this->tank_auth->reset_password($user_id, $new_pass_key, $this->form_validation->set_value('new_password')))) // success
            {
                $data['site_name'] = $this->config->item('website_name', 'tank_auth');

                // Send email with new password
                $this->_send_email('reset_password', $data['email'], $data);

                $this->_show_message($this->lang->line('auth_message_new_password_activated').' '.anchor('/auth/login/', 'Login'));
                return;

            } 
            else // fail
            {    
                $this->_show_message($this->lang->line('auth_message_new_password_failed'));
                return;
            }
        } 
        else 
        {
            // Try to activate user by password key (if not activated yet)
            if ($this->config->item('email_activation', 'tank_auth'))
                $this->tank_auth->activate_user($user_id, $new_pass_key, FALSE);

            if (!$this->tank_auth->can_reset_password($user_id, $new_pass_key))
            {
                $this->_show_message($this->lang->line('auth_message_new_password_failed'));
                return;
            }
        }
        
        $data['title'] = $this->config->item('website_name') . ' | Reset Password';
        $data['content'] = 'auth/reset_password_form';
        $this->load->view('layouts/default', $data);
    }

    /**
     * Change user password
     *
     * @return void
     */
    function change_password()
    {
        if ( ! $this->tank_auth->is_logged_in()) // not logged in or not activated
            redirect('auth/login');
        else 
        {
            $this->form_validation->set_rules('old_password', 'Old Password', 'trim|required|xss_clean');
            $this->form_validation->set_rules('new_password', 'New Password', 'trim|required|xss_clean|min_length['.$this->config->item('password_min_length', 'tank_auth').']|max_length['.$this->config->item('password_max_length', 'tank_auth').']|alpha_dash');
            $this->form_validation->set_rules('confirm_new_password', 'Confirm new Password', 'trim|required|xss_clean|matches[new_password]');

            $data['errors'] = array();

            if ($this->form_validation->run()) // validation ok
            {
                if ($this->tank_auth->change_password($this->form_validation->set_value('old_password'), $this->form_validation->set_value('new_password'))) // success
                {
                    $this->_show_message($this->lang->line('auth_message_password_changed'));
                    return;
                } 
                else // fail
                {
                    $errors = $this->tank_auth->get_error_message();
                    foreach ($errors as $k => $v)    $data['errors'][$k] = $this->lang->line($v);
                }
            }
            
            $data['title'] = $this->config->item('website_name') . ' | Change Password';
            $data['content'] = 'auth/change_password_form';
            $this->load->view('layouts/default', $data);
        }
    }

    /**
     * Change user email
     *
     * @return void
     */
    function change_email()
    {
        if ( ! $this->tank_auth->is_logged_in()) // not logged in or not activated
            redirect('auth/login');
        else 
        {
            $this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');
            $this->form_validation->set_rules('email', 'Email', 'trim|required|xss_clean|valid_email');

            $data['errors'] = array();

            if ($this->form_validation->run()) // validation ok
            {
                if ( ! is_null($data = $this->tank_auth->set_new_email($this->form_validation->set_value('email'), $this->form_validation->set_value('password')))) // success
                {
                    $data['site_name'] = $this->config->item('website_name', 'tank_auth');

                    // Send email with new email address and its activation link
                    $this->_send_email('change_email', $data['new_email'], $data);

                    $this->_show_message(sprintf($this->lang->line('auth_message_new_email_sent'), $data['new_email']));
                    return;

                } 
                else 
                {
                    $errors = $this->tank_auth->get_error_message();
                    foreach ($errors as $k => $v)    $data['errors'][$k] = $this->lang->line($v);
                }
            }
            
            $data['title'] = $this->config->item('website_name') . ' | Change Email';
            $data['content'] = 'auth/change_email_form';
            $this->load->view('layouts/default', $data);
        }
    }

    /**
     * Replace user email with a new one.
     * User is verified by user_id and authentication code in the URL.
     * Can be called by clicking on link in mail.
     *
     * @return void
     */
    function reset_email()
    {
        $user_id = $this->uri->segment(3);
        $new_email_key = $this->uri->segment(4);

        // Reset email
        if ($this->tank_auth->activate_new_email($user_id, $new_email_key)) // success
        {
            $this->tank_auth->logout();
            $this->_show_message($this->lang->line('auth_message_new_email_activated').' '.anchor('/auth/login/', 'Login'));
        } 
        else // fail
            $this->_show_message($this->lang->line('auth_message_new_email_failed'));
    }

    /**
     * Delete user from the site (only when user is logged in)
     *
     * @return void
     */
    function unregister()
    {
        if ( ! $this->tank_auth->is_logged_in()) // not logged in or not activated
            redirect('auth/login');
        else 
        {
            $this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');

            $data['errors'] = array();

            if ($this->form_validation->run()) // validation ok
            {
                if ($this->tank_auth->delete_user($this->form_validation->set_value('password'))) // success
                {    
                    $this->_show_message($this->lang->line('auth_message_unregistered'));
                    return;
                } 
                else // fail
                {                                                        
                    $errors = $this->tank_auth->get_error_message();
                    foreach ($errors as $k => $v)    $data['errors'][$k] = $this->lang->line($v);
                }
            }
            
            $data['title'] = $this->config->item('website_name') . ' | Unregister';
            $data['content'] = 'auth/unregister_form';
            $this->load->view('layouts/default', $data);
        }
    }
    
    /**
     * Show info message
     *
     * @param    string
     * @return    void
     */
    function _show_message($message)
    {
        $data['message'] = $message;
        $data['title'] = $this->config->item('website_name') . ' | Message';
        $data['content'] = 'auth/general_message';
        $this->load->view('layouts/default', $data);
    }

    /**
     * Send email message of given type (activate, forgot_password, etc.)
     *
     * @param    string
     * @param    string
     * @param    array
     * @return    void
     */
    function _send_email($type, $email, &$data)
    {
        $this->load->library('email');

        $subject = sprintf($this->lang->line('auth_subject_'.$type), $this->config->item('website_name', 'tank_auth'));
        $message = $this->load->view('email/'.$type.'-html', $data, TRUE);

        $this->email->send_email($email, $subject, $message);
    }

    /**
     * Create CAPTCHA image to verify user as a human
     *
     * @return    string
     */
    function _create_captcha()
    {
        $this->load->plugin('captcha');

        $cap = create_captcha(
            array(
                'img_path' => './'.$this->config->item('captcha_path', 'tank_auth'),
                'img_url' => base_url().$this->config->item('captcha_path', 'tank_auth'),
                'font_path' => './'.$this->config->item('captcha_fonts_path', 'tank_auth'),
                'font_size' => $this->config->item('captcha_font_size', 'tank_auth'),
                'img_width' => $this->config->item('captcha_width', 'tank_auth'),
                'img_height' => $this->config->item('captcha_height', 'tank_auth'),
                'show_grid' => $this->config->item('captcha_grid', 'tank_auth'),
                'expiration' => $this->config->item('captcha_expire', 'tank_auth'),
            )
        );

        // Save captcha params in session
        $this->session->set_flashdata(array(
            'captcha_word' => $cap['word'],
            'captcha_time' => $cap['time'],
        ));

        return $cap['image'];
    }

    /**
     * Callback function. Check if CAPTCHA test is passed.
     *
     * @param    string
     * @return    bool
     */
    function _check_captcha($code)
    {
        $time = $this->session->flashdata('captcha_time');
        $word = $this->session->flashdata('captcha_word');

        list($usec, $sec) = explode(" ", microtime());
        $now = ((float)$usec + (float)$sec);

        if ($now - $time > $this->config->item('captcha_expire', 'tank_auth')) 
        {
            $this->form_validation->set_message('_check_captcha', $this->lang->line('auth_captcha_expired'));
            return FALSE;
        } 
        else if (($this->config->item('captcha_case_sensitive', 'tank_auth') && $code != $word) || strtolower($code) != strtolower($word)) 
        {
            $this->form_validation->set_message('_check_captcha', $this->lang->line('auth_incorrect_captcha'));
            return FALSE;
        }
        
        return TRUE;
    }

    /**
     * Create reCAPTCHA JS and non-JS HTML to verify user as a human
     *
     * @return    string
     */
    function _create_recaptcha()
    {
        $this->load->helper('recaptcha');

        // Add custom theme so we can get only image
        $options = "<script>var RecaptchaOptions = {theme: 'custom', custom_theme_widget: 'recaptcha_widget'};</script>\n";

        // Get reCAPTCHA JS and non-JS HTML
        $html = recaptcha_get_html($this->config->item('recaptcha_public_key', 'tank_auth'));

        return $options.$html;
    }

    /**
     * Callback function. Check if reCAPTCHA test is passed.
     *
     * @return    bool
     */
    function _check_recaptcha()
    {
        $this->load->helper('recaptcha');

        $resp = recaptcha_check_answer(
                $this->config->item('recaptcha_private_key', 'tank_auth'),
                $_SERVER['REMOTE_ADDR'],
                $_POST['recaptcha_challenge_field'],
                $_POST['recaptcha_response_field']
        );

        if (!$resp->is_valid) 
        {
            $this->form_validation->set_message('_check_recaptcha', $this->lang->line('auth_incorrect_captcha'));
            return FALSE;
        }
        
        return TRUE;
    }

    function _check_dob()
    {
        $dob_m = $this->input->post('dob_m');
        $dob_d = $this->input->post('dob_d');
        $dob_y = $this->input->post('dob_y');
        
        if ($dob_m && $dob_d && $dob_y)
            return TRUE;
        else
        {
            $this->form_validation->set_message('_check_dob', '%s is required.');
            return FALSE;
        }
    }
}

/* End of file auth.php */
/* Location: ./application/controllers/auth.php */