<?php
namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
   
final class HomeController extends BaseController
{
    public function dispatch(Request $request, Response $response, $args)
    {
        if(empty($_SESSION['usn'])){
            $this->view->render($response, 'signup.twig');
            return $response;
        }
        else{
            //$this->logger->info("Home page action dispatched");
            //$this->flash->addMessage('info', 'Sample flash message');
            $this->view->render($response, 'home.twig',['username'=>$_SESSION['username']]);
            return $response;
        }
    }



    public function check_duplicate(Request $request, Response $response, $args)
    {
        $username_sql = $_GET['id'];

        $sql = "select * from Users where username = '$username_sql'";
        $stmt = $this->em->getConnection()->query($sql);
        $stmt->execute();

        $results = $stmt->fetchAll();
               

        if($results == NULL){
            $json_array = array("status" => "success");
                return $response->withStatus(200)
                ->withHeader('Content-Type', 'application/json')
                ->write(json_encode($json_array));
        }
        else{
            $json_array = array("status" => "fail");
                return $response->withStatus(200)
                ->withHeader('Content-Type', 'application/json')
                ->write(json_encode($json_array));
        }

    }

    public function remove(Request $request, Response $response, $args)
    {
        $username_sql = $_SESSION['username'];
        $usn = $_SESSION['usn'];

        //echo $_SESSION['username'].$_SESSION['usn']."test";
        //die("hello");

        $sql = "UPDATE Devices SET usn = -1 WHERE usn = $usn";
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();
       

        $sql = "DELETE FROM Users WHERE username = '$username_sql'";
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();


        if($results == NULL){
            $json_array = array("status" => "success");
                return $response->withStatus(200)
                ->withHeader('Content-Type', 'application/json')
                ->write(json_encode($json_array));
        }
        else{
            $json_array = array("status" => "fail");
                return $response->withStatus(200)
                ->withHeader('Content-Type', 'application/json')
                ->write(json_encode($json_array));
        }

        return $response->withStatus(200)
        ->withHeader('Content-Type', 'application/json')
        ->write(json_encode($json_array));
    }


    public function userinfo(Request $request, Response $response, $args)
    {
        $username_sql = $_SESSION['username'];
        $usn_sql  = $_SESSION['usn'];

        //userinfo
        $sql = "select username, birth, gender, email, phone_number from Users where usn = '$usn_sql'";
        $stmt = $this->em->getConnection()->query($sql);
        $stmt->execute();
        $user_results = $stmt->fetchAll();

        //polar sensor info
        $sql = "select dsn, s_name, s_type, mac_addr from Devices where usn = '$usn_sql' and s_type = 'polar'" ;
        $stmt = $this->em->getConnection()->query($sql);
        $stmt->execute();
        $polar_results = $stmt->fetchAll();

        //udoo sensor info
        $sql = "select dsn, s_name, s_type, mac_addr from Devices where usn = '$usn_sql' and s_type = 'udoo'";
        $stmt = $this->em->getConnection()->query($sql);
        $stmt->execute();
        $udoo_results = $stmt->fetchAll();

        $this->view->render($response, 'userinfo.twig', ['username'=>$_SESSION['username'], 'user_results'=>$user_results, 'polar_results'=>$polar_results, 'udoo_results'=>$udoo_results]);
    }

    public function remove_sensor(Request $request, Response $response, $args)
    {
        $usn_sql = $_SESSION['usn'];
        $dsn_sql = $_POST['dsn'];

        $sql = "UPDATE Polar SET dsn = -1 WHERE dsn = '$dsn_sql'";
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();

        $sql = "UPDATE Udoo SET dsn = -1 WHERE dsn = '$dsn_sql'";
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();

        $sql = "DELETE FROM Devices WHERE dsn = '$dsn_sql'";
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();

        $this->view->render($response, 'userinfo.twig');
        return $response;
    }

    public function regist_sensor(Request $request, Response $response, $args)
    {
        $usn_sql = $_SESSION['usn'];
        $s_name_sql = $_POST['s_name'];
        $s_type_sql = $_POST['s_type'];

        //$randomNum = mt_rand(10000000000000000, 100000000000000000);
        //$mac_addr = $randomNum;
        $mac_addr = $_POST['mac_addr'];

        $sql = "insert into Devices(usn, s_name, s_type, mac_addr) values ('$usn_sql','$s_name_sql','$s_type_sql','$mac_addr')";
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();

        $this->view->render($response, 'userinfo.twig');
        return $response;
    }

    public function check_sensor(Request $request, Response $response, $args)
    {
        $usn_sql = $_SESSION['usn'];
        //$randomNum = mt_rand(10000000000000000, 100000000000000000);
        //$mac_addr = $randomNum;
        $mac_addr = $_POST['mac_addr'];

        $sql = "select mac_addr from Devices where usn = '$usn_sql'";
        $stmt = $this->em->getConnection()->query($sql);
        $stmt->execute();
        $sensor_results = $stmt->fetchAll();
       
        $resultsno = count($sensor_results);

        for($i=0; $i<$resultsno; $i++){
            if($sensor_results[$i]['mac_addr'] == $mac_addr){
                $json_array = array("status" => 1);
                return $response->withStatus(200)
                ->withHeader('Content-Type', 'application/json')
                ->write(json_encode($json_array));
            }
        }
        $json_array = array("status" => 0);
        return $response->withStatus(200)
        ->withHeader('Content-Type', 'application/json')
        ->write(json_encode($json_array));

    }


    public function signin(Request $request, Response $response, $args)
    {    
        // print_r($_POST['password']);
        $username_sql = $_POST['username'];
        $password_sql = $_POST['password'];

        $sql = "select h_password, usn, username from Users where username = '$username_sql'";
        $stmt = $this->em->getConnection()->query($sql);
        $stmt->execute();
        $results = $stmt->fetchAll();

        if(password_verify($password_sql, $results[0]['h_password'])){
            $sql = "UPDATE Users SET loginflag = 'T' WHERE username = '$username_sql'";
            $stmt = $this->em->getConnection()->prepare($sql);
            $stmt->execute();

            $sql = "DELETE FROM Auth WHERE username = '$username_sql'";
            $stmt = $this->em->getConnection()->prepare($sql);
            $stmt->execute();
            
            //$json_array = array("status" => "success");
            $_SESSION['usn'] = $results[0]['usn'];
            $_SESSION['username'] = $results[0]['username'];
            //echo $_SESSION['usn'] . " is the session .... ";
            //die("test");
           
            $this->view->render($response, 'home.twig', ['username'=>$_SESSION['username']]);
            return $response;
        }

        else{
            //$json_array = array("status" => "fail", "message" => "signin fail...");
            $this->view->render($response, 'errorpage.twig');
            return $response;
        }

    }

    public function signup(Request $request, Response $response, $args)
    {
        $this->logger->info("Home page action dispatched");

        $this->flash->addMessage('info', 'Sample flash message');

        $this->view->render($response, 'signup.twig');
        return $response;
    }

    public function signout(Request $request, Response $response, $args)
    {
        $usn_sql = $_SESSION['usn'];
        $sql = "UPDATE Users SET loginflag = 'F' WHERE usn = '$usn_sql'";
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();

        session_unset();
        session_destroy();

        $this->view->render($response, 'nav.twig');
        return $response;
    }

    public function forgot_password(Request $request, Response $response, $args)
    {
        $this->view->render($response, 'forgot_password.twig');
        return $response;
    }

    public function self_verify(Request $request, Response $response, $args)
    {
        $email_sql = $_GET['email'];

        $nonce = password_hash($email_sql, PASSWORD_DEFAULT);

        $username_sql = $_GET['username'];

        $sql = "insert into Auth(username, temp_password, nonce, email, stateflag) values ('$username_sql','T', '$nonce','$email_sql', 'F')";
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();

        $this->sendMail2($email_sql, $nonce);

        $json_array = array("status" => "success");
        return $response->withStatus(200)
        ->withHeader('Content-Type', 'application/json')
        ->write(json_encode($json_array));
    }

    public function self_confirm_verify(Request $request, Response $response, $args)
    {
       
        $nonce = $_GET['nonce'];

        $sql = "select username from Auth where nonce = '$nonce'";
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();

        $results = $stmt->fetchAll();
               
        if($results == NULL){
            $this->view->render($response, 'errorpage.twig');
            return $response;
        }
        else{
            $sql = "UPDATE Auth SET stateflag = 'T' WHERE nonce = '$nonce'";
            $stmt = $this->em->getConnection()->prepare($sql);
            $stmt->execute();
           
            $this->view->render($response, 'success.twig');
            return $response;        
        }
    }

    public function verify(Request $request, Response $response, $args)
    {

        $nonce = password_hash($email_sql, PASSWORD_DEFAULT);

        $username_sql = $_POST['username'];

        $sql = "select email from Users where username = '$username_sql'";
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();

        $results = $stmt->fetchAll();
       
        $email_sql = $results[0]['email'];

        $randomNum = mt_rand(1000, 10000);
        $temp_password = password_hash($randomNum, PASSWORD_DEFAULT);

        $sql = "insert into Auth(username, temp_password, nonce, email, stateflag) values ('$username_sql','$temp_password', '$nonce','$email_sql', 'F')";
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();

       
        $sql = "UPDATE Users SET h_password = '$temp_password' WHERE username = '$username_sql'";
            $stmt = $this->em->getConnection()->prepare($sql);
            $stmt->execute();

        $this->sendMail($email_sql, $nonce, $randomNum);

        $this->view->render($response, 'success.twig');
        return $response;
    }

    public function confirm_verify(Request $request, Response $response, $args)
    {
       
        $nonce = $_GET['nonce'];

        $sql = "select username from Auth where nonce = '$nonce'";
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();

        $results = $stmt->fetchAll();
               
        if($results == NULL){
            $this->view->render($response, 'errorpage.twig');
            return $response;
        }
        else{
            $sql = "UPDATE Auth SET stateflag = 'T' WHERE nonce = '$nonce'";
            $stmt = $this->em->getConnection()->prepare($sql);
            $stmt->execute();
            $this->view->render($response, 'signup.twig');
            return $response;
        }
    }

    public function register(Request $request, Response $response, $args)
    {
        $username_sql = $_POST['username'];
        $password_sql = $_POST['first_password'];
        $email_sql = $_POST['email'];
        $phone_number_sql = $_POST['phone_number'];
        $birth_sql = $_POST['birth'];
        $gender_sql = $_POST['gender'];

        $hashed_password = password_hash($password_sql, PASSWORD_DEFAULT);

        $sql = "select stateflag from Auth where username = '$username_sql'";
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();

        $results = $stmt->fetchAll();

        if($results[0]['stateflag']=="T"){
            $sql = "insert into Users(username, h_password, email, birth, phone_number, gender, loginflag) values ('$username_sql','$hashed_password','$email_sql','$birth_sql','$phone_number_sql','$gender_sql', 'F')";
            $stmt = $this->em->getConnection()->prepare($sql);
            $stmt->execute();

            $sql = "DELETE FROM Auth WHERE username = '$username_sql'";
            $stmt = $this->em->getConnection()->prepare($sql);
            $stmt->execute();

            $this->view->render($response, 'success.twig');
            return $response;
        }
        else{
            $this->view->render($response, 'errorpage.twig');
            return $response;
        }
    }

    public function check_pwd(Request $request, Response $response, $args)
    {
        $username_sql = $_SESSION['username'];
        $password_sql = $_POST['current_pwd'];

        $sql = "select h_password from Users where username = '$username_sql'";
        $stmt = $this->em->getConnection()->query($sql);
        $stmt->execute();

        $results = $stmt->fetchAll();

        if(password_verify($password_sql, $results[0]['h_password'])){
            $json_array = array("status" => 0);
            return $response->withStatus(200)
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode($json_array));

        }
        else{
            $json_array = array("status" => 1);
            return $response->withStatus(200)
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode($json_array));
        }
    }

    public function change_pwd(Request $request, Response $response, $args)
    {
        $username_sql = $_SESSION['username'];
        $password_sql = $_POST['new_pwd'];

        $nonce = password_hash($password_sql, PASSWORD_DEFAULT);

        $sql = "UPDATE Users SET h_password = '$nonce', loginflag = 'F' WHERE username = '$username_sql'";
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();
       
        session_unset();
        session_destroy();  
        $this->view->render($response, 'userinfo.twig');
        return $response;    

    }

    public function sendMail($email, $nonce, $randomNum)
    {
        $results = $email;

        $mail = new PHPMailer(true);

        try {
        //Server settings
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host       = 'smtp.gmail.com';                    // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $mail->Username   = 'wkdgurwls1211@gmail.com';                     // SMTP username
        $mail->Password   = 'gurwls00!';                                   // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
        $mail->Port       = 587;                                    // TCP port to connect to

        //Recipients
        $mail->setFrom('QI.8.teamb@gmail.com', 'Team B');
        $mail->addAddress($results);     // Add a recipient
        //$mail->addAddress('ellen@example.com');               // Name is optional
        // $mail->addReplyTo('info@example.com', 'Information');
        // $mail->addCC('cc@example.com');
        // $mail->addBCC('bcc@example.com');

        // // Attachments
        // $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
        // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

        // Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = 'Whattssssup bro~~';
        $mail->Body    = '<h1>LIKER Message</h1><a href="http://teamb-iot.calit2.net/confirm_verify?nonce=' . $nonce . '">Sign-In? Click me!</a><br>your temporary password is :' . $randomNum . '';
        $mail->AltBody = 'HeyHey~';

        $mail->send();
        //echo 'Message has been sent';
        }
        catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

    }

    public function sendMail2($email, $nonce)
    {
        $results = $email;

        $mail = new PHPMailer(true);

        try {
        //Server settings
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host       = 'smtp.gmail.com';                    // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $mail->Username   = 'wkdgurwls1211@gmail.com';                     // SMTP username
        $mail->Password   = 'gurwls00!';                               // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
        $mail->Port       = 587;                                    // TCP port to connect to

        //Recipients
        $mail->setFrom('QI.8.teamb@gmail.com', 'Team B');
        $mail->addAddress($results);     // Add a recipient

        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = 'Whattssssup bro~~';
        $mail->Body    = '<h1>LIKER Message</h1><a href="http://teamb-iot.calit2.net/self_confirm_verify?nonce=' . $nonce . '">Sign-In? Click me!</a>';
        $mail->AltBody = 'HeyHey~';

        $mail->send();
        // echo 'Message has been sent';
        }
        catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

    }

   
    public function testQuery(Request $request, Response $response, $args){
       
        $sql = "select * from Users";
        $stmt = $this->em->getConnection()->query($sql);
        $stmt->execute();

        $results = $stmt->fetchAll();
        print_r($results);

        return $response->withStatus(200)
        ->withHeader('Content-Type','application/json')
        ->write(json_encode($results));
    }


    public function longerpath(Request $request, Response $response, $args){
        var_dump($args['start']);
        var_dump($args['end']);

        exit;

        $my_array = array();
        $my_array = array("name"=>"Bill","address"=>"123 Pine");
        $my_array = array("name"=>"Frank","address"=>"456 Hyatt");
       

        return $response->withStatus(200)
        ->withHeader('Content-Type','application/json')
        ->write(json_encode($results));
    }


    public function receiveData(Request $request, Response $response, $args){
        $data = $request->getParseBody();
        foreach($data as $field){
            print_r($field);
        }
        var_dump($data);
    }

    public function testJSON(Request $request, Response $response, $args){
        $my_array=array("name"=>"HyukJin","address"=>"5734 Scripps St");

        return $response->withStatus(200)
        ->withHeader('Content-Type','application/json')
        ->write(json_encode($my_array));
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
