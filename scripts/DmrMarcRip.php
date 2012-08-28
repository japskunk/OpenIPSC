#!/usr/bin/php
<?php
//
//DmrMarcRip.php - utility to Download the DMR-MARC DMR database
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
//----------------------------------CHANGE THESE--------------------------------------------------------------//
include '/home/kd8eyf/include/dmrdbwrite.inc';                             //Add this file for database creds.                              
$UserUrl = "http://www.n6dva.org/trbo-database/trbo_users_view.php?SearchString=&Print.x=67&Print.y=3&nav_menu=%23&SortField=&SelectedID=&SortDirection=&FirstRecord=1";
$RepeaterUrl = "http://www.n6dva.org/trbo-database/trbo_repeaters_view.php?SearchString=&Print.x=67&Print.y=3&nav_menu=%23&SortField=&SelectedID=&SortDirection=&FirstRecord=1";
$PrefixUrl = "http://www.n6dva.org/trbo-database/trbo_radio_id_scheme_view.php?SearchString=&Print.x=67&Print.y=3&nav_menu=%23&SortField=&SelectedID=&SortDirection=&FirstRecord=1";
$TalkgroupUrl = "http://www.dmr-marc.net/TG.html";
$RepeaterFieldNames =   array('DmrID','Callsign','City','State','Country','Frequency','Offset','Assigned','Linked','Trustee','IpscNetwork');
$UserFieldNames =       array('DmrID','Callsign','Name','City','State','Country','Radio','HomeRepeaterID','Remarks');
$PrefixFieldNames =     array('DmrID','Country','State','Group');
$TalkgroupFieldNames =  array('Network','Region','DmrID','Assignment','Notes');
//----------------------------------DONT CHANGE THESE---------------------------------------------------------//

libxml_use_internal_errors(true);                                          //DONT WARN ABOUT DOM PARSING
date_default_timezone_set('UTC');                                          //SET TIMEZONE TO UTC
$DateTime = date('Y-m-d H:i:s',time());                                    //SET DATETIME STAMP TO SQL COMPATABILE 
$hostname = exec("hostname -f");                                           //GET SYSTEM HOSTNAME

$OldUser = Array();
$OldPrefix = Array();
$OldRepeater = Array();       
$OldTalkgroup = Array();                                                        

$NewUser = RipData($UserUrl,$UserFieldNames,"0");                          //RIP SUBSCRIBER DATA
$NewRepeater = RipData($RepeaterUrl,$RepeaterFieldNames,"0");              //RIP REPEATER DATA
$NewPrefix = RipData($PrefixUrl,$PrefixFieldNames,"0");                    //RIP PREFIX DATA
$NewTalkgroup = RipData($TalkgroupUrl,$TalkgroupFieldNames,"2");
$OldUser = GetOldData("User",$UserFieldNames);                             //GET CURRENT SUBSCRIBERS
$OldRepeater = GetOldData("Repeater",$RepeaterFieldNames);                 //GET CURRENT REPEATERS
$OldPrefix = GetOldData("Prefix",$PrefixFieldNames);                       //GET CURRENT PREFIXE
$OldTalkgroup = GetOldData("Talkgroup",$TalkgroupFieldNames);        

$AddUser =      array_diff_key($NewUser,$OldUser);                         //CALCULATE WHAT USERS ARE NEW
$AddRepeater =  array_diff_key($NewRepeater,$OldRepeater);                 //CALCULATE WHAT REPEATERS ARE NEW
$AddPrefix =    array_diff_key($NewPrefix,$OldPrefix);                     //CALCULATE WHAT PREFIXS ARE NEW
$AddTalkgroup = array_diff_key($NewTalkgroup,$OldTalkgroup);     

$DelUser =      array_diff_key($OldUser,$NewUser);                         //CALCULATE WHAT USERS ARE REMOVED
$DelRepeater =  array_diff_key($OldRepeater,$NewRepeater);                 //CALCULATE WHAT REPEATERS WHERE REMOVED
$DelPrefix =    array_diff_key($OldPrefix,$NewPrefix);                     //CALCULATE WHAT PREFIXS ARE REMOVED
$DelTalkgroup = array_diff_key($OldTalkgroup,$NewTalkgroup);  

$BothUser =     array_intersect_key($NewUser,$OldUser);                    //CALCULATE WHAT USERS EXISIT IN BOTH
$BothRepeater = array_intersect_key($NewRepeater,$OldRepeater);            //CALCULATE WHAT REPEATERS EXISIT IN BOTH
$BothPrefix =   array_intersect_key($NewPrefix,$OldPrefix);                //CALCULATE WHAY PREFIXS EXISIT IN BOTH
$BothTalkgroup =array_intersect_key($NewTalkgroup,$OldTalkgroup);


DBAdd($AddUser,"User");                                                    //INSERT ADDITIONS TO SUBSCRIBER TABLE
DBAdd($AddRepeater,"Repeater");                                            //INSERT ADDITIONS TO REPEATERS TABLE    
DBAdd($AddPrefix,"Prefix");                                                //INSERT ADDITIONS TO PREFIXES TABLE
DBAdd($AddTalkgroup,"Talkgroup");                                          //INSERT ADDITIONS TO PREFIXES TABLE

DBChange($OldUser,$NewUser,$BothUser,"User");                              //MAKE CHANGED TO SUBSRIBER TABLE
DBChange($OldRepeater,$NewRepeater,$BothRepeater,"Repeater");              //MAKE CHANGES TO REPETAER TABLE
DBChange($OldPrefix,$NewPrefix,$BothPrefix,"Prefix");                      //MAKE CHANGES TO PREFIX TABLE
DBChange($OldTalkgroup,$NewTalkgroup,$BothTalkgroup,"Talkgroup");
DBRemove($DelUser,"User");                                                 //REMOVE DELETED SUBSCRIBERS FROM TABLE
DBRemove($DelRepeater,"Repeater");                                         //REMOVE DELETED REPEATERS FROM TABLE
DBRemove($DelPrefix,"Prefix");                                             //REMOVE DELETED PREFIXES FROM TABLE
DBRemove($DelTalkgroup,"Talkgroup"); 


print "DAILY DMR-MARC DB UPDATE ON: ".$hostname."\n";

function BuildSqlRemove($Array,$Table,$Where) {
     $Query = "DELETE FROM `".$Table."` WHERE ".$Where;
     return ($Query);
}

function BuildSqlInsert($Array,$Table) {
     $Key = array_keys($Array);
     $Value = array_values($Array);
     $Query = "INSERT INTO `".$Table."` (`".implode('`,`',$Key)."`) "."VALUES ('".implode("', '",$Value)."')";
     return ($Query);
}

function BuildSqlUpdate($Array,$Table,$Where) {
     foreach ($Array as $Key => $Value) {
          $Query = $Query."`".$Key."` = '".$Value."', ";
     }
     $Query = trim($Query,", ");
     $Query = "UPDATE `".$Table."` SET ".$Query." WHERE `".$Table."`.".$Where;
     return ($Query);
}
function LogChange($NewItem,$ChangeType, $RecordType)                      //LOG CHANGES TO THE CHANGE LOG
{
    global $DateTime;
     switch ($ChangeType) {
          case "NEW":
               foreach ($NewItem as $NewKey => $NewValue) {
                    $NewQuery = "INSERT INTO `ChangeLog` (`DateTime`,`DmrID`,`RecordType`,`FieldName`,`OldValue`,`NewValue`,`Event`) VALUES ('$DateTime','$NewItem[DmrID]','$RecordType','$NewKey','','$NewValue','ADD');";
                    mysql_query($NewQuery) or die(mysql_error());
               }
               break;
          case "DELETE":
               foreach ($NewItem as $NewKey => $NewValue) {
                    $DelQuery = "INSERT INTO `ChangeLog` (`DateTime`,`DmrID`,`RecordType`,`FieldName`,`OldValue`,`NewValue`,`Event`) VALUES ('$DateTime','$NewItem[DmrID]','$RecordType','$NewKey','$NewValue','','DELETE');";
                    mysql_query($DelQuery) or die(mysql_error());
               }
               break;
          case "CHANGE":
               $DiffQuery = "INSERT INTO `ChangeLog` (`DateTime`,`DmrID`,`RecordType`,`FieldName`,`OldValue`,`NewValue`,`Event`) VALUES ('$DateTime','$NewItem[DmrID]','$RecordType','$NewItem[FieldName]','$NewItem[OldValue]','$NewItem[NewValue]','CHANGE');";

               mysql_query($DiffQuery) or die(mysql_error());
               break;
     }
}
function RipData($Url,$ColumnNames,$key) {
     $Dom = new DOMDocument();
     $doctype = DOMImplementation::createDocumentType("html","-//W3C//DTD XHTML 1.1//EN","http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd");
     $Dom->appendChild($doctype);
     $Page = $Dom->loadHTMLFile($Url);                                     //CRAEATE NEW DOM OBJECT
     $Dom->preserveWhiteSpace = false;                                     //DELETE HTML WHITE SPACES
     $Table = $Dom->getElementsByTagName('table');                         //IDENTIFY START OF TABLE
     $Rows = $Table->item(0)->getElementsByTagName('tr');                  //IDENTIFY ROWS
      foreach ($Rows as $row) {                                            //START A LOOP FOR EACH ROW
          $cols = $row->getElementsByTagName('td');                        //GET THE COLUMNS
          if (is_numeric($cols->item($key)->textContent)) {                //IS THIS AN ITEM THAT WE WANT TO PROCESS
               $i = 0;
               foreach ($cols as $col) {                                   //LOOP THROUGH EACH COL AND STORE IN ARRAY
                    $array[$cols->item($key)->nodeValue][$ColumnNames[$i]] =str_replace("NBSP","",strtoupper(trim(preg_replace("/[^0-9a-zA-Z .-]/","",$col->textContent))));
                    $i++;
               }
          }
     }
     return $array;
}
function GetOldData($TableName,$FieldNames) {                              //GET DATA FROM YESTERDAYS DMR-MARC DATABASE
     $Query = "SELECT * FROM `".$TableName."`;";
     $Result = mysql_query($Query) or die("Error in query: $query ".mysql_error());
     $NumResults = mysql_num_rows($Result);
     if ($NumResults > 0) {
          while ($row = mysql_fetch_array($Result,MYSQL_ASSOC)) {
               foreach ($FieldNames as $FieldName) {
                    $array[$row[DmrID]][$FieldName] = $row[$FieldName];     //AM I OVERTHINKING THIS?
               }
          }
     }
     if (is_null($array))
     {
        return array();
     }
        else
     {
        return $array;
     }
}

function DBChange($Old,$New,$Both,$Table){                                 //CHANGE AN ITEM IN THE DATABASE
     foreach ($Both as $DiffRow) {
          $Diff = array_diff($New[$DiffRow[DmrID]],$Old[$DiffRow[DmrID]]);
          if ($Diff) {
               $ChangeQuery = BuildSqlUpdate($DiffRow,$Table,"`DmrID`='".$DiffRow[DmrID]."'")."\n";
               print $Query."\n";
               mysql_query($ChangeQuery) or die(mysql_error());
               foreach ($Diff as $DifferentItemKey => $DifferentItemVal) {
                    $DiffArray = array('DmrID' => $DiffRow[DmrID],'FieldName' => $DifferentItemKey,
                         'OldValue' => $DifferentItemVal,'NewValue' => $Old[$DiffRow[DmrID]][$DifferentItemKey]);
                    LogChange($DiffArray,"CHANGE",$Table);
               }
          }
     }
}
function DBAdd($new,$table) {                                              //ADD A ITEM TO THE DATABASE
     if (count($new) != false) {
          foreach ($new as $AddRow) {
               $Query = BuildSqlInsert($AddRow,$table)."\n";
               mysql_query($Query) or die(mysql_error());
               LogChange($AddRow,"NEW",$table);
          }
     }
}
function DBRemove($del,$table) {                                           //REMOVE A ITEM FROM TEH DATABASE
     if (count($del) != false) {
          foreach ($del as $DelRow) {
               $Where = "`DmrID`='".$DelRow[DmrID]."'";
               $Query = BuildSqlRemove($DelRow,$table,$Where);
               mysql_query($Query) or die(mysql_error());
               LogChange($DelRow,"DELETE",$table);
          }
     }
}

?>

