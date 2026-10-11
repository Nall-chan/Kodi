<?php

declare(strict_types=1);
### Konfig ab Zeile 10 !!!

if ($_IPS['SENDER'] != 'Kodi') {
    echo 'Dieses Script kann nicht direkt ausgeführt werden!';
    return;
}
##########   KONFIGURATION
#### Tabellarische Ansicht
# Folgende Parameter bestimmen das Aussehen der HTML-Tabelle in der die Radio Kanäle dargestellt werden.

// Reihenfolge und Überschriften der Tabelle. Der vordere Wert darf nicht verändert werden.
// Die Reihenfolge, der hintere Wert (Anzeigetext) und die Reihenfolge sind beliebig änderbar.
$Config['Spalten'] = [
    'Thumbnail'=> '',
    'Label'    => 'Name',
    'Runtime'  => 'Dauer',
    'Starttime'=> 'Startzeit',
    //    "Endtime"=>"Endzeit",
    //    "Plot"=>"Handlung",
    //    "Genre"=>"Genre",
    'Channel'=> 'Kanal'
];
#### Mögliche Index-Felder
/*

| Index       | Typ     | Beschreibung                        |
| :---------: | :-----: | :---------------------------------: |
| Thumbnail   | string  | Thumbnail der Aufzeichnung          |
| Label       | string  | Name der Aufzeichnung               |
| Runtime     | string  | Laufzeit der Aufzeichnung           |
| Starttime   | string  | Beginn der Aufzeichnung             |
| Endtime     | string  | Ende der Aufzeichnung               |
| Plot        | string  | Handlung der Aufzeichnung           |
| Genre       | string  | Genre der Aufzeichnung              |
| Channel     | string  | Kanal der Aufzeichnung              |

 */
// Breite der Spalten (Reihenfolge ist egal)
$Config['Breite'] = [
    'Thumbnail'=> '100em',
    'Label'    => '300em',
    'Runtime'  => '100em',
    'Starttime'=> '200em',
    'Channel'  => '150em'
];
// Style Informationen der Tabelle
$Config['Style'] = [
    // <table>-Tag:
    'T'    => 'margin:0 auto; font-size:0.8em;',
    // <thead>-Tag:
    'H'    => '',
    // <tr>-Tag im thead-Bereich:
    'HR'   => '',
    // <th>-Tag Feld Thumbnail:
    'HFThumbnail'  => 'color:#ffffff; width:35px; align:left;',
    // <th>-Tag Feld Label:
    'HFLabel'  => 'color:#ffffff; width:35px; align:left;',
    // <th>-Tag Feld Runtime:
    'HFRuntime'  => 'color:#ffffff; width:35px; align:left;',
    // <th>-Tag Feld Starttime:
    'HFStarttime'  => 'color:#ffffff; width:35px; align:left;',
    // <th>-Tag Feld Channel:
    'HFChannel'  => 'color:#ffffff; width:35px; align:left;',
    // <tbody>-Tag:
    'B'    => '',
    // <tr>-Tag:
    'BRG'  => 'background-color:#000000; color:#ffffff;',
    'BRU'  => 'background-color:#080808; color:#ffffff;',
    // <td>-Tag Feld Thumbnail:
    'DFGThumbnail' => 'text-align:center;',
    'DFUThumbnail' => 'text-align:center;',
    // <td>-Tag Feld Label:
    'DFGLabel' => 'text-align:center;',
    'DFULabel' => 'text-align:center;',
    // <td>-Tag Feld Runtime:
    'DFGRuntime' => 'text-align:center;',
    'DFURuntime' => 'text-align:center;',
    // <td>-Tag Feld Starttime:
    'DFGStarttime' => 'text-align:center;',
    'DFUStarttime' => 'text-align:center;',
    // <td>-Tag Feld Channel:
    'DFGChannel' => 'text-align:center;',
    'DFUChannel' => 'text-align:center;',
    // ^- Der Buchstabe "G" steht für gerade, "U" für ungerade.
];

### Konfig ENDE !!!
echo serialize($Config);
