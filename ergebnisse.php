<?php
header('Content-type: text/html; charset=iso-8859-1');
include_once('db.inc');

$i=0;
$out_arr = array();

$query =  ' SELECT DISTINCT name,groesse, eigenschaft, wert FROM videodatei ';
$query .= ' INNER JOIN datei2eigenschaften ON datei2eigenschaften.videodatei = videodatei.id
            INNER JOIN videoeigenschaften ON datei2eigenschaften.videoeigenschaft = videoeigenschaften.id';
$query .= ' LIMIT 0,100 ';

$l=my_sql_query($query);
while($row=mysql_fetch_assoc($l))
 {
   ### fülle das Out-Array (nummerisch nach Dateinamen getrennt)
    if($out_arr[$i][name] != $row[name]) $i++;
    $out_arr[$i][name] = $row[name];
    $out_arr[$i][groesse] = $row[groesse];
    if($out_arr[$i][props]) $out_arr[$i][props] .= ', ';
    $out_arr[$i][props] .= $row[wert];
 }

foreach($out_arr AS $row) $out .= "<tr><td> $row[name] </td><td> $row[groesse] MB </td><td style='font-size:small'> $row[props] </td></tr>";
echo '<br /><table rules=none><tr>
                <td> <b><i>Datei</b></i><br>('.$i.' Videoverweise in der Datenbank)</td>
                <td><b><i>Größe</b></i></td>
                <td><b><i>Eigenschaften</b></i></td>
             </tr>
             <tr><td colspan=3><hr></td></tr>' . $out;
?>
