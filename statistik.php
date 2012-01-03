<?php
header('Content-type: text/html; charset=iso-8859-1');
require_once('db.inc');

echo '<h2> Statistische Übersicht</h2>';

### Videos
$l=my_sql_query("SELECT COUNT(id) AS count FROM videodatei");
$row=mysql_fetch_assoc($l);
echo '<u><span onclick="zeigeergebnisse()" ><br><br>'.$row[count].' Videos erfasst. </span></u>';

### Fehler
$l=my_sql_query("SELECT COUNT(id) AS count FROM fehler");
$row=mysql_fetch_assoc($l);
echo '<br><br>'.$row[count].' verschiedene Fehler erfasst.';

### DLLSets
$l=my_sql_query("SELECT COUNT(id) AS count FROM dll_set");
$row=mysql_fetch_assoc($l);
echo '<br><br>'.$row[count].' verschiedene DLLs erfasst.';

### Protokolle
$l=my_sql_query("SELECT COUNT(id) AS count FROM protokolldatei");
$row=mysql_fetch_assoc($l);
echo '<br><br>'.$row[count].' Protokolle erfasst.
      <div id=content></div>';
?>
