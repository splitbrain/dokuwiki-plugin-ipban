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
        if (preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', trim($_REQUEST['ip']))) {
            $newban = trim($_REQUEST['ip']) . "\t" . time() . "\t" . $_SERVER['REMOTE_USER'];
            $cause = trim(preg_replace('/[\n\r\t]+/', '', $_REQUEST['cause']));
            $newban .= "\t" . $cause . "\n";
            io_savefile($conf['cachedir'] . '/ipbanplugin.txt', $newban, true);
        }

        if (is_array($_REQUEST['delip'])) {
            $del = trim(array_shift(array_keys($_REQUEST['delip'])));
            $del = preg_quote($del, '/');
            $new = array();
            $bans = @file($conf['cachedir'] . '/ipbanplugin.txt');
            if (is_array($bans)) {
                foreach ($bans as $ban) {
                    if (!preg_match('/^' . $del . '\t/', $ban)) $new[] = $ban;
                }
            }
            io_savefile($conf['cachedir'] . '/ipbanplugin.txt', join('', $new));
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
        echo '<th>' . $this->getLang('host') . '</th>';
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
                $host = @gethostbyaddr($fields[0]);
                if (!$host || $host == $fields[0]) $host = '?';
                echo '<td>' . hsc($host) . '</td>';
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
