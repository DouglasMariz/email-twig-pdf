<?php
/**
 * Created by PhpStorm.
 * User: dougl
 * Date: 18/03/2018
 * Time: 18:31
 */

namespace DouglasMariz\Email\Twig\Pdf;


class EmailTwigPdf_Mesages
{
    /**
     * Basics configurations to send mail
     */
    const SEND_TO = "É necessário incluir o destinatário da mensagem!";
    const SUBJECT = "É necessário incluir o assunto da mensagem!";
    const TEMPLATE_NOT_FOUND = "Template não encontrado!";
    const TEMPLATE_STRING_NOT_FOUND = "Template \'HTML\' ou \'TEXT\' não encontrado!";
    const FILE_NAME = "Nome do arquivo inválido";
    const CREATE_PATH = "Não foi possível criar o diretório";
    const PATH_NOT_FOUND = "Não foi possível encontrar o diretório do PDF.";
    const GENERATE_PDF = "Falha ao gerar arquivo PDF para o e-mail";
}