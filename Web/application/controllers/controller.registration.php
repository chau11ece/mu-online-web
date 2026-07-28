<?php
    in_file();

    class registration extends controller
    {
        protected $vars = [], $errors = [];

        public function __construct()
        {
            parent::__construct();
            $this->load->helper('website');
            $this->load->lib('session', ['DmNCMS']);
			$this->load->lib('csrf');
            $this->load->helper('breadcrumbs', [$this->request]);
            $this->load->helper('meta');
        }

        public function index($ref = '', $server = '')
        {
            $this->vars['config'] = $this->config->values('registration_config');
            if($this->vars['config'] && $this->vars['config']['active'] == 1){
                $this->vars['security_config'] = $this->config->values('security_config');
                if($this->vars['security_config'] != false){
                    if($this->vars['security_config']['captcha_type'] == 2){
                        $this->generate_math_captcha();
                    }
                    if($this->vars['security_config']['captcha_type'] == 3){
                        $this->load->lib('recaptcha', [true, $this->vars['security_config']['recaptcha_priv_key']]);
                    }
                }
                if($this->config->values('referral_config', 'active') == 1){
                    if($ref != ''){
                        $this->vars['show_ref'] = true;
                        $this->vars['ref'] = htmlspecialchars($ref);
                        $this->vars['server'] = htmlspecialchars($server);
                    } else{
                        $this->vars['show_ref'] = false;
                        $this->vars['ref'] = '';
                        $this->vars['server'] = '';
                    }
                }
                $this->load->view($this->config->config_entry('main|template') . DS . 'registration' . DS . 'view.registration', $this->vars);
            } else{
                $this->disabled();
            }
        }

        public function create_account()
        {
            // Rate limiting: Check if too many registration attempts from this IP
            if($this->check_registration_attempts()){
                json(['error' => __('Too many registration attempts. Please try again after 15 minutes.')]);
                return;
            }

            $this->vars['config'] = $this->config->values('registration_config');
            if($this->vars['config'] && $this->vars['config']['active'] == 1){
                $this->vars['security_config'] = $this->config->values('security_config');
                // CSRF verification
                $this->csrf->verifyToken('post', 'json', 3600, true);
                if($this->vars['security_config'] != false){
                    if($this->vars['security_config']['captcha_type'] == 3){
                        $this->load->lib('recaptcha', [true, $this->vars['security_config']['recaptcha_priv_key']]);
                    }
                }
                if($this->website->is_multiple_accounts() == true){
                    $server_list = $this->website->server_list();
                    if(!isset($_POST['server']) || !isset($server_list[$_POST['server']])){
                        json(['error' => __('Invalid server selected.')]);
                        return;
                    }
                    $this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_db_from_server($_POST['server'], true)]);
                } else{
                    $this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_default_account_database()]);
                }
                $this->load->model('account');
                // Whitelist POST fields instead of mass assignment
                $allowed_fields = ['user', 'pass', 'rpass', 'email', 'fpas_ques', 'fpas_answ', 'server', 'referrer', 'ref_server', 'rules'];
                foreach($allowed_fields as $field){
                    if(isset($_POST[$field])){
                        $this->Maccount->$field = trim($_POST[$field]);
                    }
                }
                $this->validate_registration_input();
                $this->validate_captcha();
                if($this->config->values('referral_config', 'active') == 1){
                    if(isset($_POST['referrer'])){
                        if(!$this->Maccount->valid_id($_POST['referrer']))
                            $this->errors[] = __('The referrer id you entered is invalid.'); else{
                            if(!$referrer_account = $this->Maccount->check_acc_by_guid($_POST['referrer']))
                                $this->errors[] = __('The referrer you entered doesn\'t exists.');
                        }
                    }
                }
                if(count($this->errors) > 0){
                    $this->add_registration_attempt();
                    if(count($this->errors) == 1)
                        json(['error' => $this->errors[0]]); else
                        json(['error' => $this->errors]);
                } else{
                    if($this->vars['config']['email_validation'] == 1){
                        $this->Maccount->set_activation(1);
                    }
                    if($this->Maccount->prepare_account($this->vars['config']['req_email'], $this->vars['config']['req_secret'])){
                        $this->Maccount->log_user_ip($_POST['user']);
                        if($this->config->values('referral_config', 'active') == 1){
                            if(isset($_POST['referrer'])){
                                $this->Maccount->insert_referrer($referrer_account['memb___id']);
                                if($this->config->values('referral_config', 'reward_on_registration') != 0){
                                    if(!$this->Maccount->check_referral_ip()){
                                        $this->Maccount->add_ref_reward_after_reg($referrer_account['memb___id']);
                                    }
                                }
                            }
                        }
                        $this->load->model('shop');
                        $vip_data = isset($_POST['server']) ? $this->Mshop->load_registration_vip_packages($_POST['server']) : $this->Mshop->load_registration_vip_packages();
                        if(!empty($vip_data)){
                            $vip_query_config = $this->config->values('vip_query_config');
                            foreach($vip_data AS $key => $data){
                                $server = isset($_POST['server']) ? $_POST['server'] : $data['server'];
                                $viptime = time() + $data['vip_time'];
                                $this->Mshop->insert_vip_package($data['id'], $viptime, $_POST['user'], $server);
                                $this->Mshop->add_server_vip($viptime, $data['server_vip_package'], $data['connect_member_load'], $vip_query_config, $_POST['user']);
                                $this->Maccount->add_account_log('Added free vip ' . $data['package_title'] . ' package', 0, $_POST['user'], $server);
                            }
                        }
						if(defined('IPS_CONNECT') && IPS_CONNECT == true){
                            $this->load->lib('ipb');
                            if(isset($_POST['email']) && $_POST['email'] != '' && $this->ipb->checkEmail($_POST['email']) == false){
                                $salt = $this->ipb->generateSalt();
                                $this->ipb->register($_POST['user'], $_POST['email'], $this->ipb->encrypt_password($_POST['pass'], $salt), $salt);
                            }
                        }
                        if($this->vars['config']['email_validation'] == 1){
                            json(['success' => __('Your account has been successfully created.') . ' <br />' . __('Please check your email for account activation.')]);
                        } else{
                            json(['success' => __('Your account has been successfully created.')]);
                        }
                    } else{
                        if($this->Maccount->error != false){
                            json(['error' => $this->Maccount->error]);
                        } else{
                            json(['error' => __('There was an error creating your account. Please try again later.')]);
                        }
                    }
                }
            }
        }

        public function create_account_with_fb($server, $email)
        {
            $this->vars['config'] = $this->config->values('registration_config');
            if($this->vars['config'] && $this->vars['config']['active'] == 1){
                $this->vars['server'] = ($server != false) ? $server : '';
                if(isset($_POST['add_fb_account'])){
                    if($this->website->is_multiple_accounts() == true){
                        $this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_db_from_server($_POST['server'], true)]);
                    } else{
                        $this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_default_account_database()]);
                    }
                    $this->load->model('account');
                    // Whitelist POST fields instead of mass assignment
                    $allowed_fields = ['user', 'pass', 'rpass', 'fpas_ques', 'fpas_answ', 'server'];
                    foreach($allowed_fields as $field){
                        if(isset($_POST[$field])){
                            $this->Maccount->$field = trim($_POST[$field]);
                        }
                    }
                    $this->Maccount->email = $email;
                    if(!isset($_POST['user']))
                        $this->vars['errors'][] = __('You haven\'t entered a username.'); else{
                        if(!$this->Maccount->valid_username($_POST['user']))
                            $this->vars['errors'][] = __('The username you entered is invalid.'); else{
                            if($this->Maccount->check_duplicate_account($_POST['user']))
                                $this->vars['errors'][] = __('The username you entered is already taken.');
                        }
                    }
                    if($this->vars['config']['generate_password'] == 0){
                        if(!isset($_POST['pass']))
                            $this->vars['errors'][] = __('You haven\'t entered a password.'); else{
                            if(!$this->Maccount->valid_password($_POST['pass']))
                                $this->vars['errors'][] = __('The password you entered is invalid.');
                            $this->Maccount->test_password_strength($_POST['pass'], [$this->vars['config']['min_password'], $this->vars['config']['max_password']], $this->vars['config']['password_strength']);
                            if(!empty($this->Maccount->vars['errors'])){
                                $this->vars['errors'] = array_merge(isset($this->vars['errors']) ? $this->vars['errors'] : [], $this->Maccount->vars['errors']);
                            }
                        }
                        if(!isset($_POST['rpass']))
                            $this->vars['errors'][] = __('You haven\'t entered the password-repetition.'); else{
                            if($_POST['pass'] !== $_POST['rpass'])
                                $this->vars['errors'][] = __('The two passwords you entered do not match.');
                        }
                    } else{
                        $this->Maccount->pass = $this->Maccount->generate_password($this->vars['config']['min_password'], $this->vars['config']['max_password'], $this->vars['config']['password_strength']);
                    }
                    if($this->vars['config']['req_secret'] == 1){
                        if(!isset($_POST['fpas_ques']))
                            $this->vars['errors'][] = __('You haven\'t selected secret question.'); else{
                            if(!$this->website->secret_questions($_POST['fpas_ques']))
                                $this->vars['errors'][] = __('Please select valid secret question.'); else{
                                if(!isset($_POST['fpas_answ']))
                                    $this->vars['errors'][] = __('You haven\'t entered an secret answer.');
                            }
                        }
                    }
                    if(isset($this->vars['errors']) && count($this->vars['errors']) > 0){
                        if(count($this->vars['errors']) == 1)
                            $this->vars['errors'] = $this->vars['errors'][0];
                    } else{
                        $this->Maccount->set_activation(0);
                        if($this->Maccount->prepare_account(1, $this->vars['config']['req_secret'])){
                            $this->Maccount->check_fb_user($email, $server);
                            $this->Maccount->clear_login_attemts();
                            header('Location: ' . $this->config->base_url . 'account-panel');
                        } else{
                            if($this->Maccount->error != false){
                                $this->vars['errors'][0] = $this->Maccount->error;
                            } else{
                                $this->vars['errors'][0] = __('There was an error creating your account. Please try again later.');
                            }
                        }
                    }
                }
                $this->load->view($this->config->config_entry('main|template') . DS . 'registration' . DS . 'view.fb_registration', $this->vars);
            } else{
                $this->disabled();
            }
        }

        public function success()
        {
            $this->vars['config'] = $this->config->values('registration_config');
            if($this->vars['config'] && $this->vars['config']['active'] == 1){
                $this->load->view($this->config->config_entry('main|template') . DS . 'registration' . DS . 'view.successfull', $this->vars);
            } else{
                $this->disabled();
            }
        }

        public function activation($code = '', $server = '')
        {
            // Rate limiting for activation attempts
            if($this->check_activation_attempts()){
                $this->vars['error'] = __('Too many activation attempts. Please try again after 15 minutes.');
                $this->vars['config'] = $this->config->values('registration_config');
                $this->load->view($this->config->config_entry('main|template') . DS . 'registration' . DS . 'view.activation', $this->vars);
                return;
            }

            if($this->website->is_multiple_accounts() == true){
                $this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_db_from_server($server, true)]);
            } else{
                $this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_default_account_database()]);
            }
            $this->load->model('account');
            $this->vars['config'] = $this->config->values('registration_config');
            if($this->vars['config'] && $this->vars['config']['active'] == 1){
                $code = strtolower(trim(preg_replace('/[^0-9a-f]/i', '', $code)));
                if(strlen($code) <> 40){
                    $this->add_activation_attempt();
                    $this->vars['error'] = __('Invalid account activation code.');
                } else{
                    if(!$activation = $this->Maccount->check_activation_code($code)){
                        $this->add_activation_attempt();
                        $this->vars['error'] = __('Activation code doesn\'t exist in our database.');
                    } else{
                        if($activation['activated'] == 1){
                            $this->vars['error'] = __('This account is already activated.');
                        } else if(isset($activation['expired']) && $activation['expired'] === true){
                            $this->vars['error'] = __('Your activation code has expired. Please use the resend activation page to receive a new activation email.');
                        } else{
                            if($this->Maccount->activate_account($activation['memb___id'], $code)){
                                if($this->config->values('email_config', 'welcome_email') == 1){
                                    $this->Maccount->send_welcome_email($activation['memb___id'], $activation['mail_addr']);
                                }
                                $this->vars['success'] = __('Account successfully activated. You can now login.');
                            } else{
                                $this->vars['error'] = __('Unable to activate account.');
                            }
                        }
                    }
                }
                $this->load->view($this->config->config_entry('main|template') . DS . 'registration' . DS . 'view.activation', $this->vars);
            } else{
                $this->disabled();
            }
        }

        public function resend_activation()
        {
            $this->vars['config'] = $this->config->values('registration_config');
            $this->vars['security_config'] = $this->config->values('security_config');
            if($this->vars['security_config'] != false){
                if($this->vars['security_config']['captcha_type'] == 2){
                    $this->generate_math_captcha();
                }
                if($this->vars['security_config']['captcha_type'] == 3){
                    $this->load->lib('recaptcha', [true, $this->vars['security_config']['recaptcha_priv_key']]);
                }
            }
            if($this->vars['config'] && $this->vars['config']['active'] == 1){
                if($this->vars['config']['email_validation'] == 0){
                    $this->vars['not_required'] = __('Account validation not required');
                } else{
                    if(isset($_POST['email'])){
                        // CSRF verification
                        $this->csrf->verifyToken('post', 'json', 3600, true);
                        if($this->website->is_multiple_accounts() == true){
                            $server = $_POST['server'];
                            $this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_db_from_server($_POST['server'], true)]);
                        } else{
                            $server = '';
                            $this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_default_account_database()]);
                        }
                        $this->load->model('account');
                        // Whitelist POST fields instead of mass assignment
                        $allowed_fields = ['email', 'server'];
                        foreach($allowed_fields as $field){
                            if(isset($_POST[$field])){
                                $this->Maccount->$field = trim($_POST[$field]);
                            }
                        }
                        if($_POST['email'] == '')
                            $this->errors[] = __('You haven\'t entered an email-address.'); else{
                            if(!$this->Maccount->valid_email($_POST['email']))
                                $this->errors[] = __('You have entered an invalid email-address.'); else{
                                $validated = $this->Maccount->check_if_validated($_POST['email']);
                                if($validated != false){
                                    if($validated['activated'] == 1){
                                        $this->errors[] = __('The email-address you entered is already activated.');
                                    }
                                } else{
                                    $this->errors[] = __('The email-address you entered not found in our database.');
                                }
                            }
                        }
                        $this->validate_captcha();
                        if(count($this->errors) > 0){
                            if(count($this->errors) == 1)
                                $this->vars['error'] = $this->errors[0]; else
                                $this->vars['error'] = $this->errors;
                        } else{
                            if($this->Maccount->resend_activation_email($_POST['email'], $validated['memb___id'], $server, $validated['activation_id'])){
                                $this->vars['success'] = __('Account activation email was successfully sent.');
                            }
                        }
                    }
                }
                $this->load->view($this->config->config_entry('main|template') . DS . 'registration' . DS . 'view.resend_activation', $this->vars);
            } else{
                $this->disabled();
            }
        }

        public function disabled()
        {
            $this->load->view($this->config->config_entry('main|template') . DS . 'view.module_disabled');
        }

        private function validate_registration_input()
        {
            if(!isset($_POST['user']))
                $this->errors[] = __('You haven\'t entered a username.'); else{
                if(!$this->Maccount->valid_username($_POST['user'], 'a-zA-Z0-9_-', [$this->vars['config']['min_username'], $this->vars['config']['max_username']]))
                    $this->errors[] = __('The username you entered is invalid.'); else{
                    if($this->Maccount->check_duplicate_account($_POST['user']))
                        $this->errors[] = __('The username you entered is already taken.');
                }
            }
            // Always require password
            if(!isset($_POST['pass']))
                $this->errors[] = __('You haven\'t entered a password.'); else{
                if(!$this->Maccount->valid_password($_POST['pass']))
                    $this->errors[] = __('The password you entered is invalid.');
                $this->Maccount->test_password_strength($_POST['pass'], [$this->vars['config']['min_password'], $this->vars['config']['max_password']], $this->vars['config']['password_strength']);
                if(!empty($this->Maccount->vars['errors'])){
                    $this->errors = array_merge($this->errors, $this->Maccount->vars['errors']);
                }
            }
            if(!isset($_POST['rpass']))
                $this->errors[] = __('You haven\'t entered the password-repetition.'); else{
                if($_POST['pass'] !== $_POST['rpass'])
                    $this->errors[] = __('The two passwords you entered do not match.');
            }
            // Always require email
            if(!isset($_POST['email']))
                $this->errors[] = __('You haven\'t entered an email-address.'); else{
                if(!$this->Maccount->valid_email($_POST['email']))
                    $this->errors[] = __('You have entered an invalid email-address.');
            }
            if($this->vars['config']['req_secret'] == 1){
                if(!isset($_POST['fpas_ques']))
                    $this->errors[] = __('You haven\'t selected secret question.'); else{
                    if(!$this->website->secret_questions($_POST['fpas_ques']))
                        $this->errors[] = __('Please select valid secret question.'); else{
                        if(!isset($_POST['fpas_answ']))
                            $this->errors[] = __('You haven\'t entered an secret answer.');
                    }
                }
            }
            if(!isset($_POST['rules']))
                $this->errors[] = __('You haven\'t accepted rules.'); else{
                if($_POST['rules'] != 'on')
                    $this->errors[] = __('You haven\'t accepted rules.');
            }
        }

        private function validate_captcha()
        {
            if($this->vars['security_config'] != false){
                if($this->vars['security_config']['captcha_type'] == 1){
                    if(isset($_POST['qaptcha_key'], $_SESSION['qaptcha_key'])){
                        if(!hash_equals($_SESSION['qaptcha_key'], $_POST['qaptcha_key'])){
                            $this->errors[] = __('Invalid captcha, Please check slider position.');
                        }
                    } else{
                        $this->errors[] = __('Invalid captcha, Please check slider position.');
                    }
                }
                if($this->vars['security_config']['captcha_type'] == 2){
                    if(!isset($_POST['captcha_answer']) || $_POST['captcha_answer'] === ''){
                        $this->errors[] = __('Please answer the security question.');
                    } else if(!isset($_SESSION['math_captcha_answer']) || (int)$_POST['captcha_answer'] !== $_SESSION['math_captcha_answer']){
                        $this->errors[] = __('Incorrect security answer. Please try again.');
                    }
                    // Generate new question after validation attempt
                    $this->generate_math_captcha();
                }
                if($this->vars['security_config']['captcha_type'] == 3){
                    if(isset($_POST["g-recaptcha-response"])){
                        $response = $this->recaptcha->verifyResponse(ip(), $_POST["g-recaptcha-response"]);
                        if($response == null || !$response->is_valid){
                            $this->errors[] = __('Incorrect security image response.');
                        }
                    } else{
                        $this->errors[] = __('Incorrect security image response.');
                    }
                }
            }
        }

        private function generate_math_captcha()
        {
            $a = random_int(1, 20);
            $b = random_int(1, 20);
            $_SESSION['math_captcha_answer'] = $a + $b;
            $this->vars['math_captcha_question'] = sprintf('%d + %d = ?', $a, $b);
        }

        private function check_registration_attempts()
        {
            $file = APP_PATH . DS . 'logs' . DS . 'registration_attempts.json';
            if(!file_exists($file)){
                return false;
            }
            $handle = fopen($file, 'r');
            if(!$handle){
                return false;
            }
            flock($handle, LOCK_SH);
            $data = stream_get_contents($handle);
            flock($handle, LOCK_UN);
            fclose($handle);
            if($data != false && $data != ''){
                $ips = json_decode($data, true);
                if($ips !== null && isset($ips[ip()]) && $ips[ip()]['time'] >= time() - 900){
                    return $ips[ip()]['attempts'] >= 5;
                }
            }
            return false;
        }

        private function add_registration_attempt()
        {
            $file = APP_PATH . DS . 'logs' . DS . 'registration_attempts.json';
            $handle = fopen($file, 'c+');
            if(!$handle){
                return false;
            }
            flock($handle, LOCK_EX);
            $data = stream_get_contents($handle);
            $ips = ($data != false && $data != '') ? json_decode($data, true) : [];
            if($ips === null){
                $ips = [];
            }
            // Clean up expired entries
            $now = time();
            foreach($ips as $ip => $entry){
                if($entry['time'] < $now - 900){
                    unset($ips[$ip]);
                }
            }
            if(isset($ips[ip()])){
                $ips[ip()]['attempts'] = $ips[ip()]['attempts'] + 1;
                $ips[ip()]['time'] = $now;
            } else{
                $ips[ip()] = ['attempts' => 1, 'time' => $now];
            }
            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, json_encode($ips));
            flock($handle, LOCK_UN);
            fclose($handle);
            return true;
        }

        private function check_activation_attempts()
        {
            $file = APP_PATH . DS . 'logs' . DS . 'activation_attempts.json';
            if(!file_exists($file)){
                return false;
            }
            $handle = fopen($file, 'r');
            if(!$handle){
                return false;
            }
            flock($handle, LOCK_SH);
            $data = stream_get_contents($handle);
            flock($handle, LOCK_UN);
            fclose($handle);
            if($data != false && $data != ''){
                $ips = json_decode($data, true);
                if($ips !== null && isset($ips[ip()]) && $ips[ip()]['time'] >= time() - 900){
                    return $ips[ip()]['attempts'] >= 10;
                }
            }
            return false;
        }

        private function add_activation_attempt()
        {
            $file = APP_PATH . DS . 'logs' . DS . 'activation_attempts.json';
            $handle = fopen($file, 'c+');
            if(!$handle){
                return false;
            }
            flock($handle, LOCK_EX);
            $data = stream_get_contents($handle);
            $ips = ($data != false && $data != '') ? json_decode($data, true) : [];
            if($ips === null){
                $ips = [];
            }
            // Clean up expired entries
            $now = time();
            foreach($ips as $ip => $entry){
                if($entry['time'] < $now - 900){
                    unset($ips[$ip]);
                }
            }
            if(isset($ips[ip()])){
                $ips[ip()]['attempts'] = $ips[ip()]['attempts'] + 1;
                $ips[ip()]['time'] = $now;
            } else{
                $ips[ip()] = ['attempts' => 1, 'time' => $now];
            }
            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, json_encode($ips));
            flock($handle, LOCK_UN);
            fclose($handle);
            return true;
        }
    }
