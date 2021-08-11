<?php
/**
 * Created by PhpStorm.
 * User: Suu
 * Date: 2021/8/11
 * Time: 10:49
 */

namespace app\index\controller;


use app\index\model\UserName;
use think\Cache;
use think\Controller;

class Uimg extends Controller
{
    public function sub_name(){
            $name = $this->request->param('name');
            if (empty($name)){
                return '名字不能为空';
            }


            // 使用了缓存的
//            $cache = Cache::get('active_'.$name);
//
//            if (!$cache){
//                // 不存在缓存的话，创建缓存
//                $cache_data['time'] = time();
//                $cache_data['num'] = 1;
//
//                $res = Cache::set('active_'.$name,json_encode($cache_data));
//
//                return $res ? 'Success' : 'Error';
//            }else{
//                $cache_data = json_decode($cache,true);
//
//                $diff = floor(time()-$cache_data['time']);
//                if ($diff <= 5){
//                    return '提交太过频繁';
//                }
//                $cache_new_data['time'] = time();
//                $cache_new_data['num'] = $cache_data['num'] + 1;
//
//                $res = Cache::set('active_'.$name,json_encode($cache_new_data));
//                return $res ? 'Success' : 'Error';
//            }



            $userModel = new UserName();

            $data = $userModel->where('name',$name)->find();

            if ($data){
                // 获取当前时间
                $last_time = $data['time'];
                $diff = floor(time() - $last_time);
                if ($diff <= 5){
                    return '提交太过频繁';
                }
                $res = $userModel->where('name',$name)->inc('num')->exp('time',time())->update();

                return $res ? "Success" : "Error";

            }

            $data['name'] = $name;
            $data['time'] = time();
            $data['num'] = 1;

            $res = $userModel->insert($data);

            return $res ? "Success" : 'Error';

    }

    public function anay(){
        $num = $this->request->param('num') ? $this->request->param('num') : 10;

        $res = (new UserName())->order('num desc')->field('id,time',true)->limit($num)->select();

        return $res ? json($res) : null;

    }


    public function show(){
        $num = $this->request->param('num') ? $this->request->param('num') : 20;
        $res = (new UserName())->order('num desc')->field('id,time',true)->limit($num)->select();
        foreach ($res as $re=>$value){
            if (file_exists('./image/'.$value['name'].'.jpg')){
                $value['path'] = $value['name'].'.jpg';
            }else{
                $value['path'] = 'default.jpg';
            }
        }

        $this->assign('data',$res);
        return $this->view->fetch();
    }
}