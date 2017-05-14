<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Несоответствия - Просмотр профиля</title>
  <link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
  <h3>Несоответствия - Просмотр профиля</h3>

<?php
require_once('appvars.php');
require_once('connectvars.php');

// подключение к БД
$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Выгружаем содержимое профиля из базы данных
if (!isset($_GET['user_id'])) {
    $query = "SELECT username, first_name, last_name, gender, birthdate, city, state, picture FROM mismatch_user WHERE user_id = '" . $_COOKIE['user_id'] . "'";
} else {
    $query = "SELECT username, first_name, last_name, gender, birthdate, city, state, picture FROM mismatch_user WHERE user_id = '" . $_GET['user_id'] . "'";
}

$data = mysqli_query($dbc, $query);

if (mysqli_num_rows($data) == 1) {
    // Пользователь найден, выводим его данные 
    $row = mysqli_fetch_array($data);
    echo '<table>';
    if (!empty($row['username'])) {
        echo '<tr><td class="label">Username:</td><td>' . $row['username'] . '</td></tr>';
    }
    if (!empty($row['first_name'])) {
        echo '<tr><td class="label">First name:</td><td>' . $row['first_name'] . '</td></tr>';
    }
    if (!empty($row['last_name'])) {
          echo '<tr><td class="label">Last name:</td><td>' . $row['last_name'] . '</td></tr>';
    }
    
    if (!empty($row['gender'])) {
        echo '<tr><td class="label">Gender:</td><td>';
        if ($row['gender'] == 'M') {
            echo 'Male';
        } elseif ($row['gender'] == 'F') {
            echo 'Female';
        } else {
            echo '?';
        }
        echo '</td></tr>';
    }
    
    if (!empty($row['birthdate'])) {
        if (!isset($_GET['user_id']) || ($_COOKIE['user_id'] == $_GET['user_id'])) {
            // Показываем пользовател его дату рождения
            echo '<tr><td class="label">Дата рождения:</td><td>' . $row['birthdate'] . '</td></tr>';
        } else {
            // Показываем только год, если просматривает кто-то другой
            list($year, $month, $day) = explode('-', $row['birthdate']);
            echo '<tr><td class="label">Год рождения:</td><td>' . $year . '</td></tr>';
        }
    }
    if (!empty($row['city']) || !empty($row['state'])) {
        echo '<tr><td class="label">Расположение:</td><td>' . $row['city'] . ', ' . $row['state'] . '</td></tr>';
    }
    if (!empty($row['picture'])) {
        echo '<tr><td class="label">Фотография:</td><td><img src="' . MM_UPLOADPATH . $row['picture'] .
        '" alt="Фотография" /></td></tr>';
    }
    echo '</table>';
    if (!isset($_GET['user_id']) || ($_COOKIE['user_id'] == $_GET['user_id'])) {
        echo '<p>Хотите <a href="editprofile.php">редактировать свой профиль?</a>?</p>';
    }
} else {
    echo '<p class="error">Возникли сложности с доступом к профилю.</p>';
}

phpinfo(32);
mysqli_close($dbc);

?>
</body> 
</html>
