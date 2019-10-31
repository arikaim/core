<?php
/**
 *  Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Traits\Db;

/**
 * Manage models with parent - child relations.
 *  Change parrent id column name in model:
 *       protected $parentColumn = "column name";
*/
trait Tree 
{       
    /**
     * Get model tree
     *
     * @param Moldel $model
     * @return array
     */
    public function getModelPath($model)
    {
        $result = [];
        array_unshift($result,$model->toArray());
      
        while ($model != false) {
            $parentId = $model->attributes[$this->getParentColumn()];
            $model = parent::where('id','=',$parentId)->first();
            if (is_object($model) == true) {
                array_unshift($result,$model->toArray());
            }
        }

        return $result;
    }

    /**
     * Get parent id attribute name default: parent_id
     *
     * @return string
     */
    public function getParentColumn()
    {
        return (isset($this->parentColumn) == true) ? $this->parentColumn : 'parent_id';
    }

    /**
     * Gte model tree for current model
     *
     * @return void
     */
    public function getTreePath()
    {      
        return $this->getModelPath($this);
    }
}
