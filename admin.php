<?php
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'admin.php');

/**
 * All DokuWiki plugins to extend the admin function
 * need to inherit from this class
 */
class admin_plugin_ipban extends DokuWiki_Admin_Plugin {

    /**
     * return some info
     */
    function getInfo(){
        return confToHash(dirname(__FILE__).'/info.txt');
    }

    /**
     * access for managers
     */
    function forAdminOnly(){
        return false;
    }

    /**
     * return sort order for position in admin menu
     */
    function getMenuSort() {
        return 41;
    }

    /**
     * handle user request
     */
    function handle() {
        global $conf;
        if(preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/',trim($_REQUEST['ip']))){
            $newban = trim($_REQUEST['ip'])."\t".time()."\t".$_SERVER['REMOTE_USER'];
            $cause  = trim(preg_replace('/[\n\r\t]+/','',$_REQUEST['cause']));
            $newban .= "\t".$cause."\n";
            io_savefile($conf['cachedir'].'/ipbanplugin.txt',$newban,true);
        }

        if(is_array($_REQUEST['delip'])){
            $del = trim(array_shift(array_keys($_REQUEST['delip'])));
            $del = preg_quote($del,'/');
            $new = array();
            $bans = @file($conf['cachedir'].'/ipbanplugin.txt');
            if(is_array($bans)) foreach($bans as $ban){
                if(!preg_match('/^'.$del.'\t/',$ban)) $new[] = $ban;
            }
            io_savefile($conf['cachedir'].'/ipbanplugin.txt',join('',$new));
        }
    }

    /**
     * output appropriate html
     */
    function html() {
        global $conf;

        echo $this->plugin_locale_xhtml('intro');

        echo '<form method="post" action="">';
        echo '<table class="inline" width="100%">';
        echo '<tr>';
        echo '<th>'.$this->getLang('ip').'</th>';
        echo '<th>'.$this->getLang('host').'</th>';
        echo '<th>'.$this->getLang('date').'</th>';
        echo '<th>'.$this->getLang('by').'</th>';
        echo '<th>'.$this->getLang('cause').'</th>';
        echo '<th>'.$this->getLang('del').'</th>';
        echo '</tr>';
        $bans = @file($conf['cachedir'].'/ipbanplugin.txt');
        if(is_array($bans)) foreach($bans as $ban){
            $fields = explode("\t",$ban);
            echo '<tr>';
            echo '<td>'.hsc($fields[0]).'</td>';
            $host = @gethostbyaddr($fields[0]);
            if(!$host || $host == $fields[0]) $host='?';
            echo '<td>'.hsc($host).'</td>';
            echo '<td>'.strftime($conf['dformat'],$fields[1]).'</td>';
            echo '<td>'.hsc($fields[2]).'</td>';
            echo '<td>'.hsc($fields[3]).'</td>';
            echo '<td><input type="submit" name="delip['.$fields[0].']" value="'.$this->getLang('del').'" class="button" /></td>';
            echo '</tr>';
        }
        echo '<tr>';
        echo '<th colspan="6">';
        echo '<div>'.$this->getLang('newban').':</div>';
        echo '<label for="plg__ipban_ip">'.$this->getLang('ip').':</label>';
        echo '<input type="text" name="ip" id="plg__ipban_ip" class="edit" /> ';
        echo '<label for="plg__ipban_cause">'.$this->getLang('cause').':</label>';
        echo '<input type="text" name="cause" id="plg__ipban_cause" class="edit" /> ';
        echo '<input type="submit" class="button" value="'.$this->getLang('ban').'" />';
        echo '</th>';
        echo '</tr>';
        echo '</table>';
        echo '</form>';

    }


}
//Setup VIM: ex: et ts=4 enc=utf-8 :
