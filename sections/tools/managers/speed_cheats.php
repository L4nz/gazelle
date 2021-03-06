<?

include(SERVER_ROOT . '/sections/tools/managers/speed_functions.php');

// The "order by x" links on columns headers
function header_link($SortKey, $DefaultWay = "desc") {
    global $OrderBy, $OrderWay;
    if ($SortKey == $OrderBy) {
        if ($OrderWay == "desc") {
            $NewWay = "asc";
        } else {
            $NewWay = "desc";
        }
    } else {
        $NewWay = $DefaultWay;
    }

    return "tools.php?action=speed_cheats&amp;order_way=" . $NewWay . "&amp;order_by=" . $SortKey . "&amp;" . get_url(array('action', 'order_way', 'order_by'));
}


function format_torrentid($torrentID, $name, $maxlen = 20) {
    if ($torrentID == 0) return 'None';
    if ($name == '') $tname = $torrentID;
    else $tname = $name;
    $str = '<a href="torrents.php?torrentid='.$torrentID.'" title="'.$tname.'">'. cut_string($tname, $maxlen).'</a>';
    if ($name == '') $str = "torrent not found [$str]";
    return $str;
}
function size_span($speed, $text) {
    return '<span style="color:'.($speed>0?'black':'lightgrey').'">'.$text.'</span>';
}
function speed_span($speed, $highlightlimit, $color, $text) {
    if ($speed>=$highlightlimit) $scolor = $color;
    elseif ($speed>0) $scolor = 'black';
    else $scolor = 'lightgrey';
    return '<span style="color:'.$scolor.'">'.$text.'</span>';
}


if(!check_perms('users_manage_cheats')) { error(403); }


if (!empty($_GET['order_way']) && $_GET['order_way'] == 'asc') {
    $OrderWay = 'asc'; // For header links
} else {
    $_GET['order_way'] = 'desc';
    $OrderWay = 'desc';
}
                       //     xbt.id, uid, Username, xbt.downloaded, remaining, t.Size, xbt.uploaded, 
                     ////       upspeed, downspeed, timespent, peer_id, xbt.ip, tg.ID, fid, tg.Name, xbt.mtime
                                    
// User 	Remaining 	Uploaded 	UpSpeed 	ClientIPaddress 	date time 	
//	TorrentID 	Total 	Downloaded 	DownSpeed 	                	total time 
                                    
if (empty($_GET['order_by']) || !in_array($_GET['order_by'], array('Username', 'upspeed', 'count', 'mtime' ))) {
    $_GET['order_by'] = 'upspeed';
    $OrderBy = 'MAX(xbt.upspeed)'; 
} else {
    $OrderBy = $_GET['order_by'];
}

        
$DB->query("SELECT DeleteRecordsMins, KeepSpeed FROM site_options ");
list($DeleteRecordsMins, $KeepSpeed) = $DB->next_record();
        
$ViewSpeed = isset($_GET['viewspeed'])?(int)$_GET['viewspeed']:$KeepSpeed;
$BanSpeed = isset($_GET['banspeed'])?(int)$_GET['banspeed']:$BanSpeed;
 
show_header('Speed Cheats','watchlist');

?>
<div class="thin">
    <h2>(possible) cheaters</h2>
     
<?
    $Watchlist = print_user_watchlist();
     
?>
    <div class="head">options</div>
<?
 

    $CanManage = check_perms('admin_manage_cheats');
?>
    <table class="box pad">
        <form action="tools.php" method="post">
            
                <input type="hidden" name="action" value="save_records_options" />
                <input type="hidden" name="userid" value="<?=$_GET['userid']?>" />
                <input type="hidden" name="torrentid" value="<?=$_GET['torrentid']?>" />
                <input type="hidden" name="viewspeed" value="<?=$ViewSpeed?>" />
                <input type="hidden" name="auth" value="<?=$LoggedUser['AuthKey']?>" />
 
            <tr class="colhead"><td colspan="3">view settings: </td></tr>
            <tr class="rowb">  
                <td class="center">
                    Viewing: <?=$ViewInfo?> &nbsp; (order: <?="$OrderBy $OrderWay"?>)
<?                  if ($ViewInfo!='all over speed specified') { ?>
                        <a href="tools.php?action=speed_records&viewspeed=<?=$ViewSpeed?>" title="Removes any user or torrent filters for viewing (still applies speed filter)">View All</a>
<?                  } ?>
                </td>
                <td class="center">
                            <label for="viewbanned" title="Keep Speed">include disabled users </label>
                        <input type="checkbox" value="1" onchange="change_view()"
                               id="viewbanned" name="viewbanned" <? if (isset($_GET['viewbanned']) && $_GET['viewbanned'])echo' checked="checked"'?> />
                </td>
                <td class="center">
                    <label for="viewspeed" title="View Speed">View records with upload speed over </label>
                    <select id="viewspeed" name="viewspeed" title="Hide records under this speed" onchange="change_view()">
                        <option value="0"<?=($ViewSpeed==0?' selected="selected"':'');?>>&nbsp;0&nbsp;&nbsp;</option>
                        <option value="262144"<?=($ViewSpeed==262144?' selected="selected"':'');?>>&nbsp;<?=get_size(262144);?>/s&nbsp;&nbsp;</option>
                        <option value="524288"<?=($ViewSpeed==524288?' selected="selected"':'');?>>&nbsp;<?=get_size(524288);?>/s&nbsp;&nbsp;</option>
                        <option value="1048576"<?=($ViewSpeed==1048576?' selected="selected"':'');?>>&nbsp;<?=get_size(1048576);?>/s&nbsp;&nbsp;</option>
<?                      for($i=2;$i<=20;$i+=2){ 
                            print_speed_option($i * 1048576 , $ViewSpeed );
                        }
                        for($i=30;$i<=200;$i+=10){ 
                            print_speed_option($i * 1048576 , $ViewSpeed );
                        }
                        /*
                        for($i=18;$i<=32;$i+=2){ 
                            print_speed_option($i * 1048576 , $ViewSpeed );
                        }
                        for($i=64;$i<=128;$i+=32){ 
                            print_speed_option($i * 1048576 , $ViewSpeed );
                        }
                        for($i=256;$i<=1024;$i+=128){ 
                            print_speed_option($i * 1048576 , $ViewSpeed );
                        }
                        for($i=110;$i<=200;$i+=10){ 
                            print_speed_option($i * 1048576 , $ViewSpeed );
                        }
                        for($i=250;$i<=1000;$i+=50){ 
                            print_speed_option($i * 1048576 , $ViewSpeed );
                        } 
                        for($i=1;$i<=4096;$i*=2){ 
                            print_speed_option($i * 1048576 , $ViewSpeed );
                        }*/
                        ?>
                    </select>
                </td>
            </tr>
        </form>
        <tr class="colhead"><td colspan="3">group ban tool: </td></tr>
        <tr class="rowb">
            <form id="speedrecords" action="tools.php" method="post" onsubmit="return prompt_before_multiban();">
                <input type="hidden" name="action" value="ban_speed_cheat" />
                <td class="center"> 
                    <label for="banspeed" title="Ban Speed">Ban users with upload speed over </label>
                    <select id="banspeed" name="banspeed" title="Ban users who have recorded speeds over this"  onchange="preview_users()">
    <?                      for($i=10;$i<=20;$i+=2){ 
                                print_speed_option($i * 1048576 , $BanSpeed );
                            }
                            for($i=30;$i<=200;$i+=10){ 
                                print_speed_option($i * 1048576 , $BanSpeed );
                            }
                            ?>
                    </select>
                    &nbsp;<a href="#" onclick="preview_users();return false;" title="Preview all the users who have recorded a max speed over this">Preview users</a>
                </td>
                <td colspan="2" class="center">  
                    <input type="submit" name="banusers" value="Ban specified users" title="Will ban users over the speed specified" /> 
                </td>
            </form>
        </tr>
    </table>
    <br/>
<?
//---------- print records
            
list($Page,$Limit) = page_limit(50);
 
if ($OrderBy=="upspeed") $SQLOrderBy="MAX(xbt.upspeed)";
elseif ($OrderBy=="mtime") $SQLOrderBy="MAX(xbt.mtime)"; 
elseif ($OrderBy=="count") $SQLOrderBy="Count(xbt.id)";
else $SQLOrderBy=$OrderBy;

    if (isset($_GET['viewbanned']) && $_GET['viewbanned']){
        $ViewInfo .= ' (all)';
    } else {
        $WHERE .= " AND (um.ID IS NULL OR um.Enabled='1')";
        $ViewInfo .= ' (enabled only)';
    }
    
$DB->query("SELECT SQL_CALC_FOUND_ROWS
                             uid, Username, Count(xbt.id), MAX(upspeed), MAX(mtime), 
                             GROUP_CONCAT(DISTINCT LEFT(xbt.peer_id,8) SEPARATOR '|'), 
                             GROUP_CONCAT(DISTINCT xbt.ip SEPARATOR '|'),
                             ui.Donor, ui.Warned, um.Enabled, um.PermissionID
                          FROM xbt_peers_history AS xbt
                     LEFT JOIN users_main AS um ON um.ID=xbt.uid
                     LEFT JOIN users_info AS ui ON ui.UserID=xbt.uid
                         WHERE (xbt.upspeed)>='$ViewSpeed' $WHERE
                      GROUP BY xbt.uid   
                      ORDER BY $SQLOrderBy $OrderWay 
                         LIMIT $Limit");



$Records = $DB->to_array();
$DB->query("SELECT FOUND_ROWS()");
list($NumResults) = $DB->next_record();
 
$Pages=get_pages($Page,$NumResults,50,9);

?>
    
	<div class="linkbox"><?=$Pages?></div>
     
    <div class="head"><?=$NumResults?> users with speed over <?=get_size($ViewSpeed).'/s'?></div>
        <table>
            <tr class="colhead">
                <td style="width:66px"></td>
                <td class="center"><a href="<?=header_link('Username') ?>">User</a></td>
                <td class="center"><a href="<?=header_link('upspeed') ?>">Max UpSpeed</a></td>
                <td class="center"><a href="<?=header_link('count') ?>">count</a></td>
                <td class="center"><span style="color:#777">-clientID-</span></td>
                <td class="center">Client IP addresses</td>
                <td class="center"><a href="<?=header_link('mtime') ?>">last seen</a></td>
                <td class="center"></td>
            </tr>
<?
            $row = 'a';
            if($NumResults==0){
?> 
                    <tr class="rowb">
                        <td class="center" colspan="8">no speed records</td>
                    </tr>
<?
            } else {
                foreach ($Records as $Record) {
                    list( $UserID, $Username, $CountRecords, $MaxUpSpeed, $LastTime, $PeerIDs, $IPs, $IsDonor, $Warned, $Enabled, $ClassID) = $Record;
                    $row = ($row === 'a' ? 'b' : 'a');
                    
                    $PeerIDs = explode('|', $PeerIDs);
                    $IPs = explode('|', $IPs);
                    /*
                    $DB->query(" SELECT e.UserID AS UserID, x.IP, 'tracker', 'account' FROM xbt_snatched AS x JOIN users_history_ips AS e ON x.IP=e.IP 
                                 WHERE x.IP != '127.0.0.1' AND x.IP !='' AND e.UserID!= $UserID AND x.uid = $UserID
                                 GROUP BY x.uid
                                UNION
                                 SELECT x2.uid AS UserID, x.IP, 'tracker', 'tracker' FROM xbt_snatched AS x JOIN xbt_snatched AS x2 ON x.IP=x2.IP 
                                 WHERE x.IP != '127.0.0.1' AND x.IP !='' AND x2.uid!= $UserID AND x.uid = $UserID
                                 GROUP BY x.uid
                                UNION
                                 SELECT x.uid AS UserID, x.IP, 'account', 'tracker' FROM xbt_snatched AS x JOIN users_history_ips AS e ON x.IP=e.IP 
                                 WHERE x.IP != '127.0.0.1' AND x.IP !='' AND e.UserID = $UserID AND x.uid != $UserID
                                 GROUP BY x.uid
                                UNION
                                 SELECT e1.UserID AS UserID, e1.IP, 'account', 'account' FROM users_history_ips AS e1 JOIN users_history_ips AS e ON e1.IP=e.IP 
                                 WHERE e1.IP != '127.0.0.1' AND e1.IP !='' AND e.UserID = $UserID AND e1.UserID != $UserID  
                                ORDER BY  UserID, IP   "); */
                    
	$DB->query(" SELECT e.UserID AS UserID, um.IP, 'account', 'history' FROM users_main AS um JOIN users_history_ips AS e ON um.IP=e.IP 
				 WHERE um.IP != '127.0.0.1' AND um.IP !='' AND e.UserID!= $UserID AND um.ID = $UserID
                UNION
                 SELECT e.ID AS UserID, um.IP, 'account', 'account' FROM users_main AS um JOIN users_main AS e ON um.IP=e.IP 
				 WHERE um.IP != '127.0.0.1' AND um.IP !='' AND e.ID!= $UserID AND um.ID = $UserID
                UNION
                 SELECT um.ID AS UserID, um.IP, 'history', 'account' FROM users_main AS um JOIN users_history_ips AS e ON um.IP=e.IP 
				 WHERE um.IP != '127.0.0.1' AND um.IP !='' AND e.UserID = $UserID AND um.ID != $UserID
                UNION
                 SELECT um.UserID AS UserID, um.IP, 'history', 'history' FROM users_history_ips AS um JOIN users_history_ips AS e ON um.IP=e.IP 
				 WHERE um.IP != '127.0.0.1' AND um.IP !='' AND e.UserID = $UserID AND um.UserID != $UserID  
                ORDER BY  UserID, IP   ");
                    $IPDupeCount = $DB->record_count();
                    $IPDupes = $DB->to_array();
                    
?> 
                    <tr class="row<?=$row?>">
                        <td>
                           <a href="?action=speed_records&viewspeed=0&userid=<?=$UserID?>" title="View records for just <?=$Username?>">[view]</a> 
<?                         if (!array_key_exists($UserID, $Watchlist)) {   
?>                            <a onclick="watchlist_add('<?=$UserID?>',true);return false;" href="#" title="Add <?=$Username?> to watchlist"><img src="static/common/symbols/watched.png" alt="view" /></a><?
                           }  ?>
                        </td>
                        <td class="center">
<?                          echo format_username($UserID, $Username, $IsDonor, $Warned, $Enabled, $ClassID, false, false);  

                            if ($IPDupeCount>0) { ?> 
                             
                            <span style="float:right;">
                                <a href="#" title="view matching ip's for this user" onclick="$('#linkeddiv<?=$UserID?>').toggle();this.innerHTML=this.innerHTML=='(hide)'?'(view)':'(hide)';return false;">(view)</a>
                            </span>
<?
                            }
?>
                            
                        </td>
                        <td class="center"><?=speed_span($MaxUpSpeed, $KeepSpeed, 'red', get_size($MaxUpSpeed).'/s')?></td>
                        <td class="center"><?=$CountRecords?></td>
                        <td class="center"><?
                            foreach($PeerIDs as $PeerID) {
                        ?>  <span style="color:#555"><?=substr($PeerID,0,8)  ?></span> <br/>
                        <?  } ?>
                        </td>
                        <td class="center"><?
                            foreach($IPs as $IP) {
                                $ipcc = geoip($IP);
                                echo display_ip($IP, $ipcc)."<br/>";
                            }
                        ?> 
                        </td>
                        <td class="center"><?=time_diff($LastTime, 2, true, false, 1)?></td>
                        <td class="center">
<?
                            if ($Enabled=='1'){
?>
                            <form id="speedrecords" action="tools.php" method="post">
                                <input type="hidden" name="action" value="ban_speed_cheat" />
                                <input type="hidden" name="userid" value="<?=$UserID?>" />
                                <input type="hidden" name="maxspeed" value="<?=$MaxUpSpeed?>" />
                                <input type="hidden" name="banspeed" value="<?=$BanSpeed?>" />
                                <input type="submit" name="banuser" value="ban" title="ban this user for being a big fat cheat" />
                            </form>
<?
                            }
?>
                        </td>
                    </tr>
<?
            if ($IPDupeCount>0) { 
?>
                    <tr id="linkeddiv<?=$UserID?>" style="font-size:0.9em;" class="hidden row<?=$row?>">
                        <td colspan="8"> 
            <table width="100%" class="border">
<?
            $i = 0;
            foreach($IPDupes AS $IPDupe) {
                list($EUserID, $IP, $EType1, $EType2) = $IPDupe;
                $i++;
                $DupeInfo = user_info($EUserID);
?> 
            <tr>
                <td>
                    <a href="?action=speed_records&viewspeed=0&userid=<?=$EUserID?>" title="View records for just <?=$DupeInfo['Username']?>">[view]</a> 
<?                  if (!array_key_exists($EUserID, $Watchlist)) {   
?>                      <a onclick="watchlist_add('<?=$EUserID?>',true);return false;" href="#" title="Add <?=$DupeInfo['Username']?> to watchlist"><img src="static/common/symbols/watched.png" alt="view" /></a><?
                    }  ?>
                </td>
                <td align="center">
                    <?=format_username($EUserID, $DupeInfo['Username'], $DupeInfo['Donor'], $DupeInfo['Warned'], $DupeInfo['Enabled'], $DupeInfo['PermissionID'])?>
                </td>
                <td align="center">
                    <?=display_ip($IP, $DupeInfo['ipcc'])?>
                </td>
                <td align="left">
                    <?="$Username's $EType1 <-> $DupeInfo[Username]'s $EType2"?>
                </td>
                <td>
<?
                    if ( !array_key_exists($EUserID, $Dupes) ) {
?>
						[<a href="user.php?action=dupes&dupeaction=link&auth=<?=$LoggedUser['AuthKey']?>&userid=<?=$UserID?>&targetid=<?=$EUserID?>">link</a>]
<?
                    }
?>
                </td> 
            </tr>
<?
            }
?>
            </table>
                            
                        </td>
                    </tr>
<?
            }
?> 
               
<?
                   

                }
            }
            ?>
        </table>
	<div class="linkbox"><?=$Pages?></div>
</div>
<? show_footer(); ?>