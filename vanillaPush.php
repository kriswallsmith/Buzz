<?php
$urls = [];

function parseUrl($headers, $handle)
{
    global $urls;
    foreach ($headers as $header) {
        if (strpos($header, ':path:') === 0) {
            $path = substr($header, 6);
            $url = curl_getinfo($handle)['url'];
            $url = str_replace(
                parse_url($url, PHP_URL_PATH),
                $path,
                $url
            );
            $urls[$url] = $handle;
        }
    }
}

function getUrl($handle)
{
    $found = false;
    global $urls;
    foreach ($urls as $url => $h) {
        if ($handle == $h) {
            $found = $url;
        }
    }
    if (!$found) {
        $found = curl_getinfo($handle)['url'];
    }

    return $found;
}


function get_request($url)
{
    $cb = function ($parent, $pushed, $headers) {
        curl_setopt($pushed, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($pushed, CURLOPT_HEADER, true);

        //FIXME These two lines will cause segmentation fault
        //curl_setopt($pushed, CURLOPT_HEADERFUNCTION, null);
        //curl_setopt($pushed, CURLOPT_WRITEFUNCTION, null);

        parseUrl($headers, $pushed);

        return CURL_PUSH_OK;
    };

    $mh = curl_multi_init();

    curl_multi_setopt($mh, CURLMOPT_PIPELINING, CURLPIPE_MULTIPLEX);
    curl_multi_setopt($mh, CURLMOPT_PUSHFUNCTION, $cb);

    $curl = curl_init();
    curl_reset($curl);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($curl, CURLOPT_MAXREDIRS, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($curl, CURLOPT_HTTPGET, true);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, true);

    curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
    curl_setopt($curl, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_FAILONERROR, false);
    $originalResponseContent = '';


//FIXME Using timeout will weirdly disable pushed responses (content will be empty)
//curl_setopt($curl, CURLOPT_TIMEOUT, 30);

// -----------
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    //FIXME These 2 lines will case segmentation fault
    /*
    curl_setopt(
        $curl,
        CURLOPT_HEADERFUNCTION,
        function ($ch, $data) {
            return strlen($data);
        }
    );
    curl_setopt(
        $curl,
        CURLOPT_WRITEFUNCTION,
        function ($ch, $data) use (&$originalResponseContent) {
            $originalResponseContent .= $data;

            return strlen($data);
        }
    );
    */
    curl_multi_add_handle($mh, $curl);


// Start fetching the responses.
    $content = null;
    $active = null;
    do {
        $mrc = curl_multi_exec($mh, $active);
    } while ($mrc == CURLM_CALL_MULTI_PERFORM);

    while ($active && $mrc == CURLM_OK) {
        curl_multi_select($mh);
        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        while ($info = curl_multi_info_read($mh)) {
            if ($info['msg'] == CURLMSG_DONE) {
                $handle = $info['handle'];
                if ($handle !== null) {
                    $content = curl_multi_getcontent($handle);
                    $url = getUrl($handle);

// Debug
                    echo strlen($content).': '.$url."\n";

                    curl_multi_remove_handle($mh, $handle);
                    curl_close($handle);
                }
            }
        }


    }

    curl_multi_close($mh);

    return $originalResponseContent;
}

$url = 'https://http2.golang.org/serverpush';
$response = get_request($url);

echo strlen($response).': '.$url."\n\n";