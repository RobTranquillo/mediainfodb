<?php
header('Content-type: text/html; charset=iso-8859-1');
require_once('db.inc');

 $q = "SELECT files.id,name FROM files
			INNER JOIN files2properties ON file_id = files.id
			INNER JOIN properties ON  properties.id = property_id
			WHERE properties.eigenschaft = '$_POST[property]' AND properties.wert LIKE '%$_POST[value]%';";

$l = my_sql_query($q);
while($r = mysql_fetch_assoc($l))
{
		$click = ' onclick="show_file_info('.$r['id'].')" ';
		$out .= "<li class='clicklist' $click> $r[name] </li>";
}

echo "<ul> $out </ul>";
?>