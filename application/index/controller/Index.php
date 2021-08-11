<?php
namespace app\index\controller;

use app\index\model\Content;
use think\Cache;
use think\Controller;
use think\Db;
use think\Request;

class Index extends Controller
{


    public function index()
    {
        return $this->view->fetch();
    }

    public function login(){
        if (Request::instance()->isPost()){

            $name = Request::instance()->post('name');
            $pwd = Request::instance()->post('pwd');

            if ($name == 'admin' && $pwd == 'admin') {
                Cache::set('login_name',$name);
                $this->success('登陆成功','index/index/main');
            }
            // 其余转向第二个系统

        }else{
            $this->error("登录错误");

        }
    }

    public function main(){

        $cache = Cache::get('login_name');
        if (!$cache || empty($cache) || $cache === false){
            $this->redirect('index/index/index');
        }


        $contentModel = new Content();

        $is_sw = Request::instance()->get('is_sw');


        $total = $contentModel->where(function ($query) use ($is_sw){
            if ($is_sw) {
                $query->where('is_sw',1);
            }
        })->order('time desc')->count();

        $data = $contentModel->where(function ($query) use ($is_sw){
            if ($is_sw) {
                $query->where('is_sw',1);
            }
        })->order('time desc')->paginate(50,false,['query'=>Request::instance()->param()]);

        $page = $data->render();


        $this->view->assign('total',$total);
        $this->view->assign('page',$page);
        $this->view->assign('content_data',$data);

        return $this->view->fetch();
    }



    public function add(){
        if (Request::instance()->isGet()){

            $content = Request::instance()->get('content');

            $validate = $this->validate([
                'content' => $content,
            ],[
                'content' => 'require'
            ]);

            if (true !== $validate){
                return $validate;
            }

            $is_sw = false;

            $data['content'] = $content;
            $data['id'] = strtoupper(substr(uniqid('A'),0,15));
            $data['time'] = time();

            if (true !== $this->filter($content)){
                $is_sw = true;
                $data['is_sw'] = 1;
            }

            $contentModel = new Content();

            $insert = $contentModel->insert($data);


            return $is_sw ? json('有违禁词，请规范留言') : ($insert ? 1 : 0);

        }else{
            return '请求方式错误';
        }
    }


    public function get_content(){
        if (Request::instance()->isGet()){
            $contentModel = new Content();
                $res = $contentModel->where(
                    ['is_sw'=>0,'is_read'=>0]
                )->order('time asc')->limit(1)->find();

                // 获取后更改状态
//                var_dump($res->toArray());
//                if (count($res) > 0){
//                    $ids = [];
//                    foreach ($res as $re){
//                        $ids[] = ['id'=>$re['id'],'is_read'=>1];
//                    }
//                    $contentModel->saveAll($ids);
//                }

                $contentModel->update(['is_read'=>1],['id'=>$res['id']]);
                header('Content-Type:application/json; charset=utf-8');
                return $res ? json_encode(['id'=>$res['id'],'content'=>$res['content']],JSON_UNESCAPED_UNICODE) : "null";
        }
        return '请求方式错误';
    }

    public function delete($id){
        if (Request::instance()->isPost()){
            $contentModel = new Content();

            $validate = $this->validate([
                'id' => Request::instance()->input('id'),
            ],[
                'id' => 'require'
            ]);

            if (true !== $validate){
                return $validate;
            }

            $res = $contentModel->where(['id'=>$id])->delete();

            return $res ? 'Success' : 'Error';
        }else{
            $this->error("请求方式错误");
        }
    }

    public function relieve($id){
        if (Request::instance()->isPost()){
            $contentModel = new Content();

            $validate = $this->validate([
                'id' => Request::instance()->input('id'),
            ],[
                'id' => 'require'
            ]);

            if (true !== $validate){
                return $validate;
            }

            $res = $contentModel->save(['is_sw'=>0],['id'=>$id]);

            return $res ? 'Success' : 'Error';
        }else{
            $this->error("请求方式错误");
        }
    }

    public function ban($id){
        if (Request::instance()->isPost()){
            $contentModel = new Content();

            $validate = $this->validate([
                'id' => Request::instance()->input('id'),
            ],[
                'id' => 'require'
            ]);

            if (true !== $validate){
                return $validate;
            }

            $res = $contentModel->save(['is_sw'=>1],['id'=>$id]);

            return $res ? 'Success' : 'Error';
        }else{
            $this->error("请求方式错误");
        }
    }
    public function clean_all(){
        if (Request::instance()->isPost()){
            return Db::execute('truncate table content');
        }else{
            $this->error("请求方式错误");
        }
    }
    public function get_content_with_w(){
        if (Request::instance()->isGet()){
            $contentModel = new Content();
            $res = $contentModel->where(
                ['is_sw'=>1,'is_read'=>1,'is_sw_read'=>0]
            )->order('time desc')->field('id')->limit(1)->value('id');

            $contentModel->save(['is_sw_read'=>1],['id'=>$res]);

            return $res ? $res : "null";
        }
        return '请求方式错误';
    }


    public function filter($content){

        if($content=="") return false;

        $wordArr = config('words');

        foreach ($wordArr as $row){

            if (false !== strstr($content,$row)) return false;

        }

        return true;

    }


    public function show(){
        return $this->view->fetch('show/index');
    }

}

