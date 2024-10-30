<?php
    if ( ! defined( 'ABSPATH' ) ) exit;

    if ( get_option( 'cognix_user_consent' ) == "" ) {
		?>
        <div class="wrap">
            <div class="left-part">
                <h1>Cognix AI Plugin Settings</h1>

                <form method="post" action="options.php">
					<?php
					settings_fields( 'cognix_settings' );
					do_settings_sections( 'cognix_tools' );
                    $base_url = get_option( 'cognix_base_url' );
					?>
                    <input type="hidden" name="cognix_base_url" value="<?php echo esc_attr( $base_url ); ?>"/>
                    <table class="form-table">
                        <tr>
                            <th scope="row">User Consent</th>
                            <td>
                                <label for="cognix_user_consent">
                                    <input type="checkbox" id="cognix_user_consent"
                                           name="cognix_user_consent" <?php checked( get_option( 'cognix_user_consent' ), 1 ); ?> value="1"/>
                                    I consent to site's data exchange with the plugin's API.
                                </label>
                            </td>
                        </tr>
                    </table>

					<?php submit_button(); ?>
                </form>
            </div>
        </div>
	<?php } else {
		?>
        <div class="wrap">
			<?php
			$email         = "";
			$password      = "";
			if ( isset( $user_info['cognix_password'] ) ) {
				$password = $user_info['cognix_password'][0];
				$email    = $user_info['cognix_email'][0];
			}
            $tokens = get_option('cognix_tokens');
            $token = $tokens[(string)get_current_user_id()] ?? false;
			?>
            <div class="left-part">
            <?php if ( $response == true ) { ?>
                <h1>Step 1 - Login to cognix.ai . The token generated in this step will be needed to proceed to the next step</h1>
                <h2>Existing users - enter your credentials</h2>
                <form method="post" name="cognix_login" id="cognix_login" action="">

                    <p><strong>Email Id/username:</strong><br/>
                        <input type="text" class="regular-text" name="lemail" id="lemail"
                               value="<?php echo esc_html( $email ); ?>"/>
                    </p>
                    <p><strong>Password:</strong><br/>
                        <input type="password" class="regular-text" id="lpassword" name="lpassword"
                               value="<?php echo esc_html( $password ); ?>"/>
                    </p>
                    <p><input type="submit" name="login" value="Login"/></p>
                    <input type="hidden" name="action" value="login"/>

                    
                    <p id="cognix_login_status_msg"></p>
                    
                </form>

                <?php } else { ?>
                <div name="cognix_register" id="cognix_register_frm" style="display: <?php echo esc_attr($disp_reg_form_style); ?> ">

                <form method="post" name="cognix_register" id="cognix_register" action="" >
                <div class="welcome-container">
                        <h1>Welcome to Cognix.ai!</h1>
                        <!-- <div class="image-placeholder">Image Placeholder</div> -->
                        <p>
                            Thank you for installing our plugin. Since this is your first time setting it up, please take a moment to sign up. 
                            Once your registration is complete, we will redirect you to the login page where you can create your first bot.
                        </p>
                        <p>
                            We are excited to have you on board and are here to assist you with any questions or support you might need. 
                            Please reach us at <a href="mailto:support@cognix.ai">Cognix Support</a> in case of any issues.
                            Enjoy your journey with Cognix.ai!
                        </p>
                    </div>
                    
                    <p><strong>First Name:</strong><br/>
                        <input type="text" class="regular-text" name="firstname" id="firstname"
							<?php if ( isset( $user_info['cognix_firstname'] ) ) { ?>
                                value="<?php echo esc_html( $user_info['cognix_firstname'][0] ); ?>"
							<?php } ?>
                        />
                    </p>
                    <p><strong>Last Name:</strong><br/>
                        <input type="text" class="regular-text" name="lastname" id="lastname"

							<?php if ( isset( $user_info['cognix_lastname'] ) ) { ?>
                                value="<?php echo esc_html( $user_info['cognix_lastname'][0] ); ?>"
							<?php } ?> />
                    </p>
                    <p><strong>Member Name:</strong><br/>
                        <input type="text" class="regular-text" name="member_name" id="member_name"
							<?php if ( isset( $user_info['cognix_membername'] ) ) { ?>
                                value="<?php echo esc_html( $user_info['cognix_membername'][0] ); ?>"
							<?php } ?>
                        />
                        <span id="member_name_allowed" style="width:16px; height:16px"></span>
                    </p>
                    <p><strong>Email Id:</strong><br/>
                        <input type="email" class="regular-text" name="email" id="email"
							<?php if ( isset( $user_info['cognix_email'] ) ) { ?>
                                value="<?php echo esc_html( $user_info['cognix_email'][0] ); ?>"
							<?php } ?>
                        />
                        <span id="email_allowed" style="width:16px; height:16px"></span>
                    </p>
                    <p><strong>Password:</strong><br/>
                        <input type="password" class="regular-text" id="password" name="password" value=""/>
                    </p>
                    <p><strong>Confirm Password:</strong><br/>
                        <input type="password" class="regular-text" name="con_password" value=""/>
                    </p>


                    <p><input type="submit" name="Submit" value="Register" style="margin-right:10px"/>
                    <!-- <input type="button" id="cognixRegCancelButton" name="Cancel" class="button-primary" value="Cancel"/> -->
                   </p>
                    
                    <input type="hidden" name="action" value="register"/>
                    <p class="responsemsg"></p>
                    <p id="cognix_reg_status_msg"></p>
                    

                </form>
                </div>
                    

                    <?php } ?>


                <?php if ( $response == true ) { ?>
				
                    <h1>Step 2 : Create Bot</h1>
					<?php if ( $password != "" && $email != "" ) { ?>
                        <form method="post" name="createchat" id="createchat" action="">
                            <p><label>Select Bot Type</label>
                                <select name="bottype" id="bottype">
                                    <option value="1" selected="selected">Chat Bot</option>
                                    <option value="2">Question Answer Bot</option>
                                </select>
                            
                            <p><label>Page URLs (Specify the web pages on your site that you want to include in the bot):</label><br/>
                                <div class="pages-list">
								<?php
								$pages = get_pages();
								foreach ( $pages as $page ) {
									?>
                                    <label>
                                        <input type="checkbox" name="pageurl[]" class="upages" id="upages"
                                               value="<?php echo esc_attr($page->ID); ?>"><?php echo esc_html($page->post_title); ?>
                                    </label>
								<?php } ?>
                            </div>
                            </p>
                            <p><strong>Enhance your bot's capabilities by uploading additional documentation, such as FAQs, customer service guides, and more. Simply visit the site after creating your bot to upload your content. The data will be instantly incorporated, allowing the bot to understand and respond to queries based on the new information without any further steps required.</strong></p>


                            <p><input type="submit" name="crchatboat" value="Create Bot" <?php echo $token ? '' : 'disabled="disabled"' ; ?>/></p>
                            <input type="hidden" name="action" value="chatboat"/>
                            <p class="chatresponsemsg"></p>
                            <span class="loading">Creating Chat bot......</span>
                            
                            <p id="cognix_bot_create_msg"></p>
                        </form>
                        
                        <?php $script = get_option('cognix_script') ?? ''; ?>
                        <div class="sctipt-data  <?php echo empty($script) ? 'hidden': '';?> ">
                            <h3><strong>We have added this code block to your site, so the bot should automatically appear at the bottom right corner on your site. If it's not visible, you can add the code block manually. Note: You can change the bot's position by visiting the plugin site at cognix.ai and adjusting the launch icon location there. </strong></h3>
                            <p class="chatbot-script"><code><?php echo esc_html(trim($script)); ?></code></p>
                        </div>

					<?php } else { ?>
                        <h2> Login to create Bot</h2>
					<?php } ?>
                    <p class="note">
                        <strong>The bot is available for free during a trial period or up to a word limit. To ensure uninterrupted service, please visit <a href="https://www.cognix.ai/" target="_blank">cognix.ai</a> to subscribe.</strong><br/><br/>
                            You can edit your bot by visiting cognix.ai, where you'll find numerous configuration options. Use the credentials you created during registration to log in. Once logged in, you will see your newly created bot listed among your bots.
                    </p>
				<?php } ?>
            </div>
            <div class="right-part ">

                
            </div>
        </div>
	<?php }