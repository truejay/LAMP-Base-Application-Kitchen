<?php
/**
 */
class UserAutologinTable extends Doctrine_MyTable
{
    /**
     * Get user data for auto-logged in user.
     * Return NULL if given key or user ID is invalid.
     *
     * @param    int
     * @param    string
     * @return    object
     */
    function get($user_id, $key)
    {
        $q = $this->createQuery()
            ->select('u.id, u.username')
            ->from('User u')
            ->leftJoin('u.UserAutologin ua')
            ->where('ua.users_id = ?', $user_id)
            ->where('ua.key_id = ?', $key);
        
        if ($q->count() === 1) return $q->fetchOne();
        return NULL;
            
        /*    
        $this->db->select(self::TABLE_USERS.'.id');
        $this->db->select(self::TABLE_USERS.'.username');
        $this->db->from(self::TABLE_USERS);
        $this->db->join(self::TABLE, self::TABLE.'.user_id = '.self::TABLE_USERS.'.id');
        $this->db->where(self::TABLE.'.user_id', $user_id);
        $this->db->where(self::TABLE.'.key_id', $key);
        $query = $this->db->get();
        if ($query->num_rows() == 1) return $query->row();
        return NULL;
        */
    }

    /**
     * Save data for user's autologin
     *
     * @param    int
     * @param    string
     * @return    bool
     */
    function set($user_id, $key, &$input)
    {
        $ua = new UserAutologin();
        $ua->users_id = $user_id;
        $ua->key_id = $key;
        $ua->user_agent = substr($input->user_agent(), 0, 149);
        $ua->last_ip = $input->ip_address();
        
        if ($up->isValid())
        {
            $ua->save();
            return TRUE;
        }
        return FALSE;
        
        /*
        return $this->db->insert(self::TABLE, array(
            'user_id'         => $user_id,
            'key_id'         => $key,
            'user_agent'     => substr($this->input->user_agent(), 0, 149),
            'last_ip'         => $this->input->ip_address(),
        ));
        */
    }

    /**
     * Delete user's autologin data
     *
     * @param    int
     * @param    string
     * @return    void
     */
    function delete($user_id, $key)
    {
        $q = $this->createQuery()
            ->delete()
            ->where('users_id = ?', $user_id)
            ->where('key_id = ?', $key);
            
        $q->execute();
        
        /*    
        $this->db->where('user_id', $user_id);
        $this->db->where('key_id', $key);
        $this->db->delete(self::TABLE);
        */
    }

    /**
     * Delete all autologin data for given user
     *
     * @param    int
     * @return    void
     */
    function clear($user_id)
    {
        $q = $this->createQuery()
            ->delete()
            ->where('users_id = ?', $user_id);
        
        $q->execute();
        
        /*    
        $this->db->where('user_id', $user_id);
        $this->db->delete(self::TABLE);
        */
    }

    /**
     * Purge autologin data for given user and login conditions
     *
     * @param    int
     * @return    void
     */
    function purge($user_id, &$input)
    {
        $q = $this->createQuery()
            ->delete()
            ->where('users_id = ?', $user_id)
            ->where('user_agent = ?', substr($input->user_agent(), 0, 149))
            ->where('last_ip = ?', $input->ip_address());
            
        $q->execute();
        
        /*            
        $this->db->where('user_id', $user_id);
        $this->db->where('user_agent', substr($this->input->user_agent(), 0, 149));
        $this->db->where('last_ip', $this->input->ip_address());
        $this->db->delete(self::TABLE);
        */
    }
}