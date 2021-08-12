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
            case 'signup_ok':
                return 'You have successfully created your account';
            case 'signup_error':
                return 'We can\'t verify your credit card';
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
            case 'signup_ok':
                return <<<EOD
                    <p>You have successfully created your account</p>
                    <p>Invoice printed</p>
                EOD;
            case 'signup_error':
                return <<<EOD
                    <p>We can't verify your credit card</p>
                    <p>Please sign up again</p>
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
