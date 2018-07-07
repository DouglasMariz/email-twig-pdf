<?php

namespace DouglasMariz\Email\Twig\Pdf;

/**
 * Email
 * Email Submission Abstraction Class
 *
 * @author Douglas Mariz <douglasmariz.developer@gmail.com>
 * @version 1.0 - dev-master
 */

use Swift_Message;
use Swift_SmtpTransport;
use Swift_Mailer;
use Swift_Attachment;

use Twig_Loader_Filesystem;
use Twig_Environment;

use Spipu\Html2Pdf\Html2Pdf;

class EmailTwigPdf_Email
{

    private $userName;
    private $password;
    private $from;
    private $fromName;

    private $smtp;
    private $port;
    private $encryption = "ssl";

    private $to;
    private $toName;
    private $subject;

    private $template;
    private $templateTypes;
    private $templateStringHtml;
    private $templateStringText;
    private $templatePath;

    private $context;

    private $pdf;
    private $sendPdf;
    private $fileNamePdf;

    protected $loader;
    protected $twig;

    /**
     * EmailTwigPdf_Email constructor.
     * @param EmailTwigPdf_Auth $auth
     * @param EmailTwigPdf_Config|null $config
     */
    public function __construct(EmailTwigPdf_Auth $auth, EmailTwigPdf_Config $config = null)
    {
        $this->setLocaleDate();
        $this->setEmailSettings($auth, $config);

        $this->loader = new Twig_Loader_Filesystem($this->path());
        $this->twig = new Twig_Environment($this->loader);
    }

    /**
     * @return mixed
     */
    public function sendMail()
    {
        try {
            $this->swift_transport = (new Swift_SmtpTransport($this->smtp, $this->port, $this->encryption))
                ->setUsername($this->userName)
                ->setPassword($this->password)
                ->setStreamOptions(array('ssl' => array('allow_self_signed' => true, 'verify_peer' => false)));

            $this->sendPdf();
            $this->setSwiftMessage();
            $this->setSwiftMailer();
            return $this->swift_mailer->send($this->swift_message);

        } catch (\Swift_RfcComplianceException $complianceException) {
            $this->pdfCallBack();
            return "O endereço de email $this->to não está em conformidade com RFC 2822, 3.6.2";
        } catch (EmailTwigPdf_Exception $emailTwigPdf_Exception) {
            $this->pdfCallBack();
            return $emailTwigPdf_Exception->getMessage();
        } catch (\Exception $e) {
            $this->pdfCallBack();
            return 'Falha no envio.';
        }
    }

    /**
     * Create transport
     */
    private function setSwiftMailer()
    {
        $this->swift_mailer = new Swift_Mailer($this->swift_transport);
    }

    /**
     * @throws EmailTwigPdf_Exception
     * @throws \Exception
     */
    private function setSwiftMessage()
    {
        $this->swift_message = (new Swift_Message())
            ->setSubject($this->getSubject())
            ->setFrom([$this->from => $this->fromName])
            ->setTo($this->getTo(), $this->toName)
            ->setContentType("multipart/alternative");

        $this->setAttachment();
        $this->validBlock();
        $this->validTemplate();
    }

    /**
     * @return string
     * @throws EmailTwigPdf_Exception
     * @throws \Exception
     * @throws \Throwable
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    private function getBodyHtml()
    {
        if ($this->hasTemplateFile()) {
            $template = $this->twig->render($this->getTemplateFile('html'), $this->context);
        } else {
            $template = $this->twig->createTemplate($this->getTemplateString('html'))->render($this->context);
        }
        return $template;
    }

    /**
     * @return string
     * @throws EmailTwigPdf_Exception
     * @throws \Exception
     * @throws \Throwable
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    private function getBodyText()
    {
        if ($this->hasTemplateFile()) {
            $template = $this->twig->render($this->getTemplateFile('text'), $this->context);
        } else {
            $template = $this->twig->createTemplate($this->getTemplateString('text'))->render($this->context);
        }
        return $template;
    }

    /**
     * @param $to
     * @param null $name
     * @return $this
     */
    public function setTo($to, $name = null)
    {
        $this->to = $to;
        $this->toName = $name;
        return $this;
    }

    /**
     * @param string $block
     * @return $this
     */
    public function setBlock($block = 'default')
    {
        if (isset($this->context)) {
            $block = [
                'block' => $block,
            ];
            $this->context = array_merge($this->context, $block);
        }
        return $this;
    }

    /**
     * @return mixed
     * @throws EmailTwigPdf_Exception
     */
    private function getTo()
    {
        if (!isset($this->to)) {
            throw new EmailTwigPdf_Exception(EmailTwigPdf_Mesages::SEND_TO, EmailTwigPdf_ErrorsCode::SEND_TO);
        }
        return $this->to;
    }

    /**
     * @param $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return mixed
     * @throws EmailTwigPdf_Exception
     */
    private function getSubject()
    {
        if (!isset($this->subject)) {
            throw new EmailTwigPdf_Exception(EmailTwigPdf_Mesages::SUBJECT, EmailTwigPdf_ErrorsCode::SUBJECT);
        }
        return $this->subject;
    }

    /**
     * @param $extension
     * @return mixed
     * @throws EmailTwigPdf_Exception
     */
    private function getTemplateFile($extension)
    {
        if (!isset($this->template) && is_array($this->templateTypes) && !empty($this->templateTypes)) {
            throw new EmailTwigPdf_Exception(EmailTwigPdf_Mesages::TEMPLATE_NOT_FOUND, EmailTwigPdf_ErrorsCode::TEMPLATE_NOT_FOUND);
        }
        return $this->templateTypes[$extension];
    }

    /**
     * Include template through file
     * @param $name
     * @param null $context
     * @return $this
     */
    public function setTemplateFile($name, $context = null)
    {
        $this->template = $name;
        $this->context = (isset($context)) ? $context : [];
        $this->existsTemplate();
        return $this;
    }

    /**
     * @param $extension
     * @return mixed
     * @throws EmailTwigPdf_Exception
     */
    private function getTemplateString($extension)
    {
        if (!isset($this->templateStringHtml) && !isset($this->templateStringText)) {
            throw new EmailTwigPdf_Exception(EmailTwigPdf_Mesages::TEMPLATE_STRING_NOT_FOUND, EmailTwigPdf_ErrorsCode::TEMPLATE_NOT_FOUND);
        }
        switch ($extension) {
            case 'html':
                $template = $this->templateStringHtml;
                break;
            case 'text':
                $template = $this->templateStringText;
                break;
        }
        return $template;
    }

    /**
     * Include template through String HTML
     *
     * @param $string
     * @param null $context
     * @return $this
     */
    public function setTemplateStringHtml($string, $context = null)
    {
        $this->templateStringHtml = $string;
        $this->context = (isset($context)) ? $context : [];
        return $this;
    }

    /**
     * Include template through String TEXT
     *
     * @param $string
     * @param null $context
     * @return $this
     */
    public function setTemplateStringText($string, $context = null)
    {
        $this->templateStringText = $string;
        $this->context = (isset($context)) ? $context : [];
        return $this;
    }

    /**
     * Validate Template File
     *
     * @return bool
     */
    private function hasTemplateFile()
    {
        return isset($this->template);
    }

    /**
     * Validate Template String Html
     *
     * @return bool
     */
    private function hasTemplateStringHtml()
    {
        return isset($this->templateStringHtml);
    }

    /**
     * Validate Template String Text
     *
     * @return bool
     */
    private function hasTemplateStringText()
    {
        return isset($this->templateStringText);
    }

    /**
     * Includes css files in the template
     *
     * @param null $css
     * @return $this
     * @throws EmailTwigPdf_Exception
     */
    public function setCssFile($css = null)
    {
        $cssDefault = 'default';
        $cssExplode = explode('-', $css);

        if (count($cssExplode) < 2) {
            throw new EmailTwigPdf_Exception(EmailTwigPdf_Mesages::FILE_NAME, EmailTwigPdf_ErrorsCode::FILE_NAME);
        }
        $path = $this->path() . '/' . $cssExplode[0] . '/css/' . $cssExplode[1];
        $file = $path . '.css.twig';
        $name = (file_exists($file)) ? $cssExplode[0] . '/css/' . $cssExplode[1] : $cssDefault . '/css/' . $cssDefault;
        $path = $name . '.css.twig';
        if (isset($this->context)) {
            $css = [
                'css' => $path,
            ];
            $this->context = array_merge($this->context, $css);
        }
        return $this;
    }

    /**
     * Includes date and hour in the template
     *
     * @return $this
     */
    public function setDateHour()
    {
        if (isset($this->context)) {
            $data = [
                'data' => strftime('%A, %d de %B de %Y', strtotime('today')),
                'hora' => date('H:i:s')
            ];
            $this->context = array_merge($this->context, $data);
        }
        return $this;
    }

    /**
     * Set Locale pt-BR
     */
    private function setLocaleDate()
    {
        setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
        date_default_timezone_set('America/Recife');
    }

    /**
     * Include email settings
     *
     * @param EmailTwigPdf_Auth $auth
     * @param EmailTwigPdf_Config|null $config
     */
    private function setEmailSettings(EmailTwigPdf_Auth $auth, EmailTwigPdf_Config $config = null)
    {
        $this->userName = $auth->getUserName();
        $this->password = $auth->getPassword();
        $this->from = $auth->getFrom();
        $this->fromName = $auth->getFromName();
        $this->smtp = $auth->getSmtp();
        $this->port = $auth->getPort();
        $this->templatePath = (isset($config)) ? $config->getTemplatePath() : null;
    }

    /**
     * @return string
     */
    private function path()
    {
        $path = '\..\templates\email';
        return ($this->templatePath) ? $this->templatePath : __DIR__ . $path;
    }

    /**
     * Verify template
     */
    private function existsTemplate()
    {
        $nameExplode = explode('-', $this->template);
        $newPath = $this->path() . '/';
        $files['html'] = $nameExplode[0] . '/html' . '/' . $nameExplode[1] . '.html.twig';
        $files['text'] = $nameExplode[0] . '/text' . '/' . $nameExplode[1] . '.text.twig';

        foreach ($files as $key => $file) {
            $this->templateTypes[$key] = $file;
            if (!file_exists($newPath . $file)) {
                $this->templateTypes[$key] = '/default/' . $key . '/default.' . $key . '.twig';
                $defaultFiles[] = $this->path() . '/default/' . $key . '/default.' . $key . '.twig';
            }
        }
        if (isset($defaultFiles)) {
            foreach ($defaultFiles as $defaultFile) {
                if (!file_exists($defaultFile)) {
                    $this->template = null;
                }
            }
        }
    }

    /**
     * Print the Template Html on the screen
     */
    public function showTemplateHtml()
    {
        echo $this->getBodyHtml();
        exit;
    }


    /**
     * Print the Template text on the screen
     */
    public function showTemplateText()
    {
        echo $this->getBodyText();
        exit;
    }

    /**
     * @param $fileName
     * @return $this
     */
    public function setFileNamePdf($fileName)
    {
        $fileName = str_replace(" ",
            "_",
            preg_replace("/&([a-z])[a-z]+;/i",
                "$1",
                htmlentities(trim($fileName))
            )
        );
        $this->fileNamePdf = $fileName;
        return $this;
    }

    /**
     * @return string
     */
    private function getFileNamePdf()
    {
        if (!isset($this->fileNamePdf)) {
            $fileNamePdf = 'documento-' . date('YmdHis');
        } else {
            $fileNamePdf = $this->fileNamePdf . '-' . date('YmdHis');
        }
        return $fileNamePdf;
    }

    /**
     * @return $this
     */
    public function pdf()
    {
        $this->sendPdf = true;
        return $this;
    }

    /**
     * @throws EmailTwigPdf_Exception
     */
    private function sendPdf()
    {
        (!isset($this->sendPdf)) ?: $this->generatePdf();
    }

    /**
     * Generate document PDF
     *
     * @throws EmailTwigPdf_Exception
     * @throws \Exception
     * @throws \Throwable
     */
    private function generatePdf()
    {
        $this->validateFolderPdf();
        try {
            $html2pdf = new Html2Pdf('P', 'A4', 'pt');
            $html2pdf->writeHTML($this->getBodyHtml());
            $html2pdf->output($this->pdf, 'F');
        } catch (\Exception $e) {
            throw new EmailTwigPdf_Exception(EmailTwigPdf_Mesages::GENERATE_PDF, EmailTwigPdf_ErrorsCode::GENERATE_PDF);
        }
    }

    /**
     * Validate folder to generate PDF file
     *
     * @throws EmailTwigPdf_Exception
     */
    private function validateFolderPdf()
    {
        $pathPdf = $this->path() . '/pdf';
        $pathNames = explode('-', $this->template);
        $pathNames[] = $this->getTo();
        $dir = $pathPdf;
        foreach ($pathNames as $pathName) {
            $dir .= '/' . $pathName;
            if (!is_dir($dir)) {
                if (!@mkdir($dir, 0755)) {
                    throw new EmailTwigPdf_Exception(EmailTwigPdf_Mesages::CREATE_PATH . "$dir.", EmailTwigPdf_ErrorsCode::CREATE_PATH);
                }
            }
        }
        if (!is_dir($dir)) {
            throw new EmailTwigPdf_Exception(EmailTwigPdf_Mesages::PATH_NOT_FOUND, EmailTwigPdf_ErrorsCode::PATH_NOT_FOUND);
        }
        $this->pdf = $dir . '/' . $this->getFileNamePdf() . '.pdf';
    }

    /**
     * Valid Block to the template
     */
    private function validBlock()
    {
        if (is_array($this->context) && !array_key_exists('block', $this->context)) {
            $this->setBlock();
        }
    }

    /**
     * Set Attachment
     */
    private function setAttachment()
    {
        if (isset($this->pdf) && !empty($this->pdf)) {
            $this->swift_message->attach(Swift_Attachment::fromPath($this->pdf));
        }
    }

    /**
     * @throws EmailTwigPdf_Exception
     * @throws \Exception
     * @throws \Throwable
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    private function validTemplate()
    {
        if (!$this->hasTemplateFile() && !$this->hasTemplateStringText() && !$this->hasTemplateStringHtml()) {
            throw new EmailTwigPdf_Exception(EmailTwigPdf_Mesages::TEMPLATE_NOT_FOUND, EmailTwigPdf_ErrorsCode::TEMPLATE_NOT_FOUND);
        }
        if ($this->hasTemplateFile()) {
            $this->swift_message
                ->setBody($this->getBodyHtml(), 'text/html')
                ->addPart($this->getBodyText(), 'text/plain');
        }
        if ($this->hasTemplateStringText()) {
            $this->swift_message->setBody($this->getBodyText(), 'text/plain');
        }
        if ($this->hasTemplateStringHtml()) {
            $this->swift_message->setBody($this->getBodyHtml(), 'text/html');
        }
    }

    /**
     * Remove PDF file
     */
    private function pdfCallBack()
    {
        if (file_exists($this->pdf)) {
            unlink($this->pdf);
        }
    }

}
