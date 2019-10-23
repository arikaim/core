<?php
/**
 *  Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Traits\Db;

use Arikaim\Core\Utils\DateTime;

/**
 * Soft delete trait
 *      
*/
trait SoftDelete 
{    
    public function isDeleted()
    {
        return ! is_null($this->{$this->getDeletedAttributeName()});
    }

    public function softDelete($id = null)
    {
        $model = (empty($id) == true) ? $this : $this->findById($id);
        $model->{$this->getDeletedAttributeName()} = DateTime::getCurrentTime();
        
        return $model->save();
    }

    public function restore($id = null)
    {
        $model = (empty($id) == true) ? $this : $this->findById($id);
        $model->{$this->getDeletedAttributeName()} = null;
        
        return $model->save();
    }

    public function softDeletedQuery()
    {
        return $this->whereNotNull($this->getDeletedAttributeName());
    }

    /**
     * Get uuid attribute name
     *
     * @return string
     */
    public function getDeletedAttributeName()
    {
        return (isset($this->soft_delete_attribute) == true) ? $this->soft_delete_attribute : 'date_deleted';
    }
}
