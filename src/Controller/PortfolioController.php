<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ContactType;
use PHPMailer\PHPMailer\PHPMailer;
use PHPUnit\Util\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PortfolioController extends AbstractController
{
    /**
     * @Route("/", name="portfolio")
     */
    public function index(Request $request, \Swift_Mailer $mailer)
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        $articles = $this->getDoctrine()->getRepository(Article::class)->findBy(
            ['isPublished' => true],
            ['publicationDate' => 'desc']
        );

        if($form->isSubmitted() && $form->isValid()){
            $contact = $form->getData();
            $from = $contact['email'];
            $name = $contact['name'];
            $content = $contact['message'];

            try {
            $mail = new PHPMailer(true);
            //$mail->isSMTP();
            $mail->SMTPAuth=true;
            $mail->SMTPSecure = 'ssl';
            $mail->Host = 'smtp.gmail.com';
            $mail->Port = '587';
            $mail->isHTML();
            $mail->Username = 'devlop.pev@gmail.com';
            $mail->Password = 'sony.figuig';
            $mail->setFrom('test@me.com');
            $mail->Subject = 'Nouveau Contact '.$name;
            $mail->Body = $from.'\n'.$content;
            $mail->addAddress('devlop.pev@gmail.com');


                $mail->send();
                echo 'Message has been sent';
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }

            return $this->render('portfolio/index.html.twig', [
                'articles' => $articles,
                'message' => 'Votre Message a ete envoyer avec succe! Merci',
                'contactForm'=>$form->createView()
            ]);

        }



        return $this->render('portfolio/index.html.twig', [
            'articles' => $articles,
            'contactForm'=>$form->createView()
        ]);
    }
}
