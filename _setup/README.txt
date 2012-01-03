********************************************************************************

				MEDIAINFODB
				  V0.0.1

03.01.2012
********************************************************************************

This program and source is under GPL Licence.
You can use it private and non profit. On change the sourcecode or in case of 
use in other software projects, please contact me: EADG@wolke7.net


To Install mediainfodb:
1) you just have to download and unzip the archive. (or copy the files direktly from github)
2) ..direktly in your webservers directory (e.g. Apache2: htdocs)
3) create all needed tables in your database, use: "mediainfodb.sql.zip"
4) open "db.inc" and enter your database connection and copy the file in mediainfodb directory
4) start your webserver and browse to /mediainfodb .. your done!



To Insert New MediaInfo To Your DB:
1) download and install the great prog mediainfo (only the CLI Version!) from http://mediainfo.sourceforge.net 
2) unzip mediainfo(CLI) in a directory under mediainfodb
3) to get the mediainfo out of a mediafile and use it with mediainfodb just run the MediaInfo main executable with you mediafile and pipe it to an textfile.
	3.1) Windows: MediaInfo.exe "c:\Videos\hollydays2010.avi" > "c:\mediainfodb\protocolls\hollydays2010.avi.txt"
	3.2) Unix like: MediaInfo "/usr/var/videos/hollydays2010.mp4" > "/usr/var/mediainfodb/protocolls/hollydays2010.mp4.txt"	
4) run mediainfodb and klick "Files einlesen"! Now enter the directory where your protocolls are located, hit the button "start scan" .. youre done!

Have fun with mediainfodb! 


For Questions (english/deutsch): EADG@wolke7.net  


