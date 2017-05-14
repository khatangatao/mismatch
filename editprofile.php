<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Несоответствия - Редиктировать профиль</title>
  <link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
  <h3>Несоответствия - Редиктировать профиль</h3>

<?php
require_once('appvars.php');
require_once('connectvars.php');

// подключение к базе данных
$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

if (isset($_POST['submit'])) {
    // Извлекаем данные из массива POST
    $first_name = mysqli_real_escape_string($dbc, trim($_POST['firstname']));
    $last_name = mysqli_real_escape_string($dbc, trim($_POST['lastname']));
    $gender = mysqli_real_escape_string($dbc, trim($_POST['gender']));
    $birthdate = mysqli_real_escape_string($dbc, trim($_POST['birthdate']));
    $city = mysqli_real_escape_string($dbc, trim($_POST['city']));
    $state = mysqli_real_escape_string($dbc, trim($_POST['state']));
    $old_picture = mysqli_real_escape_string($dbc, trim($_POST['old_picture']));
    $new_picture = mysqli_real_escape_string($dbc, trim($_FILES['new_picture']['name']));
    $new_picture_type = $_FILES['new_picture']['type'];
    $new_picture_size = $_FILES['new_picture']['size']; 
    @list($new_picture_width, $new_picture_height) = getimagesize($_FILES['new_picture']['tmp_name']);
    $error = false;

    // Проверяем и, если необходимо, перемещаем загруженный файл
    if (!empty($new_picture)) {
        if ((($new_picture_type == 'image/gif') || ($new_picture_type == 'image/jpeg') || ($new_picture_type == 'image/pjpeg') ||
        ($new_picture_type == 'image/png')) && ($new_picture_size > 0) && ($new_picture_size <= MM_MAXFILESIZE) &&
        ($new_picture_width <= MM_MAXIMGWIDTH) && ($new_picture_height <= MM_MAXIMGHEIGHT)) {
            if ($_FILES['file']['error'] == 0) {
                // Переместить файл в галерею
                $target = MM_UPLOADPATH . basename($new_picture);
                if (move_uploaded_file($_FILES['new_picture']['tmp_name'], $target)) {
                // После перемещения файла нужно удалить старый файл
                    if (!empty($old_picture) && ($old_picture != $new_picture)) {
                        @unlink(MM_UPLOADPATH . $old_picture);
                    }   
                } else {
                    // Не удалось переместить новую картинку. Удаляем временный файл и выводим сообщение об ошибке
                    @unlink($_FILES['new_picture']['tmp_name']);
                    $error = true;
                    echo '<p class="error">Возникла проблема с загрузкой картинки.</p>';
                }
            }
        } else {
            // Новая картинка не соответствует требованиям. Удаляем временный файл и выводим сообщение об ошибке.
            @unlink($_FILES['new_picture']['tmp_name']);
            $error = true;
            echo '<p class="error">Фотография должна быть GIF, JPEG или PNG и не более чем ' . (MM_MAXFILESIZE / 1024) .
              ' KB and ' . MM_MAXIMGWIDTH . 'x' . MM_MAXIMGHEIGHT . ' pixels in size.</p>';
        }
    }

    // Обновляем содержимое данные профиля в базе данных
    if (!$error) {
        if (!empty($first_name) && !empty($last_name) && !empty($gender) && !empty($birthdate) && !empty($city) && !empty($state)) {
        // Если добавлено новое фото, обновляем только колонку с фотографией
            if (!empty($new_picture)) {
                $query = "UPDATE mismatch_user SET first_name = '$first_name', last_name = '$last_name', gender = '$gender', " .
                " birthdate = '$birthdate', city = '$city', state = '$state', picture = '$new_picture' WHERE user_id = '" . $_COOKIE['user_id'] . "'";
            } else {
                $query = "UPDATE mismatch_user SET first_name = '$first_name', last_name = '$last_name', gender = '$gender', " .
                " birthdate = '$birthdate', city = '$city', state = '$state' WHERE user_id = '" . $_COOKIE['user_id'] . "'";
            }
            mysqli_query($dbc, $query);

            // Показать пользователю, что обновление прошло успешно
            echo '<p>Профиль успешно обновлен. Хотите <a href="viewprofile.php">посмотреть свой профиль?</a>?</p>';

            mysqli_close($dbc);
            exit();
        } else {
            echo '<p class="error">Вы должны ввести все данные (фотографию не обязателььно).</p>';
        }
    }
} else {
    // Выгружаем содержимое профиля из базы данных
    $query = "SELECT first_name, last_name, gender, birthdate, city, state, picture FROM mismatch_user WHERE user_id = '" . $_COOKIE['user_id'] . "'";
    $data = mysqli_query($dbc, $query);
    $row = mysqli_fetch_array($data);

    if ($row != NULL) {
        $first_name = $row['first_name'];
        $last_name = $row['last_name'];
        $gender = $row['gender'];
        $birthdate = $row['birthdate'];
        $city = $row['city'];
        $state = $row['state'];
        $old_picture = $row['picture'];
    } else {
        echo '<p class="error">Возникла ошибка с доступом к профилю.</p>';
    }
}

mysqli_close($dbc);
?>

  <form enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MM_MAXFILESIZE; ?>" />
    <fieldset>
      <legend>Персональные данные</legend>
      <label for="firstname">Имя:</label>
      <input type="text" id="firstname" name="firstname" value="<?php if (!empty($first_name)) echo $first_name; ?>" /><br />
      <label for="lastname">Фамилия:</label>
      <input type="text" id="lastname" name="lastname" value="<?php if (!empty($last_name)) echo $last_name; ?>" /><br />
      <label for="gender">Пол:</label>
      <select id="gender" name="gender">
        <option value="M" <?php if (!empty($gender) && $gender == 'M') echo 'selected = "selected"'; ?>>Мужчина</option>
        <option value="F" <?php if (!empty($gender) && $gender == 'F') echo 'selected = "selected"'; ?>>Женщина</option>
      </select><br />
      <label for="birthdate">Дата рождения:</label>
      <input type="text" id="birthdate" name="birthdate" value="<?php if (!empty($birthdate)) echo $birthdate; else echo 'YYYY-MM-DD'; ?>" /><br />
      <label for="city">город:</label>
      <input type="text" id="city" name="city" value="<?php if (!empty($city)) echo $city; ?>" /><br />
      <label for="state">Область:</label>
      <input type="text" id="state" name="state" value="<?php if (!empty($state)) echo $state; ?>" /><br />
      <input type="hidden" name="old_picture" value="<?php if (!empty($old_picture)) echo $old_picture; ?>" />
      <label for="new_picture">Фотография:</label>
      <input type="file" id="new_picture" name="new_picture" />
      <?php if (!empty($old_picture)) {
        echo '<img class="profile" src="' . MM_UPLOADPATH . $old_picture . '" alt="Profile Picture" />';
      } ?>
    </fieldset>
    <input type="submit" value="Сохранить" name="submit" />
  </form>
</body> 
</html>
