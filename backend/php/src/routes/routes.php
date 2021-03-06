<?php
//<project>\backend\src\routes\routes.php
//mapeo de rutas y controladores

return [   
    ["url"=>"/","controller"=>"App\Controllers\NotFoundController","method"=>"index"],
//infrastructure
    ["url"=>"/get-max-upload-size","controller"=>"App\Controllers\InfrastructureController","method"=>"get_maxuploadsize"],

//upload types
    ["url"=>"/upload","controller"=>"App\Controllers\UploadController","method"=>"index"],
    ["url"=>"/upload/multiple","controller"=>"App\Controllers\UploadController","method"=>"multiple"],
    ["url"=>"/upload/by-url","controller"=>"App\Controllers\UploadController","method"=>"by_url"],

//folder resource interaction
    ["url"=>"/remove","controller"=>"App\Controllers\UploadController","method"=>"remove"],
    ["url"=>"/folders","controller"=>"App\Controllers\UploadController","method"=>"folders"],
    ["url"=>"/files","controller"=>"App\Controllers\UploadController","method"=>"files"],

//security
    ["url"=>"/security/get-password","controller"=>"App\Controllers\Security\PasswordController","method"=>"index"],
    ["url"=>"/security/get-signature","controller"=>"App\Controllers\Security\SignatureController","method"=>"index"],
    ["url"=>"/security/is-valid-signature","controller"=>"App\Controllers\Security\SignatureController","method"=>"is_valid_signature"],

//tokens
    ["url"=>"/security/login","controller"=>"App\Controllers\Security\LoginController","method"=>"index"],
    ["url"=>"/security/login-middle","controller"=>"App\Controllers\Security\LoginController","method"=>"middle"],
    ["url"=>"/security/is-valid-token","controller"=>"App\Controllers\Security\LoginController","method"=>"is_valid_token"],

//resto de rutas
    ["url"=>"/404","controller"=>"App\Controllers\NotFoundController","method"=>"error_404"]
];
