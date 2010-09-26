<?php
abstract class Doctrine_MyTable extends Doctrine_Table
{
    public function createQuery($alias = '')
    {
        if ( ! empty($alias)) {
            $alias = ' ' . trim($alias);
        }

        $class = $this->getAttribute(Doctrine_Core::ATTR_QUERY_CLASS);

        return Doctrine_MyQuery::create(null, $class)
            ->from($this->getComponentName() . $alias);
    }
}
?>