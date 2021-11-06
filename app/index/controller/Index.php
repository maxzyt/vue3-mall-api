<?php

namespace app\index\controller;

use app\common\controller\Frontend;

class Index extends Frontend
{
    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';

    public function index()
    {
//        if (ob_get_level() == 0) ob_start();
//
//        for ($i = 0; $i<10; $i++){
//
//            echo "<br> Line to show.";
//            echo str_pad('',4096)."\n";
//
//            ob_flush();
//            flush();
//            sleep(2);
//        }
//
//        echo "Done.";
//
//        ob_end_flush();
        return $this->view->fetch();
    }

    public function news()
    {
        $newslist = [];

        return jsonp(['newslist' => $newslist, 'new' => count($newslist), 'url' => 'https://www.iuok.cn?ref=news']);
    }
}
