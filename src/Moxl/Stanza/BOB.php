<?php

namespace Moxl\Stanza;

class BOB {
    static function answer($to, $id, $cid, $type, $base64) {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $data = $dom->createElementNS('urn:xmpp:bob', 'data', $base64);
        $data->setAttribute('cid', $cid);
        $data->setAttribute('type', $type);
        $data->setAttribute('max-age', '86400');

        \Moxl\API::request(\Moxl\API::iqWrapper($data, $to, 'result', $id));
    }
}
