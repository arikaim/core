<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager;
use Arikaim\Core\Utils\Number;

class Events extends Model  
{
    protected $fillable = ['name','handler_class','extension_name','uuid','priority'];
    public $timestamps = false;

    public function getEvents($name) 
    {           
        $this->where('name','=',$name);
        $model = $this->where('status','=',1)->orderBy('priority')->get();
        if ( is_object($model) == true ) return $model->toArray(); 
        return [];
    }

    public function disableExtensionEvents($extension_name) 
    {  
        $this->changeEventStatus(null,$extension_name,0);
    }

    public function enableExtensionEvents($extension_name) 
    {  
       $this->changeEventStatus(null,$extension_name,1);
    }

    public function enableEvent($event_name)
    {
        $this->changeEventStatus($event_name,null,1);
    }

    public function disableEvent($event_name)
    {
        $this->changeEventStatus($event_name,null,0);
    }

    private function changeEventStatus($event_name = null, $extension_name = null, $status) 
    {
        if ( $event_name != null ) {
            $this->where('name','=',$event_name);
        }
        if ( $extension_name != null ) {
            $this->where('extension_name','=',$extension_name);
        }
        $events = $this->get();
        foreach ($events as $event) {
            $event->status = Number::getInteger($status);
            $event->update();
        }
    }
}
