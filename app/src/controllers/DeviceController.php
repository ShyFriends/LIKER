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

    public function heartrate_num(Request $request, Response $response, $args)
    {
        $heartrate_num = [];
        $usn_sql = $_SESSION['usn'];

        $sql = "select dsn from Devices where usn = '$usn_sql' and s_type = 'polar'";
        $stmt = $this->em->getConnection()->query($sql);
        $stmt->execute();
        $results = $stmt->fetchAll();

        $dsn_sql = $results[0]['dsn'];
//and DATE_ADD(NOW(), INTERVAL -10 SECOND) <= time and time <= now()
        $sql = "select heart_rate from Polar where dsn=".$dsn_sql." order by time desc limit 1";
        $stmt = $this->em->getConnection()->query($sql);
        $stmt->execute();
        $results = $stmt->fetchAll();

        $heartrate_num['heartrate'] = $results[0]['heart_rate'];

        $json_array = $heartrate_num;
            return $response->withStatus(200)
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode($json_array));   
    }

    public function heartrate_info(Request $request, Response $response, $args)
    {
        $heartrate_info = [];
        $usn_sql = $_SESSION['usn'];

        $sql = "select dsn from Devices where usn = '$usn_sql' and s_type = 'polar'";
        $stmt = $this->em->getConnection()->query($sql);
        $stmt->execute();
        $results = $stmt->fetchAll();

        $dsn_sql = $results[0]['dsn'];

        $sql = "select avg(heart_rate), max(heart_rate), min(heart_rate) from Polar where dsn = ".$dsn_sql;
        $stmt = $this->em->getConnection()->query($sql);
        $stmt->execute();
        $results = $stmt->fetchAll();

        $heartrate_info['avg'] = $results[0]['avg(heart_rate)'];
        $heartrate_info['min'] = $results[0]['min(heart_rate)'];
        $heartrate_info['max'] = $results[0]['max(heart_rate)'];

        $json_array = $heartrate_info;
            return $response->withStatus(200)
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode($json_array));   
    }

     public function historic_heartrate(Request $request, Response $response, $args)
    {
        $historic_polar = [];
        $usn_sql = $_SESSION['usn'];
        $start_time_sql = $_GET['start_time'];
        $end_time_sql = $_GET['end_time'];

        $sql = "select dsn from Devices where usn = '$usn_sql' and s_type = 'polar'";
        $stmt = $this->em->getConnection()->query($sql);
        $stmt->execute();
        $results = $stmt->fetchAll();

        $dsn_sql = $results[0]['dsn'];

        $sql = "select time, heart_rate from Polar where dsn=".$dsn_sql." and '".$start_time_sql."' <= time and time < '".$end_time_sql."' order by time asc";
        $stmt = $this->em->getConnection()->query($sql);
        $stmt->execute();
        $pre_polar_data = $stmt->fetchAll();

        $resultsno = count($pre_polar_data);

        for($i=0; $i<$resultsno; $i++){
            $historic_polar[$i]['heart_rate'] = $pre_polar_data[$i]['heart_rate'];
            $historic_polar[$i]['time'] = $pre_polar_data[$i]['time'];
            $sql = "select TIMESTAMPDIFF(second, Date_format('".$start_time_sql."', '%Y-%m-%d %H:%i:%s'), date_format('".$pre_polar_data[$i]['time']."', '%Y-%m-%d %H:%i:%s')) AS timediff";
            $stmt = $this->em->getConnection()->query($sql);
            $stmt->execute();
            $timediff = $stmt->fetchAll(); 
            $historic_polar[$i]['timediff'] = $timediff[0]['timediff'];
        }

        /*for($i=0; $i<24; $i++){
            $sql = "select heart_rate from Polar where dsn=".$dsn_sql." and '".$date_sql." ".$i.":00:00' <= time and time < '".$date_sql." ".($i+1).":00:00' order by time asc limit 0,1";
            if(i==23){  
                $sql = "select heart_rate from Polar where dsn=".$dsn_sql." and '".$date_sql." ".$i.":00:00' <= time and time < '".($date_sql+1)." 00:00:00' order by time asc limit 0,1";
            }        
            $stmt = $this->em->getConnection()->query($sql);
            $stmt->execute();
            $pre_polar_data = $stmt->fetchAll();
            $historic_polar[$i] = $pre_polar_data[0]['heart_rate'];
        }*/
     
        $json_array = $historic_polar;
            return $response->withStatus(200)
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode($json_array));
    }

    public function realtime_heartrate(Request $request, Response $response, $args)
    {
        $realtime_polar = [];
        $usn_sql = $_SESSION['usn'];

        $sql = "select dsn from Devices where usn = '$usn_sql' and s_type = 'polar'";
        $stmt = $this->em->getConnection()->query($sql);
        $stmt->execute();
        $results = $stmt->fetchAll();

        $dsn_sql = $results[0]['dsn'];

         for($i=0; $i<60; $i++){
            $sql = "select heart_rate from Polar where dsn=".$dsn_sql." and DATE_ADD(NOW(), INTERVAL -".($i+1)." MINUTE) <= time and time < DATE_ADD(NOW(), INTERVAL -".$i." MINUTE) order by time desc limit 0,1";
            $stmt = $this->em->getConnection()->query($sql);
            $stmt->execute();
            $pre_polar_data = $stmt->fetchAll();
            $realtime_polar[$i] = $pre_polar_data[0]['heart_rate'];
        }
     
      $json_array = $realtime_polar;
            return $response->withStatus(200)
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode($json_array));
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

        $sql = "select udoo_sn, co_aqi, no2_aqi, so2_aqi, o3_aqi, pm2_5_aqi, pm10_aqi, longitude, latitude from Udoo where dsn = '$dsn_sql' order by time desc limit 1";
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

    public function location_aqi(Request $request, Response $response, $args)
    {
        $usn_sql = $_SESSION['usn'];
        $dsn_sql = $_GET['udoo_id'];

        $sql = "select dsn, co_aqi, no2_aqi, so2_aqi, o3_aqi, pm2_5_aqi, pm10_aqi, time from Udoo where dsn = '$dsn_sql' order by time desc limit 12 ";
        $stmt = $this->em->getConnection()->query($sql);
        $stmt->execute();

        $results = $stmt->fetchAll();

        $json_array = $results;
            return $response->withStatus(200)
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode($json_array));
    }

    public function device_data(Request $request, Response $response, $args)
    {
        $usn_sql = $_SESSION['usn'];
        $dsn_sql = $_GET['udoo_id'];

        $sql = "select dsn, mac_addr, s_name, s_type from Devices where dsn = '$dsn_sql'";
        $stmt = $this->em->getConnection()->query($sql);
        $stmt->execute();

        $results = $stmt->fetchAll();

        $json_array = $results;
                return $response->withStatus(200)
                ->withHeader('Content-Type', 'application/json')
                ->write(json_encode($json_array));
    }

    public function locations(Request $request, Response $response, $args)
    {
        $ne_lat_sql = (float)$_GET['ne_lat'];
        $ne_lng_sql = (float)$_GET['ne_lng'];
        $sw_lat_sql = (float)$_GET['sw_lat'];
        $sw_lng_sql = (float)$_GET['sw_lng'];

        $usn_sql = $_SESSION['usn'];

        if($ne_lng_sql<0&&$sw_lng_sql>0){
            
            $sql = "select longitude, latitude, dsn, co_aqi, no2_aqi, so2_aqi, o3_aqi, pm2_5_aqi, pm10_aqi from Udoo where latitude < '$ne_lat_sql' and latitude > '$sw_lat_sql' and longitude < 180 and (longitude < '$ne_lng_sql' or longitude > '$sw_lng_sql') and (time,dsn) in (SELECT max(time),dsn FROM Udoo group by dsn);";
            $stmt = $this->em->getConnection()->query($sql);
            $stmt->execute();

            $results = $stmt->fetchAll();
        }
        else{
            $sql = "select longitude, latitude, dsn, co_aqi, no2_aqi, so2_aqi, o3_aqi, pm2_5_aqi, pm10_aqi from Udoo where latitude < '$ne_lat_sql' and latitude > '$sw_lat_sql' and longitude < '$ne_lng_sql' and longitude > '$sw_lng_sql' and (time,dsn) in (SELECT max(time),dsn FROM Udoo group by dsn);";
            $stmt = $this->em->getConnection()->query($sql);
            $stmt->execute();

            $results = $stmt->fetchAll();
        }

        

        //print_r($results);
        $json_array = $results;
                return $response->withStatus(200)
                ->withHeader('Content-Type', 'application/json')
                ->write(json_encode($json_array));
    }

    public function historic_aqi(Request $request, Response $response, $args)
    {
        $historic_udoo = [];
        $usn_sql = $_SESSION['usn'];
        $start_time_sql = $_GET['start_time'];
        $end_time_sql = $_GET['end_time'];
        $dsn_sql = $_GET['dsn'];

        $sql = "select dsn, time, longitude, latitude, co_aqi, no2_aqi, so2_aqi, o3_aqi, pm2_5_aqi, pm10_aqi from Udoo where dsn=".$dsn_sql." and '".$start_time_sql."' <= time and time < '".$end_time_sql."' order by time asc";

        $stmt = $this->em->getConnection()->query($sql);
        $stmt->execute();
        $pre_udoo_data = $stmt->fetchAll();

        $resultsno = count($pre_udoo_data);

        for($i=0; $i<$resultsno; $i++){
            $historic_udoo[$i]['co_aqi'] = $pre_udoo_data[$i]['co_aqi'];
            $historic_udoo[$i]['no2_aqi'] = $pre_udoo_data[$i]['no2_aqi'];
            $historic_udoo[$i]['so2_aqi'] = $pre_udoo_data[$i]['so2_aqi'];
            $historic_udoo[$i]['o3_aqi'] = $pre_udoo_data[$i]['o3_aqi'];
            $historic_udoo[$i]['pm2_5_aqi'] = $pre_udoo_data[$i]['pm2_5_aqi'];
            $historic_udoo[$i]['pm10_aqi'] = $pre_udoo_data[$i]['pm10_aqi'];
            $historic_udoo[$i]['time'] = $pre_udoo_data[$i]['time'];

            $sql = "select TIMESTAMPDIFF(second, Date_format('".$start_time_sql."', '%Y-%m-%d %H:%i:%s'), date_format('".$pre_udoo_data[$i]['time']."', '%Y-%m-%d %H:%i:%s')) AS timediff";
            $stmt = $this->em->getConnection()->query($sql);
            $stmt->execute();
            $timediff = $stmt->fetchAll(); 
            $historic_udoo[$i]['timediff'] = $timediff[0]['timediff'];
        }
     
      $json_array = $historic_udoo;
            return $response->withStatus(200)
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode($json_array));
    }
}
