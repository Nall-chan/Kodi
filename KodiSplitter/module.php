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
 * @property array $ReplyJSONData
 * @property string $BufferIN
 * @property string $Host
 * @property bool $StatusIsChanging
 * @example <b>Ohne</b>
 */
class KodiSplitter extends IPSModule
{
    use \KodiSplitter\BufferHelper,
        \KodiSplitter\InstanceStatus,
        \KodiBase\DebugHelper,
        \KodiSplitter\Semaphore {
        \KodiSplitter\InstanceStatus::MessageSink as IOMessageSink;
        \KodiSplitter\InstanceStatus::RegisterParent as IORegisterParent;
        \KodiSplitter\InstanceStatus::RequestAction as IORequestAction;
    }
    /**
     * RPC-Namespace
     *
     * @access private
     * @var string
     * @value 'JSONRPC'
     */
    protected static $Namespace = 'JSONRPC';

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Create()
    {
        parent::Create();
        $this->RequireParent('{3CFF0FD9-E306-41DB-9B5A-9D06D38576C3}');
        $this->RegisterPropertyBoolean('Open', false);
        $this->RegisterPropertyInteger('Port', 9090);
        $this->RegisterPropertyInteger('Webport', 80);
        $this->RegisterPropertyBoolean('BasisAuth', false);
        $this->RegisterPropertyString('Username', '');
        $this->RegisterPropertyString('Password', '');
        $this->RegisterPropertyBoolean('Watchdog', false);
        $this->RegisterPropertyInteger('Interval', 5);
        $this->RegisterTimer('KeepAlive', 0, 'KODIRPC_KeepAlive($_IPS[\'TARGET\']);');
        $this->RegisterTimer('Watchdog', 0, 'KODIRPC_Watchdog($_IPS[\'TARGET\']);');
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
    public function ApplyChanges()
    {
        $this->RegisterMessage($this->InstanceID, FM_CONNECT);
        $this->RegisterMessage($this->InstanceID, FM_DISCONNECT);
        $this->RegisterMessage($this->InstanceID, IM_CHANGESTATUS);

        $this->ParentID = 0;
        $this->ReplyJSONData = [];
        $this->BufferIN = '';
        $this->UnregisterVariable('BufferIN');
        $this->UnregisterVariable('ReplyJSONData');

        parent::ApplyChanges();

        if (IPS_GetKernelRunlevel() != KR_READY) {
            $this->RegisterMessage(0, IPS_KERNELSTARTED);
            $this->SetStatus(IS_INACTIVE);
            return;
        }

        $ParentID = $this->RegisterParent();

        // Nie öffnen
        if (!$this->ReadPropertyBoolean('Open')) {
            if ($ParentID > 0) {
                IPS_SetProperty($ParentID, 'Open', false);
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
        $Open = $this->DoPing();
        if ($Open) {
            if (!$this->CheckPort()) {
                // echo 'Could not connect to JSON-RPC TCP-Port.';
                // todo
                // Popupalert
                $Open = false;
            }
        }
        if ($Open) {
            if (!$this->CheckWebserver()) {
                // echo 'Could not connect to webserver.';
                // todo
                // Popupalert
                $Open = false;
            }
        }

        if (!$Open) {
            IPS_SetProperty($ParentID, 'Open', false);
            @IPS_ApplyChanges($ParentID);
            return;
        }

        if (IPS_GetProperty($ParentID, 'Port') != $this->ReadPropertyInteger('Port')) {
            IPS_SetProperty($ParentID, 'Port', $this->ReadPropertyInteger('Port'));
        }

        if (IPS_GetProperty($ParentID, 'Open') != true) {
            IPS_SetProperty($ParentID, 'Open', true);
        }

        @IPS_ApplyChanges($ParentID);
        return;
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
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
                            $this->LogMessage('Connected to Kodi', KL_NOTIFY);
                            $this->ReadJSONRPCVersion();
                            $this->SetWatchdogTimer(false);
                            $this->SetTimerInterval('KeepAlive', 180 * 1000);
                            $this->SendPowerEvent(true);
                            break;
                        case IS_EBASE + 3: //ERROR RCP-Server
                        case IS_EBASE + 4: //ERROR WebServer
                        case IS_EBASE + 2: //mis config
                        case IS_INACTIVE:
                            $this->SetWatchdogTimer(true);
                            $this->SetTimerInterval('KeepAlive', 0);
                            $this->SendPowerEvent(false);
                            break;
                    }
                    $this->SendDebug('MessageSink', 'StatusIsChanging now unlocked', 0);
                    $this->StatusIsChanging = false;
                }
                break;
        }
    }

    public function RequestAction($Ident, $Value)
    {
        if ($this->IORequestAction($Ident, $Value)) {
            return true;
        }
    }

    /**
     * Dekodiert die empfangenen Events und Antworten auf 'GetProperties'.
     *
     * @access protected
     * @param string $Method RPC-Funktion ohne Namespace
     * @param object $KodiPayload Der zu dekodierende Datensatz als Objekt.
     */
//    protected function Decode($Method, $Event)
//    {
//        $this->SendDebug('Decode' . $Method, $Event, 0);
//
//        if ($Method == 'OnQuit')
//            $this->IOChangeState(IS_INACTIVE);
//    }
    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function GetConfigurationForParent()
    {
        $Config['Open'] = false;
        if ($this->ReadPropertyBoolean('Open')) {
            $Config['Open'] = ($this->GetStatus() == IS_ACTIVE);
        }
        $Config['Port'] = $this->ReadPropertyInteger('Port');
        return json_encode($Config);
    }

    ################## PUBLIC
    /**
     * IPS-Instanz-Funktion 'KODIRPC_GetImage'. Holt ein Bild vom Kodi-Webfront.
     *
     * @access public
     * @param string $path Pfad des Bildes.
     * @result string Bildinhalt als Bytestring.
     */
    public function GetImage(string $path)
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
     * @result bool true wenn Kodi erreichbar, sonst false.
     */
    public function KeepAlive()
    {
        $KodiData = new Kodi_RPC_Data('JSONRPC', 'Ping');
        $ret = @$this->Send($KodiData);
        if ($ret !== 'pong') {
            echo 'Connection to Kodi lost.';
            $this->SetStatus(203);
            return false;
        }
        return true;
    }

    /**
     * IPS-Instanz-Funktion 'KODIRPC_Watchdog'.
     * Sendet einen TCP-Ping an Kodi und prüft die erreichbarkeit des OS.
     * Wird erkannt, dass das OS erreichbar ist, wird versucht eine RPC-Verbindung zu Kodi aufzubauen.
     *
     * @access public
     */
    public function Watchdog()
    {
        $this->SendDebug('Watchdog', 'run', 0);
        if (!$this->ReadPropertyBoolean('Open')) {
            return;
        }
        if ($this->Host != '') {
            if ($this->HasActiveParent()) {
                return;
            }
            if (!$this->DoPing()) {
                return;
            }
            if (!$this->CheckPort()) {
                return;
            }
            IPS_SetProperty($this->ParentID, 'Open', true);
            @IPS_ApplyChanges($this->ParentID);
        }
    }

    ################## DATAPOINTS DEVICE
    /**
     * Interne Funktion des SDK. Nimmt Daten von Children entgegen und sendet Diese weiter.
     *
     * @access public
     * @param string $JSONString Ein Kodi_RPC_Data-Objekt welches als JSONString kodiert ist.
     * @result bool true wenn Daten gesendet werden konnten, sonst false.
     */
    public function ForwardData($JSONString)
    {
//        $this->SendDebug('Forward', $JSONString, 0);

        $Data = json_decode($JSONString);
//        if ($Data->DataID <> '{0222A902-A6FA-4E94-94D3-D54AA4666321}')
//            return false;
        $KodiData = new Kodi_RPC_Data();
        $KodiData->CreateFromGenericObject($Data);
//        try
//        {
        $ret = $this->Send($KodiData);
        //          $this->SendDebug('Result', $answer, 0);
        if (!is_null($ret)) {
            return serialize($ret);
        }
//        }
//        catch (Exception $ex)
//        {
//            trigger_error($ex->getMessage(), $ex->getCode());
//        }
        return false;
    }

    ################## DATAPOINTS PARENT
    /**
     * Empfängt Daten vom Parent.
     *
     * @access public
     * @param string $JSONString Das empfangene JSON-kodierte Objekt vom Parent.
     * @result bool True wenn Daten verarbeitet wurden, sonst false.
     */
    public function ReceiveData($JSONString)
    {
        $data = json_decode($JSONString);

        // DatenStream zusammenfügen
        $head = $this->BufferIN;
        $Data = $head . utf8_decode($data->Buffer);

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
//                if (($KodiData->Namespace == 'System') and ( $KodiData->Method == 'OnQuit'))
//                    $this->Decode($KodiData->Method, $KodiData->GetEvent());
                $this->SendDataToDevice($KodiData);
//                if (self::$Namespace == $KodiData->Namespace)
//                    $this->Decode($KodiData->Method, $KodiData->GetEvent());
            }
        }
        return true;
    }

    protected function RegisterParent()
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
    protected function KernelReady()
    {
        $this->UnregisterMessage(0, IPS_KERNELSTARTED);
        $this->ApplyChanges();
    }

    /**
     * Wird ausgeführt wenn sich der Status vom Parent ändert.
     * @access protected
     */
    protected function IOChangeState($State)
    {
        if ($this->StatusIsChanging) {
            $this->SendDebug('IOChangeState', 'StatusIsChanging already locked', 0);
            return;
        }
        $this->StatusIsChanging = true;
        $this->SendDebug('IOChangeState', 'StatusIsChanging now locked', 0);
        if (!$this->ReadPropertyBoolean('Open')) {
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
                        echo 'Could not connect to webserver.';
                        $NewState = 204;
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
                    IPS_SetProperty($this->ParentID, 'Open', false);
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
     * @result mixed Enthält die Antwort auf das Versendete Objekt oder NULL im Fehlerfall.
     */
    protected function Send(Kodi_RPC_Data $KodiData)
    {
        try {
            if ($this->ReadPropertyBoolean('Open') === false) {
                throw new Exception('Instance inactive.', E_USER_NOTICE);
            }

            if (!$this->HasActiveParent()) {
                throw new Exception('Instance has no active parent.', E_USER_NOTICE);
            }
            $this->SendDebug('Send', $KodiData, 0);
            $this->SendQueuePush((int) $KodiData->Id);
            $this->SendDataToParent($KodiData);
            $ReplyKodiData = $this->WaitForResponse((int) $KodiData->Id);

            if ($ReplyKodiData === false) {
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
            //trigger_error('Error (' . $ex->getCode() . '): ' . $ex->getMessage(), E_USER_NOTICE);
            echo $ex->getMessage();
        } catch (Exception $ex) {
            $this->SendDebug('Receive', $ex->getMessage(), 0);
            //trigger_error($ex->getMessage(), $ex->getCode());
            echo $ex->getMessage();
        }
        return null;
    }

    /**
     * Sendet ein Kodi_RPC-Objekt an den Parent.
     *
     * @access protected
     * @param Kodi_RPC_Data $Data Das Objekt welches versendet werden soll.
     * @result bool true
     */
    protected function SendDataToParent($Data)
    {
        $JsonString = $Data->ToRPCJSONString('{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}');
        parent::SendDataToParent($JsonString);
        return true;
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
    private function CheckPort()
    {
        $Socket = @stream_socket_client('tcp://' . $this->Host . ':' . $this->ReadPropertyInteger('Port'), $errno, $errstr, 2);
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
    private function CheckWebserver()
    {
        $KodiData = new Kodi_RPC_Data('JSONRPC', 'Ping');
        $JSON = $KodiData->ToRawRPCJSONString();
        $Result = $this->DoWebserverRequest('/jsonrpc?request=' . rawurlencode($JSON));
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
        return false;
    }

    /**
     * Führt eine Anfrage an den Kodi-Webserver aus.
     *
     * @param string $URI URI welche angefragt wird.
     * @return boolean|string Inhalt der Antwort, False bei Fehler.
     */
    private function DoWebserverRequest(string $URI)
    {
        $Host = $this->Host;
        $Port = $this->ReadPropertyInteger('Webport');
        $UseBasisAuth = $this->ReadPropertyBoolean('BasisAuth');
        $User = $this->ReadPropertyString('Username');
        $Pass = $this->ReadPropertyString('Password');
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
    private function SetWatchdogTimer(bool $Active)
    {
        if ($this->ReadPropertyBoolean('Open')) {
            if ($this->ReadPropertyBoolean('Watchdog')) {
                $Interval = $this->ReadPropertyInteger('Interval');
                $Interval = ($Interval < 5) ? 0 : $Interval;
                if ($Active) {
                    $this->SetTimerInterval('Watchdog', $Interval * 1000);
                    $this->SendDebug('Watchdog', 'active', 0);
                    return;
                }
            }
        }
        $this->SetTimerInterval('Watchdog', 0);
        $this->SendDebug('Watchdog', 'inactive', 0);
    }

    /**
     * Sendet ein PowerEvent an die Children.
     * Ermöglicht es dass der Children vom Typ KodiDeviceSystem den aktuellen an/aus Zustand von Kodi kennt.
     *
     * @access private
     * @param bool $value true für an, false für aus.
     */
    private function SendPowerEvent($value)
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

    private function DoPing()
    {
        $Result = @Sys_Ping($this->Host, 500);
        $this->SendDebug('Pinging', $Result, 0);
        return $Result;
    }

    /**
     * Sendet Kodi_RPC_Data an die Children.
     *
     * @access private
     * @param Kodi_RPC_Data $KodiData Ein Kodi_RPC_Data-Objekt.
     */
    private function SendDataToDevice(Kodi_RPC_Data $KodiData)
    {
        $Data = $KodiData->ToJSONString('{73249F91-710A-4D24-B1F1-A72F216C2BDC}');
        //$this->SendDebug('IPS_SendDataToChildren', $Data, 0);
        $this->SendDataToChildren($Data);
    }

    /**
     * Wartet auf eine RPC-Antwort.
     *
     * @access private
     * @param int $Id Die RPC-ID auf die gewartet wird.
     * @result mixed Enthält ein Kodi_RPC_Data-Objekt mit der Antwort, oder false bei einem Timeout.
     */
    private function WaitForResponse(int $Id)
    {
        for ($i = 0; $i < 1000; $i++) {
            $ret = $this->ReplyJSONData;
            if (!array_key_exists($Id, $ret)) {
                return false;
            }
            if ($ret[$Id] != '') {
                return $this->SendQueuePop($Id);
            }
            IPS_Sleep(5);
        }
        $this->SendQueueRemove($Id);
        return false;
    }

    ################## SENDQUEUE
    /**
     * Fügt eine Anfrage in die SendQueue ein.
     *
     * @access private
     * @param int $Id die RPC-ID des versendeten RPC-Objektes.
     */
    private function SendQueuePush(int $Id)
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
    private function SendQueueUpdate(int $Id, Kodi_RPC_Data $KodiData)
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
    private function SendQueuePop(int $Id)
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
    private function SendQueueRemove(int $Id)
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
