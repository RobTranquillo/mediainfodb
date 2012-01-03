<div id="result">
<?PHP
  ####  Das mit dem <div id='result'></div> Aussenrum ist zwar ein bisschen bescheuert,
  ####  aber notwendig damit alle Ausgaben der Datei im besagten Tag landen und von der
  ####  Ajax-Hochladenfunktion gefunden werden

  include_once('db.inc');
  #if(save_protokoll()) protokoll_auswerten();
 
  if($_POST['scan_dir']) scan_dir();  //starte den EinleseVorgang
  
  #make_srv_dir_list();


################################################
//SRV Dir list
$out = '';
$found_files =0;
function make_srv_dir_list()
{
	global $out,$found_files;
	function ReadDirRekursiv($dir)
	{
		global $out,$found_files;
		$start = time();
		if(false === is_dir($dir)) return false;
		
		$handle = opendir($dir);
		
		if(false === $handle) return false;
				
		while(false !== ($file = readdir($handle)))
		{
			if('.' == $file || '..' == $file) continue;
		
			if(is_dir($dir .'\\'. $file)) {
				ReadDirRekursiv($dir.$file.'/');
				if($start + 10 < time()) return false;
				continue;				
			}
			$out .= "\r\nMediaInfo.exe  \"$dir$file\" > \"c:\\xampp\\htdocs\\mediainfodb\\protokolle\\".$found_files."_$file.txt\"";			
			$found_files++;
		}
		closedir($handle);		
		return true;
	}
	
	$dir = "g:\\#Video\\AVI\\";	
	ReadDirRekursiv($dir);
	
	echo $found_files . "Dateien gefunden! "; # $out;
	
	if($handle = fopen('c:\\mediainfo_batch.bat', "w"))
	{
		$out = 	"pause \r\n".
				"c: \r\n".
				"cd \"c:\\xampp\\htdocs\\mediainfodb\\MediaInfo_CLI\\\" \r\n"
				.$out.
				"\r\n pause \r\n";
		fwrite($handle,$out);
		fclose($handle);
	}	
}

################################################
function scan_dir()
{	
	$prot_count = 1;
	
	$l = my_sql_query("SELECT path FROM files");
	while($r = mysql_fetch_assoc($l)) $all_paths .= $r['path'].' ';	
	
	if($handle = opendir($_POST['scan_dir']))
	{
		//maximal soviel Protokolle einlesen
		if($_POST['scan_limit'] > 0 AND $_POST['scan_limit'] < 1000) $limit = $_POST['scan_limit'];
		else $limit = 10;
	
	
		while(false !== ($file = readdir($handle)) AND $prot_count <= $limit) 
		{
			if($file != '.' AND $file != '..') 
				{
					echo '<br><b>' . $prot_count++ . '. protocol found in directory: ' . $file . '</b>' ;
					$h_file = fopen($_POST['scan_dir'].'/'.$file,'r');
					for($x=0;$x<3;$x++) //die ersten 3 Zeilen des Protokolls lesen um die VideoDatei zu ermitteln 
					{
						$line = fgets($h_file); 
						if(substr_count($line,'Complete name')) 
						{
							echo '<br> - found video in protocol: ' . $video_full_path = trim(substr($line, 34));
							if(substr_count($all_paths,$video_full_path)) 
							{
								echo '<br> - <i>Abbruch, Video schon in DB vorhanden!</i>';
								$prot_count--;
							}
							else protokoll_auswerten($_POST['scan_dir'].'/'.$file);
						}
					}
				}
		}
	}
}  
  
################################################
function protokoll_auswerten($protocol_path)
{
### alle Parameter auflisten
### ABER zum Parameter muss dann in der verknüpfungstabelle noch erscheinen in welchem Bereich der Parameter aufgetreten ist
### Mögliche Bereiche: General(=1), Video(=2), Audio(=3)
	$suchparameter = array('Format','Format/Info','Format version','File size','Duration','Overall bit rate','Writing library',
							'Bit rate','Width','Height','Display aspect ratio','Frame rate','Color space','Chroma subsampling','Bit depth','Compression mode',
							'Bits/(Pixel*Frame)','Stream size','Channel(s)','Sampling rate','Bit depth','Interleave, duration','Interleave, preload duration',
							'Codec ID','Codec ID/Hint','Codec ID/Info','Format profile','Format settings','Bit rate mode','Maximum bit rate','Standard',
							'Scan type','Scan order','Compression mode','Delay relative to video');
	$parm_stack = array(); //dateiinfos
	$section = 0;  //in welchen Teil der Datei man sich befindet
	$bsl = mysql_escape_string('\\');
	$parms_count = 0;
	
	$zeilenweise = file($protocol_path);
	
	foreach($zeilenweise AS $line)
	{
		$line_field = substr($line,0,33); //mediainfo teilt immer beim 34ten Zeichen mit einem Doppelpunkt
		$line_value = substr($line,34);
		
		$line_field = mysql_escape_string(trim($line_field)); //strings reinigen
		$line_value = mysql_escape_string(trim($line_value));
		
		if($line_field == 'General')	$section = 1;
		if($line_field == 'Video') 		$section = 2;
		if($line_field == 'Audio') 		$section = 3;

		if(!$file_id AND $line_field == 'Complete name') 
		{
			$name = substr($line_value,strrpos($line_value,$bsl)+2);			
			my_sql_query("INSERT INTO files (name,path) VALUES ('$name','$line_value')");
			$file_id = mysql_insert_id();
		}
		
		if(in_array($line_field,$suchparameter)) {  // wenn ein gesuchtes Feld in der Zeile liegt		
			$prop_id = check_and_insert_sql('properties',array('eigenschaft'=>$line_field, 'wert' => $line_value));		
			my_sql_query("INSERT INTO files2properties (file_id,property_id,section) VALUES ($file_id, $prop_id, $section)");
			$parms_count++;
		}
	}
	
	echo '<br>'. $parms_count . ' - VideoParameter gefunden und in die DB eingetragen.';
}

################################################
# Legt Sicherheitskopie des Protokolls an
function save_protokoll()
{
 $err = false;
 if(!empty($_FILES['file']['name']))
    {
     if(file_exists('protokolle/'.$_FILES['file']['name']))
     {
       echo '<br><b>Protokollverarbeitung abgebrochen!</b> Datei dieses namens ist schon vorhanden <br><br> <a href="protokolle/">Protokoll Ordner anzeigen</a>';
       if(filesize('protokolle/'.$_FILES['file']['name'])!= $_FILES['file']['size'])
           echo '<br>Die bereits vorhandene Datei unterschiedet sich von der eben hochgeladenen. Wie wollen sie weiter vorgehen?
                 <br> [Datei trotzdem hochladen (schon vorhandenen Ergebnisse werden nicht überschrieben)]  -  [Abbrechen]';
     }
     else
     {
      if(@copy($_FILES['file']['tmp_name'],'protokolle/'.$_FILES['file']['name'] ) )
         {
            echo '<br><b>Protokollkopie wurde abgelegt</b> '.$_FILES['file']['size'].'Byte';
            $err = true; ### im Erfolgsfall gibt Funktion true zurück
         }
     }
    }
    return $err;
}

################################################
function datei_auswerten_depeciated()
{
    #print_r($_FILES);
    $zeilenweise = datei_einlesen();

    ### Die ganze Funktion liest die Prot-Datei von hinten beginnen durch bis Testergebnisseabschnitte beginnt/endet
    ### Fehlerhafte und einwandfreie Dateien werden gemerkt
    $i=count($zeilenweise); //$i = letzte Zeile
    $arr_datei = array();
    $bereichflag='1-Ergebnisse lesen';  ### Wo man sich im Dokument grade befindet
    $tool_version = explode(':',$zeilenweise[0]);
    $tool_version = explode(';',$tool_version[1]);

    ##### Auffüllen der Protokolldatei Informationen
    $arr['toolversion'] = trim($tool_version[0]);
    $time_r = explode('.',trim($tool_version[1]));
    $arr['datum']       = mktime(0,0,0,$time_r[1], $time_r[0], $time_r[2]);
    $arr['name']        = $_FILES['file']['name'];
    #$arr['toleranzparameter'] = $testinfos['TolParm']; ## wird weiter unten erst eingefügt
    $protokoll_id       = check_and_insert_sql('protokolldatei',$arr);   ##check&Insert ist hier eigentlich nicht nötig, da ein Protokoll nicht zweimal hochgeladen wird

    while($i>0)
    {
      ##### Bereichesteuerung (wo im Dokument man ist)
      if('----- TestErgebnisse' == substr($zeilenweise[$i],0,20)) { $bereichflag='2-Testoptionen lesen'; $i--; }
      if('----- TestOptionen -' == substr($zeilenweise[$i],0,20)) { $bereichflag='3-DLL-Set lesen';      $i--; }
      if('----- Auflistung der' == substr($zeilenweise[$i],0,20)) { $bereichflag='4-Pfad einlesen';      $i--; }


    #####  Testergebnisse einlesen
    #----- TestErgebnisse ----------------------------------------------
    #Nr   | Header    | Frames                       | DateiName
    #1    | OK        | CRASH(1.Set) Frame: 734/3011 | Cicle_MAGIX_DEMO.mpg  | 240x180*25fps*1.78*30MB*0:1:35

    if($bereichflag=='1-Ergebnisse lesen')
      {
        $arr = explode('|',$zeilenweise[$i]);
        if(preg_match( '(^[0-9]{1,4})', $arr[0])) ## wenn die Zeile mit einer 4 stelligen Ziffer beginnt (0-9999)
          {
            unset($info_arr);
            $videoinfos = explode('*',$arr[4]);
            $videoinfos[1] = explode('fps',$videoinfos[1]); ## Maßeinheit fps am Ende entfernen
            $videoinfos[3] = explode('MB',$videoinfos[3]);  ## Maßeinheit MB am Ende entfernen
            $videoinfos[4] = explode(':',$videoinfos[4]);   ## crunchtime extrahieren
            $videoinfos[4] = ($videoinfos[4][0])*3600 + ($videoinfos[4][1])*60 + $videoinfos[4][2]; ## crunchtime in Sekunden umrechnen

            $info_arr['nr']          = mysql_escape_string(trim($arr[0]));
            $info_arr['name']        = mysql_escape_string(trim($arr[3]));
            $info_arr['suffix']      = mysql_escape_string(trim(substr(strrchr($arr[3],'.'), 1)));
            $info_arr['format']      = mysql_escape_string(trim($videoinfos[0]));
            $info_arr['fps']         = mysql_escape_string(trim($videoinfos[1][0]));
            $info_arr['aspectratio'] = mysql_escape_string(trim($videoinfos[2]));
            $info_arr['groesse']     = mysql_escape_string(trim($videoinfos[3][0]));
            $info_arr['crunchtime']  = mysql_escape_string(trim($videoinfos[4]));
            if(trim($arr[1]) == 'OK' AND trim($arr[2]) == 'OK')
              {
                 array_push($arr_datei,$info_arr); #Headervergleich und Framevergleich ist OK
               }

            if(trim($arr[1]) != 'OK') #Headervergleich FEHLER
              {
                 $info_arr['header_fehler']   = trim($arr[1]);
                 array_push($arr_datei,$info_arr);
              }

            if(trim($arr[2]) != 'OK') #Framevergleich FEHLER
            {
                 $info_arr['frame_fehler']   = trim($arr[2]);
                 array_push($arr_datei,$info_arr);
            }
          }
      }

    if($bereichflag=='2-Testoptionen lesen')
      {
        if(substr($zeilenweise[$i],0,17) == 'Toleranzparameter') {
                $TolParm = explode(':',$zeilenweise[$i]);
                my_sql_query("UPDATE protokolldatei SET toleranzparameter = '$TolParm[1]' WHERE id = $protokoll_id");
                }
      }



    ##### nach dem einlesen der Ergbnisse und des Dateinamen, den zugehörigen Pfad einlesen
    #----- Auflistung der zu testenden MPEG-Dateien -----------------
    #1:   \\datenserver\Testmaterial\#Video\MPEG 2\MPEG 2\Cicle_MAGIX_DEMO.mpg
    #2:   \\datenserver\Testmaterial\#Video\MPEG 2\MPEG 2\Top Field_4zu3_Pal_Mpeg2_RTL.mpg
    #3:   \\datenserver\Testmaterial\#Video\MPEG 2\MPEG 2\2008050710005299.MPG
    if($bereichflag=='4-Pfad einlesen')
    {  #echo '<br> 4-Pfad einlesen ... zeilenweise:'.$i;
        $arr = explode(":",$zeilenweise[$i]);
        if (preg_match( '(^[0-9]{1,4})', $arr[0])) ## wenn die Zeile mit einer Ziffer beginnt (0-9999)
        {
            for($x=0; $x<count($arr_datei);$x++) #durchfahre das FileArray hänge den pfad ans array
            {
                if($arr_datei[$x]['nr']  == trim($arr[0]))
                {
                    $arr_datei[$x]['pfad']  = mysql_escape_string(trim($arr[1]));
                    $arr_datei[$x]['name']  = mysql_escape_string(trim(substr(strrchr($arr[1],'\\'),1))); #erstzt den Kurznamen duchden kompletten Namen
                }
            }
        }
    }

    ##### verwendete DLL Set ermitteln
    if($bereichflag=='3-DLL-Set lesen')
    {
        for($ii=0;$ii<4;$ii++)
        {
            $i--;
            #Strings formatieren und dann ins DLL-Array kopieren
            $arr = explode(',',$zeilenweise[$i]);
            $arr[2] = substr($arr[2],13);
            if($arr['0'] == 'MXMPEG2.dll')
                {
                  $dll[$ii]['mx2']['datum'] = trim($arr[1]);
                  $dll[$ii]['mx2']['vers']  = trim($arr[2]);
                }
            if($arr['0'] == 'mpeg2.dll')
                {
                  $dll[$ii]['mp2']['datum'] = trim($arr[1]);
                  $dll[$ii]['mp2']['vers']  = trim($arr[2]);
                }
           if($arr['0'] == 'mxavireader.dll')
                {
                  $dll[$ii]['avi']['datum'] = trim($arr[1]);
                  $dll[$ii]['avi']['vers']  = trim($arr[2]);
                  if(count($dll)==2) break; ## beendet vorzeitig die Schleife da nur 2 mxavireader.dll da sind nicht 4 wie bei mpeg
                }
        }

        # eintragen der DLLs in die DB oder ermitteln der ID in der DB wenn schon vorhanden
        $arr_used_dlls = array();
        foreach($dll AS $dll_row)
        {
            $used_dll = '';
            if(key($dll_row)== 'mp2') { $dll_row = $dll_row['mp2']; $used_dll = 'mpeg2.dll'; }
            if(key($dll_row)== 'mx2') { $dll_row = $dll_row['mx2']; $used_dll = 'MXMPEG2.dll'; }
            if(key($dll_row)== 'avi') { $dll_row = $dll_row['avi']; $used_dll = 'mxavireader.dll'; }

            if($used_dll)
            {
                $l=my_sql_query("SELECT id FROM dll_set WHERE dll_name = '$used_dll' AND dll_datum = '".$dll_row['datum']."' AND dll_version = '".$dll_row['vers']."';");
                $row=mysql_fetch_assoc($l);
                if($row[id]) array_push($arr_used_dlls,$row[id]);

                else {
                    my_sql_query("INSERT INTO dll_set (dll_name,dll_datum,dll_version) VALUES ('$used_dll','".$dll_row['datum']."','".$dll_row['vers']."');");
                    $newid=mysql_insert_id();
                    array_push($arr_used_dlls,$newid);
                }
            }
        }
       echo '<br /> DLLs wurden ermittelt';
    }
    $i--;
 }
push_file_infos_in_db($arr_datei,$arr_used_dlls,$testinfos,$protokoll_id);
}

################################################
function push_file_infos_in_db($arr_datei,$arr_used_dlls,$testinfos,$protokoll_id)
 {
    #echo '<br>$dateiArray: '  ;print_r($arr_datei);
    ### Alle Videos eintragen ($arrd_datei)
    ### Die SELECTS vor dem INSERT schützen die DB vor doppelten Einträgen!
    #   [1] => Array ( [nr] => 3  [name] => Top Field_4zu3_Pal_Mpeg2_RTL.mpg [fps] => 25  [groesse] => 12334
    #                  [aspectratio] => 1.13 [crunchtime] => 7835 [format] => 240x180 [pfad] =>    \\datenserver\Testmaterial\#Video\MPEG 2\MPEG 2\2008050710005299.MPG)
    # Für Fehlerhafte Dateien werden die Fehler erzeugt und ggf in Ergebnisse eingetragen
    # DLL Set Verknüpfung wird erstellt
    $err_count = $file_count = 0;

    foreach($arr_datei AS $file)
    {
        unset($arr);
        ##### Trage DATEI & PFAD in die DB
        $arr['groesse']  = $file['groesse'];
        $arr['name']     = $file['name'];
        $arr['pfad']     = $file['pfad'];
        $datei_id = check_and_insert_sql('videodatei',$arr,2);
        if($datei_id) $file_count++;
        $err_id = 0;

        ##### wenn Datei HEADER-fehler hat, trage den Fehler in die DB
        if(isset($file['header_fehler']))
        {
            unset($arr);
            $arr['datei_id'] = $datei_id;
            $arr['fehler']   = $file['header_fehler'];
            $arr['fehlerart']= 1; ##header Fehler
            $err_id = check_and_insert_sql('fehler',$arr);
            $err_count++;
        }

        ##### wenn Datei FRAME-fehler hat, trage den Fehler in die DB
        if(isset($file['frame_fehler']))
        {
            unset($arr);
            $arr['datei_id'] = $datei_id;
            $arr['fehler']   = $file['frame_fehler'];
            $arr['fehlerart']= 2; ##frame Fehler
            $err_id = check_and_insert_sql('fehler',$arr);
            $err_count++;
        }

        ##### Eintragen der ERGEBNISSE in die DB
        unset($arr);
        $arr['datei_id']             = $datei_id;
        if($err_id) $arr['ergebniss']= 1; ###Datei hat Fehler
        else $arr['ergebniss']       = 0;        ###Datei hat keine Fehler
        $arr['crunchtime']           = $file['crunchtime'];
        $arr['protokolldatei']       = $protokoll_id;
        $ergebnis_id = check_and_insert_sql('ergebnisse',$arr);

        ##### verwendete DLLs mit den Ergebnissen verknüpfen
        unset($arr);
        $arr['ergebnis_id'] = $ergebnis_id;
        foreach($arr_used_dlls AS $used_dll)
        {
            $arr['dll_set'] = $used_dll;
            check_and_insert_sql('erg2dllset',$arr);
        }

        ##### Eigenschaften eintragen ($arr) und verknüpfen ($lnk_arr)
        unset($arr);
        $arr['eigenschaft'] = 'format';
        $arr['wert']        = $file['format'];
        $eigenschaft_id     = check_and_insert_sql('videoeigenschaften',$arr);
            $lnk_arr['videodatei']      = $datei_id;
            $lnk_arr['videoeigenschaft']= $eigenschaft_id;
            check_and_insert_sql('datei2eigenschaften',$lnk_arr);

        $arr['eigenschaft'] = 'suffix';
        $arr['wert']        = $file['suffix'];
        $eigenschaft_id     = check_and_insert_sql('videoeigenschaften',$arr);
            $lnk_arr['videoeigenschaft']= $eigenschaft_id;
            check_and_insert_sql('datei2eigenschaften',$lnk_arr);

        $arr['eigenschaft'] = 'fps';
        $arr['wert']        = $file['fps'];
        $eigenschaft_id     = check_and_insert_sql('videoeigenschaften',$arr);
            $lnk_arr['videoeigenschaft']= $eigenschaft_id;
            check_and_insert_sql('datei2eigenschaften',$lnk_arr);

        $arr['eigenschaft'] = 'aspectratio';
        $arr['wert']        = $file['aspectratio'];
        $eigenschaft_id     = check_and_insert_sql('videoeigenschaften',$arr);
            $lnk_arr['videoeigenschaft']= $eigenschaft_id;
            check_and_insert_sql('datei2eigenschaften',$lnk_arr);
    }
    echo '<br /> <br> <b>'.$file_count.' </b>Dateien im Protokoll erkannt.
         <b>'.$err_count.' </b>detektierte Fehler im Protokoll erkannt.';
 }
?>
</div>