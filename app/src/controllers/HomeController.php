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
        $this->logger->info("Home page action dispatched");

        $this->flash->addMessage('info', 'Sample flash message');

        $this->view->render($response, 'home.twig');
        return $response;
    }

    public function check_duplicate(Request $request, Response $response, $args)
    {
        $username_sql = $_GET['id'];

        $json_array = array("username"=>$_GET['id']);

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

        return $response->withStatus(200)
        ->withHeader('Content-Type', 'application/json')
        ->write(json_encode($json_array));
    }

    public function remove(Request $request, Response $response, $args)
    {
        $username_sql = $_GET['username'];
        // $username_sql = 'chocho';

        $sql = "select usn from Users where username = '$username_sql'";
        $stmt = $this->em->getConnection()->query($sql);
        $stmt->execute();

        $results = $stmt->fetchAll();


        $usn = $results[0]['usn'];

        $sql = "UPDATE Devices SET usn = 1 WHERE usn = $usn"; 
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();
        

        $sql = "DELETE FROM Users WHERE username = '$username_sql'"; 
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();


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

        return $response->withStatus(200)
        ->withHeader('Content-Type', 'application/json')
        ->write(json_encode($json_array));
    }


    public function userinfo(Request $request, Response $response, $args)
    {
        $username_sql = $_POST['username'];

        $sql = "select username, birth, gender, email, phone_number from Users where username = '$username_sql'";
        $stmt = $this->em->getConnection()->query($sql);
        $stmt->execute();

        $results = $stmt->fetchAll();
        $birth_sql = $results[0]['birth'];
        $gender_sql = $results[0]['gender'];
        $email_sql = $results[0]['email'];
        $phone_number_sql = $results[0]['phone_number'];

        $this->view->render($response, 'userinfo.twig', ['username'=>$username_sql, 'birth'=>$birth_sql, 'gender'=>$gender_sql, 'email'=>$email_sql, 'phone_number'=>$phone_number_sql ]);
        return $response;
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

        // $sql = "UPDATE Users SET loginflag = 'F' WHERE $_SESSION[]"; 
        // $stmt = $this->em->getConnection()->prepare($sql);
        // $stmt->execute();

        $this->view->render($response, 'signup.twig');
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

            $this->view->render($response, 'success.twig');
            return $response;
        }
        else{
            $this->view->render($response, 'errorpage.twig');
            return $response;
        }



    }

    public function signin(Request $request, Response $response, $args)
    {

        // print_r($_POST['password']);
        
        $username_sql = $_POST['username'];
        $password_sql = $_POST['password'];

        $sql = "select h_password, usn from Users where username = '$username_sql'";
        $stmt = $this->em->getConnection()->query($sql);
        $stmt->execute();

        $results = $stmt->fetchAll();
        
        $data = ['username' => $username_sql];

        if(password_verify($password_sql, $results[0]['h_password'])){
            $json_array = array("status" => "success");
            session_start();
            $_SESSION['usn'] = $results[0]['usn'];
            $usn = $_SESSION['usn'];

            $this->view->render($response, 'home.twig', ['username'=>$username_sql], ['usn'=>$usn]);
            return $response;

        }
        else{
            $json_array = array("status" => "fail", "message" => "User already exists");
            $this->view->render($response, 'errorpage.twig');
            return $response;
        }

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
        $mail->Password   = 'gurwls1080524';                               // SMTP password
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
        $mail->Body    = '<h1>LIKER Message</h1><a href="http://192.168.33.99/confirm_verify?nonce=' . $nonce . '">Sign-In? Click me!</a><br>your temporary password is :' . $randomNum . '';
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
        $mail->Password   = 'gurwls1080524';                               // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
        $mail->Port       = 587;                                    // TCP port to connect to

        //Recipients
        $mail->setFrom('wkdgurwls1211@gmail.com', 'HyukJin');
        $mail->addAddress($results);     // Add a recipient

        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = 'Whattssssup bro~~';
        $mail->Body    = '<h1>LIKER Message</h1><a href="http://192.168.33.99/self_confirm_verify?nonce=' . $nonce . '">Sign-In? Click me!</a>';
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
