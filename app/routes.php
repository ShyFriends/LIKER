<?php
// Routes

$app->get('/', 'App\Controller\HomeController:dispatch')
    ->setName('homepage');

$app->get('/userinfo', 'App\Controller\HomeController:userinfo')
    ->setName('userinfo');

$app->get('/sendmail', 'App\Controller\HomeController:sendMail')
    ->setName('sendmail');

$app->get('/sendmail2', 'App\Controller\HomeController:sendMail2')
    ->setName('sendmail2');

$app->get('/self_verify/{username}/{email}', 'App\Controller\HomeController:self_verify')
    ->setName('self_verify');

$app->post('/verify', 'App\Controller\HomeController:verify')
    ->setName('verify');

$app->get('/confirm_verify', 'App\Controller\HomeController:confirm_verify')
    ->setName('confirm_verify');

$app->get('/self_confirm_verify', 'App\Controller\HomeController:self_confirm_verify')
    ->setName('self_confirm_verify');

$app->get('/signup', 'App\Controller\HomeController:signup')
    ->setName('signup');

$app->get('/forgot_password', 'App\Controller\HomeController:forgot_password')
    ->setName('forgot_password');

$app->post('/check_pwd/{current_pwd}', 'App\Controller\HomeController:check_pwd')
    ->setName('check_pwd');

$app->post('/change_pwd/{new_pwd}', 'App\Controller\HomeController:change_pwd')
    ->setName('change_pwd');

$app->get('/polar', 'App\Controller\DeviceController:polar')
    ->setName('polar');

$app->get('/udoo', 'App\Controller\DeviceController:udoo')
    ->setName('udoo');

$app->get('/aqi', 'App\Controller\DeviceController:aqi')
    ->setName('aqi');

$app->get('/heartrate', 'App\Controller\DeviceController:heartrate')
    ->setName('heartrate');

$app->post('/signin', 'App\Controller\HomeController:signin')
    ->setName('signin');

$app->post('/check_sensor', 'App\Controller\HomeController:check_sensor')
    ->setName('check_sensor');

// $app->post('/check_sensor/{mac_addr}', 'App\Controller\HomeController:check_sensor')
//     ->setName('check_sensor');

$app->post('/regist_sensor/{s_name}/{s_type}', 'App\Controller\HomeController:regist_sensor')
    ->setName('regist_sensor');

$app->post('/remove_sensor/{dsn}', 'App\Controller\HomeController:remove_sensor')
    ->setName('remove_sensor');

$app->post('/register', 'App\Controller\HomeController:register')
    ->setName('register');

$app->get('/testJSON', 'App\Controller\HomeController:testJSON')
    ->setName('testJSON');

$app->get('/check_duplicate/{id}', 'App\Controller\HomeController:check_duplicate')
    ->setName('check_duplicate');

$app->get('/remove', 'App\Controller\HomeController:remove')
    ->setName('remove');

$app->get('/signout', 'App\Controller\HomeController:signout')
    ->setName('signout');

$app->get('/testQuery', 'App\Controller\HomeController:testQuery')
    ->setName('testQuery');

$app->post('/longerpate', 'App\Controller\HomeController:longerpath')
    ->setName('longerpate');

$app->post('/receiveData', 'App\Controller\HomeController:receiveData')
    ->setName('receiveData');

$app->get('/post/{id}', 'App\Controller\HomeController:viewPost')
    ->setName('view_post');

$app->get('/signup_app', 'App\Controller\AppController:signup_app')
    ->setName('signup_app');

$app->post('/signin_app', 'App\Controller\AppController:signin_app')
    ->setName('signin_app');

$app->get('/signout_app', 'App\Controller\AppController:signout_app')
    ->setName('signout_app');