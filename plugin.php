<?php
/*
Plugin Name: Gate Keeper
Plugin URI: https://github.com/Aternus/yourls-gate-keeper
Description: Adds reCAPTCHA v3 to the YOURLS Admin Area.
Version: 1.0
Author: Aternus
Author URI: https://atern.us/
*/

// No direct call
if (!defined('YOURLS_ABSPATH')) {
    die();
}

if (!defined('GATE_KEEPER_RECAPTCHA_V3_SITE_KEY')) {
    return;
}
if (!defined('GATE_KEEPER_RECAPTCHA_V3_SECRET_KEY')) {
    return;
}

// reCAPTCHA script to the head section of the HTML file
yourls_add_action('html_head', 'recaptcha_v3_html_head');
function recaptcha_v3_html_head($context)
{
    if ($context !== 'login') {
        return;
    }
    echo '<script src="https://www.google.com/recaptcha/api.js?render=' . GATE_KEEPER_RECAPTCHA_V3_SITE_KEY . '"></script>';
}

// reCAPTCHA widget to the YOURLS admin login form
yourls_add_action('login_form_bottom', 'recaptcha_v3_login_form');
function recaptcha_v3_login_form()
{
    echo '<input type="hidden" name="token" id="tokenInput">';
}

// Initialize reCAPTCHA widget
yourls_add_action('login_form_end', 'recaptcha_v3_inject_script');
function recaptcha_v3_inject_script()
{
    echo '<script>
        grecaptcha.ready(function() {
            grecaptcha.execute(\'' . GATE_KEEPER_RECAPTCHA_V3_SITE_KEY . '\', {action: \'submit\'}).then(function(token) {
                document.getElementById(\'tokenInput\').value = token;
            });
        });
    </script>';
}


// Initialize reCAPTCHA widget and verify user's response
yourls_add_action('pre_login_username_password', 'recaptcha_v3_validation');
function recaptcha_v3_validation()
{
    $token = $_POST['token'];

    // call curl to POST request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt(
        $ch,
        CURLOPT_POSTFIELDS,
        http_build_query(array('secret' => GATE_KEEPER_RECAPTCHA_V3_SECRET_KEY, 'response' => $token))
    );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    $arrResponse = json_decode($response, true);

    // verify the response
    if ($arrResponse["success"] == '1' && $arrResponse["score"] >= 0.5) {
        // reCAPTCHA succeeded
        return true;
    } else {
        // reCAPTCHA failed
        yourls_login_screen($error_msg = 'reCAPTCHA verification failed');
        yourls_die('reCAPTCHA verification failed. Please try again.');
        return false;
    }
}
