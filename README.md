# email-twig-pdf
#### PHP Email Class: Biblioteca para enviar e-mail. "swiftmailer", "twig" e "html2pdf".
Email-Twig-PDF é uma classe de abstração para submissão de e-mail, gera PDF e usa Twig para renderizar o modelo.

### Como usar

Configurações de arquivos e pastas

    Verifique se os modelos existem no diretório base: "{DIRETORIO_BASE}/templates/email/{feature}/{fileName}".
    Se não existir, crie o(s) modelo(s) com extensões {.html.twig} para enviar mensagens HTML e {.text.twig} para uma mensagem simples sem tags html.
    
Class EmailTwigPdf_Config

    Você pode configurar um diretório base para armazenar os templates.
    Ex: $config = new EmailTwigPdf_Config(['template_path' => __DIR__.'/your-path']);
    
    OBS: Deve seguir a seguinte estrutura:
    DIRETORIO_BASE
        templates
            email
            
Class EmailTwigPdf_Auth

    Classe para colocar suas credenciais de acesso ao email do remetente.
    Ex: 
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

Template de Exemplo
    
    Já existe uma pasta chamada "templates" com um modelo default, de exemplo.

Uso simples da classe:

    require_once('vendor/autoload.php');
    
    use \DouglasMariz\Email\Twig\Pdf\EmailTwigPdf_Email;
    use \DouglasMariz\Email\Twig\Pdf\EmailTwigPdf_Auth;
    use \DouglasMariz\Email\Twig\Pdf\EmailTwigPdf_Config;
    
    try {
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
            ->setDateHour() //Adiciona ao template a data e hora no formato pt_BR, apenas se existir no template as variáveis {{data}} e {{hora}}
            ->pdf()
            ->sendMail();
    
    } catch (Exception $e) {
        echo $e->getMessage();
    }

Úteis

    $email->pdf(); // Para gerar um arquivo PDF e enviar como anexo ao email.
    $email->setFileNamePdf(); // Define o nome para o arquivo PDF.
    $email->setBlock(); // Define o BLOCO que será utilizado no template, caso esteja definido
        EX:
            {% if block == 'default' %}
                {% block default %}
                    {{ content }}
                {% endblock %}
            {% endif %}
            
    $email->showTemplateText(); // Renderiza na tela o template.
    $email->showTemplateHtml(); // Renderiza na tela o template.
    