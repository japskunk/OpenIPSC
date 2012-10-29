<?php 
//lastheard.php - render webpage of dmr lasthead list
//
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
            <img src="logo.png" border="0" width="300" height="75" alt="logo" /></div>
		</a>
        <div class="nav">
            <ul>
                <li>
                    <a href="netstatus.php">NetStatus</a>
                </li>
                <li>
                    <a href="lastheard.php" class="active">LastHeard</a>
                </li>
                <li>
                    <a href="calllog.php">Call Log</a>
                </li>
                <li>
                    <a href="users.php">Users</a>
                </li>
            </ul>
        </div>
    </div>
    <div id="content" class="fixed"> 
        <div id="maincontent">
            <h2>Last Heard List</h2>
            
            <table  width="100%" border="0" cellspacing="0" >
                <tr>
                    <th>Date</th>
                    <th>Long Ago</th>
                    <th>User</th>
                    <th>Repeater</th>
                    <th>Destination</th>
                    <th>Slot</th>
                    <th>Network</th>
                <tr>
                <? 
include '/usr/local/include/dmrdb.inc' ;
date_default_timezone_set( 'UTC' ) ;
$Date = date( 'l F jS, Y', time() ) ;
$DateTime = date( 'd M y, H:i:s', time() ) ;

$Query = "
	SELECT 
		 `LastHeard`.`DmrID`
		,`LastHeard`.`StartTime`
		,`LastHeard`.`SourceNet`
		,`LastHeard`.`TimeSlot`
		,`LastHeard`.`RepeaterID` 
		,`LastHeard`.`DestinationID`
   		,`User`.`Callsign` 		AS `UserCallsign`
   		,`User`.`Name`     		AS `UserName`
   		,`Network`.`Description`     AS `NetworkDescription`
   		,`Repeater`.`Short`          AS `Short`
   		,`Repeater`.`City`           AS `RepeaterCity`
   		,`Repeater`.`CallSign`       AS `RepeaterCallsign`
   		,`Talkgroup`.`Assignment`    AS `Talkgroup`
	FROM 
		`LastHeard`
	LEFT JOIN   
		`User` 
	ON          
		`LastHeard`.`DmrID` = `User`.`DmrID`
	LEFT JOIN   
		`Network`
	ON          
		`LastHeard`.`SourceNet` = `Network`.`DmrID`
	LEFT JOIN   
		`Repeater` 
	ON          
		`LastHeard`.`RepeaterID` = `Repeater`.`DmrID`
	LEFT JOIN   
		`Talkgroup` 
	ON          
		`LastHeard`.`DestinationID` = `Talkgroup`.`DmrID`
	WHERE
                `LastHeard`.`RepeaterID`
        LIKE
                '______'
	ORDER BY  `LastHeard`.`StartTime` DESC";

mysql_query( $Query ) or die( "MYSQL ERROR:" . mysql_error() ) ;
$Result = mysql_query( $Query ) or die( mysql_errno . " " . mysql_error() ) ;
while ( $Event = mysql_fetch_array( $Result ) ) {
	$Talkgroup =   (is_null($Event[Talkgroup])?$Event[DestinationID]:$Event[Talkgroup]);
	$UserName =    (is_null($Event[UserName]))?$Event[DmrID]:$Event[UserCallsign].str_repeat('&nbsp',(7-strlen($Event[UserCallsign]))).$Event[UserName];
	$Audience=     ($Event[GroupCall] == 1?"GROUP":"PRIVATE");
	$Type =        ($Event[VoiceCall] == 1?"VOICE":"DATA");
	$RowClass =    (($i % 2 != 0)?"odd":"even");
	$Repeater =    (is_null($Event[RepeaterCity]))?$Event[RepeaterID]:$Event[RepeaterCallsign].str_repeat('&nbsp',(7-strlen($Event[RepeaterCallsign]))).$Event[RepeaterCity];
	$LongAgo =     (duration(strtotime("now")-strtotime($Event[StartTime])));?>
                <tr>
                    <td nowrap class=<?=$RowClass?>><?=$Event[StartTime]?></td>
                    <td nowrap class=<?=$RowClass?>><?=$LongAgo?></td>
                    <td nowrap class=<?=$RowClass?>><?=$UserName?></td>
                    <td nowrap class=<?=$RowClass?>><?=$Repeater?></td>
                    <td nowrap class=<?=$RowClass?>><?=$Talkgroup?></td>
                    <td nowrap class=<?=$RowClass?>><?=$Event[TimeSlot]?></td>
                    <td nowrap class=<?=$RowClass?>><?=$Event[NetworkDescription]?></td>
                </tr>
                <?
    $i++ ;
} ?>
            </table>
            <br />
        </div>
    </div>
    <div id="footer" class="fixed">
        <a href="https://github.com/KD8EYF/OpenIPSC">OpenIPSC DMR Monitor</a>
        <div id="credits">&copy 2012 KD8EYF</div>
    </div>    
    </body>
</html>
<? 
function duration( $seconds ){
	$days = floor( $seconds / 60 / 60 / 24 ) ;
	$hours = $seconds / 60 / 60 % 24 ;
	$mins = $seconds / 60 % 60 ;
	$secs = $seconds % 60 ;
	$duration = '' ;
	if ( $days > 0 ) {
		$duration = "$days" . "D " ;
	} elseif ( $hours > 0 ) $duration .= "$hours" . "H " ;
	if ( $mins > 0 ) $duration .= "$mins" . "M " ;
	if ( ( $secs > 0 ) && ( $hours < 1 ) && ( $mins < 10 ) ) $duration .= "$secs" . "S " ;
	$duration = trim( $duration ) ;
	if ( $seconds >= 365 * 24 * 60 ) { $duration = "NEVER" ; }	;
	if ( $duration == null ) $duration = '0' . 'S' ;
	if ( $seconds >= 1000000000 ) $duration = "NEVER" ;
	return $duration ;}
?>
