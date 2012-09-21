#!/usr/bin/perl
#
#     DAVID KIERZKOWSKI, KD8EYF
#     13287 Teak Ct.
#     Sterling Heights, MI 48312
#
# (C) Hans-J. Barthen, DL5DI
#     Franz-Josef-Str. 20
#     D-56642 Kruft
#
#       RAW UDP VOICE PAYLOAD HEX OFFSET- THANKS 2 DL5DI
#       OFFSET: 0       USE: Format ID L:A/GI
#       OFFSET: 1-4     USE: Repeater ID
#       OFFSET: 5       USE: Sequence Number
#       OFFSET: 6-8     USE: Source ID
#       OFFSET: 9-11    USE: DestinationID
#       OFFSET: 12      USE: Priority Voice / Data
#       OFFSET: 13-16   USE: Call Control
#       OFFSET: 17      USE: Call Control Info
#       OFFSET: 18      USE: Controb Source ID
#       OFFSET: 17b32   USE: Timeslot
#       OFFSET: 19      USE: Payload Type
#       OFFSET: 20-21   USE: Sequence Number
#       OFFSET: 22-25   USE: TimeStamp
#       OFFSET: 26-29   USE: SyncSrcID
#       OFFSET: 30      USE: DataTypeVoiceHeader
#       OFFSET: 31      USE: RSSI/threshold and parity values
#       OFFSET: 32-33   USE: length to follow (words)
#       OFFSET: 34      USE: RSSI Status
#       OFFSET: 35      USE: Slot Type Sync
#       OFFSET: 36-37   USE: Data Size

# 0         1         2         3
# 012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234
# 2012-09-16 23:17:25 50201 98 312601 6a
# 2012-09-16 23:11:15 50201 80 312601 145 3126002 2 02 00004e00 20 80 dd 64761 1183fed8 0  01 80 11 84 0a 96
# 1          2        3     4  5      6   7       8 9  10       11 12    13    14       15 16 17 18 19 20 21

#       DL5DI DMR-MONITOR.pl VOICE STRING
#       POSITION:1      USE: DATE
#       POSITION:2      USE: TIME
#       POSITION:3      USE: Source Network
#       POSITION:4      USE: PAcket Type
#       POSITION:5      USE: Source Repeater ID
#       POSITION:6      USE: Call Sequence Number
#       POSITION:7      USE: Destination ID
#       POSITION:8      USE: Prio - Voice / Data ?
#       POSITION:9      USE: Flow Control Flags
#       POSITION:10     USE: CallControlInfo
#       POSITION:11     USE: ContribSrcID
#       POSITION:12     USE: Payload Type
#       POSITION:13     USE: Sequence Number
#       POSITION:14     USE: TimeStamp
#       POSITION:15     USE: SyncSrcID
#       POSITION:16     USE: DataTypeVoiceHeader
#       POSITION:17     USE: RSSI Threshold and Parity Values
#       POSITION:18     USE: Length in words to follow
#       POSITION:19     USE: Rssi Status
#       POSITION:20     USE: Slot Type
#       POSITION:21     USE: Data Size
use IO::Socket::INET;                   #Load Network module
use DBI;                                #load db module
my ($socket,$rdata);
my ($peeraddress,$peerport);
$socket = new IO::Socket::INET (LocalPort => 50000,Proto => 'udp',) or die "ERROR in Socket Creation : $!\n";   #open UDP port
$err = 0;
while($err == 0) {
        $SqlConn ||= DBI->connect("DBI:mysql:database=dmrdb:host=localhost", "dmruser") or die "Can't connect to database: $DBI::errstr\n";  #connect to db
        $Frame = $socket->getline();    #store new line in a Frame
        #print $Frame;
	($Date,$Time,$SourceNet,$PacketTypeHex) = split(/ /,$Frame);       #Get The timestamp network and type of packet for pre processing
        $DateTime = $Date . " " . $Time;                                #assemble timestamp
        $PacketType = hex($PacketTypeHex);                                 #decode packet type from hex to dec
        if (($PacketType ge 150) && ($PacketType le 153)){              #looks like a MOTOROLA HEARTBEAT
                ($DmrID,$TimeSlotHex) = split(/ /,substr($Frame,29));   #pull out the timeslot status
                $Ts1Online = "0";                                       #
                $Ts2Online = "0";
                if(hex(substr($TimeSlotHex,1,1)) & 2) { $Ts2Online = 1; }
                if(hex(substr($TimeSlotHex,1,1)) & 8) { $Ts1Online = 1; }
                printf "HB TYP:%s TS1:%s TS2:%s RTD: %08d SRN: %05d\n",$PacketType, $Ts1Online, $Ts2Online, $DmrID, $SourceNet;
                $Query = "INSERT INTO `Network` (`DmrID`,`Description`,`Publish`,`DateTime`) VALUES ('$SourceNet', 'UNKNOWN - $SourceNet', '0', '$DateTime') ON DUPLICATE KEY UPDATE DateTime='$DateTime';";
		 $Statement = $SqlConn->prepare($Query);
                $Statement->execute();
		$Query = "INSERT INTO `RepeaterLog` (`DmrID`, `SourceNet`, `DateTime`, `Ts1Online`, `Ts2Online`, `PacketType`, `TimeSlotRaw`) VALUES ('$DmrID','$SourceNet','$DateTime','$Ts1Online','$Ts2Online','$PacketType','$TimeSlotHex');";
		$Statement = $SqlConn->prepare($Query);
                $Statement->execute();
		$Query = "INSERT INTO `Repeater` (`DmrID`, `SourceNet`, `LastHeard`, `Ts1Online`, `Ts2Online`) VALUES ('$DmrID','$SourceNet','$DateTime','$Ts1Online','$Ts2Online') ON DUPLICATE KEY UPDATE `LastHeard` = '$DateTime', `SourceNet` = '$SourceNet', `Ts1Online` = '$Ts1Online', `Ts2Online` = '$Ts2Online';";
        }
        	$Statement = $SqlConn->prepare($Query);
                $Statement->execute();
	if (($PacketType ge 128) && ($PacketType le 132)) {     #MOTOROLA VOICE / DATA
                ($RepeaterID,$Sequence,$DmrID,$DestinationID,$Priority,$FlowControlFlags,$CallControlInfo,$ContribSrcID,$PayloadType,$SeqNumber,$TimeStamp,$SyncSrcID,$DataType,$RssiThreshold,$Length,$RssiStatus,$SlotType,$DataSize) = split(/ /,substr($Frame,29));
                $TimeSlot=1;$Group=0;$Private=0;$Final=0;$Data=0;$Voice=0;$Private=0;$Answer=0;
                if(hex(substr($CallControlInfo,0,1)) & 2) { $TimeSlot = 2; }
                if(hex(substr($CallControlInfo,0,1)) & 4) { $Final = 1;}
               	if(hex(substr($PacketTypeHex,1,1)) & 4) { $Answer = 1;} 
		elsif(hex(substr($PacketTypeHex,1,1)) & 1) { $Private = 1;}
		else{ $Group = 1;}
		if(hex(substr($Priority,1,1)) & 1) { $Data = "1";}
		if(hex(substr($Priority,1,1)) & 2) { $Voice = "1";}
		printf "V/D TS:%s G:%s P:%s F:%s D:%s V:%s A:%s TYP:%s RID:%08d SEQ:%03d DMRID:%08d DST:%08d PRI:%s FCF:%s CCI:%s CSI:%s PLT:%s SQN:%05s TSP:%s SSI:%s DAT:%s RST:%s LEN:%02s RSS:%s SLT:%s DAS:%05s",$TimeSlot,$Group,$Private,$Final,$Data,$Voice,$Answer,$PacketType,$RepeaterID,$Sequence,$DmrID,$DestinationID,$Priority,$FlowControlFlags,$CallControlInfo,$ContribSrcID,$PayloadType,$SeqNumber,$TimeStamp,$SyncSrcID,$DataType,$RssiThreshold,$Length,$RssiStatus,$SlotType,$DataSize;
                $Query = "INSERT INTO `Network` (`DmrID`,`Description`,`Publish`,`DateTime`) VALUES ('$SourceNet', 'UNKNOWN - $SourceNet', '0', '$DateTime') ON DUPLICATE KEY UPDATE DateTime='$DateTime';";
                $Statement = $SqlConn->prepare($Query);
                $Statement->execute();
		$Query = "INSERT INTO `dmrdb`.`UserLog` (`Key`, `StartTime`, `EndTime`, `SourceNet`, `PacketType`, `RepeaterID`, `DmrID`, `DestinationID`, `Sequence`, `TimeSlot`, `GroupCall`, `PrivateCall`, `VoiceCall`, `DataCall`, `Priority`, `FlowControlFlags`, `CallControlInfo`, `ContribSrcID`, `PayloadType`, `SeqNumber`, `TimeStamp`, `SyncSrcID`, `DataType`, `RssiThreshold`, `Length`, `RssiStatus`, `SlotType`, `DataSize`) VALUES (MD5('$Date$SourceID$RepeaterID$Sequence$SourceNet$TimeSlot'),'$DateTime','0000-00-00 00:00:00','$SourceNet', '$PacketType', '$RepeaterID', '$DmrID', '$DestinationID', '$Sequence', '$TimeSlot','$GroupCall','$PrivateCall','$VoiceCall','$DataCall','$Priority','$FlowControlFlags','$CallControlInfo','$ContribSrcID','$PayloadType','$SeqNumber','$TimeStamp','$SyncSrcID','$DataType','$RssiThreshold','$Length','$RssiStatus','$SlotType','$DataSize') ON DUPLICATE KEY UPDATE EndTime='$DateTime';";
                $Statement = $SqlConn->prepare($Query);
                $Statement->execute();
        }
}
$dbh->close;
$socket->close();
exit;

