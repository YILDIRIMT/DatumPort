<?php
try {
   $connect = new PDO("mysql:host=localhost;dbname=datumport_msql;charset=utf8","root","");
} catch (PDOException $e) {
echo $e->getMessage();
}
?>
