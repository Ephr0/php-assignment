<?php
  require_once "functions.php";
  session_start();
  $users = get_file("users.json");
  if (!isset($_SESSION["user_id"]) || $users[$_SESSION["user_id"]]["is_admin"]){
    header("Location: index.php");
    exit;
  }
  $user = $users[$_SESSION["user_id"]];

  function validate($form){
    if (!$form || isset($form["rework"])){
      return "Empty";
    }
    $cats = ["Local large project", "Local small project", "Equal opportunity Budapest", "Green Budapest"];
    if (strlen($form["title"]) < 10){
      return "Title must be at least 10 characters!";
    }
    if (strlen($form["description"]) < 150){
      return "Description must be at least 150 characters!";
    }
    if (!filter_var($form["image"], FILTER_VALIDATE_URL)){
      return "Image isn't a valid format!";
    }
    if (!in_array($form["category"], $cats)){
      return "Choose category from dropdown menu!";
    }
    if (!preg_match('/^(1007|1(0[1-9]|1[0-9]|2[0-3])[1-9])$/', $form["postal_code"])){
      return "Postal code should be in the format 1,01-23,1-9 or 1007!";
    }
    return "Success!";
  }

  $login = false;
  $message = validate($_POST);
  $error = true;
  if ("Empty" === $message){
    $error = null;
  }
  else if ("Success!" === $message){
    $error = false;
  }
  if ($error === false && count($_POST) > 0){
    $projects = get_file("projects.json");
    $id = $_POST["id"] !== "" ? (int)$_POST["id"] : count($projects);
    $obj = [
      "id" => $id,
      "status" => "pending",
      "title" => $_POST["title"],
      "description" => $_POST["description"],
      "category" => $_POST["category"],
      "postal_code" => $_POST["postal_code"],
      "image" => $_POST["image"],
      "owner" => $_SESSION["user_id"],
      "submitted" => new DateTime("now", new DateTimeZone("UTC"))->format("Y-m-d\TH:i:s\Z"),
      "approved" => ""
    ];
    if ($id === count($projects)){
      array_push($projects, $obj);
    }
    else{
      $projects[$id] = $obj;
    }
    $new_json = json_encode($projects);
    file_put_contents("projects.json", $new_json);
    header("Location: myprojects.php");
    exit;
  }
  $_POST["title"] = !isset($_POST["title"]) ? "" : $_POST["title"];
  $_POST["description"] = !isset($_POST["description"]) ? "" : $_POST["description"];
  $_POST["category"] = !isset($_POST["category"]) ? "Local small project" : $_POST["category"];
  $_POST["postal_code"] =!isset($_POST["postal_code"]) ? "" : $_POST["postal_code"];
  $_POST["image"] = !isset($_POST["image"]) ? "" : $_POST["image"];
  if (!isset($_POST["id"]) || $_POST["id"] === ""){
    $_POST["id"] = "";
  }
  else{
    $_POST["id"] = (int)$_POST["id"];
  }
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
  </head>

  <body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
      <div class="container-fluid">
        <button
          class="navbar-toggler"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#navbarSupportedContent"
          aria-controls="navbarSupportedContent"
          aria-expanded="false"
          aria-label="Toggle navigation"
        >
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item">
              <a class="nav-link active" aria-current="page" href="/">Home</a>
            </li>
            <?php if (!$login && $user["is_admin"]): ?>
              <li class="nav-item">
                <a class="nav-link" href="projects-admin.php">Admin</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="statistics.php">Statistics</a>
              </li>
            <?php endif;?>
            <?php if (!$login && !$user["is_admin"]): ?>
              <li class="nav-item">
                <a class="nav-link" href="myprojects.php">My projects</a>
              </li>
            <?php endif;?>
          </ul>
          <div class="d-flex gap-2">
            <?php if ($login): ?>
              <a href="login.php" class="btn btn-primary">Login</a>
              <a href="register.php" class="btn btn-outline-primary">Register</a>
            <?php else: ?>
              <?php if (!$login && !$user["is_admin"]): ?>
                <a href="editproject.php" class="btn btn-primary">Add project</a>
              <?php endif;?>
              <a href="logout.php" class="btn btn-outline-primary">Log out</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </nav>
    <div class="container mt-3">
      <form action="editproject.php" method="post" novalidate>
        <div class="mb-3">
          <label for="title" class="form-label">Title</label>
          <input type="text" class="form-control" id="title" aria-describedby="emailHelp" name="title" value='<?=$_POST["title"]?>'>
        </div>
        <div class="mb-3">
          <label for="description" class="form-label">Description</label>
          <input type="text" class="form-control" id="description" aria-describedby="emailHelp" name="description" value='<?=$_POST["description"]?>'>
        </div>
        <div class="mb-3">
          <label for="category" class="form-label">Category</label>
          <select name="category" id="category" class="form-control">
            <option value="Local small project" selected>Local small project</option>
            <option value="Local big project">Local big project</option>
            <option value="Equal opportunity Budapest">Equal opportunity Budapest</option>
            <option value="Green Budapest">Green Budapest</option>
          </select>
        </div>
        <div class="mb-3">
          <label for="postal_code" class="form-label">Postal code</label>
          <input type="text" class="form-control" id="postal_code" aria-describedby="emailHelp" name="postal_code" value='<?=$_POST["postal_code"]?>'>
        </div>
        <div class="mb-3">
          <label for="image" class="form-label">Image url</label>
          <input type="text" class="form-control" id="image" aria-describedby="emailHelp" name="image" value='<?=$_POST["image"]?>'>
        </div>
        <input type="hidden" name="id" value="<?=$_POST["id"]?>"/>
        <button type="submit" class="btn btn-primary" value="Submit">Submit</button>
      </form>
      <div class="mb-3">
        <?php if ($error):?>
        <p class="text-danger"><?= $message?></p>
        <?php endif;?>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>