<?php
include("../DB_CONNECT.php"); 
session_start(); 

// User login check
if ($_SESSION["USERNAME"] && $_SESSION["KEY"] == 1) {

    // Get the number of data
    $SELECT_COUNT_VF = $connect->prepare("SELECT COUNT(*) FROM vrfurn001");
    $SELECT_COUNT_VF->execute();
    $COUNT_VF = $SELECT_COUNT_VF->fetchColumn();

    echo "<h2>Approved Data</h2>";

    // List Data
    for ($i = $COUNT_VF; $i >= 1; $i--) {
        $WCLN_DATA = $connect->prepare("SELECT * FROM vrfurn001 WHERE id = :id");
        $WCLN_DATA->execute(array(":id" => $i));
        $result = $WCLN_DATA->fetch();

        echo '<hr><br><div class="data-item">';
        echo "<b>" . "Action Performer : " . $result['urn_sender_verify'] . "</b><br><br>";
        echo "<span style='font-weight: bold;'>ID:</span> " . $i . "<br>";
        echo "<span style='font-weight: bold;'>Data Name:</span> " . $result['urun_verify'] . "<br>";
        echo "<span style='font-weight: bold;'>Piece:</span> " . $result['adet_verify'] . "<br>";
        echo "<span style='font-weight: bold;'>Data Code:</span> " . $result["urun_kodu_verify"] . "<br>";

        echo '<form action="main_page.php" method="POST">
                <button name="rev" style="padding: 8px 15px; margin-top:15px; margin-right: 10px; cursor: pointer; border: none; outline: none; border-radius: 5px; font-size: 14px; background-color: #d9534f; color: white;" value="'.$i.'">Sent Back</button>
              </form>';
    }
} else {
    header("location: ../index.php");
    exit;
}
?>
