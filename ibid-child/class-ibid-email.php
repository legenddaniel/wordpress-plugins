<?php

defined('ABSPATH') || exit;

class Ibid_Auction_Email
{
    /**
     * Create email subject
     * @param string $template
     * @return string
     */
    private function subject($template)
    {
        switch ($template) {
            case 'signup':
                return <<<EOD
                    <p>Invoice printed</p>
                EOD;
            default:
                throw new Exception('Invalid parameter passed!');
        }
    }

    /**
     * Create email template
     * @param string $template
     * @return string
     */
    private function content($template)
    {
        switch ($template) {
            case 'signup':
                return 'You have successfully created your account';
            default:
                throw new Exception('Invalid parameter passed!');
        }
    }

    /**
     * Send email
     * @param string $email
     * @param string $template
     * @param array $attachments
     */
    public static function send($email, $template, $attachments)
    {
        wp_mail(
            $email,
            self::subject($template),
            self::content($template),
            array('Content-Type: text/html; charset=UTF-8'),
            $attachments
        );
    }
}
