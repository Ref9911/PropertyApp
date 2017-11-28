<?php
/**
 * Created by PhpStorm.
 * User: Chilly
 * Date: 2017/5/24
 * Time: 9:13
 */
namespace app\admin\controller;

use think\Controller;
use think\Auth;

class Common extends Controller{
    public function _initialize(){
        parent::_initialize();
        if(session('userid')==''){
            $this->redirect('Login/login');
        }
        else{
            $auth=new Auth();
            $controller = request()->controller();
            $action = request()->action();
            $info = $auth->check($controller,session('userid'));
            if($info){
                $info = $auth->check($controller.'/'.$action,session('userid'));
                if(!$info){
                    $this->error('你没有权限访问此界面');
                }
            }else{
                session('userid',null);
                $this->error('你没有权限','Login/login');exit;
            }
        }
    }
}