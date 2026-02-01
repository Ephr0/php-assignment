<?php
  session_start();
  if (isset($_SESSION["user_id"])){
    header("Location: index.php");
    exit;
  }
  function user_data():Array{
    $file = file_get_contents("users.json");
    return json_decode($file, true);
  }

  function user_unique($user){
    $json = user_data();
    foreach ($json as $obj){
      if ($obj["username"] == $user){
        return false;
      }
    }
    return true;
  }

  function validate($form){
    if (!$form){
      return "Empty";
    }
    if (str_contains($form["user"], " ")){
      return "Username contains a space!";
    }
    if (!user_unique($form["user"])){
      return "User is not unique!";
    }
    if (!filter_var($form["email"], FILTER_VALIDATE_EMAIL)){
      return "Email must be in correct format!";
    }
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $form["password"])){
      return "Password must be at least 8 characters with at least 1 lower and upper character, and 1 digit!";
    }
    if ($form["password"] !== $form["confirm"]){
      return "Password does not match!";
    }

    return "Success!";
  }
  $login = true;
  $message = validate($_POST);
  $error = true;
  if ("Empty" === $message){
    $error = null;
  }
  else if ("Success!" === $message){
    $error = false;
  }
  if (!$error && count($_POST) > 0){
    $json = user_data();
    $obj = [
      "id" => count($json),
      "username" => $_POST["user"],
      "email" => $_POST["email"],
      "password" => password_hash($_POST["password"], PASSWORD_DEFAULT),
      "is_admin" => false
    ];
    array_push($json, $obj);
    $new_json = json_encode($json);
    file_put_contents("users.json", $new_json);
    header("Location: login.php");
    exit;
  }
?>
<!doctype html>
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
      <form action="register.php" method="post" novalidate>
        <div class="mb-3">
          <label for="email" class="form-label">Email address</label>
          <input type="email" class="form-control" id="email" aria-describedby="emailHelp" name="email">
        </div>
        <div class="mb-3">
          <label for="user" class="form-label">Username</label>
          <input type="text" class="form-control" id="user" aria-describedby="emailHelp" name="user">
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" class="form-control" id="password" name="password">
        </div>
        <div class="mb-3">
          <label for="confirm" class="form-label">Confirm password</label>
          <input type="password" class="form-control" id="confirm" name="confirm">
        </div>
        <button type="submit" class="btn btn-primary" value="Submit">Submit</button>
      </form>
      <div class="mb-3">
        <?php if ($error):?>
        <p class="text-danger"><?= $message?></p>
        <?php elseif ($error === false):?>
        <p class="text-blue-600"><?= $message?> </p>
        <a href="login.php">Go to login</a>
        <?php endif;?>
      </div>
    </div>
  </body>

</html>