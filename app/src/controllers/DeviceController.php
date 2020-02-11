<?php
namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

final class DeviceController extends BaseController
{
    public function dispatch(Request $request, Response $response, $args)
    {
        $this->logger->info("Home page action dispatched");

        $this->flash->addMessage('info', 'Sample flash message');

        $this->view->render($response, 'home.twig');
        return $response;
    }

    public function polar(Request $request, Response $response, $args)
    {
        $username_sql = $_SESSION['username'];
        $this->view->render($response, 'devices/polar.twig',['username'=>$username_sql]);
        return $response;
    }

    public function udoo(Request $request, Response $response, $args)
    {   
        $username_sql = $_SESSION['username'];
        $this->view->render($response, 'devices/udoo.twig', ['username'=>$username_sql]);
        return $response;
    }

    public function aqi(Request $request, Response $response, $args) 
    {
        $username_sql = $_SESSION['username'];
        $this->view->render($response, 'devices/aqi.twig',['username'=>$username_sql]);
        return $response;
    }

    public function heartrate(Request $request, Response $response, $args)
    {
        $username_sql = $_SESSION['username'];
        $this->view->render($response, 'devices/heartrate.twig',['username'=>$username_sql]);
        return $response;
    }

    public function viewPost(Request $request, Response $response, $args)
    {
        $this->logger->info("View post using Doctrine with Slim 3");

        $messages = $this->flash->getMessage('info');

        try {
            $post = $this->em->find('App\Model\Post', intval($args['id']));
        } catch (\Exception $e) {
            echo $e->getMessage();
            die;
        }

        $this->view->render($response, 'post.twig', ['post' => $post, 'flash' => $messages]);
        return $response;
    }
}
