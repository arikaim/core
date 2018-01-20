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

class DbHandler extends AbstractProcessingHandler
{
    private $model;

    public function __construct($level = Logger::DEBUG, $bubble = true) 
    {
        parent::__construct($level,$bubble);
        try {
            $this->model = Model::Logs(true);
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
        
        if (is_object($this->model) == true) {
            $this->model->fill($info);
            return $this->model->save();
        }
    }  
}
