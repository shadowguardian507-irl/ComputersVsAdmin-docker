<?php
////////////////////////////////////////////////
//              LGPL notice                   //
////////////////////////////////////////////////
/*
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Lesser General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Lesser General Public License for more details.

    You should have received a copy of the GNU Lesser General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

////////////////////////////////////////////////
//               Version info                 //
////////////////////////////////////////////////
/*
see Version.info.php
*/

////////////////////////////////////////////////
//         used libraries/code modules        //
////////////////////////////////////////////////
/*
adLDAP 4.0.4 -- released under GNU LESSER GENERAL PUBLIC LICENSE, Version 2.1 by  http://adldap.sourceforge.net/
*/


////////////////////////////////////////////////
//               Developer Info               //
////////////////////////////////////////////////
/*
Name : James
Alias : Shadow AKA ShadowGauardian507-IRL

Contact : shadow@shadowguardian507-irl.tk
Alternate contact : shadow@etheria-software.tk

Note as an Anti-spam Measure I run graylisting on my mail servers, so new senders email will be held for some time before it arrives in my mail box,
please ensure that the service you are sending from tolerates graylisting on target address (most normal mail systems are perfectly happy with this)

This software is provided WITHOUT any SUPPORT or WARRANTY but bug reports and feature requests are welcome.
*/
?>

<?php

//session init stuff must stay at top of file
session_start();
$conferr = false;
$conferrtext = "";
//config options
if (!file_exists ( "./conf.d/active/settings.conf.php"))
  {
    $conferr = true;
    $conferrtext = "Config file missing please check that ./conf.d/active/settings.conf.php exists<br /> template can be found in ./conf.d/template/settings.conf.php";
  }
  else {
    require_once ('conf.d/active/settings.conf.php');
  }

$hide = false;

if($_GET['hide']==="on")
{
$hide = true;
}
?>
<?php

error_reporting(1);
// theme module loader
foreach (glob("./themes/active/*.theme.php") as $themefilename)
{
    include $themefilename;
}

// component loder
foreach (glob("./components/php/*.enabled.comp.php") as $enabledcompname)
{
    include $enabledcompname;
}

?>
<?php
if($conferr){
  echo "<div id=coreerrorbox>"."<br />Apologies there is an error with a core system component<br/><br />".$conferrtext."<br /><br /></div>";
  die;
  }
?>

<br />

<title><?php echo $config['systemname'] ?></title>
<H1><center><u><?php echo $config['systemname'] ?></u></center></H1>



<div id="SystemStatus">
</style>


html online
<br />
<?php
$initerror=false;
echo "php online";
?>
<br />
<?php
// status box logic + render
require_once('adLDAP/adLDAP.php');
echo "Loaded adLDAP";
?>

<br />
<?php
//trust all ssl certs (just need encoded connection)
putenv('LDAPTLS_REQCERT=never');

$aderror=false;
try {
    $adldap = new adLDAP(array('account_suffix'=>$config['domainAccountSuffix'],'base_dn'=>$config['domainBaseDn'],'domain_controllers'=>$config['domainDCs'],'admin_username'=>$config['domainAccessAccountName'],'admin_password'=>$config['domainAccessAccountPasscode']));
}
catch (adLDAPException $e) {
    $aderrortext=$e;
    $initerror=true;
    $aderror=true;
}

if( !$aderror)
{
echo "Connected to Active Directory";
}
else
{
echo "Active Directory Connection Failed";
}
?>
</div>

<?php
//load dynamic theme for system status
include('./themes/active/systemstatus.theme.m.php');
?>


<?php
//render error box
if ($initerror)
{
echo "<div id=coreerrorbox>";
echo "Apologies there is an error with a core system component<br />";
echo $config['errorreportmsg'].' and quote the following <br /> ('.$config['systemname'].')';
echo '<br />';
if($aderror){echo $aderrortext."<br /><br />";}
echo "</div>";
exit();
}
?>

<?php

$computerlist = array();

$result = $adldap->folder()->listing(NULL,NULL,true,'computer');
foreach ($result as $line)
{
    $computerlist[] = str_replace('$','',$line['samaccountname'][0]);
}
?>

<?php

print '<table align="center">';

$gresult = $adldap->folder()->listing(NULL,adLDAP::ADLDAP_FOLDER,true,'group');

foreach ($gresult as $gline)
{
  if (strpos($gline['samaccountname'][0], $config['adminOUpretag']) !== false )
  {
    $pgrouplist[] = $gline['samaccountname'][0];
  }
}

foreach ($pgrouplist as $pgline)
{
   $comptomatch ="";
   $comptomatch = str_replace($config['adminOUpretag'],'',str_replace('$','',$pgline));

   if ($comptomatch != NULL)
   {

      $compfound = false;

      foreach ($computerlist as $comp)
      {
          if ($comptomatch === $comp)
          {
   	     $compfound = true;
          }
      }


      if ($compfound && !$hide)
      {
	 print "<tr><td>";
	 print '<div id="sec-pc-group-good">';
	 print  str_replace('$','',$pgline) . " -> ";
	 print "paired computer (".$comptomatch.") found <br />";
	 print "</div></tr></td>";
      }
	   
      if(!$compfound)
      {
	 print "<tr><td>";
	 print '<div id="sec-pc-group-bad">';
	 print  str_replace('$','',$pgline) . " -> ";
         print "no paired computer found <br />";
	 print "</div></tr></td>";
      }

   }

}


print "</table>"

?>
