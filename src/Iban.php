<?php
/**
 * Created by IntelliJ IDEA.
 * User: wdev
 * Date: 23.01.2019
 * Time: 00:49
 */

namespace Ibanka;

use Iban\Validation\Validator;
use Iban\Validation\Iban as IIban;

class Iban
{
    /**
     * @param $iban
     * @param bool $buyuk_harf
     * @return \Filebase\Document
     * @throws IbankaException
     * @throws \Filebase\Filesystem\FilesystemException
     */
    public static function bak($iban, $buyuk_harf = false)
    {
        if (!Iban::dogru($iban))
            throw new IbankaException("Iban geçersiz");

        $iban = new IIban($iban);
        $bban = $iban->getBban();

        $banka = substr($bban, 1, 4);

        $bankalar = self::_bankalar();

        $subeIndex = [
            "0010" => 5,
            "0012" => 6,
            "0032" => 6,
            "0046" => 5,
            "0062" => 6,
            "0064" => 10,
            "0096" => 7,
            "0103" => 6,
            "0109" => 5,
            "0123" => 5,
            "0124" => 5,
            "0125" => 5,
            "0206" => 5
        ];

        $bankaData = $bankalar->get($banka)->toArray();

        $subeData = [];
        if (isset($subeIndex[$banka])) {
            $sube = substr($bban, $subeIndex[$banka], 5);
            $subeData = $bankalar->get($banka . $sube)->toArray();
        } else if ($banka === "0059") {
            $sube = "0" . substr($bban, 5, 4);
            $subeData = $bankalar->get($banka . $sube)->toArray();
        }

        if (!$buyuk_harf) {
            $subeData = array_map(function ($string) {
                return mb_convert_case($string, MB_CASE_TITLE, "UTF-8");
            }, $subeData);

            $bankaData = array_map(function ($string) {
                return mb_convert_case($string, MB_CASE_TITLE, "UTF-8");
            }, $bankaData);
        }

        return array_merge(["iban" => $iban->getNormalizedIban()], $bankaData, $subeData);
    }

    /**
     * @param $banka
     * @param $sube
     * @param bool $buyuk_harf
     * @throws \Filebase\Filesystem\FilesystemException
     */
    public static function ara($banka, $sube, $buyuk_harf = false)
    {
        $bankalar = self::_bankalar();

        $bankaData = $bankalar->get($banka)->toArray();
        $subeData = $bankalar->get($banka . $sube)->toArray();

        if (!$buyuk_harf) {
            $subeData = array_map(function ($string) {
                return mb_convert_case($string, MB_CASE_TITLE, "UTF-8");
            }, $subeData);

            $bankaData = array_map(function ($string) {
                return mb_convert_case($string, MB_CASE_TITLE, "UTF-8");
            }, $bankaData);
        }

        return array_merge([$bankaData, $subeData]);
    }

    /**
     * @param $iban
     * @return bool
     */
    public static function dogru($iban)
    {
        $iban = new IIban($iban);
        $validator = new Validator();

        return $validator->validate($iban);
    }

    /**
     * @return \Filebase\Database
     * @throws \Filebase\Filesystem\FilesystemException
     */
    private static function _bankalar(): \Filebase\Database
    {
        $bankalar = new \Filebase\Database([
            'dir' => join(DIRECTORY_SEPARATOR, [dirname(__FILE__), "depo", "bankalar"]),
            'backupLocation' => join(DIRECTORY_SEPARATOR, [dirname(__FILE__), "depo", "yedek"]),
            'format' => \Filebase\Format\Json::class,
            'cache' => true,
            'cache_expires' => 1800,
            'pretty' => true,
            'safe_filename' => true,
            'read_only' => true
        ]);
        return $bankalar;
    }
}