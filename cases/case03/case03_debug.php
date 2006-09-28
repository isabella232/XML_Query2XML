<?php
require_once('XML/Query2XML.php');
require_once('DB.php');
$query2xml = XML_Query2XML::factory(DB::connect('mysql://root@localhost/Query2XML_Tests'));

require_once('Log.php');
$debugLogger = &Log::factory('file', 'case03.log', 'Query2XML');
$query2xml->enableDebugLog($debugLogger);

$query2xml->startProfiling();


$dom = $query2xml->getXML(
    "SELECT
        *
     FROM
        artist",
    array(
        'rootTag' => 'music_library',
        'rowTag' => 'artist',
        'idColumn' => 'artistid',
        'elements' => array(
            'artistid',
            'name',
            'birth_year',
            'birth_place',
            'genre',
            'albums' => array(
                'sql' => array(
                    'data' => array(
                        'artistid'
                    ),
                    'query' => 'SELECT * FROM album WHERE artist_id = ?'
                ),
                'sql_options' => array(
                    'uncached'      => true,
                    'single_record' => false,
                    'merge'         => false,
                    'merge_master'  => false
                ),
                'rootTag' => 'albums',
                'rowTag' => 'album',
                'idColumn' => 'albumid',
                'elements' => array(
                    'albumid',
                    'title',
                    'published_year',
                    'comment'
                )
            )
        )
    )
);

header('Content-Type: application/xml');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";

require_once('XML/Beautifier.php');
$beautifier = new XML_Beautifier();
print $beautifier->formatString($dom->saveXML());

require_once('File.php');
$fp = new File();
$fp->write('case03.profile', $query2xml->getProfile(), FILE_MODE_WRITE);
?>