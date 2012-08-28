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
////////////////////////////////////////CHANGE THESE///////////////////////////////////////////////////////////
include '/home/kd8eyf/include/dmrdbwrite.inc';                                     //init database                              
$UserUrl = "http://www.n6dva.org/trbo-database/trbo_users_view.php?SearchString=&Print.x=67&Print.y=3&nav_menu=%23&SortField=&SelectedID=&SortDirection=&FirstRecord=1";
$RepeaterUrl = "http://www.n6dva.org/trbo-database/trbo_repeaters_view.php?SearchString=&Print.x=67&Print.y=3&nav_menu=%23&SortField=&SelectedID=&SortDirection=&FirstRecord=1";
$PrefixUrl = "http://www.n6dva.org/trbo-database/trbo_radio_id_scheme_view.php?SearchString=&Print.x=67&Print.y=3&nav_menu=%23&SortField=&SelectedID=&SortDirection=&FirstRecord=1";
$TalkgroupUrl = "http://www.dmr-marc.net/TG.html";
$RepeaterFieldNames =   array('DmrID','Callsign','City','State','Country','Frequency','Offset','Assigned','Linked','Trustee','IpscNetwork');
$UserFieldNames =       array('DmrID','Callsign','Name','City','State','Country','Radio','HomeRepeaterID','Remarks');
$PrefixFieldNames =     array('DmrID','Country','State','Group');
$TalkgroupFieldNames =  array('Network','Region','DmrID','Assignment','Notes');
////////////////////////////////////////DONT CHANGE THESE//////////////////////////////////////////////////////
libxml_use_internal_errors(true);                                           //DONT WARN ABOUT DOM PARSING
date_default_timezone_set('UTC');                                           //SET TIMEZONE TO UTC
$DateTime = date('Y-m-d H:i:s',time());                                     //SET DATETIME STAMP TO SQL COMPATABILE 
$hostname = exec("hostname -f");                                            //GET SYSTEM HOSTNAME

$OldUser = Array();
$OldPrefix = Array();
$OldRepeater = Array();       
$OldTalkgroup = Array();                                                        

$NewUser = RipData($UserUrl,$UserFieldNames,"0");                               //RIP SUBSCRIBER DATA
$NewRepeater = RipData($RepeaterUrl,$RepeaterFieldNames,"0");                   //RIP REPEATER DATA
$NewPrefix = RipData($PrefixUrl,$PrefixFieldNames,"0");                         //RIP PREFIX DATA
$NewTalkgroup = RipData($TalkgroupUrl,$TalkgroupFieldNames,"2");
$OldUser = GetOldData("User",$UserFieldNames);                              //GET CURRENT SUBSCRIBERS
$OldRepeater = GetOldData("Repeater",$RepeaterFieldNames);                  //GET CURRENT REPEATERS
$OldPrefix = GetOldData("Prefix",$PrefixFieldNames);                        //GET CURRENT PREFIXE
$OldTalkgroup = GetOldData("Talkgroup",$TalkgroupFieldNames);        

$AddUser =      array_diff_key($NewUser,$OldUser);                               //CALCULATE WHAT USERS ARE NEW
$AddRepeater =  array_diff_key($NewRepeater,$OldRepeater);                   //CALCULATE WHAT REPEATERS ARE NEW
$AddPrefix =    array_diff_key($NewPrefix,$OldPrefix);                         //CALCULATE WHAT PREFIXS ARE NEW
$AddTalkgroup = array_diff_key($NewTalkgroup,$OldTalkgroup);     

$DelUser =      array_diff_key($OldUser,$NewUser);                               //CALCULATE WHAT USERS ARE REMOVED
$DelRepeater =  array_diff_key($OldRepeater,$NewRepeater);                   //CALCULATE WHAT REPEATERS WHERE REMOVED
$DelPrefix =    array_diff_key($OldPrefix,$NewPrefix);                         //CALCULATE WHAT PREFIXS ARE REMOVED
$DelTalkgroup = array_diff_key($OldTalkgroup,$NewTalkgroup);  

$BothUser =     array_intersect_key($NewUser,$OldUser);                         //CALCULATE WHAT USERS EXISIT IN BOTH
$BothRepeater = array_intersect_key($NewRepeater,$OldRepeater);             //CALCULATE WHAT REPEATERS EXISIT IN BOTH
$BothPrefix =   array_intersect_key($NewPrefix,$OldPrefix);                   //CALCULATE WHAY PREFIXS EXISIT IN BOTH
$BothTalkgroup =array_intersect_key($NewTalkgroup,$OldTalkgroup);


DBAdd($AddUser,"User");                                                     //INSERT ADDITIONS TO SUBSCRIBER TABLE
DBAdd($AddRepeater,"Repeater");                                             //INSERT ADDITIONS TO REPEATERS TABLE    
DBAdd($AddPrefix,"Prefix");                                                 //INSERT ADDITIONS TO PREFIXES TABLE
DBAdd($AddTalkgroup,"Talkgroup");                                                 //INSERT ADDITIONS TO PREFIXES TABLE

DBChange($OldUser,$NewUser,$BothUser,"User");                               //MAKE CHANGED TO SUBSRIBER TABLE
DBChange($OldRepeater,$NewRepeater,$BothRepeater,"Repeater");               //MAKE CHANGES TO REPETAER TABLE
DBChange($OldPrefix,$NewPrefix,$BothPrefix,"Prefix");                       //MAKE CHANGES TO PREFIX TABLE
DBChange($OldTalkgroup,$NewTalkgroup,$BothTalkgroup,"Talkgroup");
DBRemove($DelUser,"User");                                                  //REMOVE DELETED SUBSCRIBERS FROM TABLE
DBRemove($DelRepeater,"Repeater");                                          //REMOVE DELETED REPEATERS FROM TABLE
DBRemove($DelPrefix,"Prefix");                                              //REMOVE DELETED PREFIXES FROM TABLE
DBRemove($DelTalkgroup,"Talkgroup"); 


print "DAILY DMR-MARC DB UPDATE ON: ".$hostname."\n";
print "STARTED ON: ".$DateTime."\n";
if(count($OldUser)){print "SUBSCRIBER RECORDS ALREADING IN DB: ".count($OldUser)."\n";}
if(count($NewUser)){print "SUBSCRIBER RECORDS RIPPED: ".count($NewUser)."\n";}
if(count($DelUser)){print "SUBSCRIBER RECORDS REMOVED FROM RIP: ".count($DelUser)."\n";}
if(count($AddUser)){print "SUBSCRIBER RECORDS ADDED TO DB: ".count($AddUser)."\n";}
if(count($BothUser)){print "SUBSCRIBER RECORDS UNCHANGED: ".count($BothUser)."\n";}
if(count($DiffUser)){print "SUBSCRIBER RECORDS ALREADY EXIST THAT HAVE CHANGED: ".count($DiffUser)."\n";}
if(count($OldRepeater)){print "REPEATER RECORDS ALREADY IN DB: ".count($OldRepeater)."\n";}
if(count($NewRepeater)){print "REPEATER RECORDS RIPPED: ".count($NewRepeater)."\n";}
if(count($DelRepeater)){print "REPEATER RECORDS REMOVED FROM RIP: ".count($DelRepeater)."\n";}
if(count($AddRepeater)){print "REPETAER RECORDS ADDED TO DB: ".count($AddRepeater)."\n";}
if(count($BothRepeater)){print "REPEATER RECORDS UNCHANGED: ".count($BothRepeater)."\n";}
if(count($DiffTalkgroup)){print "Talkgroup RECORDS ALREADY EXIST THAT HAVE CHANGED: ".count($DiffTalkgroup)."\n";}
if(count($OldTalkgroup)){print "Talkgroup RECORDS ALREADY IN DB: ".count($OldTalkgroup)."\n";}
if(count($NewTalkgroup)){print "Talkgroup RECORDS RIPPED: ".count($NewTalkgroup)."\n";}
if(count($DelTalkgroup)){print "Talkgroup RECORDS REMOVED FROM RIP: ".count($DelTalkgroup)."\n";}
if(count($AddTalkgroup)){print "Talkgroup RECORDS ADDED TO DB: ".count($AddTalkgroup)."\n";}
if(count($BothTalkgroup)){print "Talkgroup RECORDS UNCHANGED: ".count($BothTalkgroup)."\n";}
if(count($DiffTalkgroup)){print "Talkgroup RECORDS ALREADY EXIST THAT HAVE CHANGED: ".count($DiffTalkgroup)."\n";}
if(count($OldPrefix)){print "Prefix RECORDS ALREADY IN DB: ".count($OldPrefix)."\n";}
if(count($NewPrefix)){print "Prefix RECORDS RIPPED: ".count($NewPrefix)."\n";}
if(count($DelPrefix)){print "Prefix RECORDS REMOVED FROM RIP: ".count($DelPrefix)."\n";}
if(count($AddPrefix)){print "Prefix RECORDS ADDED TO DB: ".count($AddPrefix)."\n";}
if(count($BothPrefix)){print "Prefix RECORDS UNCHANGED: ".count($BothPrefix)."\n";}
if(count($DiffPrefix)){print "Prefix RECORDS ALREADY EXIST THAT HAVE CHANGED: ".count($DiffPrefix)."\n";}
print "DMR-MARC RIP DONE\n";


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
function LogChange($NewItem,$ChangeType, $RecordType) //logs to the change log
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
     $Page = $Dom->loadHTMLFile($Url); // new dom object for users
     $Dom->preserveWhiteSpace = false; //discard white space
     $Table = $Dom->getElementsByTagName('table'); //the table by its tag name
     $Rows = $Table->item(0)->getElementsByTagName('tr'); //get all rows from the table
      foreach ($Rows as $row) { // loop over the table rows
          $cols = $row->getElementsByTagName('td'); // get each column by tag name
          if (is_numeric($cols->item($key)->textContent)) {
               $i = 0;
               foreach ($cols as $col) {
                    $array[$cols->item($key)->nodeValue][$ColumnNames[$i]] =str_replace("NBSP","",strtoupper(trim(preg_replace("/[^0-9a-zA-Z .-]/","",$col->textContent))));
                    $i++;
               }
          }
     }
     return $array;
}
function GetOldData($TableName,$FieldNames) {    
     $Query = "SELECT * FROM `".$TableName."`;";
     $Result = mysql_query($Query) or die("Error in query: $query ".mysql_error());
     $NumResults = mysql_num_rows($Result);
     if ($NumResults > 0) {
          while ($row = mysql_fetch_array($Result,MYSQL_ASSOC)) {
               foreach ($FieldNames as $FieldName) {
                    $array[$row[DmrID]][$FieldName] = $row[$FieldName]; //AM I OVERTHINKING THIS?
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

function DBChange($Old,$New,$Both,$Table){
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
function DBAdd($new,$table) {
     if (count($new) != false) {
          foreach ($new as $AddRow) {
               $Query = BuildSqlInsert($AddRow,$table)."\n";
               mysql_query($Query) or die(mysql_error());
               LogChange($AddRow,"NEW",$table);
          }
     }
}
function DBRemove($del,$table) {
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

