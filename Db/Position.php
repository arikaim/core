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

trait Position 
{    
    public abstract function getModel();

    public function setPosition()
    {   
        $model = $this->getModel();     
        $position = $model->max('position');       
        $position++;
        $model->position = $position;        
        return $position;
    }

    public function movePositionAfter($after_uuid)
    {   
        $model = $this->getModel();
        $current_position = $model->position;
        
        // set current possition to 0 avoid unique index error
        $model->position = 0;
        $model->update();

        if ($model->uuid == $after_uuid) {
            return $this->moveFirst($model,$current_position);
        }
       
        $after_model = $model->where('uuid','=',$after_uuid)->first();
        $after_position = $after_model->position;
        if ($current_position == $after_position) {
            return false;
        }
        // right move
        if ($current_position < $after_position) {
            $list = $model->whereRaw(" position > $current_position AND position <= $after_position ORDER BY position ")->get();
            foreach ($list as $item) {
                $item->position = $item->position - 1;
                $item->update();
            }
            $model->position = $after_position;
            $model->update();
        }
        // left move
        if ($current_position > $after_position) {
            $this->leftMove($model,$current_position,$after_position);
        }
        return true;
    }

    public function moveFirst($model, $current_position)
    {
        $this->leftMove($model,$current_position,0);
    }

    protected function leftMove($model, $current_position, $after_position)
    {
        $list = $model->whereRaw(" position > $after_position AND position <= $current_position ORDER BY position DESC ")->get();
        foreach ($list as $item) {
           // echo $item->position;
            $item->position = $item->position + 1;
            $item->update();
        }
        $model->position = $after_position + 1;
        $model->update();
        return true;
    }
}
