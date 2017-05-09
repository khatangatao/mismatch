<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//RU"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Несоответствие. Регистрация</title>
  <link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
  <h3>Несоответствие. Регистрация</h3>

<?php
  require_once('appvars.php');
  require_once('connectvars.php');

  // Соединение с БД
  $dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

  if (isset($_POST['submit'])) {
    // Grab the profile data from the POST
    $username = mysqli_real_escape_string($dbc, trim($_POST['username']));
    $password1 = mysqli_real_escape_string($dbc, trim($_POST['password1']));
    $password2 = mysqli_real_escape_string($dbc, trim($_POST['password2']));

    if (!empty($username) && !empty($password1) && !empty($password2) && ($password1 == $password2)) {
      // Проверка имени пользователя на уникальность
      $query = "SELECT * FROM mismatch_user WHERE username = '$username'";
      $data = mysqli_query($dbc, $query);
      if (mysqli_num_rows($data) == 0) {
        // The username is unique, so insert the data into the database
        $query = "INSERT INTO mismatch_user (username, password, join_date) VALUES ('$username', SHA('$password1'), NOW())";
        mysqli_query($dbc, $query);

        // Confirm success with the user
        echo '<p>Учетная запись создана. Теперь вы можете <a href="login.php">войти</a>.</p>';

        mysqli_close($dbc);
        exit();
      }
      else {
        // An account already exists for this username, so display an error message
        echo '<p class="error">Пользователь с таким именем уже существует. Выберите другое имя.</p>';
        $username = "";
      }
    }
    else {
      echo '<p class="error">Вы должны ввести все необходимые данные для регистрации.</p>';
    }
  }

  mysqli_close($dbc);
?>

  <p>Пожалуйста введите имя и пароль для регистрации на сайте "Несоответствие".</p>
  <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <fieldset>
      <legend>Registration Info</legend>
      <label for="username">Имя пользователя:</label>
      <input type="text" id="username" name="username" value="<?php if (!empty($username)) echo $username; ?>" /><br />
      <label for="password1">Пароль:</label>
      <input type="password" id="password1" name="password1" /><br />
      <label for="password2">Подтвердите пароль:</label>
      <input type="password" id="password2" name="password2" /><br />
    </fieldset>
    <input type="submit" value="Зарегистрироваться" name="submit" />
  </form>
</body> 
</html>
