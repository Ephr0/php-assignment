<?php 
 $errors = [];
 $data = [];
 $input = $_GET;

 $is_valid = validate($data, $errors, $input);
 function validate(&$data, &$errors, $input) {
    if (!array_key_exists('name',$input) || (strlen($input['name']) < 4 && trim($input['name']) === $input['name'])){
      $errors[0] = true;
    }
    else if (!array_key_exists('type',$input) || !preg_match('/^[a-zA-Z\s]/', $input['type'])){
      $errors[1] = true;
    }
    else if (!array_key_exists('age_m',$input) || !filter_var($input['age_m'], FILTER_VALIDATE_INT)){
      $errors[2] = true;
    }
    
    return count($errors) === 0;
 }
 if ($is_valid){
  $json = json_decode(file_get_contents("cheese_stock.json"));
  $json[]= [
    "name" => $input['name'],
    "type" => $input['type'],
    "age_m" => $input['age_m'],
    "origin" => $input['origin']
  ];
  $res = file_put_contents("cheese_stock.json", json_encode($json));
 }

  // Add to the JSON if valid!
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Cheese Arrived!</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>New Cheese Arrived!</h1>

    <form action="addcheese.php" method="get" novalidate> 
        <h4>Cheese Name</h4>
        <input type="text" name="name" value="<?php echo $input["name"] ?? ""?>">
        
        <h4>Place of Origin</h4>
        <select name="origin">
            <option value="gr">Greece</option>
            <option value="fr">France</option>
            <option value="it">Italy</option>
            <option value="ger">Germany</option>
        </select>
        
        <h4>Type</h4>
        <input type="text" name="type" value="<?php echo $input['type'] ?? '';?>">
        
        <h4>Age (in months)</h4>
        <input type="number" name="age_m" value="<?php echo $input['age_m'] ?? '';?>">
        
        <input type="submit" value="Submit">
    </form>
    <div id="results">
        <?php if ($is_valid): ?>
        <!-- Display this if valid -->
        <h2>Successful Addition! 😍</h2>
        <a href='index.php'>Back to homepage</a>
        
        <?php else: ?>
        <!-- Display this if not valid -->
        <h2>Failed Addition! 😢😭</h2>
        <ul id="errors">
          <?php if (array_key_exists(0, $errors) && $errors[0]): ?>
            <li>Provide a name with at least 4 characters without spaces!</li> <!-- plain -->
          <?php endif; ?>
          <?php if (array_key_exists(1, $errors) && $errors[1]): ?>
            <li>Provide the type!</li> <!-- regex -->
          <?php endif; ?>
          <?php if (array_key_exists(2, $errors) && $errors[2]): ?>
            <li>Provide the age of the cheese!</li> <!-- filter_var -->
          <?php endif; ?>
        </ul>
        <?php endif;?>
    </div>

    <ul id="help">
      <li><a href="addcheese.php?name=&origin=gr&type=&age_m=">❌cheesename ❌cheesetype ❌cheeseage</a></li>
      <li><a href="addcheese.php?name=Feta&origin=gr&type=&age_m=">✅cheesename ❌cheesetype ❌cheeseage</a></li>
      <li><a href="addcheese.php?name=Feta&origin=gr&type=white&age_m=">✅cheesename ✅cheesetype ❌cheeseage</a></li>
      <li><a href="addcheese.php?name=Feta&origin=gr&type=white&age_m=3">✅cheesename ✅cheesetype ✅cheeseage</a></li>
    </ul>
</body>
</html>
