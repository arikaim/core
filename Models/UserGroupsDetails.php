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
use Arikaim\Core\Db\Uuid;

/**
 * User groups details database model
 */
class UserGroupsDetails extends Model  
{
    use Uuid;

    protected $fillable = [        
        'user_id',
        'groupd_id',
        'date_expire'];
        
    public $timestamps = false;
}
