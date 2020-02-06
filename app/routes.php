<?php
// Routes

$app->get('/', 'App\Controller\HomeController:dispatch')
    ->setName('homepage');

$app->post('/userinfo', 'App\Controller\HomeController:userinfo')
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

$app->post('/register', 'App\Controller\HomeController:register')
    ->setName('register');

$app->get('/testJSON', 'App\Controller\HomeController:testJSON')
    ->setName('testJSON');

$app->get('/check_duplicate/{id}', 'App\Controller\HomeController:check_duplicate')
    ->setName('check_duplicate');

$app->get('/testQuery', 'App\Controller\HomeController:testQuery')
    ->setName('testQuery');

$app->post('/longerpate', 'App\Controller\HomeController:longerpath')
    ->setName('longerpate');

$app->post('/receiveData', 'App\Controller\HomeController:receiveData')
    ->setName('receiveData');

$app->get('/post/{id}', 'App\Controller\HomeController:viewPost')
    ->setName('view_post');
