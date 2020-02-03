<?php
// Routes

$app->get('/', 'App\Controller\HomeController:dispatch')
    ->setName('homepage');

$app->get('/sendmail', 'App\Controller\HomeController:sendMail')
    ->setName('sendmail');

$app->get('/signup', 'App\Controller\HomeController:signup')
    ->setName('signup');

$app->get('/forgot_password', 'App\Controller\HomeController:forgot_password')
    ->setName('forgot_password');

$app->get('/aqi', 'App\Controller\DeviceController:aqi')
    ->setName('aqi');

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
