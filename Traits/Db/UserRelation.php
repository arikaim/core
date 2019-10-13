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

use Arikaim\Core\Arikaim;
use Arikaim\Core\Models\Users;

/**
 * User Relation trait
 *      
*/
trait UserRelation 
{    
    /**
     * Init model events.
     *
     * @return void
     */
    public static function bootUserRelation()
    {
        static::creating(function($model) {
            $user_id = $model->getUuidAttributeName();   
            if (empty($model->attributes[$user_id]) == true) {  
                $auth_id = Arikaim::auth()->getId();
                $model->attributes[$user_id] = (empty($auth_id) == true) ? null : $auth_id;
            }
        });
    }

    /**
     * Get uuid attribute name
     *
     * @return string
     */
    public function getUserIdAttributeName()
    {
        return (isset($this->user_attribute_name) == true) ? $this->user_attribute_name : 'user_id';
    }

    /**
     * User relation
     *
     * @return Relation
     */
    public function user()
    {
        return $this->belongsTo(Users::class,$this->getUserIdAttributeName());
    }
}
