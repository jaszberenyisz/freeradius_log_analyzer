#!/usr/bin/php
<?php
/*
Freeradius LOG Analyzer v.1.0
by Szabolcs Jaszberenyi
2018
*/

// --------------------------------------------
// Config section
// --------------------------------------------
  // LOG file location / directory
  $ld="/var/log/freeradius/";
  // Temporary log file
  $tmp="/tmp/radius.log.tmp";
  // LOG (text only) output to this file
  $outlog="/tmp/freeradius_log_analyzer.log";
  // HTML output to this file
  $outhtm="/tmp/freeradius_log_analyzer.htm";
  // AD/NT domain name
  $domain="EXAMPLE";
  // Output: false for TXT and true for HTML
  $html=true;
  // Include the footer?
  $html_footer=true;
  /*
  Detail level:
  Error indication is shown in every case!
  0 - only usernames
  1 - usernames and count of logins
  2 - usernames, count of logins and MAC addresses - order and group by MAC addresses
  */
  $detail_level=2;
  // MAC warning treshold - Warn if user uses more than N pcs unique MAC addresses (good for searching for hacked accounts)
  // 0 = turn off feature
  $mac_warning=5;
  // Check for home directory?
  $home_check=true;
  // Home directory path - only if we are checking them
  $home_path="/home"
  /*
  Exclude from home directory check - for service accounts
  If it's empty, we won't exclude anyone
  Even with one user you have to keep the array format!
  Example: $home_exclude=array("username1","username2");
  */
  $home_exclude="";
  // Language to use
  require_once("lang/hu.php");

// --------------------------------------------
// Functions
// --------------------------------------------

// File Write
function fw($x)
{
  global $html,$f,$h;
// Html
  $xh=str_replace("<!-- EOL -->","",$x);
  if ($html) fwrite($h,$xh."\n");
// Log
  $x=str_replace("<!-- EOL -->","\n",$x);
  if (trim($x)=="<hr />") $x="-----------------------------";
  $x=strip_tags($x);
  if ($x<>"") fwrite($f,$x."\n");
}

// New line
function nl()
{
  global $html,$f,$h;
  fw("  <br />");
}

// --------------------------------------------
// Main program
// Collecting log files
// Process only the lines where we find MAC address in combination with successfull login
// --------------------------------------------
  @unlink($tmp);
  $dir=dir($ld);
  while (false !== ($logfile = $dir->read()))
  {
    if (!in_array($logfile,array(".","..")))
    {
      if (is_file($ld.$logfile))
      {
        if (filemtime($ld.$logfile)>=(time()-(86400*60)))               // not more than 2 months
        {
          print("- Logfile: $logfile\n");
          if (substr($logfile,-3)==".gz") exec('/bin/zcat '.$ld.str_replace(".gz","",$logfile).' | /bin/grep "Login OK:" | /bin/grep " cli " | /bin/grep -v "via TLS tunnel" >> '.$tmp);
          else exec('/bin/cat '.$ld.str_replace(".gz","",$logfile).' | /bin/grep "Login OK:" | /bin/grep " cli " | /bin/grep -v "via TLS tunnel" >> '.$tmp);
        }
      }
    }
  }
  $dir->close();
  unset($dir);
// Read file
  $f=fopen($tmp,"r+");
  $c=fread($f,filesize($tmp));
  fclose($f);
  unset($f);
// Processing log file
  $s=explode("\n",$c);
  foreach ($s as $sor)
  {
    if ($sor)
    {
      // username
      $n=explode(" [",$sor);
      $n=explode("] ",$n["1"]);
      $nev=trim($n["0"]);
      if (substr(strtoupper($nev),0,strlen($domain)+2)==$domain."\\\\") $nev=str_ireplace($domain."\\\\","",$nev);
      // MAC address
      $mac=explode(" ",$sor);           // Avoid PHP Notice, we need a variable
      $mac=strtolower(trim(substr(end($mac),0,-1)));
      $mac=str_replace("-","",$mac);
      $mac=str_replace(":","",$mac);
      if (($nev)&&($mac))
      {
        if (isset($adat[$nev][$mac])) $adat[$nev][$mac]++;
        else $adat[$nev][$mac]=1;
      }
    }
  }
  unlink($tmp);
  unset($c);
  unset($n);
  unset($s);
  unset($sor);
  unset($nev);
  unset($mac);
// --------------------------------------------
// Processing data
// --------------------------------------------
  ksort($adat);
  $f=fopen($outlog,"w+");
  if ($html)
  {
    $h=fopen($outhtm,"w+");
    fw('<html>');
    fw('<body>');
    fw('<div style="background: #ffffff; color: #000000;"><!-- Start body -->');
  }
  fw('  <h2 style="text-shadow: 2px 2px 3px rgba(0,0,0,0.4);">Freeradius LOG Analyzer</h2>');
  fw('  '._generated_at.': '.date("Y.m.d. H:i:s"));
  foreach($adat as $k => $v)
  {
    $hiba="";
    $uc=count($v);
    nl();
    fw('  <hr style="border: 1px solid #000000;" />');
    nl();
    fw("  "._user.": <strong>".$k.'</strong>');
    nl();
    // Check for MAC address count?
    if ((int)$mac_warning>0)
    {
      if ($uc>(int)$mac_warning) $hiba="!"._warning."! ".$k." "._uses_more_mac_addresses_than_treshold." ".(int)$mac_warning." "._pcs;
    }
    // Check for home directory?
    if ($home_check)
    {
      // If not excluded from checking
      if (!in_array($k,$home_exclude))
      {
        // If home directory NOT exists
        if (!file_exists($home_path."/".$k)) $hiba="!"._home_directory_not_found." (".$home_path."/".$k.")";
      }
    }
    // Show warning
    if ($hiba) { fw('    <span style="color: #ff0000; font-weight: 800;">'.$hiba."</span>"); nl(); }
    nl();
    if ($detail_level>0)
    {
      fw("  "._mac_addresses.": ".$uc." "._pcs." "._unique);
      nl();
    }
    if ($detail_level>1)
    {
      $i=1;
      $dstyle="";
      foreach($v as $k2 => $v2)
      {
        if ($i%2==0) $dstyle=' background: #d0d0d0;';
        else $dstyle=' background: #f0f0f0;';
        if (strpos($k2,".")) $type='<div style="color: #c00000;"><strong>'._vpn.'</strong></div>';
        else $type='<div style="color: #0000c0;"><strong>'._wifi.'</strong></div>';
        fw('  <div style="min-height: 24px;'.$dstyle.'">');
        fw('    <div style="float: left; min-width: 100px;">MAC '.$i.':</div>');
        fw('    <div style="float: left; min-width: 170px;">'.$k2.'</div>');
        fw('    <div style="float: left; min-width: 170px; text-align: right;">'.$v2.' '._pcs.' bejelentkezés</div>');
        fw('    <div style="float: left; min-width: 50px; padding-left: 20px;">'.$type.'</div>');
        fw('  </div><!-- EOL -->');
        $i++;
      }
    }
  }
  if ($html)
  {
    if ($html_footer)
    {
      fw('  <!-- Footer -->');
      nl();
      fw('  <div style="background: #000000; color: #d0d0d0; font-size: 0.8em; padding: 5px; text-align: right;">Freeradius LOG Analyzer by Szabolcs Jászberényi (2018)</div>');
    }
    fw('</div><!-- End body -->');
    fw('</body>');
    fw('</html>');
    fflush($h);
    fclose($h);
  }
  fflush($f);
  fclose($f);
// --------------------------------------------
// Print Warnings to STD OUT
// --------------------------------------------
  system('/bin/cat '.$outlog.' | /bin/grep " !"');
?>
