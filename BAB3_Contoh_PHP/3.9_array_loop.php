<?php
$prod1 = array("Sistem Informasi", "Teknik Komputer", "Komputerisasi Akuntansi");
$jumlah = count($prod1);
for ($x=0; $x<$jumlah; $x++) {
    echo $prod1[$x];
    echo "<br>";
}
?>