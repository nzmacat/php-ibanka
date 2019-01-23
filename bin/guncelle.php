<?php
/**
 * Created by IntelliJ IDEA.
 * User: wdev
 * Date: 22.01.2019
 * Time: 22:56
 */

require_once join(DIRECTORY_SEPARATOR, ["vendor", "autoload.php"]);

try {
    Ibanka\Otomatik::guncelle();
} catch (\Exception $e) {
    echo PHP_EOL . "Hata: " . $e->getMessage() . PHP_EOL;
}