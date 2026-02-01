<?php
  require_once "functions.php";
  session_start();
  $users = get_file("users.json");
  if (!isset($_SESSION["user_id"]) || !$users[(int)$_SESSION["user_id"]]["is_admin"]){
    header("Location: index.php");
    exit;
  }
  function cmp($a, $b){
    if ($a["votes"] == $b["votes"]){
      return 0;
    }
    return $a["votes"] > $b["votes"] ? -1 : 1;
  }
  
  function set_votes(): Array{
    $projects = get_file("projects.json");
    $votes = get_file("votes.json");
    foreach ($votes as $v){
      if (!isset($projects[$v["project"]]["votes"])) $projects[$v["project"]]["votes"] = 1;
      else $projects[$v["project"]]["votes"] = $projects[$v["project"]]["votes"] + 1;
    }
    return $projects;
  }

  function max_votes($projects) {
    $max = null;
    foreach ($projects as $p) {
        if (!isset($p["votes"])) continue;
        if ($max === null || $p["votes"] > $max["votes"]) {
            $max = $p;
        }
    }
    return $max;
  }
  function set_category($projects): Array{
    $cat = [];
    foreach ($projects as $p){
      if (!isset($p["votes"])) $p["votes"] = 0;
      if (!isset($cat[$p["category"]])) $cat[$p["category"]] = Array($p);
      else array_push($cat[$p["category"]], $p);
    }
    foreach ($cat as &$c){
      usort($c, 'cmp');
    }
    return $cat;
  }
  $user = $users[$_SESSION["user_id"]];
  $projects = set_votes();
  $max = max_votes($projects);
  $cat = set_category($projects);

  $statuses = ["pending", "rework", "approved", "rejected"];
  $stats = [];

  foreach ($projects as $p) {
    $status = $p["status"];
    $category = $p["category"];

    if (!isset($stats[$status])) {
      foreach ($cat as $catName => $_) {
        $stats[$status][$catName] = 0;
      }
    }

    $stats[$status][$category]++;
  }
  $login = false;
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
    <div class="container mt-2">
      <h1 class="fw-bold">
        Statistics
      </h1>
      <h3>Most voted project</h3>
      <table class="table table-striped border align-middle">
        <thead>
          <tr>
            <th scope="col">Title</th>
            <th scope="col">Category</th>
            <th scope="col">Votes</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
              <a href="project.php?id=<?= $max['id'] ?>">
                <?= htmlspecialchars($max["title"]) ?>
              </a>
            </td>
            <td><?= $max["category"]?></td>
            <td ><?= $max["votes"]?></td>
          </tr>
        </tbody>
      </table>
      <h3>Top three projects with most votes for each category</h3>
      <?php foreach($cat as $key => $c): ?>
      <h5><?= $key ?></h5>
        <table class="table table-striped border align-middle">
          <thead>
            <tr>
              <th scope="col">#</th>
              <th scope="col">Title</th>
              <th scope="col">Votes</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($c as $i => $v): ?>
              <?php if ($v["status"] === "pending") continue; ?>
              <?php if ($i >=3) break; ?>
              <tr>
                <th scope="row"><?= $i?></th>
                <td>
                  <a href="project.php?id=<?= $c[$i]['id'] ?>">
                    <?= htmlspecialchars($c[$i]["title"]) ?>
                  </a>
                </td>
                <td><?=$v["votes"]?></td>
              </tr>
            <?php endforeach;?>
          </tbody>
        </table>
      <?php endforeach;?>
      <h3 class="mt-5">Project statistics</h3>

      <div class="row">
        <div class="col-md-6">
          <h5>Projects by status</h5>
          <canvas id="statusBar"></canvas>
        </div>

        <div class="col-md-6">
          <h5>Projects by category</h5>
          <canvas id="categoryColumn"></canvas>
        </div>
      </div>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
      document.addEventListener("DOMContentLoaded", () => {

        const categories = <?= json_encode(array_keys($cat)) ?>;
        const stats = <?= json_encode($stats) ?>;

        const colors = {
          pending: "#0d6efd",
          rework: "#fd7e14",
          approved: "#198754",
          rejected: "#0dcaf0"
        };

        new Chart(document.getElementById("statusBar"), {
          type: "bar",
          data: {
            labels: categories,
            datasets: Object.keys(stats).map(status => ({
              label: status.charAt(0).toUpperCase() + status.slice(1),
              data: categories.map(c => stats[status][c]),
              backgroundColor: colors[status]
            }))
          },
          options: {
            indexAxis: "y",
            responsive: true,
            scales: {
              x: { stacked: true },
              y: { stacked: true }
            }
          }
        });

        new Chart(document.getElementById("categoryColumn"), {
          type: "bar",
          data: {
            labels: categories,
            datasets: Object.keys(stats).map(status => ({
              label: status.charAt(0).toUpperCase() + status.slice(1),
              data: categories.map(c => stats[status][c]),
              backgroundColor: colors[status]
            }))
          },
          options: {
            responsive: true,
            scales: {
              x: { stacked: true },
              y: { stacked: true }
            }
          }
        });

      });
    </script>
  </body>
</html>