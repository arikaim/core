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

use Arikaim\Core\Access\Interfaces\UserProviderInterface;
use Arikaim\Core\Access\Interfaces\AutoTokensInterface;
use Arikaim\Core\Utils\Text;
use Arikaim\Core\Utils\Uuid as UuidFactory;
use Arikaim\Core\Utils\DateTime;
use Arikaim\Core\Models\Users;

use Arikaim\Core\Db\Traits\Uuid;
use Arikaim\Core\Db\Traits\Find;
use Arikaim\Core\Db\Traits\Status;
use Arikaim\Core\Db\Traits\DateCreated;
use Arikaim\Core\Access\Traits\Auth;

/**
 * Access tokens database model
*/
class AccessTokens extends Model implements UserProviderInterface
{
    use Uuid,
        Find,
        Status,
        Auth,
        DateCreated;

    /**
     * Db table name
     *
     * @var string
     */
    protected $table = 'access_tokens';

    /**
     * Auth id column name
     *
     * @var string
     */
    protected $authIdColumn = 'user_id';

    /**
     * Fillable attributes
     *
     * @var array
    */
    protected $fillable = [
        'token',
        'date_expired',
        'user_id',
        'type'
    ];

    /**
     * Disable timestamps
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * User relation
     *
     * @return Relation|null
     */
    public function user()
    {
        return $this->belongsTo(Users::class,'user_id');
    }

    /**
     * Get token type
     *
     * @param string $token
     * @return integer|null
     */
    public function getType(string $token): ?int
    {
        $model = $this->getToken($token);
        
        return (\is_object($model) == true) ? $model->type : null;
    }

    /**
     * Get user credentials
     *
     * @param array $credential
     * @return array|null
     */
    public function getUserByCredentials(array $credentials): ?array
    {
        $token = $credentials['token'] ?? null;
        if (empty($token) == true) {
            return null;
        }
      
        $model = $this->findByColumn($token,'token');
        if ($model === false) {
            return null;
        }
        if ($model->isExpired() == true) {  
            // token expired                 
            return null;
        }
        if ($model->status != $this->ACTIVE()) { 
            // token is disabled
            return null;
        }

        $user = $model->user()->first();
        $authId = $user->getAuthId();
        $user = $user->toArray();
        $user['auth_id'] = $authId;
      
        return $user;
    }

    /**
     * Return user details by auth id
     *
     * @param string|integer $id
     * @return array|null
     */
    public function getUserById($id): ?array
    {
        $model = $this->findById($id);

        return (\is_object($model) == false) ? null : $model->user()->first()->toArray();          
    }

    /**
     * Return true token is correct.
     *
     * @param string $password
     * @return bool
     */
    public function verifyPassword(string $password): bool
    {
        $model = $this->findByColumn($password,'token'); 

        return \is_object($model);
    }

    /**
     * Expired mutator attribute
     *
     * @return boolean
     */
    public function getExpiredAttribute()
    {
        return ($this->date_expired == -1) ? false : $this->isExpired();          
    }

    /**
     * Create access token
     *
     * @param integer $userId
     * @param integer $type
     * @param integer $expireTime
     * @param bool $deleteExpired
     * @return array|false
     */
    public function createToken(
        int $userId, 
        int $type = AutoTokensInterface::PAGE_ACCESS_TOKEN, 
        int $expireTime = 1800, 
        bool $deleteExpired = true
    )
    {       
        $dateExpired = ($expireTime != -1) ? DateTime::getTimestamp() + $expireTime : $expireTime;
        switch($type) {
            case AutoTokensInterface::LOGIN_ACCESS_TOKEN: 
                $token = UuidFactory::create();
                break;
            case AutoTokensInterface::PAGE_ACCESS_TOKEN:
                $token = UuidFactory::create();
                break;
            case AutoTokensInterface::API_ACCESS_TOKEN:
                $token = Text::createToken(62);
                break;
            default:
                $token = UuidFactory::create();
        }
       
        if ($type == AutoTokensInterface::PAGE_ACCESS_TOKEN) {
            $this->deleteUserToken($userId,$type);
        }
      
        if ($deleteExpired == true) {          
            $this->deleteExpired($userId,$type);
        }
        
        $model = $this->getTokenByUser($userId,$type);
        if (\is_object($model) == true) {
            return $model->toArray();
        }

        $info = [
            'user_id'      => $userId,
            'token'        => $token,
            'date_expired' => $dateExpired,
            'type'         => $type
        ];
        $model = $this->create($info);

        return (\is_object($model) == true) ? $model->toArray() : false;
    }

    /**
     * Remove access token
     *
     * @param string $token
     * @return boolean
     */
    public function removeToken(string $token): bool
    {
        $model = $this->findByColumn($token,['uuid','token']);

        return (\is_object($model) == true) ? (bool)$model->delete() : true;           
    }

    /**
     * Get access token
     *
     * @param  string $token
     * @return Model|null
     */
    public function getToken(string $token)
    {      
        $model = $this->findByColumn($token,'token');

        return (\is_object($model) == true) ? $model : null;
    }

    /**
     * Return true if token is expired
     *
     * @param string|null $token
     * @return boolean
     */
    public function isExpired(?string $token = null): bool
    {
        $model = (empty($token) == true) ? $this : $this->findByColumn($token,'token');
        if (\is_object($model) == false) {
            return true;
        }
        if ($model->date_expired == -1) {
            return false;
        }

        return ((DateTime::getTimestamp() > $model->date_expired) || (empty($model->date_expired) == true));
    }

    /**
     * Find token
     *
     * @param integer $userId
     * @param integer $type
     * @return Model|false
     */
    public function getTokenByUser(int $userId, int $type = AutoTokensInterface::PAGE_ACCESS_TOKEN)
    {
        $model = $this->where('user_id','=',$userId)->where('type','=',$type)->first();

        return (\is_object($model) == true) ? $model : false;
    }

    /**
     * Return true if token exist
     *
     * @param integer $userId
     * @param integer $type
     * @return boolean
     */
    public function hasToken(int $userId, int $type = AutoTokensInterface::PAGE_ACCESS_TOKEN): bool
    {    
        return \is_object($this->getTokenByUser($userId,$type));
    }

    /**
     * Delete user token
     *
     * @param integer $userId
     * @param integer|null $type
     * @return boolean
     */
    public function deleteUserToken(int $userId, ?int $type = AutoTokensInterface::PAGE_ACCESS_TOKEN): bool
    {
        $model = $this->where('user_id','=', $userId);
        if (empty($type) == false) {
            $model = $model->where('type','=',$type);
        }
       
        return (bool)$model->delete();
    }

    /**
     * Delete expired token
     *
     * @param integer $userId
     * @param integer|null $type
     * @return boolean
     */
    public function deleteExpired(int $userId, ?int $type = null): bool
    {
        $model = $this
            ->where('date_expired','<',DateTime::getTimestamp())
            ->where('date_expired','<>',-1)
            ->where('user_id','=',$userId);
        
        if ($type != null) {
            $model = $model->where('type','=',$type);
        }

        return (bool)$model->delete();
    }

    /**
     * Delete all expired tokens
     *
     * @return bool
     */
    public function deleteExpiredTokens(): bool
    {
        return (bool)$this->where('date_expired','<',DateTime::getTimestamp())->where('date_expired','<>',-1)->delete();
    }

    /**
     * Get all tokens for user
     *
     * @param integer $userId
     * @return null|Model
     */
    public function getUserTokens(int $userId)
    {
        return $this->where('user_id','=',$userId)->get();
    }
}
