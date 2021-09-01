<?php

$dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');

$insert_sth = $dbh->prepare("INSERT INTO bbs (body) VALUES (:text)");
$insert_sth->execute(array(
    ':text' => 'hello world!!!!!!!!!'
));
print('insertできました');
