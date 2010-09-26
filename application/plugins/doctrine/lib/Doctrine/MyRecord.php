<?php
abstract class Doctrine_MyRecord extends Doctrine_Record
{
    public function save(Doctrine_Connection $conn = null)
    {
        // If specific connection is not provided then lets force the connection
        // to be the master
        if ($conn === null) {
            $conn = Doctrine_Manager::getInstance()->getConnection('master');
        }
        parent::save($conn);
    }
}
?>