<?php
  require_once("functions.php");
  session_start();
  $users = get_file("users.json");
  $projects = get_file("projects.json");
  if ($projects[$_GET["id"]]["status"] !== "approved" && !$users[$_SESSION["user_id"]]["is_admin"] && $projects[$_GET["id"]]["owner"] !== $_SESSION["user_id"]){
    header("Location: index.php");
    exit;
  }
  function get_rework($project){
    if ($project["status"] !== "rework") return null;
    $rework = get_file("rework.json");
    $filtered = array_filter($rework, fn($r) => $r["project_id"] === $project["id"]);
    return array_values($filtered);
  }
  $login = true;
  $user = null;
  $admin = false;
  if (isset($_SESSION["user_id"])){
    $login = false;
    $user = $users[$_SESSION["user_id"]];
    $admin = $user["is_admin"];
  }
  $project = $projects[$_GET["id"]];
  $rework = get_rework($project);
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Project Details</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
    rel="stylesheet"
  >
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
    <div class="container my-5">
      <div class="mb-4">
        <h1 class="fw-bold">
          <?= $project['title'] ?>
        </h1>

        <span class="badge bg-secondary">
          <?= $project['category'] ?>
        </span>

        <span class="badge bg-info text-dark ms-2">
          <?= $project['status'] ?>
        </span>
      </div>
      <div class="mb-4">
        <img
          src=""
          alt="Project image"
          class="img-fluid rounded border"
        >
      </div>

      <div class="mb-4">
        <h4>Description</h4>
        <p class="text-muted">
          <?= $project['description'] ?>
        </p>
      </div>

      <div class="row mb-4">
        <div class="col-md-6">
          <ul class="list-group">
            <li class="list-group-item">
              <strong>Submitted by:</strong>
               <?= $users[$project['owner']]["username"] ?> 
            </li>
            <li class="list-group-item">
              <strong>Postal code:</strong>
               <?= $project['postal_code'] ?> 
            </li>
          </ul>
        </div>

        <div class="col-md-6">
          <ul class="list-group">
            <li class="list-group-item">
              <strong>Submission date:</strong>
               <?= $project['submitted'] ?> 
            </li>
            <li class="list-group-item">
              <strong>Publication date:</strong>
               <?= $project['approved'] ?? '-' ?> 
            </li>
          </ul>
        </div>
      </div>
      <?php if ($admin): ?>
      <div class="card border-warning mb-4">
        <div class="card-header bg-warning-subtle">
          <strong>Admin actions</strong>
        </div>

        <div class="card-body">
          <form method="post" action="rework.php">
            <input type="hidden" name="project_id" value="<?=$project["id"]?>"/>
            <button class="btn btn-success me-2" type="submit" name="approved">
              Approve
            </button>

            <button class="btn btn-danger me-2" type="submit" name="rejected">
              Reject
            </button>

            <div class="mt-3">
              <label class="form-label">Send back for rework</label>
              <textarea
                class="form-control"
                rows="3"
                name="comments"
                placeholder="Explain what needs to be fixed..."
              ></textarea>
              <?php if (isset($_GET["error"]) && $_GET["error"]): ?>
                <div class="mt-1">
                  <p class="text-danger"><?= $_GET["message"] ?></p>
                </div>
              <?php endif;?>
              <button class="btn btn-warning mt-2" type="submit" name="rework">
                Send to rework
              </button>
            </div>
          </form>
        </div>
      </div>
      <?php endif;?>

      <?php if (!$admin && $user && $project["status"] === "rework"): ?>

        <div class="alert alert-warning">
          <strong>Latest rework comment:</strong><br>
          <?= htmlspecialchars($rework[array_key_last($rework)]["comments"]) ?>
        </div>

        <?php if (count($rework) > 1): ?>
          <div class="dropdown mb-4">
            <button
              class="btn btn-outline-secondary dropdown-toggle"
              type="button"
              data-bs-toggle="dropdown"
              aria-expanded="false"
            >
              Previous reworks
            </button>

            <ul class="dropdown-menu w-100 p-3">
              <?php foreach ($rework as $i => $r): ?>
                <?php if ($i === array_key_last($rework)) continue; ?>

                <li class="mb-3">
                  <div class="fw-bold mb-1">
                    Rework #<?= $i + 1 ?>
                  </div>

                  <div class="border rounded p-3 bg-light small">

                    <p><strong>Title:</strong><br>
                      <?= htmlspecialchars($r["title"]) ?>
                    </p>

                    <p><strong>Description:</strong><br>
                      <?= nl2br(htmlspecialchars($r["description"])) ?>
                    </p>

                    <p><strong>Category:</strong>
                      <?= htmlspecialchars($r["category"]) ?>
                    </p>

                    <p><strong>Postal code:</strong>
                      <?= htmlspecialchars($r["postal_code"]) ?>
                    </p>

                    <p><strong>Submitted:</strong>
                      <?= htmlspecialchars($r["submitted"]) ?>
                    </p>

                    <p><strong>Admin comment:</strong><br>
                      <?= nl2br(htmlspecialchars($r["comments"])) ?>
                    </p>

                    <?php if (!empty($r["image"])): ?>
                      <img
                        src="<?= htmlspecialchars($r["image"]) ?>"
                        alt="Project image"
                        class="img-fluid rounded mt-2"
                        style="max-width: 200px;"
                      >
                    <?php endif; ?>

                  </div>
                </li>

              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form action="editproject.php" method="post">
          <input type="hidden" name="title" value="<?= htmlspecialchars($project["title"]) ?>"/>
          <input type="hidden" name="description" value="<?= htmlspecialchars($project["description"]) ?>"/>
          <input type="hidden" name="category" value="<?= htmlspecialchars($project["category"]) ?>"/>
          <input type="hidden" name="postal_code" value="<?= htmlspecialchars($project["postal_code"]) ?>"/>
          <input type="hidden" name="image" value="<?= htmlspecialchars($project["image"]) ?>"/>
          <input type="hidden" name="id" value="<?= (int)$project["id"] ?>"/>

          <button class="btn btn-primary" type="submit" name="rework">
            Resubmit for approval
          </button>
        </form>

      <?php endif; ?>


    </div>
    
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
  </body>
  </html>
