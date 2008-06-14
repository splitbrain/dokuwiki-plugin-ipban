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
     * return some info
     */
    function getInfo(){
        return confToHash(dirname(__FILE__).'/info.txt');
    }

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
        if(is_array($bans)) foreach($bans as $ban){
            $fields = explode("\t",$ban);
            if($fields[0] == $client){
                $text = $this->plugin_locale_xhtml('banned');
                $text .= sprintf('<p>'.$this->getLang('banned').'</p>',
                                 hsc($client), strftime($conf['dformat'],$fields[1]),
                                 hsc($fields[3]));
                $title = $this->getLang('denied');
                header("HTTP/1.0 403 Forbidden");
                echo<<<EOT
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head><title>$title</title></head>
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
