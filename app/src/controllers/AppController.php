<?php
namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
    
final class AppController extends BaseController
{
    public function dispatch(Request $request, Response $response, $args)
    {
        if(empty($_SESSION['usn'])){
            $this->view->render($response, 'signup.twig');
            return $response;
        }
        else{
            $this->logger->info("Home page action dispatched");
            $this->flash->addMessage('info', 'Sample flash message');
            $this->view->render($response, 'home.twig', ['username'=>$_SESSION['username']]);
            return $response;
        }
    }

    public function send_polardata_app(Request $request, Response $response, $args)
    {
        $dsn_sql = $_POST['dsn'];
        $time_sql = $_POST['time'];
        $lat_sql = $_POST['latitude'];
        $long_sql = $_POST['longitude'];
        $heartrate_sql = $_POST['heartrate'];

        $sql = "insert into Polar (dsn,heart_rate,latitude,longitude,time) values (".$dsn_sql.",".$heartrate_sql.",".$lat_sql.",".$long_sql.",'".$time_sql."')";
        $stmt = $this->em->getConnection()->query($sql);

        $data = array("message" => "true");
        echo json_encode($data);
    }

    public function send_udoodata_app(Request $request, Response $response, $args)
    {
        $dsn_sql = $_POST['dsn'];
        $time_sql = $_POST['time'];
        $co_sql = $_POST['co'];
        $no2_sql = $_POST['no2'];
        $so2_sql = $_POST['so2'];
        $o3_sql = $_POST['o3'];
        $pm25_sql = $_POST['pm25'];
        $pm10_sql = $_POST['pm10'];
        $lat_sql = $_POST['latitude'];
        $long_sql = $_POST['longitude'];

        $sql = "insert into Udoo (dsn,co,no2,so2,o3,pm2.5,pm10,latitude,longitude,time) values (".$dsn_sql.",".$co_sql.",".$no2_sql.",".$so2_sql.",".$o3_sql.",".$pm25_sql.",".$pm10_sql.",".$lat_sql.",".$long_sql.",'".$time_sql."')";
        $stmt = $this->em->getConnection()->query($sql);

        $data = array("message" => "true");
        echo json_encode($data);
    }   

    public function check_duplicate_app(Request $request, Response $response, $args)
    {
        $username_sql = $_GET['id'];

        $sql = "select * from Users where username = '$username_sql'";
        $stmt = $this->em->getConnection()->query($sql);

        $results = $stmt->fetchAll();

        if($results == NULL){
            $data = array("message" => "true");
            echo json_encode($data);
        }
        else{
            $data = array("message" => "false");
            echo json_encode($data);
        }

    }
    
    public function self_verify_app(Request $request, Response $response, $args)
    {
        $email_sql = $_GET['email'];

        $nonce = password_hash($email_sql, PASSWORD_DEFAULT);

        $username_sql = $_GET['username'];

        $sql = "insert into Auth(username, temp_password, nonce, email, stateflag) values ('$username_sql','T', '$nonce','$email_sql', 'F')";
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();

        $this->sendMail2_app($email_sql, $nonce);


        $data = array("message" => "true");
        echo json_encode($data);

    }

    public function self_confirm_verify_app(Request $request, Response $response, $args)
    {

        $nonce = $_GET['nonce'];

        $sql = "select username from Auth where nonce = '$nonce'";
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();

        $results = $stmt->fetchAll();

        if($results == NULL){
            $data = array("message" => "false");
            echo json_encode($data);
        }
        else{
            $sql = "UPDATE Auth SET stateflag = 'T' WHERE nonce = '$nonce'";
            $stmt = $this->em->getConnection()->prepare($sql);
            $stmt->execute();

            $data = array("message" => "true");
            echo json_encode($data);

        }
    }

    
    public function verify_app(Request $request, Response $response, $args)
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


        $this->sendMail_app($email_sql, $nonce, $randomNum);

        $data = array("message" => "true");
        echo json_encode($data);
    }

    public function confirm_verify_app(Request $request, Response $response, $args)
    {

        $nonce = $_GET['nonce'];

        $sql = "select username from Auth where nonce = '$nonce'";
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();

        $results = $stmt->fetchAll();

        if($results == NULL){
            $data = array("message" => "true");
            echo json_encode($data);
        }
        else{
            $sql = "UPDATE Auth SET stateflag = 'T' WHERE nonce = '$nonce'";
            $stmt = $this->em->getConnection()->prepare($sql);
            $stmt->execute();
            $data = array("message" => "true");
            echo json_encode($data);
        }
    }

    public function signin_app(Request $request, Response $response, $args)
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
            $data = array("message" => "true");
            echo json_encode($data);
        }
        else{
            $data = array("message" => "false");
            echo json_encode($data);
        }
    }

    public function signup_app(Request $request, Response $response, $args)
    {
        $this->logger->info("Home page action dispatched");

        $this->flash->addMessage('info', 'Sample flash message');

        $this->view->render($response, 'signup.twig');
        return $response;
    }

    public function signout_app(Request $request, Response $response, $args)
    {
        $username_sql = $_GET['username'];
        $sql = "UPDATE Users SET loginflag = 'F' WHERE username = '$username_sql'";
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();

        session_unset();
        session_destroy();

        $data = array("message" => "true");
        echo json_encode($data);
    }



   public function register_app(Request $request, Response $response, $args)
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

            $data = array("message" => "true");

            echo json_encode($data);
        }
        else{
            $data = array("message" => "false");
            echo json_encode($data);
        }
    }

    public function sendMail_app($email, $nonce, $randomNum)
    {
        $results = $email;
        print_r($results);
        $mail = new PHPMailer(true);

        try {
        //Server settings
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host       = 'smtp.gmail.com';                    // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $mail->Username   = 'pcyeon07@gmail.com';                     // SMTP username
        $mail->Password   = 'Qkrcodus97!';                                   // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
        $mail->Port       = 587;                                    // TCP port to connect to

        //Recipients
        $mail->setFrom('wkdgurwls1211@gmail.com', 'HyukJin');
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
        $mail->Body    = '<h1>LIKER Message</h1><a href="http://teamb-iot.calit2.net/confirm_verify_app?nonce=' . $nonce . '">Sign-In? Click me!</a><br>your temporary password is :' . $randomNum . '';
        $mail->AltBody = 'HeyHey~';

        $mail->send();
        //echo 'Message has been sent';
        }
        catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

    }


    public function sendMail2_app($email, $nonce)
    {
        $results = $email;

        $mail = new PHPMailer(true);

        try {
        //Server settings
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host       = 'smtp.gmail.com';                    // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $mail->Username   = 'pcyeon07@gmail.com';                     // SMTP username
        $mail->Password   = 'Qkrcodus97!';                               // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
        $mail->Port       = 587;                                    // TCP port to connect to

        //Recipients
        $mail->setFrom('QI.8.teamb@gmail.com', 'HyukJin');
        $mail->addAddress($results);     // Add a recipient

        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = 'Whattssssup bro~~';
        $mail->Body    = '<h1>LIKER Message</h1><a href="http://teamb-iot.calit2.net/self_confirm_verify_app?nonce=' . $nonce . '">Sign-In? Click me!</a>';
        $mail->AltBody = 'HeyHey~';

        $mail->send();
        // echo 'Message has been sent';
        }
        catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

    }

    public function check_it_app(Request $request, Response $response, $args)
    {
        $username_sql = $_GET['username'];

            $sql = "select stateflag from Auth where username = '$username_sql'";
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();

        $results = $stmt->fetchAll();

        if($results[0]['stateflag']=="T"){
            $data = array("message" => "true");
            echo json_encode($data);
        }
        else{
            $data = array("message" => "false");
            echo json_encode($data);
        }

    }



    public function userinfo_app(Request $request, Response $response, $args)
    {
        $username_sql = $_GET['username'];
        //userinfo
        $sql = "select usn, birth, gender, email, phone_number from Users where username = '$username_sql'";
        $stmt = $this->em->getConnection()->query($sql);
        $stmt->execute();
        $user_results = $stmt->fetchAll();

        $usn_sql = $user_results[0]['usn'];

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

        $my_array=array("username"=>$_SESSION['username'],"user_results"=>$user_results, 'polar_results'=>$polar_results, 'udoo_results'=>$udoo_results);

        return $response->withStatus(200)
        ->withHeader('Content-Type','application/json')
        ->write(json_encode($my_array));


        // $this->view->render($response, 'userinfo.twig', ['username'=>$_SESSION['username'], 'user_results'=>$user_results, 'polar_results'=>$polar_results, 'udoo_results'=>$udoo_results]);
    }
    
    public function remove_sensor_app(Request $request, Response $response, $args)
    {
        $username_sql = $_POST['username'];
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

        $data = array("message" => "true");
        echo json_encode($data);
    }

    public function regist_sensor_app(Request $request, Response $response, $args)
    {
        $username_sql = $_POST['username'];
        $s_name_sql = $_POST['s_name'];
        $s_type_sql = $_POST['s_type'];
        $mac_addr = $_POST['mac_addr'];


        $sql = "select usn from Users where username = '$username_sql'";
        $stmt = $this->em->getConnection()->query($sql);
        $stmt->execute();
        $user_results = $stmt->fetchAll();

        $usn_sql = $user_results[0]['usn'];
        //$randomNum = mt_rand(10000000000000000, 100000000000000000);
        //$mac_addr = $randomNum;

        $sql = "insert into Devices(usn, s_name, s_type, mac_addr) values ('$usn_sql','$s_name_sql','$s_type_sql','$mac_addr')";
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();

        $data = array("message" => "true");
        echo json_encode($data);
    }

    public function check_sensor_app(Request $request, Response $response, $args)
    {
        $username_sql = $_POST['username'];

        $randomNum = mt_rand(10000000000000000, 100000000000000000);
        $mac_addr = $randomNum;
        //$mac_addr = $_POST['mac_addr']; 
        $sql = "select mac_addr from Devices natural join Users where username = '$username_sql'";
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
        $json_array = array("status" => 1);
        return $response->withStatus(200)
        ->withHeader('Content-Type', 'application/json')
        ->write(json_encode($json_array));

    }

    public function remove_app(Request $request, Response $response, $args)
    {
        $username_sql = $_GET['username'];

        $sql = "select usn from Users where username = '$username_sql'";
        $stmt = $this->em->getConnection()->query($sql);
        $stmt->execute();
        $user_results = $stmt->fetchAll();

        $usn_sql = $user_results[0]['usn'];
        //echo $_SESSION['username'].$_SESSION['usn']."test";
        //die("hello");

        $sql = "UPDATE Devices SET usn = 1 WHERE usn = $usn_sql";
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();
       

        $sql = "DELETE FROM Users WHERE username = '$username_sql'";
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();


        if($results == NULL){
            $data = array("message" => "true");
            echo json_encode($data);
        }
        else{
            $data = array("message" => "false");
            echo json_encode($data);
        }

    }

    public function check_pwd_app(Request $request, Response $response, $args)
    {
        $username_sql = $_POST['username'];
        $password_sql = $_POST['current_pwd'];

        $sql = "select h_password from Users where username = '$username_sql'";
        $stmt = $this->em->getConnection()->query($sql);
        $stmt->execute();

        $results = $stmt->fetchAll();

        if(password_verify($password_sql, $results[0]['h_password'])){
           $data = array("message" => "true");
            echo json_encode($data);

        }
        else{
            $data = array("message" => "false");
            echo json_encode($data);
        }
    }

    public function change_pwd_app(Request $request, Response $response, $args)
    {
        $username_sql = $_POST['username'];
        $password_sql = $_POST['new_pwd'];

        $nonce = password_hash($password_sql, PASSWORD_DEFAULT);

        $sql = "UPDATE Users SET h_password = '$nonce', loginflag = 'F' WHERE username = '$username_sql'";
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();
       
        session_unset();
        session_destroy();  
        $data = array("message" => "true");
        echo json_encode($data);  

    }

}

