<?

require_once(__DIR__ . "/../KodiClass.php");  // diverse Klassen
/*
 * @addtogroup kodi
 * @{
 *
 * @package       Kodi
 * @file          module.php
 * @author        Michael Tröger
 *
 */

/**
 * KodiDeviceApplication Klasse für den Namespace Player der KODI-API.
 * Erweitert KodiBase.
 *
 */
class KodiDevicePlayer extends KodiBase
{

    /**
     * PlayerID für Audio
     * 
     * @access private
     * @static int
     * @value 0
     */
    const Audio = 0;

    /**
     * PlayerID für Video
     * 
     * @access private
     * @static int
     * @value 1
     */
    const Video = 1;

    /**
     * PlayerID für Bilder
     * 
     * @access private
     * @static int
     * @value 2
     */
    const Picture = 2;

    /**
     * RPC-Namespace
     * 
     * @access private
     *  @var string
     * @value 'Application'
     */
    static $Namespace = 'Player';

    /**
     * Alle Properties des RPC-Namespace
     * 
     * @access private
     *  @var array 
     */
    static $Properties = array(
        "type",
        "partymode",
        "speed",
        "time",
        "percentage",
        "totaltime",
        "playlistid",
        "position",
        "repeat",
        "shuffled",
        "canseek",
        "canchangespeed",
        "canmove",
        "canzoom",
        "canrotate",
        "canshuffle",
        "canrepeat",
        "currentaudiostream",
        "audiostreams",
        "subtitleenabled",
        "currentsubtitle",
        "subtitles",
        "live"
    );

    /**
     * Ein Teil der Properties des RPC-Namespace für Statusmeldungen
     * 
     * @access private
     *  @var array 
     */
    static $PartialProperties = array(
        "type",
        "partymode",
        "speed",
        "time",
        "percentage",
        "repeat",
        "shuffled",
        "currentaudiostream",
        "subtitleenabled",
        "currentsubtitle"
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
     * Eigene PlayerId
     * 
     * @access private
     *  @var int Kodi-Player-ID dieser Instanz 
     */
    private $PlayerId = null;

    /**
     * Wenn dieser Player in Kodi gerade Active ist, true sonst false.
     * 
     * @access private
     *  @var bool true = aktiv, false = inaktiv, null wenn nicht bekannt.
     */
    private $isActive = null;

    /**
     * Zuordnung der von Kodi gemeldeten Medientypen zu den PlayerIDs
     * 
     * @access private
     *  @var array Key ist der Medientyp, Value die PlayerID
     */
    static $Playertype = array(
        "song" => 0,
        "audio" => 0,
        "video" => 1,
        "episode" => 1,
        "movie" => 1,
        "picture" => 2,
        "pictures" => 2
    );

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Create()
    {
        parent::Create();
        $this->RegisterPropertyInteger('PlayerID', static::Audio);
        $this->RegisterPropertyInteger('CoverSize', 300);
        $this->RegisterPropertyString('CoverTyp', 'thumb');
        $this->SetCover('');
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Destroy()
    {
        parent::Destroy();
        if (IPS_GetKernelRunlevel() <> KR_READY)
            return;
        $CoverID = @IPS_GetObjectIDByIdent('CoverIMG', $this->InstanceID);
        if ($CoverID > 0)
            @IPS_DeleteMedia($CoverID, true);
    }

    /**
     * Interne Funktion des SDK.
     * 
     * @access public
     */
    public function ApplyChanges()
    {
//        $this->RegisterVariableBoolean("_isactive", "isplayeractive", "", -5);
//        IPS_SetHidden($this->GetIDForIdent('_isactive'), true);

        $this->Init();

        $this->RegisterProfileIntegerEx("Repeat.Kodi", "", "", "", Array(
            //Array(0, "Prev", "", -1),
            Array(0, "Aus", "", -1),
            Array(1, "Titel", "", -1),
            Array(2, "Playlist", "", -1)
        ));
        $this->RegisterProfileIntegerEx("Speed.Kodi", "Intensity", "", "", Array(
            //Array(0, "Prev", "", -1),
            Array(-32, "32 <<", "", -1),
            Array(-16, "16 <<", "", -1),
            Array(-8, "8 <<", "", -1),
            Array(-4, "4 <<", "", -1),
            Array(-2, "2 <<", "", -1),
            Array(-1, "1 <<", "", -1),
            Array(0, "Pause", "", 0x0000FF),
            Array(1, "Play", "", 0x00FF00),
            Array(2, "2 >>", "", -1),
            Array(4, "4 >>", "", -1),
            Array(8, "8 >>", "", -1),
            Array(16, "16 >>", "", -1),
            Array(32, "32 >>", "", -1)
        ));

        $this->RegisterProfileInteger("Intensity.Kodi", "Intensity", "", " %", 0, 100, 1);
        $this->RegisterProfileIntegerEx("Status." . $this->InstanceID . ".Kodi", "Information", "", "", Array(
            Array(0, "Prev", "", -1),
            Array(1, "Stop", "", -1),
            Array(2, "Play", "", -1),
            Array(3, "Pause", "", -1),
            Array(4, "Next", "", -1)
        ));
        switch ($this->PlayerId)
        {
            case self::Audio:
                $this->UnregisterVariable("showtitle");
                $this->UnregisterVariable("season");
                $this->UnregisterVariable("episode");
                $this->UnregisterVariable("plot");
                $this->UnregisterVariable("audiostream");
                $this->UnregisterVariable("subtitle");
                $this->UnregisterProfile("AudioStream." . $this->InstanceID . ".Kodi");
                $this->UnregisterProfile("Subtitels." . $this->InstanceID . ".Kodi");


                $this->RegisterVariableString("album", "Album", "", 15);
                $this->RegisterVariableInteger("track", "Track", "", 16);
                $this->RegisterVariableInteger("disc", "Disc", "", 17);
                $this->RegisterVariableString("artist", "Artist", "", 20);
                $this->RegisterVariableString("lyrics", "Lyrics", "", 30);

                break;
            case self::Video:
//                $this->UnregisterVariable("repeat");
//                $this->UnregisterVariable("shuffled");
//                $this->UnregisterVariable("partymode");
                $this->UnregisterVariable("album");
                $this->UnregisterVariable("track");
                $this->UnregisterVariable("disc");
                $this->UnregisterVariable("artist");
                $this->UnregisterVariable("lyrics");

//                $this->RegisterProfileIntegerEx("Status." . $this->InstanceID . ".Kodi", "Information", "", "", Array(
//                    Array(1, "Stop", "", -1),
//                    Array(2, "Play", "", -1),
//                    Array(3, "Pause", "", -1)
//                ));
                $this->RegisterProfileIntegerEx("AudioStream." . $this->InstanceID . ".Kodi", "", "", "", Array(
                    Array(0, "1", "", -1)
                ));

                $this->RegisterVariableString("showtitle", "Serie", "", 13);
                $this->RegisterVariableInteger("season", "Staffel", "", 15);
                $this->RegisterVariableInteger("episode", "Episode", "", 16);

                $this->RegisterVariableString("plot", "Handlung", "~TextBox", 19);
                $this->RegisterVariableInteger("audiostream", "Audiospur", "AudioStream." . $this->InstanceID . ".Kodi", 30);

                $this->RegisterProfileIntegerEx("Subtitels." . $this->InstanceID . ".Kodi", "", "", "", Array(
                    Array(-1, "Aus", "", -1),
                    Array(0, "Extern", "", -1)
                ));
                $this->RegisterVariableInteger("subtitle", "Aktiver Untertitel", "Subtitels." . $this->InstanceID . ".Kodi", 41);
                break;
            case self::Picture:

                $this->UnregisterVariable("showtitle");
                $this->UnregisterVariable("season");
                $this->UnregisterVariable("episode");
                $this->UnregisterVariable("plot");
                $this->UnregisterVariable("audiostream");
                $this->UnregisterVariable("subtitle");
                $this->UnregisterProfile("AudioStream." . $this->InstanceID . ".Kodi");
                $this->UnregisterProfile("Subtitels." . $this->InstanceID . ".Kodi");

                $this->UnregisterVariable("album");
                $this->UnregisterVariable("track");
                $this->UnregisterVariable("disc");
                $this->UnregisterVariable("artist");
                $this->UnregisterVariable("lyrics");

//                $this->UnregisterVariable("position");                
//                $this->RegisterProfileIntegerEx("Status." . $this->InstanceID . ".Kodi", "Information", "", "", Array(
//                    Array(0, "Prev", "", -1),
//                    Array(1, "Stop", "", -1),
//                    Array(2, "Play", "", -1),
//                    Array(3, "Pause", "", -1),
//                    Array(4, "Next", "", -1)
//                ));
                break;
        }
        $this->RegisterVariableInteger("repeat", "Wiederholen", "Repeat.Kodi", 11);
        $this->EnableAction("repeat");
        $this->RegisterVariableBoolean("shuffled", "Zufall", "~Switch", 12);
        $this->EnableAction("shuffled");
        $this->RegisterVariableBoolean("partymode", "Partymodus", "~Switch", 13);
        $this->EnableAction("partymode");
        $this->RegisterVariableString("label", "Titel", "", 14);
        $this->RegisterVariableString("genre", "Genre", "", 21);
        $this->RegisterVariableInteger("Status", "Status", "Status." . $this->InstanceID . ".Kodi", 3);
        $this->EnableAction("Status");
        $this->RegisterVariableInteger("speed", "Geschwindigkeit", "Speed.Kodi", 10);
        $this->RegisterVariableInteger("year", "Jahr", "", 19);
//        $this->RegisterVariableString("type", "Typ", "", 20);
        $this->RegisterVariableString("duration", "Dauer", "", 24);
        $this->RegisterVariableString("time", "Spielzeit", "", 25);
        $this->RegisterVariableInteger("percentage", "Position", "Intensity.Kodi", 26);
        $this->EnableAction("percentage");

        parent::ApplyChanges();


        $this->getActivePlayer();


        if ($this->isActive)
            $this->GetItemInternal();

        $this->RegisterTimer('PlayerStatus', 0, 'KODIPLAYER_RequestState($_IPS[\'TARGET\'],"PARTIAL");');
    }

################## PRIVATE     

    /**
     * Setzt die Eigenschaften isActive und PlayerId der Instanz
     * damit andere Funktionen Diese nutzen können
     * 
     * @access private
     */
    private function Init()
    {
        if (is_null($this->PlayerId))
            $this->PlayerId = $this->ReadPropertyInteger('PlayerID');
        if (is_null($this->isActive))
        //$this->isActive = GetValueBoolean($this->GetIDForIdent('_isactive'));
            $this->isActive = $this->GetBuffer('_isactive');
    }

    /**
     * Fragt Kodi an ob der Playertyp der Instanz gerade aktiv ist.
     * 
     * @return bool true wenn Player aktiv ist, sonset false
     */
    private function getActivePlayer()
    {
        $this->Init();
        $KodiData = new Kodi_RPC_Data(static::$Namespace);
        $KodiData->GetActivePlayers();
        $ret = @$this->SendDirect($KodiData);

        if (is_null($ret) or ( count($ret) == 0))
            $this->isActive = false;
        else
            $this->isActive = ((int) $ret[0]->playerid == $this->PlayerId);

        //$this->SetValueBoolean('_isactive', $this->isActive);
        $this->SetBuffer('_isactive', $this->isActive);
        return (bool) $this->isActive;
    }

    /**
     * Setzt die Eigenschaft isActive sowie die dazugehörige IPS-Variable.
     * 
     * @access private
     * @param bool $isActive True wenn Player als aktive gesetzt werden soll, sonder false.
     */
    private function setActivePlayer(bool $isActive)
    {
        $this->isActive = $isActive;
        //$this->SetValueBoolean('_isactive', $isActive);
        $this->SetBuffer('_isactive', $this->isActive);
    }

    /**
     * Werte der Eigenschaften anfragen.
     * 
     * @access protected
     * @param array $Params Enthält den Index "properties", in welchen alle anzufragenden Eigenschaften als Array enthalten sind.
     * @return bool true bei erfolgreicher Ausführung und dekodierung, sonst false.
     */
    protected function RequestProperties(array $Params)
    {
        $this->Init();
        $Param = array_merge($Params, array("playerid" => $this->PlayerId));
        //parent::RequestProperties($Params);
        if (!$this->isActive)
            return false;
        $KodiData = new Kodi_RPC_Data(static::$Namespace);
        $KodiData->GetProperties($Param);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret))
            return false;
        $this->Decode('GetProperties', $ret);
        return true;
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
        if (property_exists($KodiPayload, 'player'))
        {
            if ($KodiPayload->player->playerid <> $this->PlayerId)
                return false;
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
                    if (self::$Playertype[(string) $KodiPayload->item->type] <> $this->PlayerId)
                        return false;
                }
            }
        }
        $this->SendDebug($Method, $KodiPayload, 0);

        switch ($Method)
        {
            case 'GetProperties':
            case 'OnPropertyChanged':
                foreach ($KodiPayload as $param => $value)
                {
                    switch ($param)
                    {
                        case "subtitles":
                            if ($this->PlayerId <> self::Video)
                                break;
                            $this->CreateSubtitleProfil($value);
                            if (count($value) == 0)
                                $this->DisableAction('subtitle');
                            else
                                $this->EnableAction('subtitle');
                            break;
//                            $this->SetValueInteger($param, );
                        case "subtitleenabled":
                            if ($this->PlayerId <> self::Video)
                                break;
                            if ($value === false)
                                $this->SetValueInteger('subtitle', -1);
                            break;
                        // Object
                        case "currentsubtitle":
                            if ($this->PlayerId <> self::Video)
                                break;
                            if (is_object($value))
                            {
                                if (property_exists($value, 'index'))
                                {
                                    $this->SetValueInteger('subtitle', (int) $value->index);
                                }
                                else
                                {
                                    $this->SetValueInteger('subtitle', -1);
                                }
                            }
                            else
                            {
                                $this->SetValueInteger('subtitle', -1);
                            }
                            break;
                        case "audiostreams":
                            if ($this->PlayerId <> self::Video)
                                break;
                            $this->CreateAudioProfil($value);
                            if (count($value) == 1)
                                $this->DisableAction('audiostream');
                            else
                                $this->EnableAction('audiostream');
                            break;
                        case "currentaudiostream":
                            if ($this->PlayerId <> self::Video)
                                break;
                            if (is_object($value))
                            {
                                if (property_exists($value, 'index'))
                                {
                                    $this->SetValueInteger('audiostream', (int) $value->index);
                                }
                                else
                                {
                                    $this->SetValueInteger('audiostream', 0);
                                }
                            }
                            else
                            {
                                $this->SetValueInteger('audiostream', 0);
                            }
                            break;

//                            if (is_object($value))
//                            {
//                                if (property_exists($value, 'bitrate'))
//                                    $this->SetValueInteger('audiobitrate', (int) $value->bitrate);
//                                else
//                                    $this->SetValueInteger('audiobitrate', 0);
//
//                                if (property_exists($value, 'channels'))
//                                    $this->SetValueInteger('audiochannels', (int) $value->channels);
//                                else
//                                    $this->SetValueInteger('audiochannels', 0);
//
//                                if (property_exists($value, 'index'))
//                                    $this->SetValueInteger('audioindex', (int) $value->index);
//                                else
//                                    $this->SetValueInteger('audioindex', 0);
//
//                                if (property_exists($value, 'language'))
//                                    $this->SetValueString('audiolanguage', (string) $value->language);
//                                else
//                                    $this->SetValueString('audiolanguage', "");
//
//                                if (property_exists($value, 'name'))
//                                    $this->SetValueString('audiocodec', (string) $value->name);
//                                else
//                                    $this->SetValueString('audiocodec', "");
//                            } else
//                            {
//                                $this->SetValueInteger('audiobitrate', 0);
//                                $this->SetValueInteger('audiochannels', 0);
//                                $this->SetValueInteger('audioindex', 0);
//                                $this->SetValueString('audiolanguage', "");
//                                $this->SetValueString('audiocodec', "");
//                            }
//                            break;
                        //time
                        case "totaltime":
                            $this->SetValueString('duration', $this->ConvertTime($value));
                            break;
                        case "time":
                            $this->SetValueString($param, $this->ConvertTime($value));
                            break;
                        // Anzahl

                        case "repeat": //off
                            $this->SetValueInteger($param, array_search((string) $value, array("off", "one", "all")));
                            break;
                        //boolean
                        case "shuffled":
                        case "partymode":
                            $this->SetValueBoolean($param, (bool) $value);
                            break;
                        //integer
                        case "speed":
                            if ((int) $value == 0)
                                $this->SetValueInteger('Status', 3);
                            else
                                $this->SetValueInteger('Status', 2);
                        case "percentage":
                            $this->SetValueInteger($param, (int) $value);
                            break;

                        //Action en/disable
                        case "canseek":
                            if ((bool) $value)
                                $this->EnableAction('percentage');
                            else
                                $this->DisableAction('percentage');
                            break;
                        case "canshuffle":
                            if ((bool) $value)
                                $this->EnableAction('shuffled');
                            else
                                $this->DisableAction('shuffled');
                            break;
                        case "canrepeat":
                            if ((bool) $value)
                                $this->EnableAction('repeat');
                            else
                                $this->DisableAction('repeat');
                            break;
                        case "canchangespeed":
                            if ((bool) $value)
                                $this->EnableAction('speed');
                            else
                                $this->DisableAction('speed');
                            break;
                        //Todo Bilder
                        case "canrotate":
                        case "canzoom":
                        case "canmove":
                            break;
                        //ignore
                        case "playlistid":
                        case "live":
                        case "playlist":
                        case "position":
                        case "type":
                            break;
                        default:
                            $this->SendDebug('Todo:' . $param, $value, 0);

                            break;
                    }
                }
                break;
            case 'OnStop':
                $this->SetTimerInterval('PlayerStatus', 0);
                $this->SetValueInteger('Status', 1);
                $this->SetValueString('duration', '');
                $this->SetValueString('totaltime', '');
                $this->SetValueString('time', '');
                $this->SetValueInteger('percentage', 0);
                $this->SetValueInteger('speed', 0);
                $this->setActivePlayer(false);
                IPS_RunScriptText('<? KODIPLAYER_RequestState(' . $this->InstanceID . ',"ALL");');
                IPS_RunScriptText('<? KODIPLAYER_GetItemInternal(' . $this->InstanceID . ');');

                break;
            case 'OnPlay':
                $this->setActivePlayer(true);
                $this->SetValueInteger('Status', 2);
                IPS_RunScriptText('<? KODIPLAYER_RequestState(' . $this->InstanceID . ',"ALL");');
                IPS_RunScriptText('<? KODIPLAYER_GetItemInternal(' . $this->InstanceID . ');');
                $this->SetTimerInterval('PlayerStatus', 2000);
                break;
            case 'OnPause':
                $this->SetTimerInterval('PlayerStatus', 0);
                $this->SetValueInteger('Status', 3);
                IPS_RunScriptText('<? KODIPLAYER_RequestState(' . $this->InstanceID . ',"ALL");');
                break;
            case 'OnSeek':
                $this->SetValueString('time', $this->ConvertTime($KodiPayload->player->time));
                break;
            case 'OnSpeedChanged':
                IPS_RunScriptText('<? KODIPLAYER_RequestState(' . $this->InstanceID . ',"speed");');
                break;
            default:
//                IPS_LogMessage($Method, print_r($KodiPayload, true));
                $this->SendDebug($Method, $KodiPayload, 0);
                break;
        }
    }

    /**
     * Holt das über $flie übergebene Cover vom Kodi-Webinterface, skaliert und konvertiert dieses und speichert es in einem MedienObjekt ab.
     * 
     * @access private
     * @param string $file
     */
    private function SetCover(string $file)
    {
        $CoverID = @IPS_GetObjectIDByIdent('CoverIMG', $this->InstanceID);
        $Size = $this->ReadPropertyString("CoverSize");
        if ($CoverID === false)
        {
            $CoverID = IPS_CreateMedia(1);
            IPS_SetParent($CoverID, $this->InstanceID);
            IPS_SetIdent($CoverID, 'CoverIMG');
            IPS_SetName($CoverID, 'Cover');
            IPS_SetPosition($CoverID, 27);
            IPS_SetMediaCached($CoverID, true);
            $filename = "media" . DIRECTORY_SEPARATOR . "Cover_" . $this->InstanceID . ".png";
            IPS_SetMediaFile($CoverID, $filename, False);
        }

        if ($file == "")
            $CoverRAW = FALSE;
        else
        {
            $ParentID = $this->GetParent();
            if ($ParentID !== false)
                $CoverRAW = KODIRPC_GetImage($ParentID, $file);
        }

        if (!($CoverRAW === false))
        {
            $image = @imagecreatefromstring($CoverRAW);
            if (!($image === false))
            {
                $width = imagesx($image);
                $height = imagesy($image);
                if ($height > $Size)
                {
                    $factor = $height / $Size;
                    $image = imagescale($image, $width / $factor, $height / $factor);
                }
                ob_start();
                @imagepng($image);
                $CoverRAW = ob_get_contents(); // read from buffer                
                ob_end_clean(); // delete buffer                
            }
        }

        if ($CoverRAW === false)
            $CoverRAW = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "nocover.png");

        IPS_SetMediaContent($CoverID, base64_encode($CoverRAW));
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
            case "Status":
                switch ($Value)
                {
                    case 0: //Prev
                        return $this->GoToPrevious();
                    case 1: //Stop
                        return $this->Stop();
                    case 2: //Play
                        return $this->Play();
                    case 3: //Pause
                        return $this->Pause();
                    case 4: //Next
                        return $this->GoToNext();
                    default:
                        return trigger_error('Invalid Ident.', E_USER_NOTICE);
                }
            case "shuffled":
                return $this->SetShuffle($Value);
            case "repeat":
                return $this->SetRepeat($Value);
            case "speed":
                return $this->SetSpeed($Value);
            case "partymode":
                return $this->SetPartymode($Value);
            case "percentage":
                return $this->SetPosition($Value);
            case "subtitle":
                return $this->SetSubtitle($Value);
            case "audiostream":
                return $this->SetAudioStream($Value);
            default:
                return trigger_error('Invalid Ident.', E_USER_NOTICE);
        }
    }

################## PUBLIC

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_GetItemInternal'.
     * Holt sich die Daten des aktuellen wiedergegebenen Items, und bildet die Eigenschaften in IPS-Variablen ab.
     * 
     * @access public
     */
    public function GetItemInternal()
    {
//        return;
        $this->Init();
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetItem(array('playerid' => $this->PlayerId, 'properties' => self::$ItemList));
        $raw = $this->SendDirect($KodiData);
        if (is_null($raw))
            return;
        $ret = $raw->item;

        switch ($this->PlayerId)
        {
            case self::Audio:
                $this->SetValueString('label', $ret->label);
//                $this->SetValueString('type', $ret->type);

                if (property_exists($ret, 'displayartist'))
                    $this->SetValueString('artist', $ret->displayartist);
                else
                {
                    if (property_exists($ret, 'albumartist'))
                    {
                        if (is_array($ret->artist))
                            $this->SetValueString('artist', implode(', ', $ret->albumartist));
                        else
                            $this->SetValueString('artist', $ret->albumartist);
                    }
                    else
                    {
                        if (property_exists($ret, 'artist'))
                        {
                            if (is_array($ret->artist))
                                $this->SetValueString('artist', implode(', ', $ret->artist));
                            else
                                $this->SetValueString('artist', $ret->artist);
                        }
                        else
                            $this->SetValueString('artist', "");
                    }
                }

                if (property_exists($ret, 'genre'))
                {
                    if (is_array($ret->genre))
                        $this->SetValueString('genre', implode(', ', $ret->genre));
                    else
                        $this->SetValueString('genre', $ret->genre);
                }
                else
                    $this->SetValueString('genre', "");

                if (property_exists($ret, 'album'))
                    $this->SetValueString('album', $ret->album);
                else
                    $this->SetValueString('album', "");

                if (property_exists($ret, 'year'))
                    $this->SetValueInteger('year', $ret->year);
                else
                    $this->SetValueInteger('year', 0);

                if (property_exists($ret, 'track'))
                    $this->SetValueInteger('track', $ret->track);
                else
                    $this->SetValueInteger('track', 0);

                if (property_exists($ret, 'disc'))
                    $this->SetValueInteger('disc', $ret->disc);
                else
                    $this->SetValueInteger('disc', 0);

                if (property_exists($ret, 'duration'))
                    $this->SetValueString('duration', $this->ConvertTime($ret->duration));
                else
                    $this->SetValueString('duration', "");

                if (property_exists($ret, 'lyrics'))
                    $this->SetValueString('lyrics', $ret->lyrics);
                else
                    $this->SetValueString('lyrics', "");

                switch ($this->ReadPropertyString('CoverTyp'))
                {
                    case"artist":
                        if (property_exists($ret, 'art'))
                        {
                            if (property_exists($ret->art, 'artist.fanart'))
                                if ($ret->art->{'artist.fanart'} <> "")
                                {
                                    $this->SetCover($ret->art->{'artist.fanart'});
                                    break;
                                }
                        }
                        if (property_exists($ret, 'fanart'))
                            if ($ret->fanart <> "")
                            {
                                $this->SetCover($ret->fanart);
                                break;
                            }
                    default:
                        if (property_exists($ret, 'art'))
                        {
                            if (property_exists($ret->art, 'thumb'))
                                if ($ret->art->thumb <> "")
                                {
                                    $this->SetCover($ret->art->thumb);
                                    break;
                                }
                        }
                        if (property_exists($ret, 'thumbnail'))
                        {
                            if ($ret->thumbnail <> "")
                            {
                                $this->SetCover($ret->thumbnail);
                                break;
                            }
                        }
                        $this->SetCover("");
                        break;
                }

                break;
            case self::Video:

                if (property_exists($ret, 'showtitle'))
                    $this->SetValueString('showtitle', $ret->showtitle);
                else
                    $this->SetValueString('showtitle', "");

                $this->SetValueString('label', $ret->label);

                if (property_exists($ret, 'season'))
                    $this->SetValueInteger('season', $ret->season);
                else
                    $this->SetValueInteger('season', -1);

                if (property_exists($ret, 'episode'))
                    $this->SetValueInteger('episode', $ret->episode);
                else
                    $this->SetValueInteger('episode', -1);

                if (property_exists($ret, 'genre'))
                {
                    if (is_array($ret->genre))
                        $this->SetValueString('genre', implode(', ', $ret->genre));
                    else
                        $this->SetValueString('genre', $ret->genre);
                }
                else
                    $this->SetValueString('genre', "");

                if (property_exists($ret, 'runtime'))
                    $this->SetValueString('duration', $this->ConvertTime($ret->runtime));
                else
                    $this->SetValueString('duration', "");

                if (property_exists($ret, 'year'))
                    $this->SetValueInteger('year', $ret->year);
                else
                    $this->SetValueInteger('year', 0);

                if (property_exists($ret, 'plot'))
                    $this->SetValueString('plot', $ret->plot);
                else
                    $this->SetValueString('plot', "");

                switch ($this->ReadPropertyString('CoverTyp'))
                {
                    case"poster":
                        if (property_exists($ret, 'art'))
                        {
                            if (property_exists($ret->art, 'tvshow.poster'))
                            {
                                if ($ret->art->{'tvshow.poster'} <> "")
                                {
                                    $this->SetCover($ret->art->{'tvshow.poster'});
                                    break;
                                }
                            }
                            if (property_exists($ret->art, 'poster'))
                            {
                                if ($ret->art->{'poster'} <> "")
                                {
                                    $this->SetCover($ret->art->{'poster'});
                                    break;
                                }
                            }
                        }
                        if (property_exists($ret, 'poster'))
                        {
                            if ($ret->poster <> "")
                            {
                                $this->SetCover($ret->poster);
                                break;
                            }
                        }
                        if (property_exists($ret, 'art'))
                        {
                            if (property_exists($ret->art, 'tvshow.banner'))
                            {
                                if ($ret->art->{'tvshow.banner'} <> "")
                                {
                                    $this->SetCover($ret->art->{'tvshow.banner'});
                                    break;
                                }
                            }
                        }
                        if (property_exists($ret, 'banner'))
                        {
                            if ($ret->banner <> "")
                            {
                                $this->SetCover($ret->banner);
                                break;
                            }
                        }
                        if (property_exists($ret, 'art'))
                        {
                            if (property_exists($ret->art, 'thumb'))
                                if ($ret->art->thumb <> "")
                                {
                                    $this->SetCover($ret->art->thumb);
                                    break;
                                }
                        }
                        if (property_exists($ret, 'thumbnail'))
                        {
                            if ($ret->thumbnail <> "")
                            {
                                $this->SetCover($ret->thumbnail);
                                break;
                            }
                        }
                        $this->SetCover("");

                        break;
                    case"banner":
                        if (property_exists($ret, 'art'))
                        {
                            if (property_exists($ret->art, 'tvshow.banner'))
                            {
                                if ($ret->art->{'tvshow.banner'} <> "")
                                {
                                    $this->SetCover($ret->art->{'tvshow.banner'});
                                    break;
                                }
                            }
                        }
                        if (property_exists($ret, 'banner'))
                        {
                            if ($ret->banner <> "")
                            {
                                $this->SetCover($ret->banner);
                                break;
                            }
                        }
                        if (property_exists($ret, 'art'))
                        {
                            if (property_exists($ret->art, 'tvshow.poster'))
                            {
                                if ($ret->art->{'tvshow.poster'} <> "")
                                {
                                    $this->SetCover($ret->art->{'tvshow.poster'});
                                    break;
                                }
                            }
                            if (property_exists($ret->art, 'poster'))
                            {
                                if ($ret->art->{'poster'} <> "")
                                {
                                    $this->SetCover($ret->art->{'poster'});
                                    break;
                                }
                            }
                        }
                        if (property_exists($ret, 'poster'))
                        {
                            if ($ret->poster <> "")
                            {
                                $this->SetCover($ret->poster);
                                break;
                            }
                        }
                    default:
                        if (property_exists($ret, 'art'))
                        {
                            if (property_exists($ret->art, 'thumb'))
                                if ($ret->art->thumb <> "")
                                {
                                    $this->SetCover($ret->art->thumb);
                                    break;
                                }
                        }
                        if (property_exists($ret, 'thumbnail'))
                        {
                            if ($ret->thumbnail <> "")
                            {
                                $this->SetCover($ret->thumbnail);
                                break;
                            }
                        }
                        $this->SetCover("");

                        break;
                }
                break;
        }
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_GetItem'.
     * Holt die Daten des aktuellen wiedergegebenen Items, und gibt Diese als Array zurück.
     * 
     * @access public
     * @return array|bool Das Array mit den Eigenschaften des Item, im Fehlerfall false
     */
    public function GetItem()
    {
        $this->Init();
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetItem(array('playerid' => $this->PlayerId, 'properties' => self::$ItemList));
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret))
            return false;
        return json_decode(json_encode($ret->item), true);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_SetSubtitle'.
     * Deaktiviert oder aktiviert einen Untertitel
     * 
     * @access public
     * @param int $Value Index des zu aktivierenden Untertitels, -1 für keinen.
     * @return bool true bei erfolgreicher Ausführung und dekodierung, sonst false.
     */
    public function SetSubtitle(int $Value)
    {
        if (!is_int($Value))
        {
            trigger_error('Value must be integer', E_USER_NOTICE);
            return false;
        }
        $this->Init();
        if (!$this->isActive)
        {
            trigger_error('Player not active', E_USER_NOTICE);
            return false;
        }
        if ($Value == -1)
            $Value = "off";
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->SetSubtitle(array("playerid" => $this->PlayerId, "subtitle" => $Value));
        $ret = $this->Send($KodiData);
//        if (is_null($ret))
//            return false;
        if ($ret === "OK")
            return true;
        trigger_error('Error on set audiostream.', E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_SetAudioStream'.
     * Aktiviert einen bestimmten Audiostream.
     * 
     * @access public
     * @param int $Value Index des zu aktivierenden Audiostream.
     * @return bool true bei erfolgreicher Ausführung und dekodierung, sonst false.
     */
    public function SetAudioStream(int $Value)
    {
        if (!is_int($Value))
        {
            trigger_error('Value must be integer', E_USER_NOTICE);
            return false;
        }
        $this->Init();
        if (!$this->isActive)
        {
            trigger_error('Player not active', E_USER_NOTICE);
            return false;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->SetAudioStream(array("playerid" => $this->PlayerId, "stream" => $Value));
        $ret = $this->Send($KodiData);
//        if (is_null($ret))
//            return false;
        if ($ret === "OK")
            return true;
        trigger_error('Error on set audiostream.', E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_Play'.
     * Starte die Wiedergabe des aktuelle pausierten Items.
     * 
     * @access public
     * @return bool True bei Erfolg, sonst false.
     */
    public function Play()
    {
        $this->Init();
        if (!$this->isActive)
            return $this->LoadPlaylist();

//        {
//            trigger_error('Player not active', E_USER_NOTICE);
//        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->PlayPause(array("playerid" => $this->PlayerId, "play" => true));
        $ret = $this->Send($KodiData);
        if (is_null($ret))
            return false;
        if ($ret->speed === 1)
        {
            $this->SetValueInteger("Status", 2);
            return true;
        }
        else
        {
            trigger_error('Error on send play.', E_USER_NOTICE);
        }

        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_Pause'.
     * Pausiert die Wiedergabe des aktuellen Items.
     * 
     * @access public
     * @return bool True bei Erfolg, sonst false.
     */
    public function Pause()
    {
        $this->Init();
        if (!$this->isActive)
        {
            trigger_error('Player not active', E_USER_NOTICE);
            return false;
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->PlayPause(array("playerid" => $this->PlayerId, "play" => false));
        $ret = $this->Send($KodiData);
        if (is_null($ret))
            return false;
        if ($ret->speed === 0)
        {
            $this->SetValueInteger("Status", 3);
            return true;
        }
        else
        {
            trigger_error('Error on send pause.', E_USER_NOTICE);
        }

        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_Stop'.
     * Stoppt die Wiedergabe des aktuellen Items.
     * 
     * @access public
     * @return bool True bei Erfolg, sonst false.
     */
    public function Stop()
    {
        $this->Init();
        if (!$this->isActive)
        {
            trigger_error('Player not active', E_USER_NOTICE);
            return false;
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Stop(array("playerid" => $this->PlayerId));
        $ret = $this->Send($KodiData);
        if (is_null($ret))
            return false;
        if ($ret === "OK")
        {
            $this->SetValueInteger("Status", 1);
            return true;
        }
        else
        {
            trigger_error('Error on send stop.', E_USER_NOTICE);
        }
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_GoToNext'.
     * Springt zum nächsten Item in der Wiedergabeliste.
     * 
     * @access public
     * @return bool True bei Erfolg, sonst false.
     */
    public function GoToNext()
    {
        return $this->GoToValue('next');
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_GoToPrevious'.
     * Springt zum vorherigen Item in der Wiedergabeliste.
     * 
     * @access public
     * @return bool True bei Erfolg, sonst false.
     */
    public function GoToPrevious()
    {
        return $this->GoToValue('previous');
    }

    /**
     * Springt auf ein bestimmtes Item in der Wiedergabeliste.
     * 
     * @access private
     * @param int|string $Value Index oder String-Enum
     *   enum["previous", "next"]
     * @return bool True bei Erfolg, sonst false.
     */
    private function GoToValue($Value)
    {
        $this->Init();
        if (!$this->isActive)
        {
            trigger_error('Player not active', E_USER_NOTICE);
            return false;
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GoTo(array("playerid" => $this->PlayerId, "to" => $Value));
        $ret = $this->Send($KodiData);
        if (is_null($ret))
            return false;
        if ($ret === "OK")
            return true;
        trigger_error('Error on send ' . $Value . '.', E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_GoToTrack'.
     * Springt auf ein bestimmtes Item in der Wiedergabeliste.
     * 
     * @access public
     * @param int $Value Index in der Wiedergabeliste.
     * @return bool True bei Erfolg, sonst false.
     */
    public function GoToTrack(int $Value)
    {
        if (!is_int($Value))
        {
            trigger_error('Value must be integer', E_USER_NOTICE);
            return false;
        }
        return $this->GoToValue($Value + 1);
        /* $this->Init();
          if (!$this->isActive)
          {
          trigger_error('Player not active', E_USER_NOTICE);
          return false;
          }

          $KodiData = new Kodi_RPC_Data(self::$Namespace);
          $Kodi->GoTo(array("playerid" => $this->PlayerId, "to" => $Value + 1));
          $ret = $this->Send($KodiData);
          if (is_null($ret))
          return false;
          if ($ret === "OK")
          return true;
          trigger_error('Error on goto track.', E_USER_NOTICE);
          return false; */
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_SetShuffle'.
     * Setzt den Zufallsmodus.
     * 
     * @access public
     * @param bool $Value True für Zufallswiedergabe aktiv, false für deaktiv.
     * @return bool True bei Erfolg, sonst false.
     */
    public function SetShuffle(bool $Value)
    {
        if (!is_bool($Value))
        {
            trigger_error('Value must be boolean', E_USER_NOTICE);
            return false;
        }
        $this->Init();
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->SetShuffle(array("playerid" => $this->PlayerId, "shuffle" => $Value));
        $ret = $this->Send($KodiData);
        if (is_null($ret))
            return false;
        if ($ret === "OK")
        {
            $this->SetValueBoolean("shuffled", $Value);
            return true;
        }
        else
        {
            trigger_error('Error on set shuffle.', E_USER_NOTICE);
        }

        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_SetRepeat'.
     * Setzten den Wiederholungsmodus.
     * 
     * @access public
     * @param int $Value Modus der Wiederholung.
     *   enum[0=aus, 1=Titel, 2=Alle]
     * @return bool True bei Erfolg, sonst false.
     */
    public function SetRepeat(int $Value)
    {
        if (!is_int($Value))
        {
            trigger_error('Value must be integer', E_USER_NOTICE);
            return false;
        }
        if (($Value < 0) or ( $Value > 2))
        {
            trigger_error('Value must be between 0 and 2', E_USER_NOTICE);
            return false;
        }
        $this->Init();
        $repeat = array("off", "one", "all");
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->SetRepeat(array("playerid" => $this->PlayerId, "repeat" => $repeat[$Value]));
        $ret = $this->Send($KodiData);
        if (is_null($ret))
            return false;
        if ($ret === "OK")
        {
            $this->SetValueInteger("repeat", $Value);
            return true;
        }
        else
        {
            trigger_error('Error on set repeat.', E_USER_NOTICE);
        }

        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_SetPartymode'.
     * Setzt den Partymodus.
     * 
     * @access public
     * @param bool $Value True für Partymodus aktiv, false für deaktiv.
     * @return bool True bei Erfolg, sonst false.
     */
    public function SetPartymode(bool $Value)
    {
        if (!is_bool($Value))
        {
            trigger_error('Value must be boolean', E_USER_NOTICE);
            return false;
        }
        $this->Init();
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->SetPartymode(array("playerid" => $this->PlayerId, "partymode" => $Value));
        $ret = $this->Send($KodiData);
        if (is_null($ret))
            return false;
        if ($ret === "OK")
        {
            $this->SetValueBoolean("partymode", $Value);
            return true;
        }
        else
        {
            trigger_error('Error on set partymode.', E_USER_NOTICE);
        }

        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_SetSpeed'.
     * Setzten die Abspielgeschwindigkeit.
     * 
     * @access public
     * @param int $Value Geschwindigkeit.
     *   enum[-32, -16, -8, -4, -2, 0, 1, 2, 4, 8, 16, 32]
     * @return bool True bei Erfolg, sonst false.
     */
    public function SetSpeed(int $Value)
    {
        if (!is_int($Value))
        {
            trigger_error('Value must be integer', E_USER_NOTICE);
            return false;
        }
        if ($Value == 1)
            return $this->Play();
        if ($Value == 0)
            return $this->Pause();
        $this->Init();

        if (!$this->isActive)
        {
            trigger_error('Player not active', E_USER_NOTICE);
            return false;
        }

        if (!in_array($Value, array(-32, -16, -8, -4, -2, -1, 0, 1, 2, 4, 8, 16, 32)))
        {
            trigger_error('Invalid Value for speed.', E_USER_NOTICE);
            return false;
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->SetSpeed(array("playerid" => $this->PlayerId, "speed" => $Value));
        $ret = $this->Send($KodiData);
        if (is_null($ret))
            return false;
        if ((int) $ret->speed == $Value)
        {
            $this->SetValueInteger("speed", $Value);
            return true;
        }
        else
        {
            trigger_error('Error on set speed.', E_USER_NOTICE);
        }
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_SetPosition'.
     * Springt auf eine absolute Position innerhalb einer Wiedergabe.
     * 
     * @access public
     * @param int $Value Position in Prozent
     * @return bool True bei Erfolg, sonst false.
     */
    public function SetPosition(int $Value)
    {
        if (!is_int($Value))
        {
            trigger_error('Value must be integer', E_USER_NOTICE);
            return false;
        }
        if (($Value < 0) or ( $Value > 100))
        {
            trigger_error('Value must be between 0 and 100', E_USER_NOTICE);
            return false;
        }
        $this->Init();
        if (!$this->isActive)
        {
            trigger_error('Player not active', E_USER_NOTICE);
            return false;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Seek(array("playerid" => $this->PlayerId, "value" => array("percentage" => $Value)));
        $ret = $this->Send($KodiData);
        if (is_null($ret))
            return false;
        // TODO Werte stimmen bei audio nicht =?!
        if ((int) $ret->percentage == $Value)
        {
            $this->SetValueInteger("percentage", $Value);
            return true;
        }
        else
        {
            trigger_error('Error on set Position.', E_USER_NOTICE);
        }
        return false;
    }

    /**
     * Lädt ein Item und startet die Wiedergabe.
     * 
     * @access private
     * @param string $ItemTyp Der Typ des Item.
     * @param string $ItemValue Der Wert des Item.
     * @param array $Ext Array welches mit übergeben werden soll (optional).
     * @return bool True bei Erfolg. Sonst false.
     */
    private function Load(string $ItemTyp, string $ItemValue, $Ext = array())
    {
        $this->Init();
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Open(array_merge(array("item" => array($ItemTyp => $ItemValue)), $Ext));
        $ret = $this->Send($KodiData);
//        if (is_null($ret))
//            return false;
        if ($ret === "OK")
            return true;
        trigger_error('Error on load ' . $ItemTyp . '.', E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODPLAYER_LoadAlbum'.
     * Lädt ein Album und startet die Wiedergabe.
     * 
     * @access public
     * @param int $AlbumId ID des Album.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.  
     */
    public function LoadAlbum(int $AlbumId)
    {
        if (!is_int($AlbumId))
        {
            trigger_error('AlbumId must be integer', E_USER_NOTICE);
            return false;
        }
        return $this->Load("albumid", $AlbumId);
    }

    /**
     * IPS-Instanz-Funktion 'KODPLAYER_LoadArtist'.
     * Lädt alle Itemes eines Artist und startet die Wiedergabe.
     * 
     * @access public
     * @param int $ArtistId ID des Artist.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.  
     */
    public function LoadArtist(int $ArtistId)
    {
        if (!is_int($ArtistId))
        {
            trigger_error('ArtistId must be integer', E_USER_NOTICE);
            return false;
        }
        return $this->Load("artistid", $ArtistId);
    }

    /**
     * IPS-Instanz-Funktion 'KODPLAYER_LoadDirectory'.
     * Lädt alle Itemes eines Verzeichnisses und startet die Wiedergabe.
     * 
     * @access public
     * @param string $Directory Pfad welcher hinzugefügt werden soll.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.  
     */
    public function LoadDirectory(string $Directory)
    {
        if (!is_string($Directory))
        {
            trigger_error('Directory must be string', E_USER_NOTICE);
            return false;
        }
        return $this->Load("directory", $Directory);
    }

    /**
     * IPS-Instanz-Funktion 'KODPLAYER_LoadDirectoryRecursive'.
     * Lädt alle Itemes eines Verzeichnisses, sowie dessen Unterverzeichnisse, und startet die Wiedergabe.
     * 
     * @access public
     * @param string $Directory Pfad welcher hinzugefügt werden soll.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.  
     */
    public function LoadDirectoryRecursive(string $Directory)
    {
        if (!is_string($Directory))
        {
            trigger_error('Directory must be string', E_USER_NOTICE);
            return false;
        }
        return $this->Load("Directory", $Directory, array("recursive" => true));
    }

    /**
     * IPS-Instanz-Funktion 'KODPLAYER_LoadEpisode'.
     * Lädt eine Episode und startet die Wiedergabe.
     * 
     * @access public
     * @param int $EpisodeId ID der Episode.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.  
     */
    public function LoadEpisode(int $EpisodeId)
    {
        if (!is_int($EpisodeId))
        {
            trigger_error('EpisodeId must be integer', E_USER_NOTICE);
            return false;
        }
        return $this->Load("episodeid", $EpisodeId);
    }

    /**
     * IPS-Instanz-Funktion 'KODPLAYER_LoadFile'.
     * Lädt eine Datei und startet die Wiedergabe.
     * 
     * @access public
     * @param string $File Pfad zu einer Datei.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.  
     */
    public function LoadFile(string $File)
    {
        if (!is_string($File))
        {
            trigger_error('File must be string', E_USER_NOTICE);
            return false;
        }
        return $this->Load("file", $File);
    }

    /**
     * IPS-Instanz-Funktion 'KODPLAYER_LoadGenre'.
     * Lädt eine komplettes Genre und startet die Wiedergabe.
     * 
     * @access public
     * @param int $GenreId ID des Genres.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.  
     */
    public function LoadGenre(int $GenreId)
    {
        if (!is_int($GenreId))
        {
            trigger_error('GenreId must be integer', E_USER_NOTICE);
            return false;
        }
        return $this->Load("genreid", $GenreId);
    }

    /**
     * IPS-Instanz-Funktion 'KODPLAYER_LoadMovie'.
     * Lädt ein Film und startet die Wiedergabe.
     * 
     * @access public
     * @param int $MovieId ID des Filmes.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.  
     */
    public function LoadMovie(int $MovieId)
    {
        if (!is_int($MovieId))
        {
            trigger_error('MovieId must be integer', E_USER_NOTICE);
            return false;
        }
        return $this->Load("movieid", $MovieId);
    }

    /**
     * IPS-Instanz-Funktion 'KODPLAYER_LoadMusicvideo'.
     * Lädt ein Musicvideo und startet die Wiedergabe.
     * 
     * @access public
     * @param int $MusicvideoId ID des Musicvideos.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.  
     */
    public function LoadMusicvideo(int $MusicvideoId)
    {
        if (!is_int($MusicvideoId))
        {
            trigger_error('MusicvideoId must be integer', E_USER_NOTICE);
            return false;
        }
        return $this->Load("musicvideoid", $MusicvideoId);
    }

    /**
     * IPS-Instanz-Funktion 'KODPLAYER_LoadPlaylist'.
     * Lädt die Playlist und startet die Wiedergabe.
     * 
     * @access public
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.  
     */
    public function LoadPlaylist()
    {
        $this->Init();
        return $this->Load("playlistid", $this->PlayerId);
    }

    /**
     * IPS-Instanz-Funktion 'KODPLAYER_LoadSong'.
     * Lädt ein Songs und startet die Wiedergabe.
     * 
     * @access public
     * @param int $SongId ID des Songs.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.  
     */
    public function LoadSong(int $SongId)
    {
        if (!is_int($SongId))
        {
            trigger_error('SongId must be integer', E_USER_NOTICE);
            return false;
        }
        return $this->Load("songid", $SongId);
    }

    /**
     * Liefert den Parent der Instanz.
     * 
     * @return int|bool InstanzID des Parent, false wenn kein Parent vorhanden.
     */
    protected function GetParent()
    {
        $instance = IPS_GetInstance($this->InstanceID);
        return ($instance['ConnectionID'] > 0) ? $instance['ConnectionID'] : false;
    }

    private function CreateProfilArray($Data, $Assos = Array())
    {


        foreach ($Data as $item)
        {
            if ($item->language == "")
            {
                if (property_exists($item, 'name'))
                    $Assos[] = array($item->index, $item->name, "", -1);
                else
                    $Assos[] = array($item->index, "Unbekannt", "", -1);
            }
            else
                $Assos[] = array($item->index, $item->language, "", -1);
        }
        return $Assos;
    }

    private function CreateSubtitleProfil($Subtitles)
    {
        $Assos[0] = Array(-1, "Aus", "", -1);
        $Assos = $this->CreateProfilArray($Subtitles, $Assos);

        $this->RegisterProfileIntegerEx("Subtitels." . $this->InstanceID . ".Kodi", "", "", "", $Assos);
    }

    private function CreateAudioProfil($AudioStream)
    {
        $Assos = $this->CreateProfilArray($AudioStream);
        $this->RegisterProfileIntegerEx("AudioStream." . $this->InstanceID . ".Kodi", "", "", "", $Assos);
    }

}

/** @} */
?>