<?php
require_once('XML/Query2XML.php');
require_once('DB.php');
$query2xml = XML_Query2XML::factory(DB::connect('mysql://root@localhost/Query2XML_Tests'));
$dom = $query2xml->getXML(
    "SELECT
         *
     FROM
         customer c
         LEFT JOIN sale s ON c.customerid = s.customer_id
         LEFT JOIN album al ON s.album_id = al.albumid
         LEFT JOIN artist ar ON al.artist_id = ar.artistid",
    array(
        'rootTag' => 'music_store',
        'rowTag' => 'customer',
        'idColumn' => 'customerid',
        'elements' => array(
            'customerid',
            'first_name',
            'last_name',
            'email',
            'sales' => array(
                'rootTag' => 'sales',
                'rowTag' => 'sale',
                'idColumn' => 'saleid',
                'elements' => array(
                    'saleid',
                    'timestamp',
                    'date' => "!return substr(\"{\$record['timestamp']}\", 0, strpos(\"{\$record['timestamp']}\", ' '));",
                    'time' => "!return substr(\"{\$record['timestamp']}\", strpos(\"{\$record['timestamp']}\", ' ') + 1);",
                    'album' => array(
                        'rootTag' => '',
                        'rowTag' => 'album',
                        'idColumn' => 'albumid',
                        'elements' => array(
                            'albumid',
                            'title',
                            'published_year',
                            'comment',
                            'artist' => array(
                                'rootTag' => '',
                                'rowTag' => 'artist',
                                'idColumn' => 'artistid',
                                'elements' => array(
                                    'artistid',
                                    'name',
                                    'birth_year',
                                    'birth_place',
                                    'genre'
                                ) //artist elements
                            ) //artist array
                        ) //album elements
                    ) //album array
                ) //sales elements
            ) //sales array
        ) //root elements
    ) //root
); //getXML method call

$root = $dom->firstChild;
$root->setAttribute('date_generated', date("Y-m-d\TH:i:s", 1124801570));

header('Content-Type: application/xml');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";

require_once('XML/Beautifier.php');
$beautifier = new XML_Beautifier();
print $beautifier->formatString($dom->saveXML());
?>