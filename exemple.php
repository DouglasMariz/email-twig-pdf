<?php

require_once('vendor/autoload.php');

use \DouglasMariz\Email\Twig\Pdf\EmailTwigPdf_Email;
use \DouglasMariz\Email\Twig\Pdf\EmailTwigPdf_Auth;
use \DouglasMariz\Email\Twig\Pdf\EmailTwigPdf_Config;

try {
    $credentials = [
        'userName' => 'user_name',
        'password' => 'password',
        'from' => 'email@gmail.com',
        'fromName' => 'from_name',
        'smtp' => 'smtp.email.com',
        'port' => 465,
        'encryption' => 'ssl',
    ];
    $auth = new EmailTwigPdf_Auth($credentials);
    $config = new EmailTwigPdf_Config(['template_path' => __DIR__ . '/your-path']);

    $email = new EmailTwigPdf_Email($auth, $config);
    $context = [
        'nome' => 'Usuário',
        'titulo' => 'Título',
        'mensagem' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.
                        Blanditiis cum debitis et expedita fugit hic iure laboriosam laudantium.',
    ];
    $result = $email->setTo('email@gmail.com', 'Nome Destinatário')
        ->setSubject('Assunto do email')
        ->setTemplateFile('pasta-arquivo', $context)
        ->setCssFile('pasta-arquivo')
        ->setDateHour()
        ->pdf()
        ->sendMail();

    if($result)
        echo 'Enviado com sucesso!';

} catch (Exception $e) {
    echo $e->getMessage();
}
