<?
	# $Id: watch.php,v 1.1.2.4 2002-02-09 19:42:42 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require("./include/common.php");
	require("./include/freshports.php");
	require("./include/databaselogin.php");
	require("./include/getvalues.php");


// if we don't know who they are, we'll make sure they login first
if (!$visitor) {
        header("Location: login.php?origin=" . $PHP_SELF);  /* Redirect browser to PHP web site */
        exit;  /* Make sure that code below does not get executed when we redirect. */
}

	freshports_Start("your watched ports",
					"freshports - new ports, applications",
					"FreeBSD, index, applications, ports");
?>

<table width="<? echo $TableWidth ?>" border="0" ALIGN="center">

<tr><td valign="top" width="100%">
<table width="100%" border="0">
<tr>
    <td colspan="5" bgcolor="#AD0040" height="30"><font color="#FFFFFF" size="+1"><? echo $FreshPortsTitle; ?> - your watch list</font></td>
  </tr>
<tr><td>
This page lists the ports which are on your watch list. To modify the contents of this list, click on 
<a href="watch-categories.php">watch list - Categories</a> at the right.
</td></tr>
<script language="php">

$DESC_URL = "ftp://ftp.freebsd.org/pub/FreeBSD/branches/-current/ports";

if ($UserID == '') {
   echo '<tr><td>';
   echo 'You must be logged in order to view your watch lists.';
   echo '</td></tr>';
} else {


$WatchID = freshports_MainWatchID($UserID, $db);

// make sure the value for $sort is valid

echo "<tr><td>\n";

switch ($sort) {
/* sorting by port is disabled. Doesn't make sense to do this
   case "port":
      $sort = "version, updated desc";
      $cache_file .= ".port";
      break;
*/
   case "updated":
      $sort = "updated desc, port";
      echo 'sorted by last update date.  but you can sort by <a href="' . $PHP_SELF . '?sort=category">category</a>';
      $ShowCategoryHeaders = 0;
      break;

   default:
      $sort ="category, port";
      echo 'sorted by category.  but you can sort by <a href="' . $PHP_SELF . '?sort=updated">last update</a>';
      $ShowCategoryHeaders = 1;
      $cache_file .= ".updated";
}

echo "</td></tr>\n";

srand((double)microtime()*1000000);
$cache_time_rnd =       300 - rand(0, 600);


if ($Debug) {
echo '<br>';
echo '$cache_file=', $cache_file, '<br>';
echo '$LastUpdateFile=', $LastUpdateFile , '<br>';
echo '!(file_exists($cache_file))=',     !(file_exists($cache_file)), '<br>';
echo '!(file_exists($LastUpdateFile))=', !(file_exists($LastUpdateFile)), "<br>";
echo 'filectime($cache_file)=',          filectime($cache_file), "<br>";
echo 'filectime($LastUpdateFile)=',      filectime($LastUpdateFile), "<br>";
echo '$cache_time_rnd=',                 $cache_time_rnd, '<br>';
echo 'filectime($cache_file) - filectime($LastUpdateFile) + $cache_time_rnd =', filectime($cache_file) - filectime($LastUpdateFile) + $cache_time_rnd, '<br>';
}

$UpdateCache = 0;
if (!file_exists($cache_file)) {
//   echo 'cache does not exist<br>';
   // cache does not exist, we create it
   $UpdateCache = 1;
} else {
//   echo 'cache exists<br>';
   if (!file_exists($LastUpdateFile)) {
      // no updates, so cache is fine.
//      echo 'but no update file<br>';
   } else {
//      echo 'cache file was ';
      // is the cache older than the db?
      if ((filectime($cache_file) + $cache_time_rnd) < filectime($LastUpdateFile)) {
//         echo 'created before the last database update<br>';
         $UpdateCache = 1;
      } else {
//         echo 'created after the last database update<br>';
      }
   }
}

$UpdateCache = 1;

if ($WatchID == '') {
   echo "<tr><td>Your watch list is empty.</td></tr>";
} else {

if ($UpdateCache == 1) {
//   echo 'time to update the cache';

$sql = "";
$sql = "select ports.id, element.name as port, ports.id as ports_id, commit_log.commit_date as updated, " .
       "categories.name as category, categories.id as category_id, ports.version as version, ".
       "commit_log.committer, commit_log.description as update_description, " .
       "ports.maintainer, ports.short_description, ports.date_added as date_added, ".
       "ports.last_commit_id as last_change_log_id, " .
       "ports.package_exists, ports.extract_suffix, ports.homepage, element.status, " .
       "ports.broken, ports.forbidden ".
       "from watch_list_element, element, categories, ports LEFT OUTER JOIN commit_log on (ports.last_commit_id = commit_log.id) " .
       "WHERE ports.category_id             = categories.id " .
	   "  and watch_list_element.element_id = ports.element_id " .
	   "  and ports.element_id              = element.id ";

$sql .= " order by $sort ";
//$sql .= " limit 20";

//$Debug=1;
if ($Debug) {
   echo $sql;
}

$result = pg_exec($db, $sql);
if (!$result) {
	echo pg_errormessage();
}
//$HTML = "</tr></td><tr>";

$HTML .= '<tr><td>';

// get the list of topics, which we need to modify the order
$NumPorts=0;

require("../classes/ports.php");
$port = new Port($db);
$port->LocalResult = $result;

$LastCategory='';
$GlobalHideLastChange = "N";
$numrows = pg_numrows($result);

$DaysMarkedAsNew= $DaysMarkedAsNew= $GlobalHideLastChange= $ShowChangesLink= $ShowDescriptionLink= $ShowDownloadPortLink= $ShowHomepageLink= $ShowLastChange= $ShowMaintainedBy= $ShowPortCreationDate= $ShowPackageLink= $ShowShortDescription =1;
$ShowPortCreationDate = 0;
$HideCategory = 1;
$ShowCategories		= 1;
GLOBAL	$ShowDepends;
$ShowDepends		= 1;
#$HideDescription = 1;
$ShowEverything  = 1;
$ShowShortDescription = "Y";
$ShowMaintainedBy     = "Y";
#$GlobalHideLastChange = "Y";
$ShowDescriptionLink  = "N";

for ($i = 0; $i < $numrows; $i++) {
	$port->FetchNth($i);
	if ($ShowCategoryHeaders) {
		$Category = $port->category;

		if ($LastCategory != $Category) {
			$LastCategory = $Category;
			$HTML .= '<h3><a href="/' . $Category . '/">Category ' . $Category . '</a></h3>';
		}
	}

	$HTML .= freshports_PortDetails($port, $db, $DaysMarkedAsNew, $DaysMarkedAsNew, $GlobalHideLastChange, $HideCategory, $HideDescription, $ShowChangesLink, $ShowDescriptionLink, $ShowDownloadPortLink, $ShowEverything, $ShowHomepageLink, $ShowLastChange, $ShowMaintainedBy, $ShowPortCreationDate, $ShowPackageLink, $ShowShortDescription);
}

}

$HTML .= "</td></tr>\n";

$HTML .= "<tr><td>$numrows ports found</td></tr>\n";

echo $HTML;

} // end if no WatchID
}

</script>
</table>
</td>
  <td valign="top" width="*">
<? include("./include/side-bars.php") ?>
</td>
</tr>
</table>

<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><TD>
<? include("./include/footer.php") ?>
</TD></TR>
</TABLE>

</body>
</html>
