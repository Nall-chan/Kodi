<?php

declare(strict_types=1);
### Konfig ab Zeile 10 !!!

if ($_IPS['SENDER'] != 'Kodi') {
    echo 'Dieses Script kann nicht direkt ausgeführt werden!';
    return;
}
##########   KONFIGURATION
#### Tabellarische Ansicht
# Folgende Parameter bestimmen das Aussehen der HTML-Tabelle in der die Favoriten dargestellt werden.

// Reihenfolge und Überschriften der Tabelle. Der vordere Wert darf nicht verändert werden.
// Die Reihenfolge, der hintere Wert (Anzeigetext) und die Reihenfolge sind beliebig änderbar.
$Config['Spalten'] = [
    'Thumbnail'=> '',
    'Title'    => 'Name',
    'Path'     => 'Path',
    'Type'     => 'Typ'
];
#### Mögliche Index-Felder
/*

| Index       | Typ     | Beschreibung                        |
| :---------: | :-----: | :---------------------------------: |
| Path        | string  | Path des Favoriten                  |
| Thumbnail   | string  | Vorschaubild des Favoriten          |
| Title       | string  | Name des Favoriten                  |
| Type        | string  | Art des Favoriten                   |
 */
// Breite der Spalten (Reihenfolge ist egal)
$Config['Breite'] = [
    'Thumbnail'=> '100em',
    'Title'    => '300em',
    'Path'     => '600em',
    'Type'     => '100em'
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
    // <th>-Tag Feld Title:
    'HFTitle'  => 'color:#ffffff; width:35px; align:left;',
    // <th>-Tag Feld Path:
    'HFPath'  => 'color:#ffffff; width:35px; align:left;',
    // <th>-Tag Feld Type:
    'HFType'  => 'color:#ffffff; width:35px; align:left;',
    // <tbody>-Tag:
    'B'    => '',
    // <tr>-Tag:
    'BRG'  => 'background-color:#000000; color:#ffffff;',
    'BRU'  => 'background-color:#080808; color:#ffffff;',
    // <td>-Tag Feld Thumbnail:
    'DFGThumbnail' => 'text-align:center;',
    'DFUThumbnail' => 'text-align:center;',
    // <td>-Tag Feld Title:
    'DFGTitle' => 'text-align:center;',
    'DFUTitle' => 'text-align:center;',
    // <td>-Tag Feld Path:
    'DFGPath' => 'text-align:center;',
    'DFUPath' => 'text-align:center;',
    // <td>-Tag Feld Type:
    'DFGType' => 'text-align:center;',
    'DFUType' => 'text-align:center;',
    // ^- Der Buchstabe "G" steht für gerade, "U" für ungerade.
];

### Konfig ENDE !!!
echo serialize($Config);
