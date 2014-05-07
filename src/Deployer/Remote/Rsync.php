<?php
/**
 * Created by PhpStorm.
 * User: Paweł (Smeagol) Bogut
 * Date: 07.05.14
 * Time: 20:24
 */

namespace Deployer\Remote;

use Deployer\Remote\Remote as SSHRemote;

class Rsync extends SSHRemote {

    private $ignoreList = array();

    public function __construct($server, $user, $password) {
        parent::__construct($server, $user, $password);
    }

    public function uploadFile($from, $to)
    {
        $cmd = sprintf($this->getCommand(),
            $this->getServerPort(), $from, $this->getUser(), $this->getServerHost(), $to
        );
        $this->exec($cmd);
    }

    public function uploadDir($from, $to) {
        $this->prepareDirName($from);
        $this->prepareDirName($to);
        $cmd = sprintf($this->getCommand(),
            $this->getServerPort(), $from, $this->getUser(), $this->getServerHost(), $to
        );
        $this->exec($cmd);
    }

    public function setIgnoreList($ignore) {
        if (!is_array($ignore)) {
            $ignore = array($ignore);
        }
        $this->ignoreList = $ignore;
    }

    protected function getCommand() {
        return "rsync -ar --info=progress2 -e 'ssh -p %s' '%s' '%s@%s:%s' " . $this->getExclude();
    }

    protected function getExclude() {
        $result = '';
        foreach($this->ignoreList as $ignore) {
            $result .= sprintf("--exclude '%s' ", $ignore);
        }
        return $result;
    }

    protected function prepareDirName(&$dir) {
        $dir = sprintf("%s/",$dir);
        return $dir;
    }

    protected function exec($cmd) {
        $descriptorspec = array(
            0 => array("pipe", "r"),   // stdin is a pipe that the child will read from
            1 => array("pipe", "w"),   // stdout is a pipe that the child will write to
            2 => array("pipe", "w")    // stderr is a pipe that the child will write to
        );
        ob_implicit_flush(true);flush();
        $process = proc_open($cmd, $descriptorspec, $pipes, realpath('.'.DIRECTORY_SEPARATOR), array());
        if (is_resource($process)) {
            while ($s = fgets($pipes[1])) {
                print $s;
                flush();
            }
        }
    }

}