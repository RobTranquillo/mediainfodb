<?php
header('Content-type: text/html; charset=iso-8859-1');
include_once('htmlkopf.html');



echo "<div id='Login1' class='rund main-color' style='width:50%;'>
        <span onclick='protokolle()'> <u>Home</u> </span> &nbsp;
        <span onclick='read_new_prot()'> <u>Files einlesen</u> </span> &nbsp;
        <span onclick='statistik()'> <u>Statistik</u> </span> &nbsp;
        </div>

      <div id='content_box' class='rund liste border main-color'>
        <div id='content' class='tinyborder rund' style='overflow:auto; height:99%; '></div>
        </div>";
?>

</body>
</html>