<?php
//Открытие сессии
require_once('startsession.php');

//Вывод заголовка страницы
$page_title = 'Анкета';
require_once('header.php');

require_once('appvars.php');
require_once('connectvars.php');

//Проверка, вошел ли пользователь в приложение, прежде чем двигаться дальше
if (!isset($_SESSION['user_id'])) {
echo '<p class="login">Пожалуйста, <a href="login.php">войдите в приложение</a>' . 
' для получения доступа к этой странице.';
exit();
} else {
    echo ('<p class="login">Вы вошли в приложение как ' . $_SESSION['username'] . 
        '. <a href="logout.php">Выход из приложения</a>.</p>');
}

//Вывод навигационного меню
require_once('navmenu.php');


// подключение к БД
$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

  // Если этот пользователь еще не вводил ни одного признака несоответствия в анкету, добавлениев таблицу базы данных записей с пустыми значениями признаков несоответствия 
$query = "SELECT * FROM mismatch_response WHERE user_id = '" . $_SESSION['user_id'] . "'";
$data = mysqli_query($dbc, $query);
if (mysqli_num_rows($data) == 0) {
	// Вначале извлечение списка идентификаторов признаков несоответствия из таблицы mismatch_topic
	$query = "SELECT topic_id FROM mismatch_topic ORDER BY category_id, topic_id";
	$data = mysqli_query($dbc, $query);
	$topicIDs = array();
	while ($row = mysqli_fetch_array($data)) {
	  	array_push($topicIDs, $row['topic_id']);
	}

	// Добавление записей с пустыми значениями признаков несоответствия в таблицу mismatch_response
	foreach ($topicIDs as $topic_id) {
		$query = "INSERT INTO mismatch_response (user_id, topic_id) VALUES ('" . $_SESSION['user_id']. "', '$topic_id')";
		mysqli_query($dbc, $query);
	}
 }

// Если форма "Анкета" отправлена на сервер для обработки, обновить значения признаков несоответствия в таблице mismatch_response
if (isset($_POST['submit'])) {
	// Обновление признаков несоответствия в таблице mismatch_response
	foreach ($_POST as $response_id => $response) {
		$query = "UPDATE mismatch_response SET response = '$response' WHERE response_id = '$response_id'";
		mysqli_query($dbc, $query);
	}
	echo '<p>Your responses have been saved.</p>';
}

// Извлечение данных признаков несоответсвия из базы для создания формы
$query = "SELECT mr.response_id, mr.topic_id, mr.response, mt.name AS topic_name, mc.name AS category_name " .
"FROM mismatch_response AS mr " .
"INNER JOIN mismatch_topic AS mt USING (topic_id) " .
"INNER JOIN mismatch_category AS mc USING (category_id) " .
"WHERE mr.user_id = '" . $_SESSION['user_id'] . "'";
$data = mysqli_query($dbc, $query);
$responses = array();
while ($row = mysqli_fetch_array($data)) {
	array_push($responses, $row);
}

mysqli_close($dbc);

// Создание формы анкеты путем прохождения в цикле массива с данными признаков несоответствия
echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';
echo '<p>Как вы относитесь к этим вещам?</p>';
$category = $responses[0]['category_name'];
echo '<fieldset><legend>' . $responses[0]['category_name'] . '</legend>';
foreach ($responses as $response) {
    // Начинаем новую группу признаков несоответствия только в том случае, если изменилась категория, к которой они относятся
    if ($category != $response['category_name']) {
		$category = $response['category_name'];
		echo '</fieldset><fieldset><legend>' . $response['category_name'] . '</legend>';
    }

    // Вывод кнопок с зависимой фиксацией для выбора признаков несоответствия
    echo '<label ' . ($response['response'] == NULL ? 'class="error"' : '') . ' for="' . $response['response_id'] . '">' . $response['topic_name'] . ':</label>';
    echo '<input type="radio" id="' . $response['response_id'] . '" name="' . $response['response_id'] . '" value="1" ' . ($response['response'] == 1 ? 'checked="checked"' : '') . ' />Love ';
    echo '<input type="radio" id="' . $response['response_id'] . '" name="' . $response['response_id'] . '" value="2" ' . ($response['response'] == 2 ? 'checked="checked"' : '') . ' />Hate<br />';
}
echo '</fieldset>';
echo '<input type="submit" value="Сохранить анкету" name="submit" />';
echo '</form>';


//Вывод нижнего колонтитула
require_once('footer.php');
?>
