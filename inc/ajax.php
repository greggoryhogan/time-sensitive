<?php 
/*
Functions related to ajax actions
*/

//add_action( 'wp_ajax_nopriv_eos_onboard_user', 'eos_onboard_user' );
add_action( 'wp_ajax_eos_onboard_user', 'eos_onboard_user' ); //public
function eos_onboard_user() {
    $user_id = $_POST['user_id'];
    $user_role = getUserRole($user_id);
    if($user_role == 'subscriber') {
        $message = get_option('eos_mentee_email_text');
        $subject = get_option('eos_mentee_email_subject');
    } elseif($user_role == 'contributor') {
        $message = get_option('eos_mentor_email_text');
        $subject = get_option('eos_mentor_email_subject');
    }
    $user = get_user_by('id',$user_id);
    $email = $user->user_email;
    $sent = wp_mail($email, $subject, $message);
    if($sent) {
        add_user_meta($user_id,'sentonboarding',1,true);
        echo 'Success';
    } else {
        echo 'error';
    }
    wp_die();
}

//add_action( 'wp_ajax_nopriv_eos_test_email', 'eos_test_email' );
add_action( 'wp_ajax_eos_test_email', 'eos_test_email' ); //public
function eos_test_email() {
    $email = $_POST['email'];
    $subject = $_POST['subject'];
    $message = format_eos_message($_POST['message'],0);
    $message = nl2br($message, false);
    $sent = wp_mail($email, $subject, $message);
    if($sent) {
        echo 'success';
    } else {
        echo 'error';
    }    
    wp_die();
}

function format_eos_message($message, $user = 0) {
    $message = str_replace('{{login url}}',get_bloginfo('url').'/login/',$message);
    $message = str_replace('{{website}}',get_bloginfo('url'),$message);
    $message = str_replace('{{signature}}',get_option('eos_signature'),$message);

    if($user > 0) {
        //do user related functions
    } else {    
        $message = str_replace('{{first name}}','User',$message);
        $message = str_replace('{{last name}}','Lastname',$message);
        $message = str_replace('{{email}}','sample@email.com',$message);
        $message = str_replace('{{athlete mentor}}','Your Mentor',$message);
    }
    return $message;
}
?>