<?php

namespace app\index\controller;

use model\M;

class T extends M {

    public function index(){
        exit_dump($this->i());
        exit_dump(['index-t-index']);
    }

    public function k(){
        exit_dump(['index-t-k']);
    }

}