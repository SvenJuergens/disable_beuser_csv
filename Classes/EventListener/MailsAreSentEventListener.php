<?php
declare(strict_types=1);

namespace SvenJuergens\DisableBeuserCsv\EventListener;

use League\Csv\CannotInsertRecord;
use League\Csv\Exception;
use SvenJuergens\DisableBeuser\Event\BeforeMailsAreSentEvent;
use League\Csv\Writer;

class MailsAreSentEventListener
{
    /**
     * @throws CannotInsertRecord
     * @throws Exception
     */
    public function before(BeforeMailsAreSentEvent $event): void
    {
        $disabledUser = $event->getDisabledUser();
        foreach ($disabledUser as &$user) {
            $user['lastlogin_date'] = ($user['lastlogin'] ?? false) ? date('d.m.Y - H:i', $user['lastlogin']) : '';
            $user['crdate_date'] = ($user['crdate'] ?? false) ? date('d.m.Y - H:i', $user['crdate']) : '';
            unset($user['lastlogin'], $user['crdate']);
        }
        unset($user);
        $header = array_keys($disabledUser[0]);
        $csv = Writer::createFromString();
        $csv->insertOne($header);
        $csv->insertAll($disabledUser);

        $event->getMailer()->attach(
            $csv->toString(),
            'disable_beuser.csv',
            'text/csv'
        );
    }

}
