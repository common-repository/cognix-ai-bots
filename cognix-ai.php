<?php
/*
Plugin Name: Cognix.AI - AI Bots
Plugin URI: https://cognix.ai/#/wpPluginInfo
Description: Natural language based tools like chat bots/QA bots and other tools.
Version: 1.0.2
Author: Cognix.ai
Tested up to: WordPress version : 6.6
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The main plugin class.
 */
class Cognix_AI_Plugin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_footer', [ $this, 'add_custom_script_to_footer' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_menu', [ $this, 'add_menu_page' ] );
		$this->register_ajax_actions();
	}

	/**
	 * Adds a custom script to the footer.
	 */
	public function add_custom_script_to_footer() {
		$script = get_option( 'cognix_script' );
		if ( ! empty( $script ) ) {
			
            $allowed_html = array(
                'script' => array(
                    'type' => true,
                ),
                'chat-widget' => array(
                    'serviceUrl' => true,
                    'agent_id' => true,
                    'web_key' => true,
                ),
                'link' => array(
                    'rel' => true,
                    'href' => true,
                ),
            );
    
            echo wp_kses($script, $allowed_html);
		}
	}

	/**
	 * Registers plugin settings.
	 */
	public function register_settings() {
		register_setting( 'cognix_settings', 'cognix_user_consent' );
		register_setting( 'cognix_settings', 'cognix_base_url' );
	}

	/**
	 * Adds a menu page and a submenu page for the plugin settings.
	 */
	public function add_menu_page() {
		add_menu_page(
			'Cognix Tools',
			'Cognix Tools',
			'edit_posts',
			'cognix_tools',
			[ $this, 'render_main_page' ],
			'dashicons-forms',
			10
		);

		add_submenu_page(
			'cognix_tools',
			'Settings',
			'Settings',
			'manage_options',
			'cognix_settings',
			[ $this, 'render_settings_page' ]
		);
	}

	/**
	 * Renders the settings page. We'll just include the file here.
	 */
	public function render_settings_page() {
		include 'views/settings-page.php';
	}

	/**
	 * Registers AJAX actions for user registration and login.
	 */
	private function register_ajax_actions() {
		add_action( 'wp_ajax_cognix_register_user', [ $this, 'cognix_register_user' ] );
        add_action( 'wp_ajax_cognix_login_user',  [ $this, 'cognix_login_user' ] );
        add_action( 'wp_ajax_cognix_create_chat_bots', [ $this, 'cognix_create_chat_bots' ] );
        add_action( 'wp_ajax_check_email_exists', [ $this, 'cognix_check_email' ] );
        add_action( 'wp_ajax_check_username_exists', [ $this, 'cognix_check_username' ] );
		// Repeat for other AJAX actions as needed.
	}

    /**
     * Handlers for AJAX actions
     */
    public function cognix_register_user(){
        $firstname   = isset( $_REQUEST['firstname'] ) ? sanitize_text_field( $_REQUEST['firstname'] ) : '';
        $lastname    = isset( $_REQUEST['lastname'] ) ? sanitize_text_field( $_REQUEST['lastname'] ) : '';
        $member_name = isset( $_REQUEST['member_name'] ) ? sanitize_text_field( $_REQUEST['member_name'] ) : '';
        $password    = isset( $_REQUEST['password'] ) ? sanitize_text_field( $_REQUEST['password'] ) : '';
        $email       = isset( $_REQUEST['email'] ) ? sanitize_text_field( $_REQUEST['email'] ) : '';

        $curent_domain = filter_input( INPUT_SERVER, 'SERVER_NAME', FILTER_SANITIZE_URL );

        $userdata = array(
            'email'         => $email,
            'password'      => $password,
            'userName'      => $member_name,
            'userNameAlias' => '',
            'firstName'     => $firstname,
            'lastName'      => $lastname
        );
        $webinfo  = array( 'pluginWebsiteName' => $curent_domain );
        $webpostdata = array( 'user' => $userdata, 'wpPluginInfo' => $webinfo );
        $headers     = array( 'Content-Type' => 'application/json' );
        $jsonData = wp_json_encode( $webpostdata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
        $baseurl = get_option( 'cognix_base_url' );
        $baseurl = empty($baseurl) ? 'https://cognix.ai/llmToolsJavaApi' : $baseurl;

        $request_args = array(
            'headers'     => $headers,
            'body'        => $jsonData,
            'timeout'     => 15, // Adjust timeout as needed
            'redirection' => 5,  // Maximum number of redirects to follow
            'sslverify'   => true // Set to false to skip SSL certificate verification (use with caution)
        );

        $response = wp_remote_post( $baseurl . '/user/addWpUser', $request_args );
        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            wp_send_json_error("Something went wrong: $error_message");
        }

        $response = wp_remote_retrieve_body($response);
        // Handle response as needed
        $phpresponse = json_decode($response, true);
        // if ( array_key_exists("message", $phpresponse) ) {
        //     $result = $phpresponse["message"];
        //     wp_send_json_error($result);
        // }

        $user_id = get_current_user_id();
        update_user_meta($user_id, 'cognix_firstname', $firstname);
        update_user_meta($user_id, 'cognix_lastname', $lastname);
        update_user_meta($user_id, 'cognix_password', $password);
        update_user_meta($user_id, 'cognix_email', $email);
        update_user_meta($user_id, 'cognix_membername', $member_name);
        update_user_meta($user_id, 'cognix_curdomain', $curent_domain);
        $result = array('message' => $phpresponse);
        wp_send_json_success($result, null, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function my_custom_registration_redirect($user_id) {
        // Check if the user registration was successful
        if ($user_id) {
            // Define the login page URL
            $login_url = wp_login_url(); // This will use the default WordPress login URL
            
            // Alternatively, if you have a custom login page, specify its URL:
            // $login_url = site_url('/custom-login-page/');
    
            // Redirect to the login page
            wp_safe_redirect($login_url);
            exit; // Ensure the script stops executing after the redirect
        }
    }

    public function cognix_login_user(){
        $password = isset( $_REQUEST['lpassword'] ) ? sanitize_text_field( $_REQUEST['lpassword'] ) : '';
        $email    = isset( $_REQUEST['lemail'] ) ? sanitize_text_field( $_REQUEST['lemail'] ) : '';
        $headers = array(
            'Content-Type' => 'application/json',
        );

        $baseurl      = get_option( 'cognix_base_url' );
        $baseurl = empty($baseurl) ? 'https://cognix.ai/llmToolsJavaApi' : $baseurl;
        $request_url  = $baseurl . '/token/login?username=' . urlencode( $email ) . '&password=' . urlencode( $password );

        $request_args = array(
            'headers'     => $headers,
            'timeout'     => 15, // Adjust timeout as needed
            'redirection' => 5,  // Maximum number of redirects to follow
            'sslverify'   => true // Set to false to skip SSL certificate verification (use with caution)
        );
        $response = wp_remote_get($request_url, $request_args);

        if ( is_wp_error($response) ) {
            $error_message = $response->get_error_message();
            wp_send_json_error("Something went wrong: $error_message");
        }

        $response_body = wp_remote_retrieve_body($response);
        $loginresponse = json_decode($response_body, true);

        if ( array_key_exists("token", $loginresponse) ) {
            $result1 = array('message' => 'Token Generated Successfully');
            $tokens = get_option('cognix_tokens');
            $tokens[(string)get_current_user_id()] = $loginresponse["token"];
            update_option('cognix_tokens',$tokens);
            wp_send_json_success($result1, null, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $result1 = array('message' => "This user not found");
        wp_send_json_error($result1, null, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    }

    public function cognix_create_chat_bots(){
        $user_id   = get_current_user_id();
        $tokens = get_option('cognix_tokens');
        $token = $tokens[(string)$user_id] ?? '';

        if (isset($_REQUEST['upages']) && is_array($_REQUEST['upages'])) {
            $upages = array_map('sanitize_text_field', wp_unslash($_REQUEST['upages']));
        } else {
            $upages = [];
        }
        
        $bottype = isset( $_REQUEST['bottype'] ) ? sanitize_text_field( $_REQUEST['bottype'] ) : '';
        $pageurls = '';
        if(!empty($upages))
        foreach ($upages as $page_id){
            if(empty($pageurls)){
                $pageurls = get_permalink($page_id);
            } else {
                $pageurls .= "," . get_permalink($page_id);
            }
        }
        if(empty($pageurls)){
            $pageurls = home_url();
        }

        $baseurl      = get_option( 'cognix_base_url' );
        $baseurl = empty($baseurl) ? 'https://cognix.ai/llmToolsJavaApi' : $baseurl;

        
        $curdomin = parse_url(home_url(), PHP_URL_HOST);

        $seuserdata = array(
            'webPagesList' => $pageurls,
            'toolType' => (int)$bottype,
            'allowedOrigins' => 'https://' . $curdomin
        );

        $resjsonData = wp_json_encode($seuserdata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $request_args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token
            ),
            'body' => $resjsonData,
            'timeout' => 15, // Adjust timeout as needed
            'redirection' => 10, // Maximum number of redirects to follow
            'httpversion' => '1.1',
            'sslverify' => true // Set to false to skip SSL certificate verification (use with caution)
        );

        $response = wp_remote_post($baseurl . '/tools/createBotViaWP', $request_args);

        if ( is_wp_error($response) ) {
            $error_message = $response->get_error_message();
            wp_send_json_error("Something went wrong: $error_message");
        }

        $response_body = wp_remote_retrieve_body($response);
        $boresponse = json_decode($response_body, true);

        // Handle the response as needed
        if ( array_key_exists("message", $boresponse) && strpos($boresponse["message"],'script') ) {
            update_option('cognix_script', $boresponse["message"]);
            update_option('cognix_createdResourceId', $boresponse["createdResourceId"]);
            update_option('cognix_createdResourceName', $boresponse["createdResourceName"]);
            $result1 = array('message' => "Bot created succesfully", 'script' => htmlentities($boresponse["message"]));
            wp_send_json_success( $result1, null,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
        }

        $result1 = array('message' => "This user not found");
        wp_send_json_error($result1, null, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function cognix_check_email(){
        $email = isset( $_REQUEST['value'] ) ? sanitize_email( $_REQUEST['value'] ) : '';
        if(empty($email)){
            wp_send_json_success('empty');
        }
        $baseurl = get_option( 'cognix_base_url' );
        $baseurl = empty($baseurl) ? 'https://cognix.ai/llmToolsJavaApi' : $baseurl;
        $email_exists = $this->check_if_email_exists($baseurl, $email);
        if($email_exists){
            wp_send_json_error();
        } else {
            wp_send_json_success();
        }
    }

    public function cognix_check_username(){
        $member_name = isset( $_REQUEST['value'] ) ? sanitize_text_field( $_REQUEST['value'] ) : '';
        if(empty($member_name)){
            wp_send_json_error();
        }
        $baseurl = get_option( 'cognix_base_url' );
        $baseurl = empty($baseurl) ? 'https://cognix.ai/llmToolsJavaApi' : $baseurl;
        $username_exists = $this->check_if_username_exists($baseurl, $member_name);
        if($username_exists){
            wp_send_json_error();
        } else {
            wp_send_json_success();
        }

    }

	/**
	 * Renders the main plugin page.
	 */
	public function render_main_page() {
		$this->enqueue_scripts_and_styles();
		$this->handle_main_page_logic();
	}

	/**
	 * Enqueues scripts and styles for the plugin.
	 */
	private function enqueue_scripts_and_styles() {
		wp_enqueue_script('cognix-validate-script', plugin_dir_url(__FILE__) . 'jquery.validate.min.js', ['jquery'], true);
		wp_enqueue_script('cognix-main-script', plugin_dir_url(__FILE__) . 'main.js', ['jquery', 'cognix-validate-script'], true);
		wp_localize_script('cognix-main-script', 'cognix_object', ['ajax_url' => admin_url('admin-ajax.php')]);
		wp_enqueue_style('cognix-main-style', plugin_dir_url(__FILE__) . 'styles.css');
        wp_add_inline_style(
            'cognix-main-style',
            '.loading {background: url(' . esc_url(plugin_dir_url(__FILE__) . 'loading.gif') . ') center center no-repeat; background-size: contain;}'
        );
        
	}

	/**
	 * Handles logic for displaying the main plugin page.
	 */
	private function handle_main_page_logic() {
		$base_url = get_option('cognix_base_url');
        $base_url = empty($base_url) ? 'https://cognix.ai/llmToolsJavaApi' : $base_url;
        $current_domain = parse_url(home_url(), PHP_URL_HOST);


		$response = $this->check_user_existence_for_website($base_url, $current_domain);
		//$class_for_registration_form = $response ? 'regclu' : '';

        //if a user exists no need to show registation form in the beginning
        $disp_reg_form_style =  $response ? 'none': 'block';

        //if a user exists show a registation button if another user needs to be registered.
        $cognix_reg_div_disp =  $response ? 'block': 'none';

		//$register_button_class = empty($class_for_registration_form) ? 'hideuser' : '';

		$user_id = get_current_user_id();
		$user_info = get_user_meta($user_id);

		include 'views/main-plugin-page.php';
	}

    

	/**
	 * Checks if a user exists for the WordPress website.
	 *
	 * @param string $base_url Base URL for the API call.
	 * @param string $current_domain The current domain of the WordPress site.
	 *
	 * @return bool True if the user exists, false otherwise.
	 */
	private function check_user_existence_for_website($base_url, $current_domain) {
		$headers = ['Content-Type: application/json'];
		$response = wp_remote_get("{$base_url}/user/doesUerExistForWPWebsite?websiteName={$current_domain}", ['headers' => $headers]);
		if (is_wp_error($response)) {
			return false;
		}
		$body = wp_remote_retrieve_body($response);
		$decoded_responce = json_decode($body);
		if(isset($decoded_responce->error)){
            return false;
        }
        return $body == 'true'; // Assuming the API returns a boolean-like response.
	}

    /**
     * Checks if a user with specific email exists for the WordPress website.
     *
     * @param string $base_url Base URL for the API call.
     * @param string $email The email user tried to register with.
     *
     * @return bool True if the user exists, false otherwise.
     */
    private function check_if_email_exists($base_url, $email){
        $headers = ['Content-Type: application/json'];
        $response = wp_remote_get("{$base_url}/user/checkEmailExists?email={$email}", ['headers' => $headers]);
        if (is_wp_error($response)) {
            return true;
        }
        $body = wp_remote_retrieve_body($response);
        $decoded_responce = json_decode($body);
        if(isset($decoded_responce->error)){
            return true;
        }
        return $body == 'true';
    }

    /**
     * Checks if a user with specific username exists for the WordPress website.
     *
     * @param string $base_url Base URL for the API call.
     * @param string $email The username user tried to register with.
     *
     * @return bool True if the user exists, false otherwise.
     */
    private function check_if_username_exists($base_url, $username){
        $headers = ['Content-Type: application/json'];

        $response = wp_remote_get("{$base_url}/user/checkUserNameExists?username={$username}", ['headers' => $headers]);
        if (is_wp_error($response)) {
            return true;
        }
        $body = wp_remote_retrieve_body($response);
        $decoded_responce = json_decode($body);
        if(isset($decoded_responce->error)){
            return true;
        }
        return $body == 'true';
    }

}

/**
 * Initializes the plugin.
 */
function cognix_ai_plugin_init() {
	new Cognix_AI_Plugin();
}
add_action( 'plugins_loaded', 'cognix_ai_plugin_init' );
register_uninstall_hook(__FILE__,'cognix_uninstall');

function cognix_uninstall(){
    delete_option('cognix_base_url');
    delete_option('cognix_script');
    delete_option('cognix_createdResourceId');
    delete_option('cognix_createdResourceName');
    delete_option('cognix_tokens');
    delete_option('cognix_user_consent');
}
