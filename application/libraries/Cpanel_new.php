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
        // Coba metode token terlebih dahulu
        if ($this->auth_token) {
            if ($this->loginWithToken()) {
                return true;
            }
        }

        // Fallback ke metode session login
        return $this->loginWithSession();
    }

    /**
     * Login menggunakan auth token
     */
    private function loginWithToken()
    {
        $loginUrl = "https://{$this->cpanel_host}:{$this->cpanel_port}/execute/Email/list_pops";
        
        $headers = [
            'Authorization: cpanel ' . $this->cpanel_user . ':' . $this->auth_token,
            'Content-Type: application/json'
        ];

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
            return true;
        }

        return false;
    }

    /**
     * Login menggunakan session token
     */
    private function loginWithSession()
    {
        $loginUrl = "https://{$this->cpanel_host}:{$this->cpanel_port}/login/?login_only=1";
        $postFields = [
            "user" => $this->cpanel_user,
            "pass" => $this->cpanel_pass
        ];

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
        log_message('info', 'CPanel Session Login - Headers: ' . $header);

        if ($error) {
            log_message('error', 'CPanel Session Login - cURL Error: ' . $error);
            return false;
        }

        $json = json_decode($body, true);

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
    }

    /**
     * Request API dengan multiple metode
     */
    private function request($url, $method = 'GET', $data = null)
    {
        if (!$this->session_token) {
            if (!$this->login()) {
                return ["error" => "Login gagal"];
            }
        }

        // Jika menggunakan token auth
        if ($this->session_token === 'TOKEN_AUTH') {
            return $this->requestWithToken($url, $method, $data);
        }

        // Jika menggunakan session auth
        return $this->requestWithSession($url, $method, $data);
    }

    /**
     * Request menggunakan token
     */
    private function requestWithToken($url, $method = 'GET', $data = null)
    {
        $endpoint = "https://{$this->cpanel_host}:{$this->cpanel_port}/execute" . $url;
        
        $headers = [
            'Authorization: cpanel ' . $this->cpanel_user . ':' . $this->auth_token,
            'Content-Type: application/json'
        ];

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

        log_message('info', 'CPanel Token Request - Endpoint: ' . $endpoint);
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

        return json_decode($result, true);
    }

    /**
     * Request menggunakan session
     */
    private function requestWithSession($url, $method = 'GET', $data = null)
    {
        $endpoint = "https://{$this->cpanel_host}:{$this->cpanel_port}{$this->session_token}{$url}";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'CodeIgniter-CPanel/1.0');
        
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

        log_message('info', 'CPanel Session Request - Endpoint: ' . $endpoint);
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

        return json_decode($result, true);
    }

    /**
     * Test koneksi ke cPanel
     */
    public function testConnection()
    {
        if ($this->auth_token) {
            return $this->requestWithToken('/UAPI/get_user_information');
        } else {
            $url = "/json-api/cpanel?cpanel_jsonapi_user={$this->cpanel_user}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=UAPI&cpanel_jsonapi_func=get_user_information";
            return $this->request($url);
        }
    }

    /**
     * Ambil daftar email
     */
    public function listEmailAccounts($domain = null)
    {
        if ($this->auth_token) {
            return $this->requestWithToken('/Email/list_pops');
        } else {
            $domain = $domain ?: $this->cpanel_host;
            $url = "/json-api/cpanel?cpanel_jsonapi_user={$this->cpanel_user}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=Email&cpanel_jsonapi_func=listpopswithdisk&domain={$domain}";
            return $this->request($url);
        }
    }

    /**
     * Buat akun email baru
     */
    public function createEmailAccount($email, $password, $quota = 250)
    {
        $domain = substr(strrchr($email, "@"), 1);
        $user = substr($email, 0, strpos($email, "@"));
        
        if ($this->auth_token) {
            $data = [
                'email' => $user,
                'domain' => $domain,
                'passwd' => $password,
                'quota' => $quota
            ];
            return $this->requestWithToken('/Email/add_pop', 'POST', $data);
        } else {
            $url = "/json-api/cpanel?cpanel_jsonapi_user={$this->cpanel_user}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=Email&cpanel_jsonapi_func=add_pop&email={$user}&domain={$domain}&passwd={$password}&quota={$quota}";
            return $this->request($url);
        }
    }

    /**
     * Update akun email
     */
    public function updateEmailAccount($email, $password = null, $quota = null)
    {
        $domain = substr(strrchr($email, "@"), 1);
        $user = substr($email, 0, strpos($email, "@"));
        
        if ($this->auth_token) {
            $data = [
                'email' => $user,
                'domain' => $domain
            ];
            if ($password) $data['passwd'] = $password;
            if ($quota) $data['quota'] = $quota;
            
            return $this->requestWithToken('/Email/passwd_pop', 'POST', $data);
        } else {
            $params = [];
            if ($password) {
                $params[] = "passwd={$password}";
            }
            if ($quota) {
                $params[] = "quota={$quota}";
            }
            
            $paramString = !empty($params) ? "&" . implode("&", $params) : "";
            
            $url = "/json-api/cpanel?cpanel_jsonapi_user={$this->cpanel_user}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=Email&cpanel_jsonapi_func=passwd_pop&email={$user}&domain={$domain}{$paramString}";
            return $this->request($url);
        }
    }

    /**
     * Hapus akun email
     */
    public function deleteEmailAccount($email)
    {
        $domain = substr(strrchr($email, "@"), 1);
        $user = substr($email, 0, strpos($email, "@"));
        
        if ($this->auth_token) {
            $data = [
                'email' => $user,
                'domain' => $domain
            ];
            return $this->requestWithToken('/Email/del_pop', 'POST', $data);
        } else {
            $url = "/json-api/cpanel?cpanel_jsonapi_user={$this->cpanel_user}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=Email&cpanel_jsonapi_func=del_pop&email={$user}&domain={$domain}";
            return $this->request($url);
        }
    }

    /**
     * Get session token (untuk debugging)
     */
    public function getSessionToken()
    {
        return $this->session_token;
    }

    /**
     * Get auth method
     */
    public function getAuthMethod()
    {
        return $this->session_token === 'TOKEN_AUTH' ? 'TOKEN' : 'SESSION';
    }

    /**
     * Force login ulang
     */
    public function forceLogin()
    {
        $this->session_token = null;
        $this->cookies = "";
        return $this->login();
    }
}
