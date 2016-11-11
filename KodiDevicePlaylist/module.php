<?

require_once(__DIR__ . "/../KodiClass.php");  // diverse Klassen
/*
 * @addtogroup kodi
 * @{
 *
 * @package       Kodi
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2016 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       1.0
 * @example <b>Ohne</b>
 */

/**
 * KodiDevicePlaylist Klasse für den Namespace Playlist der KODI-API.
 * Erweitert KodiBase.
 *
 * @package       Kodi
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2016 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       1.0
 * @example <b>Ohne</b>
 */
class KodiDevicePlaylist extends KodiBase
{

    /**
     * PlaylistID für Audio
     * 
     * @access private
     * @static int
     * @value 0
     */
    const Audio = 0;

    /**
     * PlaylistID für Video
     * 
     * @access private
     * @static int
     * @value 1
     */
    const Video = 1;

    /**
     * PlaylistID für Bilder
     * 
     * @access private
     * @static int
     * @value 2
     */
    const Pictures = 2;

    /**
     * RPC-Namespace
     * 
     * @access private
     *  @var string
     * @value 'Application'
     */
    static $Namespace = array('Playlist', 'Player');

    /**
     * Alle Properties des RPC-Namespace
     * 
     * @access private
     * @var array 
     */
    static $Properties = array();

    /**
     * Zuordnung der von Kodi gemeldeten Medientypen zu den PlaylistIDs
     * 
     * @access private
     *  @var array Key ist der Medientyp, Value die PlaylistID
     */
    static $Playertype = array(
        "song" => 0,
        "audio" => 0,
        "radio" => 0,
        "video" => 1,
        "episode" => 1,
        "movie" => 1,
        "tv" => 1,
        "picture" => 2,
        "pictures" => 2
    );

    /**
     * Alle Properties eines Item
     * 
     * @access private
     *  @var array 
     */
    static $ItemList = array(
        "title",
        "artist",
        "albumartist",
        "genre",
        "year",
        "rating",
        "album",
        "track",
        "duration",
        "comment",
        "lyrics",
        "musicbrainztrackid",
        "musicbrainzartistid",
        "musicbrainzalbumid",
        "musicbrainzalbumartistid",
        "playcount",
        "fanart",
        "director",
        "trailer",
        "tagline",
        "plot",
        "plotoutline",
        "originaltitle",
        "lastplayed",
        "writer",
        "studio",
        "mpaa",
        "cast",
        "country",
        "imdbnumber",
        "premiered",
        "productioncode",
        "runtime",
        "set",
        "showlink",
        "streamdetails",
        "top250",
        "votes",
        "firstaired",
        "season",
        "episode",
        "showtitle",
        "thumbnail",
        "file",
        "resume",
        "artistid",
        "albumid",
        "tvshowid",
        "setid",
        "watchedepisodes",
        "disc",
        "tag",
        "art",
        "genreid",
        "displayartist",
        "albumartistid",
        "description",
        "theme",
        "mood",
        "style",
        "albumlabel",
        "sorttitle",
        "episodeguide",
        "uniqueid",
        "dateadded",
        "channel",
        "channeltype",
        "hidden",
        "locked",
        "channelnumber",
        "starttime",
        "endtime");

    /**
     * Kleiner Teil der Properties eines Item
     * 
     * @access private
     *  @var array 
     */
    static $ItemListSmall = array(
        "title",
        "artist",
        "albumartist",
        "genre",
        "year",
        "album",
        "track",
        "duration",
        "plot",
        "runtime",
        "season",
        "episode",
        "showtitle",
        "thumbnail",
        "file",
        "disc",
        "albumlabel",
    );

    /**
     * Eigene PlaylistId
     * 
     * @access private
     *  @var int Kodi-Playlist-ID dieser Instanz 
     */
    private $PlaylistId = null;

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Create()
    {
        parent::Create();
        $this->RegisterPropertyInteger('PlaylistID', 0);
        $this->RegisterPropertyBoolean('showPlaylist', true);
        $ID = @$this->GetIDForIdent('PlaylistDesign');
        if ($ID == false)
            $ID = $this->RegisterScript('PlaylistDesign', 'Playlist Config', $this->CreatePlaylistConfigScript(), -7);
        IPS_SetHidden($ID, true);
        $this->RegisterPropertyInteger("Playlistconfig", $ID);
    }

    /**
     * Interne Funktion des SDK.
     * 
     * @access public
     */
    public function Destroy()
    {
        $this->UnregisterHook('/hook/KodiPlaylist' . $this->InstanceID);
        $this->UnregisterProfile("Tracklist." . $this->InstanceID . ".Kodi");
    }

    /**
     * Interne Funktion des SDK.
     * 
     * @access public
     */
    public function ApplyChanges()
    {

        if ($this->ReadPropertyBoolean('showPlaylist'))
        {
            $this->RegisterVariableString("Playlist", "Playlist", "~HTMLBox", 2);
            $sid = $this->RegisterScript("WebHookPlaylist", "WebHookPlaylist", '<? //Do not delete or modify.
if (isset($_GET["Index"]))
    if (IPS_RequestAction(' . $this->InstanceID . ',"position",$_GET["Index"]) === true) echo "OK";
', -8);
            IPS_SetHidden($sid, true);
            if (IPS_GetKernelRunlevel() == KR_READY)
                $this->RegisterHook('/hook/KodiPlaylist' . $this->InstanceID, $sid);

            $ID = @$this->GetIDForIdent('PlaylistDesign');
            if ($ID == false)
                $ID = $this->RegisterScript('PlaylistDesign', 'Playlist Config', $this->CreatePlaylistConfigScript(), -7);
            IPS_SetHidden($ID, true);
        }
        else
        {
            $this->UnregisterVariable("Playlist");
            $this->UnregisterScript("WebHookPlaylist");
            if (IPS_GetKernelRunlevel() == KR_READY)
                $this->UnregisterHook('/hook/KodiPlaylist' . $this->InstanceID);
        }

        $this->RegisterProfileInteger("Tracklist." . $this->InstanceID . ".Kodi", "", "", "", 1, 1, 1);

        $this->RegisterVariableInteger("position", "Playlist Position", "Tracklist." . $this->InstanceID . ".Kodi", 1);
        $this->EnableAction("position");
        $this->ClearBufferList();
        $this->Init();
        parent::ApplyChanges();
    }

################## PRIVATE     

    /**
     * Setzt die Eigenschaften PlaylistId der Instanz
     * damit andere Funktionen Diese nutzen können
     * 
     * @access private
     */
    private function Init()
    {
        if (is_null($this->PlaylistId))
            $this->PlaylistId = $this->ReadPropertyInteger('PlaylistID');
    }

    /**
     * Dekodiert die empfangenen Daten und führt die Statusvariablen nach.
     * 
     * @access protected
     * @param string $Method RPC-Funktion ohne Namespace
     * @param object $KodiPayload Der zu dekodierende Datensatz als Objekt.
     */
    protected function Decode($Method, $KodiPayload)
    {
        $this->Init();
        //prüfen ob Player oder Playlist
        //Player nur bei neuer Position
        if (property_exists($KodiPayload, 'playlistid'))
        {
            if ($KodiPayload->playlistid <> $this->PlaylistId)
                return;
        }
        else if (property_exists($KodiPayload, 'player'))
        {
            if ($KodiPayload->player->playerid <> $this->PlaylistId)
                return;
        }
        else
        {
            if (property_exists($KodiPayload, 'type'))
            {
                if (self::$Playertype[(string) $KodiPayload->type] <> $this->PlayerId)
                    return false;
            }
            else
            {
                if (property_exists($KodiPayload, 'item'))
                {
                    if (property_exists($KodiPayload->item, 'channeltype'))
                    {
                        if (self::$Playertype[(string) $KodiPayload->item->channeltype] <> $this->PlaylistId)
                            return false;
                    }
                    else
                    {
                        if (self::$Playertype[(string) $KodiPayload->item->type] <> $this->PlaylistId)
                            return false;
                    }
                }
            }
        }
//        elseif (property_exists($KodiPayload, 'item'))
//        {
//            if (self::$Playertype[(string) $KodiPayload->item->type] <> $this->PlaylistId)
//                return false;
//        }
//        else
//            return false;

        $this->SendDebug($Method, $KodiPayload, 0);

        if (property_exists($KodiPayload, 'property'))
            $KodiPayload = $KodiPayload->property;

        switch ($Method)
        {
            case 'GetProperties':
            case 'OnPropertyChanged':
                foreach ($KodiPayload as $param => $value)
                {
                    switch ($param)
                    {
                        case "position":
                            if ($this->SetValueInteger($param, (int) $value + 1))// and ( $KodiPayload->playlistid <> -1))
                                $this->RefreshPlaylist();
                            break;
                        case "shuffled":
                            $this->ClearBufferList();
                            $this->SetBuffer('LastAddonItem', 0);
                            $KodiData = new Kodi_RPC_Data(self::$Namespace[0], 'GetItems', array('playlistid' => $this->PlaylistId, 'properties' => self::$ItemListSmall,));
                            $ret = $this->SendDirect($KodiData);
                            if (is_null($ret))
                                break;
                            if ($ret->limits->total > 0)
                                $items = $KodiData->ToArray($ret->items);
                            else
                                break;
                            foreach ($items as $Index => $item)
                            {
                                if ($item['title'] == "")
                                {
                                    if ($item['label'] <> "")
                                        $item['title'] = $item['label'];
                                    else
                                    {
                                        $parts = explode('/', $item['file']);
                                        $item['title'] = array_pop($parts);
                                    }
                                }
                                $this->AddItemToBufferList($Index, $item);
                            }
                            $this->SetBuffer('LastAddonItem', $Index);
                            $this->RefreshPlaylist(); //Liste neu bauen
                            break;
                    }
                }
                break;
            case 'OnStop':
                $this->SetValueInteger("position", 0);
            case 'OnPlay':
                $KodiData = new Kodi_RPC_Data(self::$Namespace[1]);
                $KodiData->GetProperties(array('playerid' => $this->PlaylistId, 'properties' => array("playlistid", "position")));
                $ret = $this->SendDirect($KodiData);
                if (!is_null($ret))
                    $this->Decode('GetProperties', $ret);
                $LastAddonItem = (int) $this->GetBuffer('LastAddonItem');
                if ($LastAddonItem > 0)
                    break;

                $KodiData = new Kodi_RPC_Data(self::$Namespace[0], 'GetItems', array('playlistid' => $this->PlaylistId, 'properties' => self::$ItemListSmall,));
                $ret = $this->SendDirect($KodiData);
                if (is_null($ret))
                    break;
                if ($ret->limits->total > 0)
                    $items = $KodiData->ToArray($ret->items);
                else
                    break;
                foreach ($items as $Index => $item)
                {
                    if ($item['title'] == "")
                    {
                        if ($item['label'] <> "")
                            $item['title'] = $item['label'];
                        else
                        {
                            $parts = explode('/', $item['file']);
                            $item['title'] = array_pop($parts);
                        }
                    }
                    $this->AddItemToBufferList($Index, $item);
                }
                $this->SetBuffer('LastAddonItem', $Index);
                $this->RefreshPlaylist(); //Liste neu bauen
                break;
            case 'OnAdd':
                $LastAddonItem = (int) $this->GetBuffer('LastAddonItem');
                if (($LastAddonItem > 0 ) and ( $LastAddonItem >= $KodiPayload->position))
                    break;
                $KodiData = new Kodi_RPC_Data(self::$Namespace[0], 'GetItems', array('playlistid' => $this->PlaylistId, 'properties' => self::$ItemListSmall, 'limits' => array('start' => $LastAddonItem)));
                $ret = $this->SendDirect($KodiData);
                if (is_null($ret))
                    break;
                if ($ret->limits->total > 0)
                    $items = $KodiData->ToArray($ret->items);
                else
                    break;
                foreach ($items as $Index => $item)
                {

                    if ($item['title'] == "")
                    {
                        if ($item['label'] <> "")
                            $item['title'] = $item['label'];
                        else
                        {
                            $parts = explode('/', $item['file']);
                            $item['title'] = array_pop($parts);
                        }
                    }
                    $this->SendDebug("AddBuffer", $item, 0);
                    $this->AddItemToBufferList($Index + $LastAddonItem, $item);
                }
                $this->SetBuffer('LastAddonItem', $Index + $LastAddonItem);
//                if ($ret->limits->total == $KodiPayload->position + 1)
//                {
                $this->SendDebug('REFRESH_HTML', 'REFRESH_HTML', 0);
                $this->RefreshPlaylist(); //Liste neu bauen
//                }
                break;
            case 'OnRemove':
                // function welche ein Item aus dem Buff entfernt
                $this->SendDebug("RemoveItemFromBuffer", $KodiPayload, 0);
                $this->RemoveItemFromBufferList($KodiPayload->position);
                $this->RefreshPlaylist(); //Liste neu bauen
                break;
            case 'OnClear':
                $this->SendDebug("ClearBuffer", $KodiPayload, 0);
                $this->SetValueInteger("position", 0);
                $this->ClearBufferList();
                $this->RefreshPlaylist(true);
                break;
            default:
                $this->SendDebug($Method, $KodiPayload, 0);
                break;
        }
    }

    /**
     * Liefert ein Array über alle aktiven Buffer.
     * 
     * @access private
     * @return array Array welches die Buffer enthält
     */
    private function GetBufferList()
    {
        $Buffers = unserialize($this->GetBuffer('Bufferlist'));
        if ($Buffers == false)
            return array();
        return $Buffers;
    }

    /**
     * Liefert alle Items aus den Buffern.
     * 
     * @access private
     * @param array $Bufferlist Enthält die Liste der Buffer
     * @return array Array mit allen Items aus den Buffern
     */
    private function GetItemsFromBufferList(array $Bufferlist)
    {
        $Line = "";
        foreach ($Bufferlist as $Buffer)
        {
            $Line .= $this->GetBuffer('Playlist' . $Buffer);
        }
        $Liste = unserialize($Line);
        if ($Liste == false)
            return array();
        return $Liste;
    }

    /**
     * Füllt Buffer mit den Daten der Items
     * 
     * @access private
     * @param array $Items Items welche in die Buffer geschrieben werden sollen.
     * @return array Array welche die befüllten Buffer enthält.
     */
    private function SetItemsToBufferList(array $Items)
    {
        $Lines = str_split(serialize($Items), 8000);
        foreach ($Lines as $BufferIndex => $BufferLine)
        {
            $this->SetBuffer('Playlist' . $BufferIndex, $BufferLine);
        }
        return array_keys($Lines);
    }

    /**
     * Schreibt eine Liste von genutzen Buffern.
     * 
     * @access private
     * @param array $List Array welches die genutzen Buffer enthält.
     */
    private function SetBufferList(array $List)
    {
        $this->SetBuffer('Bufferlist', serialize($List));
    }

    /**
     * Fügt ein Item ein oder löscht ein Item aus den Buffern.
     * 
     * @access private
     * @param int $Index Der Index des Item.
     * @param array $item Das Item zu einfügen in den Buffern. Ein leeres Array löscht einen Index aus den Buffern.
     */
    private function AddOrRemoveItemToBufferList(int $Index, array $item)
    {
        $OldBuffers = $this->GetBufferList();
        $this->SendDebug('Bufferlist old', count($OldBuffers), 0);

        $Liste = $this->GetItemsFromBufferList($OldBuffers);
        if (count($item) == 0)
            unset($Liste[$Index]);
        else
            $Liste[$Index] = $item;

        $NewBuffers = $this->SetItemsToBufferList($Liste);
        $this->SetBufferList($NewBuffers);
        $this->SendDebug('Bufferlist new', count($NewBuffers), 0);

        $DelBuffers = array_diff_key($OldBuffers, $NewBuffers);
        $this->SendDebug('Bufferlist del', count($DelBuffers), 0);
        foreach ($DelBuffers as $DelBuffer)
        {
            $this->SetBuffer('Playlist' . $DelBuffer, "");
            $this->SendDebug('Bufferlist' . $DelBuffer, 'DELETE', 0);
        }
    }

    /**
     * Fügt ein Item in die Buffer ein.
     * 
     * @access private
     * @param int $Index Der Index des Items.
     * @param array $item Das einzufügende Item.
     */
    private function AddItemToBufferList(int $Index, array $item)
    {
        $this->AddOrRemoveItemToBufferList($Index, $item);
    }

    /**
     * Löscht ein Item aus den Buffern.
     * 
     * @access private
     * @param int $Index Der Index des zu löschenden Items 
     */ private function RemoveItemFromBufferList(int $Index)
    {
        $this->AddOrRemoveItemToBufferList($Index, array());
    }

    /**
     * Leert alle Buffer.
     * 
     * @access private
     */
    private function ClearBufferList()
    {
        $OldBuffers = unserialize($this->GetBuffer('Bufferlist'));
        if ($OldBuffers === false)
        {
            $this->SendDebug('Bufferlist old', 'EMPTY', 0);
        }
        else
        {
            $this->SendDebug('Bufferlist old', count($OldBuffers), 0);
            foreach ($OldBuffers as $OldBuffer)
            {
                $this->SetBuffer('Playlist' . $OldBuffer, "");
            }
            $this->SetBuffer("Bufferlist", "");
        }
        $this->SetBuffer('LastAddonItem', 0);
    }

    /**
     * Erzeugt aus der Playlist eine HTML-Tabelle für eine ~HTMLBox-Variable.
     * 
     * @access private
     * @param bool $Empty Bei TRUE wird eine leere Tabelle erzeugt.
     */
    private function RefreshPlaylist($Empty = false)
    {
        if (!$this->ReadPropertyBoolean('showPlaylist'))
            return;
        $ScriptID = $this->ReadPropertyInteger('Playlistconfig');
        if ($ScriptID == 0)
            return;
        if (!IPS_ScriptExists($ScriptID))
            return;

        $result = IPS_RunScriptWaitEx($ScriptID, array('SENDER' => 'Kodi'));
        $Config = unserialize($result);
        if (($Config === false) or ( !is_array($Config)))
        {
            trigger_error('Error on read Playlistconfig-Script', E_USER_NOTICE);
            return;
        }

        $Data = array();
        if (!$Empty)
        {
            $OldBuffers = $this->GetBufferList();
            $this->SendDebug('Bufferlist read', count($OldBuffers), 0);

            $Data = $this->GetItemsFromBufferList($OldBuffers);
        }
        $Name = "Tracklist." . $this->InstanceID . ".Kodi";
        if (!IPS_VariableProfileExists($Name))
        {
            IPS_CreateVariableProfile($Name, 1);
            IPS_SetVariableProfileValues($Name, 1, count($Data), 1);
        }
        else
        {
            if (IPS_GetVariableProfile($Name)['MaxValue'] <> count($Data))
                IPS_SetVariableProfileValues($Name, 1, count($Data), 1);
        }

        if ($Data === false)
            return;

        $HTMLData = $this->GetTableHeader($Config);
        $pos = 0;
        $CurrentTrack = GetValueInteger($this->GetIDForIdent('position'));
        if (count($Data) > 0)
        {
            foreach ($Data as $Position => $line)
            {
                $Line = array();
                foreach ($line as $key => $value)
                {
                    if (is_string($key))
                        $Line[ucfirst($key)] = $value;
                    else
                        $Line[$key] = $value; //$key is not a string
                }
                $Line['Position'] = $Position + 1;
                $Line['Type'] = ucfirst($Line['Type']);

                if (array_key_exists('Runtime', $Line))
                {
                    if ($Line['Runtime'] > 3600)
                        $Line['Runtime'] = @date("H:i:s", $Line['Runtime'] - 3600);
                    elseif ($Line['Runtime'] > 0)
                        $Line['Runtime'] = @date("i:s", $Line['Runtime']);
                    else
                        $Line['Runtime'] = '---';
                } else
                {
                    $Line['Runtime'] = '---';
                }

                $Line['Play'] = ($Line['Position'] == $CurrentTrack ? '<div class="iconMediumSpinner ipsIconArrowRight" style="width: 100%; background-position: center center;"></div>' : '');

//                $HTMLData .='<tr style="' . $Config['Style']['BR' . ($Line['Position'] == $CurrentTrack ? 'A' : ($pos % 2 ? 'U' : 'G'))] . '"onclick="window.xhrGet' . $this->InstanceID . '({ url: \'hook/KodiPlaylist' . $this->InstanceID . '?Index=' . $Line['Position'] . '\' })">';
                $HTMLData .='<tr style="' . $Config['Style']['BR' . ($Line['Position'] == $CurrentTrack ? 'A' : ($pos % 2 ? 'U' : 'G'))] . '"onclick="xhrGet' . $this->InstanceID . '({ url: \'hook/KodiPlaylist' . $this->InstanceID . '?Index=' . $Line['Position'] . '\' })">';
                foreach ($Config['Spalten'] as $feldIndex => $value)
                {
                    if (!array_key_exists($feldIndex, $Line))
                        $Line[$feldIndex] = '';
                    if ($Line[$feldIndex] === -1)
                        $Line[$feldIndex] = '';
                    if ($Line[$feldIndex] === 0)
                        $Line[$feldIndex] = '';
                    if (is_array($Line[$feldIndex]))
                        $Line[$feldIndex] = implode(', ', $Line[$feldIndex]);
                    $HTMLData .= '<td style="' . $Config['Style']['DF' . ($Line['Position'] == $CurrentTrack ? 'A' : ($pos % 2 ? 'U' : 'G')) . $feldIndex] . '">' . (string) $Line[$feldIndex] . '</td>';
                }
                $HTMLData .= '</tr>' . PHP_EOL;
                $pos++;
            }
        }
        $HTMLData .= $this->GetTableFooter();
        $this->SetValueString('Playlist', $HTMLData);
    }

    /**
     * Gibt den Inhalt des PHP-Scriptes zurück, welche die Konfiguration und das Design der Playlist-Tabelle enthält.
     * 
     * @access private
     * @return string Ein PHP-Script welche als Grundlage für die User dient.
     */
    private function CreatePlaylistConfigScript()
    {
        $Script = '<?
### Konfig ab Zeile 10 !!!

if ($_IPS["SENDER"] <> "Kodi")
{
	echo "Dieses Script kann nicht direkt ausgeführt werden!";
	return;
}
##########   KONFIGURATION
#### Tabellarische Ansicht
# Folgende Parameter bestimmen das Aussehen der HTML-Tabelle in der die Playlist dargestellt wird.

// Reihenfolge und Überschriften der Tabelle. Der vordere Wert darf nicht verändert werden.
// Die Reihenfolge, der hintere Wert (Anzeigetext) und die Reihenfolge sind beliebig änderbar.
$Config["Spalten"] = array(
    "Play" =>"",
    "Position"=>"Pos",
    "Type" => "Type",
    "Title"=>"Titel",
    "Showtitle"=>"Serie",    
    "Season"=>"S",
    "Episode"=>"E",
    "Album" => "Album",
    "Track" => "Track",
    "Disc" => "Disc",
    "Genre" => "Stil",
    "Artist"=>"Interpret",
    "Year" => "Jahr",
    "Runtime"=>"Dauer"

);
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
$Config["Breite"] = array(
    "Play" =>"50em",
    "Position" => "50em",
    "Type" => "100em",
    "Showtitle" => "300em",
    "Title" => "300em",
    "Season" => "50em",
    "Episode" => "50em",
    "Genre" => "200em",
    "Album" => "200em",
    "Disc" => "50em",
    "Track" => "50em",
    "Artist" => "200em",
    "Year" => "50em",
    "Runtime" => "100em"
);
// Style Informationen der Tabelle
$Config["Style"] = array(
    // <table>-Tag:
    "T"    => "margin:0 auto; font-size:0.8em;",
    // <thead>-Tag:
    "H"    => "",
    // <tr>-Tag im thead-Bereich:
    "HR"   => "",
    // <th>-Tag Feld Play:
    "HFPlay"  => "color:#ffffff; width:35px; align:left;",
    // <th>-Tag Feld Position:
    "HFPosition"  => "color:#ffffff; width:35px; align:left;",
    // <th>-Tag Feld Type:
    "HFType"  => "color:#ffffff; width:35px; align:left;",
    // <th>-Tag Feld Title:
    "HFTitle"  => "color:#ffffff; width:35px; align:left;",
    // <th>-Tag Feld Showtitle:
    "HFShowtitle"  => "color:#ffffff; width:35px; align:left;",
    // <th>-Tag Feld Season:
    "HFSeason"  => "color:#ffffff; width:35px; align:left;",
    // <th>-Tag Feld Episode:
    "HFEpisode"  => "color:#ffffff; width:35px; align:left;",
    // <th>-Tag Feld Album:
    "HFAlbum"  => "color:#ffffff; width:35px; align:left;",
    // <th>-Tag Feld Disc:
    "HFDisc"  => "color:#ffffff; width:35px; align:left;",
    // <th>-Tag Feld Track:
    "HFTrack"  => "color:#ffffff; width:35px; align:left;",
    // <th>-Tag Feld Genre:
    "HFGenre"  => "color:#ffffff; width:35px; align:left;",
    // <th>-Tag Feld Artist:
    "HFArtist"  => "color:#ffffff; width:35px; align:left;",
    // <th>-Tag Feld Year:
    "HFYear"  => "color:#ffffff; width:35px; align:left;",
    // <th>-Tag Feld Runtime:
    "HFRuntime"  => "color:#ffffff; width:35px; align:left;",
    // <tbody>-Tag:
    "B"    => "",
    // <tr>-Tag:
    "BRG"  => "background-color:#000000; color:#ffffff;",
    "BRU"  => "background-color:#080808; color:#ffffff;",
    "BRA"  => "background-color:#808000; color:#ffffff;",
    // <td>-Tag Feld Play:
    "DFGPlay" => "text-align:center;",
    "DFUPlay" => "text-align:center;",
    "DFAPlay" => "text-align:center;",
    // <td>-Tag Feld Position:
    "DFGPosition" => "text-align:center;",
    "DFUPosition" => "text-align:center;",
    "DFAPosition" => "text-align:center;",
    // <td>-Tag Feld Type:
    "DFGType" => "text-align:center;",
    "DFUType" => "text-align:center;",
    "DFAType" => "text-align:center;",
    // <td>-Tag Feld Showtitle:
    "DFGShowtitle" => "text-align:center;",
    "DFUShowtitle" => "text-align:center;",
    "DFAShowtitle" => "text-align:center;",
    // <td>-Tag Feld Title:
    "DFGTitle" => "text-align:center;",
    "DFUTitle" => "text-align:center;",
    "DFATitle" => "text-align:center;",
    // <td>-Tag Feld Season:
    "DFGSeason" => "text-align:center;",
    "DFUSeason" => "text-align:center;",
    "DFASeason" => "text-align:center;",
    // <td>-Tag Feld Episode:
    "DFGEpisode" => "text-align:center;",
    "DFUEpisode" => "text-align:center;",
    "DFAEpisode" => "text-align:center;",
    // <td>-Tag Feld Album:
    "DFGAlbum" => "text-align:center;",
    "DFUAlbum" => "text-align:center;",
    "DFAAlbum" => "text-align:center;",
    // <td>-Tag Feld Disc:
    "DFGDisc" => "text-align:center;",
    "DFUDisc" => "text-align:center;",
    "DFADisc" => "text-align:center;",
    // <td>-Tag Feld Track:
    "DFGTrack" => "text-align:center;",
    "DFUTrack" => "text-align:center;",
    "DFATrack" => "text-align:center;",
    // <td>-Tag Feld Genre:
    "DFGGenre" => "text-align:center;",
    "DFUGenre" => "text-align:center;",
    "DFAGenre" => "text-align:center;",
    // <td>-Tag Feld Artist:
    "DFGArtist" => "text-align:center;",
    "DFUArtist" => "text-align:center;",
    "DFAArtist" => "text-align:center;",
    // <td>-Tag Feld Year:
    "DFGYear" => "text-align:center;",
    "DFUYear" => "text-align:center;",
    "DFAYear" => "text-align:center;",
    // <td>-Tag Feld Runtime:
    "DFGRuntime" => "text-align:center;",
    "DFURuntime" => "text-align:center;",
    "DFARuntime" => "text-align:center;"
    // ^- Der Buchstabe "G" steht für gerade, "U" für ungerade., "A" für Aktiv
 );
### Konfig ENDE !!!
echo serialize($Config);
?>';
        return $Script;
    }

################## ActionHandler

    /**
     * Actionhandler der Statusvariablen. Interne SDK-Funktion.
     * 
     * @access public
     * @param string $Ident Der Ident der Statusvariable.
     * @param bool|float|int|string $Value Der angeforderte neue Wert.
     */
    public function RequestAction($Ident, $Value)
    {
        switch ($Ident)
        {
            case "position":
                $this->Init();
                $KodiData = new Kodi_RPC_Data(self::$Namespace[1]);
                $KodiData->GoTo(array('playerid' => $this->PlaylistId, "to" => (int) $Value - 1));
                $ret = $this->SendDirect($KodiData);
                if (is_null($ret))
                    return false;
                if ($ret === "OK")
                    return true;
                return trigger_error('Error on GoTo Track.', E_USER_NOTICE);
            default:
                return trigger_error('Invalid Ident.', E_USER_NOTICE);
        }
    }

################## PUBLIC

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_Get'.
     * Gibt alle Einträge der Playlist als Array zurück.
     * 
     * @access public
     * @return array Das Array mit den Eigenschaften des Item, im Fehlerfall ein leeren Array.
     */
    public function Get()
    {
        $this->Init();
        $KodiData = new Kodi_RPC_Data(self::$Namespace[0], 'GetItems', array('playlistid' => $this->PlaylistId, 'properties' => self::$ItemListSmall));
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret))
            return false;
        if ($ret->limits->total > 0)
            return $KodiData->ToArray($ret->items);
        return array();
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_GetItem'.
     * Gibt ein Eintrag der Playlist zurück.
     * 
     * @access public
     * @param int $Index Index des Items welche gelesen werden soll.
     * @return array Das Array mit den Eigenschaften des Item, im Fehlerfall ein leeren Array.
     */
    public function GetItem(int $Index)
    {
        $this->Init();
        $KodiData = new Kodi_RPC_Data(self::$Namespace[0], 'GetItems', array('playlistid' => $this->PlaylistId, 'properties' => self::$ItemListSmall, 'limits' => array('start' => $Index, 'end' => $Index + 1)));
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret))
            return false;
        if ($ret->limits->total > 0)
            return $KodiData->ToArray($ret->items);
        return array();
    }

    /**
     * Fügt der Playlist ein Item hinzu.
     * 
     * @access private
     * @param string $ItemTyp Der Typ des Item.
     * @param string $ItemValue Der Wert des Item.
     * @param array $Ext Array welches mit übergeben werden soll (optional).
     * @return bool True bei Erfolg. Sonst false.
     */
    private function Add(string $ItemTyp, string $ItemValue, $Ext = array())
    {

        $this->Init();
        $KodiData = new Kodi_RPC_Data(self::$Namespace[0]);
        $KodiData->Add(array_merge(array('playlistid' => $this->PlaylistId, "item" => array($ItemTyp => $ItemValue)), $Ext));
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret))
            return false;
        if ($ret === "OK")
            return true;
        trigger_error('Error on add ' . $ItemTyp . '.', E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_AddAlbum'.
     * Fügt der Playlist ein Album hinzu.
     * 
     * @access public
     * @param int $AlbumId ID des Album.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.  
     */
    public function AddAlbum(int $AlbumId)
    {
        if (!is_int($AlbumId))
        {
            trigger_error('AlbumId must be integer', E_USER_NOTICE);
            return false;
        }
        return $this->Add("albumid", $AlbumId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_AddArtist'.
     * Fügt der Playlist alle Itemes eines Artist hinzu.
     * 
     * @access public
     * @param int $ArtistId ID des Artist.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.  
     */
    public function AddArtist(int $ArtistId)
    {
        if (!is_int($ArtistId))
        {
            trigger_error('ArtistId must be int', E_USER_NOTICE);
            return false;
        }
        return $this->Add("artistid", $ArtistId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_AddDirectory'.
     * Fügt der Playlist alle Itemes eines Verzeichnisses hinzu.
     * 
     * @access public
     * @param string $Directory Pfad welcher hinzugefügt werden soll.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.  
     */
    public function AddDirectory(string $Directory)
    {
        if (!is_string($Directory))
        {
            trigger_error('Directory must be string', E_USER_NOTICE);
            return false;
        }
        return $this->Add("directory", $Directory);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_AddDirectoryRecursive'.
     * Fügt der Playlist alle Itemes eines Verzeichnisses, sowie dessen Unterverzeichnisse, hinzu.
     * 
     * @access public
     * @param string $Directory Pfad welcher hinzugefügt werden soll.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.  
     */
    public function AddDirectoryRecursive(string $Directory)
    {
        if (!is_string($Directory))
        {
            trigger_error('Directory must be string', E_USER_NOTICE);
            return false;
        }
        return $this->Add("Directory", $Directory, array("recursive" => true));
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_AddEpisode'.
     * Fügt der Playlist eine Episode hinzu.
     * 
     * @access public
     * @param int $EpisodeId ID der Episode.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.  
     */
    public function AddEpisode(int $EpisodeId)
    {
        if (!is_int($EpisodeId))
        {
            trigger_error('EpisodeId must be integer', E_USER_NOTICE);
            return false;
        }
        return $this->Add("episodeid", $EpisodeId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_AddFile'.
     * Fügt der Playlist eine Datei hinzu.
     * 
     * @access public
     * @param string $File Pfad zu einer Datei.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.  
     */
    public function AddFile(string $File)
    {
        if (!is_string($File))
        {
            trigger_error('File must be string', E_USER_NOTICE);
            return false;
        }
        return $this->Add("file", $File);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_AddGenre'.
     * Fügt der Playlist eine komplettes Genre hinzu.
     * 
     * @access public
     * @param int $GenreId ID des Genres.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE. 
     */
    public function AddGenre(int $GenreId)
    {
        if (!is_int($GenreId))
        {
            trigger_error('GenreId must be integer', E_USER_NOTICE);
            return false;
        }
        return $this->Add("genreid", $GenreId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_AddMovie'.
     * Fügt der Playlist ein Film hinzu.
     * 
     * @access public
     * @param int $MovieId ID des Filmes.
     * @return bool True bei Erfolg. Sonst false.
     */
    public function AddMovie(int $MovieId)
    {
        if (!is_int($MovieId))
        {
            trigger_error('MovieId must be int', E_USER_NOTICE);
            return false;
        }
        return $this->Add("movieid", $MovieId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_AddMusicVideo'.
     * Fügt der Playlist ein Musicvideo hinzu.
     * 
     * @access public
     * @param int $MusicvideoId ID des Musicvideos.
     * @return bool True bei Erfolg. Sonst false.
     */
    public function AddMusicVideo(int $MusicvideoId)
    {
        if (!is_int($MusicvideoId))
        {
            trigger_error('MusicvideoId must be int', E_USER_NOTICE);
            return false;
        }
        return $this->Add("musicvideoid", $MusicvideoId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_AddSong'.
     * Fügt der Playlist ein Song hinzu.
     * 
     * @access public
     * @param int $SongId ID des Songs.
     * @return bool True bei Erfolg. Sonst false.
     */
    public function AddSong(int $SongId)
    {
        if (!is_int($SongId))
        {
            trigger_error('SongId must be integer', E_USER_NOTICE);
            return false;
        }
        return $this->Add("songid", $SongId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_Clear'.
     * Leert die Playlist
     * 
     * @access public
     * @return bool True bei Erfolg. Sonst false.
     */
    public function Clear()
    {

        $this->Init();
        $KodiData = new Kodi_RPC_Data(self::$Namespace[0]);
        $KodiData->Clear(array('playlistid' => $this->PlaylistId));
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret))
            return false;
        if ($ret === "OK")
            return true;
        trigger_error('Error on clear playlist.', E_USER_NOTICE);
        return false;
    }

    /**
     * Fügt der Playlist ein Item, an einer bestimmten Position, hinzu.
     * 
     * @access private
     * @param int $Position Position des Item in der Playlist.
     * @param string $ItemTyp Der Typ des Item.
     * @param string $ItemValue Der Wert des Item.
     * @param array $Ext Array welches mit übergeben werden soll (optional).
     * @return bool True bei Erfolg. Sonst false.
     */
    private function Insert(int $Position, string $ItemTyp, string $ItemValue, $Ext = array())
    {
        if (!is_int($Position))
        {
            trigger_error('Position must be integer', E_USER_NOTICE);
            return false;
        }
        $this->Init();
        $KodiData = new Kodi_RPC_Data(self::$Namespace[0], 'Insert', array_merge(array('playlistid' => $this->PlaylistId, 'position' => $Position, 'item' => array($ItemTyp => $ItemValue)), $Ext));
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret))
            return false;
        if ($ret === "OK")
            return true;
        trigger_error('Error on insert ' . $ItemTyp . '.', E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_InsertAlbum'.
     * Fügt in der Playlist ein Album ein.
     * Alle anderen Einträge werden automatisch nach hinten verschoben.
     * 
     * @access public
     * @param int $AlbumId ID des Album.
     * @param int $Position Startposition des Album in der Playlist.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.  
     */
    public function InsertAlbum(int $AlbumId, int $Position)
    {
        if (!is_int($AlbumId))
        {
            trigger_error('AlbumId must be integer', E_USER_NOTICE);
            return false;
        }
        return $this->Insert($Position, "albumid", $AlbumId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_InsertArtist'.
     * Fügt in der Playlist alle Items eines Artist ein.
     * Alle anderen Einträge werden automatisch nach hinten verschoben.
     * 
     * @access public
     * @param int $ArtistId ID des Artist.
     * @param int $Position Startposition des Album in der Playlist.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.  
     */
    public function InsertArtist(int $ArtistId, int $Position)
    {
        if (!is_int($ArtistId))
        {
            trigger_error('ArtistId must be integer', E_USER_NOTICE);
            return false;
        }
        return $this->Insert($Position, "artistid", $ArtistId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_InsertDirectory'.
     * Fügt in der Playlist alle Itemes eines Verzeichnisses ein.
     * Alle anderen Einträge werden automatisch nach hinten verschoben.
     * 
     * @access public
     * @param string $Directory Pfad welcher hinzugefügt werden soll.
     * @param int $Position Startposition des Album in der Playlist.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.  
     */
    public function InsertDirectory(string $Directory, int $Position)
    {
        if (!is_string($Directory))
        {
            trigger_error('Directory must be string', E_USER_NOTICE);
            return false;
        }
        return $this->Insert($Position, "directory", $Directory);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_InsertDirectoryRecursive'.
     * Fügt in der Playlist alle Itemes eines Verzeichnisses, sowie dessen Unterverzeichnisse, ein.
     * Alle anderen Einträge werden automatisch nach hinten verschoben.
     * 
     * @access public
     * @param string $Directory Pfad welcher hinzugefügt werden soll.
     * @param int $Position Startposition des Album in der Playlist.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.  
     */
    public function InsertDirectoryRecursive(string $Directory, int $Position)
    {
        if (!is_string($Directory))
        {
            trigger_error('Directory must be string', E_USER_NOTICE);
            return false;
        }
        return $this->Insert($Position, "Directory", $Directory, array("recursive" => true));
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_InsertEpisode'.
     * Fügt in der Playlist eine Episode ein.
     * Alle anderen Einträge werden automatisch nach hinten verschoben.
     * 
     * @access public
     * @param int $EpisodeId ID der Episode.
     * @param int $Position Startposition des Album in der Playlist.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.  
     */
    public function InsertEpisode(int $EpisodeId, int $Position)
    {
        if (!is_int($EpisodeId))
        {
            trigger_error('EpisodeId must be int', E_USER_NOTICE);
            return false;
        }
        return $this->Insert($Position, "episodeid", $EpisodeId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_InsertFile'.
     * Fügt in der Playlist eine Datei ein.
     * Alle anderen Einträge werden automatisch nach hinten verschoben.
     * 
     * @access public
     * @param string $File Pfad zu einer Datei.
     * @param int $Position Startposition des Album in der Playlist.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.  
     */
    public function InsertFile(string $File, int $Position)
    {
        if (!is_string($File))
        {
            trigger_error('File must be string', E_USER_NOTICE);
            return false;
        }
        return $this->Insert($Position, "file", $File);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_InsertGenre'.
     * Fügt in der Playlist eine komplettes Genre ein.
     * Alle anderen Einträge werden automatisch nach hinten verschoben.
     * 
     * @access public
     * @param int $GenreId ID des Genres welches hinzugefügt werden soll.
     * @param int $Position Startposition des Album in der Playlist.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.  
     */
    public function InsertGenre(int $GenreId, int $Position)
    {
        if (!is_int($GenreId))
        {
            trigger_error('GenreId must be integer', E_USER_NOTICE);
            return false;
        }
        return $this->Insert($Position, "genreid", $GenreId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_InsertMovie'.
     * Fügt in der Playlist ein Filme ein.
     * Alle anderen Einträge werden automatisch nach hinten verschoben.
     * 
     * @access public
     * @param int $MovieId ID des Filmes.
     * @param int $Position Startposition des Album in der Playlist.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.  
     */
    public function InsertMovie(int $MovieId, int $Position)
    {
        if (!is_int($MovieId))
        {
            trigger_error('MovieId must be integer', E_USER_NOTICE);
            return false;
        }
        return $this->Insert($Position, "movieid", $MovieId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_InsertMusicVideo'.
     * Fügt in der Playlist ein Musicvideo ein.
     * Alle anderen Einträge werden automatisch nach hinten verschoben.
     * 
     * @access public
     * @param int $MusicvideoId ID des Musicvideos.
     * @param int $Position Startposition des Album in der Playlist.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.  
     */
    public function InsertMusicVideo(int $MusicvideoId, int $Position)
    {
        if (!is_int($MusicvideoId))
        {
            trigger_error('MusicvideoId must be integer', E_USER_NOTICE);
            return false;
        }
        return $this->Insert($Position, "musicvideoid", $MusicvideoId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_InsertSong'.
     * Fügt in der Playlist ein Lied ein.
     * Alle anderen Einträge werden automatisch nach hinten verschoben.
     * 
     * @access public
     * @param int $SongId ID des Songs.
     * @param int $Position Startposition des Album in der Playlist.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.  
     */
    public function InsertSong(int $SongId, int $Position)
    {
        if (!is_int($SongId))
        {
            trigger_error('SongId must be integer', E_USER_NOTICE);
            return false;
        }
        return $this->Insert($Position, "songid", $SongId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_Remove'.
     * Entfernt einen Eintrag aus der Playlist.
     * Alle anderen Einträge werden automatisch nach vorne verschoben.
     * 
     * @access public
     * @param int $Position Eintrag welcher entfernt wird.
     * @return bool True bei Erfolg. Sonst false.
     */
    public function Remove(int $Position)
    {
        if (!is_int($Position))
        {
            trigger_error('Position must be integer', E_USER_NOTICE);
            return false;
        }
        $this->Init();
        $KodiData = new Kodi_RPC_Data(self::$Namespace[0], 'Remove', array('playlistid' => $this->PlaylistId, 'position' => $Position));
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret))
            return false;
        if ($ret === "OK")
            return true;
        trigger_error('Error on remove item.', E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_Swap'.
     * Tauscht zwei Einträge innerhalb der Playlist.
     * 
     * @access public
     * @param int $Position1 | $Position2 Positionen der Einträge welche untereinander getsucht werden.
     * @return bool True bei Erfolg. Sonst false.
     */
    public function Swap(int $Position1, int $Position2)
    {
        if (!is_int($Position1))
        {
            trigger_error('Position1 must be integer', E_USER_NOTICE);
            return false;
        }
        if (!is_int($Position2))
        {
            trigger_error('Position2 must be integer', E_USER_NOTICE);
            return false;
        }
        $this->Init();
        $KodiData = new Kodi_RPC_Data(self::$Namespace[0], 'Swap', array('playlistid' => $this->PlaylistId, 'position1' => $Position1, 'position2' => $Position2));
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret))
            return false;
        if ($ret === "OK")
            return true;
        trigger_error('Error on swap items.', E_USER_NOTICE);
        return false;
    }

}

/** @} */
?>