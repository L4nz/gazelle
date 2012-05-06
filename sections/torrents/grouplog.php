<?
$GroupID = $_GET['groupid'];
if (!is_number($GroupID)) { error(404); }

show_header("History for Group $GroupID");

$Groups = get_groups(array($GroupID), true, true, false);
if (!empty($Groups['matches'][$GroupID])) {
	$Group = $Groups['matches'][$GroupID];
	$Title = display_artists($Group['ExtendedArtists']).'<a href="torrents.php?id='.$GroupID.'">'.$Group['Name'].'</a>';
} else {
	$Title = "Group $GroupID";
}
die('sdlfkjldsaf');
?>

<div class="thin">
	<h2>History for <?=$Title?></h2>

	<table>
		<tr class="colhead">
			<td>Date</td>
			<td>Torrent</td>
			<td>User</td>
			<td>Info</td>
		</tr>
<?
	$Log = $DB->query("SELECT TorrentID, UserID, Info, Time FROM group_log WHERE GroupID = ".$GroupID." ORDER BY Time DESC");

	while (list($TorrentID, $UserID, $Info, $Time) = $DB->next_record())
	{
?>
		<tr class="rowa">
			<td><?=$Time?></td>
                        
                        <td />
			
			$DB->query("SELECT Username FROM users_main WHERE ID = ".$UserID);
			list($Username) = $DB->next_record();
			$DB->set_query_id($Log);
?>
			<td><?=format_username($UserID, $Username)?></td>
			<td><?=$Info?></td>
		</tr>
<?
	}
?>
	</table>
</div>
<?
show_footer();
?>
