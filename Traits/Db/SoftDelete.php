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
    /**
     * Return true if model is deleted
     *
     * @return boolean
     */
    public function isDeleted()
    {
        return ! is_null($this->{$this->getDeletedColumn()});
    }

    /**
     * Soft delete model
     *
     * @param integer string $id
     * @return boolean
     */
    public function softDelete($id = null)
    {
        $model = (empty($id) == true) ? $this : $this->findById($id);
        $model->{$this->getDeletedColumn()} = DateTime::getCurrentTime();
        
        return $model->save();
    }

    /**
     * Restore soft deleted models
     *
     * @param integer string $id
     * @return boolean
     */
    public function restore($id = null)
    {
        $model = (empty($id) == true) ? $this : $this->findById($id);
        $model->{$this->getDeletedColumn()} = null;
        
        return $model->save();
    }

    /**
     * Get soft deleted query
     *
     * @return QueryBuilder
     */
    public function softDeletedQuery()
    {
        return $this->whereNotNull($this->getDeletedColumn());
    }

    /**
     * Get uuid attribute name
     *
     * @return string
     */
    public function getDeletedColumn()
    {
        return (isset($this->softDeleteColumn) == true) ? $this->softDeleteColumn : 'date_deleted';
    }
}
