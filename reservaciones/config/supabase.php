<?php
// reservaciones/config/supabase.php
// Supabase REST API Client

class SupabaseClient {
    private $url;
    private $key;
    
    public function __construct() {
        $this->url = getenv('SUPABASE_URL') ?: 'https://baiwkomjrzmzovlseiff.supabase.co';
        $this->key = getenv('SUPABASE_KEY') ?: 'sb_publishable_pssohGJHQcEgsbSTgW-XIw_r16AkCrm';
    }
    
    /**
     * Make a GET request to Supabase REST API
     */
    public function get($table, $params = []) {
        $queryString = http_build_query($params);
        $url = "{$this->url}/rest/v1/{$table}";
        if ($queryString) {
            $url .= "?{$queryString}";
        }
        
        return $this->request('GET', $url);
    }
    
    /**
     * Make a POST request to Supabase REST API
     */
    public function post($table, $data) {
        $url = "{$this->url}/rest/v1/{$table}";
        return $this->request('POST', $url, $data);
    }
    
    /**
     * Make a PATCH request to Supabase REST API
     */
    public function patch($table, $data, $params = []) {
        $queryString = http_build_query($params);
        $url = "{$this->url}/rest/v1/{$table}";
        if ($queryString) {
            $url .= "?{$queryString}";
        }
        
        return $this->request('PATCH', $url, $data);
    }
    
    /**
     * Make a DELETE request to Supabase REST API
     */
    public function delete($table, $params = []) {
        $queryString = http_build_query($params);
        $url = "{$this->url}/rest/v1/{$table}?{$queryString}";
        
        return $this->request('DELETE', $url);
    }
    
    /**
     * Call a Supabase function (RPC)
     */
    public function rpc($functionName, $params = []) {
        $url = "{$this->url}/rest/v1/rpc/{$functionName}";
        return $this->request('POST', $url, $params);
    }
    
    /**
     * Execute HTTP request
     */
    private function request($method, $url, $data = null) {
        $ch = curl_init();
        
        $headers = [
            'apikey: ' . $this->key,
            'Authorization: Bearer ' . $this->key,
            'Content-Type: application/json',
            'Prefer: return=representation'
        ];
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        
        if ($data !== null && in_array($method, ['POST', 'PATCH', 'PUT'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception("cURL Error: {$error}");
        }
        
        curl_close($ch);
        
        if ($httpCode >= 400) {
            throw new Exception("HTTP Error {$httpCode}: {$response}");
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Count rows in a table with optional filters
     */
    public function count($table, $filters = []) {
        $params = array_merge($filters, ['select' => '*', 'count' => 'exact']);
        
        $url = "{$this->url}/rest/v1/{$table}?" . http_build_query($params);
        
        $ch = curl_init();
        $headers = [
            'apikey: ' . $this->key,
            'Authorization: Bearer ' . $this->key,
            'Prefer: count=exact'
        ];
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, true);
        
        $response = curl_exec($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $headerSize);
        
        curl_close($ch);
        
        // Extract Content-Range header
        if (preg_match('/Content-Range: \d+-\d+\/(\d+)/', $header, $matches)) {
            return (int)$matches[1];
        }
        
        return 0;
    }
}
?>
