<?php


namespace App\Services;


use Illuminate\Support\Facades\View;
use SendGrid\Mail\Mail;
use SendGrid\Mail\TypeException;

class EmailHandler
{
    /**
     * Retrieve the Sendgrid key
     * @return mixed
     */
    private function getKey()
    {
        return env('SENDGRID_API_KEY');
    }

    /**
     * This pushes email template to a recipient
     * @param array $emailConfig
     * @param string $bladeTemplate
     * @param array $bladeData
     * @return false
     */
    public function pushMail(array $emailConfig, string $bladeTemplate, array $bladeData = []): bool
    {
        try {

            $api_key = $this->getKey();

            $email = new Mail();
            $email->setFrom($emailConfig['sender_email'], $emailConfig['sender_name']);
            $email->setSubject($emailConfig['subject']);
            $email->addTo($emailConfig['recipient_email'], $emailConfig['recipient_name']);

            $view = View::make($bladeTemplate, $bladeData);
            $html = $view->render();//fetch the content of the blade template
            $email->addContent( "text/html", $html);
            $sendgrid = new \SendGrid($api_key);
            $response = $sendgrid->send($email);
//            print $response->statusCode() . "\n";
//            print_r($response->headers());
//            print $response->body() . "\n";
            return true;

        } catch (TypeException $e) {

            return false;
        }
    }
}
