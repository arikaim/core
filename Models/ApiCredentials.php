<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Models;

use Illuminate\Database\Eloquent\Model;

use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Utils\DateTime;
use Arikaim\Core\Interfaces\Auth\UserProviderInterface;
use Arikaim\Core\Utils\Uuid as UuidFactory;

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
     * Db table name
     *
     * @var string
     */
    protected $table = 'api_credentials';

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
    protected $authIdColumn = 'user_id';

    /**
     * Enrypt password disabled
     *
     * @var mixed
     */
    protected $passwordEncryptAlgo = null;

    /**
     * Password attribute name
     *
     * @var string
     */
    protected $passwordColumn = 'secret';

    /**
     * Create Api Credintails
     *
     * @param integer $userId
     * @param integer|null $expireTime
     * @return Model
     */
    public function createCredintails($userId, $expireTime = null)
    {
        $dateExpired = (empty($expireTime) == true) ? null : DateTime::getTimestamp() + $expireTime;

        return $this->create([
            'user_id'   => $userId,
            'key'       => UuidFactory::create(),
            'secret'    => Utils::createRandomKey(),
            'date_expired' => $dateExpired,  
        ]);
    }

    /**
     * Get user api credintails
     *
     * @param integer $userId
     * @return Model|false
     */
    public function getCredintails($userId)
    {
        return $this->findByColumn($userId,'user_id');
    }

    /**
     * Get user by api credentials
     *
     * @param array $credentials
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
