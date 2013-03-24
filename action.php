<?php
/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'action.php');

class action_plugin_ipban extends DokuWiki_Action_Plugin {

    /**
     * register the eventhandlers and initialize some options
     */
    function register(&$controller){

        $controller->register_hook('DOKUWIKI_STARTED',
                                   'BEFORE',
                                   $this,
                                   'handle_start',
                                   array());
    }

    /**
     * Do the magic
     */
    function handle_start(&$event, $param){
        global $conf;
        $bans = @file($conf['cachedir'].'/ipbanplugin.txt');
        $client = clientIP(true);
        if(is_array($bans))
          foreach($bans as $ban) {
            $fields = explode("\t", $ban);
            $banned = false;
            if (($p=strcspn($fields[0],'/-*')) != strlen($fields[0])) {
              $cli = (float)sprintf("%u",ip2long($client));
              switch ($fields[0][$p]) {
                case '/':
                  $mask = (pow(2, (32 - substr($fields[0],$p+1))) - 1);
                  $v1 = (float)sprintf("%u",ip2long(substr($fields[0], 0, $p))) & ~$mask;
                  $v2 = $v1 | $mask;
                  break;
                case '-':
                  $v1 = (float)sprintf("%u",ip2long(substr($fields[0], 0, $p)));
                  $v2 = (float)sprintf("%u",ip2long(substr($fields[0], $p+1)));
                  break;
                case '*':
                  $v1 = (float)sprintf("%u",ip2long(str_replace('*', '0', $fields[0])));
                  $v2 = (float)sprintf("%u",ip2long(str_replace('*', '255', $fields[0])));
                  break;
              }
              $banned = $v1 <= $cli && $cli <= $v2;
            } elseif ($fields[0] == $client)
                $banned =  TRUE;
            if($banned){
                $text = $this->locale_xhtml('banned');
                $text .= sprintf('<p>'.$this->getLang('banned').'</p>',
                                 hsc($client), strftime($conf['dformat'],$fields[1]),
                                 hsc($fields[3]));
                $title = $this->getLang('denied');
                header("HTTP/1.0 403 Forbidden");
                echo<<<EOT
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>$title</title>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
</head>
<body style="font-family: Arial, sans-serif">
  <div style="width:60%; margin: auto; background-color: #fcc;
              border: 1px solid #faa; padding: 0.5em 1em;">
  $text
  </div>
</body>
</html>
EOT;
                exit;
            }
        }
    }
}
