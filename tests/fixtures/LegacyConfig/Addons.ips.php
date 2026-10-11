<?php

declare(strict_types=1);
### Konfig ab Zeile 10 !!!

if ($_IPS['SENDER'] != 'Kodi') {
    echo 'Dieses Script kann nicht direkt ausgeführt werden!';
    return;
}
##########   KONFIGURATION
#### Tabellarische Ansicht
# Folgende Parameter bestimmen das Aussehen der HTML-Tabelle in der die Addons dargestellt werden.

// Reihenfolge und Überschriften der Tabelle. Der vordere Wert darf nicht verändert werden.
// Die Reihenfolge, der hintere Wert (Anzeigetext) und die Reihenfolge sind beliebig änderbar.
$Config['Spalten'] = [
    'Thumbnail'=> '',
    'Enabled'  => 'Aktiv',
    'Name'     => 'Name',
    'Version'  => 'Version',
    'Summary'  => 'Beschreibung',
    //    "Description"=>"Beschreibung",
    //    "Path"=>"Path",
    //    "Author" => "Author",
    //    "Disclaimer" => "Disclaimer", //meist leer
    //    "Fanart" => "Fanart",         //meist leer
    //    "Broken" => "Defekt",
    //    "Rating"=>"Rating",
    //    "Type" => "Typ",
    //    "Addonid" => "Id",
    'Execute' => 'Starten'
];
#### Mögliche Index-Felder
/*

| Index       | Typ     | Beschreibung                        |
| :---------: | :-----: | :---------------------------------: |
| Addonid     | string  | Id des Addon                        |
| Author      | string  | Autor des Addon                     |
| Broken      | boolean | Zustand ob Addon defekt ist         |
| Description | string  | Beschreibung                        |
| Disclaimer  | string  | Haftungsausschluss                  |
| Enabled     | boolean | Addon aktiv (steuerbar)             |
| Execute     | boolean | Addon starten (steuerbar)           |
| Fanart      | string  | Fanart des Addon                    |
| Name        | string  | Name des Addon                      |
| Path        | string  | Path zum Addon auf dem System       |
| Rating      | integer | Bewertung                           |
| Summary     | string  | Zusammenfassung                     |
| Thumbnail   | string  | Vorschaubild des Addon              |
| Type        | string  | Art des Addon                       |
| Version     | string  | Version des Addon                   |
 */
// Breite der Spalten (Reihenfolge ist egal)
$Config['Breite'] = [
    'Thumbnail'=> '100em',
    'Enabled'  => '120em',
    'Name'     => '200em',
    'Version'  => '100em',
    'Summary'  => '400em',
    //    "Description"=>"400em",
    //    "Path"=>"100em",
    //    "Author" => "100em",
    //    "Disclaimer" => "100em", //meist leer
    //    "Fanart" => "100em",         //meist leer
    //    "Broken" => "50em",
    //    "Rating"=>"50em",
    //    "Type" => "100em",
    //    "Addonid" => "200em",
    'Execute' => '100em'

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
    // <th>-Tag Feld Enabled:
    'HFEnabled'  => 'color:#ffffff; width:35px; align:left;',
    // <th>-Tag Feld Name:
    'HFName'  => 'color:#ffffff; width:35px; align:left;',
    // <th>-Tag Feld Version:
    'HFVersion'  => 'color:#ffffff; width:35px; align:left;',
    // <th>-Tag Feld Summary:
    'HFSummary'  => 'color:#ffffff; width:35px; align:left;',
    // <th>-Tag Feld Description:
    'HFDescription'  => 'color:#ffffff; width:35px; align:left;',
    // <th>-Tag Feld Path:
    'HFPath'  => 'color:#ffffff; width:35px; align:left;',
    // <th>-Tag Feld Author:
    'HFAuthor'  => 'color:#ffffff; width:35px; align:left;',
    // <th>-Tag Feld Disclaimer:
    'HFDisclaimer'  => 'color:#ffffff; width:35px; align:left;',
    // <th>-Tag Feld Fanart:
    'HFFanart'  => 'color:#ffffff; width:35px; align:left;',
    // <th>-Tag Feld Broken:
    'HFBroken'  => 'color:#ffffff; width:35px; align:left;',
    // <th>-Tag Feld Rating:
    'HFRating'  => 'color:#ffffff; width:35px; align:left;',
    // <th>-Tag Feld Type:
    'HFType'  => 'color:#ffffff; width:35px; align:left;',
    // <th>-Tag Feld Addonid:
    'HFAddonid'  => 'color:#ffffff; width:35px; align:left;',
    // <th>-Tag Feld Execute:
    'HFExecute'  => 'color:#ffffff; width:35px; align:left;',
    // <tbody>-Tag:
    'B'    => '',
    // <tr>-Tag:
    'BRG'  => 'background-color:#000000; color:#ffffff;',
    'BRU'  => 'background-color:#080808; color:#ffffff;',
    // <td>-Tag Feld Thumbnail:
    'DFGThumbnail' => 'text-align:center;',
    'DFUThumbnail' => 'text-align:center;',
    // <td>-Tag Feld Enabled:
    'DFGEnabled' => 'text-align:center;',
    'DFUEnabled' => 'text-align:center;',
    // <td>-Tag Feld Name:
    'DFGName' => 'text-align:center;',
    'DFUName' => 'text-align:center;',
    // <td>-Tag Feld Version:
    'DFGVersion' => 'text-align:center;',
    'DFUVersion' => 'text-align:center;',
    // <td>-Tag Feld Summary:
    'DFGSummary' => 'text-align:center;',
    'DFUSummary' => 'text-align:center;',
    // <td>-Tag Feld Description:
    'DFGDescription' => 'text-align:center;',
    'DFUDescription' => 'text-align:center;',
    // <td>-Tag Feld Path:
    'DFGPath' => 'text-align:center;',
    'DFUPath' => 'text-align:center;',
    // <td>-Tag Feld Author:
    'DFGAuthor' => 'text-align:center;',
    'DFUAuthor' => 'text-align:center;',
    // <td>-Tag Feld Disclaimer:
    'DFGDisclaimer' => 'text-align:center;',
    'DFUDisclaimer' => 'text-align:center;',
    // <td>-Tag Feld Fanart:
    'DFGFanart' => 'text-align:center;',
    'DFUFanart' => 'text-align:center;',
    // <td>-Tag Feld Broken:
    'DFGBroken' => 'text-align:center;',
    'DFUBroken' => 'text-align:center;',
    // <td>-Tag Feld Rating:
    'DFGRating' => 'text-align:center;',
    'DFURating' => 'text-align:center;',
    // <td>-Tag Feld Type:
    'DFGType' => 'text-align:center;',
    'DFUType' => 'text-align:center;',
    // <td>-Tag Feld Addonid:
    'DFGAddonid' => 'text-align:center;',
    'DFUAddonid' => 'text-align:center;',
    // <td>-Tag Feld Execute:
    'DFGExecute' => 'text-align:center;',
    'DFUExecute' => 'text-align:center;',
    // ^- Der Buchstabe "G" steht für gerade, "U" für ungerade.
];

// CSS-Styles für die Klassen der Buttons.
// isenabled => Button für Addon ist aktivieren
// isdisabled => Button für Addon ist deaktivieren
// doenabled => Button für Addon aktivieren
// dodisabled => Button für Addon deaktivieren
//execute => Button für Addon ausführen
$Config['Button'] = [
    'isenabled' => '
background-image: linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-image: -o-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-image: -moz-linear-gradient(50% 0%, transparent 0px, rgba(0, 0, 0, 0.3) 28%, rgba(0, 0, 0, 0.3) 100%);
background-image: -webkit-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-image: -ms-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
color: rgba(255, 255, 255, 0.3);
color: rgb(255, 255, 255);
background-color: rgba(255,255,255,0.1);
background-color: rgb(0, 255, 0);
width: 25%;
display: inline-block;
margin: 2px 0px 1px 3px;
font-family: arial,sans-serif; 
font-size: 17px;
line-height: 28px;
border-color: transparent;
border-style: solid;
border-width: 1px 0px;
min-height: 28px;
padding: 0px 10px;
vertical-align: middle;',

    'isdisabled'=> '
background-image: linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-image: -o-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-image: -moz-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-image: -webkit-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-image: -ms-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-color: rgba(255, 255, 255, 0.3);
width: 25%;
display: inline-block;
margin: 2px 0px 1px 3px;
font-family: arial,sans-serif; 
font-size: 17px;
line-height: 28px;
border-color: transparent;
border-style: solid;
border-width: 1px 0px;
min-height: 28px;
padding: 0px 10px;
vertical-align: middle;',

    'doenabled' => '
background-image: -webkit-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-image: -o-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-image: -moz-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-image: linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-image: -ms-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-color: rgba(0, 255, 0, 0.3);
width: 25%;
display: inline-block;
margin: 2px 0px 1px 3px;
font-family: arial,sans-serif; 
font-size: 17px;
line-height: 28px;
border-color: transparent;
border-style: solid;
border-width: 1px 0px;
min-height: 28px;
padding: 0px 10px;
vertical-align: middle;',

    'dodisabled'=> '
background-image: linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-image: -o-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-image: -moz-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-image: -webkit-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-image: -ms-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-color: rgba(255,255,255,0.1);
color: rgba(255, 255, 255, 0.3);
width: 25%;
display: inline-block;
margin: 2px 0px 1px 3px;
font-family: arial,sans-serif; 
font-size: 17px;
line-height: 28px;
border-color: transparent;
border-style: solid;
border-width: 1px 0px;
min-height: 28px;
padding: 0px 10px;
vertical-align: middle;',
    'execute'=> '
background-image: -webkit-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-image: -o-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-image: -moz-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-image: linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-image: -ms-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-color: rgb(0, 255, 0);
width: 80%;
display: inline-block;
margin: 2px 0px 1px 3px;
font-family: arial,sans-serif; 
font-size: 17px;
line-height: 28px;
border-color: transparent;
border-style: solid;
border-width: 1px 0px;
min-height: 28px;
padding: 0px 10px;
vertical-align: middle;',

];

### Konfig ENDE !!!
echo serialize($Config);
