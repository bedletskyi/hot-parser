<?php

namespace App\Modules;



use phpQuery;

class Proxy
{
    /**
     * Получаем список Прокси
     * @return array, bool
     */
    public function getProxies()
    {
        $ch = curl_init('https://hidemy.name/ru/proxy-list/?country=UA&maxtime=3000#list');

        curl_setopt($ch, CURLOPT_NOBODY, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36');
        curl_setopt($ch, CURLOPT_COOKIE, "__cfduid=dfed48de5752dd009b2c98bdbd90774941527924035");
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);

        $response = (string) curl_exec($ch);

        curl_close($ch);

        $dom = phpQuery::newDocument($response);

        $table = $dom->find('table.proxy__t tbody tr');
        $i=0;

        $proxies = array();

        foreach ($table as $tr) {
            $tr = pq($tr);

            $ip = $tr->find('td.tdl')->text();
            $port = $tr->find('td:eq(1)')->text();
            $protocol = $tr->find('td:eq(4)')->text();

            if ($protocol == 'HTTP')
                $protocol = 'CURLPROXY_HTTP';

            if ($protocol == 'HTTPS')
                $protocol = 'CURLPROXY_HTTPS';

            if ($protocol == 'SOCKS4')
                $protocol = 'CURLPROXY_SOCKS4';

            if ($protocol == 'SOCKS5')
                $protocol = 'CURLPROXY_SOCKS5';

            $proxies[$i] = [
                'ip' => $ip . ':' . $port,
                'protocol' => $protocol
            ];

            $i++;
        }
        if (!empty($proxies))
            return $proxies;
        else
            return false;
    }
}