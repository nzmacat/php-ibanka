<?php
/**
 * Created by IntelliJ IDEA.
 * User: wdev
 * Date: 22.01.2019
 * Time: 22:55
 */

namespace Ibanka;

class Otomatik
{
    private static function prog($string, $size, &$last)
    {
        $time = microtime(true);
        return function ($count) use ($size, $string, &$last, $time) {

            $last += $count;
            $bolum = ($last / $size);
            $yuzde = floor($bolum * 100);
            $cols = intval(exec('tput cols'));
            $cols = $cols > 40 ? $cols : 80;

            echo "\r\033[2K";
            echo sprintf("\e[0;31m[%s]\e[0m\e[1;33m %s  (%s/%s)\e[0m ",
                str_pad($yuzde, 3, ' ', STR_PAD_LEFT),
                str_pad($string, $cols - 40, '.', STR_PAD_RIGHT),
                str_pad($last, 6, ' ', STR_PAD_LEFT),
                str_pad($size, 6, ' ', STR_PAD_LEFT));

            if ($bolum === 1)
            {
                echo "\e[0;32m" . sprintf("%.2fs.", (microtime(true) - $time)) . "\e[0m" . PHP_EOL;
            } else {
                echo "\033[0K";
            }

            fflush(STDOUT);
        };
    }

    /**
     * @throws \Filebase\Filesystem\FilesystemException
     * @throws \Exception
     */
    public static function guncelle()
    {
        ini_set('max_execution_time', 300);

        echo "\e[0;32m[   ] Dosya alınıyor.\e[0m";
        fflush(STDOUT);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://eftemkt.tcmb.gov.tr/bankasubelistesi/bankaSubeTumListe.xml",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        $status = curl_getinfo($curl);

        if ($err)
            throw new \Exception($err);


        if ($status['http_code'] !== 200)
            throw new \Exception("File not found!");

        echo "\r\033[2K\e[0;32m[  x] Dosya alındı.\e[0m" . PHP_EOL;
        fflush(STDOUT);

        $xml = simplexml_load_string($response);

        $bankalar = new \Filebase\Database([
            'dir' => join(DIRECTORY_SEPARATOR, [dirname(__FILE__), "depo", "bankalar"]),
            'backupLocation' => join(DIRECTORY_SEPARATOR, [dirname(__FILE__), "depo", "yedek"]),
            'format' => \Filebase\Format\Json::class,
            'cache' => true,
            'cache_expires' => 1800,
            'pretty' => true,
            'safe_filename' => true,
            'read_only' => false
        ]);

        $last = 0;
        $size = 0;
        foreach ($xml->bankaSubeleri as $data)
        {
            $size += 1;
            foreach ($data->sube as $sube)
                $size += 1;
        }

        $prog = Otomatik::prog("Veri işleniyor", $size, $last);

        foreach ($xml->bankaSubeleri as $data) {
            $banka = new \Filebase\Document($bankalar);

            $banka->setId((string)$data->banka->bKd);
            $banka->save([
                "banka_id" => (string)$data->banka->bKd,
                "banka_il" => (string)$data->banka->bIlAd,
                "adres" => (string)$data->banka->adr,
                "banka_isim" => (string)$data->banka->bAd
            ]);

            $prog(1);

            foreach ($data->sube as $sube) {
                $doc = new \Filebase\Document($bankalar);
                $doc->setId((string)$sube->bKd . (string)$sube->sKd);
                $doc->save([
                    "banka_id" => (string)$sube->bKd,
                    "sube_id" => (string)$sube->sKd,
                    "il_kodu" => (string)$sube->sIlKd,
                    "sube_isim" => (string)$sube->sAd
                ]);

                $prog(1);
            }
        }
    }
}