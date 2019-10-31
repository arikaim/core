<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\Logger;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Db\Schema;

/**
 * Database log handler, write log record to database 
 */
class DbHandler extends AbstractProcessingHandler
{
    /**
     * Model obj
     *
     * @var object
     */
    private $model;

    /**
     * Constructor
     *
     * @param Model|null $model
     * @param integer $level
     * @param boolean $bubble
     */
    public function __construct($model = null, $level = Logger::DEBUG, $bubble = true) 
    {
        parent::__construct($level,$bubble);

        try {
            $this->model = (is_object($model) == false) ? Model::Logs() : $model;
            if (Schema::hasTable($this->model) == false) {
                $this->model = null;
            }
        } catch (\Exception $e) {
            $this->model = null;
        }
    }

    /**
     * Write log record to db
     *
     * @param array $record
     * @return void
     */
    protected function write(array $record)
    {
        return (is_object($this->model) == true) ? $this->model->create($record) : false;          
    }  
}
