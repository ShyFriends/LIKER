<?php
// Routes

$app->get('/', 'App\Controller\HomeController:dispatch')
    ->setName('homepage');

$app->get('/sendmail', 'App\Controller\HomeController:sendMail')
    ->setName('sendmail');

$app->get('/signup', 'App\Controller\HomeController:signup')
    ->setName('signup');

$app->get('/post/{id}', 'App\Controller\HomeController:viewPost')
    ->setName('view_post');
