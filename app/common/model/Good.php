<?php

namespace app\common\model;

class Good extends BaseModel
{
    public static function goodList($cid, $page, $limit, $order)
    {
        $start = --$page * $limit;
        return self::field('id, name, price, image,policy,sale')->where('status', 'normal')->where('good_category_id',
            $cid)->order($order)->limit($start, $limit)->select();
    }
}