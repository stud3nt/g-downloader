@echo off

echo Creating websocket serwer service...

sc create "G-Downloader websocket server" start= demand displayname= "G-Downloader server" binpath= C:\xampp\htdocs\g-downloader\run_websocket.bat

pause >nul