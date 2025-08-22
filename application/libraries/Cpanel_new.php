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
     * Login menggunakan session token
     */
    private function loginWithSession()
    {
        try {
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
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
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
            log_message('info', 'CPanel Session Login - Response Body: ' . $body);

            if ($error) {
                log_message('error', 'CPanel Session Login - cURL Error: ' . $error);
                return false;
            }

            if ($http_code !== 200) {
                log_message('error', 'CPanel Session Login - HTTP Error: ' . $http_code);
                return false;
            }

            $json = json_decode($body, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', 'CPanel Session Login - JSON decode error: ' . json_last_error_msg());
                return false;
            }

            if (isset($json['status']) && $json['status'] == 1) {
                $this->session_token = $json['security_token'];
                log_message('info', 'CPanel Session Login - Session Token: ' . $this->session_token);

                if (preg_match_all('/Set-Cookie:\s*([^;]*)/mi', $header, $matches)) {
                    $this->cookies = implode("; ", $matches[1]);
                    log_message('info', 'CPanel Session Login - Cookies: ' . $this->cookies);
                }

                return true;
            }

            log_message('error', 'CPanel Session Login - Failed: ' . $body);
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
            return $this->requestWithSession($url, $method, $data);
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
     * Request menggunakan session
     */
    private function requestWithSession($url, $method = 'GET', $data = null)
    {
        try {
            if (!$this->session_token) {
                log_message('error', 'CPanel Session Request - No session token available');
                return ["error" => "No session token available"];
            }
            
            $endpoint = "https://{$this->cpanel_host}:{$this->cpanel_port}{$this->session_token}{$url}";
            
            log_message('info', 'CPanel Session Request - Making request to: ' . $endpoint);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_USERAGENT, 'CodeIgniter-CPanel/1.0');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            
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
            log_message('info', 'CPanel Session Request - Response: ' . $result);

            if ($error) {
                log_message('error', 'CPanel Session Request - cURL Error: ' . $error);
                return ["error" => "Request error: " . $error];
            }

            if ($http_code !== 200) {
                log_message('error', 'CPanel Session Request - HTTP Error: ' . $http_code);
                return ["error" => "HTTP error: " . $http_code];
            }

            $decoded = json_decode($result, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', 'CPanel Session Request - JSON decode error: ' . json_last_error_msg());
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
     * Ambil daftar email
     */
    public function listEmailAccounts($domain = null)
    {
        try {
            log_message('info', 'CPanel listEmailAccounts - Starting request');
            
            if ($this->auth_token && !empty($this->auth_token)) {
                log_message('info', 'CPanel listEmailAccounts - Using token authentication');
                $result = $this->requestWithToken('/Email/list_pops');
            } else {
                log_message('info', 'CPanel listEmailAccounts - Using session authentication');
                $domain = $domain ?: $this->cpanel_host;
                $url = "/json-api/cpanel?cpanel_jsonapi_user={$this->cpanel_user}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=Email&cpanel_jsonapi_func=listpopswithdisk&domain={$domain}";
                $result = $this->request($url);
            }
            
            log_message('info', 'CPanel listEmailAccounts - Result: ' . json_encode($result));
            return $result;
        } catch (Exception $e) {
            log_message('error', 'CPanel listEmailAccounts - Exception: ' . $e->getMessage());
            return ['error' => 'Exception in listEmailAccounts: ' . $e->getMessage()];
        }
    }

    /**
     * Buat akun email baru
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
                    'passwd' => $password,
                    'quota' => $quota
                ];
                $result = $this->requestWithToken('/Email/add_pop', 'POST', $data);
            } else {
                log_message('info', 'CPanel createEmailAccount - Using session authentication');
                $url = "/json-api/cpanel?cpanel_jsonapi_user={$this->cpanel_user}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=Email&cpanel_jsonapi_func=add_pop&email={$user}&domain={$domain}&passwd={$password}&quota={$quota}";
                $result = $this->request($url);
            }
            
            log_message('info', 'CPanel createEmailAccount - Result: ' . json_encode($result));
            return $result;
        } catch (Exception $e) {
            log_message('error', 'CPanel createEmailAccount - Exception: ' . $e->getMessage());
            return ['error' => 'Exception in createEmailAccount: ' . $e->getMessage()];
        }
    }

    /**
     * Update akun email
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
                if ($password) $data['passwd'] = $password;
                if ($quota) $data['quota'] = $quota;
                
                $result = $this->requestWithToken('/Email/passwd_pop', 'POST', $data);
            } else {
                log_message('info', 'CPanel updateEmailAccount - Using session authentication');
                $params = [];
                if ($password) {
                    $params[] = "passwd={$password}";
                }
                if ($quota) {
                    $params[] = "quota={$quota}";
                }
                
                $paramString = !empty($params) ? "&" . implode("&", $params) : "";
                
                $url = "/json-api/cpanel?cpanel_jsonapi_user={$this->cpanel_user}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=Email&cpanel_jsonapi_func=passwd_pop&email={$user}&domain={$domain}{$paramString}";
                $result = $this->request($url);
            }
            
            log_message('info', 'CPanel updateEmailAccount - Result: ' . json_encode($result));
            return $result;
        } catch (Exception $e) {
            log_message('error', 'CPanel updateEmailAccount - Exception: ' . $e->getMessage());
            return ['error' => 'Exception in updateEmailAccount: ' . $e->getMessage()];
        }
    }

    /**
     * Hapus akun email
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
                $result = $this->requestWithToken('/Email/del_pop', 'POST', $data);
            } else {
                log_message('info', 'CPanel deleteEmailAccount - Using session authentication');
                $url = "/json-api/cpanel?cpanel_jsonapi_user={$this->cpanel_user}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=Email&cpanel_jsonapi_func=del_pop&email={$user}&domain={$domain}";
                $result = $this->request($url);
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
     * Force login ulang
     */
    public function forceLogin()
    {
        try {
            log_message('info', 'CPanel forceLogin - Forcing new login');
            $this->session_token = null;
            $this->cookies = "";
            $result = $this->login();
            log_message('info', 'CPanel forceLogin - Result: ' . ($result ? 'SUCCESS' : 'FAILED'));
            return $result;
        } catch (Exception $e) {
            log_message('error', 'CPanel forceLogin - Exception: ' . $e->getMessage());
            return false;
        }
    }
}
