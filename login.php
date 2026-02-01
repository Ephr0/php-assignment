<?php
  session_start();
  function get_user_data($form){
    if (!$form) return null;
    $file = file_get_contents("users.json");
    $json = json_decode($file, true);
    foreach ($json as $obj){
      if ($obj["username"] == $form["user"]){
        return $obj;
      }
    }
    return null;
  }
  $user = get_user_data($_GET);
  $message = "";
  $error = false;
  if (!$user && count($_GET)> 0) {
    $error = true;
    $message = "This user doesn't exist!";
  }
  else if ($user && password_verify($_GET["password"], $user["password"])){
    $message = "Login successful!";
    $_SESSION["user_id"] = $user["id"];
    $error = false;
  }
  else if ($user && !password_verify($_GET["password"], $user["password"])){
    $error = true;
    $message = "Wrong password!";
  }

  $login = true;
  if (isset($_SESSION["user_id"])){
    header("Location: index.php");
    exit;
  }

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"/>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <div class="container mt-3">
      <form action="login.php" method="get" novalidate>
        <div class="mb-3">
          <label for="user" class="form-label">Username</label>
          <input type="text" class="form-control" id="user" aria-describedby="emailHelp" name="user">
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" class="form-control" id="password" name="password">
        </div>
        <button type="submit" class="btn btn-primary" value="Submit">Submit</button>
      </form>
      <div class="mb-3">
        <?php if ($error):?>
          <p class="text-danger"><?= $message?></p>
        <?php endif;?>
      </div>
    </div>
  </body>
</html>
