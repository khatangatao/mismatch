<?php
//Открытие сессии
require_once('startsession.php');

//Вывод заголовка страницы
$page_title = 'Просмотр профиля';
require_once('header.php');

require_once('appvars.php');
require_once('connectvars.php');

//Вывод навигационного меню
require_once('navmenu.php');

//Проверка, вошел ли пользователь в приложение, прежде чем двигаться дальше
if (!isset($_SESSION['user_id'])) {
echo '<p class="login">Пожалуйста, <a href="login.php">войдите в приложение</a>' . 
' для получения доступа к этой странице.';
exit();
} else {
    echo ('<p class="login">Вы вошли в приложение как ' . $_SESSION['username'] . 
        '. <a href="logout.php">Выход из приложения</a>.</p>');
}


// подключение к БД
$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Выгружаем содержимое профиля из базы данных
if (!isset($_GET['user_id'])) {
    $query = "SELECT username, first_name, last_name, gender, birthdate, city, state, picture FROM mismatch_user WHERE user_id = '" . $_SESSION['user_id'] . "'";
} else {
    $query = "SELECT username, first_name, last_name, gender, birthdate, city, state, picture FROM mismatch_user WHERE user_id = '" . $_GET['user_id'] . "'";
}

$data = mysqli_query($dbc, $query);

if (mysqli_num_rows($data) == 1) {
    // Пользователь найден, выводим его данные 
    $row = mysqli_fetch_array($data);
    echo '<table>';
    if (!empty($row['username'])) {
        echo '<tr><td class="label">Логин:</td><td>' . $row['username'] . '</td></tr>';
    }
    if (!empty($row['first_name'])) {
        echo '<tr><td class="label">Имя:</td><td>' . $row['first_name'] . '</td></tr>';
    }
    if (!empty($row['last_name'])) {
          echo '<tr><td class="label">Фамилия:</td><td>' . $row['last_name'] . '</td></tr>';
    }
    
    if (!empty($row['gender'])) {
        echo '<tr><td class="label">Пол:</td><td>';
        if ($row['gender'] == 'M') {
            echo 'Мужчина';
        } elseif ($row['gender'] == 'F') {
            echo 'Женщина';
        } else {
            echo '?';
        }
        echo '</td></tr>';
    }
    
    if (!empty($row['birthdate'])) {
        if (!isset($_GET['user_id']) || ($_SESSION['user_id'] == $_GET['user_id'])) {
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
    if (!isset($_GET['user_id']) || ($_SESSION['user_id'] == $_GET['user_id'])) {
        echo '<p>Хотите <a href="editprofile.php">редактировать свой профиль?</a>?</p>';
    }
} else {
    echo '<p class="error">Возникли сложности с доступом к профилю.</p>';
}

mysqli_close($dbc);

?>

<?php
//Вывод нижнего колонтитула
require_once('footer.php');
?>

