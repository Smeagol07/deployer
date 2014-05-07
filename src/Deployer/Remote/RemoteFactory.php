<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Deployer\Remote;

class RemoteFactory
{
    public function create($server, $user, $password)
    {
        //check if rsync is available
        exec('rsync --version', $output, $return);
        if ($return === 0) {
            return new Rsync($server, $user, $password);
        }
        return new Remote($server, $user, $password);
    }
} 