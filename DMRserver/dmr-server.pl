#!/usr/bin/perl
use IO::Socket::INET;
use DBI;
my ($socket,$rdata);
my ($peeraddress,$peerport);
$socket = new IO::Socket::INET (LocalPort => 50000,Proto => 'udp',) or die "ERROR in Socket Creation : $!\n";
$err = 0;
while($err == 0) {
	$SqlConn ||= DBI->connect("DBI:mysql:database=dmrdb:host=localhost", dmruser) or die "Can't connect to database: $DBI::errstr\n";
	$Frame = $socket->getline();
	print $Frame;
	($Date,$Time,$SourceNet,$PacketType) = split(/ /,$Frame);
	$DateTime = $Date . " " . $Time;
  	$PacketType = hex($PacketType);
	if (($PacketType ge 150) && ($PacketType le 153)){	#MOTOROLA HEARTBEAT
		($DmrID,$TimeSlotHex) = split(/ /,substr($Frame,32));
		$Ts1Online = "0";
		$Ts2Online = "0";
        	if(hex(substr($TimeSlotHex,1,1)) & 2) { $Ts2Online = 1; }
	        if(hex(substr($TimeSlotHex,1,1)) & 8) { $Ts1Online = 1; }
    		$Query = "INSERT INTO `RepeaterLog` (`DmrID`, `SourceNet`, `DateTime`, `Ts1Online`, `Ts2Online`, `PacketType`, `TimeSlotRaw`) VALUES ('$DmrID','$SourceNet','$DateTime','$Ts1Online','$Ts2Online','$PacketType','$TimeSlotHex');";
		$Statement = $SqlConn->prepare($Query);
                $Statement->execute();
	}
	if (($PacketType ge 128) && ($PacketType le 132)) {	#MOTOROLA VOICE / DATA
		($RepeaterID,$Sequence,$DmrID,$DestinationID,$o12,$o13,$o14,$o15,$o16,$o17,$o18) = split(/ /,substr($Frame,32));		
		$TimeSlot=1;$Group=0;$Private=0;$Final=0;$Data=0;$Voice=0;$Private=0;
        	if(hex(substr($o17,0,1)) & 2) { $TimeSlot = 2; }
	        if(hex(substr($o17,0,1)) & 4) { $Final = 1;}
	        if(hex(substr($o12,1,1)) & 1) { $DataCall = 1; }
       		if(hex(substr($o12,1,1)) & 2) { $VoiceCall = 1; }
		if($PacketType eq 128){ $GroupCall = 1 };
		if($PacketType eq 132){ $PrivateCall = 1 }; 	
		$Query = "INSERT INTO `UserLog` (`Key`, `StartTime`, `EndTime`,`SourceNet`, `PacketType`, `RepeaterID`, `DmrID`, `DestinationID`, `Sequence`, `TimeSlot`, `GroupCall`, `PrivateCall`, `VoiceCall`, `DataCall`, `Raw`) VALUES (CRC32('$SourceID$RepeaterID$Sequence$SourceNet'),'$DateTime','$DateTime','$SourceNet', '$PacketType', '$RepeaterID', '$DmrID', '$DestinationID', '$Sequence', '$TimeSlot','$GroupCall','$PrivateCall','$VoiceCall','$DataCall','$Raw') ON DUPLICATE KEY UPDATE EndTime='$DateTime';";
		$Statement = $SqlConn->prepare($Query);
                $Statement->execute();
	}
}
$dbh->close;
$socket->close();
exit;
