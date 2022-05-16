<?php

echo "Введите ваше имя: \n";
$name = readline();
echo "Введите email: \n";
$email = readline();
echo "Введите телефон: \n";
$phone = readline();
echo "Введите номер кабинета которую хотите забронировать: \n";
$room = readline();
echo "Введите дату и время заезда в формате: \"ГГ-ММ-ДД ЧЧ:мм\" \n";
$fromStr = readline();
echo "Введите дату и время отъезда в формате: \"ГГ-ММ-ДД ЧЧ:мм\" \n";
$toStr = readline();

$from = strtotime($fromStr . ":00");
$to = strtotime($toStr . ":00");



try {
    $dsn = 'mysql:host=localhost;dbname=booking';
    $pdo = new PDO($dsn, 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = ("SELECT from_, to_, name FROM bookings WHERE room=:room");
    $query = $pdo->prepare($sql);
    $query->execute(['room' => $room]);
    $data = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    return false;
}


if (!empty($data)) {
    foreach ($data as $key) {
        $from_ = strtotime($key['from_']);
        $to_ = strtotime($key['to_']);
        if (!($from < $from_ && $to < $from_ || $from > $from_ && $from > $to_)) {
            warningNotify($key);
            goto jump;
        }
    }
    booking($room, $fromStr, $toStr, $name, $email, $phone, $pdo);
    successNotify($room, $fromStr, $toStr);
    jump:
} else {
    booking($room, $fromStr, $toStr, $name, $email, $phone, $pdo);
    successNotify($room, $fromStr, $toStr);
}




function booking($room, $fromStr, $toStr, $name, $email, $phone, $pdo)
{
    $sql = "INSERT INTO bookings(room, from_, to_, name, email, phone) VALUES('$room', '$fromStr', '$toStr', '$name', '$email', '$phone')";
    $query = $pdo->prepare($sql);
    $query->execute();

}

function successNotify($room, $fromStr, $toStr)
{
    echo "Вы заняли кабинет " . $room . " с " . $fromStr . " по   " . $toStr . "\n";
}

function warningNotify($key)
{
    $to = substr($key['to_'], 0, -3);
    echo "К  сожалению данный кабинет занят человеком по имени " . "\"" . $key['name'] . "\"" . " до " . $to;
}
