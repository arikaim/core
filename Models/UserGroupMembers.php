<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Models;

use Illuminate\Database\Eloquent\Model;

use Arikaim\Core\Models\Users;
use Arikaim\Core\Models\UserGroups;

use Arikaim\Core\Traits\Db\Uuid;
use Arikaim\Core\Traits\Db\DateCreated;

/**
 * User groups details database model
 */
class UserGroupMembers extends Model  
{
    use Uuid,
        DateCreated;

    /**
     * Fillable attributes
     *
     * @var array
    */
    protected $fillable = [        
        'user_id',
        'groupd_id',
        'date_expire'
    ];
    
    /**
     * Disable timestamps
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * User group relation
     *
     * @return void
     */
    public function group()
    {
        return $this->hasOne(UserGroups::class,'groupd_id','id');     
    }

    /**
     * User relation
     *
     * @return void
     */
    public function user()
    {
        return $this->hasOne(Users::class,'user_id','id');     
    }
}