<?php

namespace Db;


class AdminUserRole extends Model
{
    public static $table_name = 'admin_user_role';
    public static $primary_key = array('user_id','rold_id');
    //public static $primary_key = 'rold_id';

    static $belongs_to  = array(
        array('admin_user','foreign_key'=>'admin_user_id'),
        array('admin_role','foreign_key'=>'admin_role_id')
    );



}
