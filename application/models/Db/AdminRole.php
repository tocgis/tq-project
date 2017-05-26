<?php

namespace Db;

class AdminRole extends Model
{
    public static $table_name = 'admin_role';
    public static $primary_key = 'role_id';

    static $belongs_to = array(
        array('admin_user_role','foreign_key'=>'admin_role_id'),
        array('admin_user','through'=>'admin_user_role','foreign_key'=>'admin_role_id')
    );

    static $has_many = array(
        array('admin_role_menu'),
        array('admin_menu','through'=>'admin_role_menu','foreign_key'=>'admin_role_id'),
        array('admin_role_resource'),
        array('admin_resource','through'=>'admin_role_resource','foreign_key'=>'admin_role_id'),
    );

    public static function getIndex($user_id = 0)
    {
        $Roles = self::find('all',array(
            'conditions'=>array('status >= 0')
        ));

        if ($Roles) {
            foreach ($Roles as $k => $Role) {
                $data[] = array(
                    'role_id'=>$Role->role_id,
                    'title'=>$Role->title,
                    'description'=>$Role->description,
                    'status'=>$Role->status,
                    'created_at'=>$Role->created_at,
                    'updated_at'=>$Role->updated_at
                );
            }
            return $data;
        }
    }

}
