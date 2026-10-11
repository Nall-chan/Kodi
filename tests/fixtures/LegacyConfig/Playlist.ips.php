<?php

declare(strict_types=1);
### Konfig ab Zeile 10 !!!

if ($_IPS['SENDER'] != 'Kodi') {
    echo 'Dieses Script kann nicht direkt ausgeführt werden!';
    return;
}
##########   KONFIGURATION
#### Tabellarische Ansicht
# Folgende Parameter bestimmen das Aussehen der HTML-Tabelle in der die Playlist dargestellt wird.

// Reihenfolge und Überschriften der Tabelle. Der vordere Wert darf nicht verändert werden.
// Die Reihenfolge, der hintere Wert (Anzeigetext) und die Reihenfolge sind beliebig änderbar.
$Config['Spalten'] = [
    'Play'     => '',
    'Position' => 'Pos',
    'Type'     => 'Type',
    'Title'    => 'Titel',
    'Showtitle'=> 'Serie',
    'Season'   => 'S',
    'Episode'  => 'E',
    'Album'    => 'Album',
    'Track'    => 'Track',
    'Disc'     => 'Disc',
    'Genre'    => 'Stil',
    'Artist'   => 'Interpret',
    'Year'     => 'Jahr',
    'Runtime'  => 'Dauer'

];
#### Mögliche Index-Felder
/*

| Index            | Typ     | Beschreibung                        |
| :--------------: | :-----: | :---------------------------------: |
| Play             |  kein   | Play-Icon                           |
| Position         | integer | Position in der Playlist            |
| Type             | string  | Typ des Eintrags                    |
| Id               | integer | UID der Datei in der Kodi-Datenbank |
| Title            | string  | Titel                               |
| Showtitle        | string  | Serienname                          |
| Season           | integer | Season                              |
| Episode          | integer | Episode                             |
| Genre            | string  | Genre                               |
| Album            | string  | Album                               |
| Artist           | string  | Interpret                           |
| Runtime          | integer | Länge in Sekunden                   |
| Disc             | integer | Disc                                |
| Track            | integer | Tracknummer im Album                |
| Url              | string  | Pfad der Playlist                   |
| Year             | integer | Jahr, soweit hinterlegt             |
 */
// Breite der Spalten (Reihenfolge ist egal)
$Config['Breite'] = [
    'Play'      => '50em',
    'Position'  => '50em',
    'Type'      => '100em',
    'Showtitle' => '300em',
    'Title'     => '300em',
    'Season'    => '50em',
    'Episode'   => '50em',
    'Genre'     => '200em',
    'Album'     => '200em',
    'Disc'      => '50em',
    'Track'     => '50em',
    'Artist'    => '200em',
    'Year'      => '50em',
    'Runtime'   => '100em'
];
// Style Informationen der Tabelle
$Config['Style'] = [
    // <table>-Tag:
    'T'    => 'margin:0 auto; font-size:0.8em;',
    // <thead>-Tag:
    'H'    => '',
    // <tr>-Tag im thead-Bereich:
    'HR'   => '',
    // <th>-Tag Feld Play:
    'HFPlay'  => 'color:#ffffff; width:35px; align:left;',
    // <th>-Tag Feld Position:
    'HFPosition'  => 'color:#ffffff; width:35px; align:left;',
    // <th>-Tag Feld Type:
    'HFType'  => 'color:#ffffff; width:35px; align:left;',
    // <th>-Tag Feld Title:
    'HFTitle'  => 'color:#ffffff; width:35px; align:left;',
    // <th>-Tag Feld Showtitle:
    'HFShowtitle'  => 'color:#ffffff; width:35px; align:left;',
    // <th>-Tag Feld Season:
    'HFSeason'  => 'color:#ffffff; width:35px; align:left;',
    // <th>-Tag Feld Episode:
    'HFEpisode'  => 'color:#ffffff; width:35px; align:left;',
    // <th>-Tag Feld Album:
    'HFAlbum'  => 'color:#ffffff; width:35px; align:left;',
    // <th>-Tag Feld Disc:
    'HFDisc'  => 'color:#ffffff; width:35px; align:left;',
    // <th>-Tag Feld Track:
    'HFTrack'  => 'color:#ffffff; width:35px; align:left;',
    // <th>-Tag Feld Genre:
    'HFGenre'  => 'color:#ffffff; width:35px; align:left;',
    // <th>-Tag Feld Artist:
    'HFArtist'  => 'color:#ffffff; width:35px; align:left;',
    // <th>-Tag Feld Year:
    'HFYear'  => 'color:#ffffff; width:35px; align:left;',
    // <th>-Tag Feld Runtime:
    'HFRuntime'  => 'color:#ffffff; width:35px; align:left;',
    // <tbody>-Tag:
    'B'    => '',
    // <tr>-Tag:
    'BRG'  => 'background-color:#000000; color:#ffffff;',
    'BRU'  => 'background-color:#080808; color:#ffffff;',
    'BRA'  => 'background-color:#808000; color:#ffffff;',
    // <td>-Tag Feld Play:
    'DFGPlay' => 'text-align:center;',
    'DFUPlay' => 'text-align:center;',
    'DFAPlay' => 'text-align:center;',
    // <td>-Tag Feld Position:
    'DFGPosition' => 'text-align:center;',
    'DFUPosition' => 'text-align:center;',
    'DFAPosition' => 'text-align:center;',
    // <td>-Tag Feld Type:
    'DFGType' => 'text-align:center;',
    'DFUType' => 'text-align:center;',
    'DFAType' => 'text-align:center;',
    // <td>-Tag Feld Showtitle:
    'DFGShowtitle' => 'text-align:center;',
    'DFUShowtitle' => 'text-align:center;',
    'DFAShowtitle' => 'text-align:center;',
    // <td>-Tag Feld Title:
    'DFGTitle' => 'text-align:center;',
    'DFUTitle' => 'text-align:center;',
    'DFATitle' => 'text-align:center;',
    // <td>-Tag Feld Season:
    'DFGSeason' => 'text-align:center;',
    'DFUSeason' => 'text-align:center;',
    'DFASeason' => 'text-align:center;',
    // <td>-Tag Feld Episode:
    'DFGEpisode' => 'text-align:center;',
    'DFUEpisode' => 'text-align:center;',
    'DFAEpisode' => 'text-align:center;',
    // <td>-Tag Feld Album:
    'DFGAlbum' => 'text-align:center;',
    'DFUAlbum' => 'text-align:center;',
    'DFAAlbum' => 'text-align:center;',
    // <td>-Tag Feld Disc:
    'DFGDisc' => 'text-align:center;',
    'DFUDisc' => 'text-align:center;',
    'DFADisc' => 'text-align:center;',
    // <td>-Tag Feld Track:
    'DFGTrack' => 'text-align:center;',
    'DFUTrack' => 'text-align:center;',
    'DFATrack' => 'text-align:center;',
    // <td>-Tag Feld Genre:
    'DFGGenre' => 'text-align:center;',
    'DFUGenre' => 'text-align:center;',
    'DFAGenre' => 'text-align:center;',
    // <td>-Tag Feld Artist:
    'DFGArtist' => 'text-align:center;',
    'DFUArtist' => 'text-align:center;',
    'DFAArtist' => 'text-align:center;',
    // <td>-Tag Feld Year:
    'DFGYear' => 'text-align:center;',
    'DFUYear' => 'text-align:center;',
    'DFAYear' => 'text-align:center;',
    // <td>-Tag Feld Runtime:
    'DFGRuntime' => 'text-align:center;',
    'DFURuntime' => 'text-align:center;',
    'DFARuntime' => 'text-align:center;'
    // ^- Der Buchstabe "G" steht für gerade, "U" für ungerade., "A" für Aktiv
];
### Konfig ENDE !!!
echo serialize($Config);
