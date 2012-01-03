<html>
<head>
<title>Ajax Uploader</title>
<script type="text/javascript">
$(function() {
	// Formular abschicken

	$('#start_dir_scan').click(function() {
		// Formular per $.post() schicken		
		var path = $('#scan_dir').val();
		var limit = $('#scan_limit').val();
		$.post('upload_event.php',{scan_dir:path,scan_limit:limit},function(data) { $('#content').html(data); $('#result').css("text-align","left"); } )
		$('#content').css("align","left");		
	});		
	
});
</script>
</head>
<body>
<div align="center">
	
	<br><br><b>Verzeichnis einlesen:</b><br> <br>
        <input type="text" 	 name="scan_dir" 		id="scan_dir"		value="\\datenserver\htdocs\mediainfodb\protokolle\"  size=50/><br>
		<input type="text" 	 name="scan_limit" 		id="scan_limit"		value="10"  size=3 maxlength=3/> Protokolle maximal einlesen.<br>
		<input type="button" name="start_dir_scan" 	id="start_dir_scan" value="starte scan" />
	
</div>
</body>
</html>