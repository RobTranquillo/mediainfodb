<?php
/* 
 * Datei zum auslesen aller Protokolle für den Slider in "FehlerAnzeigen"
 * Datei gibt ein JSON Array zurück!
 * ARR: []
 */
header('Content-type: text/html; charset=iso-8859-1');
require_once('../db.inc');
##################################################
//alle Protokolle (zum Bereich auswählen) suchen
if($_GET[type]=='prot')
{
    $datum_arr[erstes] = '';  #erstes Protokolle
    $datum_arr[letztes] = ''; #letztes Prot
    $datum_arr[i] = 0;        #Anzahl

    $l=my_sql_query("SELECT DISTINCT datum FROM protokolldatei ORDER BY datum");
    while($row=mysql_fetch_assoc($l))
     {
        $datum_arr[i]++;
        if($datum_arr[erstes]=='') $datum_arr[erstes] = date('d-m-Y',$row[datum]);
        $datum_arr[letztes]=date('d-m-Y',$row[datum]);
        $datum_arr[$datum_arr[i]] = $row[datum]; ### Array aller Protokolle
     }
    echo json_encode($datum_arr);
}

##################################################
//Fehler zum ausgewählten bereich suchen
if($_GET[type]=='fehler')
{
    $table_arr=array();
    $i=0;
    if($_GET[start]=='undefined') unset($_GET[start]);
    if($_GET[ende]=='undefined') unset($_GET[ende]);
    if($_GET[start] AND $_GET[ende]) $where = " WHERE datum >= $_GET[start] AND datum <= $_GET[ende] ";
    $q="SELECT DISTINCT fehler.datei_id,videodatei.name,datum,fehlerart,fehler FROM fehler
        INNER JOIN ergebnisse     ON ergebnisse.datei_id = fehler.datei_id
        INNER JOIN protokolldatei ON ergebnisse.protokolldatei = protokolldatei.id
        INNER JOIN videodatei     ON videodatei.id = fehler.datei_id
        $where
        ORDER BY videodatei.name";
    $l=my_sql_query($q);
    while($row=mysql_fetch_assoc($l))
     {
        if($table_arr[$i][name] != $row[name] OR ($table_arr[$i][name] == $row[name] AND $table_arr[$i][datum] != $row[datum])) $i++;
        $table_arr[$i][name]   = $row[name];
        if($row[fehlerart]==1) $table_arr[$i][HDfehler]=$row[fehler];
        if($row[fehlerart]==2) $table_arr[$i][FRfehler]=$row[fehler];
        $table_arr[$i][datum]  = date('d-m-Y',$row[datum]);
     }

foreach($table_arr AS $row)
{
  $table .=  "<tr><td>$row[name]</td><td>$row[datum]</td><td>$row[HDfehler]</td><td>$row[FRfehler]</td></tr>";
}
#echo json_encode($arr);
echo '<table border=0 style="position:relative; botton:5px; top:5px; left:15px; right:5px; width:700px; height:250px;">
        <tr><td><b>Videoname</b></td><td><b>Datum</b></td><td><b>Headerfehler</b></td><td><b>Framefehler</b></td></tr>
        '.$table .'</table>';
    # ."<br>$q";
}
?>
