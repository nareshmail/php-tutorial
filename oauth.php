<!-- Copyright (c) Microsoft. All rights reserved. Licensed under the MIT license. See full license at the bottom of this file. -->
<?php
  class oAuthService {
    private static $clientId = "21a66e5f-74c5-4acb-a0ee-02814e3fe217";
    private static $clientSecret = "tqlvN4Skz4Ah7BVcttEpJLxilJ4V0h+EnrSmLAaYfmQ=";
    private static $authority = "https://login.microsoftonline.com";
    private static $authorizeUrl = '/common/oauth2/authorize?client_id=%1$s&redirect_uri=%2$s&response_type=code';
    private static $tokenUrl = "/common/oauth2/token";
    
    public static function getLoginUrl($redirectUri) {
      $loginUrl = self::$authority.sprintf(self::$authorizeUrl, self::$clientId, urlencode($redirectUri));
      error_log("Generated login URL: ".$loginUrl);
      return $loginUrl;
    }
    
    public static function getTokenFromAuthCode($authCode, $redirectUri) {
      // Build the form data to post to the OAuth2 token endpoint
      $token_request_data = array(
        "grant_type" => "authorization_code",
        "code" => $authCode,
        "redirect_uri" => $redirectUri,
        "resource" => "https://outlook.office365.com/",
        "client_id" => self::$clientId,
        "client_secret" => self::$clientSecret
      );
      
      // Calling http_build_query is important to get the data
      // formatted as Azure expects.
      $token_request_body = http_build_query($token_request_data);
      error_log("Request body: ".$token_request_body);
      
      $curl = curl_init(self::$authority.self::$tokenUrl);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $token_request_body);
      
      $response = curl_exec($curl);
      error_log("curl_exec done.");
      
      $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      error_log("Request returned status ".$httpCode);
      if ($httpCode >= 400) {
        return array('errorNumber' => $httpCode,
                     'error' => 'Token request returned HTTP error '.$httpCode);
      }
      
      // Check error
      $curl_errno = curl_errno($curl);
      $curl_err = curl_error($curl);
      if ($curl_errno) {
        $msg = $curl_errno.": ".$curl_err;
        error_log("CURL returned an error: ".$msg);
        return array('errorNumber' => $curl_errno,
                     'error' => $msg);
      }
      
      curl_close($curl);
      
      // The response is a JSON payload, so decode it into
      // an array.
      $json_vals = json_decode($response, true);
      error_log("TOKEN RESPONSE:");
      foreach ($json_vals as $key=>$value) {
        error_log("  ".$key.": ".$value);
      }
      
      return $json_vals;
    }
  }
?>

<!--
 MIT License: 
 
 Permission is hereby granted, free of charge, to any person obtaining 
 a copy of this software and associated documentation files (the 
 ""Software""), to deal in the Software without restriction, including 
 without limitation the rights to use, copy, modify, merge, publish, 
 distribute, sublicense, and/or sell copies of the Software, and to 
 permit persons to whom the Software is furnished to do so, subject to 
 the following conditions: 
 
 The above copyright notice and this permission notice shall be 
 included in all copies or substantial portions of the Software. 
 
 THE SOFTWARE IS PROVIDED ""AS IS"", WITHOUT WARRANTY OF ANY KIND, 
 EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF 
 MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND 
 NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE 
 LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION 
 OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION 
 WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
-->