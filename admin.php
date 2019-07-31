<?php
/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

class admin_plugin_ipban extends DokuWiki_Admin_Plugin
{

    /** @inheritDoc */
    function forAdminOnly()
    {
        return false;
    }

    /** @inheritDoc */
    function getMenuSort()
    {
        return 41;
    }

    /** @inheritDoc */
    function handle()
    {
        global $conf;
        global $INPUT;

        $ip = trim($INPUT->str('ip'));
        if ($ip) {
            require_once(__DIR__ . '/ip-lib/ip-lib.php');
            $range = \IPLib\Factory::rangeFromString($ip);
            if ($range === null) {
                msg($this->getLang('badrange'), -1);
            } else {
                $newban = $ip . "\t" . time() . "\t" . $INPUT->server->str('REMOTE_USER');
                $cause = trim(preg_replace('/[\n\r\t]+/', '', $INPUT->str('cause')));
                $newban .= "\t" . $cause . "\n";
                io_savefile($conf['cachedir'] . '/ipbanplugin.txt', $newban, true);
            }

        }

        $delip = $INPUT->extract('delip')->str('delip');
        if ($delip) {
            $delip = preg_quote($delip, '/');

            io_deleteFromFile(
                $conf['cachedir'] . '/ipbanplugin.txt',
                '/^' . $delip . '\t/',
                true
            );
        }
    }

    /** @inheritDoc */
    function html()
    {
        global $conf;

        echo $this->locale_xhtml('intro');

        echo '<form method="post" action="">';
        echo '<table class="inline" width="100%">';
        echo '<tr>';
        echo '<th>' . $this->getLang('ip') . '</th>';
        echo '<th>' . $this->getLang('date') . '</th>';
        echo '<th>' . $this->getLang('by') . '</th>';
        echo '<th>' . $this->getLang('cause') . '</th>';
        echo '<th>' . $this->getLang('del') . '</th>';
        echo '</tr>';
        $bans = @file($conf['cachedir'] . '/ipbanplugin.txt');
        if (is_array($bans)) {
            foreach ($bans as $ban) {
                $fields = explode("\t", $ban);
                echo '<tr>';
                echo '<td>' . hsc($fields[0]) . '</td>';
                echo '<td>' . strftime($conf['dformat'], $fields[1]) . '</td>';
                echo '<td>' . hsc($fields[2]) . '</td>';
                echo '<td>' . hsc($fields[3]) . '</td>';
                echo '<td><input type="submit" name="delip[' . $fields[0] . ']" value="' . $this->getLang('del') . '" class="button" /></td>';
                echo '</tr>';
            }
        }
        echo '<tr>';
        echo '<th colspan="6">';
        echo '<div>' . $this->getLang('newban') . ':</div>';
        echo '<label for="plg__ipban_ip">' . $this->getLang('ip') . ':</label>';
        echo '<input type="text" name="ip" id="plg__ipban_ip" class="edit" /> ';
        echo '<label for="plg__ipban_cause">' . $this->getLang('cause') . ':</label>';
        echo '<input type="text" name="cause" id="plg__ipban_cause" class="edit" /> ';
        echo '<input type="submit" class="button" value="' . $this->getLang('ban') . '" />';
        echo '</th>';
        echo '</tr>';
        echo '</table>';
        echo '</form>';

    }

}
