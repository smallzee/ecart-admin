<?php
header('Access-Control-Allow-Origin: *');
include_once '../includes/functions.php';
$functions1 = new functions();
$system_configs = $functions1->get_system_configs();

if ($system_configs['smtp-from-mail'] != '' || isset($system_configs['smtp-from-mail'])) {
    include_once '../library/class.phpmailer.php';
}

function send_email($to, $subject, $message)
{
    include_once '../includes/crud.php';
    $db = new Database();
    $db->connect();
    include_once '../includes/functions.php';
    $functions11 = new functions();
    $system_configs = $functions11->get_system_configs();


    $app_name = $system_configs['app_name'];
    $from_mail = $system_configs['from_mail'];
    $reply_to = $system_configs['reply_to'];

    if ($system_configs['smtp-from-mail'] == '' || !isset($system_configs['smtp-from-mail'])) {
        //send email
        $headers = "From: " . $app_name . "<" . $from_mail . ">\n";
        $headers .= "Reply-To: " . $reply_to . "\n";
        $headers .= "MIME-Version: 1.0\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\n";
        if (!mail($to, $subject, $message, $headers))
            return false;
        else
            return true;
    } else {
        $smtp_from_mail = $system_configs['smtp-from-mail'];
        $smtp_reply_to = $system_configs['smtp-reply-to'];
        $smtp_email_password = $system_configs['smtp-email-password'];
        $smtp_host = $system_configs['smtp-host'];
        $smtp_port = $system_configs['smtp-port'];
        $smtp_content_type = ($system_configs['smtp-content-type'] == 'html') ? true : '';
        $smtp_encryption_type = $system_configs['smtp-encryption-type'];
        $app_name = $system_configs['app_name'];
        $mail = new PHPMailer(); // create a new object
        $mail->IsSMTP(); // enable SMTP
        $mail->SMTPAuth = true; // authentication enabled
        $mail->SMTPSecure = $smtp_encryption_type; // secure transfer enabled REQUIRED for Gmail
        $mail->Host = $smtp_host;
        $mail->Port = $smtp_port; // or 587
        if ($smtp_content_type == '') {
            $mail->IsHTML(false);
        } else {
            $mail->IsHTML(true);
        }
        $mail->Username = $smtp_from_mail;
        $mail->Password = $smtp_email_password;
        $mail->SetFrom($smtp_from_mail);
        $mail->Subject = $subject;
        $mail->Body = $message;
        $mail->AddAddress($to, $app_name);
        $mail->addReplyTo($smtp_reply_to, $app_name);
        if ($mail->send()) {
            return true;
        } else {
            return false;
        }
    }
}


function send_email_with_template($to, $subject, $item_data1, $order_data)
{
    include_once '../includes/crud.php';
    $db = new Database();
    $db->connect();
    include_once '../includes/functions.php';
    $functions111 = new functions();
    $system_configs = $functions111->get_system_configs();

    $app_name = $system_configs['app_name'];
    $from_mail = $system_configs['from_mail'];
    $reply_to = $system_configs['reply_to'];


    ob_start();
    include 'email-templates/order-receipt.php';
    $message = ob_get_contents();
    ob_end_clean();

    //send email
    $headers = "From: " . $app_name . "<" . $from_mail . ">\n";
    $headers .= "Reply-To: " . $reply_to . "\n";
    $headers .= "MIME-Version: 1.0\n";
    $headers .= "Content-Type: text/html; charset=ISO-8859-1\n";
    if (!mail($to, $subject, $message, $headers))
        return false;
    else
        return true;
}

function send_smtp_mail($to, $subject, $item_data1, $order_data)
{
    include_once '../includes/functions.php';
    $functions1 = new functions();
    $system_configs = $functions1->get_system_configs();
    if ($system_configs['smtp-from-mail'] == '' || !isset($system_configs['smtp-from-mail'])) {
        if (send_email_with_template($to, $subject, $item_data1, $order_data)) {
            return true;
        } else {
            return false;
        }
    } else {
        $smtp_from_mail = $system_configs['smtp-from-mail'];
        $smtp_reply_to = $system_configs['smtp-reply-to'];
        $smtp_email_password = $system_configs['smtp-email-password'];
        $smtp_host = $system_configs['smtp-host'];
        $smtp_port = $system_configs['smtp-port'];
        $smtp_content_type = ($system_configs['smtp-content-type'] == 'html') ? true : '';
        $smtp_encryption_type = $system_configs['smtp-encryption-type'];
        $app_name = $system_configs['app_name'];
        ob_start();
        include 'email-templates/order-receipt.php';
        $message = ob_get_contents();
        ob_end_clean();
        $mail1 = new PHPMailer(); // create a new object
        $mail1->IsSMTP(); // enable SMTP
        $mail1->SMTPAuth = true; // authentication enabled
        $mail1->SMTPSecure = $smtp_encryption_type; // secure transfer enabled REQUIRED for Gmail
        $mail1->Host = $smtp_host;
        $mail1->Port = $smtp_port; // or 587
        if ($smtp_content_type == '') {
            $mail1->IsHTML(false);
        } else {
            $mail1->IsHTML(true);
        }
        $mail1->Username = $smtp_from_mail;
        $mail1->Password = $smtp_email_password;
        $mail1->SetFrom($smtp_from_mail);
        $mail1->Subject = $subject;
        $mail1->Body = $message;
        $mail1->AddAddress($to, $app_name);
        $mail1->addReplyTo($smtp_reply_to, $app_name);
        if ($mail1->send()) {
            return true;
        } else {
            return false;
        }
    }
}
