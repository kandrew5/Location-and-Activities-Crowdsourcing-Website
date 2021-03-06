<?php
  require '../dbconnect.php';

  session_start();
  $user_id_query = "SELECT userID FROM user WHERE username = '". $_SESSION['username']."'";
  $connected_user_id_result = mysqli_query($conn, $user_id_query);
  while ($row = mysqli_fetch_assoc($connected_user_id_result)) {
    $connected_user_id = sprintf($row['userID']);
  }

  $connected_user_score=0;

  $nowtime = intval(sprintf('%d000',time()));
  $this_months_sec = time() - strtotime("1-".(date("m-Y",time())));

  $all_user_activities_query = sprintf("SELECT count(activity_timestamp) FROM user_activity WHERE userMapData_userId = '%s'
  AND ($nowtime - activity_timestamp)/1000 < $this_months_sec", mysqli_real_escape_string($conn,$connected_user_id));
  $eco_user_activities_query = sprintf("SELECT count(activity_timestamp) FROM user_activity WHERE userMapData_userId = '%s'
  AND ($nowtime - activity_timestamp)/1000 < $this_months_sec AND eco = 1", mysqli_real_escape_string($conn,$connected_user_id));

  $all_user_activities_result = mysqli_query($conn, $all_user_activities_query);
  if(!$all_user_activities_result){
    exit();
  }
  $eco_user_activities_result = mysqli_query($conn, $eco_user_activities_query);
  if(!$eco_user_activities_result){
    exit();
  }

  while ($row = mysqli_fetch_assoc($all_user_activities_result)) {
    $all_user_activities = ($row['count(activity_timestamp)'])/1000;
  }
  while ($row = mysqli_fetch_assoc($eco_user_activities_result)) {
    $eco_user_activities = ($row['count(activity_timestamp)'])/1000;
  }

  if($all_user_activities != 0){
    $connected_user_score=$eco_user_activities/$all_user_activities;
  }
  else{
    $connected_user_score = 0;
  }

//------------------------------------------------ 4th query - top 3 for last month ------------------------------------------------
  $users_act_array=array();
  $temp_top_users=array();
  $top_users_table=array();
  $users_rank_table=array();
  $users_eco_act_array=array();
  $connected_user_rank=4;

  $number_of_users_act_query = "SELECT COUNT(eco), userMapData_userId FROM user_activity
  WHERE ($nowtime - activity_timestamp)/1000 < $this_months_sec GROUP BY userMapData_userId";
  $number_of_users_eco_act_query = "SELECT COUNT(eco), userMapData_userId FROM user_activity
  WHERE eco=1 AND ($nowtime - activity_timestamp)/1000 < $this_months_sec GROUP BY userMapData_userId";

  $number_of_users_act_results = mysqli_query($conn, $number_of_users_act_query);
  if(!$number_of_users_act_results){
    exit();
  }
  $number_of_users_eco_act_result = mysqli_query($conn, $number_of_users_eco_act_query);
  if(!$number_of_users_eco_act_result){
    exit();
  }

  while ($row = mysqli_fetch_assoc($number_of_users_act_results)) {
      $users_act_array[$row['userMapData_userId']]=$row['COUNT(eco)']; //$users_act_array[]: $key: userId, $value: number of user registrations
  }

  while ($row = mysqli_fetch_assoc($number_of_users_eco_act_result)) {
      $users_eco_act_array[$row['userMapData_userId']]=$row['COUNT(eco)'];  //$users_eco_act_array[]: $key: increasing numbering, $value: number of eco user registrations
  }
  $counter=0;
  foreach ($users_act_array as $key => $value) {
    if(isset($users_eco_act_array[$key])){
      $temp_top_users[$key] = $users_eco_act_array[$key]/$value; // $temp_top_users[]: $key: userId, $value: eco percent
    }
    else{
      $temp_top_users[$key] = 0;
    }
  }

  if($temp_top_users!=null){
    arsort($temp_top_users);

    $counter=1;
    foreach ($temp_top_users as $key => $value) { // check if user is in top 3
      if($key==$connected_user_id){
          $connected_user_rank = $counter;
      }
      $counter++;
    }

    if($connected_user_rank<=3){
      $top_rank_users = array_slice($temp_top_users,0,4);
    }
    else{
      $top_rank_users = array_slice($temp_top_users,0,3);
      $top_rank_users += [$connected_user_id=>$connected_user_score];
    }
    arsort($top_rank_users);

    foreach ($top_rank_users as $key => $value) { //get name and surname
      $user_names_query = sprintf("SELECT name, surname FROM user WHERE userId = '$key'");
      $user_names_result = mysqli_query($conn, $user_names_query);
      if(!$user_names_result){
        exit();
      }
      while ($row = mysqli_fetch_assoc($user_names_result)) {
        $user_names[] = sprintf($row['name']." ".substr($row['surname'],0,1));
      }
    }

    $connected_user_name_query = sprintf("SELECT name, surname FROM user WHERE userId = '%s'", mysqli_real_escape_string($conn,$connected_user_id));
    $connected_user_name_result = mysqli_query($conn, $connected_user_name_query);
    if(!$connected_user_name_result){
      exit();
    }

    while ($row = mysqli_fetch_assoc($connected_user_name_result)) {
      $connected_user_name = sprintf($row['name']." ".substr($row['surname'],0,1));
    }

    $counter=0;
    foreach ($top_rank_users as $key => $value) {
      if($user_names[$counter] != $connected_user_name){
        $top_users_table[$user_names[$counter]]=round($value*100,2);
        $users_rank_table[$counter] = $counter+1;
        $counter++;
      }
      else{
        $top_users_table["You"]=round($value*100,2);
        $users_rank_table[$counter] = $connected_user_rank;
        $counter++;
      }
    }
    echo json_encode(array($top_users_table,$users_rank_table));
  }else{
    $connected_user_name_query = sprintf("SELECT name, surname FROM user WHERE userId = '%s'", mysqli_real_escape_string($conn,$connected_user_id));
    $connected_user_name_result = mysqli_query($conn, $connected_user_name_query);
    if(!$connected_user_name_result){
      exit();
    }

    while ($row = mysqli_fetch_assoc($connected_user_name_result)) {
      $connected_user_name = sprintf($row['name']." ".substr($row['surname'],0,1));
      $top_users_table["You"]=0;
      $users_rank_table[0] = 1;
    }
    echo json_encode(array($top_users_table,$users_rank_table));
  }
?>
