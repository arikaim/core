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
 * Update Status field
 * Change default status column name in model:
 *      protected $statusColumn = 'column name';
*/
trait Status 
{        
    /**
     * Disabled
     */
    static $DISABLED = 0;

    /**
     * Active
     */
    static $ACTIVE = 1;
    
    /**
     * Completed
     */
    static $COMPLETED = 2;  

    /**
     * Deleted
     */
    static $DELETED = 3;  

    /**
     * Pending activation
     */
    static $PENDING = 4;

    /**
     *  Suspended
     */
    static $SUSPENDED = 5;

    /**
     * Return active value
     *
     * @return integer
     */
    public function ACTIVE()
    {
        return Self::$ACTIVE;
    }

    /**
     * Return disabled value
     *
     * @return integer
     */
    public function DISABLED()
    {
        return Self::$DISABLED;
    }

    /**
     * Return deleted value
     *
     * @return integer
     */
    public function SOFTDELETED()
    {
        return Self::$DELETED;
    }

    /**
     * Return completed value
     *
     * @return integer
     */
    public function COMPLETED()
    {
        return Self::$COMPLETED;
    }

    /**
     * Pending activation
     *
     * @return integer
     */
    public function PENDING()
    {
        return Self::$PENDING;
    }

    /**
     * Suspended
     *
     * @return integer
     */
    public function SUSPENDED()
    {
        return Self::$SUSPENDED;
    }

    /**
     * Get status column name
     *
     * @return string
     */
    public function getStatusColumn()
    {
        return (isset($this->statusColumn) == true) ? $this->statusColumn : 'status';
    }

    /**
     * Return active model query builder
     *
     * @return void
     */
    public function getActive()
    {
        return parent::where($this->getStatusColumn(),'=',Self::$ACTIVE);
    }
    
    /**
     * Return disabled model query builder
     *
     * @return void
     */
    public function getDisabled()
    {
        return parent::where($this->getStatusColumn(),'=',Self::$DISABLED);
    }

    /**
     * Return deleted model query builder
     *
     * @return void
     */
    public function getDeleted()
    {
        return parent::where($this->getStatusColumn(),'=',Self::$DELETED);
    }

    /**
     * Set model status
     *
     * @param integer|string|null $status
     * @return bool
     */
    public function setStatus($status = null)
    {
        $attribute = $this->getStatusColumn();
        if ($status === "toggle") {     
            $status = ($this->$attribute == 1) ? 0 : 1;
        }
        $this->$attribute = $status;    

        return $this->save();         
    }
}
