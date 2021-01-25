<?php
namespace Codemanas\Zoom\Core;
use \Zoom_Video_Conferencing_Api;
require_once(ZVC_PLUGIN_INCLUDES_PATH.'\helpers.php');
class Oauth extends Zoom_Video_Conferencing_Api{
    public static $_instance = null;
    private $authorization_header = '';
    private $client_id = 'LhF1UQO0SKuuBFUH69iyrw';
    private $secret_id = 'fwlJ987YTaY5MICPNIXSCaAexPGrq1DW';
    //redirect uri required to generate request access token url
    private $redirect_uri = 'https://oauth.codemanas.com/zprocess/';
    
    // authorization is 2nd part of Oauth process
    private $authorize_uri = 'https://zoom.us/oauth/authorize';
    private $zoom_verify_listener = '';
    private $access_token_url = 'https://zoom.us/oauth/token';

    // revoke details
    private $revoke_uri = 'https://zoom.us/oauth/revoke';
    
    //user details
    private $current_user_id = NULL;
    private $user_oauth_data = NULL;
    private $connected_user_info =  NULL;

    public static function get_instance(){
        if( is_null(self::$_instance)){
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct(){
        $this->set_required_fields();
        parent::__construct();
        add_action('admin_init',[$this,'request_or_remove_access_token']);
        add_filter('vczapi_core_api_request_headers',[$this,'change_request_headers']);
        //currently workig under the assumption that once connected only connected user is used - and not any accounts they may have under those user
        add_filter('vczapi_users_list',[$this,'set_zoom_user']);
        add_action('vczapi_check_oauth_response',array($this,'check_refresh_token_and_resend_request'),10,4);
    }

    public function OauthConnected(){
        if( !empty($this->user_oauth_data) && !empty($this->connected_user_info)){
            return true;
        }else{
            return false;
        }
    }

    public function getMyInfo(){
        return $this->sendRequest( 'users/me', [] );
    }

    public function connect_zoom_page_for_authors(){
        ?>
            <div class="wrap">
            <?php $this->maybe_connect_to_zoom_html(); ?>
            </div>
            <?php
    }

    public function set_required_fields(){
        $this->zoom_verify_listener = admin_url( 'edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-settings' );
        $this->authorization_header = 'Basic ' . base64_encode( $this->client_id . ':' . $this->secret_id );
        $this->current_user_id = get_current_user_id();
        
        
        $this->user_oauth_data = (!vczapi_is_oauth_used_globally()) ? get_user_meta($this->current_user_id,'vczapi_zoom_oauth',true) : get_option('vczapi_global_zoom_oauth');
        $this->connected_user_info = (!vczapi_is_oauth_used_globally()) ? get_user_meta($this->current_user_id,'vczapi_connected_user_info',true):get_option('vczapi_global_connected_user_info');
    }

    public function maybe_connected_to_zoom_html(){
        if(empty($this->user_oauth_data)){
            ?>
              <a href="<?php echo $this->get_request_user_authentication_url(); ?>" class="button button-hero button-primary">Connect Your Account via Oauth</a>
            <?php
        } 
        else{
            $connected_user_info = json_decode( $this->getMyInfo() );
            ?>
            <h4>
            <?php 
             if( \vczapi_is_oauth_used_globally() ){
                _e('This is the site wide Zoom Account','video-conferencing-with-zoom-api');
             }else{
                _e('You are Connected to Zoom','video-conferencing-with-zoom-api');     
             }
             ?></h4>
            <div class="" style="display:flex;flex-wrap:wrap;">
                <div class="" style="">
                    <img src="<?php echo $connected_user_info->pic_url; ?>" style="border-radius:50%;">
                </div>
                <div class="" style="padding-left:20px">
                <ul>
                    <li>Host Id: <?php echo $connected_user_info->id; ?></li>
                    <li>Name: <?php echo $connected_user_info->first_name.' '.$connected_user_info->last_name; ?></li>
                    <li>Email: <?php echo $connected_user_info->email; ?></li>
                </ul>
                </div>
                <div style="padding-left:20px">
                <?php
                    if( \vczapi_is_oauth_used_globally() && !\current_user_can('manage_options') ){
                        ?>
                        <style>
                        #vczapi-remove-oauth-access{
                            display:none;
                        }
                        </style>
                        <?php
                    }
                ?>
                <a id="vczapi-remove-oauth-access" href="<?php echo $this->zoom_verify_listener.'&revoke_access_token=true'; ?>" class="button button-hero button-primary" style="margin-top:10px;">Disconnect your account</a>
                </div>
                
            </div>
            
           
            <?php
        }
        
    }

    public function get_request_user_authentication_url(){
        return add_query_arg([
            'response_type' => 'code',
            'client_id' => $this->client_id,
            'redirect_uri' => $this->redirect_uri.'?state='.$this->zoom_verify_listener
        ],$this->authorize_uri);
    }

    public function request_or_remove_access_token(){
        global $pagenow;
        
        if( !current_user_can('edit_posts')){
            return;
        }
        
        if(  $pagenow == 'edit.php' && filter_input(INPUT_GET,'post_type') == 'zoom-meetings' && filter_input(INPUT_GET,'page') == 'zoom-video-conferencing-settings' ){
            $this->requestAccessToken();
            $this->revokeAccessToken();
        }
        
    }

    public function requestAccessToken(){
        $code = filter_input(INPUT_GET,'code');             
        if( empty($code) ){
            return;
        }
        
        $request_access_token_url = add_query_arg(
            [
                'grant_type'   => 'authorization_code',
                'code'         => $code,
                'redirect_uri' => $this->redirect_uri.'?state='.esc_url($this->zoom_verify_listener),
            ],
            $this->access_token_url
        );
        
        $header_args = [
            'headers' => [
                'Authorization' => $this->authorization_header,
            ],
        ];

       $post_response = wp_remote_post( $request_access_token_url,$header_args );
       $response_body = json_decode( wp_remote_retrieve_body($post_response) );
        
       if( !isset($response_body->error)){
         //update user oauth data first or else getting connected user info will be wrong
         update_user_meta($this->current_user_id,'vczapi_zoom_oauth',$response_body);
         $this->user_oauth_data = $response_body;
         
         $vczpai_connected_user_info = json_decode( $this->getMyInfo() );
         update_user_meta($this->current_user_id,'vczapi_connected_user_info',$vczpai_connected_user_info);
         $this->connected_user_info = $vczpai_connected_user_info;

         if( \vczapi_is_oauth_used_globally() ){
            update_option('vczapi_global_zoom_oauth',$response_body);
            update_option('vczapi_global_connected_user_info',$vczapi_connected_user_info);
        }
         

         wp_redirect($this->zoom_verify_listener);
         exit;
       }
    }

    public function revokeAccessToken(){
        
        $revoke = filter_input(INPUT_GET,'revoke_access_token'); 
        
        if( empty($revoke)){
            return;
        }
        
        $revoke_query = add_query_arg(
            [
                'token' => $this->user_oauth_data->access_token
            ],
            $this->revoke_uri
        );

        $header_args = [
            'headers' => [
                'Authorization' => $this->authorization_header,
            ],
        ];

        $response = wp_remote_post($revoke_query,$header_args);
        $response_body = wp_remote_retrieve_body($response);

        delete_user_meta($this->current_user_id,'vczapi_zoom_oauth');
        delete_user_meta($this->current_user_id,'vczapi_connected_user_info');

        if( \vczapi_is_oauth_used_globally() ){
            delete_option('vczapi_global_zoom_oauth');
			delete_option('vczapi_global_connected_user_info');
        }
        
        wp_redirect($this->zoom_verify_listener);
        exit;

    }

    public function change_request_headers($headers){
        if( !empty($this->user_oauth_data) ){
            $headers['Authorization'] = 'Bearer '.$this->user_oauth_data->access_token; 
        }
        return $headers;
    }   

    public function set_zoom_user($users){
        
        if(!vczapi_is_oauth_used_globally() &&  !empty($this->connected_user_info)){
            $users = [$this->connected_user_info];
        }
            
        return $users;
    }

    public function check_refresh_token_and_resend_request($response,$calledFunction, $data, $request){
        //** remove filter so we don't end up in a loop */
        remove_filter('vczapi_check_oauth_response',[$this,'refresh_token_and_resend_request'],10);
        $response_check = json_decode($response);
        if(isset($response_check->code) && $response_check->code == '124'){
            $response_body = $this->regenerate_access_token();
            if(isset($response_body->access_token)){
               $response = zoom_conference()->sendRequest($calledFunction, $data, $request);
            }
        }
        return $response;
    }

    public function regenerate_access_token(){
        $this->access_token_url;
        if(isset($this->user_oauth_data->refresh_token) && !empty($this->user_oauth_data->refresh_token)){
            $regen_access_token_url = add_query_arg(
                [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $this->user_oauth_data->refresh_token
                ],
                $this->access_token_url
             );
             
            $header_args = [
                'headers' => [
                    'Authorization' => $this->authorization_header,
                ],
            ];

            $response = wp_remote_post($regen_access_token_url,$header_args);
            
            $response_body = json_decode ( wp_remote_retrieve_body($response) );
            if( !isset($response_body->error)){
                //update user oauth data first or else getting connected user info will be wrong
                update_user_meta($this->current_user_id ,'vczapi_zoom_oauth',$response_body);
                $this->user_oauth_data = $response_body;
                $vczpai_connected_user_info = json_decode( $this->getMyInfo() );
                update_user_meta($this->current_user_id ,'vczapi_connected_user_info',$vczpai_connected_user_info);
                $this->connected_user_info = $vczpai_connected_user_info;
                \file_put_contents(\get_stylesheet_directory().'/access_token_regenerated.txt',var_export($response_body,true));
            }

            return $response_body;
        }    
    }   
}
Oauth::get_instance();