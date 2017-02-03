<?php
require_once( dirname(__FILE__) . '/HaPi-1.1.1/HarvestAPI.php' );

if (is_ajax()) {
  if (isset($_POST["action"]) && !empty($_POST["action"])) { //Checks if action value exists
    $action = $_POST["action"];
    switch($action) { //Switch case for value of action
      case "user": get_entries($_POST["username"], $_POST["password"]); break;
    }
  }
}

//Function to check if the request is an AJAX request
function is_ajax() {
  return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

function get_entries($username, $password) {
  $return = $_POST;

  spl_autoload_register(array('HarvestAPI', 'autoload') );

  $api = new HarvestAPI();
  $api->setUser( $username );
  $api->setPassword( $password );
  $api->setAccount( "freshconsulting" );

  $api->setRetryMode( HarvestAPI::RETRY );
  $api->setSSL(true);

  $result = $api->whoAmI();
  $me = '';
  if( $result->isSuccess() ) {
    $me = $result->data;

    $return['user'] = json_encode(array(
      'id' => $me->get('-user')->get('id'),
      'firstName' => $me->get('-user')->get('first-name'),
      'lastName' => $me->get('-user')->get('last-name'),
      'avatar' => $me->get('-user')->get('avatar-url')
    ));

    echo json_encode($return);
  } else {
    echo json_encode(array('user' => 'Error'));
  }
}
