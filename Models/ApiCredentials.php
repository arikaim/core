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

use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Utils\DateTime;
use Arikaim\Core\Interfaces\Auth\UserProviderInterface;

use Arikaim\Core\Traits\Db\Uuid;
use Arikaim\Core\Traits\Db\Find;
use Arikaim\Core\Traits\Db\Status;
use Arikaim\Core\Traits\Db\DateCreated;
use Arikaim\Core\Traits\Auth\Auth;
use Arikaim\Core\Traits\Auth\Password;

/**
 * Api Credentials database model
*/
class ApiCredentials extends Model implements UserProviderInterface
{
    use Uuid,
        Find,
        Status,
        Auth,
        Password,
        DateCreated;

    /**
     * Fillable attributes
     *
     * @var array
    */
    protected $fillable = [
        'key',
        'secret',
        'date_expired',
        'user_id'
    ];

    /**
     * Hidden attributes
     *
     * @var array
     */
    protected $hidden = ['secret'];

    /**
     * Disable timestamps
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * Auth id attribute name
     *
     * @var integer
    */
    protected $auth_id_attribute = 'user_id';

    /**
     * Enrypt password disabled
     *
     * @var mixed
     */
    protected $password_encrypt_algo = null;

    /**
     * Password attribute name
     *
     * @var string
     */
    protected $password_attribute = 'secret';

    /**
     * Create Api Credintails
     *
     * @param integer $user_id
     * @param integer|null $expire_time
     * @return Model
     */
    public function createCredintails($user_id, $expire_time = null)
    {
        $date_expired = (empty($expire_time) == true) ? null : DateTime::getCurrentTime() + $expire_time;

        return $this->create([
            'user_id'   => $user_id,
            'key'       => Utils::createUUID(),
            'secret'    => Utils::createRandomKey(),
            'date_expired' => $date_expired,  
        ]);
    }

    /**
     * Get user api credintails
     *
     * @param integer $user_id
     * @return Model|false
     */
    public function getCredintails($user_id)
    {
        return $this->findByColumn($user_id,'user_id');
    }

    /**
     * Get user by api credentials
     *
     * @param array $credential
     * @return Model|false
     */
    public function getUserByCredentials(array $credentials)
    {
        $model = $this->where('status','=',$this->ACTIVE());

        if (isset($credentials['key']) == true && isset($credentials['secret']) == true) {
            $model = $this->where('key','=',$credentials['key']);
            $model = $model->where('secret','=',$credentials['secret'])->first();

            return (is_object($model) == false) ? false : $model;
        }

        return false;
    }
}