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
use Arikaim\Core\Traits\Db\Uuid;;
use Arikaim\Core\Traits\Db\ToggleValue;;
use Arikaim\Core\Traits\Db\Position;;
use Arikaim\Core\Traits\Db\Find;;
use Arikaim\Core\Traits\Db\Status;;

/**
 * Language database model
 */
class Language extends Model  
{
    use Uuid,
        Find,
        Status,
        ToggleValue,
        Position;

    protected $table = 'language';
    
    protected $fillable = [
        'code',
        'title',
        'native_title',
        'code_3',
        'country_code'];
   
    public $timestamps = false;
    
    public function setCodeAttribute($value)
    {
        $this->attributes['code'] = strtolower($value);
    }

    public function has($code, $active = null)
    {
        $language = $this->where('code','=',$code);
        if ($active == true) {
            $language = $language->where('status','=',Self::ACTIVE());
        }
        $language = $language->first();
        return (is_object($language) == true) ? true : false;           
    }

    public function add(array $language)
    {
        if ($this->has($language['code']) == true) {
            return false;
        }
        return $this->create($language);    
    }

    public function getDefaultLanguage()
    {
        try {
            $model = $this->where('default','=','1')->first();
            if (is_object($model) == true) {
                return $model->code;
            }            
        } catch(\Exception $e) {
            return 'en';
        }
        return 'en';
    }
}
