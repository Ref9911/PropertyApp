<?php
/**
 * Created by PhpStorm.
 * User: Chilly
 * Date: 2017/5/24
 * Time: 9:11
 */
namespace app\admin\controller;

use think\Controller;

class Login extends Controller {
    public function login(){
        if(session('userid')!=''){
            $this->redirect('User/index');
        }
        return $this->fetch();
    }
    public function do_login(){
        $username = input('post.username');
        $password = input('post.password');
        $stu = db("user");
        $info = $stu->where('username="'.$username.'"')->find();
        if($info){
            if($info['password']==$password){
                $info['nowtime'] = time();
                $stu->where('Id='.$info['Id'])->update($info);
                session('userid',$info['Id']);
                session('username',$username);
                session('lastLoginTime',session('nowLoginTime'));
                session('nowLoginTime',time());
                $this->success("登陆成功",'User/index');
            }else{
                $this->error("密码错误，请重新输入");
            }
        }else{
            $this->error("tan90°");
        }
    }
    public function login_out(){
        session('userid',null);
        $this->success("注销成功，请重新登陆",'Login/login');
    }
}