<?php
  require '../dbconnect.php';
  require 'set_colours.php';
//---------------------------------------------------Query 1a ---------------------------------------------------
  $user_activity_table_columns_query = "SHOW COLUMNS FROM user_activity";

  $user_activity_table_columns_result = mysqli_query($conn, $user_activity_table_columns_query);

  if(mysqli_num_rows($user_activity_table_columns_result)==0) {
    exit();
  }
  else {
    while ($row = mysqli_fetch_assoc($user_activity_table_columns_result)) {
      if($row['Field']!= "userMapData_userId" && $row['Field']!= "userMapData_timestampMs" && $row['Field']!= "activity_timestamp" && $row['Field']!= "eco"){
        $activity_type[$row['Field']] = 0;
      }
    }
  $counter = 0;
  foreach ($activity_type as $key => $value) {
    $query = sprintf("SELECT COUNT(%s) from user_activity", mysqli_real_escape_string($conn, $key));
    $result = mysqli_query($conn, $query);
    if(mysqli_num_rows($result)==0){
      exit();
    }
    else {
      while ($row = mysqli_fetch_row($result)) {
        $activity_type[$key] = $row[0];
        $counter += $row[0];
      }
    }
  }
    if($counter != 0) {
      //table
      $activity_type_table = $activity_type;
      arsort($activity_type_table);
      //distr of all activity types
      foreach ($activity_type as $key_1 => $value_1) {
        $activity_type[$key_1] = round(($activity_type[$key_1]/$counter)*100,2);
      }
      arsort($activity_type);
      $colours_act = set_Chart_colours($activity_type);
    }
  if(isset($colours_act)) {
  echo json_encode(array($activity_type, $colours_act, $activity_type_table));
}
}
?>
