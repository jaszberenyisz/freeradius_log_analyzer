Freeradius LOG Analyzer

Created by Szabolcs Jászberényi



Description:

A small terminal based tool to analyze freeradius log files and send statistic by e-mail automatically.



Use cases:

You can use the script:

- to monitor Enterprise WIFI connections
- to monitor VPN connections



Dependencies:

The script runs in terminal and written in PHP.

The cron scheduler uses mailutils to send the HTML based message.

Debian based installation of dependencies: ```apt install php-cli mailutils```



Installation:

Copy all the program files basically to any directory you would like to.



Configuration:

You need to configure booth files to work correctly:

- freeradius_log_analyzer.php
- freeradius_log_analyzer_cron.sh

You can do it by editting booth files configuration section.



Automated reports:

You can use cron scheduler to run the program and generate the report.

Link the freeradius_log_analyzer_cron.sh file to one of cron's directorys.

For example on a Debian based installation for a weekly report: ```ln -s YOUT_CRON_SCRIPT_FILE /etc/cron.weekly/freeradius_log_analyer.sh```



Licencing:

Software is licenced under GNU General Public Licence v3.0
