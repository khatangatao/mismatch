<?php
require_once('connectvars.php');
session_start();

//Обнуление сообщения об ошибке
$error_msg = "";

//Если пользователь еще не вошел в приложение, попытка войти
if (!isset($_SESSION['user_id'])) {
	if (isset($_POST['submit'])) {
		// Соединение с БД
		$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 

		//Получение введенных пользователем данных для аутентификации
		$user_username = mysqli_real_escape_string($dbc, trim($_POST['username']));
		$user_password = mysqli_real_escape_string($dbc, trim($_POST['password']));
		if ((!empty($user_username)) && (!empty($user_password))) {
			//Поиск имени пользователя и его пароля в БД
			$query = "SELECT user_id, username FROM mismatch_user " . 
			"WHERE username = '$user_username' AND password = SHA('$user_password')";
			$data = mysqli_query($dbc, $query);
			if (mysqli_num_rows($data) == 1) {
				//Процедура входа прошла нормально, сохраняем в переменных сессии и  куки
				//идентификатор пользователя и его имя
				$row = mysqli_fetch_array($data);				
				$_SESSION['user_id'] = $row['user_id'];
				$_SESSION['username'] = $row['username'];
				
				//устанавливаем срок действия куки 30 дней
				setcookie('user_id', $row['user_id'], time()+(60*60*24*30));
				setcookie('username', $row['username'], time()+(60*60*24*30));
				$home_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'] . '/index.php');
				header('Location:' . $home_url);
			} else {
				//Имя пользователя и/или пароль введены неверно. Создание сообщения об ошибке
				$error_msg = 'Для того, чтобы войти в приложение, вы должны ввести правильные имя и пароль';
			}			
		} else {
			//Имя пользователя и/или пароль не введены. Создание сообщения об ошибке
				$error_msg = 'Для того, чтобы войти в приложение, вы должны ввести имя и пароль';
		}
	}
}

?>

<!DOCTYPE html>
<html>
	<head>
		<title>Несоответствия. Вход в приложение.</title>
		<link rel="stylesheet" type="text/css" href="style.css"/>
	</head>
	<body>
		<h3>Несоответствия. Вход в приложение.</h3>
		<?php
		//Если куки не содержат данных, выводятся сообщения об ошибке
		//и форма входа в приложение; в противном случа- подтверждение входа 
		if (empty($_SESSION['user_id'])) {
			echo '<p class="error"' . $error_msg . '</p>';
		?>
		<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
			<fieldset>
				<legend>Вход в приложение</legend>
				<label for="username">Имя пользователя:</label>
				<input type="text" name="username" value="<?php if(!empty($user_username)) echo $user_username; ?>" /><br/>
				<label for="password">Пароль:</label>
				<input type="password" name="password" />
			</fieldset>
			<input type="submit" value="Войти" name="submit" />
		</form>
		<?php
		} else {
			//Подтверждение успешного входа в приложение
			echo ('<p class="login">Вы вошли в приложение как ' . $_SESSION['username'] . '.</p>');
			
			//Ссылка на главную страницу
			$home_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/index.php';
			echo '<a href="' .  $home_url . '">На главную страницу</a>';

		}
		phpinfo(32);
		?>
	</body>
</html>
