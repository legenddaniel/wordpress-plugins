<?php

defined('ABSPATH') || exit;

class Ibid_Auction_Email
{
    private $template;

    public function __construct($template)
    {
        $this->template = $template;
    }

    /**
     * Create email subject
     * @return string
     */
    private function subject()
    {
        switch ($this->template) {
            case 'signup':
                return 'You have successfully created your account';
            default:
                throw new Exception('Invalid parameter passed!');
        }
    }

    /**
     * Create email template
     * @return string
     */
    private function content()
    {
        switch ($this->template) {
            case 'signup':
                return <<<EOD
                    <p>You have successfully created your account</p>
                    <p>Invoice printed</p>
                EOD;
            default:
                throw new Exception('Invalid parameter passed!');
        }
    }

    /**
     * Send email
     * @param string $email
     * @param array $attachments
     */
    public function send($email, $attachments)
    {
        if (!$email) {
            throw new Exception('Invalid email!');
        }
        return wp_mail(
            $email,
            $this->subject(),
            $this->content(),
            array('Content-Type: text/html; charset=UTF-8'),
            $attachments
        );
    }
}
