<?php


return [

    // method@route   =>  module@controller/action
    'get@new/:id' => 'index@index/index',

    'get' => [
        'i' => 'index@index/index',
        'ape' => 'index@index/ape',
        'ape/:id' => 'index@index/ape',
        'ape/:id/:name' => 'index@index/ape',
        'check' => 'index@index/check',
        'lock' => 'index@index/lock',
        't' => 'index@t/index',
    ],

];