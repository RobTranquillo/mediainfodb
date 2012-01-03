<?php
$DB_HOST = "localhost";
$DB_USER = "";
$DB_PW   = "";
$DB_NAME   = "mediainfodb";

 mysql_connect($DB_HOST,$DB_USER,$DB_PW);
 mysql_select_db($DB_NAME) or die("Auswahl der Datenbank Fehlgeschlagen".mysql_error());

########################################################################################
function my_sql_query($query)  ## eineigene SQL funktion die sich um die gesammte Kommunikation kümmert 
{  
	$result = mysql_query($query);
	if($er=mysql_error()) echo '<br><b>'.$query.'</b><br>'.$er;
	return $result;
}

########################################################################################
# --function check_and_insert_sql($tab,$ins_arr[,$look_colum_max])---
# Funktion sucht erst nach den übergeben Parametern in der Datenbank, wenn sie noch nicht enthalten sind fügt sie diese ein
# Funktion gibt Id des alten oder neuen Datensatzes zurück
# Funct-ARGs: 0:betroffene Tabelle, 1: Array-> [spalte]=>['inhalt'], 2: Spalte bis zu der der Vergleich durchgeführt wird (um DB-Zeit zu sparen)
function check_and_insert_sql()
{
	$look_colum_max=false; 										## schonmal false initialisieren für Abfrage
	$tab=func_get_arg(0);      									## verw. Tabelle
	$ins_arr=func_get_arg(1);  									## Array [Spalte]=>[zeilenwert]
	if(func_num_args()==3) $look_colum_max=func_get_arg(2); 	## falls ein 3ter Parameter übergeben wurde

	reset($ins_arr);
	$i=1;
	while(count($ins_arr))
	{
		$spalte = key($ins_arr);
		$inhalt = current($ins_arr);
		array_shift($ins_arr);

		#### SELECT bauen
		if($look_colum_max==false OR ($look_colum_max!=false AND $look_colum_max >= $i))
		 {
		  if (isset($spalten_und_inhalte_select)) $spalten_und_inhalte_select .= ' AND ';
		  $spalten_und_inhalte_select .= " $spalte = '$inhalt' ";
		 }

		### INSERT bauen
		if (isset($inhalte_insert)) $inhalte_insert .= ',';
		$inhalte_insert .= "'$inhalt'";
		if (isset($spalten_insert)) $spalten_insert .= ',';
		$spalten_insert .= $spalte;
		$i++;
	}

	#echo "<br>SELECT id FROM $tab WHERE $spalten_und_inhalte_select";
	$l=my_sql_query("SELECT id FROM $tab WHERE $spalten_und_inhalte_select");
	$row=mysql_fetch_assoc($l);

	if ($row['id']) return $row['id'];
	else
	   {
		my_sql_query("INSERT INTO $tab ($spalten_insert) VALUES ($inhalte_insert)");
		return mysql_insert_id();
		}
}

?>