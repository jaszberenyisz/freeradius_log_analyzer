<h1>Freeradius LOG Analyzer</h1>
Created by Szabolcs Jászberényi



<h2>Description:</h2>

A small terminal based tool to analyze freeradius log files and send statistic by e-mail automatically.



<h2>Use cases:</h2>

You can use the script:

- to monitor Enterprise WIFI connections
- to monitor VPN connections



<h2>Dependencies:</h2>

The script runs in terminal and written in PHP.

The cron scheduler uses mailutils to send the HTML based message.

Debian based installation of dependencies: ```apt install php-cli mailutils```



<h2>Installation:</h2>

Copy all the program files basically to any directory you would like to.



<h2>Configuration:</h2>

You need to configure booth files to work correctly:

- freeradius_log_analyzer.php
- freeradius_log_analyzer_cron.sh

You can do it by editting booth files configuration section.



<h2>Automated reports:</h2>

You can use cron scheduler to run the program and generate the report.

Link the freeradius_log_analyzer_cron.sh file to one of cron's directorys.

For example on a Debian based installation for a weekly report:
```ln -s YOUT_CRON_SCRIPT_FILE /etc/cron.weekly/freeradius_log_analyer.sh```



<h2>Licencing:</h2>

Software is licenced under GNU General Public Licence v3.0
