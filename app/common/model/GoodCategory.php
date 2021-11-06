<?php

namespace app\common\model;

class GoodCategory extends BaseModel
{
    /**
     * 获取所有下级
     */
    public static function allChildren($id = [], $arr = [])
    {
        $ids = self::where('pid', 'in', $id)->column('id');
        if (!empty($ids)) {
            $arr = array_merge($arr, $ids);
            return self::allChildren($ids, $arr);
        } else {
            return $arr;
        }
    }

    /**
     * 获取二级分类
     */
    public static function secondData()
    {
        return self::field('a.*')->alias('a')->join('good_category gc', 'a.pid=gc.id')->where('a.status', 'normal')->where('gc.pid',
            0)->order('a.weigh', 'desc')->select();
    }

    /**
     * @param $pid 父ID
     */
    public static function goodData($pid)
    {
        return self::where('type', 0)->where('status', 'normal')->where('pid', $pid)->order('weigh', 'desc')->select();
    }

    /**
     * 直播分类
     * @param $pid
     */
    public static function liveData($pid)
    {
        return self::where('type', 1)->where('status', 'normal')->where('pid', $pid)->order('weigh', 'desc')->select();
    }
}