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
    //    "Lastplayed" => "zuletzt gesehen",
    'Now'  => 'es läuft',
    'Next' => 'gleich läuft'

];
#### Mögliche Index-Felder
/*

| Index       | Typ     | Beschreibung                        |
| :---------: | :-----: | :---------------------------------: |
| Thumbnail   | string  | Senderlogo                          |
| Label       | string  | Name des Kanals                     |
| Lastplayed  | string  | Wann zuletzt gesehen                |
 */
// Breite der Spalten (Reihenfolge ist egal)
$Config['Breite'] = [
    'Thumbnail'=> '100em',
    'Label'    => '150em',
    'Now'      => '300em',
    'Next'     => '300em',
    //    "Lastplayed"=>"300em"
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
    // <th>-Tag Feld Now:
    'HFNow'  => 'color:#ffffff; width:35px; align:left;',
    // <th>-Tag Feld Next:
    'HFNext'  => 'color:#ffffff; width:35px; align:left;',
    // <th>-Tag Feld Lastplayed:
    'HFLastplayed'  => 'color:#ffffff; width:35px; align:left;',
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
    // <td>-Tag Feld Now:
    'DFGNow' => 'text-align:center;',
    'DFUNow' => 'text-align:center;',
    // <td>-Tag Feld Next:
    'DFGNext' => 'text-align:center;',
    'DFUNext' => 'text-align:center;',
    // <td>-Tag Feld Lastplayed:
    'DFGLastplayed' => 'text-align:center;',
    'DFULastplayed' => 'text-align:center;',
    // ^- Der Buchstabe "G" steht für gerade, "U" für ungerade.
];

### Konfig ENDE !!!
echo serialize($Config);
