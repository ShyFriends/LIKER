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

    public function register(Request $request, Response $response, $args)
    {


        $username_sql = $_POST['username'];
        $password_sql = $_POST['password'];
        $email_sql = $_POST['email'];
        $phone_number_sql = $_POST['phone_number'];
        $birth_sql = $_POST['birth'];
        $gender_sql = 'M';


        $sql = "insert into Users(username, h_password, email, birth, phone_number, gender, loginflag) values ('$username_sql','$password_sql','$email_sql','$birth_sql','$phone_number_sql','$gender_sql', 'T')";
        $stmt = $this->em->getConnection()->query($sql);
        $stmt->execute();

        // $results = $stmt->fetchAll();

        print_r($results);
        $this->view->render($response, 'success.twig');
        return $response;
    }

    public function signin(Request $request, Response $response, $args)
    {

        // print_r($_POST['password']);
        
        $username_sql = $_POST['username'];
        $password_sql = $_POST['password'];



        $sql = "select h_password from Users where username = '$username_sql'";
        $stmt = $this->em->getConnection()->query($sql);
        $stmt->execute();

        $results = $stmt->fetchAll();

        if($results[0]['h_password']==$password_sql){
            $json_array = array("status" => "success");
            $this->view->render($response, 'home.twig');
            return $response;

        }
        else{
            $json_array = array("status" => "fail", "message" => "User already exists");
            $this->view->render($response, 'errorpage.twig');
            return $response;
        }
        // print_r($results);
        // print_r($results[0]['h_password']);
        // echo "i See~";



        // return $response->withStatus(200)
        // ->withHeader('Content-Type','application/json')
        // ->write(json_encode($json_array));
        
        //$this->view->render($response, 'home.twig');
        //return $response;
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
        $mail->Username   = 'wkdgurwls1211@gmail.com';                     // SMTP username
        $mail->Password   = 'gurwls1080524';                               // SMTP password
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
