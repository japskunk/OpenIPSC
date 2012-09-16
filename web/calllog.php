<?php 
//
//calllog.php - render webpage of dmr repeater status 
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
			</a>
		</div>
            <div class="nav">
                <ul>
                    <li><a href="netstatus.php">NetStatus</a></li>
                    <li><a href="lastheard.php">LastHeard</a></li>		
                    <li><a href="calllog.php"class="active" >Call Log</a></li>
                    <li>
                    <a href="users.php" >Users</a>
                </li>
                </ul>
            </div>
        </div>
        <div id="content" class="fixed">
            <div id="maincontent">
                <h2>Call Log</h2>
                <table  width="100%" border="0" cellspacing="0" >
                <tr>
                    <th>Date</th>
                    <th>LongAgo</th>
                    <th>Radio ID</th>
                    <th>Callsign</th>
                    <th>Name</th>
                    <th>Repeater</th>
                    <th>Rep ID</th>
                    <th>Call</th>
                    <th>Group</th>
                    <th>Net</th>
                    <th>Slot</th>
                    <th>Aud</th>
                    <th>Type</th>
                <tr>
                <? 
include '/usr/local/include/dmrdb.inc' ;
date_default_timezone_set( 'UTC' ) ;
$Date = date( 'l F jS, Y', time() ) ;
$DateTime = date( 'd M y, H:i:s', time() ) ;
$Query = "SELECT UserLog.StartTime AS StartTime, UserLog.DmrID AS DmrID, User.Callsign AS UserCallsign, User.Name AS UserName, UserLog.RepeaterID AS RepeaterID, Repeater.City AS RepeaterCity, Repeater.CallSign AS RepeaterCallsign, UserLog.DestinationID AS DestinationID, UserLog.SourceNet AS SourceNet, UserLog.TimeSlot AS TimeSlot, UserLog.GroupCall AS GroupCall, UserLog.PrivateCall AS PrivateCall, UserLog.VoiceCall AS VoiceCall, UserLog.DataCall AS DataCall, Talkgroup.Assignment AS Talkgroup FROM UserLog LEFT JOIN User ON (UserLog.DmrID = User.DmrID ) LEFT JOIN Talkgroup ON (UserLog.DestinationID = Talkgroup.DmrID) LEFT JOIN Repeater ON (UserLog.RepeaterID = Repeater.DmrID ) ORDER BY StartTime DESC LIMIT 30;" ;
mysql_query( $Query ) or die( "MYSQL ERROR:" . mysql_error() ) ;
$Result = mysql_query( $Query ) or die( mysql_errno . " " . mysql_error() ) ;
while ( $Event = mysql_fetch_array( $Result ) ) {
    $Talkgroup =   (is_null($Event[Talkgroup])?$Event[DestinationID]:$Event[Talkgroup]);
    $Audience=     ($Event[GroupCall] == 1?"GROUP":"PRIVATE");
	$Type =        ($Event[VoiceCall] == 1?"VOICE":"DATA");
	$RowClass =    (($i % 2 != 0)?"odd":"even");
	$Repeater =    (is_null($Event[RepeaterCity]))?$Event[Short]:$Event[RepeaterCallsign].str_repeat('&nbsp',(7-strlen($Event[RepeaterCallsign]))).$Event[RepeaterCity];
	$LongAgo =     (duration(strtotime("now")-strtotime($Event[StartTime])));?>
                <tr>
                <td nowrap class=<?=$RowClass?>><?=$Event[StartTime]?></td>
                <td nowrap class=<?=$RowClass?>><?=$LongAgo?></td>
                <td nowrap class=<?=$RowClass?>><?=$Event[DmrID]?></td>
                <td nowrap class=<?=$RowClass?>><?=$Event[UserCallsign]?></td>
                <td nowrap class=<?=$RowClass?>><?=$Event[UserName]?></td>
                <td nowrap class=<?=$RowClass?>><?=$Repeater?></td>
                <td nowrap class=<?=$RowClass?>><?=$Event[RepeaterID]?></td>
                <td nowrap class=<?=$RowClass?>><?=$Event[RepeaterCallsign]?></td>
                <td nowrap class=<?=$RowClass?>><?=$Talkgroup?></td>
                <td nowrap class=<?=$RowClass?>><?=$Event[SourceNet]?></td>
                <td nowrap class=<?=$RowClass?>><?=$Event[TimeSlot]?></td>
                <td nowrap class=<?=$RowClass?>><?=""?></td>
                <td nowrap class=<?=$RowClass?>><?=""?></td>
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
        <div id="credits">&copy 2012 KD8EYF
        </div>
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