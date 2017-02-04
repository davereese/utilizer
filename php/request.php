<?php
require_once( dirname(__FILE__) . '/HaPi-1.1.1/HarvestAPI.php' );

if (is_ajax()) {
  if (isset($_POST["action"]) && !empty($_POST["action"])) { //Checks if action value exists
    $action = $_POST["action"];
    switch($action) { //Switch case for value of action
      case "userEntries": get_entries($_POST["username"], $_POST["password"], $_POST["id"], $_POST["last"]); break;
    }
  }
}

// get_entries('dave@freshconsulting.com', 'coquina05', '631693');

//Function to check if the request is an AJAX request
function is_ajax() {
  return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

function get_entries($username, $password, $user_id, $last_week = null) {
  $return = $_POST;

  spl_autoload_register(array('HarvestAPI', 'autoload') );

  $api = new HarvestAPI();
  $api->setUser( $username );
  $api->setPassword( $password );
  $api->setAccount( "freshconsulting" );

  $api->setRetryMode( HarvestAPI::RETRY );
  $api->setSSL(true);

  if ( 'true' === $last_week ) {
    $range = Harvest_Range::lastWeek( "PST", Harvest_Range::MONDAY );
  } else {
    $range = Harvest_Range::thisWeek( "PST", Harvest_Range::MONDAY );
  }

  $result = $api->getUserEntries( $user_id, $range );
  if( $result->isSuccess() ) {
    $entries = array();
    $dayEntries = $result->data;
    $projects = array();
    foreach ($dayEntries as $key => $value) {
      $temp_array = array();
      // Project
      // check returned projects so we don't have to fetch data for duplicates
      if ( false === array_key_exists($value->get('project_id'), $projects) ) {
        $result2 = $api->getProject( $value->get('project_id') );
        if( $result2->isSuccess() ) {
          // add results to projects array
          $projects[$value->get('project_id')] = $result2->data;
          // assign results to variable
          $project = $result2->data;
          // Add to temp array
          $temp_array['name'] = $project->get('name');
          $temp_array['task'] = $value->get('notes');
          $temp_array['hours'] = $value->get('hours');
          $temp_array['date'] = $value->get('spent-at');
          $temp_array['billable'] = $project->get('billable');
        }
      } else {
        // assign previously returned project to variable
        $project = $projects[$value->get('project_id')];
        // Add to temp array
        $temp_array['name'] = $project->get('name');
        $temp_array['task'] = $value->get('notes');
        $temp_array['hours'] = $value->get('hours');
        $temp_array['date'] = $value->get('spent-at');
        $temp_array['billable'] = $project->get('billable');
      }
      // Billable vs. Non-Billable Checks
      if ( 'TIME OFF' === $temp_array['name'] ) {
        $temp_array['billable'] = 'false';
      }
      // Add temp array to entries array
      array_push($entries, $temp_array);
    }

    $return['json'] = json_encode($entries);
    echo json_encode($return);
  } else {
    echo json_encode(array('json' => 'Error'));
  }
}
