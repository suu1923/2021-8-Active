<?php
/**
 * Created by PhpStorm.
 * User: Suu
 * Date: 2021/8/11
 * Time: 10:53
 */

namespace app\index\model;


use think\Model;

class UserName extends Model
{

    protected $table = 'username';


    protected $append = ['percentage'];

    protected function getPercentageAttr(){
        $total = $this->sum('num');
        $self = $this->num;

        return number_format(($self/$total)*100,2).'%';
    }
}