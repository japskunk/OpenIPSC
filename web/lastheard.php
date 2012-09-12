<?php 
//
//lastheard.php - render webpage of dmr lasthead list 
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
		<div class="nav">
			<ul>
			  <li><a href="netstatus.php">NetStatus</a></li>
              <li><a href="lastheard.php" class="active">LastHeard</a></li>		
			  <li><a href="calllog.php">Call Log</a></li>
			  </div>
			  </ul>
		</div>
	</div>
<div id="content" class="fixed">
    <div id="maincontent">
        <h2>DMR Last Heard List</h2>
        <table  width="100%" border="0" cellspacing="0" >
                <tr>
                    <td colspan=10 class="networkheader"><? echo $SourceNet[Description]; ?></td>
                </tr>
                <tr>
                    <th>Date</th>
                    <th>Long Ago</th>
                    <th>User</th>
                    <th>Repeater</th>
                    <th>Destination</th>
                    <th>Slot</th>
                    <th>Network</th>
                    <th>Aud</th>
                    <th>Type</th>
                <tr>
        <? 
        include '/usr/local/include/dmrdb.inc' ;
        date_default_timezone_set( 'UTC' ) ;
        $Date = date( 'l F jS, Y', time() ) ;
        $DateTime = date( 'd M y, H:i:s', time() ) ;
        $Query = "SELECT UserLog.StartTime AS StartTime, UserLog.DmrID AS DmrID, User.Callsign AS UserCallsign, Network.Description AS NetworkDescription, User.Name AS UserName, UserLog.RepeaterID AS RepeaterID, Repeater.Short AS Short, Repeater.City AS RepeaterCity, Repeater.CallSign AS RepeaterCallsign, UserLog.DestinationID   AS DestinationID, UserLog.SourceNet AS SourceNet, UserLog.TimeSlot AS TimeSlot, UserLog.GroupCall AS GroupCall, UserLog.PrivateCall AS PrivateCall, UserLog.VoiceCall AS VoiceCall, UserLog.DataCall AS DataCall, Talkgroup.Assignment AS Talkgroup FROM UserLog LEFT JOIN Network ON (UserLog.SourceNET = Network.DmrID) LEFT JOIN User ON (UserLog.DmrID = User.DmrID ) LEFT JOIN Repeater ON (UserLog.RepeaterID = Repeater.DmrID ) LEFT JOIN Talkgroup ON (UserLog.DestinationID = Talkgroup.DmrID) GROUP BY DmrID ORDER BY StartTime DESC LIMIT 30;" ;
        mysql_query( $Query ) or die( "MYSQL ERROR:" . mysql_error() ) ;
        $Result = mysql_query( $Query ) or die( mysql_errno . " " . mysql_error() ) ;
        while ( $Event = mysql_fetch_array( $Result ) ) {
                $Audience = ""; $Type="";
                if (($Event[Talkgroup]=="")) {$Talkgroup = $Event[DestinationID];} else {$Talkgroup =$Event[Talkgroup]; }
                $LongAgo = ( strtotime( "now" ) - strtotime( $Event[LastHeard] ) );
                if ($Event[GroupCall] = 1) $Audience = "GROUP";
                if ($Event[PrivateCall] = 1) $Audience = "PRIVATE";
                if ($Event[Voice] = 1) $Type = "VOICE";
                if ($Event[Data] = 1) $Type = "DATA";
                if ( $i % 2 != 0 ) $RowClass = "odd" ; else  $RowClass = "even" ;
                if (is_null($Event[RepeaterCity])) {$Repeater = $Event[Short];} else {$Repeater = $Event[RepeaterCallsign].str_repeat('&nbsp',(7-strlen($Event[RepeaterCallsign]))).$Event[RepeaterCity];}
                $LongAgo = duration( strtotime( "now" ) - strtotime( $Event[StartTime] ) ) ;
                                        
                echo "<tr>";
                echo "<td nowrap class=$RowClass>$Event[StartTime]</td>" ;
                echo "<td nowrap class=$RowClass>$LongAgo</td>" ;
                echo "<td nowrap class=$RowClass>$Event[UserCallsign]".str_repeat('&nbsp',(7-strlen($Event[UserCallsign])))." $Event[UserName]</td>" ;
                echo "<td nowrap class=$RowClass>$Repeater</td>" ;
                echo "<td nowrap class=$RowClass>$Talkgroup</td>" ;
                echo "<td nowrap class=$RowClass>$Event[TimeSlot]</td>" ;
                echo "<td nowrap class=$RowClass>$Event[NetworkDescription]</td>" ;
                echo "<td nowrap class=$RowClass>$Audience</td>" ;
                echo "<td nowrap class=$RowClass>$Type</td>" ;
                echo "</tr>";
                
		$i++ ;	
		}
		echo "</tr>" ;
		
	echo "</table>" ;
	echo "<br />" ;?>
    
    </div>
   </div>
  </div>
 <div id="footer" class="fixed">
		<p class="credits">
            UNDER CONSTRUCTION  UNDER CONSTRUCTION  UNDER CONSTRUCTION  UNDER CONSTRUCTION  UNDER CONSTRUCTION  
		 </p>				   
	</div>
</body>
</html>
<?
function duration( $seconds )
{
	$days = floor( $seconds / 60 / 60 / 24 ) ;
	$hours = $seconds / 60 / 60 % 24 ;
	$mins = $seconds / 60 % 60 ;
	$secs = $seconds % 60 ;
	$duration = '' ;
	if ( $days > 0 ) {
		$duration = "$days"."D " ;
	} elseif ( $hours > 0 ) $duration .= "$hours" . "H " ;
	if ( $mins > 0 ) $duration .= "$mins" . "M " ;
	if ( ( $secs > 0 ) && ( $hours < 1 ) && ( $mins < 10 ) ) $duration .= "$secs" .
			"S " ;
	$duration = trim( $duration ) ;
	if ($seconds >= 365*24*60) {$duration = "NEVER";};
    if ( $duration == null ) $duration = '0' . 'S' ;
    if ($seconds >= 1000000000) $duration = "NEVER";
	return $duration ;}?>