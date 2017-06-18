<?php

namespace app\admin\controller;

use ape\Controller;
use ape\Request;

class Index extends Controller {

    public function index(Request $request){
        exit_dump($request->controller());
        exit_dump(['this is admin-Index-index']);
        $this->display();
    }

}