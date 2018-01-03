<?php

require_once(__DIR__.'/../phpMailer/PHPMailerAutoload.php');




function sendSubscriptionEmail($first,$email,$message,$subject){

    require_once (__DIR__.'/Mandrill.php'); //Not required with Composer
    try {
        $fileEndEnd = file_get_contents(__DIR__.'/subscribe_template.html');
        $search = array('[TITLE]','[Name]','[MESSAGE]');
        $first = mb_convert_encoding($first, 'HTML-ENTITIES', "UTF-8");
        $replace = array('La constituante',$first,$message);
        $fileEndEnd = str_replace($search, $replace, $fileEndEnd);
        $fileEndEnd = mb_convert_encoding($fileEndEnd, 'HTML-ENTITIES', "UTF-8");
        $subject =  'Confirmez votre adresse email';
        $textAltBody = 'Bonjour '.$first.'\r\n'.'Merci pour votre inscription sur la constituante.\r\nVeuillez trouver ci-dessous le code d\'activation afin de confirmer votre inscription.\r\n'.$message.'\r\nConstitutionnellement,\r\n';
        $mandrill = new Mandrill('API_KEY_HERE');
        $message = array(
            'html' => $fileEndEnd,
            'text' => $textAltBody,
            'subject' => $subject,
            'from_email' => 'contact@laconstituante.fr',
            'from_name' => 'La constituante',
            'to' => array(
                array(
                    'email' => $email,
                    'name' => $first,
                    'type' => 'to'
                )
            ),
            'headers' => array('Reply-To' => 'contact@laconstituante.fr'),
            'important' => false,
            'track_opens' => null,
            'track_clicks' => null,
            'auto_text' => null,
            'auto_html' => null,
            'inline_css' => null,
            'url_strip_qs' => null,
            'preserve_recipients' => null,
            'view_content_link' => null,
            'bcc_address' => null,
            'tracking_domain' => null,
            'signing_domain' => null,
            'return_path_domain' => null,
            'merge' => true,
            'merge_language' => 'mailchimp',
            'global_merge_vars' => array(
                array(
                    'name' => 'merge1',
                    'content' => 'merge1 content'
                )
            ),
            'merge_vars' => null,
            'tags' => array('create-account'),
            'subaccount' => null,
            'google_analytics_domains' => null,
            'google_analytics_campaign' => null,
            'metadata' => array('website' => 'www.laconstituante.fr'),
            'recipient_metadata' => null,
            'attachments' => null,
            'images' => null
        );
        $async = false;
        $ip_pool = 'Main Pool';
        $send_at = null;
        $result = $mandrill->messages->send($message);
        
        error_log(print_r($result,true),3,__DIR__.'/mail-error.log');
        if($result && isset($result['status']) ){
            return $result['status'];
        }
        return false;
    }
    catch(Mandrill_Error $e) {
        // Mandrill errors are thrown as exceptions
        //echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
        //// A mandrill error occurred: Mandrill_Unknown_Subaccount - No subaccount exists with the id 'customer-123'
        //throw $e;
        error_log(print_r($result,true),3,__DIR__.'/mail-error.log');
    }


}

