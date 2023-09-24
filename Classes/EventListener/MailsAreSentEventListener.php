<?php
declare(strict_types=1);

namespace SvenJuergens\DisableBeuserCsv\EventListener;

use SvenJuergens\DisableBeuser\Event\BeforeMailsAreSentEvent;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\CsvUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MailsAreSentEventListener
{
    public function before(BeforeMailsAreSentEvent $event): void
    {
        $disabledUser = $event->getDisabledUser();
        foreach ($disabledUser as &$user) {
            $user['lastlogin_date'] = ($user['lastlogin'] ?? false) ? date('d.m.Y - H:i', $user['lastlogin']) : '';
            $user['crdate_date'] = ($user['crdate'] ?? false) ? date('d.m.Y - H:i', $user['crdate']) : '';
            unset($user['lastlogin'], $user['crdate']);
        }
        unset($user);
        $headerRow = array_keys($disabledUser[0]);

        $extensionConfig = GeneralUtility::makeInstance(ExtensionConfiguration::class)
            ->get('disable_beuser_csv');

        $csvDelimiter = $extensionConfig['delimiter'] ?? ',';
        $csvQuote = $extensionConfig['quote'] ?? '"';

        // Create result
        $result[] = CsvUtility::csvValues($headerRow, $csvDelimiter, $csvQuote);
        foreach ($disabledUser as $record) {
            $result[] = CsvUtility::csvValues($record, $csvDelimiter, $csvQuote);
        }

        $event->getMailer()->attach(
            implode(CRLF, $result),
            'disable_beuser.csv',
            'text/csv'
        );
    }

}
