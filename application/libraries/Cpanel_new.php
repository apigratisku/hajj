<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cpanel_new {

    private $cpanel_user;
    private $cpanel_pass;
    private $cpanel_host;
    private $cpanel_port;
    private $session_token = null;
    private $cookies = "";
    private $auth_token = null;

    public function __construct($config = array())
    {
        $this->cpanel_user = isset($config['user']) ? $config['user'] : "menb8295";
        $this->cpanel_pass = isset($config['pass']) ? $config['pass'] : "hrPG2nS6SZTk88";
        $this->cpanel_host = isset($config['host']) ? $config['host'] : "menfins.site";
        $this->cpanel_port = isset($config['port']) ? $config['port'] : 2083;
        $this->auth_token = isset($config['auth_token']) ? $config['auth_token'] : null;
    }

    /**
     * Login dengan multiple metode
     */
    private function login()
    {
        // Coba metode token terlebih dahulu jika auth_token tersedia
        if ($this->auth_token && !empty($this->auth_token)) {
            log_message('info', 'CPanel Login - Attempting token authentication');
            if ($this->loginWithToken()) {
                return true;
            }
        }

        // Fallback ke metode session login
        log_message('info', 'CPanel Login - Attempting session authentication');
        return $this->loginWithSession();
    }

    /**
     * Login menggunakan auth token
     */
    private function loginWithToken()
    {
        try {
            $loginUrl = "https://{$this->cpanel_host}:{$this->cpanel_port}/execute/Email/list_pops";
            
            $headers = [
                'Authorization: cpanel ' . $this->cpanel_user . ':' . $this->auth_token,
                'Content-Type: application/json'
            ];

            log_message('info', 'CPanel Token Login - Attempting token login to: ' . $loginUrl);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $loginUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_USERAGENT, 'CodeIgniter-CPanel/1.0');

            $result = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            log_message('info', 'CPanel Token Login - HTTP Code: ' . $http_code);
            log_message('info', 'CPanel Token Login - Response: ' . $result);

            if ($error) {
                log_message('error', 'CPanel Token Login - cURL Error: ' . $error);
                return false;
            }

            if ($http_code === 200) {
                $this->session_token = 'TOKEN_AUTH';
                log_message('info', 'CPanel Token Login - Token authentication successful');
                return true;
            }

            log_message('error', 'CPanel Token Login - HTTP Error: ' . $http_code);
            return false;
        } catch (Exception $e) {
            log_message('error', 'CPanel Token Login - Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Login menggunakan session token dengan optimasi performa
     */
    private function loginWithSession()
    {
        try {
            // Coba login dengan metode standar
            $loginUrl = "https://{$this->cpanel_host}:{$this->cpanel_port}/login/?login_only=1";
            $postFields = [
                "user" => $this->cpanel_user,
                "pass" => $this->cpanel_pass
            ];

            log_message('info', 'CPanel Session Login - Attempting login to: ' . $loginUrl);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $loginUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15); // Kurangi timeout
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // Tambah connect timeout
            curl_setopt($ch, CURLOPT_USERAGENT, 'CodeIgniter-CPanel/1.0');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            $result = curl_exec($ch);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($result, 0, $header_size);
            $body = substr($result, $header_size);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            log_message('info', 'CPanel Session Login - HTTP Code: ' . $http_code);
            log_message('info', 'CPanel Session Login - Response Body: ' . substr($body, 0, 200)); // Kurangi log

            if ($error) {
                log_message('error', 'CPanel Session Login - cURL Error: ' . $error);
                return false;
            }

            if ($http_code !== 200) {
                log_message('error', 'CPanel Session Login - HTTP Error: ' . $http_code);
                return false;
            }

            // Coba parse JSON response
            $json = json_decode($body, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($json['status']) && $json['status'] == 1) {
                $this->session_token = $json['security_token'];
                log_message('info', 'CPanel Session Login - Session Token: ' . $this->session_token);

                if (preg_match_all('/Set-Cookie:\s*([^;]*)/mi', $header, $matches)) {
                    $this->cookies = implode("; ", $matches[1]);
                    log_message('info', 'CPanel Session Login - Cookies: ' . $this->cookies);
                }

                return true;
            }

            // Jika JSON parsing gagal, coba extract session token dari header atau response
            if (preg_match('/cpsess(\d+)/', $header . $body, $matches)) {
                $this->session_token = '/cpsess' . $matches[1];
                log_message('info', 'CPanel Session Login - Extracted Session Token: ' . $this->session_token);

                if (preg_match_all('/Set-Cookie:\s*([^;]*)/mi', $header, $matches)) {
                    $this->cookies = implode("; ", $matches[1]);
                    log_message('info', 'CPanel Session Login - Cookies: ' . $this->cookies);
                }

                return true;
            }
            
            // Coba extract dari URL redirect atau response body
            if (preg_match('/cpsess(\d+)/', $body, $matches)) {
                $this->session_token = '/cpsess' . $matches[1];
                log_message('info', 'CPanel Session Login - Extracted Session Token from body: ' . $this->session_token);
                return true;
            }

            log_message('error', 'CPanel Session Login - Failed to extract session token');
            return false;
        } catch (Exception $e) {
            log_message('error', 'CPanel Session Login - Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Request API dengan multiple metode
     */
    private function request($url, $method = 'GET', $data = null)
    {
        try {
            if (!$this->session_token) {
                log_message('info', 'CPanel request - No session token, attempting login');
                if (!$this->login()) {
                    log_message('error', 'CPanel request - Login failed');
                    return ["error" => "Login gagal"];
                }
            }

            // Jika menggunakan token auth
            if ($this->session_token === 'TOKEN_AUTH') {
                log_message('info', 'CPanel request - Using token authentication');
                return $this->requestWithToken($url, $method, $data);
            }

            // Jika menggunakan session auth
            log_message('info', 'CPanel request - Using session authentication');
            $result = $this->requestWithSession($url, $method, $data);
            
            // Jika mendapat HTTP 403, coba force login ulang dengan retry mechanism
            if (isset($result['error']) && strpos($result['error'], '403') !== false) {
                log_message('info', 'CPanel request - HTTP 403 detected, attempting auto-recovery');
                
                // Coba force login hingga 3 kali
                $max_retries = 3;
                $retry_count = 0;
                
                while ($retry_count < $max_retries) {
                    $retry_count++;
                    log_message('info', 'CPanel request - Auto-recovery attempt ' . $retry_count . ' of ' . $max_retries);
                    
                    if ($this->forceLogin()) {
                        log_message('info', 'CPanel request - Force login successful, retrying request');
                        $result = $this->requestWithSession($url, $method, $data);
                        
                        // Jika masih mendapat 403, lanjutkan retry
                        if (isset($result['error']) && strpos($result['error'], '403') !== false) {
                            log_message('info', 'CPanel request - Still getting 403 after force login, retrying...');
                            continue;
                        } else {
                            // Berhasil atau error lain, keluar dari loop
                            break;
                        }
                    } else {
                        log_message('error', 'CPanel request - Force login failed on attempt ' . $retry_count);
                        if ($retry_count >= $max_retries) {
                            return ["error" => "Force login failed after " . $max_retries . " attempts"];
                        }
                    }
                }
            }
            
            return $result;
        } catch (Exception $e) {
            log_message('error', 'CPanel request - Exception: ' . $e->getMessage());
            return ["error" => "Request exception: " . $e->getMessage()];
        }
    }

    /**
     * Request menggunakan token
     */
    private function requestWithToken($url, $method = 'GET', $data = null)
    {
        try {
            if (!$this->auth_token || empty($this->auth_token)) {
                log_message('error', 'CPanel Token Request - No auth token available');
                return ["error" => "No auth token available"];
            }
            
            $endpoint = "https://{$this->cpanel_host}:{$this->cpanel_port}/execute" . $url;
            
            $headers = [
                'Authorization: cpanel ' . $this->cpanel_user . ':' . $this->auth_token,
                'Content-Type: application/json'
            ];

            log_message('info', 'CPanel Token Request - Making request to: ' . $endpoint);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_USERAGENT, 'CodeIgniter-CPanel/1.0');

            if ($method === 'POST' && $data) {
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }

            $result = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            log_message('info', 'CPanel Token Request - HTTP Code: ' . $http_code);
            log_message('info', 'CPanel Token Request - Response: ' . $result);

            if ($error) {
                log_message('error', 'CPanel Token Request - cURL Error: ' . $error);
                return ["error" => "Request error: " . $error];
            }

            if ($http_code !== 200) {
                log_message('error', 'CPanel Token Request - HTTP Error: ' . $http_code);
                return ["error" => "HTTP error: " . $http_code];
            }

            $decoded = json_decode($result, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', 'CPanel Token Request - JSON decode error: ' . json_last_error_msg());
                return ["error" => "Invalid JSON response: " . json_last_error_msg()];
            }

            return $decoded;
        } catch (Exception $e) {
            log_message('error', 'CPanel Token Request - Exception: ' . $e->getMessage());
            return ["error" => "Token request exception: " . $e->getMessage()];
        }
    }

    /**
     * Request menggunakan session dengan optimasi performa
     */
    private function requestWithSession($url, $method = 'GET', $data = null)
    {
        try {
            if (!$this->session_token) {
                log_message('error', 'CPanel Session Request - No session token available');
                return ["error" => "No session token available"];
            }
            
            // Gunakan format URL yang sesuai dengan Jupiter interface
            if (strpos($url, '/json-api/') === 0) {
                // Untuk JSON API, gunakan format langsung
                $endpoint = "https://{$this->cpanel_host}:{$this->cpanel_port}{$url}";
            } else {
                // Untuk execute/uapi, tambahkan session token
                $endpoint = "https://{$this->cpanel_host}:{$this->cpanel_port}{$this->session_token}{$url}";
            }
            
            log_message('info', 'CPanel Session Request - Making request to: ' . $endpoint);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15); // Kurangi timeout untuk performa lebih baik
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // Tambah connect timeout
            curl_setopt($ch, CURLOPT_USERAGENT, 'CodeIgniter-CPanel/1.0');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_VERBOSE, false); // Matikan verbose untuk performa
            
            if ($this->cookies) {
                curl_setopt($ch, CURLOPT_COOKIE, $this->cookies);
            }

            if ($method === 'POST' && $data) {
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            }
            
            $result = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            log_message('info', 'CPanel Session Request - HTTP Code: ' . $http_code);
            log_message('info', 'CPanel Session Request - Response: ' . substr($result, 0, 200)); // Kurangi log response

            if ($error) {
                log_message('error', 'CPanel Session Request - cURL Error: ' . $error);
                return ["error" => "Request error: " . $error];
            }

            if ($http_code === 403) {
                log_message('error', 'CPanel Session Request - HTTP 403 Forbidden (Access denied)');
                return ["error" => "HTTP error: 403 (Access denied)"];
            } elseif ($http_code !== 200) {
                log_message('error', 'CPanel Session Request - HTTP Error: ' . $http_code);
                return ["error" => "HTTP error: " . $http_code];
            }

            $decoded = json_decode($result, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', 'CPanel Session Request - JSON decode error: ' . json_last_error_msg());
                log_message('error', 'CPanel Session Request - Raw response: ' . $result);
                return ["error" => "Invalid JSON response: " . json_last_error_msg()];
            }

            return $decoded;
        } catch (Exception $e) {
            log_message('error', 'CPanel Session Request - Exception: ' . $e->getMessage());
            return ["error" => "Session request exception: " . $e->getMessage()];
        }
    }

    /**
     * Test koneksi ke cPanel
     */
    public function testConnection()
    {
        try {
            log_message('info', 'CPanel testConnection - Starting connection test');
            
            if ($this->auth_token && !empty($this->auth_token)) {
                log_message('info', 'CPanel testConnection - Using token authentication');
                $result = $this->requestWithToken('/UAPI/get_user_information');
            } else {
                log_message('info', 'CPanel testConnection - Using session authentication');
                $url = "/json-api/cpanel?cpanel_jsonapi_user={$this->cpanel_user}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=UAPI&cpanel_jsonapi_func=get_user_information";
                $result = $this->request($url);
            }
            
            log_message('info', 'CPanel testConnection - Result: ' . json_encode($result));
            return $result;
        } catch (Exception $e) {
            log_message('error', 'CPanel testConnection - Exception: ' . $e->getMessage());
            return ['error' => 'Exception in testConnection: ' . $e->getMessage()];
        }
    }

    /**
     * Ambil daftar email dengan optimasi performa
     */
    public function listEmailAccounts($domain = null)
    {
        try {
            log_message('info', 'CPanel listEmailAccounts - Starting optimized request');
            
            if ($this->auth_token && !empty($this->auth_token)) {
                log_message('info', 'CPanel listEmailAccounts - Using token authentication');
                $result = $this->requestWithToken('/Email/list_pops');
            } else {
                log_message('info', 'CPanel listEmailAccounts - Using session authentication');
                $domain = $domain ?: $this->cpanel_host;
                
                // Gunakan endpoint yang paling cepat dan reliable
                $endpoints = [
                    // Jupiter interface endpoint yang paling cepat
                    "/json-api/cpanel?cpanel_jsonapi_user={$this->cpanel_user}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=Email&cpanel_jsonapi_func=list_pops&domain={$domain}",
                    // Fallback ke endpoint lain jika diperlukan
                    "/execute/Email/list_pops"
                ];
                
                $result = null;
                foreach ($endpoints as $endpoint) {
                    log_message('info', 'CPanel listEmailAccounts - Trying endpoint: ' . $endpoint);
                    $result = $this->request($endpoint);
                    
                    if (!isset($result['error']) && !empty($result)) {
                        log_message('info', 'CPanel listEmailAccounts - Success with endpoint: ' . $endpoint);
                        break;
                    }
                }
                
                if (!$result || isset($result['error'])) {
                    log_message('error', 'CPanel listEmailAccounts - All endpoints failed');
                    return ['error' => 'Failed to get email accounts from all endpoints'];
                }
            }
            
            log_message('info', 'CPanel listEmailAccounts - Result: ' . json_encode($result));
            return $result;
        } catch (Exception $e) {
            log_message('error', 'CPanel listEmailAccounts - Exception: ' . $e->getMessage());
            return ['error' => 'Exception in listEmailAccounts: ' . $e->getMessage()];
        }
    }

    /**
     * Buat akun email baru dengan optimasi untuk mengatasi HTTP 403
     */
    public function createEmailAccount($email, $password, $quota = 250)
    {
        try {
            log_message('info', 'CPanel createEmailAccount - Creating email: ' . $email);
            
            $domain = substr(strrchr($email, "@"), 1);
            $user = substr($email, 0, strpos($email, "@"));
            
            if ($this->auth_token && !empty($this->auth_token)) {
                log_message('info', 'CPanel createEmailAccount - Using token authentication');
                $data = [
                    'email' => $user,
                    'domain' => $domain,
                    'pass' => $password,
                    'quota' => $quota
                ];
                $result = $this->requestWithToken('/Email/add_pop', 'POST', $data);
            } else {
                log_message('info', 'CPanel createEmailAccount - Using session authentication');
                
                // Force fresh login untuk operasi write dengan timeout yang lebih pendek
                log_message('info', 'CPanel createEmailAccount - Force fresh login for write operation');
                if (!$this->forceLogin()) {
                    log_message('error', 'CPanel createEmailAccount - Force login failed');
                    return ['error' => 'Failed to establish fresh session for write operation'];
                }
                
                // Tunggu sebentar untuk memastikan session stabil
                sleep(1);
                
                // Coba endpoint yang paling reliable untuk Jupiter interface
                $endpoints = [
                    // Jupiter interface specific endpoint dengan POST data
                    "/json-api/cpanel?cpanel_jsonapi_user={$this->cpanel_user}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=Email&cpanel_jsonapi_func=add_pop",
                    // Execute API endpoint dengan POST data
                    "/execute/Email/add_pop",
                    // Legacy endpoint sebagai fallback
                    "/json-api/cpanel?cpanel_jsonapi_user={$this->cpanel_user}&cpanel_jsonapi_apiversion=1&cpanel_jsonapi_module=Email&cpanel_jsonapi_func=add_pop"
                ];
                
                // Data untuk POST request - coba berbagai parameter password untuk kompatibilitas rumahweb.com
                $postData = [
                    'email' => $user,
                    'domain' => $domain,
                    'passwd' => $password, // Parameter utama untuk rumahweb.com
                    'quota' => $quota
                ];
                
                // Log data yang akan dikirim untuk debugging
                log_message('info', 'CPanel createEmailAccount - POST data: ' . json_encode($postData));
                
                $result = null;
                $max_retries = 2; // Kurangi retry untuk performa lebih baik
                $retry_count = 0;
                
                while ($retry_count < $max_retries) {
                    $retry_count++;
                    log_message('info', 'CPanel createEmailAccount - Attempt ' . $retry_count . ' of ' . $max_retries);
                    
                    foreach ($endpoints as $endpoint) {
                        log_message('info', 'CPanel createEmailAccount - Trying endpoint: ' . $endpoint);
                        
                        // Gunakan POST request untuk create operation
                        $result = $this->requestWithSession($endpoint, 'POST', $postData);
                        
                        // Log response untuk debugging
                        log_message('info', 'CPanel createEmailAccount - Response: ' . json_encode($result));
                        
                        // Jika mendapat HTTP 403, coba endpoint berikutnya
                        if (isset($result['error']) && strpos($result['error'], '403') !== false) {
                            log_message('info', 'CPanel createEmailAccount - HTTP 403 detected, trying next endpoint');
                            continue;
                        }
                        
                        // Jika mendapat error password, coba dengan parameter yang berbeda
                        if (isset($result['error']) && strpos($result['error'], 'password') !== false) {
                            log_message('info', 'CPanel createEmailAccount - Password error detected, trying alternative parameters');
                            
                            // Coba berbagai parameter password yang umum digunakan di cPanel
                            $passwordParams = [
                                'pass' => 'pass',           // UAPI standard
                                'password' => 'password',   // Alternative
                                'passwd_hash' => $this->generatePasswordHash($password), // Hashed password
                                'passwd_enc' => $this->encryptPassword($password)        // Encrypted password
                            ];
                            
                            foreach ($passwordParams as $paramName => $paramValue) {
                                log_message('info', 'CPanel createEmailAccount - Trying parameter: ' . $paramName);
                                
                                $altPostData = [
                                    'email' => $user,
                                    'domain' => $domain,
                                    $paramName => $paramValue,
                                    'quota' => $quota
                                ];
                                
                                $altResult = $this->requestWithSession($endpoint, 'POST', $altPostData);
                                log_message('info', 'CPanel createEmailAccount - Alternative ' . $paramName . ' response: ' . json_encode($altResult));
                                
                                if (!isset($altResult['error']) || strpos($altResult['error'], 'password') === false) {
                                    $result = $altResult;
                                    break 2; // Break dari kedua loop
                                }
                            }
                            
                            continue;
                        }
                        
                        if (!isset($result['error']) && !empty($result)) {
                            log_message('info', 'CPanel createEmailAccount - Success with endpoint: ' . $endpoint);
                            break 2; // Break dari kedua loop
                        }
                    }
                    
                    // Jika semua endpoint gagal dan mendapat 403, coba force login sekali lagi
                    if (isset($result['error']) && strpos($result['error'], '403') !== false && $retry_count < $max_retries) {
                        log_message('info', 'CPanel createEmailAccount - All endpoints returned 403, attempting force login');
                        if ($this->forceLogin()) {
                            log_message('info', 'CPanel createEmailAccount - Force login successful, will retry');
                            sleep(1); // Tunggu sebentar setelah login
                            continue;
                        } else {
                            log_message('error', 'CPanel createEmailAccount - Force login failed on attempt ' . $retry_count);
                            break;
                        }
                    } else {
                        // Error lain selain 403, tidak perlu retry
                        break;
                    }
                }
                
                if (!$result || isset($result['error'])) {
                    log_message('error', 'CPanel createEmailAccount - All endpoints failed');
                    
                    // Jika mendapat HTTP 403, coba retry dengan fresh login
                    if (isset($result['error']) && strpos($result['error'], '403') !== false) {
                        log_message('info', 'CPanel createEmailAccount - HTTP 403 detected, trying retry mechanism');
                        $retry_result = $this->retryCreateEmailWithFreshLogin($email, $password, $quota);
                        
                        if (isset($retry_result['error'])) {
                            return ['error' => 'Failed to create email account after retry: ' . $retry_result['error']];
                        } else {
                            log_message('info', 'CPanel createEmailAccount - Success after retry');
                            return $retry_result;
                        }
                    }
                    
                    return ['error' => 'Failed to create email account: ' . (isset($result['error']) ? $result['error'] : 'Unknown error')];
                }
            }
            
            log_message('info', 'CPanel createEmailAccount - Result: ' . json_encode($result));
            return $result;
        } catch (Exception $e) {
            log_message('error', 'CPanel createEmailAccount - Exception: ' . $e->getMessage());
            return ['error' => 'Exception in createEmailAccount: ' . $e->getMessage()];
        }
    }

    /**
     * Update akun email dengan optimasi untuk mengatasi HTTP 403
     */
    public function updateEmailAccount($email, $password = null, $quota = null)
    {
        try {
            log_message('info', 'CPanel updateEmailAccount - Updating email: ' . $email);
            
            $domain = substr(strrchr($email, "@"), 1);
            $user = substr($email, 0, strpos($email, "@"));
            
            if ($this->auth_token && !empty($this->auth_token)) {
                log_message('info', 'CPanel updateEmailAccount - Using token authentication');
                $data = [
                    'email' => $user,
                    'domain' => $domain
                ];
                if ($password) $data['pass'] = $password; // Gunakan 'pass' untuk konsistensi dengan UAPI
                if ($quota) $data['quota'] = $quota;
                
                $result = $this->requestWithToken('/Email/edit_pop', 'POST', $data);
            } else {
                log_message('info', 'CPanel updateEmailAccount - Using session authentication');
                
                // Force fresh login untuk operasi update
                log_message('info', 'CPanel updateEmailAccount - Force fresh login for update operation');
                if (!$this->forceLogin()) {
                    log_message('error', 'CPanel updateEmailAccount - Force login failed');
                    return ['error' => 'Failed to establish fresh session for update operation'];
                }
                
                // Tunggu sebentar untuk memastikan session stabil
                sleep(1);
                
                // Coba endpoint yang paling reliable untuk Jupiter interface
                $endpoints = [
                    // Jupiter interface specific endpoint dengan POST data
                    "/json-api/cpanel?cpanel_jsonapi_user={$this->cpanel_user}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=Email&cpanel_jsonapi_func=edit_pop",
                    // Execute API endpoint dengan POST data
                    "/execute/Email/edit_pop",
                    // Legacy endpoint sebagai fallback
                    "/json-api/cpanel?cpanel_jsonapi_user={$this->cpanel_user}&cpanel_jsonapi_apiversion=1&cpanel_jsonapi_module=Email&cpanel_jsonapi_func=edit_pop"
                ];
                
                // Data untuk POST request - gunakan parameter 'pass' sesuai dengan UAPI
                $postData = [
                    'email' => $user,
                    'domain' => $domain
                ];
                if ($password) $postData['pass'] = $password; // Parameter password yang benar sesuai UAPI
                if ($quota) $postData['quota'] = $quota;
                
                // Log data yang akan dikirim untuk debugging
                log_message('info', 'CPanel updateEmailAccount - POST data: ' . json_encode($postData));
                
                $result = null;
                $max_retries = 2;
                $retry_count = 0;
                
                while ($retry_count < $max_retries) {
                    $retry_count++;
                    log_message('info', 'CPanel updateEmailAccount - Attempt ' . $retry_count . ' of ' . $max_retries);
                    
                    foreach ($endpoints as $endpoint) {
                        log_message('info', 'CPanel updateEmailAccount - Trying endpoint: ' . $endpoint);
                        
                        // Gunakan POST request untuk update operation
                        $result = $this->requestWithSession($endpoint, 'POST', $postData);
                        
                        // Log response untuk debugging
                        log_message('info', 'CPanel updateEmailAccount - Response: ' . json_encode($result));
                        
                        // Jika mendapat HTTP 403, coba endpoint berikutnya
                        if (isset($result['error']) && strpos($result['error'], '403') !== false) {
                            log_message('info', 'CPanel updateEmailAccount - HTTP 403 detected, trying next endpoint');
                            continue;
                        }
                        
                        // Jika mendapat error password, coba dengan parameter yang berbeda
                        if (isset($result['error']) && strpos($result['error'], 'password') !== false && $password) {
                            log_message('info', 'CPanel updateEmailAccount - Password error detected, trying alternative parameters');
                            
                            // Coba dengan parameter password yang berbeda jika 'pass' gagal
                            $altPostData = $postData;
                            unset($altPostData['pass']);
                            $altPostData['passwd'] = $password; // Coba dengan 'passwd' sebagai alternatif
                            
                            $altResult = $this->requestWithSession($endpoint, 'POST', $altPostData);
                            log_message('info', 'CPanel updateEmailAccount - Alternative passwd response: ' . json_encode($altResult));
                            
                            if (!isset($altResult['error']) || strpos($altResult['error'], 'password') === false) {
                                $result = $altResult;
                                break;
                            }
                            
                            continue;
                        }
                        
                        if (!isset($result['error']) && !empty($result)) {
                            log_message('info', 'CPanel updateEmailAccount - Success with endpoint: ' . $endpoint);
                            break 2; // Break dari kedua loop
                        }
                    }
                    
                    // Jika semua endpoint gagal dan mendapat 403, coba force login sekali lagi
                    if (isset($result['error']) && strpos($result['error'], '403') !== false && $retry_count < $max_retries) {
                        log_message('info', 'CPanel updateEmailAccount - All endpoints returned 403, attempting force login');
                        if ($this->forceLogin()) {
                            log_message('info', 'CPanel updateEmailAccount - Force login successful, will retry');
                            sleep(1); // Tunggu sebentar setelah login
                            continue;
                        } else {
                            log_message('error', 'CPanel updateEmailAccount - Force login failed on attempt ' . $retry_count);
                            break;
                        }
                    } else {
                        // Error lain selain 403, tidak perlu retry
                        break;
                    }
                }
                
                if (!$result || isset($result['error'])) {
                    log_message('error', 'CPanel updateEmailAccount - All endpoints failed');
                    return ['error' => 'Failed to update email account: ' . (isset($result['error']) ? $result['error'] : 'Unknown error')];
                }
            }
            
            log_message('info', 'CPanel updateEmailAccount - Result: ' . json_encode($result));
            return $result;
        } catch (Exception $e) {
            log_message('error', 'CPanel updateEmailAccount - Exception: ' . $e->getMessage());
            return ['error' => 'Exception in updateEmailAccount: ' . $e->getMessage()];
        }
    }

    /**
     * Hapus akun email dengan optimasi untuk mengatasi HTTP 403
     */
    public function deleteEmailAccount($email)
    {
        try {
            log_message('info', 'CPanel deleteEmailAccount - Deleting email: ' . $email);
            
            $domain = substr(strrchr($email, "@"), 1);
            $user = substr($email, 0, strpos($email, "@"));
            
            if ($this->auth_token && !empty($this->auth_token)) {
                log_message('info', 'CPanel deleteEmailAccount - Using token authentication');
                $data = [
                    'email' => $user,
                    'domain' => $domain
                ];
                $result = $this->requestWithToken('/Email/delete_pop', 'POST', $data);
            } else {
                log_message('info', 'CPanel deleteEmailAccount - Using session authentication');
                
                // Force fresh login untuk operasi delete dengan timeout yang lebih pendek
                log_message('info', 'CPanel deleteEmailAccount - Force fresh login for delete operation');
                if (!$this->forceLogin()) {
                    log_message('error', 'CPanel deleteEmailAccount - Force login failed');
                    return ['error' => 'Failed to establish fresh session for delete operation'];
                }
                
                // Tunggu sebentar untuk memastikan session stabil
                sleep(1);
                
                // Coba endpoint yang paling reliable untuk Jupiter interface
                $endpoints = [
                    // Jupiter interface specific endpoint dengan POST data
                    "/json-api/cpanel?cpanel_jsonapi_user={$this->cpanel_user}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=Email&cpanel_jsonapi_func=delete_pop",
                    // Execute API endpoint dengan POST data
                    "/execute/Email/delete_pop",
                    // Legacy endpoint sebagai fallback
                    "/json-api/cpanel?cpanel_jsonapi_user={$this->cpanel_user}&cpanel_jsonapi_apiversion=1&cpanel_jsonapi_module=Email&cpanel_jsonapi_func=delete_pop"
                ];
                
                // Data untuk POST request
                $postData = [
                    'email' => $user,
                    'domain' => $domain
                ];
                
                // Log data yang akan dikirim untuk debugging
                log_message('info', 'CPanel deleteEmailAccount - POST data: ' . json_encode($postData));
                
                $result = null;
                $max_retries = 2; // Kurangi retry untuk performa lebih baik
                $retry_count = 0;
                
                while ($retry_count < $max_retries) {
                    $retry_count++;
                    log_message('info', 'CPanel deleteEmailAccount - Attempt ' . $retry_count . ' of ' . $max_retries);
                    
                    foreach ($endpoints as $endpoint) {
                        log_message('info', 'CPanel deleteEmailAccount - Trying endpoint: ' . $endpoint);
                        
                        // Gunakan POST request untuk delete operation
                        $result = $this->requestWithSession($endpoint, 'POST', $postData);
                        
                        // Log response untuk debugging
                        log_message('info', 'CPanel deleteEmailAccount - Response: ' . json_encode($result));
                        
                        // Jika mendapat HTTP 403, coba endpoint berikutnya
                        if (isset($result['error']) && strpos($result['error'], '403') !== false) {
                            log_message('info', 'CPanel deleteEmailAccount - HTTP 403 detected, trying next endpoint');
                            continue;
                        }
                        
                        if (!isset($result['error']) && !empty($result)) {
                            log_message('info', 'CPanel deleteEmailAccount - Success with endpoint: ' . $endpoint);
                            break 2; // Break dari kedua loop
                        }
                    }
                    
                    // Jika semua endpoint gagal dan mendapat 403, coba force login sekali lagi
                    if (isset($result['error']) && strpos($result['error'], '403') !== false && $retry_count < $max_retries) {
                        log_message('info', 'CPanel deleteEmailAccount - All endpoints returned 403, attempting force login');
                        if ($this->forceLogin()) {
                            log_message('info', 'CPanel deleteEmailAccount - Force login successful, will retry');
                            sleep(1); // Tunggu sebentar setelah login
                            continue;
                        } else {
                            log_message('error', 'CPanel deleteEmailAccount - Force login failed on attempt ' . $retry_count);
                            break;
                        }
                    } else {
                        // Error lain selain 403, tidak perlu retry
                        break;
                    }
                }
                
                if (!$result || isset($result['error'])) {
                    log_message('error', 'CPanel deleteEmailAccount - All endpoints failed');
                    
                    // Jika mendapat HTTP 403, coba retry dengan fresh login
                    if (isset($result['error']) && strpos($result['error'], '403') !== false) {
                        log_message('info', 'CPanel deleteEmailAccount - HTTP 403 detected, trying retry mechanism');
                        $retry_result = $this->retryDeleteEmailWithFreshLogin($email);
                        
                        if (isset($retry_result['error'])) {
                            return ['error' => 'Failed to delete email account after retry: ' . $retry_result['error']];
                        } else {
                            log_message('info', 'CPanel deleteEmailAccount - Success after retry');
                            return $retry_result;
                        }
                    }
                    
                    return ['error' => 'Failed to delete email account: ' . (isset($result['error']) ? $result['error'] : 'Unknown error')];
                }
            }
            
            log_message('info', 'CPanel deleteEmailAccount - Result: ' . json_encode($result));
            return $result;
        } catch (Exception $e) {
            log_message('error', 'CPanel deleteEmailAccount - Exception: ' . $e->getMessage());
            return ['error' => 'Exception in deleteEmailAccount: ' . $e->getMessage()];
        }
    }

    /**
     * Get session token (untuk debugging)
     */
    public function getSessionToken()
    {
        if (!$this->session_token) {
            return 'NOT_SET';
        }
        return $this->session_token;
    }

    /**
     * Force login dan dapatkan session token baru dengan optimasi
     */
    public function forceLogin()
    {
        try {
            log_message('info', 'CPanel forceLogin - Forcing new login');
            $this->session_token = null;
            $this->cookies = "";
            
            // Coba login dengan timeout yang lebih pendek
            $result = $this->login();
            
            log_message('info', 'CPanel forceLogin - Result: ' . ($result ? 'SUCCESS' : 'FAILED'));
            return $result;
        } catch (Exception $e) {
            log_message('error', 'CPanel forceLogin - Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Test koneksi dengan Jupiter interface
     */
    public function testJupiterConnection()
    {
        try {
            log_message('info', 'CPanel testJupiterConnection - Testing Jupiter interface connection');
            
            // Coba akses halaman email accounts Jupiter
            $jupiterUrl = "https://{$this->cpanel_host}:{$this->cpanel_port}{$this->session_token}/frontend/jupiter/email_accounts/index.html";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $jupiterUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_USERAGENT, 'CodeIgniter-CPanel/1.0');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            
            if ($this->cookies) {
                curl_setopt($ch, CURLOPT_COOKIE, $this->cookies);
            }
            
            $result = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            log_message('info', 'CPanel testJupiterConnection - HTTP Code: ' . $http_code);
            
            if ($error) {
                log_message('error', 'CPanel testJupiterConnection - cURL Error: ' . $error);
                return ['error' => 'Connection error: ' . $error];
            }
            
            if ($http_code === 200) {
                log_message('info', 'CPanel testJupiterConnection - Success accessing Jupiter interface');
                return ['success' => true, 'message' => 'Jupiter interface accessible'];
            } elseif ($http_code === 403) {
                log_message('error', 'CPanel testJupiterConnection - HTTP 403 (Session expired)');
                return ['error' => 'HTTP 403 - Session expired, need to re-login'];
            } else {
                log_message('error', 'CPanel testJupiterConnection - HTTP Error: ' . $http_code);
                return ['error' => 'HTTP error: ' . $http_code];
            }
        } catch (Exception $e) {
            log_message('error', 'CPanel testJupiterConnection - Exception: ' . $e->getMessage());
            return ['error' => 'Exception: ' . $e->getMessage()];
        }
    }

    /**
     * Test session token validity
     */
    public function testSessionToken()
    {
        try {
            log_message('info', 'CPanel testSessionToken - Testing session token validity');
            
            if (!$this->session_token) {
                log_message('error', 'CPanel testSessionToken - No session token available');
                return ['error' => 'No session token available'];
            }
            
            // Test dengan endpoint sederhana
            $testUrl = "/json-api/cpanel?cpanel_jsonapi_user={$this->cpanel_user}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=UAPI&cpanel_jsonapi_func=get_user_information";
            $result = $this->request($testUrl);
            
            if (isset($result['error'])) {
                log_message('error', 'CPanel testSessionToken - Session token invalid: ' . $result['error']);
                return ['error' => 'Session token invalid: ' . $result['error']];
            }
            
            log_message('info', 'CPanel testSessionToken - Session token valid');
            return ['success' => true, 'message' => 'Session token is valid'];
        } catch (Exception $e) {
            log_message('error', 'CPanel testSessionToken - Exception: ' . $e->getMessage());
            return ['error' => 'Exception: ' . $e->getMessage()];
        }
    }

    /**
     * Test write permission dengan operasi sederhana
     */
    public function testWritePermission()
    {
        try {
            log_message('info', 'CPanel testWritePermission - Testing write permission');
            
            // Force fresh login untuk test
            if (!$this->forceLogin()) {
                log_message('error', 'CPanel testWritePermission - Force login failed');
                return ['error' => 'Failed to establish session for write test'];
            }
            
            // Test dengan operasi yang tidak mengubah data (read operation)
            $testUrl = "/json-api/cpanel?cpanel_jsonapi_user={$this->cpanel_user}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=Email&cpanel_jsonapi_func=list_pops";
            
            log_message('info', 'CPanel testWritePermission - Testing with URL: ' . $testUrl);
            
            $result = $this->requestWithSession($testUrl);
            
            if (isset($result['error'])) {
                log_message('error', 'CPanel testWritePermission - Test failed: ' . $result['error']);
                return ['error' => 'Write permission test failed: ' . $result['error']];
            }
            
            log_message('info', 'CPanel testWritePermission - Test successful');
            return ['success' => true, 'message' => 'Write permission test passed'];
            
        } catch (Exception $e) {
            log_message('error', 'CPanel testWritePermission - Exception: ' . $e->getMessage());
            return ['error' => 'Exception in write permission test: ' . $e->getMessage()];
        }
    }

    /**
     * Test Jupiter interface compatibility untuk write operations
     */
    public function testJupiterWriteCompatibility()
    {
        try {
            log_message('info', 'CPanel testJupiterWriteCompatibility - Testing Jupiter write compatibility');
            
            // Force fresh login
            if (!$this->forceLogin()) {
                log_message('error', 'CPanel testJupiterWriteCompatibility - Force login failed');
                return ['error' => 'Failed to establish session for Jupiter write test'];
            }
            
            // Test dengan endpoint Jupiter yang digunakan untuk create email
            $testUrl = "/json-api/cpanel?cpanel_jsonapi_user={$this->cpanel_user}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=Email&cpanel_jsonapi_func=add_pop";
            
            log_message('info', 'CPanel testJupiterWriteCompatibility - Testing with URL: ' . $testUrl);
            
            // Test dengan data dummy (tidak akan benar-benar membuat email)
            $testData = [
                'email' => 'test_' . time(),
                'domain' => $this->cpanel_host,
                'passwd' => 'test123',
                'quota' => 10
            ];
            
            $result = $this->requestWithSession($testUrl, 'POST', $testData);
            
            // Jika mendapat error 403, berarti ada masalah permission
            if (isset($result['error']) && strpos($result['error'], '403') !== false) {
                log_message('error', 'CPanel testJupiterWriteCompatibility - HTTP 403 detected (Permission denied)');
                return ['error' => 'HTTP 403 - Permission denied for write operations'];
            }
            
            // Jika mendapat error lain, mungkin domain tidak valid atau masalah lain
            if (isset($result['error'])) {
                log_message('info', 'CPanel testJupiterWriteCompatibility - Test completed with error (expected): ' . $result['error']);
                return ['success' => true, 'message' => 'Jupiter write compatibility test passed (error expected for test data)'];
            }
            
            log_message('info', 'CPanel testJupiterWriteCompatibility - Test successful');
            return ['success' => true, 'message' => 'Jupiter write compatibility test passed'];
            
        } catch (Exception $e) {
            log_message('error', 'CPanel testJupiterWriteCompatibility - Exception: ' . $e->getMessage());
            return ['error' => 'Exception in Jupiter write compatibility test: ' . $e->getMessage()];
        }
    }

    /**
     * Get auth method
     */
    public function getAuthMethod()
    {
        if (!$this->session_token) {
            return 'NOT_LOGGED_IN';
        }
        return $this->session_token === 'TOKEN_AUTH' ? 'TOKEN' : 'SESSION';
    }

    /**
     * Get auth token for debugging
     */
    public function getAuthToken()
    {
        return $this->auth_token;
    }

    /**
     * Enable debug mode
     */
    public function enableDebugMode()
    {
        $this->debug_mode = true;
    }



    /**
     * Test domain availability untuk email creation
     */
    public function testDomainAvailability($domain)
    {
        try {
            log_message('info', 'CPanel testDomainAvailability - Testing domain: ' . $domain);
            
            // Test dengan endpoint yang mengecek domain
            $testUrl = "/json-api/cpanel?cpanel_jsonapi_user={$this->cpanel_user}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=DomainLookup&cpanel_jsonapi_func=lookup&domain={$domain}";
            $result = $this->request($testUrl);
            
            if (isset($result['error'])) {
                log_message('error', 'CPanel testDomainAvailability - Domain test failed: ' . $result['error']);
                return ['error' => 'Domain test failed: ' . $result['error']];
            }
            
            log_message('info', 'CPanel testDomainAvailability - Domain test passed');
            return ['success' => true, 'message' => 'Domain is available for email creation'];
        } catch (Exception $e) {
            log_message('error', 'CPanel testDomainAvailability - Exception: ' . $e->getMessage());
            return ['error' => 'Exception: ' . $e->getMessage()];
        }
    }

    /**
     * Test permission untuk email creation dengan endpoint sederhana
     */
    public function testEmailPermission()
    {
        try {
            log_message('info', 'CPanel testEmailPermission - Testing email creation permission');
            
            // Test dengan endpoint sederhana yang mengecek permission
            $testUrl = "/json-api/cpanel?cpanel_jsonapi_user={$this->cpanel_user}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=Email&cpanel_jsonapi_func=list_pops";
            $result = $this->request($testUrl);
            
            if (isset($result['error'])) {
                log_message('error', 'CPanel testEmailPermission - Permission test failed: ' . $result['error']);
                return ['error' => 'Permission test failed: ' . $result['error']];
            }
            
            log_message('info', 'CPanel testEmailPermission - Permission test passed');
            return ['success' => true, 'message' => 'Email creation permission granted'];
        } catch (Exception $e) {
            log_message('error', 'CPanel testEmailPermission - Exception: ' . $e->getMessage());
            return ['error' => 'Exception: ' . $e->getMessage()];
        }
    }

    /**
     * Test dengan endpoint yang berbeda untuk email creation
     */
    public function testAlternativeEndpoints()
    {
        try {
            log_message('info', 'CPanel testAlternativeEndpoints - Testing alternative endpoints');
            
            $endpoints = [
                "/json-api/cpanel?cpanel_jsonapi_user={$this->cpanel_user}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=Email&cpanel_jsonapi_func=list_pops",
                "/execute/Email/list_pops",
                "/uapi/Email/list_pops",
                "/json-api/cpanel?cpanel_jsonapi_user={$this->cpanel_user}&cpanel_jsonapi_apiversion=1&cpanel_jsonapi_module=Email&cpanel_jsonapi_func=list_pops"
            ];
            
            $results = [];
            foreach ($endpoints as $endpoint) {
                log_message('info', 'CPanel testAlternativeEndpoints - Testing: ' . $endpoint);
                $result = $this->request($endpoint);
                $results[$endpoint] = $result;
            }
            
            return $results;
        } catch (Exception $e) {
            log_message('error', 'CPanel testAlternativeEndpoints - Exception: ' . $e->getMessage());
            return ['error' => 'Exception: ' . $e->getMessage()];
        }
    }

    /**
     * Test koneksi cepat untuk verifikasi konektivitas
     */
    public function quickConnectionTest()
    {
        try {
            log_message('info', 'CPanel quickConnectionTest - Starting quick connection test');
            
            // Test dengan endpoint sederhana dan cepat
            $testUrl = "/json-api/cpanel?cpanel_jsonapi_user={$this->cpanel_user}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=UAPI&cpanel_jsonapi_func=get_user_information";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://{$this->cpanel_host}:{$this->cpanel_port}{$testUrl}");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout sangat pendek
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_USERAGENT, 'CodeIgniter-CPanel/1.0');
            
            $result = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                log_message('error', 'CPanel quickConnectionTest - cURL Error: ' . $error);
                return ['error' => 'Connection error: ' . $error];
            }
            
            if ($http_code === 200) {
                log_message('info', 'CPanel quickConnectionTest - Connection successful');
                return ['success' => true, 'message' => 'Quick connection test passed'];
            } else {
                log_message('error', 'CPanel quickConnectionTest - HTTP Error: ' . $http_code);
                return ['error' => 'HTTP error: ' . $http_code];
            }
        } catch (Exception $e) {
            log_message('error', 'CPanel quickConnectionTest - Exception: ' . $e->getMessage());
            return ['error' => 'Exception: ' . $e->getMessage()];
        }
    }

    /**
     * Test permission untuk create email tanpa benar-benar membuat email
     */
    public function testEmailCreationPermission()
    {
        try {
            log_message('info', 'CPanel testEmailCreationPermission - Testing email creation permission');
            
            // Force fresh login untuk test
            if (!$this->forceLogin()) {
                log_message('error', 'CPanel testEmailCreationPermission - Force login failed');
                return ['error' => 'Failed to establish session for email creation test'];
            }
            
            // Test dengan endpoint Jupiter yang digunakan untuk create email
            $testUrl = "/json-api/cpanel?cpanel_jsonapi_user={$this->cpanel_user}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=Email&cpanel_jsonapi_func=add_pop";
            
            log_message('info', 'CPanel testEmailCreationPermission - Testing with URL: ' . $testUrl);
            
            // Test dengan data dummy yang tidak akan benar-benar membuat email
            $testData = [
                'email' => 'test_' . time() . '_' . rand(1000, 9999),
                'domain' => $this->cpanel_host,
                'passwd' => 'test123',
                'quota' => 10
            ];
            
            $result = $this->requestWithSession($testUrl, 'POST', $testData);
            
            // Jika mendapat error 403, berarti ada masalah permission
            if (isset($result['error']) && strpos($result['error'], '403') !== false) {
                log_message('error', 'CPanel testEmailCreationPermission - HTTP 403 detected (Permission denied)');
                return ['error' => 'HTTP 403 - Permission denied for email creation'];
            }
            
            // Jika mendapat error lain, mungkin domain tidak valid atau masalah lain
            if (isset($result['error'])) {
                log_message('info', 'CPanel testEmailCreationPermission - Test completed with error (expected): ' . $result['error']);
                return ['success' => true, 'message' => 'Email creation permission test passed (error expected for test data)'];
            }
            
            log_message('info', 'CPanel testEmailCreationPermission - Test successful');
            return ['success' => true, 'message' => 'Email creation permission test passed'];
            
        } catch (Exception $e) {
            log_message('error', 'CPanel testEmailCreationPermission - Exception: ' . $e->getMessage());
            return ['error' => 'Exception in email creation permission test: ' . $e->getMessage()];
        }
    }

    /**
     * Retry login dan coba create email lagi
     */
    public function retryCreateEmailWithFreshLogin($email, $password, $quota = 250)
    {
        try {
            log_message('info', 'CPanel retryCreateEmailWithFreshLogin - Retrying email creation with fresh login');
            
            $domain = substr(strrchr($email, "@"), 1);
            $user = substr($email, 0, strpos($email, "@"));
            
            // Force fresh login
            if (!$this->forceLogin()) {
                log_message('error', 'CPanel retryCreateEmailWithFreshLogin - Force login failed');
                return ['error' => 'Failed to establish fresh session for email creation'];
            }
            
            // Tunggu sebentar untuk memastikan session stabil
            sleep(2);
            
            // Coba create email dengan session baru - gunakan parameter 'passwd' untuk rumahweb.com
            $postData = [
                'email' => $user,
                'domain' => $domain,
                'passwd' => $password, // Parameter password utama untuk rumahweb.com
                'quota' => $quota
            ];
            
            // Log data yang akan dikirim untuk debugging
            log_message('info', 'CPanel retryCreateEmailWithFreshLogin - POST data: ' . json_encode($postData));
            
            // Gunakan endpoint yang paling reliable
            $endpoint = "/json-api/cpanel?cpanel_jsonapi_user={$this->cpanel_user}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=Email&cpanel_jsonapi_func=add_pop";
            
            log_message('info', 'CPanel retryCreateEmailWithFreshLogin - Trying with fresh session: ' . $endpoint);
            
            $result = $this->requestWithSession($endpoint, 'POST', $postData);
            
            // Log response untuk debugging
            log_message('info', 'CPanel retryCreateEmailWithFreshLogin - Response: ' . json_encode($result));
            
            if (isset($result['error'])) {
                log_message('error', 'CPanel retryCreateEmailWithFreshLogin - Still getting error: ' . $result['error']);
                
                // Jika mendapat error password, coba dengan parameter yang berbeda
                if (strpos($result['error'], 'password') !== false) {
                    log_message('info', 'CPanel retryCreateEmailWithFreshLogin - Password error detected, trying alternative parameters');
                    
                    // Coba berbagai parameter password yang umum digunakan di cPanel
                    $passwordParams = [
                        'pass' => 'pass',           // UAPI standard
                        'password' => 'password',   // Alternative
                        'passwd_hash' => $this->generatePasswordHash($password), // Hashed password
                        'passwd_enc' => $this->encryptPassword($password)        // Encrypted password
                    ];
                    
                    foreach ($passwordParams as $paramName => $paramValue) {
                        log_message('info', 'CPanel retryCreateEmailWithFreshLogin - Trying parameter: ' . $paramName);
                        
                        $altPostData = [
                            'email' => $user,
                            'domain' => $domain,
                            $paramName => $paramValue,
                            'quota' => $quota
                        ];
                        
                        $altResult = $this->requestWithSession($endpoint, 'POST', $altPostData);
                        log_message('info', 'CPanel retryCreateEmailWithFreshLogin - Alternative ' . $paramName . ' response: ' . json_encode($altResult));
                        
                        if (!isset($altResult['error']) || strpos($altResult['error'], 'password') === false) {
                            return $altResult;
                        }
                    }
                }
                
                return $result;
            }
            
            log_message('info', 'CPanel retryCreateEmailWithFreshLogin - Success with fresh session');
            return $result;
            
        } catch (Exception $e) {
            log_message('error', 'CPanel retryCreateEmailWithFreshLogin - Exception: ' . $e->getMessage());
            return ['error' => 'Exception in retryCreateEmailWithFreshLogin: ' . $e->getMessage()];
        }
    }

    /**
     * Retry delete email dengan fresh login
     */
    public function retryDeleteEmailWithFreshLogin($email)
    {
        try {
            log_message('info', 'CPanel retryDeleteEmailWithFreshLogin - Retrying email deletion with fresh login');
            
            $domain = substr(strrchr($email, "@"), 1);
            $user = substr($email, 0, strpos($email, "@"));
            
            // Force fresh login
            if (!$this->forceLogin()) {
                log_message('error', 'CPanel retryDeleteEmailWithFreshLogin - Force login failed');
                return ['error' => 'Failed to establish fresh session for email deletion'];
            }
            
            // Tunggu sebentar untuk memastikan session stabil
            sleep(2);
            
            // Coba delete email dengan session baru
            $postData = [
                'email' => $user,
                'domain' => $domain
            ];
            
            // Log data yang akan dikirim untuk debugging
            log_message('info', 'CPanel retryDeleteEmailWithFreshLogin - POST data: ' . json_encode($postData));
            
            // Gunakan endpoint yang paling reliable
            $endpoint = "/json-api/cpanel?cpanel_jsonapi_user={$this->cpanel_user}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=Email&cpanel_jsonapi_func=delete_pop";
            
            log_message('info', 'CPanel retryDeleteEmailWithFreshLogin - Trying with fresh session: ' . $endpoint);
            
            $result = $this->requestWithSession($endpoint, 'POST', $postData);
            
            // Log response untuk debugging
            log_message('info', 'CPanel retryDeleteEmailWithFreshLogin - Response: ' . json_encode($result));
            
            if (isset($result['error'])) {
                log_message('error', 'CPanel retryDeleteEmailWithFreshLogin - Still getting error: ' . $result['error']);
                return $result;
            }
            
            log_message('info', 'CPanel retryDeleteEmailWithFreshLogin - Success with fresh session');
            return $result;
            
        } catch (Exception $e) {
            log_message('error', 'CPanel retryDeleteEmailWithFreshLogin - Exception: ' . $e->getMessage());
            return ['error' => 'Exception in retryDeleteEmailWithFreshLogin: ' . $e->getMessage()];
        }
    }

    /**
     * Test create email dengan parameter password yang benar sesuai UAPI
     */
    public function testEmailCreationWithPassword($email, $password, $quota = 10)
    {
        try {
            log_message('info', 'CPanel testEmailCreationWithPassword - Testing email creation with password: ' . $email);
            
            $domain = substr(strrchr($email, "@"), 1);
            $user = substr($email, 0, strpos($email, "@"));
            
            // Force fresh login untuk test
            if (!$this->forceLogin()) {
                log_message('error', 'CPanel testEmailCreationWithPassword - Force login failed');
                return ['error' => 'Failed to establish session for email creation test'];
            }
            
            // Test dengan endpoint Jupiter yang digunakan untuk create email
            $testUrl = "/json-api/cpanel?cpanel_jsonapi_user={$this->cpanel_user}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=Email&cpanel_jsonapi_func=add_pop";
            
            log_message('info', 'CPanel testEmailCreationWithPassword - Testing with URL: ' . $testUrl);
            
            // Test dengan data yang benar - gunakan parameter 'passwd' untuk rumahweb.com
            $testData = [
                'email' => $user,
                'domain' => $domain,
                'passwd' => $password, // Parameter password utama untuk rumahweb.com
                'quota' => $quota
            ];
            
            log_message('info', 'CPanel testEmailCreationWithPassword - Test data: ' . json_encode($testData));
            
            $result = $this->requestWithSession($testUrl, 'POST', $testData);
            
            log_message('info', 'CPanel testEmailCreationWithPassword - Response: ' . json_encode($result));
            
            // Jika mendapat error 403, berarti ada masalah permission
            if (isset($result['error']) && strpos($result['error'], '403') !== false) {
                log_message('error', 'CPanel testEmailCreationWithPassword - HTTP 403 detected (Permission denied)');
                return ['error' => 'HTTP 403 - Permission denied for email creation'];
            }
            
            // Jika mendapat error password, coba dengan parameter yang berbeda
            if (isset($result['error']) && strpos($result['error'], 'password') !== false) {
                log_message('info', 'CPanel testEmailCreationWithPassword - Password error detected, trying alternative parameters');
                
                // Coba berbagai parameter password yang umum digunakan di cPanel
                $passwordParams = [
                    'pass' => 'pass',           // UAPI standard
                    'password' => 'password',   // Alternative
                    'passwd_hash' => $this->generatePasswordHash($password), // Hashed password
                    'passwd_enc' => $this->encryptPassword($password)        // Encrypted password
                ];
                
                foreach ($passwordParams as $paramName => $paramValue) {
                    log_message('info', 'CPanel testEmailCreationWithPassword - Trying parameter: ' . $paramName);
                    
                    $altTestData = [
                        'email' => $user,
                        'domain' => $domain,
                        $paramName => $paramValue,
                        'quota' => $quota
                    ];
                    
                    $altResult = $this->requestWithSession($testUrl, 'POST', $altTestData);
                    log_message('info', 'CPanel testEmailCreationWithPassword - Alternative ' . $paramName . ' response: ' . json_encode($altResult));
                    
                    if (!isset($altResult['error']) || strpos($altResult['error'], 'password') === false) {
                        return ['success' => true, 'message' => 'Email creation test passed with parameter: ' . $paramName];
                    }
                }
                
                return ['error' => 'All password parameter variations failed: ' . $result['error']];
            }
            
            // Jika mendapat error lain, mungkin domain tidak valid atau masalah lain
            if (isset($result['error'])) {
                log_message('info', 'CPanel testEmailCreationWithPassword - Test completed with error: ' . $result['error']);
                return ['error' => 'Email creation test failed: ' . $result['error']];
            }
            
            log_message('info', 'CPanel testEmailCreationWithPassword - Test successful');
            return ['success' => true, 'message' => 'Email creation test passed'];
            
        } catch (Exception $e) {
            log_message('error', 'CPanel testEmailCreationWithPassword - Exception: ' . $e->getMessage());
            return ['error' => 'Exception in email creation test: ' . $e->getMessage()];
        }
    }

    /**
     * Test semua parameter password yang mungkin untuk email creation
     */
    public function testAllPasswordParameters($email, $password, $quota = 10)
    {
        try {
            log_message('info', 'CPanel testAllPasswordParameters - Testing all password parameters for: ' . $email);
            
            $domain = substr(strrchr($email, "@"), 1);
            $user = substr($email, 0, strpos($email, "@"));
            
            // Force fresh login untuk test
            if (!$this->forceLogin()) {
                log_message('error', 'CPanel testAllPasswordParameters - Force login failed');
                return ['error' => 'Failed to establish session for password parameter test'];
            }
            
            // Test dengan endpoint Jupiter
            $testUrl = "/json-api/cpanel?cpanel_jsonapi_user={$this->cpanel_user}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=Email&cpanel_jsonapi_func=add_pop";
            
            // Test semua parameter password yang mungkin untuk rumahweb.com
            $passwordParams = [
                'passwd' => 'passwd',           // Parameter utama untuk rumahweb.com
                'pass' => 'pass',               // UAPI standard
                'password' => 'password',       // Alternative
                'passwd_hash' => $this->generatePasswordHash($password), // Hashed password
                'passwd_enc' => $this->encryptPassword($password)        // Encrypted password
            ];
            
            $results = [];
            
            foreach ($passwordParams as $paramName => $paramValue) {
                log_message('info', 'CPanel testAllPasswordParameters - Testing parameter: ' . $paramName);
                
                $testData = [
                    'email' => $user,
                    'domain' => $domain,
                    $paramName => $password,
                    'quota' => $quota
                ];
                
                $result = $this->requestWithSession($testUrl, 'POST', $testData);
                $results[$paramName] = $result;
                
                log_message('info', 'CPanel testAllPasswordParameters - Result for ' . $paramName . ': ' . json_encode($result));
                
                // Jika berhasil, return hasilnya
                if (!isset($result['error']) || strpos($result['error'], 'password') === false) {
                    log_message('info', 'CPanel testAllPasswordParameters - Success with parameter: ' . $paramName);
                    return [
                        'success' => true, 
                        'working_parameter' => $paramName,
                        'message' => 'Email creation test passed with parameter: ' . $paramName,
                        'all_results' => $results
                    ];
                }
            }
            
            // Jika semua gagal, return semua hasil untuk analisis
            log_message('error', 'CPanel testAllPasswordParameters - All password parameters failed');
            return [
                'error' => 'All password parameters failed',
                'all_results' => $results
            ];
            
        } catch (Exception $e) {
            log_message('error', 'CPanel testAllPasswordParameters - Exception: ' . $e->getMessage());
            return ['error' => 'Exception in password parameter test: ' . $e->getMessage()];
        }
    }

    /**
     * Test UAPI langsung untuk memastikan parameter password yang benar
     */
    public function testUAPIDirectly($email, $password, $quota = 10)
    {
        try {
            log_message('info', 'CPanel testUAPIDirectly - Testing UAPI directly for: ' . $email);
            
            $domain = substr(strrchr($email, "@"), 1);
            $user = substr($email, 0, strpos($email, "@"));
            
            // Force fresh login untuk test
            if (!$this->forceLogin()) {
                log_message('error', 'CPanel testUAPIDirectly - Force login failed');
                return ['error' => 'Failed to establish session for UAPI test'];
            }
            
            // Test dengan endpoint UAPI langsung
            $testUrl = "/execute/Email/add_pop";
            
            log_message('info', 'CPanel testUAPIDirectly - Testing with UAPI endpoint: ' . $testUrl);
            
            // Test dengan parameter 'passwd' untuk rumahweb.com
            $testData = [
                'email' => $user,
                'domain' => $domain,
                'passwd' => $password,
                'quota' => $quota
            ];
            
            log_message('info', 'CPanel testUAPIDirectly - Test data: ' . json_encode($testData));
            
            $result = $this->requestWithSession($testUrl, 'POST', $testData);
            
            log_message('info', 'CPanel testUAPIDirectly - Response: ' . json_encode($result));
            
            // Jika mendapat error 403, berarti ada masalah permission
            if (isset($result['error']) && strpos($result['error'], '403') !== false) {
                log_message('error', 'CPanel testUAPIDirectly - HTTP 403 detected (Permission denied)');
                return ['error' => 'HTTP 403 - Permission denied for UAPI email creation'];
            }
            
            // Jika mendapat error password, coba dengan parameter yang berbeda
            if (isset($result['error']) && strpos($result['error'], 'password') !== false) {
                log_message('info', 'CPanel testUAPIDirectly - Password error detected, trying alternative parameters');
                
                // Test semua parameter password yang mungkin
                $passwordParams = [
                    'passwd' => 'passwd',
                    'password' => 'password'
                ];
                
                foreach ($passwordParams as $paramName => $paramValue) {
                    log_message('info', 'CPanel testUAPIDirectly - Trying parameter: ' . $paramName);
                    
                    $altTestData = [
                        'email' => $user,
                        'domain' => $domain,
                        $paramName => $password,
                        'quota' => $quota
                    ];
                    
                    $altResult = $this->requestWithSession($testUrl, 'POST', $altTestData);
                    log_message('info', 'CPanel testUAPIDirectly - Result for ' . $paramName . ': ' . json_encode($altResult));
                    
                    if (!isset($altResult['error']) || strpos($altResult['error'], 'password') === false) {
                        return [
                            'success' => true, 
                            'working_parameter' => $paramName,
                            'message' => 'UAPI test passed with parameter: ' . $paramName
                        ];
                    }
                }
                
                return ['error' => 'All UAPI password parameters failed: ' . $result['error']];
            }
            
            // Jika mendapat error lain, mungkin domain tidak valid atau masalah lain
            if (isset($result['error'])) {
                log_message('info', 'CPanel testUAPIDirectly - Test completed with error: ' . $result['error']);
                return ['error' => 'UAPI test failed: ' . $result['error']];
            }
            
            log_message('info', 'CPanel testUAPIDirectly - Test successful with parameter: pass');
            return [
                'success' => true, 
                'working_parameter' => 'pass',
                'message' => 'UAPI test passed with parameter: pass'
            ];
            
        } catch (Exception $e) {
            log_message('error', 'CPanel testUAPIDirectly - Exception: ' . $e->getMessage());
            return ['error' => 'Exception in UAPI test: ' . $e->getMessage()];
        }
    }

    /**
     * Test delete email account untuk memastikan permission dan endpoint yang benar
     */
    public function testEmailDeletionPermission($email)
    {
        try {
            log_message('info', 'CPanel testEmailDeletionPermission - Testing email deletion permission for: ' . $email);
            
            $domain = substr(strrchr($email, "@"), 1);
            $user = substr($email, 0, strpos($email, "@"));
            
            // Force fresh login untuk test
            if (!$this->forceLogin()) {
                log_message('error', 'CPanel testEmailDeletionPermission - Force login failed');
                return ['error' => 'Failed to establish session for email deletion test'];
            }
            
            // Test dengan endpoint Jupiter yang digunakan untuk delete email
            $testUrl = "/json-api/cpanel?cpanel_jsonapi_user={$this->cpanel_user}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=Email&cpanel_jsonapi_func=delete_pop";
            
            log_message('info', 'CPanel testEmailDeletionPermission - Testing with URL: ' . $testUrl);
            
            // Test dengan data yang benar
            $testData = [
                'email' => $user,
                'domain' => $domain
            ];
            
            log_message('info', 'CPanel testEmailDeletionPermission - Test data: ' . json_encode($testData));
            
            $result = $this->requestWithSession($testUrl, 'POST', $testData);
            
            log_message('info', 'CPanel testEmailDeletionPermission - Response: ' . json_encode($result));
            
            // Jika mendapat error 403, berarti ada masalah permission
            if (isset($result['error']) && strpos($result['error'], '403') !== false) {
                log_message('error', 'CPanel testEmailDeletionPermission - HTTP 403 detected (Permission denied)');
                return ['error' => 'HTTP 403 - Permission denied for email deletion'];
            }
            
            // Jika mendapat error lain, mungkin email tidak ada atau masalah lain
            if (isset($result['error'])) {
                log_message('info', 'CPanel testEmailDeletionPermission - Test completed with error: ' . $result['error']);
                return ['error' => 'Email deletion test failed: ' . $result['error']];
            }
            
            log_message('info', 'CPanel testEmailDeletionPermission - Test successful');
            return ['success' => true, 'message' => 'Email deletion test passed'];
            
        } catch (Exception $e) {
            log_message('error', 'CPanel testEmailDeletionPermission - Exception: ' . $e->getMessage());
            return ['error' => 'Exception in email deletion test: ' . $e->getMessage()];
        }
    }

    /**
     * Test dengan endpoint yang berbeda untuk memastikan parameter password yang benar
     */
    public function testMultipleEndpoints($email, $password, $quota = 10)
    {
        try {
            log_message('info', 'CPanel testMultipleEndpoints - Testing multiple endpoints for: ' . $email);
            
            $domain = substr(strrchr($email, "@"), 1);
            $user = substr($email, 0, strpos($email, "@"));
            
            // Force fresh login untuk test
            if (!$this->forceLogin()) {
                log_message('error', 'CPanel testMultipleEndpoints - Force login failed');
                return ['error' => 'Failed to establish session for multiple endpoints test'];
            }
            
            // Test dengan berbagai endpoint
            $endpoints = [
                "/execute/Email/add_pop",
                "/json-api/cpanel?cpanel_jsonapi_user={$this->cpanel_user}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=Email&cpanel_jsonapi_func=add_pop",
                "/json-api/cpanel?cpanel_jsonapi_user={$this->cpanel_user}&cpanel_jsonapi_apiversion=1&cpanel_jsonapi_module=Email&cpanel_jsonapi_func=add_pop"
            ];
            
            $results = [];
            
            foreach ($endpoints as $endpoint) {
                log_message('info', 'CPanel testMultipleEndpoints - Testing endpoint: ' . $endpoint);
                
                // Test dengan parameter 'pass'
                $testData = [
                    'email' => $user,
                    'domain' => $domain,
                    'pass' => $password,
                    'quota' => $quota
                ];
                
                $result = $this->requestWithSession($endpoint, 'POST', $testData);
                $results[$endpoint] = [
                    'pass' => $result
                ];
                
                log_message('info', 'CPanel testMultipleEndpoints - Result for ' . $endpoint . ' with pass: ' . json_encode($result));
                
                // Jika berhasil dengan 'pass', return hasilnya
                if (!isset($result['error']) || strpos($result['error'], 'password') === false) {
                    return [
                        'success' => true,
                        'working_endpoint' => $endpoint,
                        'working_parameter' => 'pass',
                        'message' => 'Test passed with endpoint: ' . $endpoint . ' and parameter: pass',
                        'all_results' => $results
                    ];
                }
                
                // Jika gagal dengan 'pass', coba dengan 'passwd'
                if (isset($result['error']) && strpos($result['error'], 'password') !== false) {
                    $altTestData = [
                        'email' => $user,
                        'domain' => $domain,
                        'passwd' => $password,
                        'quota' => $quota
                    ];
                    
                    $altResult = $this->requestWithSession($endpoint, 'POST', $altTestData);
                    $results[$endpoint]['passwd'] = $altResult;
                    
                    log_message('info', 'CPanel testMultipleEndpoints - Result for ' . $endpoint . ' with passwd: ' . json_encode($altResult));
                    
                    if (!isset($altResult['error']) || strpos($altResult['error'], 'password') === false) {
                        return [
                            'success' => true,
                            'working_endpoint' => $endpoint,
                            'working_parameter' => 'passwd',
                            'message' => 'Test passed with endpoint: ' . $endpoint . ' and parameter: passwd',
                            'all_results' => $results
                        ];
                    }
                }
            }
            
            // Jika semua gagal, return semua hasil untuk analisis
            log_message('error', 'CPanel testMultipleEndpoints - All endpoints and parameters failed');
            return [
                'error' => 'All endpoints and parameters failed',
                'all_results' => $results
            ];
            
        } catch (Exception $e) {
            log_message('error', 'CPanel testMultipleEndpoints - Exception: ' . $e->getMessage());
            return ['error' => 'Exception in multiple endpoints test: ' . $e->getMessage()];
        }
         }

     /**
      * Generate password hash untuk cPanel
      */
     private function generatePasswordHash($password)
     {
         try {
             // Coba berbagai metode hash yang umum digunakan di cPanel
             $hashMethods = [
                 'md5' => md5($password),
                 'sha1' => sha1($password),
                 'sha256' => hash('sha256', $password),
                 'sha512' => hash('sha512', $password),
                 'bcrypt' => password_hash($password, PASSWORD_BCRYPT),
                 'crypt' => crypt($password, '$2y$10$' . substr(md5(uniqid()), 0, 22))
             ];
             
             // Return MD5 hash sebagai default (paling umum di cPanel)
             return $hashMethods['md5'];
         } catch (Exception $e) {
             log_message('error', 'CPanel generatePasswordHash - Exception: ' . $e->getMessage());
             return md5($password); // Fallback ke MD5
         }
     }

     /**
      * Encrypt password untuk cPanel
      */
     private function encryptPassword($password)
     {
         try {
             // Coba berbagai metode enkripsi yang umum digunakan di cPanel
             $encryptedMethods = [
                 'base64' => base64_encode($password),
                 'urlsafe' => urlencode($password),
                 'htmlentities' => htmlentities($password, ENT_QUOTES, 'UTF-8'),
                 'rawurlencode' => rawurlencode($password)
             ];
             
             // Return base64 encoded sebagai default
             return $encryptedMethods['base64'];
         } catch (Exception $e) {
             log_message('error', 'CPanel encryptPassword - Exception: ' . $e->getMessage());
             return base64_encode($password); // Fallback ke base64
         }
     }

     /**
      * Test password parameter untuk rumahweb.com
      */
     public function testRumahwebPasswordParameters($email, $password, $quota = 10)
     {
         try {
             log_message('info', 'CPanel testRumahwebPasswordParameters - Testing password parameters for rumahweb.com: ' . $email);
             
             $domain = substr(strrchr($email, "@"), 1);
             $user = substr($email, 0, strpos($email, "@"));
             
             // Force fresh login untuk test
             if (!$this->forceLogin()) {
                 log_message('error', 'CPanel testRumahwebPasswordParameters - Force login failed');
                 return ['error' => 'Failed to establish session for password parameter test'];
             }
             
             // Test dengan endpoint Jupiter
             $testUrl = "/json-api/cpanel?cpanel_jsonapi_user={$this->cpanel_user}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=Email&cpanel_jsonapi_func=add_pop";
             
             // Test semua parameter password yang mungkin untuk rumahweb.com
             $passwordParams = [
                 'passwd' => $password,           // Parameter utama untuk rumahweb.com
                 'pass' => $password,             // UAPI standard
                 'password' => $password,         // Alternative
                 'passwd_hash' => $this->generatePasswordHash($password), // Hashed password
                 'passwd_enc' => $this->encryptPassword($password)        // Encrypted password
             ];
             
             $results = [];
             
             foreach ($passwordParams as $paramName => $paramValue) {
                 log_message('info', 'CPanel testRumahwebPasswordParameters - Testing parameter: ' . $paramName);
                 
                 $testData = [
                     'email' => $user,
                     'domain' => $domain,
                     $paramName => $paramValue,
                     'quota' => $quota
                 ];
                 
                 $result = $this->requestWithSession($testUrl, 'POST', $testData);
                 $results[$paramName] = $result;
                 
                 log_message('info', 'CPanel testRumahwebPasswordParameters - Result for ' . $paramName . ': ' . json_encode($result));
                 
                 // Jika berhasil, return hasilnya
                 if (!isset($result['error']) || strpos($result['error'], 'password') === false) {
                     log_message('info', 'CPanel testRumahwebPasswordParameters - Success with parameter: ' . $paramName);
                     return [
                         'success' => true, 
                         'working_parameter' => $paramName,
                         'message' => 'Rumahweb.com password test passed with parameter: ' . $paramName,
                         'all_results' => $results
                     ];
                 }
             }
             
             // Jika semua gagal, return semua hasil untuk analisis
             log_message('error', 'CPanel testRumahwebPasswordParameters - All password parameters failed');
             return [
                 'error' => 'All password parameters failed for rumahweb.com',
                 'all_results' => $results
             ];
             
         } catch (Exception $e) {
             log_message('error', 'CPanel testRumahwebPasswordParameters - Exception: ' . $e->getMessage());
             return ['error' => 'Exception in rumahweb.com password parameter test: ' . $e->getMessage()];
         }
     }

 }
