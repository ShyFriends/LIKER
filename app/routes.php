<?php
// Routes

$app->get('/', 'App\Controller\HomeController:dispatch')
    ->setName('homepage');

$app->get('/sendmail', 'App\Controller\HomeController:sendMail')
    ->setName('sendmail');

$app->get('/signup', 'App\Controller\HomeController:signup')
    ->setName('signup');

$app->post('/signin', 'App\Controller\HomeController:signin')
    ->setName('signin');

$app->get('/testJSON', 'App\Controller\HomeController:testJSON')
    ->setName('testJSON');

$app->get('/testQuery', 'App\Controller\HomeController:testQuery')
    ->setName('testQuery');

$app->post('/longerpate', 'App\Controller\HomeController:longerpath')
    ->setName('longerpate');

$app->post('/receiveData', 'App\Controller\HomeController:receiveData')
    ->setName('receiveData');

$app->get('/post/{id}', 'App\Controller\HomeController:viewPost')
    ->setName('view_post');
