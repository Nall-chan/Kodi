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
eval('declare(strict_types=1);namespace KodiSplitter {?>' . file_get_contents(__DIR__ . '/../libs/helper/BufferHelper.php') . '}');
eval('declare(strict_types=1);namespace KodiSplitter {?>' . file_get_contents(__DIR__ . '/../libs/helper/ParentIOHelper.php') . '}');
eval('declare(strict_types=1);namespace KodiSplitter {?>' . file_get_contents(__DIR__ . '/../libs/helper/SemaphoreHelper.php') . '}');
require_once __DIR__ . '/../libs/DebugHelper.php';  // diverse Klassen
require_once __DIR__ . '/../libs/KodiRPCClass.php';  // diverse Klassen

/**
 * KodiSplitter Klasse für die Kommunikation mit der Kodi-RPC-Api.
 * Enthält den Namespace JSONRPC.
 * Erweitert IPSModule.
 *
 * @package       Kodi
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2020 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       3.00
 *
 * @property int $ParentID
 * @property array $ReplyJSONData
 * @property string $BufferIN
 * @property string $Host
 * @property string $Namespace RPC-Namespace
 * @property bool $StatusIsChanging
 * @method bool lock(string $ident)
 * @method void unlock(string $ident)
 */
class KodiSplitter extends IPSModuleStrict
{
    use \KodiSplitter\BufferHelper,
        \KodiSplitter\InstanceStatus,
        \KodiBase\DebugHelper,
        \KodiSplitter\Semaphore {
            \KodiSplitter\InstanceStatus::MessageSink as IOMessageSink;
            \KodiSplitter\InstanceStatus::RegisterParent as IORegisterParent;
            \KodiSplitter\InstanceStatus::RequestAction as IORequestAction;
        }

    public const PropertyOpen = 'Open';
    public const PropertyPort = 'Port';
    public const PropertyWebport = 'Webport';
    public const PropertyBasisAuth = 'BasisAuth';
    public const PropertyUsername = 'Username';
    public const PropertyPassword = 'Password';
    public const PropertyWatchdog = 'Watchdog';
    public const PropertyConditionType = 'ConditionType';
    public const PropertyInterval = 'Interval';
    public const PropertyWatchdogCondition = 'WatchdogCondition';

    public const TimerKeepAlive = 'KeepAlive';
    public const TimerWatchdog = 'Watchdog';

    public const ActionVisibleFormElementsWatchdog = 'Watchdog';
    public const ActionVisibleFormElementsBasisAuth = 'BasisAuth';
    public const ActionVisibleFormElementsConditionType = 'ConditionType';

    protected static $Namespace = 'JSONRPC';

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Create(): void
    {
        parent::Create();
        $this->RequireParent('{3CFF0FD9-E306-41DB-9B5A-9D06D38576C3}');
        $this->RegisterPropertyBoolean(self::PropertyOpen, false);
        $this->RegisterPropertyInteger(self::PropertyPort, 9090);
        $this->RegisterPropertyInteger(self::PropertyWebport, 80);
        $this->RegisterPropertyBoolean(self::PropertyBasisAuth, false);
        $this->RegisterPropertyString(self::PropertyUsername, '');
        $this->RegisterPropertyString(self::PropertyPassword, '');
        $this->RegisterPropertyBoolean(self::PropertyWatchdog, false);
        $this->RegisterPropertyInteger(self::PropertyConditionType, 0);
        $this->RegisterPropertyInteger(self::PropertyInterval, 5);
        $this->RegisterPropertyString(self::PropertyWatchdogCondition, '');
        $this->RegisterTimer(self::TimerKeepAlive, 0, 'KODIRPC_KeepAlive($_IPS[\'TARGET\']);');
        $this->RegisterTimer(self::TimerWatchdog, 0, 'KODIRPC_Watchdog($_IPS[\'TARGET\']);');
        $this->ParentID = 0;
        $this->ReplyJSONData = [];
        $this->BufferIN = '';
        $this->Host = '';
        $this->StatusIsChanging = false;
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function ApplyChanges(): void
    {
        $this->RegisterMessage($this->InstanceID, FM_CONNECT);
        $this->RegisterMessage($this->InstanceID, FM_DISCONNECT);
        $this->RegisterMessage($this->InstanceID, IM_CHANGESTATUS);

        $this->ParentID = 0;
        $this->ReplyJSONData = [];
        $this->BufferIN = '';
        $this->UnregisterVariable('BufferIN');
        $this->UnregisterVariable('ReplyJSONData');
        $this->SetWatchdogTimer(false);
        parent::ApplyChanges();

        if (IPS_GetKernelRunlevel() != KR_READY) {
            $this->RegisterMessage(0, IPS_KERNELSTARTED);
            $this->SetStatus(IS_INACTIVE);
            return;
        }

        $ParentID = $this->RegisterParent();

        // Nie öffnen
        if (!$this->ReadPropertyBoolean(self::PropertyOpen)) {
            $this->StatusIsChanging = false;
            if ($ParentID > 0) {
                IPS_SetProperty($ParentID, self::PropertyOpen, false);
                @IPS_ApplyChanges($ParentID);
            } else {
                $this->IOChangeState(IS_INACTIVE);
            }
            return;
        }

        // Kein Parent
        if ($ParentID == 0) {
            $this->IOChangeState(IS_INACTIVE);
            return;
        }

        // Keine Verbindung erzwingen wenn Host offline ist
        $Open = $this->CheckCondition();
        if ($Open) {
            if (!$this->CheckPort()) {
                echo $this->Translate('Could not connect to JSON-RPC TCP-Server.');
                $Open = false;
            }
        }
        if ($Open) {
            if (!$this->CheckWebserver()) {
                $Open = false;
            }
        }
        if (!$Open) {
            IPS_SetProperty($ParentID, self::PropertyOpen, false);
            @IPS_ApplyChanges($ParentID);
            $this->SetWatchdogTimer(true);
            return;
        }

        if (IPS_GetProperty($ParentID, self::PropertyPort) != $this->ReadPropertyInteger(self::PropertyPort)) {
            IPS_SetProperty($ParentID, self::PropertyPort, $this->ReadPropertyInteger(self::PropertyPort));
        }

        if (IPS_GetProperty($ParentID, self::PropertyOpen) != true) {
            IPS_SetProperty($ParentID, self::PropertyOpen, true);
        }

        @IPS_ApplyChanges($ParentID);
        return;
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function MessageSink(int $TimeStamp, int $SenderID, int $Message, array $Data): void
    {
        $this->IOMessageSink($TimeStamp, $SenderID, $Message, $Data);
        switch ($Message) {
            case IPS_KERNELSTARTED:
                $this->KernelReady();
                break;
            case IM_CHANGESTATUS:
                if ($SenderID == $this->InstanceID) {
                    if ($this->StatusIsChanging) {
                        $this->SendDebug('MessageSink', 'StatusIsChanging already locked', 0);
                        return;
                    }
                    $this->StatusIsChanging = true;
                    $this->SendDebug('MessageSink', 'StatusIsChanging now locked', 0);
                    switch ($Data[0]) {
                        case IS_ACTIVE:
                            $this->SendDebug('IM_CHANGESTATUS', 'active', 0);
                            $this->LogMessage('Connected to Kodi', KL_NOTIFY);
                            $this->ReadJSONRPCVersion();
                            $this->SetWatchdogTimer(false);
                            $this->SetTimerInterval(self::TimerKeepAlive, 180 * 1000);
                            $this->SendPowerEvent(true);
                            break;
                        case IS_EBASE + 3: //ERROR RCP-Server
                        case IS_EBASE + 4: //ERROR WebServer
                        case IS_INACTIVE:
                            $this->SendDebug('IM_CHANGESTATUS', 'not active', 0);
                            $this->SetWatchdogTimer(true);
                            $this->SetTimerInterval(self::TimerKeepAlive, 0);
                            $this->SendPowerEvent(false);
                            break;
                    }
                    $this->SendDebug('MessageSink', 'StatusIsChanging now unlocked', 0);
                    $this->StatusIsChanging = false;
                }
                break;
        }
    }

    public function GetConfigurationForm(): string
    {
        $Form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        $Form['elements'][4]['expanded'] = $this->ReadPropertyBoolean(self::PropertyBasisAuth);
        $Form['elements'][5]['expanded'] = $this->ReadPropertyBoolean(self::PropertyWatchdog);
        $Form['elements'][5]['items'][0]['items'][1]['visible'] = $this->ReadPropertyBoolean(self::PropertyWatchdog);
        $Form['elements'][5]['items'][1]['items'][0]['visible'] = $this->ReadPropertyBoolean(self::PropertyWatchdog);
        if ($this->ReadPropertyBoolean(self::PropertyWatchdog)) {
            $Form['elements'][5]['items'][1]['items'][1]['visible'] = ($this->ReadPropertyInteger(self::PropertyConditionType) == 1);
        }
        $this->SendDebug('FORM', json_encode($Form), 0);
        $this->SendDebug('FORM', json_last_error_msg(), 0);
        return json_encode($Form);
    }

    public function RequestAction(string $Ident, mixed $Value): void
    {
        if ($this->IORequestAction($Ident, $Value)) {
            return;
        }
        switch ($Ident) {
            case self::ActionVisibleFormElementsBasisAuth:
                $this->UpdateFormField('Username', 'enabled', (bool) $Value);
                $this->UpdateFormField('Password', 'enabled', (bool) $Value);
                break;
            case self::ActionVisibleFormElementsWatchdog:
                $this->UpdateFormField('Watchdog', 'caption', (bool) $Value ? 'Check every' : 'Check never');
                $this->UpdateFormField('Interval', 'visible', (bool) $Value);
                $this->UpdateFormField('ConditionType', 'visible', (bool) $Value);
                $this->UpdateFormField('ConditionPopup', 'visible', $this->ReadPropertyInteger(self::PropertyConditionType) == 1);
                break;
            case self::ActionVisibleFormElementsConditionType:
                $this->UpdateFormField('ConditionPopup', 'visible', $Value == 1);
                break;
        }
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function GetConfigurationForParent(): string
    {
        $Config[self::PropertyOpen] = false;
        if ($this->ReadPropertyBoolean(self::PropertyOpen)) {
            $Config[self::PropertyOpen] = ($this->GetStatus() == IS_ACTIVE);
        }
        $Config[self::PropertyPort] = $this->ReadPropertyInteger(self::PropertyPort);
        return json_encode($Config);
    }

    ################## PUBLIC
    /**
     * IPS-Instanz-Funktion 'KODIRPC_GetImage'. Holt ein Bild vom Kodi-Webfront.
     *
     * @access public
     * @param string $path Pfad des Bildes.
     * @return string Bildinhalt als Bytestring.
     */
    public function GetImage(string $path): string|false
    {
        $CoverRAW = $this->DoWebserverRequest('/image/' . rawurlencode($path));

        if ($CoverRAW === false) {
            trigger_error($this->Translate('Error on load image from Kodi.'), E_USER_NOTICE);
        }
        return $CoverRAW;
    }

    /**
     * IPS-Instanz-Funktion 'KODIRPC_KeepAlive'.
     * Sendet einen RPC-Ping an Kodi und prüft die erreichbarkeit.
     *
     * @access public
     * @return bool true wenn Kodi erreichbar, sonst false.
     */
    public function KeepAlive(): bool
    {
        $KodiData = new Kodi_RPC_Data('JSONRPC', 'Ping');
        $ret = @$this->Send($KodiData);
        if ($ret !== 'pong') {
            echo 'Connection to Kodi lost.';
            $this->SetStatus(IS_EBASE + 3);
            return false;
        }
        return true;
    }

    /**
     * IPS-Instanz-Funktion 'KODIRPC_Watchdog'.
     * Sendet einen TCP-Ping an Kodi und prüft die Erreichbarkeit des OS.
     * Wird erkannt, dass das OS erreichbar ist, wird versucht eine RPC-Verbindung zu Kodi aufzubauen.
     *
     * @access public
     */
    public function Watchdog(): void
    {
        $this->SendDebug(__FUNCTION__, 'run', 0);
        if (!$this->ReadPropertyBoolean(self::PropertyOpen)) {
            return;
        }
        if ($this->Host != '') {
            if ($this->HasActiveParent()) {
                return;
            }
            if (!$this->CheckCondition()) {
                return;
            }
            if (!$this->CheckPort()) {
                return;
            }
            IPS_SetProperty($this->ParentID, self::PropertyOpen, true);
            @IPS_ApplyChanges($this->ParentID);
        }
    }

    ################## DATAPOINTS DEVICE
    /**
     * Interne Funktion des SDK. Nimmt Daten von Children entgegen und sendet Diese weiter.
     *
     * @access public
     * @param string $JSONString Ein Kodi_RPC_Data-Objekt welches als JSONString kodiert ist.
     * @return bool true wenn Daten gesendet werden konnten, sonst false.
     */
    public function ForwardData(string $JSONString): string
    {
        $Data = json_decode($JSONString);
        $KodiData = new Kodi_RPC_Data();
        $KodiData->CreateFromGenericObject($Data);
        $ret = $this->Send($KodiData);
        if (!is_null($ret)) {
            return serialize($ret);
        }
        return '';
    }

    ################## DATAPOINTS PARENT
    /**
     * Empfängt Daten vom Parent.
     *
     * @access public
     * @param string $JSONString Das empfangene JSON-kodierte Objekt vom Parent.
     * @return bool True wenn Daten verarbeitet wurden, sonst false.
     */
    public function ReceiveData(string $JSONString): string
    {
        $data = json_decode($JSONString);

        // DatenStream zusammenfügen
        $head = $this->BufferIN;
        $Data = $head . hex2bin($data->Buffer);

        // Stream in einzelne Pakete schneiden
        $Count = 0;
        $Data = str_replace('}{', '}' . chr(0x04) . '{', $Data, $Count);
        $JSONLine = explode(chr(0x04), $Data);

        if (is_null(json_decode($JSONLine[$Count]))) {
            // Rest vom Stream wieder in den EmpfangsBuffer schieben
            $tail = array_pop($JSONLine);
            if (strlen($tail) > 256 * 1024) { //Drop large Paket
                $this->SendDebug('Skip date over 265kB', '', 0);
                $this->LogMessage('Kodi-RPC server send date over 265kB', KL_DEBUG);
                $tail = '';
            }
            $this->BufferIN = $tail;
        } else {
            $this->BufferIN = '';
        }

        // Pakete verarbeiten
        foreach ($JSONLine as $i => $JSON) {
            $KodiData = new Kodi_RPC_Data();
            if (!$KodiData->CreateFromJSONString($JSON)) {
                if (($i != 0) || ($JSON[0] != '{')) {
                    $this->SendDebug('Skip error on receive', $JSON, 0);
                }
                continue;
            }
            if ($KodiData->Typ == Kodi_RPC_Data::$ResultTyp) { // Reply
                try {
                    $this->SendQueueUpdate((int) $KodiData->Id, $KodiData);
                } catch (Exception $exc) {
                    $buffer = $this->BufferIN;
                    $this->BufferIN = $JSON . $buffer;
                    trigger_error($exc->getMessage(), E_USER_NOTICE);
                    continue;
                }
            } elseif ($KodiData->Typ == Kodi_RPC_Data::$EventTyp) { // Event
                if ($KodiData->Namespace == 'Other') {
                    //skip
                    continue;
                }
                $this->SendDebug('Receive Event', $KodiData, 0);
                $this->SendDataToDevice($KodiData);
            }
        }
        return '';
    }

    protected function RegisterParent(): int
    {
        $ParentID = $this->IORegisterParent();
        if ($ParentID > 0) {
            $this->Host = IPS_GetProperty($ParentID, 'Host');
        } else {
            $this->Host = '';
        }
        $this->SetSummary($this->Host);
        return $ParentID;
    }

    /**
     * Wird ausgeführt wenn der Kernel hochgefahren wurde.
     */
    protected function KernelReady(): void
    {
        $this->UnregisterMessage(0, IPS_KERNELSTARTED);
        $this->ApplyChanges();
    }

    /**
     * Wird ausgeführt wenn sich der Status vom Parent ändert.
     * @access protected
     */
    protected function IOChangeState(int $State): void
    {
        if ($this->StatusIsChanging) {
            $this->SendDebug('IOChangeState', 'StatusIsChanging already locked', 0);
            return;
        }
        $this->StatusIsChanging = true;
        $this->SendDebug('IOChangeState', 'StatusIsChanging now locked', 0);
        if (!$this->ReadPropertyBoolean(self::PropertyOpen)) {
            if ($this->GetStatus() != IS_INACTIVE) {
                $this->SetStatus(IS_INACTIVE);
            }
            $this->SendDebug('IOChangeState', 'StatusIsChanging now unlocked', 0);
            $this->StatusIsChanging = false;
            return;
        }
        switch ($State) {
            case IS_ACTIVE:
                // Keine Verbindung erzwingen wenn Host offline ist
                $NewState = IS_ACTIVE;
                $KodiData = new Kodi_RPC_Data('JSONRPC', 'Ping');
                $ret = @$this->Send($KodiData);
                if ($ret == 'pong') {
                    if (!$this->CheckWebserver()) {
                        $NewState = IS_EBASE + 4;
                    }
                } else {
                    $NewState = IS_INACTIVE;
                }
                if ($this->GetStatus() != $NewState) {
                    $this->SetStatus($NewState);
                }
                break;
            case IS_INACTIVE:
                if ($this->GetStatus() != IS_INACTIVE) {
                    $this->SetStatus(IS_INACTIVE);
                } break;
            default:
                if ($this->ParentID > 0) {
                    IPS_SetProperty($this->ParentID, self::PropertyOpen, false);
                    @IPS_ApplyChanges($this->ParentID);
                }
                break;
        }
        $this->SendDebug('IOChangeState', 'StatusIsChanging now unlocked', 0);
        $this->StatusIsChanging = false;
    }

    /**
     * Versendet ein Kodi_RPC-Objekt und empfängt die Antwort.
     *
     * @access protected
     * @param Kodi_RPC_Data $KodiData Das Objekt welches versendet werden soll.
     * @return mixed Enthält die Antwort auf das Versendete Objekt oder NULL im Fehlerfall.
     */
    protected function Send(Kodi_RPC_Data $KodiData): mixed
    {
        try {
            if ($this->ReadPropertyBoolean(self::PropertyOpen) === false) {
                throw new Exception('Instance inactive.', E_USER_NOTICE);
            }

            if (!$this->HasActiveParent()) {
                throw new Exception('Instance has no active parent.', E_USER_NOTICE);
            }
            $this->SendDebug('Send', $KodiData, 0);
            $this->SendQueuePush((int) $KodiData->Id);
            $JsonString = $KodiData->ToRPCJSONString('{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}');
            $this->SendDataToParent($JsonString);
            $ReplyKodiData = $this->WaitForResponse((int) $KodiData->Id);

            if (is_null($ReplyKodiData)) {
                throw new Exception('No answer from Kodi', E_USER_NOTICE);
            }

            $ret = $ReplyKodiData->GetResult();
            if (is_a($ret, 'KodiRPCException')) {
                throw $ret;
            }
            $this->SendDebug('Receive', $ReplyKodiData, 0);
            return $ret;
        } catch (KodiRPCException $ex) {
            $this->SendDebug('Receive', $ex, 0);
            echo $ex->getMessage();
        } catch (Exception $ex) {
            $this->SendDebug('Receive', $ex->getMessage(), 0);
            echo $ex->getMessage();
        }
        return null;
    }

    ################## PRIVATE
    private function ReadJSONRPCVersion()
    {
        $KodiData = new Kodi_RPC_Data('JSONRPC', 'Version');
        $ret = @$this->Send($KodiData);
        if ($ret !== null) {
            $this->LogMessage('Kodi RPC-Version: ' . $ret->version->major . '.' . $ret->version->minor . '.' . $ret->version->patch, KL_NOTIFY);
        }
    }
    private function CheckPort(): bool
    {
        $Socket = @stream_socket_client('tcp://' . $this->Host . ':' . $this->ReadPropertyInteger(self::PropertyPort), $errno, $errstr, 2);
        if (!$Socket) {
            $this->SendDebug('CheckPort', false, 0);
            return false;
        }
        stream_set_timeout($Socket, 1);
        $KodiData = new Kodi_RPC_Data('JSONRPC', 'Ping');
        $JSON = $KodiData->ToRawRPCJSONString();
        fwrite($Socket, $JSON);
        $Result = stream_get_line($Socket, 1024, '}');
        stream_socket_shutdown($Socket, STREAM_SHUT_RDWR);
        if ($Result === false) {
            $this->SendDebug('CheckPort', false, 0);
            return false;
        }
        $KodiResult = new Kodi_RPC_Data();
        $KodiResult->CreateFromJSONString($Result . '}');
        $ret = $KodiResult->GetResult();

        if (is_a($ret, 'KodiRPCException')) {
            trigger_error('Error (' . $ret->getCode() . '): ' . $ret->getMessage(), E_USER_NOTICE);
        }

        $Result = false;
        if ($KodiResult->GetResult() == 'pong') {
            $Result = true;
        }
        $this->SendDebug('CheckPort', $Result, 0);
        return $Result;
    }

    /**
     * Prüft die Verbindung zum WebServer.
     *
     * @return boolean True bei Erfolg, sonst false.
     */
    private function CheckWebserver(): bool
    {
        $KodiData = new Kodi_RPC_Data('JSONRPC', 'Ping');
        $JSON = $KodiData->ToRawRPCJSONString();
        $http_response_code = 0;
        $Result = $this->DoWebserverRequest('/jsonrpc?request=' . rawurlencode($JSON), $http_response_code);
        if ($Result !== false) {
            $KodiResult = new Kodi_RPC_Data();
            $KodiResult->CreateFromJSONString($Result);
            $ret = $KodiResult->GetResult();
            if (is_a($ret, 'KodiRPCException')) {
                trigger_error('Error (' . $ret->getCode() . '): ' . $ret->getMessage(), E_USER_NOTICE);
            }
            if ($KodiResult->GetResult() == 'pong') {
                return true;
            }
        }
        if ($http_response_code == 401) {
            echo $this->Translate('Unauthorized! Check username and password.');
        } else {
            echo $this->Translate('Could not connect to webserver.');
        }
        return false;
    }

    /**
     * Führt eine Anfrage an den Kodi-Webserver aus.
     *
     * @param string $URI URI welche angefragt wird.
     * @return false|string Inhalt der Antwort, False bei Fehler.
     */
    private function DoWebserverRequest(string $URI, int &$http_code = 0): false|string
    {
        $Host = $this->Host;
        $Port = $this->ReadPropertyInteger(self::PropertyWebport);
        $UseBasisAuth = $this->ReadPropertyBoolean(self::PropertyBasisAuth);
        $User = $this->ReadPropertyString(self::PropertyUsername);
        $Pass = $this->ReadPropertyString(self::PropertyPassword);
        $URL = 'http://' . $Host . ':' . $Port . $URI;
        $ch = curl_init();
        $timeout = 1; // 0 wenn kein Timeout
        curl_setopt($ch, CURLOPT_URL, $URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 5000);
        if ($UseBasisAuth) {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $User . ':' . $Pass);
        }

        $this->SendDebug('DoWebRequest', $URL, 0);
        $Result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($http_code >= 400) {
            $this->SendDebug('WebRequest Error', $http_code, 0);
            $Result = false;
        } else {
            if ($Result === false) {
                $this->SendDebug('WebRequest Result:' . $http_code, $Result, 0);
            } else {
                $this->SendDebug('WebRequest Result:' . $http_code, substr($Result, 0, 100), 0);
            }
        }
        return $Result;
    }

    /**
     * Aktiviert / Deaktiviert den WatchdogTimer.
     *
     * @param bool $Active True für aktiv, false für desaktiv.
     */
    private function SetWatchdogTimer(bool $Active): void
    {
        if ($this->ReadPropertyBoolean(self::PropertyOpen)) {
            if ($this->ReadPropertyBoolean(self::PropertyWatchdog)) {
                $Interval = $this->ReadPropertyInteger(self::PropertyInterval);
                $Interval = ($Interval < 5) ? 0 : $Interval;
                if ($Active) {
                    $this->SetTimerInterval(self::TimerWatchdog, $Interval * 1000);
                    $this->SendDebug(self::TimerWatchdog, 'active', 0);
                    return;
                }
            }
        }
        $this->SetTimerInterval(self::TimerWatchdog, 0);
        $this->SendDebug(self::TimerWatchdog, 'inactive', 0);
    }

    /**
     * Sendet ein PowerEvent an die Children.
     * Ermöglicht es dass der Children vom Typ KodiDeviceSystem den aktuellen an/aus Zustand von Kodi kennt.
     *
     * @access private
     * @param bool $value true für an, false für aus.
     */
    private function SendPowerEvent(bool $value): void
    {
        $KodiData = new Kodi_RPC_Data('System', 'Power', ['data' => $value], 0);
        $this->SendDataToDevice($KodiData);
        $KodiData = new Kodi_RPC_Data('Playlist', 'OnClear', ['data' => ['playlistid' => 0]], 0);
        $this->SendDataToDevice($KodiData);
        $KodiData = new Kodi_RPC_Data('Playlist', 'OnClear', ['data' => ['playlistid' => 1]], 0);
        $this->SendDataToDevice($KodiData);
        $KodiData = new Kodi_RPC_Data('Playlist', 'OnClear', ['data' => ['playlistid' => 2]], 0);
        $this->SendDataToDevice($KodiData);
    }

    private function CheckCondition(): bool
    {
        if (!$this->ReadPropertyBoolean(self::PropertyWatchdog)) {
            return true;
        }
        switch ($this->ReadPropertyInteger(self::PropertyConditionType)) {
            case 0:
                $Result = @Sys_Ping($this->Host, 500);
                $this->SendDebug('Pinging', $Result, 0);
                return $Result;
            case 1:
                $Result = IPS_IsConditionPassing($this->ReadPropertyString(self::PropertyWatchdogCondition));
                $this->SendDebug('CheckCondition', $Result, 0);
                return $Result;
        }
        return false;
    }

    /**
     * Sendet Kodi_RPC_Data an die Children.
     *
     * @access private
     * @param Kodi_RPC_Data $KodiData Ein Kodi_RPC_Data-Objekt.
     */
    private function SendDataToDevice(Kodi_RPC_Data $KodiData): void
    {
        $Data = $KodiData->ToJSONString('{73249F91-710A-4D24-B1F1-A72F216C2BDC}');
        $this->SendDataToChildren($Data);
    }

    /**
     * Wartet auf eine RPC-Antwort.
     *
     * @access private
     * @param int $Id Die RPC-ID auf die gewartet wird.
     * @return mixed Enthält ein Kodi_RPC_Data-Objekt mit der Antwort, oder false bei einem Timeout.
     */
    private function WaitForResponse(int $Id): ?Kodi_RPC_Data
    {
        for ($i = 0; $i < 1000; $i++) {
            $ret = $this->ReplyJSONData;
            if (!array_key_exists($Id, $ret)) {
                return null;
            }
            if ($ret[$Id] != '') {
                return $this->SendQueuePop($Id);
            }
            IPS_Sleep(5);
        }
        $this->SendQueueRemove($Id);
        return null;
    }

    ################## SENDQUEUE
    /**
     * Fügt eine Anfrage in die SendQueue ein.
     *
     * @access private
     * @param int $Id die RPC-ID des versendeten RPC-Objektes.
     */
    private function SendQueuePush(int $Id): void
    {
        if (!$this->lock('ReplyJSONData')) {
            throw new Exception('ReplyJSONData is locked', E_USER_NOTICE);
        }
        $data = $this->ReplyJSONData;
        $data[$Id] = '';
        $this->ReplyJSONData = $data;
        $this->unlock('ReplyJSONData');
    }

    /**
     * Fügt eine RPC-Antwort in die SendQueue ein.
     *
     * @access private
     * @param int $Id die RPC-ID des empfangenen Objektes.
     * @param Kodi_RPC_Data $KodiData Das empfangene RPC-Result.
     */
    private function SendQueueUpdate(int $Id, Kodi_RPC_Data $KodiData): void
    {
        if (!$this->lock('ReplyJSONData')) {
            throw new Exception('ReplyJSONData is locked', E_USER_NOTICE);
        }
        $data = $this->ReplyJSONData;
        $data[$Id] = $KodiData->ToJSONString('');
        $this->ReplyJSONData = $data;
        $this->unlock('ReplyJSONData');
    }

    /**
     * Holt eine RPC-Antwort aus der SendQueue.
     *
     * @access private
     * @param int $Id die RPC-ID des empfangenen Objektes.
     * @return Kodi_RPC_Data Das empfangene RPC-Result.
     */
    private function SendQueuePop(int $Id): Kodi_RPC_Data
    {
        $data = $this->ReplyJSONData;
        $Result = new Kodi_RPC_Data();
        $JSONObject = json_decode($data[$Id]);
        $Result->CreateFromGenericObject($JSONObject);
        $this->SendQueueRemove($Id);
        return $Result;
    }

    /**
     * Löscht einen RPC-Eintrag aus der SendQueue.
     *
     * @access private
     * @param int $Id Die RPC-ID des zu löschenden Objektes.
     */
    private function SendQueueRemove(int $Id): void
    {
        if (!$this->lock('ReplyJSONData')) {
            throw new Exception('ReplyJSONData is locked', E_USER_NOTICE);
        }
        $data = $this->ReplyJSONData;
        unset($data[$Id]);
        $this->ReplyJSONData = $data;
        $this->unlock('ReplyJSONData');
    }
}

/** @} */
