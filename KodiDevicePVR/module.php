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
 * KodiDevicePVR Klasse für den Namespace PVR der KODI-API.
 * Erweitert KodiBase.
 *
 * @package       Kodi
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2016 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       1.0
 * @example <b>Ohne</b>
 */
class KodiDevicePVR extends KodiBase
{

    /**
     * RPC-Namespace
     * 
     * @access private
     *  @var string
     * @value 'PVR'
     */
    static $Namespace = 'PVR';

    /**
     * Alle Properties des RPC-Namespace
     * 
     * @access private
     *  @var array 
     */
    static $Properties = array(
        "available",
        "recording",
        "scanning"
    );

    /**
     * Alle Eigenschaften von Kanal-Items.
     * 
     * @access private
     *  @var array 
     */
    static $ChanneltemList = array(
        "thumbnail",
        "channeltype",
        "hidden",
        "locked",
        "channel",
        "lastplayed"
    );

    /**
     * Alle Eigenschaften von Sendung-Items.
     * 
     * @access private
     *  @var array 
     */
    static $BroadcastItemList = array(
        "title",
        "plot",
        "plotoutline",
        "starttime",
        "endtime",
        "runtime",
        "progress",
        "progresspercentage",
        "genre",
        "episodename",
        "episodenum",
        "episodepart",
        "firstaired",
        "hastimer",
        "isactive",
        "parentalrating",
        "wasactive",
        "thumbnail",
        "rating"
    );

    /**
     * Alle Eigenschaften von Aufnahmen.
     * 
     * @access private
     *  @var array 
     */
    static $RecordingItemList = array(
        "title",
        "plot",
        "plotoutline",
        "genre",
        "playcount",
        "resume",
        "channel",
        "starttime",
        "endtime",
        "runtime",
        "lifetime",
        "icon",
        "art",
        "streamurl",
        "file",
        "directory"
    );

    /**
     * Alle Eigenschaften von Timern.
     * 
     * @access private
     *  @var array 
     */
    static $TimerItemList = array(
        "title",
        "summary",
        "channelid",
        "isradio",
        "repeating",
        "starttime",
        "endtime",
        "runtime",
        "lifetime",
        "firstday",
        "weekdays",
        "priority",
        "startmargin",
        "endmargin",
        "state",
        "file",
        "directory"
    );

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Create()
    {
        parent::Create();
        $this->RegisterPropertyBoolean("showIsAvailable", true);
        $this->RegisterPropertyBoolean("showIsRecording", true);
        $this->RegisterPropertyBoolean("showDoRecording", true);
        $this->RegisterPropertyBoolean("showIsScanning", true);
        $this->RegisterPropertyBoolean("showDoScanning", true);
    }

    /**
     * Interne Funktion des SDK.
     * 
     * @access public
     */
    public function ApplyChanges()
    {
        $this->RegisterProfileIntegerEx("Action.Kodi", "", "", "", Array(
            Array(0, "Ausführen", "", -1)
        ));
        if ($this->ReadPropertyBoolean('showIsAvailable'))
            $this->RegisterVariableBoolean("available", "Verfügbar", "", 1);
        else
            $this->UnregisterVariable("available");

        if ($this->ReadPropertyBoolean('showIsRecording'))
            $this->RegisterVariableBoolean("recording", "Aufnahme läuft", "", 3);
        else
            $this->UnregisterVariable("recording");

        if ($this->ReadPropertyBoolean('showDoRecording'))
        {
            $this->RegisterVariableBoolean("record", "Aufnahme aktueller Kanal", "~Switch", 4);
            $this->EnableAction("record");
        }
        else
            $this->UnregisterVariable("record");

        if ($this->ReadPropertyBoolean('showIsScanning'))
            $this->RegisterVariableBoolean("scanning", "Kanalsuche aktiv", "", 5);
        else
            $this->UnregisterVariable("scanning");

        if ($this->ReadPropertyBoolean('showDoScanning'))
        {
            $this->RegisterVariableInteger("scan", "Kanalsuche starten", "Action.Kodi", 6);
            $this->EnableAction("scan");
        }
        else
            $this->UnregisterVariable("scan");
        @parent::ApplyChanges();
    }

################## PRIVATE     

    /**
     * Dekodiert die empfangenen Events und Anworten auf 'GetProperties'.
     *
     * @param string $Method RPC-Funktion ohne Namespace
     * @param object $KodiPayload Der zu dekodierende Datensatz als Objekt.
     */
    protected function Decode($Method, $KodiPayload)
    {
        switch ($Method)
        {
            case "GetProperties":
                foreach ($KodiPayload as $param => $value)
                {
                    $this->SetValueBoolean($param, $value);
                }
                break;
            default:
                $this->SendDebug('KODI_Event', $KodiPayload, 0);
                break;
        }
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
            case "scan":
                return $this->Scan();
            case "record":
                return $this->Record($Value, "current");
            default:
                trigger_error('Invalid Ident.', E_USER_NOTICE);
        }
    }

################## PUBLIC

    /**
     * IPS-Instanz-Funktion 'KODIPVR_Scan'. Startet einen Suchlauf.
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function Scan()
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Scan();
        $ret = $this->Send($KodiData);
        if (is_null($ret))
            return false;
        if ($ret === "OK")
            return true;
        trigger_error('Error start scan', E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPVR_Record'. Startet/Beendet eine Aufnahme.
     *
     * @access public
     * @param bool $Record True für starten, false zum stoppen.
     * @param string $Channel Kanalname.
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function Record(bool $Record, string $Channel)
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Record(array("record" => $Record, "channel" => $Channel));
        $ret = $this->Send($KodiData);
        if (is_null($ret))
            return false;
        if ($ret === "OK")
            return true;
        trigger_error('Error start recording', E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPVR_GetChannels'. Liest die Kanalliste
     *
     * @access public
     * @param string $ChannelTyp [enum "tv", "radio"] Kanaltyp welcher gelesen werden soll.
     * @return array|bool Ein Array mit den Daten oder FALSE bei Fehler.
     */
    public function GetChannels(string $ChannelTyp)
    {
        if (!in_array($ChannelTyp, array("radio", "tv")))
        {
            trigger_error("ChannelTyp must 'tv' or 'radio'.", E_USER_NOTICE);
            return false;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetChannels(array("channelgroupid" => "all" . $ChannelTyp, "properties" => static::$ChanneltemList));
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret))
            return false;
        if ($ret->limits->total > 0)
            return json_decode(json_encode($ret->channels), true);
        return array();
    }

    /**
     * IPS-Instanz-Funktion 'KODIPVR_GetChannelDetails'. Liefert die Eigenschaften eines Kanals.
     *
     * @access public
     * @param int $ChannelId Kanal welcher gelesen werden soll.
     * @return array|bool Ein Array mit den Daten oder FALSE bei Fehler.
     */
    public function GetChannelDetails(int $ChannelId)
    {
        if (!is_int($ChannelId))
        {
            trigger_error("ChannelId must be integer.", E_USER_NOTICE);
            return false;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetChannelDetails(array("channelid" => $ChannelId, "properties" => static::$ChanneltemList));
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret))
            return false;
        return json_decode(json_encode($ret->channeldetails), true);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPVR_GetChannelGroups'. Liest alle Kanalgruppen.
     *
     * @access public
     * @param string $ChannelTyp [enum "tv", "radio"] Kanaltyp welcher gelesen werden soll.
     * @return array|bool Ein Array mit den Daten oder FALSE bei Fehler.
     */
    public function GetChannelGroups(string $ChannelTyp)
    {
        if (!in_array($ChannelTyp, array("radio", "tv")))
        {
            trigger_error("ChannelTyp must 'tv' or 'radio'.", E_USER_NOTICE);
            return false;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetChannelGroups(array("channeltype" => "all" . $ChannelTyp)); //, "properties" => static::$ItemList));
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret))
            return false;
        if ($ret->limits->total > 0)
            return json_decode(json_encode($ret->channelgroups), true);
        return array();
    }

    /**
     * IPS-Instanz-Funktion 'KODIPVR_GetChannelGroupDetails'. Liefert die Eigenschaften einer Kanalgruppe.
     *
     * @access public
     * @param int $ChannelGroupdId Kanal welcher gelesen werden soll.
     * @return array|bool Ein Array mit den Daten oder FALSE bei Fehler.
     */
    public function GetChannelGroupDetails(int $ChannelGroupdId)
    {
        if (!is_int($ChannelGroupdId))
        {
            trigger_error("ChannelId must be integer.", E_USER_NOTICE);
            return false;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetChannelGroupDetails(array("channelgroupid" => $ChannelGroupdId, "properties" => static::$ChanneltemList));
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret))
            return false;
        if ($ret->limits->total > 0)
            return json_decode(json_encode($ret->channelgroupdetails), true);
        return array();
    }

    /**
     * IPS-Instanz-Funktion 'KODIPVR_GetBroadcasts'. Liest die Sendungen eines Senders.
     *
     * @access public
     * @param string $ChannelId  Kanal welcher gelesen werden soll.
     * @return array|bool Ein Array mit den Daten oder FALSE bei Fehler.
     */
    public function GetBroadcasts(int $ChannelId)
    {
        if (!is_int($ChannelId))
        {
            trigger_error("ChannelId must be integer.", E_USER_NOTICE);
            return false;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetBroadcasts(array("channelid" => $ChannelId, "properties" => static::$BroadcastItemList));
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret))
            return false;
        if ($ret->limits->total > 0)
            return json_decode(json_encode($ret->broadcasts), true);
        return array();
    }

    /**
     * IPS-Instanz-Funktion 'KODIPVR_GetBroadcastDetails'. Liefert die Eigenschaften einer Sendung.
     *
     * @access public
     * @param int $BroadcastId Sendung welche gelesen werden soll.
     * @return array|bool Ein Array mit den Daten oder FALSE bei Fehler.
     */
    public function GetBroadcastDetails(int $BroadcastId)
    {
        if (!is_int($BroadcastId))
        {
            trigger_error("BroadcastId must be integer.", E_USER_NOTICE);
            return false;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetBroadcastDetails(array("broadcastid" => $BroadcastId));//, "properties" => static::$BroadcastItemList));
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret))
            return false;
        return json_decode(json_encode($ret->broadcastdetails), true);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPVR_GetRecordings'. Liefert alle Aufnahmen.
     *
     * @access public
     * @return array|bool Ein Array mit den Daten oder FALSE bei Fehler.
     */
    public function GetRecordings()
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetRecordings(array("properties" => static::$RecordingItemList));
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret))
            return false;
        if ($ret->limits->total > 0)
            return json_decode(json_encode($ret->recordings), true);
        return array();
    }

    /**
     * IPS-Instanz-Funktion 'KODIPVR_GetRecordingDetails'. Liefert die Eigenschaften einer Aufnahme.
     *
     * @access public
     * @param int $RecordingId Aufnahme welche gelesen werden soll.
     * @return array|bool Ein Array mit den Daten oder FALSE bei Fehler.
     */
    public function GetRecordingDetails(int $RecordingId)
    {
        if (!is_int($RecordingId))
        {
            trigger_error("RecordingId must be integer.", E_USER_NOTICE);
            return false;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetRecordingDetails(array("recordingid" => $RecordingId, "properties" => static::$RecordingItemList));
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret))
            return false;
        return json_decode(json_encode($ret->recordingdetails), true);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPVR_GetTimers'. Liefert alle Aufnahmetimer.
     *
     * @access public
     * @return array|bool Ein Array mit den Daten oder FALSE bei Fehler.
     */
    public function GetTimers()
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetTimers(array("properties" => static::$TimerItemList));
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret))
            return false;
        if ($ret->limits->total > 0)
            return json_decode(json_encode($ret->timers), true);
        return array();
    }

    /**
     * IPS-Instanz-Funktion 'KODIPVR_GetTimerDetails'. Liefert die Eigenschaften einer Aufnahmetimers.
     *
     * @access public
     * @param int $TimerId Timers welcher gelesen werden soll.
     * @return array|bool Ein Array mit den Daten oder FALSE bei Fehler.
     */
    public function GetTimerDetails(int $TimerId)
    {
        if (!is_int($TimerId))
        {
            trigger_error("TimerId must be integer.", E_USER_NOTICE);
            return false;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetTimerDetails(array("timerid" => $TimerId, "properties" => static::$TimerItemList));
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret))
            return false;
        return json_decode(json_encode($ret->timerdetails), true);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPVR_RequestState'. Frage eine oder mehrere Properties eines Namespace ab.
     *
     * @access public
     * @param string $Ident Enthält den Names des "properties" welches angefordert werden soll.
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function RequestState(string $Ident)
    {
        return parent::RequestState($Ident);
    }

}

/** @} */
?>