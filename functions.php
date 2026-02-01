<?php

function get_file($file_name) {
    $file = file_get_contents($file_name);
    return json_decode($file, true);
}

function find_vote($project_id, $user_id, $votes) {
    foreach ($votes as $key => $v) {
        if ($project_id === $v["project"] && $user_id === $v["user"]) {
            return $key;
        }
    }
    return -1;
}


  function calc_votes($votes){
    $ind = find_vote((int)$_POST["project_id"], $_SESSION["user_id"], $votes);
    if ($ind !== -1) unset($votes[$ind]);
    else{
      $obj = [
        "user" => $_SESSION["user_id"],
        "project" => (int)$_POST["project_id"]
      ];
      array_push($votes, $obj);
    }
    $json = json_encode((array)$votes);
    file_put_contents("votes.json", $json);
  }
?>