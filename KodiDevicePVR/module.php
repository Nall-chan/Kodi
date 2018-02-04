<?php

require_once(__DIR__ . "/../libs/KodiClass.php");  // diverse Klassen
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
 * @todo PVR.AddTimer ab v8
 * @todo PVR.ToggleTimer ab v8
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
    public static $Namespace = 'PVR';

    /**
     * Alle Properties des RPC-Namespace
     *
     * @access private
     *  @var array
     */
    public static $Properties = array(
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
    public static $ChanneltemList = array(
        "thumbnail",
        "channeltype",
        "hidden",
        "locked",
        "channel",
        "lastplayed"
    );

    /**
     * Alle Eigenschaften von Kanal-Items.
     *
     * @access private
     *  @var array
     */
    public static $ChanneltemListFull = array(
        "thumbnail",
        "channeltype",
        "hidden",
        "locked",
        "channel",
        "lastplayed",
        "broadcastnow",
        "broadcastnext"
    );

    /**
     * Alle Eigenschaften von Sendung-Items.
     *
     * @access private
     *  @var array
     */
    public static $BroadcastItemList = array(
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
    public static $RecordingItemList = array(
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
    public static $TimerItemList = array(
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
        $this->RegisterPropertyBoolean('showTVChannellist', true);
        $this->RegisterPropertyInteger('showMaxTVChannels', 20);
        $ID = @$this->GetIDForIdent('TVChannellistDesign');
        if ($ID == false) {
            $ID = $this->RegisterScript('TVChannellistDesign', 'TV Channellist Config', $this->CreateTVChannellistConfigScript(), -7);
        }
        IPS_SetHidden($ID, true);
        $this->RegisterPropertyInteger("TVChannellistconfig", $ID);
        $this->RegisterPropertyInteger("TVThumbSize", 100);
        $this->RegisterPropertyBoolean('showRadioChannellist', true);
        $this->RegisterPropertyInteger('showMaxRadioChannels', 20);
        $ID = @$this->GetIDForIdent('RadioChannellistDesign');
        if ($ID == false) {
            $ID = $this->RegisterScript('RadioChannellistDesign', 'Radio Channellist Config', $this->CreateRadioChannellistConfigScript(), -7);
        }
        IPS_SetHidden($ID, true);
        $this->RegisterPropertyInteger("RadioChannellistconfig", $ID);
        $this->RegisterPropertyInteger("RadioThumbSize", 100);

        $this->RegisterPropertyBoolean('showRecordinglist', true);
        $this->RegisterPropertyInteger('showMaxRecording', 20);
        $ID = @$this->GetIDForIdent('RecordinglistDesign');
        if ($ID == false) {
            $ID = $this->RegisterScript('RecordinglistDesign', 'Recordinglist Config', $this->CreateRecordlistConfigScript(), -7);
        }
        IPS_SetHidden($ID, true);
        $this->RegisterPropertyInteger("Recordinglistconfig", $ID);
        $this->RegisterPropertyInteger("RecordingThumbSize", 100);
        $this->RegisterTimer('RefreshLists', 0, 'KODIPVR_RefreshAll(' . $this->InstanceID . ');');
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Destroy()
    {
        if (IPS_GetKernelRunlevel() <> KR_READY) {
            return;
        }
        $this->UnregisterHook('/hook/KodiTVChannellist' . $this->InstanceID);
        $this->UnregisterHook('/hook/KodiRadioChannellist' . $this->InstanceID);
        $this->UnregisterHook('/hook/KodiRecordinglist' . $this->InstanceID);
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function ApplyChanges()
    {
        $this->RegisterProfileIntegerEx("Action.Kodi", "", "", "", array(
            array(0, "Ausführen", "", -1)
        ));
        if ($this->ReadPropertyBoolean('showIsAvailable')) {
            $this->RegisterVariableBoolean("available", "Verfügbar", "", 1);
        } else {
            $this->UnregisterVariable("available");
        }

        if ($this->ReadPropertyBoolean('showIsRecording')) {
            $this->RegisterVariableBoolean("recording", "Aufnahme läuft", "", 3);
        } else {
            $this->UnregisterVariable("recording");
        }

        if ($this->ReadPropertyBoolean('showDoRecording')) {
            $this->RegisterVariableBoolean("record", "Aufnahme aktueller Kanal", "~Switch", 4);
            $this->EnableAction("record");
        } else {
            $this->UnregisterVariable("record");
        }

        if ($this->ReadPropertyBoolean('showIsScanning')) {
            $this->RegisterVariableBoolean("scanning", "Kanalsuche aktiv", "", 5);
        } else {
            $this->UnregisterVariable("scanning");
        }

        if ($this->ReadPropertyBoolean('showDoScanning')) {
            $this->RegisterVariableInteger("scan", "Kanalsuche starten", "Action.Kodi", 6);
            $this->EnableAction("scan");
        } else {
            $this->UnregisterVariable("scan");
        }

        $this->UnregisterScript("WebHookTVChannellist");
        $this->UnregisterScript("WebHookRadioChannellist");
        $this->UnregisterScript("WebHookRecordinglist");

        if ($this->ReadPropertyBoolean('showTVChannellist')) {
            $this->RegisterVariableString("TVChannellist", "TV Kanäle", "~HTMLBox", 1);
            if (IPS_GetKernelRunlevel() == KR_READY) {
                $this->RegisterHook('/hook/KodiTVChannellist' . $this->InstanceID);
            }

            $ID = @$this->GetIDForIdent('TVChannellistDesign');
            if ($ID == false) {
                $ID = $this->RegisterScript('TVChannellistDesign', 'TVChannellist Config', $this->CreateTVChannellistConfigScript(), -7);
            }
            IPS_SetHidden($ID, true);
        } else {
            $this->UnregisterVariable("TVChannellist");
            if (IPS_GetKernelRunlevel() == KR_READY) {
                $this->UnregisterHook('/hook/KodiTVChannellist' . $this->InstanceID);
            }
        }

        if ($this->ReadPropertyBoolean('showRadioChannellist')) {
            $this->RegisterVariableString("RadioChannellist", "Radio Kanäle", "~HTMLBox", 1);
            if (IPS_GetKernelRunlevel() == KR_READY) {
                $this->RegisterHook('/hook/KodiRadioChannellist' . $this->InstanceID);
            }

            $ID = @$this->GetIDForIdent('RadioChannellistDesign');
            if ($ID == false) {
                $ID = $this->RegisterScript('RadioChannellistDesign', 'RadioChannellist Config', $this->CreateRadioChannellistConfigScript(), -7);
            }
            IPS_SetHidden($ID, true);
        } else {
            $this->UnregisterVariable("RadioChannellist");
            if (IPS_GetKernelRunlevel() == KR_READY) {
                $this->UnregisterHook('/hook/KodiRadioChannellist' . $this->InstanceID);
            }
        }

        if ($this->ReadPropertyBoolean('showRecordinglist')) {
            $this->RegisterVariableString("Recordinglist", "Aufzeichnungen", "~HTMLBox", 1);
            if (IPS_GetKernelRunlevel() == KR_READY) {
                $this->RegisterHook('/hook/KodiRecordinglist' . $this->InstanceID);
            }

            $ID = @$this->GetIDForIdent('RecordinglistDesign');
            if ($ID == false) {
                $ID = $this->RegisterScript('RecordinglistDesign', 'Recordinglist Config', $this->CreateRecordlistConfigScript(), -7);
            }
            IPS_SetHidden($ID, true);
        } else {
            $this->UnregisterVariable("Recordinglist");
            if (IPS_GetKernelRunlevel() == KR_READY) {
                $this->UnregisterHook('/hook/KodiRecordinglist' . $this->InstanceID);
            }
        }

        if ($this->ReadPropertyBoolean('showRecordinglist') or $this->ReadPropertyBoolean('showRadioChannellist') or $this->ReadPropertyBoolean('showTVChannellist')) {
            $this->SetTimerInterval('RefreshLists', 15 * 60 * 1000);
        } else {
            $this->SetTimerInterval('RefreshLists', 0);
        }

        parent::ApplyChanges();
    }

    /**
     * Wird ausgeführt wenn sich der Status vom Parent ändert.
     * @access protected
     */
    protected function IOChangeState($State)
    {
        $this->SetTimerInterval('RefreshLists', 0);
        parent::IOChangeState($State);
        if ($State == IS_ACTIVE) {
            $this->RefreshTVChannellist();
            $this->RefreshRadioChannellist();
            $this->RefreshRecordinglist();
            if ($this->ReadPropertyBoolean('showRecordinglist') or $this->ReadPropertyBoolean('showRadioChannellist') or $this->ReadPropertyBoolean('showTVChannellist')) {
                $this->SetTimerInterval('RefreshLists', 15 * 60 * 1000);
            }
        }
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
        switch ($Method) {
            case "GetProperties":
                foreach ($KodiPayload as $param => $value) {
                    $this->SetValueBoolean($param, $value);
                }
                break;
            default:
                $this->SendDebug('KODI_Event', $KodiPayload, 0);
                break;
        }
    }

    /**
     * Erzeugt aus der Liste der TV-Kanäle eine HTML-Tabelle für eine ~HTMLBox-Variable.
     *
     * @access private
     */
    private function RefreshTVChannellist()
    {
        if (!$this->ReadPropertyBoolean('showTVChannellist')) {
            return;
        }
        $ScriptID = $this->ReadPropertyInteger('TVChannellistconfig');
        if ($ScriptID == 0) {
            return;
        }
        if (!IPS_ScriptExists($ScriptID)) {
            return;
        }
        $result = IPS_RunScriptWaitEx($ScriptID, array('SENDER' => 'Kodi'));
        $Config = @unserialize($result);
        if (($Config === false) or (!is_array($Config))) {
            trigger_error('Error on read TV Channelistconfig-Script');
            return;
        }

        //$Channels = $this->GetChannels('tv');
        $Max = $this->ReadPropertyInteger('showMaxTVChannels');
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetChannels(array("channelgroupid" => "alltv", "properties" => static::$ChanneltemListFull, 'limits' => array('end' => $Max)));
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return;
        }
        if ($ret->limits->total == 0) {
            return;
        }
        $Channels = $KodiData->ToArray($ret->channels);

        $Data = array_filter($Channels, array($this, "FilterChannels"), ARRAY_FILTER_USE_BOTH);
        $HTMLData = $this->GetTableHeader($Config);
        $pos = 0;
        if (count($Data) > 0) {
            foreach ($Data as $line) {
                $Line = array();
                foreach ($line as $key => $value) {
                    if (is_string($key)) {
                        $Line[ucfirst($key)] = $value;
                    } else {
                        $Line[$key] = $value;
                    } //$key is not a string
                }
                if (array_key_exists('Thumbnail', $Config["Spalten"])) {
                    if ($Line['Thumbnail'] <> "") {
                        $CoverRAW = $this->GetThumbnail($Line['Thumbnail'], $this->ReadPropertyInteger("TVThumbSize"), 0);
                        if ($CoverRAW === false) {
                            $Line['Thumbnail'] = "";
                        } else {
                            $Line['Thumbnail'] = '<img src="data:image/png;base64,' . base64_encode($CoverRAW) . '" />';
                        }
                    }
                }
                if (array_key_exists('Now', $Config["Spalten"]) and array_key_exists('Broadcastnow', $Line)) {
                    if (array_key_exists("title", $Line['Broadcastnow'])) {
                        $Line['Now'] = $Line['Broadcastnow']['title'];
                        if (array_key_exists("episodename", $Line['Broadcastnow'])) {
                            if ($Line['Broadcastnow']['episodename'] <> "") {
                                $Line['Now'] .= ' - ' . $Line['Broadcastnow']['episodename'];
                            }
                        }
                        if (array_key_exists("progresspercentage", $Line['Broadcastnow'])) {
                            $Line['Now'] .= ' (' . (int) $Line['Broadcastnow']['progresspercentage'] . '%)';
                        }
                    }
                } else {
                    $Line['Now'] = 'No Info';
                }
                if (array_key_exists('Next', $Config["Spalten"]) and array_key_exists('Broadcastnext', $Line)) {
                    if (array_key_exists("title", $Line['Broadcastnext'])) {
                        $Line['Next'] = $Line['Broadcastnext']['title'];
                        if (array_key_exists("episodename", $Line['Broadcastnext'])) {
                            if ($Line['Broadcastnext']['episodename'] <> "") {
                                $Line['Next'] .= ' - ' . $Line['Broadcastnext']['episodename'];
                            }
                        }
                        if (array_key_exists("starttime", $Line['Broadcastnext'])) {
                            $starttime = DateTime::createFromFormat('Y-m-d H:i:s', $Line['Broadcastnext']['starttime'], new DateTimeZone('UTC'));
                            $starttime->setTimezone(new DateTimeZone(date_default_timezone_get()));
                            $Line['Next'] .= ' (' . $starttime->format("H:i") . ')';
                        }
                    }
                } else {
                    $Line['Next'] = 'No Info';
                }

                //$HTMLData .='<tr style="' . $Config['Style']['BR' . ($pos % 2 ? 'U' : 'G')] . '" onclick="window.xhrGet' . $this->InstanceID . '({ url: \'hook/KodiTVChannellist' . $this->InstanceID . '?ID=' . $Line['Channelid'] . '\' })" >';
                $HTMLData .= '<tr style="' . $Config['Style']['BR' . ($pos % 2 ? 'U' : 'G')] . '" onclick="xhrGet' . $this->InstanceID . '({ url: \'hook/KodiTVChannellist' . $this->InstanceID . '?TYP=tv&ID=' . $Line['Channelid'] . '\' })" >';

                foreach ($Config['Spalten'] as $feldIndex => $value) {
                    if (!array_key_exists($feldIndex, $Line)) {
                        $Line[$feldIndex] = '';
                    }
                    if ($Line[$feldIndex] === -1) {
                        $Line[$feldIndex] = '';
                    }
                    if (is_array($Line[$feldIndex])) {
                        $Line[$feldIndex] = implode(', ', $Line[$feldIndex]);
                    }
                    $HTMLData .= '<td style="' . $Config['Style']['DF' . ($pos % 2 ? 'U' : 'G') . $feldIndex] . '">' . (string) $Line[$feldIndex] . '</td>';
                }
                $HTMLData .= '</tr>' . PHP_EOL;
                $pos++;
//                if ($pos == $max)
//                    break;
            }
        }
        $HTMLData .= $this->GetTableFooter();
        $this->SetValueString('TVChannellist', $HTMLData);
    }

    /**
     * Erzeugt aus der Liste der Radio Kanäle eine HTML-Tabelle für eine ~HTMLBox-Variable.
     *
     * @access private
     */
    private function RefreshRadioChannellist()
    {
        if (!$this->ReadPropertyBoolean('showRadioChannellist')) {
            return;
        }
        $ScriptID = $this->ReadPropertyInteger('RadioChannellistconfig');
        if ($ScriptID == 0) {
            return;
        }
        if (!IPS_ScriptExists($ScriptID)) {
            return;
        }
        $result = IPS_RunScriptWaitEx($ScriptID, array('SENDER' => 'Kodi'));
        $Config = @unserialize($result);
        if (($Config === false) or (!is_array($Config))) {
            trigger_error('Error on read radio Channelistconfig-Script');
            return;
        }

        $Max = $this->ReadPropertyInteger('showMaxRadioChannels');
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetChannels(array("channelgroupid" => "allradio", "properties" => static::$ChanneltemListFull, 'limits' => array('end' => $Max)));
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return;
        }
        if ($ret->limits->total == 0) {
            return;
        }
        $Channels = $KodiData->ToArray($ret->channels);

        $Data = array_filter($Channels, array($this, "FilterChannels"), ARRAY_FILTER_USE_BOTH);
        $HTMLData = $this->GetTableHeader($Config);
        $pos = 0;

        if (count($Data) > 0) {
            foreach ($Data as $line) {
                $Line = array();
                foreach ($line as $key => $value) {
                    if (is_string($key)) {
                        $Line[ucfirst($key)] = $value;
                    } else {
                        $Line[$key] = $value;
                    } //$key is not a string
                }
                if (array_key_exists('Thumbnail', $Config["Spalten"])) {
                    if ($Line['Thumbnail'] <> "") {
                        $CoverRAW = $this->GetThumbnail($Line['Thumbnail'], $this->ReadPropertyInteger("RadioThumbSize"), 0);
                        if ($CoverRAW === false) {
                            $Line['Thumbnail'] = "";
                        } else {
                            $Line['Thumbnail'] = '<img src="data:image/png;base64,' . base64_encode($CoverRAW) . '" />';
                        }
                    }
                }
                if (array_key_exists('Now', $Config["Spalten"]) and array_key_exists('Broadcastnow', $Line)) {
                    if (array_key_exists("title", $Line['Broadcastnow'])) {
                        $Line['Now'] = $Line['Broadcastnow']['title'];
                        if (array_key_exists("episodename", $Line['Broadcastnow'])) {
                            if ($Line['Broadcastnow']['episodename'] <> "") {
                                $Line['Now'] .= ' - ' . $Line['Broadcastnow']['episodename'];
                            }
                        }
                        if (array_key_exists("progresspercentage", $Line['Broadcastnow'])) {
                            $Line['Now'] .= ' (' . (int) $Line['Broadcastnow']['progresspercentage'] . '%)';
                        }
                    }
                } else {
                    $Line['Now'] = 'No Info';
                }
                if (array_key_exists('Next', $Config["Spalten"]) and array_key_exists('Broadcastnext', $Line)) {
                    if (array_key_exists("title", $Line['Broadcastnext'])) {
                        $Line['Next'] = $Line['Broadcastnext']['title'];
                        if (array_key_exists("episodename", $Line['Broadcastnext'])) {
                            if ($Line['Broadcastnext']['episodename'] <> "") {
                                $Line['Next'] .= ' - ' . $Line['Broadcastnext']['episodename'];
                            }
                        }
                        if (array_key_exists("starttime", $Line['Broadcastnext'])) {
                            $starttime = DateTime::createFromFormat('Y-m-d H:i:s', $Line['Broadcastnext']['starttime'], new DateTimeZone('UTC'));
                            $starttime->setTimezone(new DateTimeZone(date_default_timezone_get()));
                            $Line['Next'] .= ' (' . $starttime->format("H:i") . ')';
                        }
                    }
                } else {
                    $Line['Next'] = 'No Info';
                }

//                $HTMLData .='<tr style="' . $Config['Style']['BR' . ($pos % 2 ? 'U' : 'G')] . '" onclick="window.xhrGet' . $this->InstanceID . '({ url: \'hook/KodiRadioChannellist' . $this->InstanceID . '?ID=' . $Line['Channelid'] . '\' })" >';
                $HTMLData .= '<tr style="' . $Config['Style']['BR' . ($pos % 2 ? 'U' : 'G')] . '" onclick="xhrGet' . $this->InstanceID . '({ url: \'hook/KodiRadioChannellist' . $this->InstanceID . '?TYP=radio&ID=' . $Line['Channelid'] . '\' })" >';

                foreach ($Config['Spalten'] as $feldIndex => $value) {
                    if (!array_key_exists($feldIndex, $Line)) {
                        $Line[$feldIndex] = '';
                    }
                    if ($Line[$feldIndex] === -1) {
                        $Line[$feldIndex] = '';
                    }
                    if (is_array($Line[$feldIndex])) {
                        $Line[$feldIndex] = implode(', ', $Line[$feldIndex]);
                    }
                    $HTMLData .= '<td style="' . $Config['Style']['DF' . ($pos % 2 ? 'U' : 'G') . $feldIndex] . '">' . (string) $Line[$feldIndex] . '</td>';
                }
                $HTMLData .= '</tr>' . PHP_EOL;
                $pos++;
//                if ($pos == $max)
//                    break;
            }
        }
        $HTMLData .= $this->GetTableFooter();
        $this->SetValueString('RadioChannellist', $HTMLData);
    }

    /**
     * Erzeugt aus der Liste der Aufzeichnungen eine HTML-Tabelle für eine ~HTMLBox-Variable.
     *
     * @access private
     */
    private function RefreshRecordinglist()
    {
        if (!$this->ReadPropertyBoolean('showRecordinglist')) {
            return;
        }
        $ScriptID = $this->ReadPropertyInteger('Recordinglistconfig');
        if ($ScriptID == 0) {
            return;
        }
        if (!IPS_ScriptExists($ScriptID)) {
            return;
        }
        $result = IPS_RunScriptWaitEx($ScriptID, array('SENDER' => 'Kodi'));
        $Config = @unserialize($result);
        if (($Config === false) or (!is_array($Config))) {
            trigger_error('Error on read Recordinglistconfig-Script');
            return;
        }

        $Max = $this->ReadPropertyInteger('showMaxRecording');
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetRecordings(array("properties" => static::$RecordingItemList, 'limits' => array('end' => $Max)));
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return;
        }
        if ($ret->limits->total == 0) {
            return;
        }
        $Data = $KodiData->ToArray($ret->recordings);

//        $Data = array_filter($Recordings, array($this, "FilterChannels"), ARRAY_FILTER_USE_BOTH);
        $HTMLData = $this->GetTableHeader($Config);
        $pos = 0;

        if (count($Data) > 0) {
            foreach ($Data as $line) {
                $Line = array();
                foreach ($line as $key => $value) {
                    if (is_string($key)) {
                        $Line[ucfirst($key)] = $value;
                    } else {
                        $Line[$key] = $value;
                    } //$key is not a string
                }
                if (array_key_exists('Thumbnail', $Config["Spalten"])) {
                    if (array_key_exists('thumb', $Line["Art"])) {
                        if ($Line['Art']['thumb'] <> "") {
                            $CoverRAW = $this->GetThumbnail($Line['Art']['thumb'], $this->ReadPropertyInteger("RecordingThumbSize"), 0);
                            if ($CoverRAW === false) {
                                $Line['Thumbnail'] = "";
                            } else {
                                $Line['Thumbnail'] = '<img src="data:image/png;base64,' . base64_encode($CoverRAW) . '" />';
                            }
                        }
                    }
                }
                $Line['Runtime'] = $this->ConvertTime($Line['Runtime']);

                //$HTMLData .='<tr style="' . $Config['Style']['BR' . ($pos % 2 ? 'U' : 'G')] . '" onclick="window.xhrGet' . $this->InstanceID . '({ url: \'hook/KodiRecordinglist' . $this->InstanceID . '?ID=' . $Line['Recordingid'] . '\' })" >';
                $HTMLData .= '<tr style="' . $Config['Style']['BR' . ($pos % 2 ? 'U' : 'G')] . '" onclick="xhrGet' . $this->InstanceID . '({ url: \'hook/KodiRecordinglist' . $this->InstanceID . '?TYP=recording&ID=' . $Line['Recordingid'] . '\' })" >';
                foreach ($Config['Spalten'] as $feldIndex => $value) {
                    if (!array_key_exists($feldIndex, $Line)) {
                        $Line[$feldIndex] = '';
                    }
                    if ($Line[$feldIndex] === -1) {
                        $Line[$feldIndex] = '';
                    }
                    if (is_array($Line[$feldIndex])) {
                        $Line[$feldIndex] = implode(', ', $Line[$feldIndex]);
                    }
                    $HTMLData .= '<td style="' . $Config['Style']['DF' . ($pos % 2 ? 'U' : 'G') . $feldIndex] . '">' . (string) $Line[$feldIndex] . '</td>';
                }
                $HTMLData .= '</tr>' . PHP_EOL;
                $pos++;
//                if ($pos == $max)
//                    break;
            }
        }
        $HTMLData .= $this->GetTableFooter();
        $this->SetValueString('Recordinglist', $HTMLData);
    }

    /**
     * Filter die aktuell versteckten Kanäle aus.
     *
     * @param array $Channel Array mit allen Kanälen.
     * @return boolean True für behalten, False für verwerfen.
     */
    protected function FilterChannels($Channel)
    {
        return !$Channel["hidden"];
    }

    /**
     * Gibt den Inhalt des PHP-Scriptes zurück, welche die Konfiguration und das Design der TV Kanal-Tabelle enthält.
     *
     * @access private
     * @return string Ein PHP-Script welche als Grundlage für die User dient.
     */
    private function CreateTVChannellistConfigScript()
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
# Folgende Parameter bestimmen das Aussehen der HTML-Tabelle in der die TV Kanäle dargestellt werden.

// Reihenfolge und Überschriften der Tabelle. Der vordere Wert darf nicht verändert werden.
// Die Reihenfolge, der hintere Wert (Anzeigetext) und die Reihenfolge sind beliebig änderbar.
$Config["Spalten"] = array(
    "Thumbnail"=>"",
    "Label" =>"Name",
//    "Lastplayed" => "zuletzt gesehen",
    "Now" => "es läuft",
    "Next" => "gleich läuft"
    
);
#### Mögliche Index-Felder
/*
        
| Index       | Typ     | Beschreibung                        |
| :---------: | :-----: | :---------------------------------: |
| Thumbnail   | string  | Senderlogo                          |
| Label       | string  | Name des Kanals                     |
| Lastplayed  | string  | Wann zuletzt gesehen                |
*/
// Breite der Spalten (Reihenfolge ist egal)
$Config["Breite"] = array(
    "Thumbnail"=>"100em",
    "Label"=>"150em",
    "Now"=>"300em",
    "Next"=>"300em",
//    "Lastplayed"=>"300em"
);
// Style Informationen der Tabelle
$Config["Style"] = array(
    // <table>-Tag:
    "T"    => "margin:0 auto; font-size:0.8em;",
    // <thead>-Tag:
    "H"    => "",
    // <tr>-Tag im thead-Bereich:
    "HR"   => "",
    // <th>-Tag Feld Thumbnail:
    "HFThumbnail"  => "color:#ffffff; width:35px; align:left;",
    // <th>-Tag Feld Label:
    "HFLabel"  => "color:#ffffff; width:35px; align:left;",
    // <th>-Tag Feld Now:
    "HFNow"  => "color:#ffffff; width:35px; align:left;",
    // <th>-Tag Feld Next:
    "HFNext"  => "color:#ffffff; width:35px; align:left;",
    // <th>-Tag Feld Lastplayed:
    "HFLastplayed"  => "color:#ffffff; width:35px; align:left;",
    // <tbody>-Tag:
    "B"    => "",
    // <tr>-Tag:
    "BRG"  => "background-color:#000000; color:#ffffff;",
    "BRU"  => "background-color:#080808; color:#ffffff;",
    // <td>-Tag Feld Thumbnail:
    "DFGThumbnail" => "text-align:center;",
    "DFUThumbnail" => "text-align:center;",
    // <td>-Tag Feld Label:
    "DFGLabel" => "text-align:center;",
    "DFULabel" => "text-align:center;",
    // <td>-Tag Feld Now:
    "DFGNow" => "text-align:center;",
    "DFUNow" => "text-align:center;",
    // <td>-Tag Feld Next:
    "DFGNext" => "text-align:center;",
    "DFUNext" => "text-align:center;",
    // <td>-Tag Feld Lastplayed:
    "DFGLastplayed" => "text-align:center;",
    "DFULastplayed" => "text-align:center;",
    // ^- Der Buchstabe "G" steht für gerade, "U" für ungerade.
 );
 
### Konfig ENDE !!!
echo serialize($Config);
';
        return $Script;
    }

    /**
     * Gibt den Inhalt des PHP-Scriptes zurück, welche die Konfiguration und das Design der Radio Kanal-Tabelle enthält.
     *
     * @access private
     * @return string Ein PHP-Script welche als Grundlage für die User dient.
     */
    private function CreateRadioChannellistConfigScript()
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
# Folgende Parameter bestimmen das Aussehen der HTML-Tabelle in der die Radio Kanäle dargestellt werden.

// Reihenfolge und Überschriften der Tabelle. Der vordere Wert darf nicht verändert werden.
// Die Reihenfolge, der hintere Wert (Anzeigetext) und die Reihenfolge sind beliebig änderbar.
$Config["Spalten"] = array(
    "Thumbnail"=>"",
    "Label" =>"Name",
//    "Lastplayed" => "zuletzt gesehen",
    "Now" => "es läuft",
    "Next" => "gleich läuft"
    
);
#### Mögliche Index-Felder
/*
        
| Index       | Typ     | Beschreibung                        |
| :---------: | :-----: | :---------------------------------: |
| Thumbnail   | string  | Senderlogo                          |
| Label       | string  | Name des Kanals                     |
| Lastplayed  | string  | Wann zuletzt gesehen                |
*/
// Breite der Spalten (Reihenfolge ist egal)
$Config["Breite"] = array(
    "Thumbnail"=>"100em",
    "Label"=>"150em",
    "Now"=>"300em",
    "Next"=>"300em",
//    "Lastplayed"=>"300em"
);
// Style Informationen der Tabelle
$Config["Style"] = array(
    // <table>-Tag:
    "T"    => "margin:0 auto; font-size:0.8em;",
    // <thead>-Tag:
    "H"    => "",
    // <tr>-Tag im thead-Bereich:
    "HR"   => "",
    // <th>-Tag Feld Thumbnail:
    "HFThumbnail"  => "color:#ffffff; width:35px; align:left;",
    // <th>-Tag Feld Label:
    "HFLabel"  => "color:#ffffff; width:35px; align:left;",
    // <th>-Tag Feld Now:
    "HFNow"  => "color:#ffffff; width:35px; align:left;",
    // <th>-Tag Feld Next:
    "HFNext"  => "color:#ffffff; width:35px; align:left;",
    // <th>-Tag Feld Lastplayed:
    "HFLastplayed"  => "color:#ffffff; width:35px; align:left;",
    // <tbody>-Tag:
    "B"    => "",
    // <tr>-Tag:
    "BRG"  => "background-color:#000000; color:#ffffff;",
    "BRU"  => "background-color:#080808; color:#ffffff;",
    // <td>-Tag Feld Thumbnail:
    "DFGThumbnail" => "text-align:center;",
    "DFUThumbnail" => "text-align:center;",
    // <td>-Tag Feld Label:
    "DFGLabel" => "text-align:center;",
    "DFULabel" => "text-align:center;",
    // <td>-Tag Feld Now:
    "DFGNow" => "text-align:center;",
    "DFUNow" => "text-align:center;",
    // <td>-Tag Feld Next:
    "DFGNext" => "text-align:center;",
    "DFUNext" => "text-align:center;",
    // <td>-Tag Feld Lastplayed:
    "DFGLastplayed" => "text-align:center;",
    "DFULastplayed" => "text-align:center;",
    // ^- Der Buchstabe "G" steht für gerade, "U" für ungerade.
 );
 
### Konfig ENDE !!!
echo serialize($Config);
';
        return $Script;
    }

    /**
     * Gibt den Inhalt des PHP-Scriptes zurück, welche die Konfiguration und das Design der Radio Kanal-Tabelle enthält.
     *
     * @access private
     * @return string Ein PHP-Script welche als Grundlage für die User dient.
     */
    private function CreateRecordlistConfigScript()
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
# Folgende Parameter bestimmen das Aussehen der HTML-Tabelle in der die Radio Kanäle dargestellt werden.

// Reihenfolge und Überschriften der Tabelle. Der vordere Wert darf nicht verändert werden.
// Die Reihenfolge, der hintere Wert (Anzeigetext) und die Reihenfolge sind beliebig änderbar.
$Config["Spalten"] = array(
    "Thumbnail"=>"",
    "Label" =>"Name",
    "Runtime" => "Dauer",
    "Starttime"=>"Startzeit",
//    "Endtime"=>"Endzeit",
//    "Plot"=>"Handlung",
//    "Genre"=>"Genre",
    "Channel"=>"Kanal"
);
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
$Config["Breite"] = array(
    "Thumbnail"=>"100em",
    "Label"=>"300em",
    "Runtime"=>"100em",
    "Starttime"=>"200em",
    "Channel"=>"150em"
);
// Style Informationen der Tabelle
$Config["Style"] = array(
    // <table>-Tag:
    "T"    => "margin:0 auto; font-size:0.8em;",
    // <thead>-Tag:
    "H"    => "",
    // <tr>-Tag im thead-Bereich:
    "HR"   => "",
    // <th>-Tag Feld Thumbnail:
    "HFThumbnail"  => "color:#ffffff; width:35px; align:left;",
    // <th>-Tag Feld Label:
    "HFLabel"  => "color:#ffffff; width:35px; align:left;",
    // <th>-Tag Feld Runtime:
    "HFRuntime"  => "color:#ffffff; width:35px; align:left;",
    // <th>-Tag Feld Starttime:
    "HFStarttime"  => "color:#ffffff; width:35px; align:left;",
    // <th>-Tag Feld Channel:
    "HFChannel"  => "color:#ffffff; width:35px; align:left;",
    // <tbody>-Tag:
    "B"    => "",
    // <tr>-Tag:
    "BRG"  => "background-color:#000000; color:#ffffff;",
    "BRU"  => "background-color:#080808; color:#ffffff;",
    // <td>-Tag Feld Thumbnail:
    "DFGThumbnail" => "text-align:center;",
    "DFUThumbnail" => "text-align:center;",
    // <td>-Tag Feld Label:
    "DFGLabel" => "text-align:center;",
    "DFULabel" => "text-align:center;",
    // <td>-Tag Feld Runtime:
    "DFGRuntime" => "text-align:center;",
    "DFURuntime" => "text-align:center;",
    // <td>-Tag Feld Starttime:
    "DFGStarttime" => "text-align:center;",
    "DFUStarttime" => "text-align:center;",
    // <td>-Tag Feld Channel:
    "DFGChannel" => "text-align:center;",
    "DFUChannel" => "text-align:center;",
    // ^- Der Buchstabe "G" steht für gerade, "U" für ungerade.
 );
 
### Konfig ENDE !!!
echo serialize($Config);
';
        return $Script;
    }

    /*
      ["channel"]=>
      string(9) "ProSieben"
      ["endtime"]=>
      string(19) "2015-04-15 23:04:00"
      ["genre"]=>
      array(2) {
      [0]=>
      string(6) "Andere"
      [1]=>
      string(9) "Unbekannt"
      }
      ["plot"]=>
      string(857) "Tödliche Frequenz, Action, USA 2014Dr. Wells wird von einem Unbekannten angegriffen. Es stellt sich heraus, dass es sich um Hartley Rathaway, einen ehemaligen Mitarbeiter von S.T.A.R.- Labs, handelt, der mit Wells noch eine Rechnung offen hat. Barry kann Hartley im Kampf zwar besiegen, doch der junge Mann kann sich kurze Zeit später aus seinem provisorischen Gefängnis befreien und wichtige Daten aus dem Zentralrechner von S.T.A.R.-Labs zu stehlen ...Regie: John F. ShowalterDrehbuch: Alison Schapker, Brooke EikmeierKamera: C. Kim MilesSchnitt: Paul KarasickDarsteller:Grant Gustin (Barry Allen/The Flash)Candice Patton (Iris West)Danielle Panabaker (Caitlin Snow)Rick Cosnett (Eddie Thawne)Carlos Valdes (Cisco Ramon)Tom Cavanagh (Dr. Harrison Wells)Jesse L. Martin (Detective Joe West)Andy Mientus (Hartley Rathaway/Pied Piper)Tom Butler (Eric Larkin)"
      ["recordingid"]=>
      int(1363)
      ["runtime"]=>
      int(3720)
      ["starttime"]=>
      string(19) "2015-04-15 22:02:00"
      ["title"]=>
      string(9) "The Flash"
     */
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
        switch ($Ident) {
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
     * Verarbeitet Daten aus dem Webhook.
     *
     * @access protected
     * @global array $_GET
     */
    public function ProcessHookdata()//($Typ, $HookData)
    {
        if (!isset($_GET["ID"])) {
            return;
        }
        if (!isset($_GET["TYP"])) {
            return;
        }
        $this->SendDebug($_GET["TYP"] . ' HOOK', $_GET["ID"], 0);
        $KodiData = new Kodi_RPC_Data('Player');
        switch ($_GET["TYP"]) {
            case "tv":
            case "radio":
                $KodiData->Open(array("item" => array('channelid' => (int) $_GET["ID"])));
                break;
            case "recording":
                $KodiData->Open(array("item" => array('recordingid' => (int) $_GET["ID"])));
                break;
        }
        $ret = $this->Send($KodiData);
        echo $ret;
    }

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
        if (is_null($ret)) {
            return false;
        }
        if ($ret === "OK") {
            return true;
        }
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
        if (is_null($ret)) {
            return false;
        }
        if ($ret === "OK") {
            return true;
        }
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
        if (!in_array($ChannelTyp, array("radio", "tv"))) {
            trigger_error("ChannelTyp must 'tv' or 'radio'.", E_USER_NOTICE);
            return false;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetChannels(array("channelgroupid" => "all" . $ChannelTyp, "properties" => static::$ChanneltemList));
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->channels);
        }
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
        if (!is_int($ChannelId)) {
            trigger_error("ChannelId must be integer.", E_USER_NOTICE);
            return false;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetChannelDetails(array("channelid" => $ChannelId, "properties" => static::$ChanneltemListFull));
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $KodiData->ToArray($ret->channeldetails);
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
        if (!in_array($ChannelTyp, array("radio", "tv"))) {
            trigger_error("ChannelTyp must 'tv' or 'radio'.", E_USER_NOTICE);
            return false;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetChannelGroups(array("channeltype" => "all" . $ChannelTyp)); //, "properties" => static::$ItemList));
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->channelgroups);
        }
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
        if (!is_int($ChannelGroupdId)) {
            trigger_error("ChannelId must be integer.", E_USER_NOTICE);
            return false;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetChannelGroupDetails(array("channelgroupid" => $ChannelGroupdId, "properties" => static::$ChanneltemList));
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->channelgroupdetails);
        }
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
        if (!is_int($ChannelId)) {
            trigger_error("ChannelId must be integer.", E_USER_NOTICE);
            return false;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetBroadcasts(array("channelid" => $ChannelId, "properties" => static::$BroadcastItemList));
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->broadcasts);
        }
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
        if (!is_int($BroadcastId)) {
            trigger_error("BroadcastId must be integer.", E_USER_NOTICE);
            return false;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetBroadcastDetails(array("broadcastid" => $BroadcastId)); //, "properties" => static::$BroadcastItemList));
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $KodiData->ToArray($ret->broadcastdetails);
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
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->recordings);
        }
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
        if (!is_int($RecordingId)) {
            trigger_error("RecordingId must be integer.", E_USER_NOTICE);
            return false;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetRecordingDetails(array("recordingid" => $RecordingId, "properties" => static::$RecordingItemList));
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $KodiData->ToArray($ret->recordingdetails);
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
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->timers);
        }
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
        if (!is_int($TimerId)) {
            trigger_error("TimerId must be integer.", E_USER_NOTICE);
            return false;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetTimerDetails(array("timerid" => $TimerId, "properties" => static::$TimerItemList));
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $KodiData->ToArray($ret->timerdetails);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPVR_RefreshAll'. Erzeugt alle HTML-Tabellen neu
     *
     * @access public
     */
    public function RefreshAll()
    {
        $this->RefreshTVChannellist();
        $this->RefreshRadioChannellist();
        $this->RefreshRecordinglist();
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
