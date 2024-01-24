<?php

declare(strict_types=1);

/*
 * @addtogroup kodi
 * @{
 *
 * @package       Kodi
 * @file          module.php
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2020 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       3.00
 *
 */
require_once __DIR__ . '/../libs/KodiClass.php';  // diverse Klassen

/**
 * KodiDevicePVR Klasse für den Namespace PVR der KODI-API.
 * Erweitert KodiBase.
 *
 * @package       Kodi
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2020 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       3.00
 * @example <b>Ohne</b>
 *
 * @todo PVR.AddTimer ab v8
 * @todo PVR.ToggleTimer ab v8
 * @todo PVR.GetBroadcastIsPlayable ab v10
 * @todo PVR.PVR.GetClients ab v10
 *
 * @property string $WebHookSecretTv
 * @property string $WebHookSecretRadio
 * @property string $WebHookSecretRecording
 *
 * @property string $Namespace RPC-Namespace
 * @property array $Properties Alle Properties des RPC-Namespace
 * @property array $ChannelItemList Alle Eigenschaften von Kanal-Items
 * @property array $ChannelItemListFull Alle Eigenschaften von Kanal-Items
 * @property array $BroadcastItemList Alle Eigenschaften von Sendung-Items
 * @property array $RecordingItemList Alle Eigenschaften von Aufnahmen
 * @property array $TimerItemList Alle Eigenschaften von Timer
 */
class KodiDevicePVR extends KodiBase
{
    public const PropertyShowIsAvailable = 'showIsAvailable';
    public const PropertyShowIsRecording = 'showIsRecording';
    public const PropertyShowDoRecording = 'showDoRecording';
    public const PropertyShowIsScanning = 'showIsScanning';
    public const PropertyShowDoScanning = 'showDoScanning';

    public const PropertyShowTVChannellist = 'showTVChannellist';
    public const PropertyShowMaxTVChannels = 'showMaxTVChannels';
    public const PropertyTVThumbSize = 'TVThumbSize';

    public const PropertyShowRadioChannellist = 'showRadioChannellist';
    public const PropertyShowMaxRadioChannels = 'showMaxRadioChannels';
    public const PropertyRadioThumbSize = 'RadioThumbSize';

    public const PropertyShowRecordinglist = 'showRecordinglist';
    public const PropertyShowMaxRecording = 'showMaxRecording';
    public const PropertyRecordingThumbSize = 'RecordingThumbSize';

    public const ActionVisibleFormElementsTVChannellist = 'showTVChannellist';
    public const ActionVisibleFormElementsRadioChannellist = 'showRadioChannellist';
    public const ActionVisibleFormElementsRecordinglist = 'showRecordinglist';

    public const HookTV = '/hook/KodiTVChannellist';
    public const HookRadio = '/hook/KodiRadioChannellist';
    public const HookRecording = '/hook/KodiRecordinglist';

    protected static $Namespace = 'PVR';
    protected static $Properties = [
        'available',
        'recording',
        'scanning'
    ];
    protected static $ChannelItemList = [
        'thumbnail',
        'channeltype',
        'hidden',
        'locked',
        'channel',
        'lastplayed'
    ];
    protected static $ChannelItemListFull = [
        'thumbnail',
        'channeltype',
        'hidden',
        'locked',
        'channel',
        'lastplayed',
        'broadcastnow',
        'broadcastnext'
    ];
    protected static $BroadcastItemList = [
        'title',
        'plot',
        'plotoutline',
        'starttime',
        'endtime',
        'runtime',
        'progress',
        'progresspercentage',
        'genre',
        'episodename',
        'episodenum',
        'episodepart',
        'firstaired',
        'hastimer',
        'isactive',
        'parentalrating',
        'wasactive',
        'thumbnail',
        'rating'
    ];
    protected static $RecordingItemList = [
        'title',
        'plot',
        'plotoutline',
        'genre',
        'playcount',
        'resume',
        'channel',
        'starttime',
        'endtime',
        'runtime',
        'lifetime',
        'icon',
        'art',
        'streamurl',
        'file',
        'directory'
    ];
    protected static $TimerItemList = [
        'title',
        'summary',
        'channelid',
        'isradio',
        'repeating',
        'starttime',
        'endtime',
        'runtime',
        'lifetime',
        'firstday',
        'weekdays',
        'priority',
        'startmargin',
        'endmargin',
        'state',
        'file',
        'directory'
    ];

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Create(): void
    {
        parent::Create();
        $this->WebHookSecretTv = '';
        $this->WebHookSecretRadio = '';
        $this->WebHookSecretRecording = '';
        $this->RegisterPropertyBoolean(self::PropertyShowIsAvailable, true);
        $this->RegisterPropertyBoolean(self::PropertyShowIsRecording, true);
        $this->RegisterPropertyBoolean(self::PropertyShowDoRecording, true);
        $this->RegisterPropertyBoolean(self::PropertyShowIsScanning, true);
        $this->RegisterPropertyBoolean(self::PropertyShowDoScanning, true);
        $this->RegisterPropertyBoolean(self::PropertyShowTVChannellist, true);
        $this->RegisterPropertyInteger(self::PropertyShowMaxTVChannels, 20);
        $this->RegisterPropertyInteger(self::PropertyTVThumbSize, 100);
        $this->RegisterPropertyBoolean(self::PropertyShowRadioChannellist, true);
        $this->RegisterPropertyInteger(self::PropertyShowMaxRadioChannels, 20);
        $this->RegisterPropertyInteger(self::PropertyRadioThumbSize, 100);
        $this->RegisterPropertyBoolean(self::PropertyShowRecordinglist, true);
        $this->RegisterPropertyInteger(self::PropertyShowMaxRecording, 20);
        $this->RegisterPropertyInteger(self::PropertyRecordingThumbSize, 100);
        $this->RegisterTimer('RefreshLists', 0, 'KODIPVR_RefreshAll(' . $this->InstanceID . ');');

        // Todo 7.0 -> Style per Konfig-Formular
        $ID = @$this->GetIDForIdent('TVChannellistDesign');
        if ($ID == false) {
            $ID = $this->RegisterScript('TVChannellistDesign', 'TV Channellist Config', $this->CreateTVChannellistConfigScript(), -7);
            IPS_SetHidden($ID, true);
        }
        $this->RegisterPropertyInteger('TVChannellistconfig', $ID);

        $ID = @$this->GetIDForIdent('RadioChannellistDesign');
        if ($ID == false) {
            $ID = $this->RegisterScript('RadioChannellistDesign', 'Radio Channellist Config', $this->CreateRadioChannellistConfigScript(), -7);
            IPS_SetHidden($ID, true);
        }
        $this->RegisterPropertyInteger('RadioChannellistconfig', $ID);

        $ID = @$this->GetIDForIdent('RecordinglistDesign');
        if ($ID == false) {
            $ID = $this->RegisterScript('RecordinglistDesign', 'Recordinglist Config', $this->CreateRecordlistConfigScript(), -7);
            IPS_SetHidden($ID, true);
        }
        $this->RegisterPropertyInteger('Recordinglistconfig', $ID);
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Destroy(): void
    {
        if (IPS_GetKernelRunlevel() != KR_READY) {
            parent::Destroy();
            return;
        }
        if (!IPS_InstanceExists($this->InstanceID)) {
            $this->UnregisterHook(self::HookTV . $this->InstanceID);
            $this->UnregisterHook(self::HookRadio . $this->InstanceID);
            $this->UnregisterHook(self::HookRecording . $this->InstanceID);
        }

        parent::Destroy();
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function ApplyChanges(): void
    {
        $this->RegisterProfileIntegerEx('Action.Kodi', '', '', '', [
            [0, $this->Translate('Execute'), '', -1]
        ]);
        if ($this->ReadPropertyBoolean(self::PropertyShowIsAvailable)) {
            $this->RegisterVariableBoolean('available', $this->Translate('Available'), '', 1);
        } else {
            $this->UnregisterVariable('available');
        }

        if ($this->ReadPropertyBoolean(self::PropertyShowIsRecording)) {
            $this->RegisterVariableBoolean('recording', $this->Translate('Recording is in progress'), '', 3);
        } else {
            $this->UnregisterVariable('recording');
        }

        if ($this->ReadPropertyBoolean(self::PropertyShowDoRecording)) {
            $this->RegisterVariableBoolean('record', $this->Translate('Recording current channel'), '~Switch', 4);
            $this->EnableAction('record');
        } else {
            $this->UnregisterVariable('record');
        }

        if ($this->ReadPropertyBoolean(self::PropertyShowIsScanning)) {
            $this->RegisterVariableBoolean('scanning', $this->Translate('Channel search active'), '', 5);
        } else {
            $this->UnregisterVariable('scanning');
        }

        if ($this->ReadPropertyBoolean(self::PropertyShowDoScanning)) {
            $this->RegisterVariableInteger('scan', $this->Translate('Start channel search'), 'Action.Kodi', 6);
            $this->EnableAction('scan');
        } else {
            $this->UnregisterVariable('scan');
        }

        $this->UnregisterScript('WebHookTVChannellist');
        $this->UnregisterScript('WebHookRadioChannellist');
        $this->UnregisterScript('WebHookRecordinglist');

        if ($this->ReadPropertyBoolean(self::PropertyShowTVChannellist)) {
            $this->RegisterVariableString('TVChannellist', $this->Translate('TV channels'), '~HTMLBox', 1);
            if (IPS_GetKernelRunlevel() == KR_READY) {
                $this->RegisterHook(self::HookTV . $this->InstanceID);
            }

            $ID = @$this->GetIDForIdent('TVChannellistDesign');
            if ($ID == false) {
                $ID = $this->RegisterScript('TVChannellistDesign', 'TVChannellist Config', $this->CreateTVChannellistConfigScript(), -7);
                IPS_SetHidden($ID, true);
            }
        } else {
            $this->UnregisterVariable('TVChannellist');
            if (IPS_GetKernelRunlevel() == KR_READY) {
                $this->UnregisterHook(self::HookTV . $this->InstanceID);
            }
        }

        if ($this->ReadPropertyBoolean(self::PropertyShowRadioChannellist)) {
            $this->RegisterVariableString('RadioChannellist', $this->Translate('Radio channels'), '~HTMLBox', 1);
            if (IPS_GetKernelRunlevel() == KR_READY) {
                $this->RegisterHook(self::HookRadio . $this->InstanceID);
            }

            $ID = @$this->GetIDForIdent('RadioChannellistDesign');
            if ($ID == false) {
                $ID = $this->RegisterScript('RadioChannellistDesign', 'RadioChannellist Config', $this->CreateRadioChannellistConfigScript(), -7);
                IPS_SetHidden($ID, true);
            }
        } else {
            $this->UnregisterVariable('RadioChannellist');
            if (IPS_GetKernelRunlevel() == KR_READY) {
                $this->UnregisterHook(self::HookRadio . $this->InstanceID);
            }
        }

        if ($this->ReadPropertyBoolean(self::PropertyShowRecordinglist)) {
            $this->RegisterVariableString('Recordinglist', $this->Translate('Recordings'), '~HTMLBox', 1);
            if (IPS_GetKernelRunlevel() == KR_READY) {
                $this->RegisterHook(self::HookRecording . $this->InstanceID);
            }

            $ID = @$this->GetIDForIdent('RecordinglistDesign');
            if ($ID == false) {
                $ID = $this->RegisterScript('RecordinglistDesign', 'Recordinglist Config', $this->CreateRecordlistConfigScript(), -7);
                IPS_SetHidden($ID, true);
            }
        } else {
            $this->UnregisterVariable('Recordinglist');
            if (IPS_GetKernelRunlevel() == KR_READY) {
                $this->UnregisterHook(self::HookRecording . $this->InstanceID);
            }
        }

        if ($this->ReadPropertyBoolean(self::PropertyShowRecordinglist) || $this->ReadPropertyBoolean(self::PropertyShowRadioChannellist) || $this->ReadPropertyBoolean(self::PropertyShowTVChannellist)) {
            if ($this->HasActiveParent()) {
                $this->SetTimerInterval('RefreshLists', 15 * 60 * 1000);
            } else {
                $this->SetTimerInterval('RefreshLists', 0);
            }
        } else {
            $this->SetTimerInterval('RefreshLists', 0);
        }
        $ScriptID = $this->ReadPropertyInteger('TVChannellistconfig');
        if ($ScriptID > 0) {
            $this->RegisterReference($ScriptID);
        }
        $ScriptID = $this->ReadPropertyInteger('RadioChannellistconfig');
        if ($ScriptID > 0) {
            $this->RegisterReference($ScriptID);
        }
        $ScriptID = $this->ReadPropertyInteger('Recordinglistconfig');
        if ($ScriptID > 0) {
            $this->RegisterReference($ScriptID);
        }

        parent::ApplyChanges();
    }
    public function GetConfigurationForm(): string
    {
        $Form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        $Form['elements'][1]['items'][1]['visible'] = $this->ReadPropertyBoolean(self::PropertyShowTVChannellist);
        $Form['elements'][1]['items'][2]['visible'] = $this->ReadPropertyBoolean(self::PropertyShowTVChannellist);
        $Form['elements'][1]['items'][3]['visible'] = $this->ReadPropertyBoolean(self::PropertyShowTVChannellist);
        $Form['elements'][2]['items'][1]['visible'] = $this->ReadPropertyBoolean(self::PropertyShowRadioChannellist);
        $Form['elements'][2]['items'][2]['visible'] = $this->ReadPropertyBoolean(self::PropertyShowRadioChannellist);
        $Form['elements'][2]['items'][3]['visible'] = $this->ReadPropertyBoolean(self::PropertyShowRadioChannellist);
        $Form['elements'][3]['items'][1]['visible'] = $this->ReadPropertyBoolean(self::PropertyShowRecordinglist);
        $Form['elements'][3]['items'][2]['visible'] = $this->ReadPropertyBoolean(self::PropertyShowRecordinglist);
        $Form['elements'][3]['items'][3]['visible'] = $this->ReadPropertyBoolean(self::PropertyShowRecordinglist);
        $this->SendDebug('FORM', json_encode($Form), 0);
        $this->SendDebug('FORM', json_last_error_msg(), 0);
        return json_encode($Form);
    }
    ################## ActionHandler
    /**
     * Actionhandler der Statusvariablen. Interne SDK-Funktion.
     *
     * @access public
     * @param string $Ident Der Ident der Statusvariable.
     * @param bool|float|int|string $Value Der angeforderte neue Wert.
     */
    public function RequestAction(string $Ident, mixed $Value, bool &$done = false): void
    {
        parent::RequestAction($Ident, $Value, $done);
        if ($done) {
            return;
        }
        switch ($Ident) {
            case 'scan':
                $this->Scan();
                return;
            case 'record':
                $this->Record($Value, 'current');
                return;
            case self::ActionVisibleFormElementsTVChannellist:
                $this->UpdateFormField('TVRow1', 'visible', (bool) $Value);
                $this->UpdateFormField('TVRow2', 'visible', (bool) $Value);
                $this->UpdateFormField('TVRow3', 'visible', (bool) $Value);
                return;
            case self::ActionVisibleFormElementsRadioChannellist:
                $this->UpdateFormField('RadioRow1', 'visible', (bool) $Value);
                $this->UpdateFormField('RadioRow2', 'visible', (bool) $Value);
                $this->UpdateFormField('RadioRow3', 'visible', (bool) $Value);
                return;
            case self::ActionVisibleFormElementsRecordinglist:
                $this->UpdateFormField('RecordingRow1', 'visible', (bool) $Value);
                $this->UpdateFormField('RecordingRow2', 'visible', (bool) $Value);
                $this->UpdateFormField('RecordingRow3', 'visible', (bool) $Value);
                return;
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
    public function ProcessHookdata(): void
    {
        if ((!isset($_GET['ID'])) || (!isset($_GET['TYP'])) || (!isset($_GET['Secret']))) {
            echo $this->Translate('Bad Request');
            return;
        }
        $CalcSecret = base64_encode(sha1($this->{'WebHookSecret' . ucfirst($_GET['TYP'])} . '0' . $_GET['ID'], true));
        if ($CalcSecret != rawurldecode($_GET['Secret'])) {
            echo $this->Translate('Access denied');
            return;
        }
        $this->SendDebug($_GET['TYP'] . ' HOOK', $_GET['ID'], 0);
        $KodiData = new Kodi_RPC_Data('Player');
        switch ($_GET['TYP']) {
            case 'tv':
            case 'radio':
                $KodiData->Open(['item' => ['channelid' => (int) $_GET['ID']]]);
                break;
            case 'recording':
                $KodiData->Open(['item' => ['recordingid' => (int) $_GET['ID']]]);
                break;
            default:
                echo $this->Translate('Bad Request');
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
    public function Scan(): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Scan();
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret === 'OK') {
            return true;
        }
        trigger_error($this->Translate('Error start scan.'), E_USER_NOTICE);
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
    public function Record(bool $Record, string $Channel): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Record(['record' => $Record, 'channel' => $Channel]);
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret === 'OK') {
            return true;
        }
        trigger_error($this->Translate('Error start recording.'), E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPVR_GetChannels'. Liest die Kanalliste
     *
     * @access public
     * @param string $ChannelTyp [enum 'tv', 'radio'] Kanaltyp welcher gelesen werden soll.
     * @return array|bool Ein Array mit den Daten oder FALSE bei Fehler.
     */
    public function GetChannels(string $ChannelTyp): false|array
    {
        if (!in_array($ChannelTyp, ['radio', 'tv'])) {
            trigger_error($this->Translate('ChannelTyp must be "tv" or "radio".'), E_USER_NOTICE);
            return false;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetChannels(['channelgroupid' => 'all' . $ChannelTyp, 'properties' => static::$ChannelItemList]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->channels);
        }
        return [];
    }

    /**
     * IPS-Instanz-Funktion 'KODIPVR_GetChannelDetails'. Liefert die Eigenschaften eines Kanals.
     *
     * @access public
     * @param int $ChannelId Kanal welcher gelesen werden soll.
     * @return array|bool Ein Array mit den Daten oder FALSE bei Fehler.
     */
    public function GetChannelDetails(int $ChannelId): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetChannelDetails(['channelid' => $ChannelId, 'properties' => static::$ChannelItemListFull]);
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
     * @param string $ChannelTyp [enum 'tv', 'radio'] Kanaltyp welcher gelesen werden soll.
     * @return array|bool Ein Array mit den Daten oder FALSE bei Fehler.
     */
    public function GetChannelGroups(string $ChannelTyp): false|array
    {
        if (!in_array($ChannelTyp, ['radio', 'tv'])) {
            trigger_error($this->Translate('ChannelTyp must be "tv" or "radio".'), E_USER_NOTICE);
            return false;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetChannelGroups(['channeltype' => 'all' . $ChannelTyp]); //, 'properties' => static::$ItemList));
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->channelgroups);
        }
        return [];
    }

    /**
     * IPS-Instanz-Funktion 'KODIPVR_GetChannelGroupDetails'. Liefert die Eigenschaften einer Kanalgruppe.
     *
     * @access public
     * @param int $ChannelGroupId Kanal welcher gelesen werden soll.
     * @return array|bool Ein Array mit den Daten oder FALSE bei Fehler.
     */
    public function GetChannelGroupDetails(int $ChannelGroupId): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetChannelGroupDetails(['channelgroupid' => $ChannelGroupId, 'properties' => static::$ChannelItemList]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->channelgroupdetails);
        }
        return [];
    }

    /**
     * IPS-Instanz-Funktion 'KODIPVR_GetBroadcasts'. Liest die Sendungen eines Senders.
     *
     * @access public
     * @param string $ChannelId  Kanal welcher gelesen werden soll.
     * @return array|bool Ein Array mit den Daten oder FALSE bei Fehler.
     */
    public function GetBroadcasts(int $ChannelId): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetBroadcasts(['channelid' => $ChannelId, 'properties' => static::$BroadcastItemList]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->broadcasts);
        }
        return [];
    }

    /**
     * IPS-Instanz-Funktion 'KODIPVR_GetBroadcastDetails'. Liefert die Eigenschaften einer Sendung.
     *
     * @access public
     * @param int $BroadcastId Sendung welche gelesen werden soll.
     * @return array|bool Ein Array mit den Daten oder FALSE bei Fehler.
     */
    public function GetBroadcastDetails(int $BroadcastId): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetBroadcastDetails(['broadcastid' => $BroadcastId]); //, 'properties' => static::$BroadcastItemList));
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
    public function GetRecordings(): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetRecordings(['properties' => static::$RecordingItemList]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->recordings);
        }
        return [];
    }

    /**
     * IPS-Instanz-Funktion 'KODIPVR_GetRecordingDetails'. Liefert die Eigenschaften einer Aufnahme.
     *
     * @access public
     * @param int $RecordingId Aufnahme welche gelesen werden soll.
     * @return array|bool Ein Array mit den Daten oder FALSE bei Fehler.
     */
    public function GetRecordingDetails(int $RecordingId): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetRecordingDetails(['recordingid' => $RecordingId, 'properties' => static::$RecordingItemList]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $KodiData->ToArray($ret->recordingdetails);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPVR_GetTimers'. Liefert alle AufnahmeTimer.
     *
     * @access public
     * @return array|bool Ein Array mit den Daten oder FALSE bei Fehler.
     */
    public function GetTimers(): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetTimers(['properties' => static::$TimerItemList]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->timers);
        }
        return [];
    }

    /**
     * IPS-Instanz-Funktion 'KODIPVR_GetTimerDetails'. Liefert die Eigenschaften einer AufnahmeTimers.
     *
     * @access public
     * @param int $TimerId Timers welcher gelesen werden soll.
     * @return array|bool Ein Array mit den Daten oder FALSE bei Fehler.
     */
    public function GetTimerDetails(int $TimerId): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetTimerDetails(['timerid' => $TimerId, 'properties' => static::$TimerItemList]);
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
    public function RefreshAll(): void
    {
        $this->RefreshTVChannellist();
        $this->RefreshRadioChannellist();
        $this->RefreshRecordinglist();
    }

    /**
     * IPS-Instanz-Funktion 'KODIPVR_RequestState'. Frage eine oder mehrere Properties eines Namespace ab.
     *
     * @access public
     * @param string $Ident Enthält den Names des 'properties' welches angefordert werden soll.
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function RequestState(string $Ident): void
    {
        parent::RequestState($Ident);
    }

    /**
     * Wird ausgeführt wenn sich der Status vom Parent ändert.
     * @access protected
     */
    protected function IOChangeState(int $State): void
    {
        $this->SetTimerInterval('RefreshLists', 0);
        if ($State == IS_ACTIVE) {
            if (!$this->PVRAvaiable()) {
                $State = IS_EBASE + 1;
            }
        }
        $this->SetStatus($State);
        parent::IOChangeState($State);
        if ($State == IS_ACTIVE) {
            $this->RefreshTVChannellist();
            $this->RefreshRadioChannellist();
            $this->RefreshRecordinglist();
            if ($this->ReadPropertyBoolean(self::PropertyShowRecordinglist) || $this->ReadPropertyBoolean(self::PropertyShowRadioChannellist) || $this->ReadPropertyBoolean(self::PropertyShowTVChannellist)) {
                $this->SetTimerInterval('RefreshLists', 15 * 60 * 1000);
            }
        }
    }
    ################## PRIVATE
    /**
     * Dekodiert die empfangenen Events und Antworten auf 'GetProperties'.
     *
     * @param string $Method RPC-Funktion ohne Namespace
     * @param object $KodiPayload Der zu dekodierende Datensatz als Objekt.
     */
    protected function Decode(string $Method, mixed $KodiPayload): void
    {
        switch ($Method) {
            case 'GetProperties':
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
     * Filter die aktuell versteckten Kanäle aus.
     *
     * @param array $Channel Array mit allen Kanälen.
     * @return boolean True für behalten, False für verwerfen.
     */
    protected function FilterChannels($Channel): bool
    {
        return !$Channel['hidden'];
    }
    private function PVRAvaiable(): bool
    {
        $KodiData = new Kodi_RPC_Data('Addons');
        $KodiData->GetAddons(['type'=>'kodi.pvrclient', 'properties' => ['enabled']]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }

        if ($ret->limits->total > 0) {
            foreach ($KodiData->ToArray($ret->addons) as $Addon) {
                if ((bool) $Addon['enabled']) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Erzeugt aus der Liste der TV-Kanäle eine HTML-Tabelle für eine ~HTMLBox-Variable.
     *
     * @access private
     */
    private function RefreshTVChannellist(): void
    {
        if (!$this->ReadPropertyBoolean(self::PropertyShowTVChannellist)) {
            return;
        }
        $ScriptID = $this->ReadPropertyInteger('TVChannellistconfig');
        if ($ScriptID == 0) {
            return;
        }
        if (!IPS_ScriptExists($ScriptID)) {
            return;
        }
        $result = IPS_RunScriptWaitEx($ScriptID, ['SENDER' => 'Kodi']);
        $Config = @unserialize($result);
        if (($Config === false) || (!is_array($Config))) {
            trigger_error($this->Translate('Error on read TV Channellistconfig-Script'));
            return;
        }

        $Max = $this->ReadPropertyInteger(self::PropertyShowMaxTVChannels);
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetChannels(['channelgroupid' => 'alltv', 'properties' => static::$ChannelItemListFull, 'limits' => ['end' => $Max]]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return;
        }
        if ($ret->limits->total == 0) {
            return;
        }
        $Channels = $KodiData->ToArray($ret->channels);

        $Data = array_filter($Channels, [$this, 'FilterChannels'], ARRAY_FILTER_USE_BOTH);

        $NewSecretTV = base64_encode(openssl_random_pseudo_bytes(12));
        $this->WebHookSecretTv = $NewSecretTV;

        $HTMLData = $this->GetTableHeader($Config);
        $pos = 0;
        if (count($Data) > 0) {
            foreach ($Data as $line) {
                $Line = [];
                foreach ($line as $key => $value) {
                    if (is_string($key)) {
                        $Line[ucfirst($key)] = $value;
                    } else {
                        $Line[$key] = $value;
                    } //$key is not a string
                }
                if (array_key_exists('Thumbnail', $Config['Spalten'])) {
                    if ($Line['Thumbnail'] != '') {
                        $CoverRAW = $this->GetThumbnail($Line['Thumbnail'], $this->ReadPropertyInteger(self::PropertyTVThumbSize), 0);
                        if ($CoverRAW === false) {
                            $Line['Thumbnail'] = '';
                        } else {
                            $Line['Thumbnail'] = '<img src="data:image/png;base64,' . base64_encode($CoverRAW) . '" />';
                        }
                    }
                }
                if (array_key_exists('Now', $Config['Spalten']) && array_key_exists('Broadcastnow', $Line)) {
                    if (array_key_exists('title', $Line['Broadcastnow'])) {
                        $Line['Now'] = $Line['Broadcastnow']['title'];
                        if (array_key_exists('episodename', $Line['Broadcastnow'])) {
                            if ($Line['Broadcastnow']['episodename'] != '') {
                                $Line['Now'] .= ' - ' . $Line['Broadcastnow']['episodename'];
                            }
                        }
                        if (array_key_exists('progresspercentage', $Line['Broadcastnow'])) {
                            $Line['Now'] .= ' (' . (int) $Line['Broadcastnow']['progresspercentage'] . '%)';
                        }
                    }
                } else {
                    $Line['Now'] = 'No Info';
                }
                if (array_key_exists('Next', $Config['Spalten']) && array_key_exists('Broadcastnext', $Line)) {
                    if (array_key_exists('title', $Line['Broadcastnext'])) {
                        $Line['Next'] = $Line['Broadcastnext']['title'];
                        if (array_key_exists('episodename', $Line['Broadcastnext'])) {
                            if ($Line['Broadcastnext']['episodename'] != '') {
                                $Line['Next'] .= ' - ' . $Line['Broadcastnext']['episodename'];
                            }
                        }
                        if (array_key_exists('starttime', $Line['Broadcastnext'])) {
                            $starttime = DateTime::createFromFormat('Y-m-d H:i:s', $Line['Broadcastnext']['starttime'], new DateTimeZone('UTC'));
                            $starttime->setTimezone(new DateTimeZone(date_default_timezone_get()));
                            $Line['Next'] .= ' (' . $starttime->format('H:i') . ')';
                        }
                    }
                } else {
                    $Line['Next'] = 'No Info';
                }
                $LineSecret = rawurlencode(base64_encode(sha1($NewSecretTV . '0' . $Line['Channelid'], true)));
                $HTMLData .= '<tr style="' . $Config['Style']['BR' . ($pos % 2 ? 'U' : 'G')] . '" onclick="xhrGet' . $this->InstanceID . '({ url: \'hook/KodiTVChannellist' . $this->InstanceID . '?TYP=tv&ID=' . $Line['Channelid'] . '&Secret=' . $LineSecret . '\' })" >';

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
    private function RefreshRadioChannellist(): void
    {
        if (!$this->ReadPropertyBoolean(self::PropertyShowRadioChannellist)) {
            return;
        }
        $ScriptID = $this->ReadPropertyInteger('RadioChannellistconfig');
        if ($ScriptID == 0) {
            return;
        }
        if (!IPS_ScriptExists($ScriptID)) {
            return;
        }
        $result = IPS_RunScriptWaitEx($ScriptID, ['SENDER' => 'Kodi']);
        $Config = @unserialize($result);
        if (($Config === false) || (!is_array($Config))) {
            trigger_error($this->Translate('Error on read radio Channellistconfig-Script'));
            return;
        }

        $Max = $this->ReadPropertyInteger(self::PropertyShowMaxRadioChannels);
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetChannels(['channelgroupid' => 'allradio', 'properties' => static::$ChannelItemListFull, 'limits' => ['end' => $Max]]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return;
        }
        if ($ret->limits->total == 0) {
            return;
        }
        $Channels = $KodiData->ToArray($ret->channels);

        $Data = array_filter($Channels, [$this, 'FilterChannels'], ARRAY_FILTER_USE_BOTH);

        $NewSecretRadio = base64_encode(openssl_random_pseudo_bytes(12));
        $this->WebHookSecretRadio = $NewSecretRadio;

        $HTMLData = $this->GetTableHeader($Config);
        $pos = 0;

        if (count($Data) > 0) {
            foreach ($Data as $line) {
                $Line = [];
                foreach ($line as $key => $value) {
                    if (is_string($key)) {
                        $Line[ucfirst($key)] = $value;
                    } else {
                        $Line[$key] = $value;
                    } //$key is not a string
                }
                if (array_key_exists('Thumbnail', $Config['Spalten'])) {
                    if ($Line['Thumbnail'] != '') {
                        $CoverRAW = $this->GetThumbnail($Line['Thumbnail'], $this->ReadPropertyInteger(self::PropertyRadioThumbSize), 0);
                        if ($CoverRAW === false) {
                            $Line['Thumbnail'] = '';
                        } else {
                            $Line['Thumbnail'] = '<img src="data:image/png;base64,' . base64_encode($CoverRAW) . '" />';
                        }
                    }
                }
                if (array_key_exists('Now', $Config['Spalten']) && array_key_exists('Broadcastnow', $Line)) {
                    if (array_key_exists('title', $Line['Broadcastnow'])) {
                        $Line['Now'] = $Line['Broadcastnow']['title'];
                        if (array_key_exists('episodename', $Line['Broadcastnow'])) {
                            if ($Line['Broadcastnow']['episodename'] != '') {
                                $Line['Now'] .= ' - ' . $Line['Broadcastnow']['episodename'];
                            }
                        }
                        if (array_key_exists('progresspercentage', $Line['Broadcastnow'])) {
                            $Line['Now'] .= ' (' . (int) $Line['Broadcastnow']['progresspercentage'] . '%)';
                        }
                    }
                } else {
                    $Line['Now'] = 'No Info';
                }
                if (array_key_exists('Next', $Config['Spalten']) && array_key_exists('Broadcastnext', $Line)) {
                    if (array_key_exists('title', $Line['Broadcastnext'])) {
                        $Line['Next'] = $Line['Broadcastnext']['title'];
                        if (array_key_exists('episodename', $Line['Broadcastnext'])) {
                            if ($Line['Broadcastnext']['episodename'] != '') {
                                $Line['Next'] .= ' - ' . $Line['Broadcastnext']['episodename'];
                            }
                        }
                        if (array_key_exists('starttime', $Line['Broadcastnext'])) {
                            $starttime = DateTime::createFromFormat('Y-m-d H:i:s', $Line['Broadcastnext']['starttime'], new DateTimeZone('UTC'));
                            $starttime->setTimezone(new DateTimeZone(date_default_timezone_get()));
                            $Line['Next'] .= ' (' . $starttime->format('H:i') . ')';
                        }
                    }
                } else {
                    $Line['Next'] = 'No Info';
                }
                $LineSecret = rawurlencode(base64_encode(sha1($NewSecretRadio . '0' . $Line['Channelid'], true)));
                $HTMLData .= '<tr style="' . $Config['Style']['BR' . ($pos % 2 ? 'U' : 'G')] . '" onclick="xhrGet' . $this->InstanceID . '({ url: \'hook/KodiRadioChannellist' . $this->InstanceID . '?TYP=radio&ID=' . $Line['Channelid'] . '&Secret=' . $LineSecret . '\' })" >';

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
    private function RefreshRecordinglist(): void
    {
        if (!$this->ReadPropertyBoolean(self::PropertyShowRecordinglist)) {
            return;
        }
        $ScriptID = $this->ReadPropertyInteger('Recordinglistconfig');
        if ($ScriptID == 0) {
            return;
        }
        if (!IPS_ScriptExists($ScriptID)) {
            return;
        }
        $result = IPS_RunScriptWaitEx($ScriptID, ['SENDER' => 'Kodi']);
        $Config = @unserialize($result);
        if (($Config === false) || (!is_array($Config))) {
            trigger_error($this->Translate('Error on read Recordinglistconfig-Script'));
            return;
        }

        $Max = $this->ReadPropertyInteger(self::PropertyShowMaxRecording);
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetRecordings(['properties' => static::$RecordingItemList, 'limits' => ['end' => $Max]]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return;
        }
        if ($ret->limits->total == 0) {
            return;
        }
        $Data = $KodiData->ToArray($ret->recordings);

        $NewSecretRecording = base64_encode(openssl_random_pseudo_bytes(12));
        $this->WebHookSecretRecording = $NewSecretRecording;

        $HTMLData = $this->GetTableHeader($Config);
        $pos = 0;

        if (count($Data) > 0) {
            foreach ($Data as $line) {
                $Line = [];
                foreach ($line as $key => $value) {
                    if (is_string($key)) {
                        $Line[ucfirst($key)] = $value;
                    } else {
                        $Line[$key] = $value;
                    } //$key is not a string
                }
                if (array_key_exists('Thumbnail', $Config['Spalten'])) {
                    if (array_key_exists('thumb', $Line['Art'])) {
                        if ($Line['Art']['thumb'] != '') {
                            $CoverRAW = $this->GetThumbnail($Line['Art']['thumb'], $this->ReadPropertyInteger(self::PropertyRecordingThumbSize), 0);
                            if ($CoverRAW === false) {
                                $Line['Thumbnail'] = '';
                            } else {
                                $Line['Thumbnail'] = '<img src="data:image/png;base64,' . base64_encode($CoverRAW) . '" />';
                            }
                        }
                    }
                }
                $Line['Runtime'] = $this->ConvertTime($Line['Runtime']);
                $LineSecret = rawurlencode(base64_encode(sha1($NewSecretRecording . '0' . $Line['Recordingid'], true)));
                $HTMLData .= '<tr style="' . $Config['Style']['BR' . ($pos % 2 ? 'U' : 'G')] . '" onclick="xhrGet' . $this->InstanceID . '({ url: \'hook/KodiRecordinglist' . $this->InstanceID . '?TYP=recording&ID=' . $Line['Recordingid'] . '&Secret=' . $LineSecret . '\' })" >';
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
     * Gibt den Inhalt des PHP-Script zurück, welche die Konfiguration und das Design der TV Kanal-Tabelle enthält.
     *
     * @access private
     * @return string Ein PHP-Script welche als Grundlage für die User dient.
     */
    private function CreateTVChannellistConfigScript(): string
    {
        $Script = '<?php
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
     * Gibt den Inhalt des PHP-Script zurück, welche die Konfiguration und das Design der Radio Kanal-Tabelle enthält.
     *
     * @access private
     * @return string Ein PHP-Script welche als Grundlage für die User dient.
     */
    private function CreateRadioChannellistConfigScript(): string
    {
        $Script = '<?php
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
     * Gibt den Inhalt des PHP-Script zurück, welche die Konfiguration und das Design der Radio Kanal-Tabelle enthält.
     *
     * @access private
     * @return string Ein PHP-Script welche als Grundlage für die User dient.
     */
    private function CreateRecordlistConfigScript(): string
    {
        $Script = '<?php
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
}

/** @} */
