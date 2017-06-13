<?php
// Функция для прорисовки гистограммы по набору данных, максимальному значению и имени файла
function draw_bar_graph($width, $height, $data, $max_value, $filename) {
    // создать пустой графический файл
    $img = imagecreatetruecolor($width, $height);

    // Создаем цвета
    $bg_color = imagecolorallocate($img, 255, 255, 255);       // белый
    $text_color = imagecolorallocate($img, 255, 255, 255);     // белый
    $bar_color = imagecolorallocate($img, 0, 0, 0);            // черный
    $border_color = imagecolorallocate($img, 192, 192, 192);   // светло-серый

    // заполняем фон
    imagefilledrectangle($img, 0, 0, $width, $height, $bg_color);
    // Рисуем графики
    $bar_width = $width / ((count($data) * 2) + 1);
    for ($i = 0; $i < count($data); $i++) {
      imagefilledrectangle($img, ($i * $bar_width * 2) + $bar_width, $height,
        ($i * $bar_width * 2) + ($bar_width * 2), $height - (($height / $max_value) * $data[$i][1]), $bar_color);
      imagestringup($img, 5, ($i * $bar_width * 2) + ($bar_width), $height - 5, $data[$i][0], $text_color);
    }

    // Рисуем рамку
    imagerectangle($img, 0, 0, $width - 1, $height - 1, $border_color);

    // рисуем диапазон значений графика
    for ($i = 1; $i <= $max_value; $i++) {
      imagestring($img, 5, 0, $height - ($i * ($height / $max_value)), $i, $bar_color);
    }

    // Запись изображения в файл
    imagepng($img, $filename, 5);
    imagedestroy($img);
 } 


//Открытие сессии
require_once('startsession.php');

//Вывод заголовка страницы
$page_title = 'Анкета';
require_once('header.php');

require_once('appvars.php');
require_once('connectvars.php');

//Проверка, вошел ли пользователь в приложение, прежде чем двигаться дальше
if (!isset($_SESSION['user_id'])) {
echo '<p class="login">Пожалуйста <a href="login.php">войдите</a> для доступа к странице.</p>';
exit();
}

//Вывод навигационного меню
require_once('navmenu.php');

// подключение к БД
$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Ищем несоответствия только в том случае, если пользователь заполнил свою анкету
$query = "SELECT * FROM mismatch_response WHERE user_id = '" . $_SESSION['user_id'] . "'";
$data = mysqli_query($dbc, $query);
if (mysqli_num_rows($data) != 0) {
    // Сперва извлечение ответов пользователя из таблицы ответов 
    $query = "SELECT mr.response_id, mr.topic_id, mr.response, mt.name AS topic_name, mc.name AS category_name " .
    "FROM mismatch_response AS mr " .
    "INNER JOIN mismatch_topic AS mt USING (topic_id) " .
    "INNER JOIN mismatch_category AS mc USING (category_id) " .
    "WHERE mr.user_id = '" . $_SESSION['user_id'] . "'";
    $data = mysqli_query($dbc, $query);
    $user_responses = array();
    while ($row = mysqli_fetch_array($data)) {
      array_push($user_responses, $row);
    }

    // Инициализируем переменные для поиска несоответсвия
    $mismatch_score = 0;
    $mismatch_user_id = -1;
    $mismatch_topics = array();
    $mismatch_categories = [];

    // Циклически проходим ответы всех пользователей и сравниваем с текущим пользователем
    $query = "SELECT user_id FROM mismatch_user WHERE user_id != '" . $_SESSION['user_id'] . "'";
    $data = mysqli_query($dbc, $query);
    while ($row = mysqli_fetch_array($data)) {
        // Извлекаем ответы потенциального кандидата на несоответствие
        $query2 = "SELECT response_id, topic_id, response FROM mismatch_response WHERE user_id = '" . $row['user_id'] . "'";
        $data2 = mysqli_query($dbc, $query2);
        $mismatch_responses = array();
        while ($row2 = mysqli_fetch_array($data2)) {
            array_push($mismatch_responses, $row2);
        }

        // Сравниваем все ответы и выводим оценку несоответствия
        
        //переменная, в которую мы занесем оценку несоответствия
        $score = 0;
        //массив несоответсвий между текущим пользователем и идеальным несоответствием
        $topics = [];
        //массив категорий, к которым принадлежат найденные признаки несоответствия
        $categories = [];        
        
        for ($i = 0; $i < count($user_responses); $i++) {
            if ($user_responses[$i]['response'] + $mismatch_responses[$i]['response'] == 3) {
                $score += 1;
                array_push($topics, $user_responses[$i]['topic_name']);
                array_push ($categories, $user_responses[$i]['category_name']);
            }
        }

        // Проверяем, не является ли найденное несоответсвие наилучшим.
        if ($score > $mismatch_score) {
            // Если да, то заносим новое несоответсвие в память
            $mismatch_score = $score;
            $mismatch_user_id = $row['user_id'];
            $mismatch_topics = array_slice($topics, 0);
            $mismatch_categories = array_slice($categories, 0);
        }
    }

    // Проверка, что несоответствие действительно найдено
    if ($mismatch_user_id != -1) {
        $query = "SELECT username, first_name, last_name, city, state, picture FROM mismatch_user WHERE user_id = '$mismatch_user_id'";
        $data = mysqli_query($dbc, $query);
        if (mysqli_num_rows($data) == 1) {
            // Выводим данные пользователя (идеального несоответствия)
            $row = mysqli_fetch_array($data);
            echo '<table><tr><td class="label">';
            if (!empty($row['first_name']) && !empty($row['last_name'])) {
                echo $row['first_name'] . ' ' . $row['last_name'] . '<br />';
            }
            if (!empty($row['city']) && !empty($row['state'])) {
                echo $row['city'] . ', ' . $row['state'] . '<br />';
            }
            echo '</td><td>';
            if (!empty($row['picture'])) {
                echo '<img src="' . MM_UPLOADPATH . $row['picture'] . '" alt="Profile Picture" /><br />';
            }
            echo '</td></tr></table>';

            // Выводим названия несоответствий между пользователями
            echo '<h4>Вы не совпадаете по ' . count($mismatch_topics) . ' пунктам:</h4>';
            echo '<table><tr>';
            $i = 0;
            foreach ($mismatch_topics as $topic) {
                echo '<td>' . $topic . '</td>';
                if (++$i > 3) {
                    echo '</tr><tr>';
                    $i = 0;
                }
            }
            echo '</tr></table>';
            
            //Расчет несовпадений по категории
//            $category_totals = array(array($mismatch_categories[0], 0));
//            foreach ($mismatch_categories as $category) {
//                if ($category_totals[count($category_totals) - 1][0] != $category) {
//                    array_push($category_totals, array($category, 1));
//                } else {
//                    $category_totals[count($category_totals) - 1][1]++;
//                }
//            }
            // Calculate the mismatched category totals
            $category_totals = array(array($mismatch_categories[0], 0));
            foreach ($mismatch_categories as $category) {
                if ($category_totals[count($category_totals) - 1][0] != $category) {
                    array_push($category_totals, array($category, 1));
                } else {
                    $category_totals[count($category_totals) - 1][1] ++;
                }
            }

            //Генерация и отображение гистограммы несовпадений
            echo '<h4> Разбивка несовпадений по категориям:</h4>';
                draw_bar_graph(480, 240, $category_totals, 5, MM_UPLOADPATH . $_SESSION['user_id'] . '-mymismatchgraph.png');
            echo '<img src="' . MM_UPLOADPATH . $_SESSION['user_id'] . '-mymismatchgraph.png" alt="График категорий несоответствий" /><br />';

            // Ссылка на идеальное несоответствие
            echo '<h4>Просмотр <a href=viewprofile.php?user_id=' . $mismatch_user_id . '>' . $row['first_name'] . '\'s профиля</a>.</h4>';
        }
    }
  } else {
    echo '<p>Вы должны сперва <a href="questionnaire.php"> заполнить анкету </a>, а потом искать несоответствия.</p>';
}

mysqli_close($dbc);

//Вывод нижнего колонтитула
require_once('footer.php');

