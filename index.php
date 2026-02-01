<?php 
  session_start();
  require_once "functions.php";
  $login = true;
  $user = "";
  if (isset($_SESSION["user_id"])){
    $login = false;
    $users = get_file("users.json");
    $user = $users[$_SESSION["user_id"]];
    if (array_key_exists("project_id",$_POST)){
      $votes = get_file("votes.json");
      calc_votes($votes);
    }
  }

  function vote_eligible($proj){
    $curr_date = new DateTime($proj["approved"]);
    $now_date = new DateTime("now", new DateTimeZone("UTC"));
    $diff = $curr_date->diff($now_date);
    return ($proj["status"] === "approved" && $diff->days<=14);
  }


  function get_categories($login, &$cat_count){
    $proj = get_file("projects.json");
    $votes = get_file("votes.json");
    foreach ($votes as $v){
      if (!array_key_exists("votes", $proj[$v["project"]])){
        $proj[$v["project"]]["votes"] = 1;
      }
      else{
        $proj[$v["project"]]["votes"] += 1;
      }
      if (!$login && $_SESSION["user_id"] == $v["user"]){
        $proj[$v["project"]]["voted"] = true;
      }
      else{
        $proj[$v["project"]]["voted"] = false;
      }
    }
    $cat = [];
    foreach ($proj as $obj){
      if ($obj["status"] !== "approved") continue;
      if (!array_key_exists("votes", $obj)){
        $obj["votes"] = 0;
      }
      if (!array_key_exists("voted", $obj)){
        $obj["voted"] = false;
      }
      if (!array_key_exists($obj["category"], $cat)) $cat[$obj["category"]][] = $obj;
      else array_push($cat[$obj["category"]],$obj);
      if (!array_key_exists($obj["category"], $cat_count)) $cat_count[$obj["category"]] = 0;
      if ($obj["voted"]) $cat_count[$obj["category"]] += 1;
    }
    return $cat;
  }
  function filtered_cats($categories){
    if (!isset($_GET["category"])) return $categories;
    if ($_GET["category"] === "All") return $categories;
    if (isset($_GET["category"])){
      if ($_GET["category"] !== "default" && array_key_exists($_GET["category"], $categories)){
        $categories = [$_GET["category"] => $categories[$_GET["category"]]];
      }
      else{
        $categories = [$_GET["category"] => []];
      }
    }
    return $categories;
  }
  $cat_count = [];
  $categories = get_categories($login, $cat_count);
  $categories = filtered_cats($categories);
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
    <div class="container">
        <div class="mt-3 mb-3">
          <form method="get">
            <select name="category" class="form-select" onchange="this.form.submit()">
              <option value="default">Choose a category</option>
              <option value="All">All</option>
              <option value="Local small project">Local small project</option>
              <option value="Local big project">Local big project</option>
              <option value="Equal opportunity Budapest">Equal opportunity Budapest</option>
              <option value="Green Budapest">Green Budapest</option>
            </select>
          </form>
        </div>
      <?php foreach($categories as $key => $c): ?>
      <h5><?= $key ?></h5>
        <table class="table table-striped border align-middle">
          <thead>
            <tr>
              <th scope="col">#</th>
              <th scope="col">Title</th>
              <th scope="col">Approved</th>
              <th scope="col">Votes</th>
              <?php if (!$login):?>
                <th scope="col">Vote</th>
              <?php endif;?>
            </tr>
          </thead>
          <tbody>
            <?php for($i=0;$i<count($c);$i++): ?>
              <tr>
                <th scope="row"><?= $i?></th>
                <td>
                  <a href="project.php?id=<?= $c[$i]['id'] ?>">
                    <?= htmlspecialchars($c[$i]["title"]) ?>
                  </a>
                </td>
                <td><?=$c[$i]["approved"]?></td>
                <td><?=$c[$i]["votes"]?></td>
                <?php if (!$login): ?>
                  <td>
                    <button
                      id="vote-btn"
                      class="btn btn-sm <?= $c[$i]["voted"] ? 'btn-outline-primary' : 'btn-primary' ?>"
                      data-project-id="<?= $c[$i]['id'] ?>"
                      <?= ($cat_count[$key]>=3 || !vote_eligible($c[$i])) ? 'disabled' : '' ?>
                    >
                      <?= $c[$i]["voted"] ? 'Remove vote' : 'Vote' ?>
                    </button>
                  </td>
                <?php endif;?>
              </tr>
            <?php endfor;?>
          </tbody>
        </table>
      <?php endforeach;?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js">
    </script>
    <script>
      function disableButtons(table, disable){
        console.log(disable);
        table.querySelectorAll("#vote-btn").forEach(btn => {
          console.log(btn);
          if (btn.classList.contains("btn-primary")){
            btn.disabled = disable;
          }
        })
      }

      document.querySelectorAll("#vote-btn").forEach(btn => {
        btn.addEventListener("click", (e) => {
          const id = e.target.dataset.projectId;
          const form = new FormData();
          form.append("project_id", id);
          fetch("vote.php",{
            method: "POST",
            body: form
          })
          .then((res) => res.json())
          .then(data => {
            if (!data.success){
              console.log("voting failed!");
              return;
            }
            const voteCell = btn.closest("tr").querySelector("td:nth-child(4)");
            voteCell.textContent = data.votes;

            btn.classList.toggle("btn-primary");
            btn.classList.toggle("btn-outline-primary");     
            disableButtons(btn.closest("table"), data.disableToggle)
            btn.innerText = btn.innerText === "Remove vote" ? "Vote" : "Remove vote";
          })
        })
      })

    </script>
  </body>
</html>
