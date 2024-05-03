<?php


class CoinMarketCapWrapper
{
    static $ROOT_ENDPOINT = 'https://pro-api.coinmarketcap.com/';

    static $plugin_dir = WP_PLUGIN_DIR . '/CryptoTracker';

    static $filename = '/TopCoins.json';

    private function get(string $endpoint, array $parameters = ['start' => '1', 'limit' => '100', 'convert' => 'USD'])
    {
        $options = get_option('crypto_tracker_settings');
        $api_key = $options['crypto_tracker_api_key'];
        $url = self::$ROOT_ENDPOINT . $endpoint;

        $headers = [
            'Accepts: application/json',
            'X-CMC_PRO_API_KEY: ' . $api_key
        ];
        $qs = http_build_query($parameters);
        $request = "{$url}?{$qs}";


        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $request,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => 1
        ));
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true);
    }

    private function fetchIdsFromMap()
    {

        return $this->get(
            'v1/cryptocurrency/map',
            [
                'start' => '1',
                'sort' => 'cmc_rank',
                'limit' => '100'
            ]
        );
    }

    public function fetchIds()
    {
        $path = self::$plugin_dir . self::$filename;
        if (file_exists($path)) {

            $jsonString = file_get_contents($path);
            $jsonData = json_decode($jsonString, true);

            return $jsonData;
        } else {
            $this->dumpsIdsToFile();
        }
    }

    public function fetchLatesQuotes($ids)
    {
        return $this->get(
            'v2/cryptocurrency/quotes/latest',
            [
                'id' => $ids,
            ]
        );
    }

    private function  dumpsIdsToFile()
    {

        $response = $this->fetchIdsFromMap();
        $list = [];

        foreach ($response['data'] as $crypto) {
            $list[$crypto['id']] = $crypto['name'];
        }
        $jsonString = json_encode($list, JSON_PRETTY_PRINT);


        $fp = fopen(self::$plugin_dir . self::$filename, "w");
        fwrite($fp, $jsonString);
        fclose($fp);

        return $list;
    }
}
