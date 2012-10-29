<?php 
//
//netstatus.php - render webpage of dmr repeater status 
//Copyright (C) 2012 David Kierzokwski (kd8eyf@digitalham.info)
//
//This program is free software; you can redistribute it and/or
//modify it under the terms of the GNU General Public License
//as published by the Free Software Foundation; either version 2
//of the License, or (at your option) any later version.
//
//This program is distributed in the hope that it will be useful,
//but WITHOUT ANY WARRANTY; without even the implied warranty of
//MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//GNU General Public License for more details.
//
//You should have received a copy of the GNU General Public License
//along with this program; if not, write to the Free Software
//Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
?>
<html>
<body>
<link rel="stylesheet" href="netstatus.css" type="text/css">
<div id="header" class="fixed">
         <div class="logo">                
                <img src="logo.png" border="0" width="300" height="75" alt="logo" />
                </div>
			</a>
		<div class="nav">
			<ul>
			  <li><a href="netstatus.php" class="active" >NetStatus</a></li>
              <li><a href="lastheard.php" >LastHeard</a></li>		
			  <li><a href="calllog.php">Call Log</a></li>
              <li>
                    <a href="users.php" >Users</a>
                </li>
  			</ul>
              </div>
			  
		</div>
	</div>
<div id="content" class="fixed">
    <div id="maincontent">
        <h2>Network Status</h2>
        <?
$state_location = "http://dmr.moses.bz/netstatus.json";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $state_location);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$json = curl_exec($ch);
curl_close($ch);
$State = json_decode($json, true);
foreach ($State as $NetworkID => $Network ){?>
    <table width="100%" border="0" cellspacing="0">
    <tr><td colspan=10 class="networkheader"><?=$State[$NetworkID][Description]; ?></td></tr>
    <tr><th>Country</th><th>State</th><th>Location</th><th>Frequency</th><th>Offset</th><th width="100%">Callsign / Trustee</th><th>&nbsp;&nbsp;STATUS&nbsp;&nbsp;</th><th>&nbsp;&nbsp;SLOT 1&nbsp;&nbsp;</th><th>&nbsp;&nbsp;SLOT 2&nbsp;&nbsp;</th></tr><?
    $i = 1;
    foreach ($State[$NetworkID] as $RepeaterID => $Repeater){
        if (is_int($RepeaterID)){
            $trClass = ( $i % 2 != 0 )? "odd": "even";
            $trClass = ($Repeater[Role]==1)?"master":$trClass; 
            $Callsign = ($Repeater[Callsign] == $Repeater[Owner])?$Repeater[Callsign]:$Repeater[Callsign]." / ".$Repeater[Owner]; ?>
            <tr class="<?=$trClass?>">
            <td><?=$Repeater[Country]?></td>
            <td><?=$Repeater[State]?></td>
            <td><?=$Repeater[City]?></td>
            <td><?=$Repeater[Frequency]?></td>
            <td><?=$Repeater[Offset]?></td>
            <td><?=$Callsign?></td><? 
            if ($Repeater[Online] ==0 ){ 
                echo "<td class=offline>LH: $Repeater[LongAgo]</td><td class=offline></td><td class=offline></td>";
            } else {
                ?><td class=online>ONLINE</td>
                <?=($Repeater[Ts1Linked])?"<td class=online>LINKED" : "<td class=unlinked>LOCAL</td>";?>
                <?=($Repeater[Ts2Linked])?"<td class=online>LINKED" : "<td class=unlinked>LOCAL</td>";}?><tr><? 
            $i++;
        }?><?
    }?>
    
    </table>
    <br /><?
}?>
    </div>
   </div>
  </div>
  <div id="footer" class="fixed"><a href="https://github.com/KD8EYF/OpenIPSC">OpenIPSC DMR Monitor</a><div id="credits">&copy 2012 KD8EYF</div></div>
</body>
</html>
