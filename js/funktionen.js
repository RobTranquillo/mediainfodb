$(function(){ 
	//$("#content_box").hide();
}); //ENDE $(function(){ 


// Funtion die Ausgeben von post-anfragen 
// function post2window(string,target,[prePost],[postPost])
function post2window(poststring,target)
{
	$.post(poststring,function(data){
		if(post2window.arguments[2] != "undefined") eval(post2window.arguments[2])
		$(target).html(data);
		if(post2window.arguments[3] != "undefined") eval(post2window.arguments[3])
		});
}

function db_sub_values(flag)
{
	if(flag == "allfiles") post2window("protokolle.php?type=all","contentbox1");
	if(flag == "formats") post2window("protokolle.php?type=all&ul=formats","contentbox1");
}

function suchen()
{
	//suche starten bei buttonklick	
	var property = $('#search_property').val();
	var value = $('#search_value').val();
	$('#search_result').html('data');
	$.post('suche.php', { property:property , value:value } ,function(data) { $('#search_result').html(data); } );	
}

function show_file_info()
{
 var id = show_file_info.arguments[0];
 var sort = show_file_info.arguments[1];

 $.post('protokolle.php?type=one&id='+id+'&sort='+sort, function(data) {
        $('#fileinfos').html(data);
        });
}

function protokolle(ul)
{
var ul_get_string = ''
 $('#content_box').hide('slow',function(){
	if(typeof ul != "undefined") ul_get_string = '&ul='+ul;
     $.post('protokolle.php?type=all'+ul_get_string, function(data) {
            $('#content').html(data);
            $('#content_box').slideDown('slow');
            });
    });
}

function read_new_prot() {
 $('#content_box').hide('slow',function(){
     $.post('uploader.php', function(data) {
            $('#content').html(data);
            $('#content_box').slideDown('slow');
            });
    });
}

function statistik() {
 $('#content_box').hide('slow',function(){
     $.post('statistik.php', function(data) {
            $('#content').html(data);
            $('#content_box').slideDown('slow');
            });
    });
}