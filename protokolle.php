<?php
header('Content-type: text/html; charset=iso-8859-1');
session_start();
require_once('db.inc');

if($_GET['type']=='one') show_one_prot($_GET['id'],$_GET['sort']);
if($_GET['type']=='all') show_all_prot();


############################################
function show_all_prot()
{	
	#-----------------------------
	# get a unsorted list from db
	function get_ul_from_db($query)
	{
		#$query =  'SELECT DISTINCT * FROM files';
				#	INNER JOIN files2properties ON files2properties.file_id = files.id
				#	INNER JOIN properties 		ON files2properties.property_id = properties.id';
					
		$l=my_sql_query($query);
		while($row=mysql_fetch_assoc($l))
		 {
				#$files = array('prot_id'=>$row['prot_id'],'timespan'=>$timespan,'fehler'=>$datei_fehler, 'intakt'=>$intakt,'datum'=>$row['datum']);
				#array_push($files_array,$files);
				#$divs[] = '<br><i onclick="show_one_protocol('.$row['prot_id'].')" style="cursor:pointer;">'.date('d-m-Y',$row['datum']).' Fehler:'.$row['fehler'].' Intakt:'.$row['intakt'].'</i>';			
				$click = ' onclick="show_file_info('.$row['id'].')" ';
				$ul_out .= "<li class='clicklist' $click >$row[name]</li>";
		 }
		 return $ul_out;
	 }
					
	switch ($_GET['ul']) 
	{
		case formats : 	
				$ul_out = get_ul_from_db('SELECT DISTINCT * FROM files Limit 2;');
				break;
		default :  
				$ul_out = get_ul_from_db('SELECT DISTINCT * FROM files');				
	}			
				
    $l=my_sql_query('SELECT DISTINCT eigenschaft FROM properties ORDER BY eigenschaft');
    while($row=mysql_fetch_assoc($l))
     {
		$click = ' onclick="show_file_info('.$row['id'].')" ';
		//$suche_out .= "<li class='clicklist' $click >$row[eigenschaft]</li>";
		$suche_option .= "<option> $row[eigenschaft] ";
     }
	
	##die box für die Eigenschaften der Files:
	echo	'<div id=fileinfos class="rund border main-color" style="position:absolute; height:45%; left:50%; top:15px; width:46.5%; overflow:auto;"></div>';
	##die box für die fileslist:
	echo	'<div id=files class="rund border main-color" style="position:absolute; height:45%; left:15px; top:15px; width:46.5%; text-align:left;">   
				<div style="position:absolute; height:10px; left:1px; top:1px; overflow:; text-align:left;"> 
						<b onclick="protokolle()"> &nbsp; All Files </b> <b onclick="protokolle(\'formats\')"> &nbsp; Formats </b> <b> &nbsp; Codecs</b> <br> 
				</div>
				<div id="contentbox1" style="position:absolute; bottom:0px; top:17px; right:4px; overflow:auto;">
					<ul>'.$ul_out.'</ul>					
				</div>
			</div>';				
	## box für die Suche
	echo	'<div id="search" class="rund border main-color" style="position:absolute; height:45%; left:15px; top:50%; width:46.5%; overflow:auto; text-align:left;">   
				<b> &nbsp; Suche </b> <br>
				<select id=search_property> '.$suche_option.' </select>
				<input type="text" id="search_value">
				<input type="button" name="btn_submit" id="search_submit" value="suchen" onclick="suchen()">
			</div>
			<div id="search_result" class="rund border main-color" style="position:absolute; height:45%; left:50%; top:50%; width:46.5%; overflow:auto; text-align:left;">   
			</div>';
}

############################################
function show_one_prot($id,$sort)
{
    $q = 'SELECT files.id AS datei_id, name,properties.wert, properties.eigenschaft  
			FROM files
            INNER JOIN files2properties ON files2properties.file_id = files.id
            INNER JOIN properties 		ON files2properties.property_id = properties.id';
    if($id AND $id != 'undefined') $q .= ' WHERE files.id = '.$id;
    if($sort AND $sort != 'undefined')
    {
        $q .= ' ORDER BY '.$sort; //$sort kann direkt im Funktionskopf eingegeben werden "show_one_protocol('.$row['prot_id'].',crunchtime)"
        if($_SESSION['old_sort'] == $sort) $q.=' DESC ';  //wenn schonmal nach diesem Merkmal sortiert wurde, drehe die Sortierung um
        $_SESSION['old_sort'] = $sort;
    }
    $r=my_sql_query($q);
    while($row=mysql_fetch_assoc($r))
    {
		$dateiname = $row['name'];
        $out .= "<tr onclick='edit_selected_files_list($row[datei_id],this,$row[crunchtime])' class='clicklist'>
                    <td>$row[eigenschaft]</td>
                    <td class='tinyfont'>$row[wert] Sek</td></tr>";
		$i++;
    }

    echo '	<div class="border rund main-color" style="position:absolute; left:10px; top:10px;">
				<b>&nbsp;&nbsp;'.$dateiname.'&nbsp;&nbsp;</b><br>
				<span class="tinyfont"> &nbsp;&nbsp;'.$i.' Erkannte Eigenchaften &nbsp;&nbsp;</span>
			</div>
			<form action="protokolle.php" name="submitliste" method=post>
			<input type="hidden" name="hidden_filelist" id="hidden_filelist" value="0">
			</form >
			<br><br><br>
			<table width="100%">
			<tr height="8px"> <td bgcolor="green"></td>
							<td bgcolor="red"></td>
			</tr>
          '. $out . '</table>';
    
}
?>