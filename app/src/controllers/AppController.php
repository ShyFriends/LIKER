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
            $this->logger->info("Home page action dispatched");
            $this->flash->addMessage('info', 'Sample flash message');
            $this->view->render($response, 'home.twig', ['username'=>$_SESSION['username']]);
            return $response;
        }
    }

    public function check_duplicate_app(Request $request, Response $response, $args)
    {
        $username_sql = $_GET['id'];

        $sql = "select * from Users where username = '$username_sql'";
        $stmt = $this->em->getConnection()->query($sql);
        $stmt->execute();

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

        $this->sendMail2($email_sql, $nonce);


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

            $json_array = array("status" => "success");
            $_SESSION['usn'] = $results[0]['usn'];
            $_SESSION['username'] = $results[0]['username'];
            //echo $_SESSION['usn'] . " is the session .... ";
            //die("test");
            $this->view->render($response, 'home.twig', ['username'=>$_SESSION['username']]);
            //$this->view->render($response, 'nav.twig', ['username'=>$_SESSION['username']]);
            //$this->view->render($response, 'sidebar.twig', ['username'=>$_SESSION['username']]);
            return $response;
        }
        else{
            $json_array = array("status" => "fail", "message" => "User already exists");
            $this->view->render($response, 'errorpage.twig');
            return $response;
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
        $usn_sql = $_SESSION['usn'];
        $sql = "UPDATE Users SET loginflag = 'F' WHERE usn = '$usn_sql'"; 
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();

        session_unset();
        session_destroy();

        $this->view->render($response, 'nav.twig');
        return $response;
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
        $mail->Username   = 'wkdgurwls1211@gmail.com';                     // SMTP username
        $mail->Password   = 'gurwls00!';                               // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
        $mail->Port       = 587;                                    // TCP port to connect to

        //Recipients
        $mail->setFrom('QI.8.teamb@gmail.com', 'HyukJin');
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


}
