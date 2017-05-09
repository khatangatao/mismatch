<?php
require_once('connectvars.php');

if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
	//Имя пользователя/пароль не действительны для отправки HTTP-заголовков,
	//подтверждающих аутентификацию
	header('HTTP/1.1 401 Unauthorized');
	header('WWW-Authenticate:Basic realm="Несоответствия"');
	exit ('<h3>Несоответствия <h3>Извините, вы должны ввести правильные имя пользователя и пароль, чтобы получить доступ к этой странице.' . 
        'Если у вас нет учетной записи, то вы можете ее <a href="signup.php">создать</a>.');
}

// Соединение с БД
$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 

//Получение введенных пользователем данных для аутентификации
$user_username = mysqli_real_escape_string($dbc, trim($_SERVER['PHP_AUTH_USER']));
$user_password = mysqli_real_escape_string($dbc, trim($_SERVER['PHP_AUTH_PW']));

//Поиск имени пользователя и его пароля в БД
$query = "SELECT user_id, username FROM mismatch_user " . 
"WHERE username = '$user_username' AND password = SHA('$user_password')";
$data = mysqli_query($dbc, $query);

if (mysqli_num_rows($data) == 1) {
	//Процедура входа прошла нормально, присваиваем переменным значени
	//идентификатора пользователя и его пароля
	$row = mysqli_fetch_array($data);
	$user_id = $row['user_id'];
	$username= $row['username'];
} else {
	//Имя пользователя и/или его пароль введены неверно
	//поэтомуо отправляются заголовки аутентификации
	header('HTTP/1.1 401 Unauthorized');
	header('WWW-Authenticate:Basic realm="Несоответствия"');
	exit ('<h3>Несоответствия <h3>Извините, вы должны ввести правильные имя пользователя и пароль, чтобы получить доступ к этой странице.' . 
        'Если у вас нет учетной записи, то вы можете ее <a href="signup.php">создать</a>.');
}

//Подтверждение успешного входа в приложение
echo('<p class="login">Вы вошли в приложение как ' . $username . '.</p>');


?>