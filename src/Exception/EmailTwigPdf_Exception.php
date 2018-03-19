<?php

namespace DouglasMariz\Email\Twig\Pdf;

/*
 * This file is part of EmailTwigPdf.
 * (c) 2018 Douglas Mariz
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * EmailTwigPdf Compliance Exception class.
 *
 * @author Douglas Mariz
 */
class EmailTwigPdf_Exception extends \Exception
{
    /**
     * Create a new EmailTwigPdfException with $message.
     *
     * @param string $message
     * @param int $code
     * @param \Exception $previous
     */
    public function __construct($message, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
