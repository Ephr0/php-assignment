<?php
  require_once "functions.php";
  session_start();
  $users = get_file("users.json");
  if (!isset($_SESSION["user_id"]) || !$users[$_SESSION["user_id"]]["is_admin"] || !isset($_POST["project_id"])){
    header("Location: index.php");
    exit;
  }
  $projects = get_file("projects.json");
  $proj = $projects[$_POST["project_id"]];
  $proj["status"] = isset($_POST["approved"]) ? "approved" : (isset($_POST["rejected"]) ? "rejected" : "rework");
  if ($proj["status"] === "approved" || $proj["status"] === "rejected"){
    $proj["approved"] = new DateTime("now", new DateTimeZone("UTC"))->format("Y-m-d\TH:i:s\Z");
    $projects[$proj["id"]] = $proj;
    $json = json_encode($projects);
    file_put_contents("projects.json", $json);
    header("Location: projects-admin.php");
    exit;
  }

  if (!isset($_POST["comments"]) || trim($_POST["comments"]) === ""){
    header(
        "Location: project.php?id=" .
        urlencode($_POST["project_id"]) .
        "&error=true&message=" .
        urlencode("Fill out comments for rework!")
    );
    exit;
  }

  $rework = get_file("rework.json");
  $rew = [
    "id" => count($rework),
    "project_id" => (int)$_POST["project_id"],
    "title" => $proj["title"],
    "description" => $proj["description"],
    "category" => $proj["category"],
    "postal_code" => $proj["postal_code"],
    "image" => $proj["image"],
    "owner" => $proj["owner"],
    "submitted" => $proj["submitted"],
    "comments" => $_POST["comments"]
  ];
  array_push($rework, $rew);
  $json = json_encode($rework);
  file_put_contents("rework.json", $json);
  $projects[$proj["id"]] = $proj;
  $json = json_encode($projects);
  file_put_contents("projects.json", $json);
  header("Location: projects-admin.php");
  exit;
?>