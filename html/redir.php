<?php
if (empty($_SERVER["HTTP_HOST"])) {
  exit;
}

# require "libs/xname.php";
include "libs/config.php";
include "libs/db.php";
$config = new Config();
$db = new Db();
$site = $_SERVER["HTTP_HOST"];
$host = explode(".", $site);
$hostn = mysql_real_escape_string($host[0]);
array_shift($host);
$siten = mysql_real_escape_string(implode(".", $host));
$site = mysql_real_escape_string($site);

$query = "SELECT val2,val4 
FROM dns_zone z 
JOIN dns_record r ON r.zoneid=z.id 
WHERE type='WWW' 
  AND ( val5 IS NULL OR val5 = '' )
  AND (   (zone='$siten' AND (val1='$hostn' OR val1='$hostn.$siten.')) 
       OR (zone='$hostn.$siten' AND val1='$hostn.$siten.')
       OR (concat(val1,'.',zone)='$site') 
       OR (val1='@' AND zone='$hostn.$siten')
       OR (val1='*' AND zone='$siten')
      )";
$res = $db->query($query);
$line = $db->fetch_row($res);
if ($line===FALSE)
{
  Header("HTTP/1.0 404 Not found");
  include("empty.php");
  exit;
}
else
{
  if (stristr($line[0], "http://")==FALSE && stristr($line[0], "https://")==FALSE) {
    $line[0] = "http://" . $line[0];
  }
  $req = $line[0];
  if ($_SERVER["REQUEST_URI"] != "/") $req .= $_SERVER["REQUEST_URI"];
  $redirect = 0;
  if (@$line[1] == "R") $redirect = 302;
  if (@$line[1] == "r") $redirect = 301;
  if ($redirect) {
    if (0) {
    echo "<!-- $site: $hostn $siten ";
    var_dump($siten);
    var_dump($query);
    echo "-->";
    } else
     Header("Location: $req", TRUE, $redirect);
  } else {
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
<html>
<head>
<title><?=$site?></title>
</head>
<frameset>
  <frame src="<?=$req?>" frameborder="0" name="mainfreednsframe">
</frameset>
<noframes>
<body>
<a href="<?=$req?>"><?=$req?></a>
</body>
</noframes>
</html>
<? 
  }
}
?>
