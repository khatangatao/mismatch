<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//RU"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Несоответствие! Противоположности притягиваются!</title>
  <link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
  <h3>Несоответствие! Противоположности притягиваются!</h3>

<?php
  require_once('appvars.php');
  require_once('connectvars.php');

  // Создание панели навигации
  echo '&#10084; <a href="viewprofile.php">View Profile</a><br />';
  echo '&#10084; <a href="editprofile.php">Edit Profile</a><br />';

  // Соединение с БД
  $dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 

  // Запрос пользовательских данных
  $query = "SELECT user_id, first_name, picture FROM mismatch_user WHERE first_name IS NOT NULL ORDER BY join_date DESC LIMIT 5";
  $data = mysqli_query($dbc, $query);

  // Loop through the array of user data, formatting it as HTML
  echo '<h4>Latest members:</h4>';
  echo '<table>';
  while ($row = mysqli_fetch_array($data)) {
    if (is_file(MM_UPLOADPATH . $row['picture']) && filesize(MM_UPLOADPATH . $row['picture']) > 0) {
      echo '<tr><td><img src="' . MM_UPLOADPATH . $row['picture'] . '" alt="' . $row['first_name'] . '" /></td>';
    }
    else {
      echo '<tr><td><img src="' . MM_UPLOADPATH . 'nopic.jpg' . '" alt="' . $row['first_name'] . '" /></td>';
    }
    echo '<td>' . $row['first_name'] . '</td></tr>';
  }
  echo '</table>';

  mysqli_close($dbc);
?>

</body> 
</html>
