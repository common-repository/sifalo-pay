<?php
class sifaloPay {

  protected $sessionID;
  protected $requestID;
  protected $api_user;
  protected $api_pass;
  protected $apiURL;

  public function __construct($api_user,$api_pass) {

    $this->api_user = $api_user;
    $this->api_pass = $api_pass;
    $this->apiURL = "https://api.sifalopay.com/gateway/";
  }

    function sifaloPay_purchase($amount,$order_ref, $billing_address, $currency, $account, $gateway) {
      // get ip
      if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
        //check ip from share internet
        $ip = sanitize_text_field($_SERVER['HTTP_CLIENT_IP']);
        } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
        //to check ip is pass from proxy
        $ip = sanitize_text_field($_SERVER['HTTP_X_FORWARDED_FOR']);
        } else {
        $ip = sanitize_text_field($_SERVER['REMOTE_ADDR']);
        }


        $authorization = base64_encode($this->api_user.':'.$this->api_pass);
        // perform txn
        $values = array(
         "amount"=>$amount,
         "account"=>$account,
         "gateway"=>$gateway,
         "currency"=>$currency,
         "channel"=>"wordpress",
         "txn_order_id"=>$order_ref,
         "url"=> esc_url_raw(get_site_url()),
         "ip" => $ip,
         "billing"=> $billing_address

         );

      $args = array(
        'body'        => json_encode($values),
        'data_format' => 'body',
        'method'      => 'POST',
        'timeout'     => '45',
        'redirection' => '10',
        'httpversion' => '1.0',
        'blocking'    => true,
        'headers'     => array('Content-Type: application/json','Authorization' => 'Basic ' . $authorization),
        'cookies'     => array(),
      );

    	
      $response = wp_remote_post( $this->apiURL, $args );

      return json_decode($response['body'], true);
    }

    function sifaloPay_purchase2($amount, $order_ref, $thanku_url, $api_environment, $billing_address, $currency) {

        $authorization = base64_encode($this->api_user.':'.$this->api_pass);
    
        // perform txn
        $values = array(
         "amount"             => $amount,
         "gateway"            => 'checkout',
         "channel"            => 'wordpress',
         "currency"           => $currency,
         "return_url"         => $thanku_url,
         "order_id"           => $order_ref,
         "billing"            => $billing_address,
         "url"                => esc_url_raw(get_site_url()),
         "ip" => $_SERVER['REMOTE_ADDR']
         );

      $args = array(
        'body'        => json_encode($values),
        'data_format' => 'body',
        'method'      => 'POST',
        'timeout'     => '45',
        'redirection' => '10',
        'httpversion' => '1.0',
        'blocking'    => true,
        'headers'     => array('Content-Type: application/json','Authorization' => 'Basic ' . $authorization),
        'cookies'     => array(),
      );

      $response = wp_remote_post( $api_environment, $args );

      return json_decode($response['body'], true);
    }

}
?>
