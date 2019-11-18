<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Traits\Db;

use Arikaim\Core\System\DateTime;

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

    public function getDeletedCount()
    {
        $query = $this->softDeletedQuery();
        return $query->count();
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
     * @param integer|string|null string $id
     * @return boolean
     */
    public function restore($id = null)
    {
        $model = (empty($id) == true) ? $this : $this->findById($id);
        $model->{$this->getDeletedColumn()} = null;
        
        return $model->save();
    }

    /**
     * Restore all soft deleted rows
     *
     * @return boolean
     */
    public function restoreAll()
    {
        $columName = $this->getDeletedColumn();
        $query = $this->softDeletedQuery();
        
        return $query->update([
            $columName => null
        ]);
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
     * Get not deleted query
     *
     * @return QueryBuilder
     */
    public function getNotDeletedQuery()
    {
        return $this->whereNull($this->getDeletedColumn());
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
