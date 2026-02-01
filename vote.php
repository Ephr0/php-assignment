<?php
require_once "functions.php";
session_start();

header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {
  echo json_encode(["error" => "not_logged_in"]);
  exit;
}

if (!isset($_POST["project_id"])) {
  echo json_encode(["error" => "missing_project"]);
  exit;
}

$votes = get_file("votes.json");
calc_votes($votes);
$votes = get_file("votes.json");

/* Return updated vote count */
$projects = get_file("projects.json");
$projectId = (int)$_POST["project_id"];
$proj_cat = $projects[$projectId]["category"];
$user_id = $_SESSION["user_id"];
$count = 0;
$cat_count = 0;

foreach ($votes as $v) {
  if ($v["project"] === $projectId) {
    $count++;
  }
  if ($projects[$v["project"]]["category"] === $proj_cat && $v["user"] === $user_id){
    $cat_count += 1;
  }
}

echo json_encode([
  "success" => true,
  "project_id" => $projectId,
  "votes" => $count,
  "disableToggle" => $cat_count >= 3,
]);
