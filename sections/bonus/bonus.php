<?
include(SERVER_ROOT.'/classes/class_text.php');
$Text = new TEXT;

// check if their credits need updating (if they have been online whilst creds are accumalting)
$DB->query("SELECT Credits FROM users_main WHERE ID='$LoggedUser[ID]'");
list($TotalCredits) = $DB->next_record();
if($TotalCredits != $LoggedUser['TotalCredits']){
    $LoggedUser['TotalCredits'] = $TotalCredits; // for interface below
    $Cache->delete_value('user_stats_' . $LoggedUser['ID']);
}

enforce_login();
show_header('Bonus Shop','bonus,bbcode');
  
            
$ShopItems = get_shop_items($LoggedUser['ID']);
?>
<div class="thin">
	<h2>Bonus shop</h2>
            <div class="box pad shadow">
<? 
                $creditinfo = get_article('creditsinline');
                if($creditinfo) echo $Text->full_format($creditinfo, true);  
?>
            </div>
            <div class="box pad shadow" id="bonusdiv">
                <h3 class="center">Credits: <?=number_format($LoggedUser['TotalCredits'],2)?></h3>
            </div>
 <?         if(!empty($_REQUEST['result'])){  ?>
                <div class="box pad shadow">
                    <h3 class="center"><?=display_str($_REQUEST['result'])?></h3> 
                </div>
<?          }  ?>
            
	<div class="head">Bonus Shop</div>
            
		<table class="bonusshop">
			<tr class="smallhead">
				<td width="120px">Title</td>
				<td width="530px" colspan="2">Description</td>
				<td width="90px" colspan="2">Price</td>
			</tr>
<?
	$Row = 'a';
      $UserBadgeIDs = get_user_shop_badges_ids($LoggedUser['ID']);
	foreach($ShopItems as $BonusItem) {
		list($ItemID, $Title, $Description, $Action, $Value, $Cost, $Image, $Badge, $Rank, $UserRank) = $BonusItem;
            $IsBadge = $Action=='badge'; 
            $IsBuyGB = $Action=='gb'; 
            $DescExtra='';
            // if user already has badge item dont allow buy
            if ($IsBadge && in_array($Value, $UserBadgeIDs)) {
                $CanBuy = false;
                $BGClass= ' itemduplicate';
            } elseif ($IsBuyGB && $LoggedUser['BytesDownloaded'] <=0) {
                $CanBuy = false;
                $BGClass= ' itemnotbuy';
            } else { //
                $CanBuy = is_float((float)$LoggedUser['TotalCredits']) ? $LoggedUser['TotalCredits'] >= $Cost: false;
                $BGClass= ($CanBuy?' itembuy' :' itemnotbuy');
                if ($IsBuyGB && $LoggedUser['BytesDownloaded'] < get_bytes($Value.'gb') ) {
                    $DescExtra = "<br/>(WARNING: will only remove ".get_size($LoggedUser['BytesDownloaded']) .")"; // get_size(get_bytes($Value.'gb') - 
                }
                if($IsBadge ) {
                    if ($LastBadge==$Badge) {
                        $CanBuy = false;
                        $BGClass = ' itemnotbuy';
                    } elseif($Rank < $UserRank) {
                        $CanBuy = false;
                        $BGClass = ' itemduplicate';
                    } else 
                        $LastBadge=$Badge;
                }
            }
		$Row = ($Row == 'a') ? 'b' : 'a';
?> 
			<tr class="row<?=$Row.$BGClass?>">
				<td width="160px"><strong><?=display_str($Title) ?></strong></td>
				<td style="border-right:none;" <? if(!$Image) { echo 'colspan="2"'; } ?>><?=display_str($Description).$DescExtra?></td>
                    <?  if ($Image) {  ?>
                        <td style="border-left:none;width:160px;text-align:center;">
                            <div class="badge">
                                <img src="<?=STATIC_SERVER.'common/badges/'.$Image?>" title="<?=$Title?>" alt="<?=$Title?>" />
                            </div>
                        </td>
                   <?   }
                   
                        if (strpos($Action, 'give') !== false) {
                            $OnSubmit = 'onsubmit="return SetUsername(\'othername'.$ItemID.'\'); "';
                        } elseif ($Action == 'title') {
                            $OnSubmit = 'onsubmit="return SetTitle(\'title'.$ItemID.'\'); "';
                        } elseif ($Action == 'ufl') {
                            $OnSubmit = 'onsubmit="return SetTorrent(\'torrentid'.$ItemID.'\'); "';
                        } else {
                            $OnSubmit = '';
                        }
                   ?>
				<td width="60px" style="text-align: center;"><strong><?=number_format($Cost) ?>c</strong></td>
				<td width="60px" style="text-align: center;">
                            <form method="post" action="" <?=$OnSubmit?>>  
                                <input type="hidden" name="action" value="buy" />
                                <input type="hidden" id="othername<?=$ItemID?>" name="othername" value="" />
                                <input type="hidden" name="shopaction" value="<?=$Action?>" />
                                <input type="hidden" name="userid" value="<?=$LoggedUser['ID']?>" />
                                <input type="hidden" name="auth" value="<?=$LoggedUser['AuthKey']?>" />
                                <input type="hidden" name="itemid" value="<?=$ItemID?>" /> 
                                <input class="shopbutton<?=($CanBuy ? ' itembuy' : ' itemnotbuy')?>" name="submit" value="<?=($CanBuy?'Buy':'x')?>" type="submit"<?=($CanBuy ? '' : ' disabled="disabled"')?> />
                                <? if($Action == 'title') echo '<input type="hidden" id="title'.$ItemID.'" name="title" value="" />'; ?>
                                <? if($Action == 'ufl') echo '<input type="hidden" id="torrentid'.$ItemID.'" name="torrentid" value="" />'; ?>
                            </form>
				</td>
			</tr>
<?	} ?>
		</table> 
</div>
<?
show_footer();
?>
