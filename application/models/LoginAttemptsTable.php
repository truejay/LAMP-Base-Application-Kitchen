<?php
/**
 */
class LoginAttemptsTable extends Doctrine_MyTable
{
    /**
     * Get number of attempts to login occured from given IP-address or login
     *
     * @param    string
     * @param    string
     * @return    int
     */
    function get_attempts_num($ip_address, $login)
    {
        $q = $this->createQuery()
            ->where('ip_address = ?', $ip_address);
        if (strlen($login) > 0) $q->orWhere('login = ?', $login);

        return $q->count();
        
        /*
        $this->db->select('1', FALSE);
        $this->db->where('ip_address', $ip_address);
        if (strlen($login) > 0) $this->db->or_where('login', $login);

        $qres = $this->db->get(self::TABLE);
        return $qres->num_rows();
        */
    }

    /**
     * Increase number of attempts for given IP-address and login
     *
     * @param    string
     * @param    string
     * @return    void
     */
    function increase_attempt($ip_address, $login)
    {
        $la = new LoginAttempts();
        $la->ip_address = $ip_address;
        $la->login = $login;
        $la->save();
        
        //$this->db->insert(self::TABLE, array('ip_address' => $ip_address, 'login' => $login));
    }

    /**
     * Clear all attempt records for given IP-address and login.
     * Also purge obsolete login attempts (to keep DB clear).
     *
     * @param    string
     * @param    string
     * @param    int
     * @return    void
     */
    function clear_attempts($ip_address, $login, $expire_period = 86400)
    {
        $q = $this->createQuery()
            ->delete()
            ->where('(ip_address = ?', $ip_address)
            ->andWhere('login = ?)', $login)
            ->orWhere('UNIX_TIMESTAMP(time) < ?', time() - $expire_period);
        
        $q->execute();
        
        /*
        $this->db->where(array('ip_address' => $ip_address, 'login' => $login));
        
        // Purge obsolete login attempts
        $this->db->or_where('UNIX_TIMESTAMP(time) <', time() - $expire_period);

        $this->db->delete(self::TABLE);
        */
    }
}