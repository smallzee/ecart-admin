<?php
include('../includes/crud.php');
$db = new Database();
$db->connect();
include_once('../includes/custom-functions.php');
$fun = new custom_functions;
include_once('../api-firebase/send-email.php');

if (isset($_POST['reset_password']) && $_POST['reset_password'] == 1) {
    if(ALLOW_MODIFICATION==0 && !defined (ALLOW_MODIFICATION)){
        echo '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
        return false;
    }
    $password = $db->escapeString($fun->xss_clean($_POST['password']));
    $password_hash = md5($password);
    $code = trim($db->escapeString($fun->xss_clean($_POST['code'])));

    if (empty($code) || !$fun->validate_code($code)) {
        $response['error'] = true;
        $response['message'] = "<div class='alert alert-danger'>Either link is invalid or expired</div>";
        echo json_encode($response);
        return false;
    } else {
        $user = $fun->get_user($code);
        $user_name = $user[0]['username'];
        $email = $user[0]['email'];
        $message = "Dear <b>" . $user_name . "</b> your password reset successfully. new password is <b>" . $password . "</b>.<br><b>Thank You.</b>";
        if (send_email($email, 'Password changed', $message)) {
            $fun->update_password($code, $password_hash);
            $fun->update_forgot_password_code($email, '');
            $response['error'] = false;
            $response['message'] = "<div class='alert alert-success'>Password changed successfully.Redirecting please wait...</div>";
            echo json_encode($response);
            return false;
        }
    }
}
