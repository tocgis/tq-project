<?php

namespace Db;

use TQ\Registry as Di;
use Qh\Http\Api\Response;
use ActiveRecord\ActiveRecordException;
use ActiveRecord\Serialization;
use ActiveRecord\SQLBuilder;
use ActiveRecord\Utils;

/**
 * 基类
 */
class Model extends \ActiveRecord\Model
{
    protected static $_u;
    protected static $instance;

    public function after_construct()
    {
        if (Di::has('u')) {
            static::$_u = Di::get('u');
        }
        Serialization::$DATETIME_FORMAT = 'Y-m-d H:i:s';

    }

    /**
     * 获取用户信息
     * @return User
     */
    public static function get_User()
    {
        if (Di::has('u')) {
            return Di::get('u');
        }
    }

    /**
     * 校验用户信息
     * @return mixed
     */
    public static function checkUser()
    {
        if (Di::has('u')) {
            return Di::get('u');
        } else {
            Response::setResponse(20005,'user not login');
            die();
        }
    }

    /**
     * Make this capsule instance available globally.
     *
     * @return void
     */
    public function setAsGlobal()
    {
        static::$instance = $this;
    }

    public function __call($method, $args)
    {
        //check for build|create_association methods
        if (preg_match('/(build|create)_/', $method))
        {
            if (!empty($args))
                $args = $args[0];

            $association_name = str_replace(array('build_', 'create_'), '', $method);
            $method = str_replace($association_name, 'association', $method);
            $table = static::table();

            if (($association = $table->get_relationship($association_name)) ||
                  ($association = $table->get_relationship(($association_name = \ActiveRecord\Utils::pluralize($association_name)))))
            {
                // access association to ensure that the relationship has been loaded
                // so that we do not double-up on records if we append a newly created
                $this->$association_name;
                return $association->$method($this, $args);
            }
        }

        $content = ("Call to undefined method: $method");

        if (!empty($content)) {
            return Response::setResponse(20000,get_called_class().' '.$content);
        }

    }

    public static function __callStatic($method, $args)
    {
        $options = static::extract_and_validate_options($args);
        $create = false;

        if (substr($method,0,17) == 'find_or_create_by')
        {
            $attributes = substr($method,17);

            // can't take any finders with OR in it when doing a find_or_create_by
            if (strpos($attributes,'_or_') !== false)
                $content = ("Cannot use OR'd attributes in find_or_create_by");

            $create = true;
            $method = 'find_by' . substr($method,17);
        }

        if (substr($method,0,7) === 'find_by')
        {
            $attributes = substr($method,8);
            $options['conditions'] = \ActiveRecord\SQLBuilder::create_conditions_from_underscored_string(static::connection(),$attributes,$args,static::$alias_attribute);

            if (!($ret = static::find('first',$options)) && $create)
                return static::create(\ActiveRecord\SQLBuilder::create_hash_from_underscored_string($attributes,$args,static::$alias_attribute));

            return $ret;
        }
        elseif (substr($method,0,11) === 'find_all_by')
        {
            $options['conditions'] = \ActiveRecord\SQLBuilder::create_conditions_from_underscored_string(static::connection(),substr($method,12),$args,static::$alias_attribute);
            return static::find('all',$options);
        }
        elseif (substr($method,0,8) === 'count_by')
        {
            $options['conditions'] = \ActiveRecord\SQLBuilder::create_conditions_from_underscored_string(static::connection(),substr($method,9),$args,static::$alias_attribute);
            return static::count($options);
        }

        $content = ("Call to undefined method: $method");


        if (!empty($content)) {
            return Response::setResponse(20000,get_called_class().' '.$content);
        }
    }

    /**
     * 改写通过主键寻找记录
     *
     * 未找到记录时，不抛出异常
     *
     * @method find_by_pk
     *
     * @param  [type]     $values
     * @param  [type]     $options
     *
     * @return [type]     [description]
     */
    public static function find_by_pk($values, $options)
    {
        $options['conditions'] = static::pk_conditions($values);
        $list = static::table()->find($options);
        $results = count($list);

        if ($results != ($expected = count($values)))
        {
            $class = get_called_class();

            if ($expected == 1)
            {
                if (!is_array($values))
                    $values = array($values);

                return ;//("Couldn't find $class with ID=" . join(',',$values));
            }

            $values = join(',',$values);
            throw new \ActiveRecord\RecordNotFound("Couldn't find all $class with IDs ($values) (found $results, but was looking for $expected)");
        }
        return $expected == 1 ? $list[0] : $list;
    }

    /**
     * 软删除
     *
     * 如定义了软删除字段则自动软删除
     *
     * @return [type]
     */
    public function before_destroy()
    {
        if (isset($this->deleted_at)) {

            if ($this->deleted_at > 0) {
                return false;
            } else {
                $this->deleted_at = time();
                $this->save();
                return false;
            }
        }
    }

}
