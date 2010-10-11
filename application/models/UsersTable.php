<?php
/**
 */
class UsersTable extends Doctrine_MyTable
{
    /**
     * Get user record by Id
     *
     * @param    int
     * @param    bool
     * @return    object
     */
    public function get_user_by_id($user_id, $activated)
    {
        $q = $this->createQuery()
            ->where('id = ?', $user_id)
            ->andWhere('activated = ?', $activated ? 1 : 0);
        
        if ($q->count() === 1) return $q->fetchOne();
        return NULL;
        
        /*    
        $this->db->where('id', $user_id);
        $this->db->where('activated', $activated ? 1 : 0);

        $query = $this->db->get(self::TABLE);
        if ($query->num_rows() == 1) return $query->row();
        return NULL;
        */
    }

    /**
     * Get user record by login (username or email)
     *
     * @param    string
     * @return    object
     */
    public function get_user_by_login($login)
    {
        $q = $this->createQuery()
            ->where('LOWER(username) = ?', strtolower($login))
            ->orWhere('LOWER(email) = ?', strtolower($login));
        
        if ($q->count() === 1) return $q->fetchOne();
        return NULL;
        
        /*
        $this->db->where('LOWER(username)=', strtolower($login));
        $this->db->or_where('LOWER(email)=', strtolower($login));

        $query = $this->db->get(self::TABLE);
        if ($query->num_rows() == 1) return $query->row();
        return NULL;
        */
    }

    /**
     * Get user record by username
     *
     * @param    string
     * @return    object
     */
    public function get_user_by_username($username)
    {
        $q = $this->createQuery()
            ->where('LOWER(username) = ?', strtolower($username));
        
        if ($q->count() === 1) return $q->fetchOne();
        return NULL;
        
        /*
        $this->db->where('LOWER(username)=', strtolower($username));

        $query = $this->db->get(self::TABLE);
        if ($query->num_rows() == 1) return $query->row();
        return NULL;
        */
    }

    /**
     * Get user record by email
     *
     * @param    string
     * @return    object
     */
    public function get_user_by_email($email)
    {
        $q = $this->createQuery()
            ->where('LOWER(email) = ?', strtolower($email));
        
        if ($q->count() === 1) return $q->fetchOne();
        return NULL;
        
        /*
        $this->db->where('LOWER(email)=', strtolower($email));

        $query = $this->db->get(self::TABLE);
        if ($query->num_rows() == 1) return $query->row();
        return NULL;
        */
    }

    /**
     * Check if username available for registering
     *
     * @param    string
     * @return    bool
     */
    public function is_username_available($username)
    {
        $q = $this->createQuery()
            ->where('LOWER(username) = ?', strtolower($username));
        
        return $q->count() === 0;
        
        /*
        $this->db->select('1', FALSE);
        $this->db->where('LOWER(username)=', strtolower($username));

        $query = $this->db->get(self::TABLE);
        return $query->num_rows() == 0;
        */
    }

    /**
     * Check if email available for registering
     *
     * @param    string
     * @return    bool
     */
    public function is_email_available($email)
    {
        $q = $this->createQuery()
            ->where('LOWER(email) = ?', strtolower($email))
            ->orWhere('LOWER(new_email) = ?', strtolower($email));
        
        return $q->count() === 0;
        
        /*
        $this->db->select('1', FALSE);
        $this->db->where('LOWER(email)=', strtolower($email));
        $this->db->or_where('LOWER(new_email)=', strtolower($email));

        $query = $this->db->get(self::TABLE);
        return $query->num_rows() == 0;
        */
    }

    /**
     * Create new user record
     *
     * @param    array
     * @param    bool
     * @return    array
     */
    public function create_user($data, $activated = TRUE)
    {
        $data['created'] = date('Y-m-d H:i:s');
        $data['activated'] = $activated ? 1 : 0;
        
        $u = new Users();
        $u->fromArray($data);
        $u->save();
        
        $users_id = $u->id;
        $this->create_profile($users_id, $data);
        return array('users_id' => $users_id);

        /*
        $data['created'] = date('Y-m-d H:i:s');
        $data['activated'] = $activated ? 1 : 0;

        if ($this->db->insert(self::TABLE, $data)) {
            $user_id = $this->db->insert_id();
            if ($activated)    $this->create_profile($user_id);
            return array('user_id' => $user_id);
        }
        return NULL;
        */
    }

    /**
     * Activate user if activation key is valid.
     * Can be called for not activated users only.
     *
     * @param    int
     * @param    string
     * @param    bool
     * @return    bool
     */
    public function activate_user($user_id, $activation_key, $activate_by_email)
    {
        $q = $this->createQuery()
            ->where('id = ?', $user_id);
        if ($activate_by_email) {
            $q->andWhere('new_email_key = ?', $activation_key);
        } else {
            $q->andWhere('new_password_key = ?', $activation_key);
        }
        $q->andWhere('activated = ?', 0);
        
        if ($q->count() === 1) {
            $q = $this->createQuery()
                ->update()
                ->set('activated', '?', 1)
                ->set('new_email_key', 'NULL')
                ->where('id = ?', $user_id);
                
            $q->execute();

            return TRUE;
        }
        return FALSE;
        
        /*
        $this->db->select('1', FALSE);
        $this->db->where('id', $user_id);
        if ($activate_by_email) {
            $this->db->where('new_email_key', $activation_key);
        } else {
            $this->db->where('new_password_key', $activation_key);
        }
        $this->db->where('activated', 0);
        $query = $this->db->get(self::TABLE);

        if ($query->num_rows() == 1) {

            $this->db->set('activated', 1);
            $this->db->set('new_email_key', NULL);
            $this->db->where('id', $user_id);
            $this->db->update(self::TABLE);

            $this->create_profile($user_id);
            return TRUE;
        }
        return FALSE;
        */
    }

    /**
     * Purge table of non-activated users
     *
     * @param    int
     * @return    void
     */
    public function purge_na($expire_period = 172800)
    {
        $q = $this->createQuery()
                ->delete()
                ->where('activated = ?', 0)
                ->andWhere('UNIX_TIMESTAMP(created) < ?', time() - $expire_period);
        
        $q->execute();
        
        /*
        $this->db->where('activated', 0);
        $this->db->where('UNIX_TIMESTAMP(created) <', time() - $expire_period);
        $this->db->delete(self::TABLE);
        */
    }

    /**
     * Delete user record
     *
     * @param    int
     * @return    bool
     */
    public function delete_user($user_id)
    {
        $q = $this->createQuery()
                ->delete()
                ->where('id = ?', $user_id);
        
        if ($q->execute() > 0) {
            $this->delete_profile($user_id);
            return TRUE;
        }       
        
        return FALSE;
        
        /*
        $this->db->where('id', $user_id);
        $this->db->delete(self::TABLE);
        if ($this->db->affected_rows() > 0) {
            $this->delete_profile($user_id);
            return TRUE;
        }
        return FALSE;
        */
    }

    /**
     * Deactivate user
     *
     * @param    int
     * @return    bool
     */
    public function deactivate_user($user_id)
    {
        $q = $this->createQuery()
                ->update()
                ->set('activated', '?', 0)
                ->where('id = ?', $user_id);
        
        return $q->execute() > 0;
        
        /*
        $this->db->where('id', $user_id);
        $this->db->delete(self::TABLE);
        if ($this->db->affected_rows() > 0) {
            $this->delete_profile($user_id);
            return TRUE;
        }
        return FALSE;
        */
    }
    
    /**
     * Set new password key for user.
     * This key can be used for authentication when resetting user's password.
     *
     * @param    int
     * @param    string
     * @return    bool
     */
    public function set_password_key($user_id, $new_pass_key)
    {
        $q = $this->createQuery()
            ->update()
            ->set('new_password_key', '?', $new_pass_key)
            ->set('new_password_requested', '?', date('Y-m-d H:i:s'))
            ->where('id = ?', $user_id);
            
        return $q->execute() > 0;
        
        /*
        $this->db->set('new_password_key', $new_pass_key);
        $this->db->set('new_password_requested', date('Y-m-d H:i:s'));
        $this->db->where('id', $user_id);

        $this->db->update(self::TABLE);
        return $this->db->affected_rows() > 0;
        */
    }

    /**
     * Check if given password key is valid and user is authenticated.
     *
     * @param    int
     * @param    string
     * @param    int
     * @return    void
     */
    public function can_reset_password($user_id, $new_pass_key, $expire_period = 900)
    {
        $q = $this->createQuery()
            ->where('id = ?', $user_id)
            ->andWhere('new_password_key = ?', $new_pass_key)
            ->andWhere('UNIX_TIMESTAMP(new_password_requested) > ?', time() - $expire_period);
        
        return $q->count() === 1;
        
        /*                 
        $this->db->select('1', FALSE);
        $this->db->where('id', $user_id);
        $this->db->where('new_password_key', $new_pass_key);
        $this->db->where('UNIX_TIMESTAMP(new_password_requested) >', time() - $expire_period);

        $query = $this->db->get(self::TABLE);
        return $query->num_rows() == 1;
        */
    }

    /**
     * Change user password if password key is valid and user is authenticated.
     *
     * @param    int
     * @param    string
     * @param    string
     * @param    int
     * @return    bool
     */
    public function reset_password($user_id, $new_pass, $new_pass_key, $expire_period = 900)
    {
        $q = $this->createQuery()
            ->update()
            ->set('password', '?', $new_pass)
            ->set('new_password_key', 'NULL')
            ->set('new_password_requested', 'NULL')
            ->where('id = ?', $user_id)
            ->andWhere('new_password_key = ?', $new_pass_key)
            ->andWhere('UNIX_TIMESTAMP(new_password_requested) >= ?', time() - $expire_period);
        
        return $q->execute() > 0;
        
        /*    
        $this->db->set('password', $new_pass);
        $this->db->set('new_password_key', NULL);
        $this->db->set('new_password_requested', NULL);
        $this->db->where('id', $user_id);
        $this->db->where('new_password_key', $new_pass_key);
        $this->db->where('UNIX_TIMESTAMP(new_password_requested) >=', time() - $expire_period);

        $this->db->update(self::TABLE);
        return $this->db->affected_rows() > 0;
        */
    }

    /**
     * Change user password
     *
     * @param    int
     * @param    string
     * @return    bool
     */
    public function change_password($user_id, $new_pass)
    {
        $q = $this->createQuery()
            ->update()
            ->set('password', '?', $new_pass)
            ->where('id = ?', $user_id);
        
        return $q->execute() > 0;
        
        /*    
        $this->db->set('password', $new_pass);
        $this->db->where('id', $user_id);

        $this->db->update(self::TABLE);
        return $this->db->affected_rows() > 0;
        */
    }

    /**
     * Set new email for user (may be activated or not).
     * The new email cannot be used for login or notification before it is activated.
     *
     * @param    int
     * @param    string
     * @param    string
     * @param    bool
     * @return    bool
     */
    public function set_new_email($user_id, $new_email, $new_email_key, $activated)
    {
        $q = $this->createQuery()
            ->update()
            ->set($activated ? 'new_email' : 'email', '?', $new_email)
            ->set('new_email_key', '?', $new_email_key)
            ->where('id = ?', $user_id)
            ->andWhere('activated = ?', $activated ? 1 : 0);
        
        return $q->execute() > 0;
        
        /*    
        $this->db->set($activated ? 'new_email' : 'email', $new_email);
        $this->db->set('new_email_key', $new_email_key);
        $this->db->where('id', $user_id);
        $this->db->where('activated', $activated ? 1 : 0);

        $this->db->update(self::TABLE);
        return $this->db->affected_rows() > 0;
        */
    }

    /**
     * Activate new email (replace old email with new one) if activation key is valid.
     *
     * @param    int
     * @param    string
     * @return    bool
     */
    public function activate_new_email($user_id, $new_email_key)
    {
        $q = $this->createQuery()
            ->update()
            ->set('email', 'new_email')
            ->set('new_email', 'NULL')
            ->set('new_email_key', 'NULL')
            ->where('id = ?', $user_id)
            ->andWhere('new_email_key = ?', $new_email_key);
        
        return $q->execute() > 0;
        
        /*
        $this->db->set('email', 'new_email', FALSE);
        $this->db->set('new_email', NULL);
        $this->db->set('new_email_key', NULL);
        $this->db->where('id', $user_id);
        $this->db->where('new_email_key', $new_email_key);

        $this->db->update(self::TABLE);
        return $this->db->affected_rows() > 0;
        */
    }

    /**
     * Update user login info, such as IP-address or login time, and
     * clear previously generated (but not activated) passwords.
     *
     * @param    int
     * @param    bool
     * @param    bool
     * @return    void
     */
    public function update_login_info($user_id, $record_ip, $record_time, &$input)
    {
        $q = $this->createQuery()
            ->update()
            ->set('new_password_key', 'NULL')
            ->set('new_password_requested', 'NULL')
            ->set('activated', '?', 1);

        if ($record_ip)      $q->set('last_ip', '?', $input->ip_address());
        if ($record_time)    $q->set('last_login', '?', date('Y-m-d H:i:s'));

        $q->where('id = ?', $user_id);
        $q->execute();
        
        /*            
        $this->db->set('new_password_key', NULL);
        $this->db->set('new_password_requested', NULL);

        if ($record_ip)        $this->db->set('last_ip', $this->input->ip_address());
        if ($record_time)    $this->db->set('last_login', date('Y-m-d H:i:s'));

        $this->db->where('id', $user_id);
        $this->db->update(self::TABLE);
        */
    }

    /**
     * Ban user
     *
     * @param    int
     * @param    string
     * @return    void
     */
    public function ban_user($user_id, $reason = NULL)
    {
        $q = $this->createQuery()
            ->update()
            ->set('banned', '?', 1)
            ->set('ban_reason', '?', $reason)
            ->where('id = ?', $user_id);
        
        $q->execute();
        
        /*
        $this->db->where('id', $user_id);
        $this->db->update(self::TABLE, array(
            'banned'        => 1,
            'ban_reason'    => $reason,
        ));
        */
    }

    /**
     * Unban user
     *
     * @param    int
     * @return    void
     */
    public function unban_user($user_id)
    {
        $q = $this->createQuery()
            ->update()
            ->set('banned', '?', 0)
            ->set('ban_reason', 'NULL')
            ->where('id = ?', $user_id);
        
        $q->execute();
        
        /*
        $this->db->where('id', $user_id);
        $this->db->update(self::TABLE, array(
            'banned'        => 0,
            'ban_reason'    => NULL,
        ));
        */
    }
    
    public function add_points($user_id, $point)
    {
        $q = $this->createQuery()
            ->update()
            ->set('points', 'points + ' . $point)
            ->where('id = ?', $user_id);
           
        $q->execute();    
    }
    
    /**
     * Create an empty profile for a new user
     *
     * @param    int
     * @return    bool
     */
    private function create_profile($user_id, $data = NULL)
    {
        $up = new UserProfiles();
        $up->users_id = $user_id;     
        
        if ($data)
        {
            $up->dob = $data['dob'];
            $up->first_name = $data['first_name'];
            $up->last_name = $data['last_name'];
            $up->gender = $data['gender'];
        }
                    
        if ($up->isValid())
        {
            $up->save();
            return TRUE;
        }
        return FALSE;
        
        /*    
        $this->db->set('user_id', $user_id);
        return $this->db->insert(self::TABLE_PROFILE);
        */
    }

    /**
     * Delete user profile
     *
     * @param    int
     * @return    void
     */
    private function delete_profile($user_id)
    {
        $q = $this->createQuery()
            ->delete('UserProfile')
            ->where('users_id = ?', $user_id);
        
        $q->execute();
        
        /*            
        $this->db->where('user_id', $user_id);
        $this->db->delete(self::TABLE_PROFILE);
        */
    }
}