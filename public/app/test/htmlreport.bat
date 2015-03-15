@echo "Vytvareni html vystupu..."
set CONFIG_FILE="%~dp0config\behat.yml"
set BEHAT="%~dp0..\..\bin\behat.bat"
set REPORTS=%~dp0..\..\temp\reports
FOR /F %%A IN ('WMIC OS GET LocalDateTime ^| FINDSTR \.') DO @SET B=%%A
set FILENAME=%B:~0,4%_%B:~4,2%-%B:~6,2%_%B:~8,2%-%B:~10,2%-%B:~12,2%.html

call %BEHAT% --config %CONFIG_FILE% --format=html --out "%REPORTS%\%FILENAME%" %*
start "" "%REPORTS%\%FILENAME%"