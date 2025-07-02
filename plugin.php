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

/**
 * Outputs specific HTML and JavaScript for the head section of a page based on the given context.
 *
 * @param array $args An array containing contextual information. The first element should specify the context, such as 'login'.
 * @return void This function does not return a value. It directly outputs content.
 */
function gate_keeper_html_head($args)
{
    list($context) = $args;
    if ($context === 'login') {
        echo '<script src="https://www.google.com/recaptcha/api.js?render=' . GATE_KEEPER_RECAPTCHA_V3_SITE_KEY . '"></script>';
    }
}

yourls_add_action('html_head', 'gate_keeper_html_head');


/**
 * Displays a hidden input field for a security token in a login form.
 *
 * @return void
 */
function gate_keeper_add_token_input()
{
    echo '<input type="hidden" name="token" id="tokenInput">';
}

yourls_add_action('login_form_bottom', 'gate_keeper_add_token_input');


/**
 * Embeds a JavaScript snippet to generate and set a reCAPTCHA v3 token.
 * The token is generated using the reCAPTCHA API and is assigned to a specific input field in the HTML document.
 *
 * @return void
 */
function gate_keeper_generate_and_insert_token()
{
    echo '<script>
        grecaptcha.ready(function() {
            grecaptcha.execute(\'' . GATE_KEEPER_RECAPTCHA_V3_SITE_KEY . '\', {action: \'submit\'}).then(function(token) {
                document.getElementById(\'tokenInput\').value = token;
            });
        });
    </script>';
}

yourls_add_action('login_form_end', 'gate_keeper_generate_and_insert_token');


/**
 * Validates a reCAPTCHA v3 token by sending it to the Google reCAPTCHA API.
 * The method verifies the token's authenticity and checks the score to determine if the request is valid.
 * If the validation fails, it triggers an error message and halts execution.
 *
 * @return bool Returns true if the reCAPTCHA verification is successful and the score is above the threshold; otherwise, returns false.
 */
function gate_keeper_validate_token()
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

yourls_add_action('pre_login_username_password', 'gate_keeper_validate_token');
