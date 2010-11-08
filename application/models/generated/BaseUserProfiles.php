<?php
/**
 * BaseUserAutologin
 */
abstract class BaseUserAutologin extends Doctrine_MyRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('user_autologin');
        $this->hasColumn('key_id', 'string', 32, array(
             'type' => 'string',
             'length' => 32,
             'fixed' => true,
             'unsigned' => false,
             'primary' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('users_id', 'integer', 4, array(
             'type' => 'integer',
             'length' => 4,
             'fixed' => false,
             'unsigned' => false,
             'primary' => true,
             'notnull' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('user_agent', 'string', 150, array(
             'type' => 'string',
             'length' => 150,
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('last_ip', 'string', 40, array(
             'type' => 'string',
             'length' => 40,
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('last_login', 'timestamp', null, array(
             'type' => 'timestamp',
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             ));
    }

    public function setUp()
    {
        parent::setUp();
        
        $this->hasOne('Users', array(
                'local' => 'users_id',
                'foreign' => 'id'
            )
        );
    }
}