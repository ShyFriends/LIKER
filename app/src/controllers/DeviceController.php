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



    public function get_aqi(Request $request, Response $response, $args)
    {
        $usn_sql = $_SESSION['usn'];
        //$usn_sql = '105';
        $sql = "select dsn from Devices where usn = '$usn_sql' and s_type = 'udoo'";
        $stmt = $this->em->getConnection()->query($sql);
        $stmt->execute();
        $results = $stmt->fetchAll();

        $dsn_sql = $results[0]['dsn'];

        $sql = "select udoo_sn, co_aqi, no2_aqi, so2_aqi, o3_aqi, pm2_5_aqi, pm10_aqi, longitude, latitude from Udoo where dsn = '$dsn_sql'";
        $stmt = $this->em->getConnection()->query($sql);
        $stmt->execute();

        $results = $stmt->fetchAll();

        $co_aqi = $results[0]['co_aqi'];
        $no2_aqi = $results[0]['no2_aqi'];
        $so2_aqi = $results[0]['so2_aqi'];
        $o3_aqi = $results[0]['o3_aqi'];
        $pm2_5_aqi = $results[0]['pm2_5_aqi'];
        $pm10_aqi = $results[0]['co_aqi'];

        $json_array = $results;
                return $response->withStatus(200)
                ->withHeader('Content-Type', 'application/json')
                ->write(json_encode($json_array));
    }
}
