<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\Logger;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Db\Schema;
use Arikaim\Core\Utils\DateTime;

/**
 * Database log handler, write log record to database 
 */
class DbHandler extends AbstractProcessingHandler
{
    private $model;

    public function __construct($level = Logger::DEBUG, $bubble = true) 
    {
        parent::__construct($level,$bubble);
        try {
            $this->model = Model::Logs();
            if (Schema::hasTable($this->model) == false) {
                $this->model = null;
            }
        } catch (\Exception $e) {
            $this->model = null;
        }
    }

    protected function write(array $record)
    {
        $info['channel'] =  $record['channel'];      
        $info['url'] = $record['context']['url'];
        $info['method'] = $record['context']['method'];
        $info['user_agent'] = $record['context']['http_user_agent'][0];
        $info['level'] = $record['level'];
        $info['message'] = $record['message'];
        $info['ip_address'] = $record['context']['client_ip'];
        $info['date_created'] = DateTime::getCurrentTime();
        
        if (is_object($this->model) == true) {           
            $this->model->fill($info);
            return $this->model->save();
        }
        return false;
    }  
}
