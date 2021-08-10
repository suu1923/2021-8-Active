<?php
/**
 * Created by PhpStorm.
 * User: Suu
 * Date: 2021/8/3
 * Time: 15:34
 */

namespace app\index\model;

use think\Model;

class Content extends Model
{

    protected $table = 'content';

    protected $hidden = [
        'time',
        'is_read',
        'is_sw'
    ];

}