<?php
	#
	# $Id: missing-category.php,v 1.1.2.28 2003-09-24 17:47:41 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/ports.php');

DEFINE('MAX_PAGE_SIZE',     500);
DEFINE('DEFAULT_PAGE_SIZE', 25);

DEFINE('NEXT_PAGE',		'Next');

function freshports_CategoryNextPreviousPage($CategoryName, $PortCount, $PageNo, $PageSize) {

	echo "Result Page:";

	$NumPages = ceil($PortCount / $PageSize);

	for ($i = 1; $i <= $NumPages; $i++) {
		if ($i == $PageNo) {
			echo "&nbsp;<b>$i</b>";
			echo "\n";
		} else {
			echo '&nbsp;<a href="/' . $CategoryName . '/?page=' . $i .  '">' . $i . '</a>';
			echo "\n";
		}
	}

	if ($PageNo == $NumPages) {
		echo '&nbsp; ' . NEXT_PAGE;
	} else {
		echo '&nbsp;<a href="/' . $CategoryName . '/?page=' . ($PageNo + 1) .  '">' . NEXT_PAGE . '</a>';
		echo "\n";
	}
}

function str_is_int($str) {
	$var = intval($str);
	return ($str == $var);
}


function freshports_Category($db, $element_id, $PageNo = 1, $PageSize = 25) {

	GLOBAL $TableWidth;
	header('HTTP/1.1 200 OK');

	$Debug = 0;

	if (In_Array("REDIRECT_QUERY_STRING", $_SERVER)) {
		if (IsSet($_SERVER["REDIRECT_QUERY_STRING"])) {
			parse_str($_SERVER['REDIRECT_QUERY_STRING'], $query_parts);
			if (IsSet($query_parts['page']))      $PageNo   = $query_parts['page'];
			if (IsSet($query_parts['page_size'])) $PageSize = $query_parts['page_size'];
		}
	}

	if (!IsSet($page) || $page == '') {
		$page = 1;
	}

	if (!IsSet($page_size) || $page_size == '') {
		$page_size = $User->page_size;
	}

	if ($Debug) {
		echo "\$page      = '$page'<br>\n";
		echo "\$page_size = '$page_size'<br>\n";
	}
					
	SetType($PageNo,   "integer");
	SetType($PageSize, "integer"); 

	if (!IsSet($PageNo)   || !str_is_int("$PageNo")   || $PageNo   < 1) {
		$PageNo = 1;
	}

	if (!IsSet($PageSize) || !str_is_int("$PageSize") || $PageSize < 1 || $PageSize > MAX_PAGE_SIZE) {	
		$PageSize = DEFAULT_PAGE_SIZE;
	}

	if ($Debug) {
		echo "\$PageNo   = '$PageNo'<br>\n";
		echo "\$PageSize = '$PageSize'<br>\n";
	}

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/categories.php');

	$category = new Category($db);
	$category->FetchByElementID($element_id);
	$title = $category->{'name'};

	# find out how many ports are in this category
	$PortCount = $category->PortCount($category->name);

	GLOBAL $User;
	if ($Debug) echo "\$User->id='$User->id'";

	freshports_Start($title,
					'freshports - new ports, applications',
					'FreeBSD, index, applications, ports');

	$DESC_URL = 'ftp://ftp.freebsd.org/pub/FreeBSD/branches/-current/ports';

	$port = new Port($db);

	$numrows = $port->FetchByCategoryInitialise($category->name, $User->id, $PageSize, $PageNo);

	?>


	<table width="<? echo $TableWidth ?>" border="0" ALIGN="center">
	<tr><td valign="top" width="100%">
	<table width="100%" border="0">
		<tr>
		 <? echo freshports_PageBannerText('Category listing - ' . $category->{'name'}); ?>
		</tr>

	<tr><td>
	<BIG><BIG><B><?php
echo $category->{'description'} 
?></B></BIG></BIG> - Number of ports in this category: <?php
echo $PortCount;

?>
<p>
	Ports marked with a <sup>*</sup> actually reside within another category but
	have <b><?php echo $category->{'name'}; ?></b> listed as a secondary category.

<?php
echo '<div align="center"><br>';
freshports_CategoryNextPreviousPage($category->name, $PortCount, $PageNo, $PageSize);
echo '</div>';

?>
	</td></tr>

<?
	if ($Debug) {
		echo "\$CategoryID = '$CategoryID'<BR>\n";;
		echo "GlobalHideLastChange = $GlobalHideLastChange<BR>\n";
		echo "\$numrows = $numrows<BR>\n";
	}

	$ShowShortDescription	= "Y";


	$HTML = freshports_echo_HTML("<TR>\n<TD>\n");


$DaysMarkedAsNew= $DaysMarkedAsNew= $GlobalHideLastChange= $ShowChangesLink= $ShowDescriptionLink= $ShowDownloadPortLink= $ShowHomepageLink= $ShowLastChange= $ShowMaintainedBy= $ShowPortCreationDate= $ShowPackageLink= $ShowShortDescription =1;
$ShowPortCreationDate = 0;
$HideCategory = 1;
$ShowCategories		= 1;
GLOBAL	$ShowDepends;
$ShowDepends		= 1;
$HideDescription = 1;
$ShowEverything  = 1;
$ShowShortDescription = "Y";
$ShowMaintainedBy     = "Y";
$GlobalHideLastChange = "Y";
$ShowDescriptionLink  = "N";
	for ($i = 0; $i < $numrows; $i++) {
		$port->FetchNth($i);

		$HTML .= freshports_PortDetails($port, $db, $DaysMarkedAsNew, $DaysMarkedAsNew, $GlobalHideLastChange, $HideCategory, $HideDescription, $ShowChangesLink, $ShowDescriptionLink, $ShowDownloadPortLink, $ShowEverything, $ShowHomepageLink, $ShowLastChange, $ShowMaintainedBy, $ShowPortCreationDate, $ShowPackageLink, $ShowShortDescription, 1, '', 0);
	} // end for

	echo $HTML;

	?>
</TD></TR>
<TR><TD>
<div align="center"><br>
<?php 
freshports_CategoryNextPreviousPage($category->name, $PortCount, $PageNo, $PageSize);
?>
</div> 
</TD></TR>
</TABLE>
  <TD VALIGN="top" WIDTH="*" ALIGN="center">
  <?
  freshports_SideBar();
  ?>
  </td>
</TR>
</TABLE>

<?
	freshports_ShowFooter();
?>

	</body>
	</html>

	<?

	}

?>