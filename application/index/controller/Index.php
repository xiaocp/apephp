<?php

namespace app\index\controller;

use ape\Config;
use ape\Controller;
use ape\Cookie;
use ape\Db;
use ape\Request;
use ape\Session;
use app\index\model\Model;
use model\M;
use t\model\T;

class Index extends Controller {

    public function index(Request $request){
//        exit_dump($request->get());
//        exit_dump(get_included_files());
//        exit_dump(Config::get('ape'));
        $m = new Model();
        $mm = new M();
        $t = new T();
//        @$page=$_GET['page']?intval($_GET['page']):1;
//        echo $page;
//        exit_dump(Config::get());
//        echo "Index-index";
//        exit_dump($this->_controller);
//        $this->view->setCache(10);

//        $str = "include";
//        if($index = strpos($str, '@')){
//            // 获取模块名称
//            $module = strstr($str, '@', true);
//            $file = substr($str, $index + 1) . Config::get('view_suffix');
//            $path = APP_PATH . $module . '/view/' . $file;
//            require $path;
//        }else{
//            $file = $str . '.html';
//            $path = APP_PATH . 'index/view/' . $file;
//            require $path;
//        }
//        exit;

        $this->view->assign('g', '1');
//        $this->display('index');

        view()->assign('g', '1');
        view()->view('index');
    }

    public function lock(){
        $s1 = microtime(true);
        \ape\Lock::run('a', function (){
            sleep(5);
            echo "ggg";
        }, function (){
            echo "false";
        });
        $s2 = microtime(true);
        echo "time:".($s2 - $s1);
        exit;
    }

    public function check(){
//        $r = \ape\Lock::checkLock('a');
//        echo $r;
//        Session::clear();
//        $r = Cookie::delete('ape');
//        Cookie::clear();
//        exit_dump($_COOKIE);
//        $r = Session::get("apes");
//        $r = Cookie::get('ape');
//        $this->view->setCache(10)->view('index');
//        cache();
//        dump(cache('g'));
    }

    public function ape(Request $request){

        exit_dump("gg");
        echo _fun(5);

        Db::newInstance(2,[
            'hostname' => '119.29.66.66',
            'username' => '',
            'password' => '',
            'database' => '',
            'prefix' => ''
        ]);
        exit_dump(Db::newInstance(2)->name('article')->limit(10)->select());
        cache('g', ['gg' => 1], 10);
        view()->assign('g', '1');
        view()->setCache(10)->view('extends');

//        Session::set("apes", 'ggg');
//        Cookie::set('ape', 'aa');
//        $r = Db::instance()->name('cart')->select();
        //$r = Db::instance()->name('zc_deal_item')->field('di.id')->alias('di')->leftJoin('zc_deal d on d.id=di.deal_id ')->where('d.id=70')->limit(5)->select();

        // guquan
        // 子查询
        //$s = Db::instance()->buildSql()->name('order')->alias('o')->field('sum(order_amount)')->where('o.seller_id = s.store_id and o.status >= 11')->select();
        //$r = Db::instance()->name('store')->alias('s')->where('if_open = 2')->order("({$s}) desc, s.add_time asc")->select();

//        dump($r);
//        $a = ['id' => 1, 'name' => 'ds'];
//        $a = array_merge($a, ['id' => 2, 'name' => 'ds']);
//        exit_dump($a);
//        $r = Db::instance()->name('zc_z_contract')->data([
//            ['id' => 17, 'title' => 'ds'],
//            ['id' => 16, 'title' => 'ds']
//        ])->insert();
//        $r = Db::instance()->name('zc_z_contract')->insert(['id' => 19, 'title' => 'ds']);
//        dump($r);
//        $r = Db::instance()->name('zc_z_contract')->get();
//        $r = Db::instance()->name('zc_z_contract')->where('id=17')->update(['content' => 'ccc','title' => 'ggg']);
//        $r = Db::instance()->name('zc_z_contract')->where("title='ds1'")->delete();
//        $r = Db::instance()->name('zc_z_contract')->get(15);
//        $r = Db::instance()->name('zc_z_contract')->min('id');
//        exit_dump($r);
        exit();

        $r = \ape\Lock::checkLock('a');

            \ape\Lock::run('a', function (){
                sleep(5);
                echo "ggg";
            }, function (){
                echo "false";
            });

        exit;
        dump($request->get());
        return "ggg";
//        exit_dump($request);
        //exit_dump(['method' => 'ape']);
//        view()->view('index2');
        view()->view('admin@index');
    }

}