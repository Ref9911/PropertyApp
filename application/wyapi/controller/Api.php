<?php
namespace app\wyapi\controller;

use think\Controller;
include 'image.class.php'; 
class Api extends Controller {
    /*登录模块*/
    public function login(){
        $table=db('member');
        $username=input('post.username');
        $password=md5(input('post.password'));
        $data=$table->where("(telphone='$username' or nickname='$username' or truename='$username') and password='$password'")->find();
        if($data){
        	$housedata=$table->field('estate.address,estate.name,house.building,house.roomnumber,house.homecard')->where("truename='".$username."' or telphone='".$username."'  or nickname='".$username."'")->join('house','member.homeid=house.Id')->join('estate','house.zoneid=estate.Id')->find();
       		$house=$housedata['address'].$housedata['name'].$housedata['building']."号楼".$housedata['roomnumber'].'单元'.$housedata['homecard'];
            $info=[
                'info'=>'succ',
                'userid'=>$data['Id'],
                'nickname'=>$data['nickname'],
                'truename'=>$data['truename'],
                'telphone'=>$data['telphone'],
                'photo'=>$data['photo'],
                'tag'=>$data['tag'],
                'house'=>$house,
                'backphoto'=>$data['backphoto'],
            ];
            return json($info);
        }else{
            $info=[
                'info'=>'fail',
            ];
            return json($info);
        }
    }


    /*注册模块*/
    public function reg(){
        $table=db('member');
        $username=input('post.username');
        $telphone=input('post.telphone');

        // $username='testuser';
        // $telphone='13884822239';
        $userinfo=[
            'password'=>md5(input('post.password')),
            'shenhe'=>0,
        ];
        // $userinfo=[
        //     'password'=>md5('111'),
        // ];
        $data=$table->where("truename='".$username."' and telphone='".$telphone."' and shenhe is null")->find();
       	if($data){
       		$housedata=$table->field('estate.address,estate.name,house.building,house.roomnumber,house.homecard')->where("truename='".$username."' and telphone='".$telphone."' and shenhe is null")->join('house','member.homeid=house.Id')->join('estate','house.zoneid=estate.Id')->find();
       		$house=$housedata['address'].$housedata['name'].$housedata['building']."号楼".$housedata['roomnumber'].'单元'.$housedata['homecard'];
       		$data=$table->where("truename='".$username."' and telphone='".$telphone."' and shenhe is null")->update($userinfo);
       		if($data){
            $info=[
                'info'=>'succ',
                'userid'=>$table->getLastInsID(),
                'house'=>$house,
            ];
            return json($info);
	        }else{
	             $info=[
	                'info'=>'fail',
	            ];
	            return json($info);
	        }
       	}else{
       		$info=[
                'info'=>'noneuser',
            ];
            return json($info);
       	}
        
    }


    /*报修保事*/
    public function baoxiu(){
        $repairtable=db('repair');
        $data=input('post.');
        $data['mid']=$data['userid'];
        unset($data['userid']);
        $data['photo']=$data['uploadfile'];
        unset($data['uploadfile']);
        $data['starttime']=time();
        $data['status']=0;
        $info=$repairtable->insert($data);
        $id=$repairtable->getLastInsID();
        $time=date('Ymd',time());
        $data2['code']=$time.$id;
        $info2=$repairtable->where('Id='.$id)->update($data2);
        if($info&&$info2){
            return json('succ');
        }else{
            return json('fail');
        }
    }

	/*图片上传*/
    public function uploadimg(){
        $userid=input('userid');   //我的互动修改用户背景 flag
        $model=input('model');  //如果单纯的上传图片 不修改其他的 这个值没有
    	if($_FILES['uploadfile']['error']==0){
           
            $userid=input('userid');   //我的互动修改用户背景 flag
            $model=input('model');  //如果单纯的上传图片 不修改其他的 这个值没有
    		$file=request()->file("uploadfile");

    		$upload=$file->move(config('upload_path'));
    		 if($upload){
                $filepatharr=explode("/",$upload->getSavename());
                $filepath=$filepatharr[0];  //取出文件路径
                $filetypearr=explode(".",$filepatharr[1]);
                $filetype=$filetypearr[count($filetypearr)-1];  //取出文件类型
                if($filetype=='jpg'){
                    $filetype='jpeg';
                }
                $src='upload/'.$upload->getSavename();
                $time=md5(time());
                $image = new Image($src);
                $image->percent = 1;
                $image->openImage();
                $image->thumpImage('upload/'.$filepath.'/'.$time);
                unlink('upload/'.$upload->getSavename());
                $path='http://www.guokaiyuan.cn/upload/'.$filepath.'/'.$time.'.'.$filetype;

                if($userid&&$model=='hudongback'){   //我的互动修改背景
                    $table=db('member');
                    $data=[
                        'backphoto'=>$path,
                    ];
                    $info2=$table->where('Id='.$userid)->update($data);
                }

                if($userid&&$model=='userphoto'){   //修改用户头像
                    $table=db('member');
                    $data=[
                        'photo'=>$path,
                    ];
                    $info2=$table->where('Id='.$userid)->update($data);
                }
                return json($path);
               
            }
    	}
    }


    /*建议咨询*/
    public function jianyi(){
        $consulttable=db('consult');
        $data=input('post.');
        $data['mid']=$data['userid'];
        unset($data['userid']);
        $data['photo']=$data['uploadfile'];
        unset($data['uploadfile']);
        $data['pubtime']=time();
        $info=$consulttable->insert($data);
        if($info){
            return json('succ');
        }else{
            return json('fail');
        }
    }

    /*建议咨询列表*/
	public function zixunlist(){
        $consulttable=db('consult');
        $userid=input('post.userid');
        $page=input('post.page');
        $info=$consulttable->where('mid='.$userid)->page($page,7)->order('id desc')->select();
        $info[0]['info']='succ';
        return json($info);
	}

    /*投诉表扬*/
    public function tousu(){
        $complaintable=db('complain');
        $data=input('post.');
        $data['mid']=$data['userid'];
        unset($data['userid']);
        $data['photo']=$data['uploadfile'];
        unset($data['uploadfile']);
        $data['pubtime']=time();
        $info=$complaintable->insert($data);
        if($info){
            return json('succ');
        }else{
            return json('fail');
        }
    }

    /*投诉表扬列表*/
	public function tousulist(){
        $complaintable=db('complain');
        $userid=input('post.userid');
        $page=input('post.page');
        $info=$complaintable->page($page,7)->where('mid='.$userid)->order('id desc')->select();
        $info[0]['info']='succ';
        return json($info);
	}

    /*修改头像*/
    public function upphoto(){
    	$table=db('member');
    	$userid=input('post.userid');
    	$data['photo']=input('post.uploadpath');
    	$info=$table->where('Id='.$userid)->update($data);
    	$housedata=$table->field('estate.address,estate.name,house.building,house.roomnumber,house.homecard')->where("member.id=".$userid)->join('house','member.homeid=house.Id')->join('estate','house.zoneid=estate.Id')->find();
       	$house=$housedata['address'].$housedata['name'].$housedata['building']."号楼".$housedata['roomnumber'].'单元'.$housedata['homecard'];
    	$info2=$table->where('id='.$userid)->find();
    	$info2['house']=$house;
        unset($info2['shenhe']);
        unset($info2['identiey']);
        unset($info2['address']);
        unset($info2['birth']);
    	if($info){
    		$info2['info']='succ';
    		return json($info2);
    	}else{
    		$info2['info']='fali';
    		return json($info2);
    	}
    }

    /*修改我的标签*/
    public function uptag(){
    	$table=db('member');
    	$userid=input('post.userid');
    	$data['tag']=input('post.tag');
    	$housedata=$table->field('estate.address,estate.name,house.building,house.roomnumber,house.homecard')->where("member.id=".$userid)->join('house','member.homeid=house.Id')->join('estate','house.zoneid=estate.Id')->find();
       	$house=$housedata['address'].$housedata['name'].$housedata['building']."号楼".$housedata['roomnumber'].'单元'.$housedata['homecard'];
    	$info=$table->where('Id='.$userid)->update($data);
    	$info2=$table->where('Id='.$userid)->find();
        unset($info2['shenhe']);
        unset($info2['identiey']);
        unset($info2['address']);
        unset($info2['birth']);
    	if($info){
    		$info2['info']='succ';
    		$info2['house']=$house;
    		return json($info2);
    	}elseif($info===false){
    		$info2['info']='fali';
    		return json($info2);
    	}else{
    		$info2['info']='succ';
    		return json($info2);
    	}
    }

    /*修改昵称*/
    public function upnickname(){
        $data=input('post.');
        $usertable=db('member');
        $data['Id']=$data['userid'];
        unset($data['userid']);
        $data['nickname']=$data['upname'];
        unset($data['upname']);
        $info=$usertable->update($data);
        $housedata=$usertable->field('estate.address,estate.name,house.building,house.roomnumber,house.homecard')->where("member.id=".$data['Id'])->join('house','member.homeid=house.Id')->join('estate','house.zoneid=estate.Id')->find();
       	$house=$housedata['address'].$housedata['name'].$housedata['building']."号楼".$housedata['roomnumber'].'单元'.$housedata['homecard'];
        $info2=$usertable->where('id='.$data['Id'])->find();
        unset($info2['shenhe']);
        unset($info2['identiey']);
        unset($info2['address']);
        unset($info2['birth']);
        $info3=[];
        if($info){
        	$info2['info']='succ';
        	$info2['house']=$house;
            return json($info2);
        }elseif($info===false){
        	$info3['info']='fail';
            return json($info3);
        }else{
            $info3['info']='none up';
            return json($info3);
        }
    }

    /*修改真实名字*/
    public function uptruename(){
        $data=input('post.');
        $usertable=db('member');
        $data['Id']=$data['userid'];
        unset($data['userid']);
        $data['truename']=$data['upname'];
        unset($data['upname']);
        $info=$usertable->update($data);
        $housedata=$usertable->field('estate.address,estate.name,house.building,house.roomnumber,house.homecard')->where("member.id=".$data['Id'])->join('house','member.homeid=house.Id')->join('estate','house.zoneid=estate.Id')->find();
       	$house=$housedata['address'].$housedata['name'].$housedata['building']."号楼".$housedata['roomnumber'].'单元'.$housedata['homecard'];
        $info2=$usertable->where('id='.$data['Id'])->find();
        unset($info2['shenhe']);
        unset($info2['identiey']);
        unset($info2['address']);
        unset($info2['birth']);

        if($info){
        	$info2['info']='succ';
        	$info2['house']=$house;
            return json($info2);
        }elseif($info===false){
        	$info3['info']='fail';
            return json($info3);
        }else{
            $info3['info']='none up';
            return json($info3);
        }
    }


    /*修改性别*/
    public function upsex(){
        $data=input('post.');
        $usertable=db('member');
        $data['Id']=$data['userid'];
        unset($data['userid']);
        $info=$usertable->update($data);
        $housedata=$usertable->field('estate.address,estate.name,house.building,house.roomnumber,house.homecard')->where("member.id=".$data['Id'])->join('house','member.homeid=house.Id')->join('estate','house.zoneid=estate.Id')->find();
       	$house=$housedata['address'].$housedata['name'].$housedata['building']."号楼".$housedata['roomnumber'].'单元'.$housedata['homecard'];
        $info2=$usertable->where('id='.$data['Id'])->find();
        unset($info2['shenhe']);
        unset($info2['identiey']);
        unset($info2['address']);
        unset($info2['birth']);
        if($info){
        	$info2['info']='succ';
        	$info2['house']=$house;
            return json($info2);
        }elseif($info===false){
        	$info3['info']='fail';
            return json($info3);
        }else{
            $info3['info']='none up';
            return json($info3);
        }
    }


     /*修改出生日期*/
    public function upbirth(){
        $data=input('post.');
        $usertable=db('member');
        $data['Id']=$data['userid'];
        unset($data['userid']);
        $info=$usertable->update($data);
        $housedata=$usertable->field('estate.address,estate.name,house.building,house.roomnumber,house.homecard')->where("member.id=".$data['Id'])->join('house','member.homeid=house.Id')->join('estate','house.zoneid=estate.Id')->find();
       	$house=$housedata['address'].$housedata['name'].$housedata['building']."号楼".$housedata['roomnumber'].'单元'.$housedata['homecard'];
        $info2=$usertable->where('id='.$data['Id'])->find();

        unset($info2['shenhe']);
        unset($info2['identiey']);
        unset($info2['address']);
        unset($info2['birth']);
        if($info){
        	$info2['info']='succ';
        	$info2['house']=$house;
            return json($info2);
        }elseif($info===false){
        	$info3['info']='fail';
            return json($info3);
        }else{
            $info3['info']='none up';
            return json($info3);
        }
    }



    /*修改手机号*/
    public function uptelphone(){
        $data=input('post.');
        $usertable=db('member');
        $oldtel=$usertable->where('Id='.$data['userid'].' and telphone='.$data['oldtelphone'])->find();
        if($oldtel){
            unset($data['oldtelphone']);
            $data['Id']=$data['userid'];
            unset($data['userid']);
            $data['telphone']=$data['newtelphone'];
            unset($data['newtelphone']);
                $info=$usertable->update($data);
            if($info){
                $newdata=[
                    'info'=>'succ',
                    'telphone'=>$data['telphone'],
                ];
                return json($newdata);
            }else{
                $newdata=[
                    'info'=>'fail',
                ];
                return json($newdata);
            }
        }else{
            $newdata=[
                'info'=>'tel not exist',
            ];
            return json($newdata);
        }
    }

    /*修改登录密码*/
    public function uplodpass(){
        $data=input('post.');
        $table=db('member');
        $userid=$data['userid'];
        // $userid=1;
        // $lodpass=md5(111);
        $lodpass=md5($data['lodpass']);
        $newpass=md5($data['newpass']);
        $lodverify=$table->where("id='".$userid."' and password='".$lodpass."'")->find();
        // if($lodverify){
        //     return json('存在');
        // }else{
        //      return json('bu 存在');
        // }
        
        if($lodverify){						//原密码是否存在
        	$data['Id']=$userid;
        	$data['password']=$newpass;
        	unset($data['userid']);
        	unset($data['newpass']);
        	unset($data['lodpass']);
        	$info=$table->update($data);
        	if($info!==false){
        		$info2=$table->where('id='.$userid)->find();
        		$housedata=$table->field('estate.address,estate.name,house.building,house.roomnumber,house.homecard')->where("member.id=".$userid)->join('house','member.homeid=house.Id')->join('estate','house.zoneid=estate.Id')->find();
       			$house=$housedata['address'].$housedata['name'].$housedata['building']."号楼".$housedata['roomnumber'].'单元'.$housedata['homecard'];
        		$info2['info']='succ';
        		$info2['house']=$house;
		        unset($info2['shenhe']);
		        unset($info2['identiey']);
		        unset($info2['address']);
		        unset($info2['birth']);
		        return json($info2);
        	}elseif($info===false){
        		$info4['info']='fail';
        		return json($info4);
        	}
        }else{
        	$info3['info']='bucunzai';
        	return json($info3);
        }

    }

    /*物业缴费*/
    public function jiaofei(){
        $data=input('post.');
        $table=db('expense');
        $data['mid']=$data['userid'];
        unset($data['userid']);
        $data['pid']=$data['type'];
        unset($data['type']);
        $data['pubtime']=time();
        $info=$table->insert($data);
        if($info){
            return json('succ');
        }else{
            return json('fail');
        }
    }


    /*添加我要互动*/
    public function addhudong(){
        $data=input('post.');
        $hudongtable=db('hudong');
        // $data['userid']=1;
        $page=1;
        $data['mid']=$data['userid'];
        unset($data['userid']);
        $data['pubtime']=time(); 
        $info=$hudongtable->insert($data);
        if($info){
        	$data=$hudongtable->field('hudong.photo,member.Id as userid,member.nickname,hudong.content,hudong.photo,hudong.pubtime,member.photo as userphoto,hudong.zan,hudong.Id,hudong.pinglun,hudong.zanuserid,hudong.looknum')->page($page,7)->join('member','hudong.mid=member.id','left')->order('id desc')->select(); 
	        $photo=[];
	        $p=0;
	        for($i=0;$i<count($data);$i++){
	        	if($data[$i]['photo']){
	        		$photo[$p]=explode(',', $data[$i]['photo']);
	        		$p++;
	        	} 
	        	$data[$i]['pubtime']=(int)$data[$i]['pubtime'];
	        }
	        $p=0;
	        for($i=0;$i<count($data);$i++){
	        	if($data[$i]['photo']){
	        		$data[$i]['photo']=$photo[$p];
	        		$p++;
	        	}
	        }
	        $data[0]['info']='succ';
            return json($data); 
        }else{
        	$data[0]['info']='fail';
            return json($data);
        }
    }

    /*我要互动列表*/
    public function hudonglist(){
        $page=input('post.page');
        $hudongtable=db('hudong');
        $membertable=db('member');
        $data=$hudongtable->field('hudong.photo,member.Id as userid,member.nickname,hudong.content,hudong.photo,hudong.pubtime,member.photo as userphoto,hudong.zan,hudong.Id,hudong.pinglun,hudong.zanuserid,hudong.looknum')->page($page,7)->join('member','hudong.mid=member.id','left')->order('id desc')->select(); 
        $photo=[];
        $p=0;
        for($i=0;$i<count($data);$i++){
        	if($data[$i]['photo']){
        		$photo[$p]=explode(',', $data[$i]['photo']);
        		$p++;
        	}
        	$data[$i]['pubtime']=(int)$data[$i]['pubtime'];
        }
        $p=0;
        for($i=0;$i<count($data);$i++){
        	if($data[$i]['photo']){
        		$data[$i]['photo']=$photo[$p];
        		$p++;
        	}
        }
       	return json($data);
    }

    /*删除互动*/
    public function delhudong(){

        $hudongtable=db('hudong');
        $id=input('post.hid');
        $info=$hudongtable->where('id='.$id)->delete();
        if($info){
        	$membertable=db('member');
	        $data=$hudongtable->field('hudong.photo,member.Id as userid,member.nickname,hudong.content,hudong.photo,hudong.pubtime,member.photo as userphoto,hudong.zan,hudong.Id,hudong.pinglun,hudong.zanuserid,hudong.looknum')->page(1,7)->join('member','hudong.mid=member.id','left')->order('id desc')->select(); 
	        $photo=[];
	        $p=0;
	        for($i=0;$i<count($data);$i++){
	        	if($data[$i]['photo']){
	        		$photo[$p]=explode(',', $data[$i]['photo']);
	        		$p++;
	        	}
	        	$data[$i]['pubtime']=(int)$data[$i]['pubtime'];
	        }
	        $p=0;
	        for($i=0;$i<count($data);$i++){
	        	if($data[$i]['photo']){
	        		$data[$i]['photo']=$photo[$p];
	        		$p++;
	        	}
	        }
	        return json($data);
        }else{
        	return json('fail');
        }
    }


	/*添加我要互动评论*/
    public function addhudongpinglun(){
        $data=input('post.');
        $table=db('hudong_pinglun');
        $data['mid']=$data['userid'];
        unset($data['userid']);
        $data['pubtime']=time(); 
        $info=$table->insert($data);
        if($info){
        	$hudongtable=db('hudong');
        	$info2=$hudongtable->where('Id='.$data['hid'])->setInc('pinglun');
        	$info3=$table->field('hudong_pinglun.content,hudong_pinglun.pubtime,member.nickname,member.photo')->join('member','member.id=hudong_pinglun.mid')->where('hid='.$data['hid'])->order('hudong_pinglun.Id desc')->select();
        	if($info2){
        		return json($info3); 
        	}
        }else{
        	return json('null');
        }
    }

    /*我要互动评论列表*/
    public function hudongpinglun_list(){
        $data=input('post.');
        $hid=input('post.hid');
        $hudongtable=db('hudong_pinglun');
        $info=$hudongtable->field('hudong_pinglun.content,hudong_pinglun.pubtime,member.nickname,member.photo,member.Id as userid,hudong.zan,hudong.zanuserid')->where('hid='.$hid)->join('member','member.id=hudong_pinglun.mid')->join('hudong','hudong_pinglun.hid=hudong.id')->order('hudong_pinglun.Id desc')->select();
        return json($info); 
    }




    /*添加消息*/
    public  function addxiaoxi(){
    	$data=input('post.');
    	$data['pubtime']=time();
    	$table=db('xiaoxi');
    	$info=$table->insert($data);
    	if($info){
    		return json('succ');
    	}else{
    		return json('fail');
    	}
    }

    /*消息列表*/
    public  function xiaoxilist(){
    	$data=input('post.');
    	$table=db('xiaoxi');
    	$page=input('post.page');
    	$model=input('post.model');
 
    	if($model=='indexbanner'){
    		$info=$table->group('id desc')->where('photo is not null')->limit(0,1)->select();
		    	for($i=0;$i<count($info);$i++){
		    		$info[$i]['pubtime']=date('Y-m-d',$info[$i]['pubtime']);
		    	}
		    	return json($info);
    	}
    	if($model=='index'){
    			$info=$table->group('id desc')->where('photo is not null')->limit(1,5)->select();
		    	for($i=0;$i<count($info);$i++){
		    		$info[$i]['pubtime']=date('Y-m-d',$info[$i]['pubtime']);
		    	}
		    	return json($info);
   		 }
		    	$info=$table->page($page,10)->group('id desc')->select();
		    	for($i=0;$i<count($info);$i++){
		    		$info[$i]['pubtime']=date('Y-m-d',$info[$i]['pubtime']);
		    	}
		 
		 return json($info);
    }    	

    /*添加公告*/
    public function gonggaoadd(){
    	$table=db('announce');
    	$data=input('post.');
    	$data['pubtime']=time();
    	$info=$table->insert($data);
    	if($info){
    		return json('succ');
    	}else{
    		return json('fail');
    	}
    }

    /*公告列表*/
    public function gonggaolist(){
    	$table=db('announce');
    	$page=input('post.page');
    	$info=$table->page($page,12)->order('id desc')->select();
    	return json($info); 
	}

    /*智慧管家条数*/
     public function categoryNum(){
        $baoxiutable=db('repair');
        $jianyitable=db('consult');
        $tousutable=db('complain');
        $jiaofeitable=db('expense');
        $info1=$baoxiutable->field('count(*)')->select();
        $info2=$jianyitable->field('count(*)')->select();
        $info3=$tousutable->field('count(*)')->select();
        $info4=$jiaofeitable->field('count(*)')->where('status=1')->select();
        $alldata=[
            'baoxiu'=>$info1[0]['count(*)'],
            'jianyi'=>$info2[0]['count(*)'],
            'tousu'=>$info3[0]['count(*)'],
            'jiaofei'=>$info4[0]['count(*)'],
        ];

        return json($alldata); 
    }
	/*个人信息*/
	public function userinfo(){
		$table=db('member');
    	$userid=input('post.userid');
    	$info=$table->where('id='.$userid)->find();
    	return json($info); 
	}

	/*添加给我们建议*/
	public function addjianyi(){
		$table=db('suggest');
    	$data=input('post.');
    	$data['mid']=$data['userid'];
    	unset($data['userid']);

    	$info=$table->insert($data);
    	if($info){
    		return json('succ'); 
    	}else{
    		return json('fail'); 
    	}
	}

	/*给我们建议列表*/
	public function jianyilist(){
		$table=db('suggest');
    	$page=input('post.page');
    	$info=$table->page($page,10)->order('id desc')->select();
    	return json($info); 
	}


	/*添加红黑榜*/
	public function addhongheibang(){
		$table=db('hongheibang');
    	$data=input('post.');
    	$data['mid']=$data['userid'];
    	unset($data['userid']);
    	$data['pubtime']=time();
    	$info=$table->insert($data);
    	if($info){
    		return json('succ'); 
    	}else{
    		return json('fail'); 
    	}
	}


	/*红黑榜列表*/
	public function hongheibanglist(){
		$table=db('hongheibang');
    	$page=input('post.page');
    	$info=$table->page($page,7)->order('id desc')->select();
    	return json($info); 
	}

	/*增加赞*/
	public function addzan(){
		$data=input('post.');
		$table=db('hudong');
		if($data['model']=='hongheibang'){
			$table=db('hongheibang');
		}
		if($data['model']=='hudong'){
			$table=db('hudong');
		}
		unset($data['model']);
    	$exist=$table->where('Id='.$data['Id'])->find();
    	if(strpos($exist['zanuserid'],$data['userid'].',')===false){
    		$info=$table->where('Id='.$data['Id'])->setInc('zan');
    		$info2=$table->execute("update hudong set zanuserid=concat(zanuserid,'".$data['userid'].",') where id=".$data['Id']);
	    	if($info&&$info2){

	    		return json('succ'); 
	    	}else{
	    		return json('fail'); 
	    	}
    	}else{
    		return json('exist'); 
    	}

    	
	}

	/*取消赞*/
	public function delzan(){
		$data=input('post.');
		if($data['model']=='hongheibang'){
			$table=db('hongheibang');
		}
		unset($data['model']);
    	
    	$info=$table->where('Id='.$data['Id'])->setDec('zan');
    	if($info){
    		return json('succ'); 
    	}else{
    		return json('fail'); 
    	}
	}

	/*当前小区地址*/
	public function curraddress(){
		$table=db('estate');
		$long=input('post.long');
		$lat=input('post.lat');
		$info=$table->where('(longitude-'.$long.'>=-0.0010 and longitude-'.$long.'<=0.0010) or (latitude-'.$lat.'>=-0.0010 and latitude-'.$lat.'<=0.0010)')->find();
		if($info){
			$info['info']='succ';
			return json($info);
		}else{
			$info2=[
				'info'=>'fail',
			];
			return json($info2);
		}
	}

	/*物业缴费*/
	public function pay(){
		$table=db('expense');
		$userid=input('post.userid');
		$status=input('post.status');
		$number=input('post.mum');
		if(empty($number)&&!isset($number)){
			$info=$table->field('expense.id,expense.money,project.title,expense.pubtime,expense.endtime')->join('project','project.Id=expense.pid')->where('mid='.$userid.' and status='.$status)->select();
		}else{
			$info=$table->field('expense.id,expense.money,project.title,expense.pubtime,expense.endtime')->join('project','project.Id=expense.pid')->where('mid!='.$userid.' and status='.$status)->select();
		}
		if($info){
			return json($info);
		}else{
			return json('null');
		}
	}
	

	/*选择地址*/
	public function alladdress(){
		$table=db('estate');
		$info=$table->field('id,address,name')->select();
		return json($info);

	}
	
	/*我的互动*/
	public function myhudong(){
		$hudongtable=db('hudong');
		$userid=input('post.userid');
		$page=input('post.page');
		$data=$hudongtable->field('hudong.photo,member.Id as userid,member.nickname,hudong.content,hudong.photo,hudong.pubtime,member.photo as userphoto,hudong.zan,hudong.Id,hudong.pinglun,hudong.zanuserid,hudong.looknum')->page($page,7)->join('member','hudong.mid=member.id','left')->where('mid='.$userid)->order('id desc')->select(); 
        $photo=[];
        $p=0;
        for($i=0;$i<count($data);$i++){
        	if($data[$i]['photo']){
        		$photo[$p]=explode(',', $data[$i]['photo']);
        		$p++;
        	}
        	$data[$i]['pubtime']=(int)$data[$i]['pubtime'];
        }
        $p=0;
        for($i=0;$i<count($data);$i++){
        	if($data[$i]['photo']){
        		$data[$i]['photo']=$photo[$p];
        		$p++;
        	}
        }
		return json($data);
	}

	/*我的服务订单*/
	public function myfuwu(){
		$table=db('repair');
		$userid=input('post.userid');
		$page=input('post.page');
			$info=$table->where('mid='.$userid)->page($page,7)->order('id desc')->select();
            if($info){
                $info[0]['info']='succ';
            }

		return json($info);
	}

    /*首页轮播图*/
    public function indexBanner(){
        $table=db('banner_image');

        $info=$table->order('Id desc')->limit(0,3)->select();

        return json($info);
    }

    /*增加浏览次数*/
    public function addlook(){
        $data=input('post.');
        $table=db('hudong');
        $info=$table->where('Id='.$data['Id'])->setInc('looknum');
        $info2=$table->where('Id='.$data['Id'])->find();
        if($info&&$info2){
            $info2['info']='succ';
            return json($info2);
        }else{
            $info2['info']='fail';
            return json($info2); 
        }
    }

    /*我的信息*/
    public function myinfo(){
        $table=db('member');
        $userid=input('post.userid');
        $housedata=$table->field('estate.address,estate.name,house.building,house.roomnumber,house.homecard')->where("member.Id=".$userid)->join('house','member.homeid=house.Id')->join('estate','house.zoneid=estate.Id')->find();
            $house=$housedata['address'].$housedata['name'].$housedata['building']."号楼".$housedata['roomnumber'].'单元'.$housedata['homecard'];

        $info=$table->where('Id='.$userid)->find();
        $info['house']=$house;
        return json($info);
    }

    /*常用电话*/
    public function publicPhone(){
    	$table=db('publicphone');
    	$info=$table->order('id desc')->select();
    	return json($info);
    }



	public function arr_sort($array,$key,$order="desc"){ //asc是升序 desc是降序
		$arr_nums=$arr=array();
		foreach($array as $k=>$v){
			$arr_nums[$k]=$v[$key];
		}
		if($order=='asc'){
			asort($arr_nums);
		}else{
			arsort($arr_nums);
		}
		foreach($arr_nums as $k=>$v){
			$arr[$k]=$array[$k];
		}
		return $arr;
	}
    /*推送消息*/
	public function tuisonglist(){
		$xiaoxitable=db('xiaoxi');
		$repairtable=db('repair');
		$userid=input('post.userid');
		$info=$xiaoxitable->query("(select id,title,content,author,pubtime from xiaoxi where(mid='".$userid."')) union(select id,mid,code as content,name,endtime as pubtime from repair where(mid='".$userid."' and status =1)) order by pubtime desc");

		for($i=0;$i<count($info);$i++){
			if($info[$i]['author']=='居家维修'||$info[$i]['author']=='园区维修'||$info[$i]['author']=='园区保洁'||$info[$i]['author']=='园区绿化'||$info[$i]['author']=='园区安保'){
				$info[$i]['title']='工单更新通知';
				$info[$i]['content']="您的工单编号 ".$info[$i]['content']." 已处理";
			}
		}
		// $info=$xiaoxitable->where("mid='".$userid."'")->order('pubtime desc')->select();
		// $info2=$repairtable->where("mid='".$userid."' and status=1")->order('endtime desc')->select();
		// $info3=array_merge($info2,$info);
		// for($i=0;$i<count($info3);$i++){
		// 	if(isset($info3[$i]['name'])){
		// 		$info3[$i]['author']=$info3[$i]['name'];
		// 		unset($info3[$i]['name']);
		// 	}
		// 	if(isset($info3[$i]['intro'])){
		// 		$info3[$i]['content']=$info3[$i]['intro'];
		// 		unset($info3[$i]['intro']);
		// 	}
		// 	if(isset($info3[$i]['endtime'])){
		// 		$info3[$i]['pubtime']=$info3[$i]['endtime'];
		// 		unset($info3[$i]['endtime']);
		// 	}
		// }

		//$info3=$this->arr_sort($info3,'pubtime');
		// echo '<pre/>';
		// print_r($info);
		return json($info);
	}

	/*首页热门话题*/
	public function hottalk(){
		$table=db('hudong');
		$info=$table->where("hudong.photo!=''")->field('hudong.photo,member.Id as userid,member.nickname,hudong.content,hudong.photo,hudong.pubtime,member.photo as userphoto,hudong.zan,hudong.Id,hudong.pinglun,hudong.zanuserid,hudong.looknum')->join('member','hudong.mid=member.id','left')->order('hudong.pinglun desc')->limit(0,4)->select();
        for($i=0;$i<count($info);$i++){
        	$info[$i]['photo']=explode(',', $info[$i]['photo']);
        }
		return json($info);
	}
}


?>