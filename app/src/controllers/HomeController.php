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

    public function signup(Request $request, Response $response, $args)
    {
        $this->logger->info("Home page action dispatched");

        $this->flash->addMessage('info', 'Sample flash message');

        $this->view->render($response, 'signup.twig');
        return $response;
    }

    public function sendMail(Request $request, Response $response, $args)
    {
        $mail = new PHPMailer(true);

        try {
        //Server settings
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host       = 'smtp.gmail.com';                    // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $mail->Username   = 'overoverthis@gmail.com';                     // SMTP username
        $mail->Password   = 'thisoverover';                               // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
        $mail->Port       = 587;                                    // TCP port to connect to

        //Recipients
        $mail->setFrom('wkdgurwls1211@gmail.com', 'HyukJin');
        $mail->addAddress('wkdgurwls00@naver.com');     // Add a recipient
        //$mail->addAddress('ellen@example.com');               // Name is optional
        // $mail->addReplyTo('info@example.com', 'Information');
        // $mail->addCC('cc@example.com');
        // $mail->addBCC('bcc@example.com');

        // // Attachments
        // $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
        // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

        // Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = 'Whattssssup bro';
        $mail->Body    = '<h1>Thanksss a lot</h1>';
        $mail->AltBody = 'HeyHey~';

        $mail->send();
        echo 'Message has been sent';
        }
        catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

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
