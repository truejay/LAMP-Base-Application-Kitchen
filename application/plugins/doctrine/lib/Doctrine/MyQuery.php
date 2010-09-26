<?php
class Doctrine_MyQuery extends Doctrine_Query
{
    // Since php doesn't support late static binding in 5.2 we need to override
    // this method to instantiate a new MyQuery instead of Doctrine_Query
    public static function create($conn = null)
    {
        return new Doctrine_MyQuery($conn);
    }

    public function preQuery()
    {
        $connection = 'master';
        
        $ci =& get_instance();
        
        $ary_slaves = $ci->config->item('slaves');
        $num_db_slaves = sizeof($ary_slaves);
        if ($num_db_slaves > 0)
            $connection = 'slave_' . rand(1, $num_db_slaves);
            
        // If this is a select query then set connection to one of the slaves
        if ($this->getType() == Doctrine_Query::SELECT) {
            $this->_conn = Doctrine_Manager::getInstance()->getConnection($connection);
        // All other queries are writes so they need to go to the master
        } else {
            $this->_conn = Doctrine_Manager::getInstance()->getConnection($connection);
        }
    }
}
?>