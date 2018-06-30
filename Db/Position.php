<?php
/**
 *  Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Db;

/**
 * Update position field
*/
trait Position 
{    
    public static function bootPosition()
    {
        static::creating(function($model) {   
            self::setPosition($model);
        });

        static::deleting(function($model) {   
            
        });
    }
    
    private static function setPosition($model)
    {   
        $position = $model->max('position');       
        $model->position = $position + 1;        
        return $model->position;
    }

    public function movePosition($model, $after_uuid)
    {   
        if (is_object($model) == false) {
            return false;
        }

        $current_position = $model->position;
        // set current possition to null avoid unique index error
        $model->position = null;
        $model->update();
    
        if ($model->uuid == $after_uuid) {
            return $this->moveFirst($model,$current_position);
        }
        $after_model = $model->where('uuid','=',$after_uuid)->first();
        $after_position = $after_model->position;
        if ($current_position == $after_position) {
            return false;
        }
        // update right rows
        if ($current_position < $after_position) {
            $list = $model->where('position','>',$current_position)->where('position','<=',$after_position)->orderBy('position')->get();
            foreach ($list as $item) {
                $item->position = $item->position - 1;
                $item->update();
            }
            $model->position = $after_position;
            $model->update();
        }
        // update left rows
        if ($current_position > $after_position) {
            $this->updateLeftItems($model,$current_position,$after_position);
        }
        return true;
    }

    private function moveFirst($model, $current_position)
    {
        $this->updateLeftItems($model,$current_position,0);
    }

    private function updateLeftItems($model, $current_position, $after_position)
    {
        $list = $model->where('position','>',$after_position)->where('position','<=',$current_position)->orderBy('position','desc')->get();
        foreach ($list as $item) {
            $item->position = $item->position + 1;
            $item->update();
        }
        $model->position = $after_position + 1;
        $model->update();
        return true;
    }
}
