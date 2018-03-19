<?php
/**
 * Created by PhpStorm.
 * User: dougl
 * Date: 18/03/2018
 * Time: 18:31
 */

namespace DouglasMariz\Email\Twig\Pdf;


class EmailTwigPdf_ErrorsCode
{
    /**
     * Basics configurations to send mail
     */
    const SEND_TO = 1;
    const SUBJECT = 2;
    const TEMPLATE_NOT_FOUND = 3;
    const FILE_NAME = 4;
    const CREATE_PATH = 5;
    const PATH_NOT_FOUND = 6;
    const GENERATE_PDF = 7;
    const AUTH_CONFIG = 8;
    const CONFIG = 9;
}