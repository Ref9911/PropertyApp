<?php
/**
 * Created by PhpStorm.
 * User: Chilly
 * Date: 2017/5/22
 * Time: 20:22
 */
namespace app\admin\controller;

use app\admin\controller\Common;

class User extends Common {
    //主页
    public function index(){
        $stu = db('user');
        $complain = db('complain');
        $repair = db('repair');
        $consult = db('consult');
        $access = db('auth_group_access');
        $group = db('auth_group');

        $id = session('userid');

        $start_time = strtotime(date("Y-m-d"));

        $total_tousu = $complain->where("type='投诉'")->select();
        $tousu = $complain->where("type='投诉' and pubtime>".$start_time)->select();
        $this->assign('total_tousu',count($total_tousu));
        $this->assign('tousu',count($tousu));

        $total_biaoyang = $complain->where("type='表扬'")->select();
        $biaoyang = $complain->where("type='表扬' and pubtime>".$start_time)->select();
        $this->assign('total_biaoyang',count($total_biaoyang));
        $this->assign('biaoyang',count($biaoyang));

        $total_baoxiu = $repair->select();
        $baoxiu = $repair->where("starttime>".$start_time)->select();
        $this->assign('total_baoxiu',count($total_baoxiu));
        $this->assign('baoxiu',count($baoxiu));

        $total_jianyi = $consult->where("type='建议'")->select();
        $jianyi = $consult->where("type='建议' and pubtime>".$start_time)->select();
        $this->assign('total_jianyi',count($total_jianyi));
        $this->assign('jianyi',count($jianyi));

        $total_zixun = $consult->where("type='咨询'")->select();
        $zixun = $consult->where("type='咨询' and pubtime>".$start_time)->select();
        $this->assign('total_zixun',count($total_zixun));
        $this->assign('zixun',count($zixun));

        $user_info = $stu->where('Id='.$id.'')->find();
        $group_id = $access->where('uid='.$id)->find()['group_id'];
        $user_info['title'] = $group->where('Id='.$group_id)->find()['title'];
        $user_info['loginTime'] = session('lastLoginTime');
        $this->assign('user_info',$user_info);

        $db = db('xiaoxi');
        $info = $db->order("Id desc")->limit(0,5)->select();
        $this->assign('info',$info);

        return $this->fetch();
    }


    //资金列表
    public function expense_list(){
        $db=db('expense');
        $list=$db->order("Id desc")->paginate(5);
        // $table2=db('member');
        // $data4=$table2->where('Id='.$list['mid'])->select();
        // $this->assign('data4',$data4);
        $this->assign('page',$list->render());
        $table2=db('member');
        $info=$list->toArray()['data'];
        for($i=0;$i<count($info);$i++){
            $info[$i]['username']=$table2->field('truename')->where('Id='.$info[$i]['mid'])->find()['truename'];
        }
        $table3=db('user');
        for($i=0;$i<count($info);$i++){
            $info[$i]['user']=$table3->field('username')->where('Id='.$info[$i]['uid'])->find()['username'];
        }
        $table4=db('project');
        for($i=0;$i<count($info);$i++){
            $info[$i]['title']=$table4->field('title')->where('Id='.$info[$i]['pid'])->find()['title'];
        }
        $this->assign('name',$info);
        return $this->fetch();
    }
    //添加资金
    public function expense_add(){
        $table=db('project');
        $list=$table->select();
        $this->assign('name',$list);
        $ta=db('user');
        $data3=$ta->select();
        $this->assign('data3',$data3);

        return $this->fetch();
    }
    public function expense_do_add(){
        $db=db('expense');
        $data=input();
        if($data['pid']==0){
            $data['pid']=' ';
        }
        if($data['uid']==0){
            $data['uid']=' ';
        }
        $data['pubtime']=time($data['pubtime']);
        $list=$db->insert($data);
        if($list){
            $this->success('succ','User/expense_list');
        }else{
            $this->error('fail');
        }
    }
    //删除资金
    public function expense_delete(){
        $db=db('expense');
        $id=input('id');
        $list=$db->where('Id in ('.$id.')')->delete();
        if($list){
            $this->success('succ','User/expense_list');
        }else{
            $this->error('fail');
        }
    }
    //修改资金
    public function expense_update(){
        $data2=input('Id');
        //	echo $data2;
        $db=db('expense');
        $data=$db->where('Id='.$data2)->find();

        $this->assign('info',$data);
//        echo $data['pid'];
        //echo "<pre>";
        //print_r($data);
        //exit;
        $ta=db('user');
        $data3=$ta->select();
        $this->assign('data3',$data3);

        $table2=db('member');
        $data4=$table2->where('Id='.$data['mid'])->find();
        $this->assign('data4',$data4);

        $table=db('project');
        $list=$table->select();

        $this->assign('name',$list);
        return $this->fetch();
    }
    public function expense_do_update(){
        $data=input();
        $table2=db('member');
        $db=db('expense');
        $data1=$data['truename'];
        unset($data['truename']);
        $mid=$data['mid'];
        unset($data['mid']);
        $data['pubtime']=time($data['pubtime']);

        if($data['pubtime']){
            $info=$db->update($data);

        }else{
            unset($data['pubtime']);
            $info=$db->update($data);
        }
        if($info){
            //$ff=$table2->where('Id='.$mid)->update($data1);

            $this->success('succ','User/expense_list');
        }else{
            $this->error('fail');
        }
    }


    //业主列表
    public function member_list(){
        $db=db('member');
        $tianjialie=$db->order("Id desc")->paginate('5');
        $this->assign('userlist',$tianjialie);
        $this->assign('page',$tianjialie->render());
        return $this->fetch();
    }
    //业主删除
    public function member_delete(){
        $id = input();
        $db = db('member');
        $id = implode(',', $id);
        $info = $db->where('Id in (' . $id . ')')->delete();
        if ($info) {
            $this->success('删除成功', 'User/member_list');
        } else {
            $this->error('删除失败', 'User/member_list');
        }
        //$info=$db->where('Id in ('.$id.')')->delete();
    }
    //业主修改
    public function member_update(){
        $table=input('post.');
        $id=input('Id');
        $db=db('member');
        $info=$db->where('Id='.$id)->find();
        $this->assign('info',$info);
        return $this->fetch();
    }
    public function member_do_update(){
        $data=input('post.Id');
        $ble=input('post.');
        $table=db('member');
        $info=$table->where('Id='.$data)->update($ble);
        if($info) {
            $this->success('修改成功','User/member_list');
        }elseif($info!==false) {
            $this->error('没有修改','User/member_list');
        }else {
            $this->error('修改失败','User/member_list');
        }
    }


    //投诉列表
    public function complain_list(){
        $db=db('complain');
        $list=$db->where('type="投诉"')->order("Id desc")->paginate('5');
        $this->assign('page',$list->render());
        $user = db('member');
        $list = $list->toArray()['data'];
        for($i=0;$i<count($list);$i++) {
            $list[$i]['username'] = $user->where('Id='.$list[$i]['mid'])->find()['nickname'];
            unset($list[$i]['mid']);
        }
        $this->assign('ergou',$list);
        return $this->fetch();
    }
    //修改投诉
    public function complain_update(){
        $id=input('Id');
        $db=db('complain');
        $info=$db->where('Id='.$id)->find();
        $db = db('member');
        $info['username']=$db->where('Id='.$info['mid'])->find()['nickname'];
        unset($info['mid']);
        $info['photo'] = explode(',',$info['photo']);
//        echo "<pre>";print_r($list);exit;
//        echo count($info['photo']);exit;
        $this->assign('num',count($info['photo']));
        $this->assign('vo',$info);
        return $this->fetch();
    }
    public function complain_do_update(){
        $db=db('complain');
        $table=input();
//        echo "<pre>";print_r($table);exit;
        $info=$db->where('Id='.$table['Id'])->update($table);
        if($info!==false) {
            $this->success('编辑成功','User/complain_list');
        }else{
            $this->error('编辑失败','User/complain_list');
        }
    }
    //删除投诉
    public function complain_delete(){
        $id=input('id');
        $db=db('complain');
        //$info=$db->where('Id='.$id)->delete();
        $info=$db->where('Id in ('.$id.')')->delete();
        if($info) {
            $this->success('删除成功','User/complain_list');
        }else{
            $this->error('删除失败','User/complain_list');
        }
    }


    //表扬列表
    public function praise_list(){
        $db=db('complain');
        $list=$db->where('type="表扬"')->order("Id desc")->paginate('5');
        $this->assign('page',$list->render());
        $user = db('member');
        $list = $list->toArray()['data'];
        for($i=0;$i<count($list);$i++) {
            $list[$i]['username'] = $user->where('Id='.$list[$i]['mid'])->find()['nickname'];
            unset($list[$i]['mid']);
        }
        //$info['photo'] = explode(',',$info['photo']);
//        echo "<pre>";print_r($list);exit;
//        echo count($info['photo']);exit;
        //$this->assign('num',count($info['photo']));
        $this->assign('ergou',$list);
        return $this->fetch();
    }
    //表扬查看
    public function praise_check(){
        $id=input('Id');
        $table=db('complain');
        $db = db('member');
        $info=$table->where('Id='.$id)->find();
        $info['username']=$db->where('Id='.$info['mid'])->find()['nickname'];
        unset($info['mid']);
        $info['photo'] = explode(',',$info['photo']);
//        echo "<pre>";print_r($info);exit;
//        echo count($info['photo']);exit;
        $this->assign('num',count($info['photo']));
        $this->assign('vo',$info);
        return $this->fetch();
    }
    //删除表扬
    public function praise_delete(){
        $id=input('id');
        $db=db('complain');
        //$info=$db->where('Id='.$id)->delete();
        $info=$db->where('Id in ('.$id.')')->delete();
        if($info) {
            $this->success('删除成功','User/praise_list');
        }else{
            $this->error('删除失败','User/praise_list');
        }
    }


    //建议列表
    public function proposal_list(){
        $db=db('consult');
        $list=$db->where('type="建议"')->order("Id desc")->paginate('5');
        $this->assign('page',$list->render());
        $user = db('member');
        $list = $list->toArray()['data'];
        for($i=0;$i<count($list);$i++) {
            $list[$i]['username'] = $user->where('Id='.$list[$i]['mid'])->find()['nickname'];
            unset($list[$i]['mid']);
        }
        $this->assign('ergou',$list);
        return $this->fetch();
    }
    //建议查看
    public function proposal_check(){
        $id=input('Id');
        $table=db('consult');
        $db = db('member');
        $info=$table->where('Id='.$id)->find();
        $info['username']=$db->where('Id='.$info['mid'])->find()['nickname'];
        unset($info['mid']);
//        echo('<pre>');print_r($info);exit;
        $info['photo'] = explode(',',$info['photo']);
//        echo "<pre>";print_r($info);exit;
//        echo count($info['photo']);exit;
        $this->assign('num',count($info['photo']));
        $this->assign('vo',$info);
        return $this->fetch();
    }
    //删除建议
    public function proposal_delete(){
        $id=input('id');
        $db=db('consult');
        //$info=$db->where('Id='.$id)->delete();
        $info=$db->where('Id in ('.$id.')')->delete();
        if($info) {
            $this->success('删除成功','User/proposal_list');
        }else{
            $this->error('删除失败','User/proposal_list');
        }
    }


    //咨询列表
    public function consult_list(){
        $db=db('consult');
        $list=$db->where('type="咨询"')->order("Id desc")->paginate('5');
        $this->assign('page',$list->render());
        $user = db('member');
        $list = $list->toArray()['data'];
        for($i=0;$i<count($list);$i++) {
            $list[$i]['username'] = $user->where('Id='.$list[$i]['mid'])->find()['nickname'];
            unset($list[$i]['mid']);
        }
        $this->assign('ergou',$list);
        return $this->fetch();
    }
    //咨询查看
    public function consult_check(){
        $id=input('Id');
        $table=db('consult');
        $db = db('member');
        $info=$table->where('Id='.$id)->find();
        $info['username']=$db->where('Id='.$info['mid'])->find()['nickname'];
        unset($info['mid']);
//        echo('<pre>');print_r($info);exit;
        $info['photo'] = explode(',',$info['photo']);
//        echo "<pre>";print_r($info);exit;
//        echo count($info['photo']);exit;
        $this->assign('num',count($info['photo']));
        $this->assign('vo',$info);
        return $this->fetch();
    }
    //删除咨询
    public function consult_delete(){
        $id=input('id');
        $db=db('consult');
        //$info=$db->where('Id='.$id)->delete();
        $info=$db->where('Id in ('.$id.')')->delete();
        if($info) {
            $this->success('删除成功','User/consult_list');
        }else{
            $this->error('删除失败','User/consult_list');
        }
    }


    //对我们的建议
    public function suggest_list(){
        $db=db('suggest');
        $list=$db->order("Id desc")->paginate('5');
        $this->assign('page',$list->render());
        $user = db('member');
        $list = $list->toArray()['data'];
        for($i=0;$i<count($list);$i++) {
            $list[$i]['username'] = $user->where('Id='.$list[$i]['mid'])->find()['nickname'];
            unset($list[$i]['mid']);
        }
        $this->assign('ergou',$list);
        return $this->fetch();
    }
    //对我们的建议查看
    public function suggest_check(){
        $id=input('Id');
        $table=db('suggest');
        $db = db('member');
        $info=$table->where('Id='.$id)->find();
        $info['username']=$db->where('Id='.$info['mid'])->find()['nickname'];
        unset($info['mid']);
        $this->assign('vo',$info);
        return $this->fetch();
    }
    //删除对我们的建议
    public function suggest_delete(){
        $id=input('id');
        $db=db('consult');
        //$info=$db->where('Id='.$id)->delete();
        $info=$db->where('Id in ('.$id.')')->delete();
        if($info) {
            $this->success('删除成功','User/suggest_list');
        }else{
            $this->error('删除失败','User/suggest_list');
        }
    }


    //收费项目列表
    public function project_list(){
        $title = input('project_search_title');
        $db = db('project');
        //判断搜索内容中是否包含空格
        if(ctype_space($title)||strpos($title," ")){
            echo "
                <script type='text/javascript'>
                    alert('输入内容中不能包含空格');
                    window.location.href='/index.php/admin/User/project_list';
                    exit;
                </script>
            ";
        }
        //判断搜索框内是否为空
        if(empty($title)){
            $user_info = $db
                ->order("Id desc")
                ->whereLike('title','%'.$title.'%')
                ->paginate(5);
        }else{
            $user_info = $db
                ->order("Id desc")
                ->whereLike('title','%'.$title.'%')
                ->paginate(5,false,['query' => request()->param()]);
        }
        //判断是否搜索到了内容
        if($user_info->toArray()['data']){
            $page = $user_info->render();
            $this->assign("page", $page);
            $info = $user_info->toArray()['data'];
//        $user = db('user');
//        for($i=0;$i<count($info);$i++) {
//            $username = $user->where('Id='.$info[$i]['uid'])->find();
//            $info[$i]['username'] = $username['username'];
//        }
            $this->assign('list', $info);
            $this->assign('title', $title);
            return $this->fetch();
        }else{
            echo "
                <script type='text/javascript'>
                    alert('没有找到内容');
                    window.location.href='/index.php/admin/User/project_list';
                    exit;
                </script>
            ";
        }

    }
    //添加收费项目
    public function project_add(){
        return $this->fetch();
    }
    public function project_do_add(){
        $db = db('project');
        $data = input();
        //如果 session('id') 不为空  uid=session('id')  默认为1
        if(session('id')){
            $data['uid'] = session('id');
        }else{
            $data['uid'] = '1';
        }
        $data['pubtime'] = time();
        $info = $db->insert($data);
        if($info){
            $this->success('添加成功','User/project_list');
        }else{
            $this->error('添加失败','User/project_list');
        }
    }
    //修改收费项目
    public function project_update(){
        $id = input('id');
        $db = db('project');
        $info = $db->where('Id='.$id)->find();
        $this->assign('list',$info);
        return $this->fetch();
    }
    public function project_do_update(){
        $db = db('project');
        $date = input();
        $info = $db->where('Id='.$date['Id'])->update($date);
        if($info){
            $this->success("修改成功",'User/project_list');
        }elseif($info==="false"){
            $this->error("修改失败",'User/project_list');
        }else{
            $this->success("未进行任何操作",'User/project_list');
        }
    }
    //删除收费项目
    public function project_delete(){
        $id = input('id');
        //判断是否有id值
        if(empty($id)){
            echo "
                <script type='text/javascript'>
                    alert('没有删除任何内容');
                    window.location.href='/index.php/admin/User/project_list';
                </script>
            ";
        }
        $db = db('project');
        $info = $db->where('Id in('.$id.')')->delete();
        if($info){
            $this->success('删除成功','User/project_list');
        }else{
            $this->error('删除失败','User/project_list');
        }
    }


    //报修列表页
    public function repair_list(){
        $table=db('repair');
        $baoxiu=$table->order("Id desc")->paginate('5');
        $this->assign('page',$baoxiu->render());
        $user = db('member');
        $baoxiu = $baoxiu->toArray()['data'];
        for($i=0;$i<count($baoxiu);$i++) {
            $baoxiu[$i]['username'] = $user->where('Id='.$baoxiu[$i]['mid'])->find()['nickname'];
            unset($baoxiu[$i]['mid']);
        }
//        echo '<pre>';
//        print_r($baoxiu);exit;
        $this->assign('baoxiu',$baoxiu);
        return $this->fetch();
    }
    //报修修改
    public function repair_update(){
        $id=input('Id');
        $table=db('repair');
        $db = db('member');
        $info=$table->where('Id='.$id)->find();
        $info['username']=$db->where('Id='.$info['mid'])->find()['nickname'];
        unset($info['mid']);
        $info['photo'] = explode(',',$info['photo']);
//        echo "<pre>";print_r($info);exit;
//        echo count($info['photo']);exit;
        $this->assign('num',count($info['photo']));
        $this->assign('info',$info);
        return $this->fetch();
    }
    public function repair_do_update(){
        $data=input();
        $id=input('post.Id');
        $table=db('repair');
        $data['endtime'] = time();
        $info=$table->where('Id='.$id)->update($data);
        if($info!==false) {
            $this->success('修改成功','User/repair_list');
        } else{
            $this->error('修改失败','User/repair_list');
        }
    }
    //报修删除
    public function repair_delete(){
        $id=input('id');
        $table=db('repair');
        $info=$table->where('Id in ('.$id.')')->delete();
        if($info) {
            $this->success('删除成功','User/repair_list');
        } else{
            $this->error('删除失败','User/repair_list');
        }
    }
    //报修搜索
    public function repair_search(){
        $stu=db('repair');
        $sousuo=input('sousuo');
        $info=$stu->whereLike("zone","%".$sousuo."%")->paginate(config('5'));
        if($info->toArray()['data']){
            $this->assign('list',$info->toArray());
            $this->assign('pages',$info->render());
            return $this->fetch('house_list');
        }
        else{
            echo "
                <script>
                    alert('没有搜索到该条数据');location.href='house_list.html';
                </script>
            ";
        }
    }


    //房产列表
    public function house_list(){
        $stu=db('house');
        $list=$stu->order('Id desc')->paginate('5');
        $this->assign('pages',$list->render());
        $list = $list->toArray()['data'];
        $user = db('member');
        $home = db('estate');
//        echo "<pre>";
//        print_r($list);exit;
        for($i=0;$i<count($list);$i++) {
            $list[$i]['propertyowner'] = $user->where('Id='.$list[$i]['uid'])->find()['truename'];
            $list[$i]['zone'] = $home->where('Id='.$list[$i]['zoneid'])->find()['name'];
            unset($list[$i]['uid']);
            unset($list[$i]['zoneid']);
        }
        $this->assign('list',$list);

        return $this->fetch();
    }
    //房产添加
    public function house_add(){
        $estate = db('estate');
        $zonename = $estate->select();
        $this->assign('zonename',$zonename);
        return $this->fetch();
    }
    public function house_do_add(){
        $stu=db('house');
        $member = db('member');
        $list=input('post.');
        $user['truename'] = $list['truename'];
        unset($list['truename']);
        $user['telphone'] = $list['telphone'];
        unset($list['telphone']);
        $user['sex'] = $list['sex'];
        unset($list['sex']);
//        echo '<pre>';
//        print_r($list);
//        echo '<pre>';
//        print_r($user);exit;
        $info=$member->insert($user);
        if($info){
            $id = $member->getLastInsID();
            $list['uid'] = $id;
            $info=$stu->insert($list);
            if($info){
                $user['homeid'] = $stu->getLastInsID();
                $member->where('Id='. $list['uid'])->update($user);
                if($member){
                    $this->success('添加成功','User/house_list');
                }else{
                    $this->error('添加失败','User/house_list');
                }
            }else{
                $this->error('添加失败','User/house_list');
            }
        }
        else{
            $this->error('添加失败','User/house_list');
        }
    }
    //房产删除
    public function house_delete(){
        $stu=db('house');
        $hid=input('id');
        $info=$stu->where('Id in ('.$hid.')')->delete();
        if($info){
            $this->success('删除成功','User/house_list');
        } else{
            $this->error('删除失败','User/house_list');
        }
    }
    //房产修改
    public function house_update(){
        $stu=db('house');
        $hid=input('Id');
        $list=$stu->where('Id='.$hid)->find();
        $es = db('estate');
        $list['zone'] = $es->where('Id='.$list['zoneid'])->find()['name'];
        unset($list['zoneid']);
        $this->assign('list',$list);
        $zonename = $es->select();
        $this->assign('zonename',$zonename);
        return $this->fetch();
    }
    public function house_do_update(){
        $stu=db('house');
        $hid=input('post.Id');
        $list=input('post.');
        $info=$stu->where('Id='.$hid)->update($list);
        if($info){
            $this->success('修改成功','User/house_list');
        } elseif($info===false){
            $this->error('修改失败','User/house_list');
        } else{
            $this->error('没有进行任何修改','User/house_list');
        }
    }
    //房产搜索
    public function house_search(){
        $stu=db('house');
        $sousuo=input('sousuo');
        $info=$stu->whereLike("zone","%".$sousuo."%")->paginate(config('5'));
        if($info->toArray()['data']){
            $this->assign('list',$info->toArray());
            $this->assign('pages',$info->render());
            return $this->fetch('house_list');
        }
        else{
            echo "
                <script>
                    alert('没有搜索到该条数据');location.href='house_list.html';
                </script>
            ";
        }
    }


    //公告列表
    public function announce_list(){
        $stu = db('announce');
        $list = $stu->order("Id desc")->paginate('5');
        $page = $list->render();
        $this->assign('pages',$page);
        $list = $list->toArray()['data'];
        $user = db('user');

        for($i=0;$i<count($list);$i++) {
            $list[$i]['author'] = $user->where('Id='.$list[$i]['uid'])->find()['username'];
            unset($list[$i]['uid']);
        }
        $this->assign('info',$list);
        return $this->fetch();
    }
    //发布公告
    public function announce_add(){
        $db = db('announce');
        $notice_list = $db->select();
        $this->assign('notice_list',$notice_list);
        $this->assign('author',session('username'));
        return $this->fetch();
    }
    public function announce_do_add(){
        $stu = db('announce');
        $db = db('user');
        $data = input();
        $file = request()->file('photo');
        if(!empty($file)){
            $filephoto = $file->move(config('upload_path'));
            $data['photo'] = 'http://www.guokaiyuan.cn/upload/'.$filephoto->getSaveName();
        }
        $data['pubtime'] = time();
        $data['uid'] = $db->where('username='.$data['author'])->find()['Id'] ;
        unset($data['author']);
        $info = $stu->insert($data);
        if($info){
            $this->success('添加成功','User/announce_list');
        }else{
            $this->error('添加失败','User/announce_list');
        }
        return $this->fetch();
    }
    //修改公告
    public function announce_update(){
        $stu = db('announce');
        $id = input('Id');
        $info = $stu->where('Id='.$id)->find();
        $this->assign('info',$info);
        return $this->fetch();
    }
    public function announce_do_update(){
        $stu = db('announce');
        $date = input();
        $list = $stu->where('Id='.$date['Id'])->find()['photo'];
        $list = explode('/',$list);
        $photo = 'upload'.'/'.$list[count($list)-2].'/'.$list[count($list)-1];
        if(!empty($photo)){
            unlink($photo);
        }
        $file = request()->file('photo');
        if(!empty($file)){
            $file = $file->move(config('upload_path'));
            $date['photo'] = 'http://www.guokaiyuan.cn/upload/'.$file->getSaveName();
        }
        $info = $stu->where('Id='.$date['Id'])->update($date);
        if($info){
            $this->success("修改成功",'User/announce_list');
        }elseif($info==="false"){
            $this->error("修改失败",'User/announce_list');
        }else{
            $this->success("未进行任何操作",'User/announce_list');
        }
    }
    //删除公告
    public function announce_delete(){
        $id = input('Id');
        $stu = db('announce');
        $list = $stu->where('Id='.$id)->find()['photo'];
        $list = explode('/',$list);
//        echo "<pre>";print_r($list);exit;
        $photo = 'upload'.'/'.$list[count($list)-2].'/'.$list[count($list)-1];
//        echo $photo;exit;
        if(!empty($photo)){
            unlink($photo);
        }
        $info = $stu->where('Id in ('.$id.')')->delete();
        if($info){
            $this->success('删除成功','User/announce_list');
        }else{
            $this->error('删除失败','User/announce_list');
        }
    }


    //用户组列表
    public function user_group_list(){
        $list = db('auth_group');
        $user_info = $list->order("Id desc")->paginate('5');
        $this->assign("list",$user_info->toArray());
        $page = $user_info->render();
        $this->assign("page",$page);
        return $this->fetch();
    }

    //用户组添加
    public function user_group_add(){
        return $this->fetch();
    }
    public function do_user_group_add(){
        $stu = db('auth_group');
        $data = input();
        $info = $stu->insert($data);
        if($info){
            $this->success('添加成功','User/user_group_list');
        }else{
            $this->error('添加失败','User/user_group_list');
        }
        return $this->fetch();
    }
    //用户组修改
    public function user_group_update(){
        $stu = db('auth_group');
        $id = input('id');
        $info = $stu->where('Id='.$id)->find();
        $this->assign('list',$info);
        return $this->fetch();
    }
    public function do_user_group_update(){
        $stu = db('auth_group');
        $date = input();
        $id = $date['id'];
        unset($date['id']);
        $info = $stu->where('Id='.$id)->update($date);
        if($info){
            $this->success("修改成功",'User/user_group_list');
        }elseif($info==="false"){
            $this->error("修改失败",'User/user_group_list');
        }else{
            $this->success("未进行任何操作",'User/user_group_list');
        }
    }
    //用户组删除
    public function user_group_delete(){
        $id = input('id');
        $stu = db('auth_group');
        $info = $stu->where('Id in ('.$id.')')->delete();
        if($info){
            $this->success('删除成功','User/user_group_list');
        }else{
            $this->error('删除失败','User/user_group_list');
        }
    }


    //用户规则列表
    public function rule_list(){
        $list = db('auth_rule');
        $user_info = $list->order("Id desc")->paginate('5');
        $this->assign("list",$user_info->toArray());
        $page = $user_info->render();
        $this->assign("page",$page);
        return $this->fetch();
    }
    //用户规则添加
    public function rule_add(){
        return $this->fetch();
    }
    public function do_rule_add(){
        $stu = db('auth_rule');
        $data = input();
        $info = $stu->insert($data);
        if($info){
            $this->success('添加成功','User/rule_list');
        }else{
            $this->error('添加失败','User/rule_list');
        }
        return $this->fetch();
    }
    //用户规则修改
    public function rule_update(){
        $stu = db('auth_rule');
        $id = input('id');
        $info = $stu->where('Id='.$id)->find();
        $this->assign('list',$info);
        return $this->fetch();
    }
    public function do_rule_update(){
        $stu = db('auth_rule');
        $date = input();
        $id = $date['id'];
        unset($date['id']);
        $info = $stu->where('Id='.$id)->update($date);
        if($info){
            $this->success("修改成功",'User/rule_list');
        }elseif($info==="false"){
            $this->error("修改失败",'User/rule_list');
        }else{
            $this->success("未进行任何操作",'User/rule_list');
        }
    }
    //用户规则删除
    public function rule_delete(){
        $id = input('id');
        $stu = db('auth_rule');
        $info = $stu->where('Id in('.$id.')')->delete();
        if($info){
            $this->success('删除成功','User/rule_list');
        }else{
            $this->error('删除失败','User/rule_list');
        }
    }

        //设置用户组权限
    public function set_rule(){
        $group_id=input('id');
        $db=db('auth_rule');
        $userruletable=db('auth_group');
        $rule_list=$db->where('parentid=0')->select();
        foreach($rule_list as &$v) {
            $v['second']=$db->where('parentid='.$v['Id'])->select();
            foreach($v['second'] as &$t) {
                $t['third']=$db->where('parentid='.$t['Id'])->select();
            }
        }
//        echo "<pre>";
//        print_r($rule_list);exit;
        $userrule=$userruletable->field('rules')->where('Id='.$group_id)->find();
        $this->assign('groupid',$group_id);
        $this->assign('userrule',$userrule['rules']);
        $this->assign('rulelist',$rule_list);
        return $this->fetch();
    }


    public function do_set_rule(){
        $data=input('post.');
        $db=db('auth_group');
        $rules=implode(',',$data['ruleid']);
        $adata=[
            'rules'=>$rules
        ];
        $info=$db->where('id='.$data['groupid'])->update($adata);
        if($info!==false) {
            $this->success('保存成功','User/user_group_list');
        }
    }


    //管理员列表
    public function admin_list(){
        $list = db('user');
        $user_info = $list->order("Id desc")->paginate('5');
        $page = $user_info->render();
        $this->assign("page",$page);
        $user_info = $user_info->toArray()['data'];
        $access = db('auth_group_access');
        $group = db('auth_group');
        for($i=0;$i<count($user_info);$i++) {
            $user_info[$i]['group'] = $access->where('uid='.$user_info[$i]['Id'])->find()['group_id'];
            $user_info[$i]['group'] = $group->where('Id='.$user_info[$i]['group'])->find()['title'];
        }
//        echo '<pre>';
//        print_r($user_info);exit;
        $this->assign("list",$user_info);
        return $this->fetch();
    }
    //管理员添加
    public function admin_add(){
        $group = db('auth_group');
        $title = $group->select();
        $this->assign('title',$title);
        return $this->fetch();
    }
    public function admin_do_add(){
        $user = db('user');
        $access = db('auth_group_access');
        $data = input();
//        echo "<pre>";
//        print_r($data);exit;
        $file = request()->file('photo');
        $filephoto = $file->move(config('upload_path'));
        $data['photo'] = 'http://www.guokaiyuan.cn/upload/'.$filephoto->getSaveName();
        $group['group_id'] = $data['group_id'];
        unset($data['group_id']);
        $info = $user->insert($data);
        if($info){
            $group['uid'] = $user->getLastInsID();
            $info = $access->insert($group);
            if($info){
                $this->success('添加成功','User/admin_list');
            }else{
                $this->error('添加失败','User/admin_list');
            }
        }else{
            $this->error('添加失败','User/admin_list');
        }
        return $this->fetch();
    }
    //管理员修改
    public function admin_update(){
        $stu = db('user');
        $access = db('auth_group_access');
        $id = input('id');
        $info = $stu->where('Id='.$id)->find();
        $info['group_id'] = $access->where('uid='.$info['Id'])->find()['group_id'];
        $this->assign('list',$info);
        $group = db('auth_group');
        $title = $group->select();
        $this->assign('title',$title);
        return $this->fetch();
    }
    public function admin_do_update(){
        $stu = db('user');
        $access = db('auth_group_access');
        $date = input();
        $list = $stu->where('Id='.$date['Id'])->find()['photo'];
        if(!empty($list)){
            $list = explode('/',$list);
            $photo = 'upload'.'/'.$list[count($list)-2].'/'.$list[count($list)-1];
            unlink($photo);
        }

        $file = request()->file('photo');
        if($file){
            $file = $file->move(config('upload_path'));
            $date['photo'] = 'http://www.guokaiyuan.cn/upload/'.$file->getSaveName();
        }
        $group['group_id'] = $date['group_id'];
        unset($date['group_id']);
        $id = $date['Id'];
        unset($date['Id']);
        $info = $stu->where('Id='.$id)->update($date);
//        echo $stu->getLastSql();exit;
        if($info==="false"){
            $this->error("修改失败",'User/rule_list');
        }else{
            $info = $access->where('uid='.$id)->update($group);
            if($info==="false"){
                $this->error("修改失败",'User/admin_list');
            }else{
                $this->success("修改成功",'User/admin_list');
            }
        }
    }
    //管理员删除
    public function admin_delete(){
        $id = input('id');
        $stu = db('user');
        $info = $stu->where('Id in('.$id.')')->delete();
        $list = $stu->where('Id='.$info['Id'])->find()['photo'];
        $list = explode('/',$list);
        $photo = 'upload'.'/'.$list[count($list)-2].'/'.$list[count($list)-1];
        if(!empty($photo)){
            unlink($photo);
        }
        if($info){
            $this->success('删除成功','User/admin_list');
        }else{
            $this->error('删除失败','User/admin_list');
        }
    }


    //banner图列表
    public function banner_image_list(){
        $db = db('banner_image');
        $info = $db->order("Id desc")->paginate('3');
        $page = $info->render();
        $this->assign("page",$page);
        $info = $info->toArray()['data'];
        $this->assign('info',$info);
        return $this->fetch();
    }
    //banner图添加
    public function banner_image_add(){
        return $this->fetch();
    }
    public function banner_image_do_add(){
        $db = db('banner_image');
        $data = input();
        $file = request()->file('photo');
        $filephoto = $file->move(config('upload_path'));
//        echo "<pre>";
//        print_r($filephoto->getSaveName());exit;
        $data['photo'] = 'http://www.guokaiyuan.cn/upload/'.$filephoto->getSaveName();

        $info = $db->insert($data);
        if($info){
            $this->success('添加成功','User/banner_image_list');
        }else{
            $this->error('添加失败','User/banner_image_list');
        }
        return $this->fetch();
    }
    //banner图修改
    public function banner_image_update(){
        $stu = db('banner_image');
        $id = input('Id');
        $info = $stu->where('Id='.$id)->find();
        $this->assign('info',$info);
        return $this->fetch();
    }
    public function banner_image_do_update(){
        $stu = db('banner_image');
        $date = input();
        $list = $stu->where('Id='.$date['Id'])->find()['photo'];
        if(!empty($list)){
            $list = explode('/',$list);
            $photo = 'upload'.'/'.$list[count($list)-2].'/'.$list[count($list)-1];
            unlink($photo);
        }
        $file = request()->file('photo');
        if($file){
            $file = $file->move(config('upload_path'));
            $date['photo'] = 'http://www.guokaiyuan.cn/upload/'.$file->getSaveName();
        }
        $info = $stu->where('Id='.$date['Id'])->update($date);
        if($info){
            $this->success("修改成功",'User/banner_image_list');
        }elseif($info==="false"){
            $this->error("修改失败",'User/banner_image_list');
        }else{
            $this->success("未进行任何操作",'User/banner_image_list');
        }
    }
    //banner图删除
    public function banner_image_delete(){
        $id = input('Id');
        $stu = db('banner_image');
        $info = $stu->where('Id in('.$id.')')->delete();
        $list = $stu->where('Id='.$info['Id'])->find()['photo'];
        $list = explode('/',$list);
        $photo = 'upload'.'/'.$list[count($list)-2].'/'.$list[count($list)-1];
        if(!empty($photo)){
            unlink($photo);
        }
        if($info){
            $this->success('删除成功','User/banner_image_list');
        }else{
            $this->error('删除失败','User/banner_image_list');
        }
    }

    //消息列表
    public function message_list(){
        $db = db('xiaoxi');
        $info = $db->order("Id desc")->paginate('5');
        $page = $info->render();
        $this->assign("page",$page);
        $info = $info->toArray()['data'];
        $this->assign('info',$info);
        return $this->fetch();
    }
    //消息添加
    public function message_add(){
        return $this->fetch();
    }
    public function message_do_add(){
        $db = db('xiaoxi');
        $data = input();
        $file = request()->file('photo');
        $filephoto = $file->move(config('upload_path'));
        $data['pubtime'] = time();
        $data['photo'] = 'http://www.guokaiyuan.cn/upload/'.$filephoto->getSaveName();
        $info = $db->insert($data);
        if($info){
            $this->success('添加成功','User/message_list');
        }else{
            $this->error('添加失败','User/message_list');
        }
        return $this->fetch();
    }
    //消息修改
    public function message_update(){
        $stu = db('xiaoxi');
        $id = input('Id');
        $info = $stu->where('Id='.$id)->find();
        $this->assign('info',$info);
        return $this->fetch();
    }
    public function message_do_update(){
        $stu = db('xiaoxi');
        $date = input();
        $list = $stu->where('Id='.$date['Id'])->find()['photo'];
        $list = explode('/',$list);
        $photo = 'upload'.'/'.$list[count($list)-2].'/'.$list[count($list)-1];
        if(!empty($photo)){
            unlink($photo);
        }
        $file = request()->file('photo');
        if($file){
            $file = $file->move(config('upload_path'));
            $date['photo'] = 'http://www.guokaiyuan.cn/upload/'.$file->getSaveName();
        }
        $date['pubtime'] = time();
        $info = $stu->where('Id='.$date['Id'])->update($date);
        if($info){
            $this->success("修改成功",'User/message_list');
        }elseif($info==="false"){
            $this->error("修改失败",'User/message_list');
        }else{
            $this->success("未进行任何操作",'User/message_list');
        }
    }
    //消息删除
    public function message_delete(){
        $id = input('id');
        $stu = db('xiaoxi');
        $info = $stu->where('Id in('.$id.')')->delete();
        $list = $stu->where('Id='.$info['Id'])->find()['photo'];
        $list = explode('/',$list);
        $photo = 'upload'.'/'.$list[count($list)-2].'/'.$list[count($list)-1];
        if(!empty($photo)){
            unlink($photo);
        }
        if($info){
            $this->success('删除成功','User/message_list');
        }else{
            $this->error('删除失败','User/message_list');
        }
    }

    //电话列表
    public function telephone_list(){
        $db = db('publicphone');
        $info = $db->order("Id desc")->paginate('5');
        $page = $info->render();
        $this->assign("page",$page);
        $info = $info->toArray()['data'];
        $this->assign('info',$info);
        return $this->fetch();
    }
    //电话添加
    public function telephone_add(){
        return $this->fetch();
    }
    public function telephone_do_add(){
        $db = db('publicphone');
        $data = input();
        $info = $db->insert($data);
        if($info){
            $this->success('添加成功','User/telephone_list');
        }else{
            $this->error('添加失败','User/telephone_list');
        }
        return $this->fetch();
    }
    //电话修改
    public function telephone_update(){
        $stu = db('publicphone');
        $id = input('Id');
        $info = $stu->where('Id='.$id)->find();
        $this->assign('info',$info);
        return $this->fetch();
    }
    public function telephone_do_update(){
        $stu = db('publicphone');
        $date = input();
        $info = $stu->where('Id='.$date['Id'])->update($date);
        if($info){
            $this->success("修改成功",'User/telephone_list');
        }elseif($info==="false"){
            $this->error("修改失败",'User/telephone_list');
        }else{
            $this->success("未进行任何操作",'User/telephone_list');
        }
    }
    //电话删除
    public function telephone_delete(){
        $id = input('id');
        $stu = db('publicphone');
        $info = $stu->where('Id in('.$id.')')->delete();
        if($info){
            $this->success('删除成功','User/telephone_list');
        }else{
            $this->error('删除失败','User/telephone_list');
        }
    }

    //检测英文名称是否存在
    public function check_name_en(){
        $name=input('post.names');
        $stu = db('auth_rule');
        $info = $stu->where("name='".$name."'")->find();
        if($info){
            $data = [
                'status' => 1,
                'msg' => "此英文名称已存在"
            ];
        }else{
            $data = [
                'status' => 0,
                'msg' => "此英文名称可用"
            ];
        }
        return json($data);
    }
    //检测中文名称是否存在
    public function check_name_zh(){
        $name=input('post.names');
        $stu = db('auth_rule');
        $info = $stu->where("title='".$name."'")->find();
        if($info){
            $data = [
                'status' => 1,
                'msg' => "此中文名称已存在"
            ];
        }else{
            $data = [
                'status' => 0,
                'msg' => "此中文名称可用"
            ];
        }
        return json($data);
    }

}