<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */
class action_plugin_ipban extends DokuWiki_Action_Plugin
{

    /**
     * register the eventhandlers and initialize some options
     */
    public function register(Doku_Event_Handler $controller)
    {

        $controller->register_hook('DOKUWIKI_STARTED',
            'BEFORE',
            $this,
            'handle_start',
            array());
    }

    /**
     * Do the magic
     *
     * @param Doku_Event $event
     * @param $param
     */
    public function handle_start(Doku_Event $event, $param)
    {
        global $conf;

        $bans = @file($conf['cachedir'] . '/ipbanplugin.txt');
        $client = clientIP(true);
        if (!is_array($bans)) return;

        // if the client isn't banned, we're done
        $banreason = $this->isBanned($client, $bans);
        if (!$banreason) return;

        // prepare template
        $text = $this->locale_xhtml('banned');
        $text .= vsprintf('<p>' . $this->getLang('banned') . '</p>', array_map('hsc', $banreason));
        $title = $this->getLang('denied');

        // output
        http_status(403, 'Forbidden');
        echo '<!DOCTYPE html>';
        echo '<html>';
        echo "<head><title>$title</title></head>";
        echo '<body style="font-family: Arial, sans-serif">';
        echo '<div style="width:60%; margin: auto; background-color: #fcc; border: 1px solid #faa; padding: 0.5em 1em;">';
        echo $text;
        echo '</div>';
        echo '</body>';
        echo '</html>';
        exit;
    }

    /**
     * Check if the given client IP is in the list of ban lines
     *
     * @param string $client IP of the client
     * @param string[] $banconf List of ban lines
     * @return false|array false or ban info [ip, date, reason]
     */
    protected function isBanned($client, $banconf)
    {
        require_once(__DIR__ . '/ip-lib/ip-lib.php');

        $ip = \IPLib\Factory::addressFromString($client);
        foreach ($banconf as $ban) {
            list($range, $dt, /*user*/, $reason) = explode("\t", trim($ban));
            $ipRange = \IPLib\Factory::rangeFromString($range);
            if($ipRange === null) continue;
            if ($ip->matches($ipRange)) {
                return [
                    $ip->toString(),
                    dformat($dt),
                    $reason,
                ];
            }
        }

        return false;
    }
}
