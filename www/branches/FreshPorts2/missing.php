<?php
	#
	# $Id: missing.php,v 1.1.2.18 2003-09-24 17:47:41 dan Exp $
	#
	# Copyright (c) 2001-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/databaselogin.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/getvalues.php');


function freshports_Parse404URI($REQUEST_URI, $db) {
	#
	# we have a pending 404
	# if we can parse it, then do so and return 1;
	# otherwise, return 0.

	$Debug = 0;

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/element_record.php');

	UnSet($result);

	$ElementRecord = new ElementRecord($db);

	if (substr($REQUEST_URI, 0, 1) != '/') {
		$REQUEST_URI = '/' . $REQUEST_URI;
	}

	if (!preg_match('|^/?ports/|', $REQUEST_URI)) {
		$REQUEST_URI = '/ports' . $REQUEST_URI;
	}


	if ($ElementRecord->FetchByName($REQUEST_URI)) {
		if ($ElementRecord->IsPort()) {

			require_once($_SERVER['DOCUMENT_ROOT'] . '/missing-port.php');
			freshports_PortDescription($db, $ElementRecord->id);

		} else {
			if ($ElementRecord->IsCategory()) {

				require_once($_SERVER['DOCUMENT_ROOT'] . '/missing-category.php');
				freshports_Category($db, $ElementRecord->id);

			} else {
				if ($Debug) echo "\$ElementRecord->element_id='$ElementRecord->element_id'<br>";
				freshports_Commits($ElementRecord);
			}
		}
	}

	if ($Debug) {
		echo "\$ElementRecord->id         = $ElementRecord->id<br>";
		echo "\$ElementRecord->name       = $ElementRecord->name<br>";
		echo "\$ElementRecord->type       = $ElementRecord->type<br>";
		echo "\$ElementRecord->status     = $ElementRecord->status<br>";
		echo "\$ElementRecord->iscategory = $ElementRecord->iscategory<br>";
		echo "\$ElementRecord->isport     = $ElementRecord->isport<br>";
		echo '<br>';
		echo "\$ElementRecord->element_pathname = $ElementRecord->element_pathname<br>";
	}

	return $result;
}

$result = freshports_Parse404URI($_SERVER['REQUEST_URI'], $db);

if ($result) {

	#
	# this is a true 404

	$Title = 'Document not found';
	freshports_Start($Title,
					$FreshPortsTitle . ' - new ports, applications',
					'FreeBSD, index, applications, ports');

?>

<TABLE WIDTH="<? echo $TableWidth ?>" BORDER="0" ALIGN="center">
<TR>
<TD WIDTH="100%" VALIGN="top">
<TABLE WIDTH="100%" BORDER="0" CELLPADDING="1">
<TR>
    <TD BGCOLOR="#AD0040" HEIGHT="29"><FONT COLOR="#FFFFFF"><BIG><BIG>
<?
   echo "$FreshPortsTitle -- $Title";
?>
</BIG></BIG></FONT></TD>
</TR>

<TR>
<TD WIDTH="100%" VALIGN="top">
<P>
Sorry, but I don't know anything about that.
</P>

<P>
<? echo $result ?>
</P>

<P>
Perhaps a <A HREF="/categories.php">list of categories</A> or <A HREF="/search.php">the search page</A> might be helpful.
</P>

</TD>
</TR>
</TABLE>
</TD>

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
} else {
#	echo " ummm, not sure what that was: '$result'";
}

?>
