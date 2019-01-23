<?php
/**
 * Created by IntelliJ IDEA.
 * User: wdev
 * Date: 23.01.2019
 * Time: 00:49
 */

require_once join(DIRECTORY_SEPARATOR, ["vendor", "autoload.php"]);

use Ibanka\Iban;

if ($argc < 2) {
    echo "Iban girin" . PHP_EOL;
} else {
    try {
        print_r(Iban::bak($argv[1]));
    } catch (\Filebase\Filesystem\FilesystemException $e) {
    } catch (\Ibanka\IbankaException $e) {
    }
}