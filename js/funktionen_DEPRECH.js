$(function(){ 
$("#content_box").hide();
});

var showflag = 0; //0 = es ist kein Fenster offen
function zeigeergebnisse() {
    $.post('ergebnisse.php', function(data) {
        $('#content').html(data);
        $('#content_box').slideDown('slow');
    });
 }

var selected_files_list = new Array();
var crunchtime = 0;
function edit_selected_files_list(id,zeile,time)
{
    /// Filtert die id aus dem selected_array die schon drin ist, wenn ein Feld zum 2ten mal geklickt wird
    var temp = new Array();
    var isnew = true;
    var filecount = $('#filecount').html();

    if(filecount > 0) $('#btn_dl_liste').show();
    else $('#btn_dl_liste').hide();
    
    for(var i=0; i<selected_files_list.length; i++)
        {
            //alle alten Einträge selected_files_list behalten
            if(selected_files_list[i] != id) temp.push(selected_files_list[i])
            else
                {
                    isnew = false; //Id kommt im array vor, wurde also schonmal geklickt
                    crunchtime = (1*crunchtime) - (1*time);  //Crunchtime entfernte Datei subrahieren
                    $('#crunchtime').html(crunchout(crunchtime));
                    filecount--;
                    $('#filecount').html(filecount);
                }
        }
    
    if(isnew==true) 
        {
            $(zeile).fadeTo("fast",0.33);
            $(zeile).css("background-color","#66CDFF");
            temp.push(id); // wenn die ID wirklich noch nicht vorkam
            crunchtime = (1*crunchtime) + (1*time); //Crunchtime für neue Datei addieren
            $('#crunchtime').html(crunchout(crunchtime));
            filecount++;
            $('#filecount').html(filecount);
        }
    else
        {
            $(zeile).css("background-color","");
            $(zeile).fadeTo("fast",1);
        }

    selected_files_list = temp;
    $('#hidden_filelist').html('<input type="hidden" name="hidden_filelist" id="hidden_filelist" value="'+selected_files_list.join()+'">');
    if(filecount < 0 || crunchtime < 0) alert('Rechenfehler sind aufgetreten, bitte Seite neuladen!');

    //// kleine Funktion die die Zeitausgabe formatiert
    function crunchout(crunchtime)
    {  
       crunchtime = crunchtime/60;
       return crunchtime.toFixed(2);
    }
}

function show_one_protocol()
{
 var id = show_one_protocol.arguments[0];
 var sort = show_one_protocol.arguments[1];

 $.post('protokolle.php?type=one&id='+id+'&sort='+sort, function(data) {
        $('#content').html(data);
        $('#content_box').slideDown('slow');
        $('#btn_dl_liste').hide();
        });
}

function protokolle()
{
 $('#content_box').hide('slow',function(){
     $.post('protokolle.php?type=all', function(data) {
            $('#content').html(data);
            $('#content_box').slideDown('slow');
            });
    });
}

var arr = new Array();
function zeigefehler() {
    $('#content_box').hide('slow',function(){
        $.post('ajax/get_prot.php?type=prot', function(prot_json) {
            var time1 = new Date();
            var time2 = new Date();
            arr = prot_json;
            $('#content').html('<br> Zeitraum wählen: <div id="slider" style="width: 50%; "></div><div style="position:absolute; left:10px;"><br>'+
                                prot_json.erstes+'<br>Ältestes Protokoll</div><div style="position:absolute; right:10px;">'+
                                prot_json.letztes+'<br>Jüngstes Protokoll</div>'+
                                '[<span id="start"></span> - <span id="ende"></span>]'+
                                '<br><br><div id=submit style="font-size: 80%; width:150px;" onclick="fehler_holen()">Fehler anzeigen</div>'+
                                '<br><br><div id=liste style="position:absolute; bottom:15px; top:150px; left:15px; right:15px; overflow:auto;"></div');
            $('#submit').button();
            $('#content_box').slideDown('slow');
            $("#slider").slider({animate: 'slow', range:true, min: 1, max: prot_json.i, values: [0,prot_json.i]});
            $("#slider").slider({change: function(){
                    var slide1 = $("#slider").slider("values",0);
                    var slide2 = $("#slider").slider("values",1);

                    time1.setTime(prot_json[slide1]*1000);
                    time2.setTime(prot_json[slide2]*1000);
                    var mon1 = parseInt(time1.getMonth()) + parseInt(1);
                    var mon2 = parseInt(time2.getMonth()) + parseInt(1);

                    $("#start").html(time1.getDate()+'-'+ mon1 +'-'+time1.getFullYear());
                    $("#ende").html(time2.getDate() +'-'+ mon2 +'-'+time2.getFullYear());
                    arr.start = slide1;
                    arr.ende  = slide2;
                    
                    }
                });
        },"json");
    });    
}

function fehler_holen() {
    $.post('ajax/get_prot.php?type=fehler&start='+arr[arr.start]+'&ende='+arr[arr.ende], function(err_table) {
        $('#liste').html(err_table);
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