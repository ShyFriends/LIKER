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
}
