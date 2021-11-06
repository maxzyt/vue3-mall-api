<?php
namespace app\common\model;
use think\facade\Db;
class UserHistory extends BaseModel{
    protected $autoWriteTimestamp = true;
    protected $createTime = 'createtime';
    protected $updateTime = '';
    protected $append=['createtime_text'];
    public static function delHistory($uid,$id,$goodId)
    {
        $row=self::where('id',$id)->where('user_id',$uid)->find();
        if(empty($row)){
            return ['code'=>0,'msg'=>'参数错误'];
        }
        $arr=explode(',',$row->good_id);
        $rs=false;
        if(in_array($goodId,$arr)){
            $arr=array_diff($arr,[$goodId]);
            if(empty($arr)){
                $rs=$row->delete();
            }else{
                $row->good_id=implode(',',$arr);
                $rs=$row->save();
            }
        }
        return $rs?['code'=>1,'msg'=>'删除成功']:['code'=>0,'msg'=>'删除失败'];
    }
    public function getCreatetimeTextAttr($value,$data)
    {
        $value=is_numeric($value)?$value:(isset($data['createtime'])?$data['createtime']:'');
        return is_numeric($value)?date("Y-m-d",$value):'';
    }
    public static function addHistory($uid,$goodId)
    {
        $row=self::where('user_id',$uid)->whereDay('createtime')->find();
        $rs=false;
        if($row){
            $arr=explode(',',$row->good_id);
            if(!in_array($goodId,$arr)){
                array_unshift($arr,$goodId);
                $row->good_id=implode(',',$arr);
                $rs=$row->save();
            }
        }else{
            self::create([
                'user_id'=>$uid,
                'good_id'=>$goodId
            ]);
            $rs=true;
        }
        return $rs;
    }
    public static function getHistory($uid,$page,$limit)
    {
        $start = --$page*$limit;
        $data=self::where('user_id', $uid)->order('id desc')->limit($start, $limit)->select()->toArray();
        if(empty($data)){
            return [];
        }
        foreach ($data as &$row){
            $temp=explode(',',$row['good_id']);//var_dump($temp);
            //$row['goodData']=Good::field('id,name,price,image,policy')->where('id','in',$temp)->order('field(id,'.$row['good_id'].')')->select()->toArray();
            $row['goodData']=Db::query("select id,name,price,image,policy from fa_good where id IN(".$row['good_id'].") order by field(id,".$row['good_id'].")");
        }
        return $data;
    }
    public function user()
    {
        return $this->belongsTo('User', 'user_id', 'id')->joinType('LEFT');
    }


    public function good()
    {
        return $this->belongsTo('Good', 'good_id', 'id')->joinType('LEFT');
    }
}
