<?php

// ___ ____   ____ ____        _   
//|_ _|  _ \ / ___| __ )  ___ | |_ 
// | || |_) | |   |  _ \ / _ \| __|
// | ||  _ <| |___| |_) | (_) | |_ 
//|___|_| \_\\____|____/ \___/ \__|
//                        By Ethan.

define("cmdChar", "!");

function search($sock, $chan, $arg){
$t = file_get_contents(sprintf('http://api.duckduckgo.com/?q=%s&format=xml&pretty=1', $arg));
$result = new SimpleXMLElement($t);
if(strlen($result->Results[0]->Result[0]->FirstURL[0]) < 1){
$i = $result->RelatedTopics[0]->RelatedTopic[0]->FirstURL[0];
}else{
$i = $result->Results[0]->Result[0]->FirstURL[0];
}
sendMessage($sock, $chan, sprintf('Top Result from DuckDuckGo Search is, %s', $i));
}

function getData($sock){
return fgets($sock, 256);
}

function sendPacket($sock, $packet){
fwrite($sock, $packet . "\r\n");
sleep(1);
}

function sendMessage($sock, $chan, $strMessage){
sendPacket($sock, sprintf('PRIVMSG %s : %s', $chan, $strMessage));
}

echo ' IRC Host : ';
$ip = trim(fgets(STDIN));
echo ' Port : ';
$port = trim(fgets(STDIN));
echo ' Channel without # : ';
$channel = trim(fgets(STDIN));
$channel = sprintf('#%s', $channel);
echo ' Nick : ';
$nick = trim(fgets(STDIN));

$socket = fsockopen($ip, $port);
sendPacket($socket, sprintf('NICK %s', $nick));
sendPacket($socket, sprintf('USER %s %s %s: %s', $nick, $nick, $nick, $nick));
sendPacket($socket, sprintf('JOIN %s', $channel));
sendMessage($socket, $channel, 'Hello');

while(1){
$strData = getData($socket);
echo $strData;

if(strpos($strData, sprintf('KICK %s %s', $channel, $nick)) > -1){
sendPacket($socket, sprintf('JOIN %s', $channel));
}else if(strpos($strData, sprintf('PING :')) > -1){
sendPacket($socket, sprintf('PONG :PING'));
echo "Replied to Ping Request \n";
}else if(strpos($strData, sprintf('PRIVMSG %s', $channel)) > -1){
$dta = (explode(' ', $strData));
unset($dta[0]);
unset($dta[1]);
unset($dta[2]);
$strCommand = substr($dta[3], 1);
unset($dta[3]);

$strArgument = "";
foreach($dta as $sta){
   $strArgument .= !$sta ? '' : sprintf('%s', $sta);
}

$strArgument = substr($strArgument , 0, strlen($strArgument) -2);

if(substr($strCommand, 0, 1) == cmdChar){
 switch(strtoupper($strCommand)){
  case cmdChar . 'SAY':
  sendMessage($socket, $channel, sprintf($strArgument));
  break;
 
  case cmdChar . 'MD5':
  sendMessage($socket, $channel, sprintf('"%s" in MD5 is %s', $strArgument, md5($strArgument)));
  break;
 
  case cmdChar . 'SHA1':
  sendMessage($socket, $channel, sprintf('"%s" in SHA1 is %s', $strArgument, sha1($strArgument)));
  break;
  
  case cmdChar . 'SHA256':
  sendMessage($socket, $channel, sprintf('"%s" in SHA256 is %s', $strArgument, hash('sha256', $strArgument)));
  break;
  
  case cmdChar . 'SHA512':
  sendMessage($socket, $channel, sprintf('"%s" in SHA512 is %s', $strArgument, hash('sha512', $strArgument)));
  break;
  
  case cmdChar . 'DUCKDUCKGO':
  search($socket, $channel, $strArgument);
  break;
  
  default:
  sendMessage($socket, $channel, sprintf("Not a Valid Command..."));
  break;
 
   }
  }
 }
}
?>