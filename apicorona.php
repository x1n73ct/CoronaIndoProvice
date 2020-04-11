<?php

//get string between
function get_string_between($string, $start, $end)
{
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

//jatim
function CoronaJatim()
{
    $url = "http://covid19dev.jatimprov.go.id/xweb/draxi";
    $get_contents = file_get_contents($url);
    $DOM = new \DOMDocument();
    $DOM->loadHTML(get_string_between($get_contents, '<tbody>', '</tbody>'));

    $trList = $DOM->getElementsByTagName("tr");
    $rows = [];
    foreach ($trList as $tr) {
        $row = [];
        foreach ($tr->getElementsByTagName("td") as $td) {
            $row[] = trim($td->textContent);
        }
        $rows[] = $row;
    }

    $aDataTableDetailHTML = [];
    foreach ($rows[0] as $col => $value) {
        $aDataTableDetailHTML[] = array_column($rows, $col);
    }

    $kota = $aDataTableDetailHTML[0];
    $odp = $aDataTableDetailHTML[1];
    $pdp = $aDataTableDetailHTML[2];
    $confirm = $aDataTableDetailHTML[3];

    $data_jatim = [];
    for ($i = 0; $i <= count($kota) - 1; $i++) {
        $data_jatim[$i]["city"] = $kota[$i];
        $data_jatim[$i]["odp"] = $odp[$i];
        $data_jatim[$i]["pdp"] = $pdp[$i];
        $data_jatim[$i]["confirm"] = $confirm[$i];
    }
    $count_odp = array_sum(array_column($data_jatim, 'odp'));
    $count_pdp = array_sum(array_column($data_jatim, 'pdp'));
    $count_confirm = array_sum(array_column($data_jatim, 'confirm'));
    return json_encode(["odp" => (int) $count_odp, "pdp" => (int) $count_pdp, "confirm" => (int) $count_confirm, "detail" => $data_jatim]);
}

//jateng
function CoronaJateng()
{
    $url = "https://corona.jatengprov.go.id/";
    $get_contents = file_get_contents($url);
    $count_odp = get_string_between($get_contents, '<h3 class="font-counter fc-ungu">', '<sup');
    $count_pdp = get_string_between($get_contents, '<h3 class="font-counter fc-orange">', '</sup');
    $count_confirm = get_string_between($get_contents, '<div class="font-counter fc-red">', '<h6');
    return json_encode(["odp" => (int) $count_odp, "pdp" => (int) $count_pdp, "confirm" => (int) $count_confirm]);
}

//jabar
function CoronaJabar()
{
    $url = "https://covid19-public.digitalservice.id/analytics/aggregation/";
    $data_jabar = json_decode(file_get_contents($url), true);

    $last_key = end(array_keys(array_filter(array_column($data_jabar, 'positif'))));
    $data = (object) $data_jabar[$last_key];
    return json_encode(["odp" => (int) $data->total_odp, "pdp" => (int) $data->total_pdp, "confirm" => (int) $data->total_positif_saat_ini]);
}


//aceh
// $url = "https://dashboard.bravo.siat.web.id/public/dashboard/b1fcaade-589b-4620-a715-21d2d4cc234e";  

// var h1 = document.getElementsByTagName("h1");
// var hasil = [];
// hasil['odp'] = h1[1].textContent;
// hasil['pdp'] = h1[2].textContent;
// hasil['confirm'] = h1[5].textContent;
// console.log(hasil);

//sumbar
function CoronaSumbar()
{
    $url = "https://corona.sumbarprov.go.id/details/index_master_corona";
    $get_contents = file_get_contents($url);
    $DOM = new \DOMDocument();
    @$DOM->loadHTML(get_string_between($get_contents, '<tbody>', '</tbody>'));
    $trList = $DOM->getElementsByTagName("tr");
    $rows = [];
    foreach ($trList as $tr) {
        $row = [];
        foreach ($tr->getElementsByTagName("td") as $td) {
            $row[] = trim($td->textContent);
        }
        $rows[] = $row;
    }

    return json_encode(["odp" => (int) $rows[0][2], "pdp" => (int) $rows[0][3], "confirm" => (int) $rows[0][6]]);
}

//sumsel
function Coronasumsel()
{
    $url = "http://corona.sumselprov.go.id/index.php?module=home&id=1";
    $get_contents = file_get_contents($url);
    $DOM = new \DOMDocument();
    @$DOM->loadHTML($get_contents);
    $xp    = new \DOMXPath($DOM);
    $nodes = $xp->query('//font[@color="#006600"]');
    $result = [];
    foreach ($nodes as $element) {
        $nodes = $element->childNodes;
        foreach ($nodes as $node) {
            $result[] = $node->nodeValue;
        }
    }

    return json_encode(["odp" => (int) $result[0], "pdp" => (int) $result[1], "confirm" => (int) $result[2]]);
}

// lampung
function CoronaLampung()
{
    $url = "https://geoportal.lampungprov.go.id/gis/rest/services/Kesehatan/COVID19_KABUPATEN/FeatureServer/0/query?f=json&where=OBJECTID+%3E+0&outFields=*&returnGeometry=false&fbclid=IwAR2kiLexsOlYgMxTWykITW3qJNStatBAm5HGcTeDKHUSy1b0VYoUONZZ1S8";

    $data_lampung = [];
    $key = 0;
    $get_contents = json_decode(file_get_contents($url), true);
    foreach ($get_contents['features'] as $value) {
        $data_lampung[$key]['city'] = $value['attributes']['kabupaten'];
        $data_lampung[$key]['odp'] = (int) $value['attributes']['odp'];
        $data_lampung[$key]['pdp'] = (int) $value['attributes']['pdp'];
        $data_lampung[$key]['confirm'] = (int) $value['attributes']['hsp'];
        $key++;
    }

    $count_odp = array_sum(array_column($data_lampung, 'odp'));
    $count_pdp = array_sum(array_column($data_lampung, 'pdp'));
    $count_confirm = array_sum(array_column($data_lampung, 'confirm'));
    return json_encode(["odp" => (int) $count_odp, "pdp" => (int) $count_pdp, "confirm" => (int) $count_confirm, "detail" => $data_lampung]);
}


//banten
function CoronaBanten()
{
    $url = "https://infocorona.bantenprov.go.id/home";
    $get_contents = file_get_contents($url);
    $DOM = new \DOMDocument();
    @$DOM->loadHTML(get_string_between($get_contents, '<!-- end Home Slider -->', '</section>'));
    $pList = $DOM->getElementsByTagName("b");
    $hasil = [];
    $arr = [
        PHP_EOL => "",
        "  " => ""
    ];
    foreach ($pList as $cal) {
        $hasil[] = explode(" ", strtr($cal->nodeValue, $arr))[0];
    }

    return json_encode(["odp" => (int) $hasil[0], "pdp" => (int) $hasil[1], "confirm" => (int) $hasil[2]]);
}

//sulsel
function CoronaSulsel()
{
    $url = "https://covid19.sulselprov.go.id";
    $arrContextOptions = array(
        "ssl" => array(
            "verify_peer" => false,
            "verify_peer_name" => false,
        ),
    );

    $get_contents = file_get_contents($url, false, stream_context_create($arrContextOptions));
    $DOM = new \DOMDocument();
    @$DOM->loadHTML($get_contents);
    $xp    = new \DOMXPath($DOM);
    $nodes = $xp->query('//span[@style="font-size: 100px; font-weight:bold "]');
    $result = [];
    foreach ($nodes as $element) {
        $nodes = $element->childNodes;
        foreach ($nodes as $node) {
            $result[] = $node->nodeValue;
        }
    }

    return json_encode(["odp" => (int) $result[0], "pdp" => (int) $result[1], "confirm" => (int) $result[2]]);
}

//ntb
function CoronaNtb()
{
    $url = "https://corona.ntbprov.go.id";
    $get_contents = file_get_contents($url);
    $DOM = new \DOMDocument();
    @$DOM->loadHTML($get_contents);
    $xp    = new \DOMXPath($DOM);
    $nodes = $xp->query('//div[@class="card-body weather-small"]');
    $result = [];
    $arr_filter  = [
        PHP_EOL => "",
        "\t" => "",
        "\n" => "",
        "   " => "",
        "  " => ""
    ];
    foreach ($nodes as $element) {
        $nodes = $element->childNodes;
        foreach ($nodes as $node) {
            $result[] = array_filter(explode(" ", strtr($node->nodeValue, $arr_filter)))[1];
        }
    }
    $data = array_filter($result);
    return json_encode(["odp" => (int) $data[1], "pdp" => (int) $data[4], "confirm" => $data[7]]);
}


//kaltim
function CoronaKaltim()
{

    $url = "http://covid19.kaltimprov.go.id";
    $get_contents = file_get_contents($url);
    $DOM = new \DOMDocument();
    @$DOM->loadHTML($get_contents);
    $xp    = new \DOMXPath($DOM);
    $nodes = $xp->query('//div[@class="right"]');
    $result = [];
    $arr_filter  = [
        PHP_EOL => "",
        "\t" => "",
    ];
    foreach ($nodes as $element) {
        $nodes = $element->childNodes;
        foreach ($nodes as $node) {
            $result[] = explode(" ", strtr($node->nodeValue, $arr_filter))[0];
        }
    }
    return json_encode(["odp" => (int) $result[2], "pdp" => (int) $result[1], "confirm" => (int) $result[0]]);
}


//kalbar
function CoronaKalbar()
{
    $url = "https://dinkes.kalbarprov.go.id/covid-19";
    $arrContextOptions = array(
        "ssl" => array(
            "verify_peer" => false,
            "verify_peer_name" => false,
        ),
    );

    $get_contents = file_get_contents($url, false, stream_context_create($arrContextOptions));
    $DOM = new \DOMDocument();
    @$DOM->loadHTML($get_contents);
    $xp    = new \DOMXPath($DOM);
    $nodes = $xp->query('//h2[@class="elementor-cta__title elementor-cta__content-item elementor-content-item"]');
    $result = [];
    $arr_filter =  ["\t" => "", "\n" => ""];
    foreach ($nodes as $element) {
        $nodes = $element->childNodes;
        foreach ($nodes as $node) {
            $result[] = explode(" ", strtr($node->nodeValue, $arr_filter))[0];
        }
    }

    return json_encode(["odp" => (int) $result[1], "pdp" => (int) $result[0], "confirm" => "Belum ada Data"]);
}

// maluku utara
function CoronaMalut()
{
    $url = "http://corona.malutprov.go.id/";
    $arrContextOptions = array(
        "ssl" => array(
            "verify_peer" => false,
            "verify_peer_name" => false,
        ),
    );

    $get_contents = file_get_contents($url, false, stream_context_create($arrContextOptions));
    $DOM = new \DOMDocument();
    @$DOM->loadHTML($get_contents);
    $xp    = new \DOMXPath($DOM);
    $nodes = $xp->query('//p[@class="card-text"]');
    $result = [];
    $arr_filter =  ["\t" => "", "\n" => ""];
    foreach ($nodes as $element) {
        $nodes = $element->childNodes;
        foreach ($nodes as $node) {
            $result[] = $node->nodeValue;
        }
    }

    return json_encode(["odp" => (int) $result[1], "pdp" => (int) $result[2], "confirm" => (int) $result[3]]);
}

//kepri
function CoronaKepri()
{
    $url = "https://corona.kepriprov.go.id/data/";
    $arrContextOptions = array(
        "ssl" => array(
            "verify_peer" => false,
            "verify_peer_name" => false,
        ),
    );

    $get_contents = file_get_contents($url, false, stream_context_create($arrContextOptions));
    $DOM = new \DOMDocument();
    @$DOM->loadHTML($get_contents);
    $xp    = new \DOMXPath($DOM);
    $nodes = $xp->query('//span[@class="elementor-price-table__integer-part"]');
    $result = [];
    $arr_filter =  ["\t" => "", "\n" => ""];
    foreach ($nodes as $element) {
        $nodes = $element->childNodes;
        foreach ($nodes as $node) {
            $result[] = $node->nodeValue;
        }
    }

    return json_encode(["odp" => (int) $result[0], "pdp" => (int) $result[1], "confirm" => (int) $result[2]]);
}

//Riau
function CoronaRiau()
{
    $url = "https://corona.riau.go.id";
    $arrContextOptions = array(
        "ssl" => array(
            "verify_peer" => false,
            "verify_peer_name" => false,
        ),
    );

    $get_contents = file_get_contents($url, false, stream_context_create($arrContextOptions));
    $DOM = new \DOMDocument();
    @$DOM->loadHTML($get_contents);
    $xp    = new \DOMXPath($DOM);
    $nodes = $xp->query('//span[@data-from-value="0"]');
    $result = [];
    foreach ($nodes as $element) {
        $result[] = $element->getAttribute("data-to-value");
    }
    return json_encode(["odp" => (int) $result[0], "pdp" => (int) $result[1], "confirm" => (int) $result[2]]);
}

//Maluku
function CoronaMaluku()
{
    $url = "https://corona.malukuprov.go.id";
    $arrContextOptions = array(
        "ssl" => array(
            "verify_peer" => false,
            "verify_peer_name" => false,
        ),
    );

    $get_contents = file_get_contents($url, false, stream_context_create($arrContextOptions));
    $DOM = new \DOMDocument();
    @$DOM->loadHTML($get_contents);
    $xp    = new \DOMXPath($DOM);
    $nodes = $xp->query('//span[@data-from-value="0"]');
    $result = [];
    foreach ($nodes as $element) {
        $result[] = $element->getAttribute("data-to-value");
    }
    return json_encode(["odp" => (int) $result[0], "pdp" => (int) $result[1], "confirm" => (int) $result[2]]);
}