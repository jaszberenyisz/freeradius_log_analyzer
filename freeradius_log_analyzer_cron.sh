#!/bin/bash
export term=linux

#########################
# Configuration section #
#########################

# Working directory
WD="/root/tmp/"
# Send the e-mail to this address
send_to="admin@pagisz.hu"

#################################
# Software dependencies section #
#################################
# Mailutils installed?
if [ ! -x "/usr/bin/mail" ]
then
  /usr/bin/apt install -y mailutils
fi
# PHP-CLI installed?
if [ ! -x "/usr/bin/php" ]
then
  /usr/bin/apt install -y php-cli
fi

#########################
# Main software section #
#########################
# Crteate the directpry if not exists
if [ ! -d "$WD" ]; then
  /bin/mkdir -p "$WD"
fi
# Change current working directory
cd "$WD"
# Run the LOG analyzer script
$WD/freeradius_log_analyzer.php
# Get the output file that we want to send (TXT or HTML?)
if [ -f $WD/"freeradius_log_analyzer.htm" ]; then
  fn=$WD/"freeradius_log_analyzer.htm"
else
  fn=$WD/"freeradius_log_analyzer.log"
fi
# Send the e-mail
/bin/cat $fn | /usr/bin/mail -s "Freeradius LOG Analyzer" -a "Content-type: text/html; charset=UTF-8" $send_to
# Delete the HTML file if present
if [ -f $WD/"freeradius_log_analyzer.htm" ]; then
  /bin/rm $WD/"freeradius_log_analyzer.htm"
fi
# End
exit 0

