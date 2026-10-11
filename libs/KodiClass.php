<?php

declare(strict_types=1);

eval('declare(strict_types=1);namespace KodiBase {?>' . file_get_contents(__DIR__ . '/../libs/helper/BufferHelper.php') . '}');
eval('declare(strict_types=1);namespace KodiBase {?>' . file_get_contents(__DIR__ . '/../libs/helper/ParentIOHelper.php') . '}');
eval('declare(strict_types=1);namespace KodiBase {?>' . file_get_contents(__DIR__ . '/../libs/helper/VariableProfileHelper.php') . '}');
eval('declare(strict_types=1);namespace KodiBase {?>' . file_get_contents(__DIR__ . '/../libs/helper/AttributeArrayHelper.php') . '}');

require_once __DIR__ . '/DebugHelper.php';  // diverse Klassen
require_once __DIR__ . '/KodiRPCClass.php';  // diverse Klassen
require_once __DIR__ . '/HTMLTableHelper.php';  // HTML-Tabellen

/**
 * Basisklasse für alle Kodi IPS-Instanzklassen.
 * Erweitert IPSModule.
 *
 * @project       KodiBase
 * @file          KodiClass.php
 * @abstract
 * @package       Kodi
 * @author        Michael Tröger <nall4chan@gmail.com>
 * @copyright     2026 Michael Tröger
 * @license       Custom Non-Commercial Share-Alike License (see LICENSE)
 * @version       3.02
 * @example <b>Ohne</b>
 * @property int $ParentID
 * @property string $WebHookSecret
 * @property array|string $Namespace RPC-Namespace
 * @property array $Properties Alle Properties des RPC-Namespace
 * @property array $ItemListFull Alle Properties eines Item
 *
 * @method void RegisterProfileIntegerEx(string $Name, string $Icon, string $Prefix, string $Suffix, array $Associations, int $MaxValue = -1, float $StepSize = 0)
 * @method void UnregisterProfile(string $Name)
 * @method void RegisterProfileInteger(string $Name, string $Icon, string $Prefix, string $Suffix, int $MinValue, int $MaxValue, float $StepSize)
 * @method bool RegisterHook(string $WebHook)
 * @method bool UnregisterHook(string $WebHook)
 * @method bool IORequestAction(string $Ident, mixed $Value)
 */
abstract class KodiBase extends IPSModuleStrict
{
    use \KodiBase\VariableProfileHelper,
        \KodiBase\DebugHelper,
        \KodiBase\BufferHelper,
        \KodiBase\AttributeArrayHelper,
        \KodiBase\HTMLTable,
        \KodiBase\InstanceStatus {
            \KodiBase\InstanceStatus::MessageSink as IOMessageSink;
            \KodiBase\InstanceStatus::RegisterParent as IORegisterParent;
            \KodiBase\InstanceStatus::RequestAction as IORequestAction;
        }

    public const ProfilePrefix = 'Kodi.';

    protected static $Namespace;
    protected static $PartialProperties;
    protected static $Properties;
    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Create(): void
    {
        parent::Create();
        $this->ParentID = 0;
        $this->WebHookSecret = '';
        if (IPS_GetKernelRunlevel() != KR_READY) {
            $this->RegisterMessage(0, IPS_KERNELSTARTED);
        }
    }

    /**
     * GetCompatibleParents
     *
     * @return string
     */
    public function GetCompatibleParents(): string
    {
        return '{"type": "connect", "moduleIDs": ["{D2F106B5-4473-4C19-A48F-812E8BAA316C}"]}';
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
        $this->ParentID = 0;

        // Referenzen auf die alten Konfigurations-Skripte der HTML-Tabellen (bis 3.02) entfernen
        foreach ($this->GetReferenceList() as $Reference) {
            $this->UnregisterReference($Reference);
        }
        $this->UnregisterVariable('_ReplyJSONData');

        if (is_array(static::$Namespace)) {
            $Lines = [];
            foreach (static::$Namespace as $Trigger) {
                $Lines[] = '.*"Namespace":"' . $Trigger . '".*';
            }
            $Line = implode('|', $Lines);
            $this->SetReceiveDataFilter('(' . $Line . ')');
        } else {
            $this->SetReceiveDataFilter('.*"Namespace":"' . static::$Namespace . '".*');
        }

        parent::ApplyChanges();

        if (IPS_GetKernelRunlevel() != KR_READY) {
            return;
        }

        $this->SetStatus(IS_ACTIVE);
        // Wenn Parent aktiv, dann Anmeldung an der Hardware bzw. Datenabgleich starten
        $this->RegisterParent();
        if ($this->HasActiveParent()) {
            $this->IOChangeState(IS_ACTIVE);
        } else {
            $this->IOChangeState(IS_INACTIVE);
        }
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
                $this->UnregisterMessage(0, IPS_KERNELSTARTED);
                $this->KernelReady();
                break;
        }
    }

    /**
     * Actionhandler der Statusvariablen und Formularaktionen. Interne SDK-Funktion.
     * Fehler (trigger_error) der Module werden hier per ModulErrorHandler an den Aufrufer ausgegeben.
     *
     * @access public
     * @param string $Ident Der Ident der Statusvariable bzw. Aktion.
     * @param mixed $Value Der angeforderte neue Wert.
     */
    public function RequestAction(string $Ident, mixed $Value): void
    {
        if ($this->IORequestAction($Ident, $Value)) {
            return;
        }
        set_error_handler([$this, 'ModulErrorHandler'], E_USER_NOTICE | E_USER_WARNING);
        try {
            $this->ActionHandler($Ident, $Value);
        } finally {
            restore_error_handler();
        }
    }

    ################## PUBLIC
    /**
     * IPS-Instanz-Funktion '*_RequestState'. Frage eine oder mehrere Properties eines Namespace ab.
     *
     * @access public
     * @param string $Ident Enthält den Names des "properties" welches angefordert werden soll.
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function RequestState(string $Ident): bool
    {
        if ($Ident == 'ALL') {
            return $this->RequestProperties(['properties' => static::$Properties]);
        }
        if ($Ident == 'PARTIAL') {
            return $this->RequestProperties(['properties' => static::$PartialProperties]);
        }
        if (!in_array($Ident, static::$Properties)) {
            trigger_error($this->Translate('Property not found.'), E_USER_NOTICE);
            return false;
        }
        return $this->RequestProperties(['properties' => [$Ident]]);
    }

    ################## Datapoints
    /**
     * Interne SDK-Funktion. Empfängt Datenpakete vom KodiSplitter.
     *
     * @access public
     * @param string $JSONString Das Datenpaket als JSON formatierter String.
     * @return bool true bei erfolgreicher Datenannahme, sonst false.
     */
    public function ReceiveData(string $JSONString): string
    {
        $Data = json_decode($JSONString);
        $KodiData = new Kodi_RPC_Data();
        $KodiData->CreateFromGenericObject($Data);
        if ($KodiData->Typ != Kodi_RPC_Data::$EventTyp) {
            return '';
        }

        $Event = $KodiData->GetEvent();

        $this->Decode($KodiData->Method, $Event);
        return '';
    }

    /**
     * Gibt Fehlermeldungen an den Aufrufer aus.
     *
     * @access protected
     */
    protected function ModulErrorHandler(int $errno, string $errstr): bool
    {
        echo $errstr . PHP_EOL;
        return true;
    }

    /**
     * Verarbeitet die Aktionen des Moduls. Wird von den Modulen überschrieben.
     *
     * @access protected
     * @param string $Ident Der Ident der Statusvariable bzw. Aktion.
     * @param mixed $Value Der angeforderte neue Wert.
     */
    protected function ActionHandler(string $Ident, mixed $Value): void
    {
        trigger_error($this->Translate('Invalid Ident.'), E_USER_NOTICE);
    }

    /**
     * Wird ausgeführt wenn der Kernel hochgefahren wurde.
     */
    protected function KernelReady(): void
    {
        $this->ApplyChanges();
    }

    protected function RegisterParent(): void
    {
        $SplitterId = $this->IORegisterParent();
        if ($SplitterId > 0) {
            $IOId = @IPS_GetInstance($SplitterId)['ConnectionID'];
            if ($IOId > 0) {
                $this->SetSummary(IPS_GetProperty($IOId, 'Host'));
                return;
            }
        }
        $this->SetSummary(('none'));
    }

    /**
     * Wird ausgeführt wenn sich der Status vom Parent ändert.
     * @access protected
     */
    protected function IOChangeState(int $State): void
    {
        $this->SendDebug('ParentChangeState', $State, 0);
        if ($State == IS_ACTIVE) {
            $this->RequestProperties(['properties' => static::$Properties]);
        }
    }

    ################## PRIVATE
    /**
     * Werte der Eigenschaften anfragen.
     *
     * @access protected
     * @param array $Params Enthält den Index "properties", in welchen alle anzufragenden Eigenschaften als Array enthalten sind.
     * @return bool true bei erfolgreicher Ausführung und dekodierung, sonst false.
     */
    protected function RequestProperties(array $Params): bool
    {
        if (count($Params['properties']) == 0) {
            return true;
        }
        $this->SendDebug('RequestProperties', implode(',', $Params['properties']), 0);
        $KodiData = new Kodi_RPC_Data(static::$Namespace, 'GetProperties', $Params);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        $this->Decode('GetProperties', $ret);
        return true;
    }

    /**
     * Muss überschieben werden. Dekodiert die empfangenen Events und Antworten auf 'GetProperties'.
     *
     * @abstract
     * @access protected
     * @param string $Method RPC-Funktion ohne Namespace
     * @param object $KodiPayload Der zu dekodierende Datensatz als Objekt.
     */
    abstract protected function Decode(string $Method, mixed $KodiPayload): void;

    /**
     * Liest eine Seite einer Liste aus einer Kodi-Datenbank (List.Limits).
     * Hintergrund: Instanz-Funktionen dürfen höchstens 1 MB zurückgeben, große Bibliotheken überschreiten das.
     *
     * @access protected
     * @param string $Method RPC-Methode im Namespace des Moduls, z.B. 'GetMovies'.
     * @param array $Fields Die anzufordernden Felder.
     * @param string $ResultKey Schlüssel der Liste in der Antwort, z.B. 'movies'.
     * @param int $Start Erster Eintrag (ab 0).
     * @param int $Count Anzahl der Einträge (ab 1).
     * @return array|bool Array mit 'total' (Gesamtzahl) und 'items' (Einträge der Seite) oder false bei Fehler.
     */
    protected function GetListPage(string $Method, array $Fields, string $ResultKey, int $Start, int $Count): false|array
    {
        if (($Start < 0) || ($Count < 1)) {
            trigger_error($this->Translate('Start must be 0 or higher and count must be 1 or higher.'), E_USER_NOTICE);
            return false;
        }
        $KodiData = new Kodi_RPC_Data(static::$Namespace, $Method, ['properties' => $Fields, 'limits' => ['start' => $Start, 'end' => $Start + $Count]]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return [
            'total' => (int) $ret->limits->total,
            'items' => property_exists($ret, $ResultKey) ? $KodiData->ToArray($ret->{$ResultKey}) : []
        ];
    }

    /**
     * Meldet den Aufruf einer veralteten Instanz-Funktion (Alias mit altem Namen).
     *
     * @access protected
     * @param string $OldFunction Veralteter Funktionsname inkl. Präfix.
     * @param string $NewFunction Neuer Funktionsname inkl. Präfix.
     */
    protected function DeprecatedFunction(string $OldFunction, string $NewFunction): void
    {
        // Kein trigger_error: Symcon verwirft dann den Rückgabewert der Instanz-Funktion (Aufrufer erhält false)
        $this->LogMessage(sprintf($this->Translate('%s is deprecated and will be removed in version 3.20. Use %s instead.'), $OldFunction, $NewFunction), KL_WARNING);
    }

    /**
     * Erzeugt ein lesbares Zeitformat.
     *
     * @access protected
     * @param object|int $Time Die zu formatierende Zeit als Kodi-Objekt oder als Sekunden.
     * @return string Gibt die formatierte Zeit zurück.
     */
    protected function ConvertTime(int|object $Time): string
    {
        if (is_object($Time)) {
            $Time->minutes = str_pad((string) $Time->minutes, 2, '00', STR_PAD_LEFT);
            $Time->seconds = str_pad((string) $Time->seconds, 2, '00', STR_PAD_LEFT);
            if ($Time->hours > 0) {
                return $Time->hours . ':' . $Time->minutes . ':' . $Time->seconds;
            }
            return $Time->minutes . ':' . $Time->seconds;
        }
        if (is_int($Time)) {
            // Dauer in Sekunden, rein rechnerisch (unabhängig von Zeitzone und Sommerzeit), Format wie bei Player.Time
            $Hours = intdiv($Time, 3600);
            $Minutes = str_pad((string) intdiv($Time % 3600, 60), 2, '0', STR_PAD_LEFT);
            $Seconds = str_pad((string) ($Time % 60), 2, '0', STR_PAD_LEFT);
            if ($Hours > 0) {
                return $Hours . ':' . $Minutes . ':' . $Seconds;
            }
            return $Minutes . ':' . $Seconds;
        }
        return (string) $Time;
    }

    /**
     * Holt das über $file übergebene Thumbnail vom Kodi-Webinterface, skaliert und konvertiert dieses.
     *
     * @access private
     * @param string $file Path zum Thumbnail im Kodi-Webserver
     */
    protected function GetThumbnail(string $file, int $SizeWidth = 0, int $SizeHeight = 0): string|false
    {
        if ($this->ParentID == 0) {
            return false;
        }
        if ($file == '') {
            return false;
        }

        // Kodi liefert über /image/ nur image://-Pfade zuverlässig (rohe http://- oder special://-Pfade ergeben 404).
        if (!str_starts_with($file, 'image://')) {
            $file = 'image://' . rawurlencode($file) . '/';
        }
        $ThumbBase64 = @KODIRPC_GetImageBase64($this->ParentID, $file);
        $ThumbRAW = is_string($ThumbBase64) ? base64_decode($ThumbBase64, true) : false;

        if ($ThumbRAW !== false) {
            $image = @imagecreatefromstring($ThumbRAW);
            if ($image !== false) {
                $width = imagesx($image);
                $height = imagesy($image);
                $FactorW = 1;
                $FactorH = 1;
                if ($SizeWidth > 0) {
                    if ($width > $SizeWidth) {
                        $FactorW = $width / $SizeWidth;
                    }
                }
                if ($SizeHeight > 0) {
                    if ($height > $SizeHeight) {
                        $FactorH = $height / $SizeHeight;
                    }
                }
                $factor = ($FactorH < $FactorW ? $FactorW : $FactorH);
                if ($factor != 1) {
                    $image = imagescale($image, (int) ($width / $factor), (int) ($height / $factor));
                }
                imagealphablending($image, false);
                imagesavealpha($image, true);
                ob_start();
                @imagepng($image);
                $ThumbRAW = ob_get_contents(); // read from buffer
                ob_end_clean(); // delete buffer
            }
        }

        return $ThumbRAW;
    }

    /**
     * Konvertiert $Data zu einem JSONString und versendet diese an den Splitter.
     *
     * @access protected
     * @param Kodi_RPC_Data $KodiData Zu versendende Daten.
     * @return null|array|object|mixed|KodiRPCException Objekt mit der Antwort. NULL im Fehlerfall.
     */
    protected function Send(Kodi_RPC_Data $KodiData): mixed
    {
        try {
            $JSONData = $KodiData->ToJSONString('{0222A902-A6FA-4E94-94D3-D54AA4666321}');
            if (!$this->HasActiveParent()) {
                throw new Exception($this->Translate('Instance has no active parent.'), E_USER_NOTICE);
            }
            $answer = $this->SendDataToParent($JSONData);
            $this->SendDebug('Send', $JSONData, 0);
            if ($answer === false) {
                $this->SendDebug('Receive', 'No valid answer', 0);
                return null;
            }
            $result = unserialize($answer);
            $this->SendDebug('Receive', $result, 0);
            return $result;
        } catch (Exception $exc) {
            trigger_error($exc->getMessage(), E_USER_NOTICE);
            return null;
        }
    }

    /**
     * Konvertiert $Data zu einem JSONString und versendet diese an den Splitter zum Direktversand.
     *
     * @access protected
     * @param Kodi_RPC_Data $KodiData Zu versendende Daten.
     * @return null|array|object|mixed|KodiRPCException Objekt mit der Antwort. NULL im Fehlerfall.
     */
    protected function SendDirect(Kodi_RPC_Data $KodiData): mixed
    {
        try {
            if (!$this->HasActiveParent()) {
                throw new Exception($this->Translate('Instance has no active parent.'), E_USER_NOTICE);
            }

            $SplitterInstance = IPS_GetInstance($this->ParentID);

            $Data = $KodiData->ToRawRPCJSONString();
            if (@IPS_GetProperty($SplitterInstance['ConnectionID'], 'Open') === false) {
                throw new Exception($this->Translate('Instance inactive.'), E_USER_NOTICE);
            }

            $Host = @IPS_GetProperty($SplitterInstance['ConnectionID'], 'Host');
            if ($Host == '') {
                return null;
            }

            $URI = $Host . ':' . IPS_GetProperty($this->ParentID, 'Webport') . '/jsonrpc';
            $UseBasisAuth = IPS_GetProperty($this->ParentID, 'BasisAuth');
            $User = IPS_GetProperty($this->ParentID, 'Username');
            $Pass = IPS_GetProperty($this->ParentID, 'Password');

            $header[] = 'Accept: application/json';
            $header[] = 'Cache-Control: max-age=0';
            $header[] = 'Connection: close';
            $header[] = 'Accept-Charset: UTF-8';
            $header[] = 'Content-type: application/json;charset="UTF-8"';
            $ch = curl_init('http://' . $URI);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_FAILONERROR, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $Data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 1000);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, 300000);
            if ($UseBasisAuth) {
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($ch, CURLOPT_USERPWD, $User . ':' . $Pass);
            }

            $this->SendDebug('Send Direct', $Data, 0);
            $result = curl_exec($ch);

            if ($result === false) {
                throw new Exception($this->Translate('Kodi unreachable.'), E_USER_NOTICE);
            }
            $this->SendDebug('Receive Direct', $result, 0);

            $ReplayKodiData = new Kodi_RPC_Data();
            $ReplayKodiData->CreateFromJSONString($result);
            $ret = $ReplayKodiData->GetResult();
            if (is_a($ret, 'KodiRPCException')) {
                throw $ret;
            }
            $this->SendDebug('Receive Direct', $ReplayKodiData, 0);
            return $ret;
        } catch (KodiRPCException $ex) {
            $this->SendDebug('Receive Direct', $ex, 0);
            trigger_error(sprintf($this->Translate('Error (%d) on %s: %s'), $ex->getCode(), $KodiData->GetCalledMethod(), $ex->getMessage()), E_USER_NOTICE);
        } catch (Exception $ex) {
            $this->SendDebug('Receive Direct', $ex->getMessage() . "\r\n" . $ex->getTraceAsString(), 0);
            trigger_error($ex->getMessage(), E_USER_NOTICE);
        }
        return null;
    }

    /**
     * Setzte eine IPS-Variable vom Typ bool auf den Wert von $value
     *
     * @access protected
     * @param string $Ident Ident der Statusvariable.
     * @param bool $value Neuer Wert der Statusvariable.
     * @return bool true wenn der neue Wert vom alten abweicht, sonst false.
     */
    protected function SetValueBoolean(string $Ident, bool $value): bool
    {
        $id = @$this->GetIDForIdent($Ident);
        if ($id === false) {
            return false;
        }
        if (GetValueBoolean($id) != $value) {
            $this->SetValue($Ident, $value);
            return true;
        }
        return false;
    }

    /**
     * Setzte eine IPS-Variable vom Typ float auf den Wert von $value. Versteckt nicht benutzte Variablen anhand der Ident.
     *
     * @access protected
     * @param string $Ident Ident der Statusvariable.
     * @param float $value Neuer Wert der Statusvariable.
     * @return bool true wenn der neue Wert vom alten abweicht, sonst false.
     */
    protected function SetValueFloat(string $Ident, float $value): bool
    {
        $id = @$this->GetIDForIdent($Ident);
        if ($id === false) {
            return false;
        }
        if (GetValueFloat($id) != $value) {
            $this->SetValue($Ident, $value);
            return true;
        }
        return false;
    }
    /**
     * Setzte eine IPS-Variable vom Typ integer auf den Wert von $value. Versteckt nicht benutzte Variablen anhand der Ident.
     *
     * @access protected
     * @param string $Ident Ident der Statusvariable.
     * @param int $value Neuer Wert der Statusvariable.
     * @return bool true wenn der neue Wert vom alten abweicht, sonst false.
     */
    protected function SetValueInteger(string $Ident, int $value): bool
    {
        $id = @$this->GetIDForIdent($Ident);
        if ($id === false) {
            return false;
        }
        if (GetValueInteger($id) != $value) {
            $this->SetValue($Ident, $value);
            return true;
        }
        return false;
    }
    /**
     * Setzte eine IPS-Variable vom Typ string auf den Wert von $value. Versteckt nicht benutzte Variablen anhand der Ident.
     *
     * @access protected
     * @param string $Ident Ident der Statusvariable.
     * @param string $value Neuer Wert der Statusvariable.
     * @return bool true wenn der neue Wert vom alten abweicht, sonst false.
     */
    protected function SetValueString(string $Ident, string $value): bool
    {
        $id = @$this->GetIDForIdent($Ident);
        if ($id === false) {
            return false;
        }
        if (GetValueString($id) != $value) {
            $this->SetValue($Ident, $value);
            return true;
        }
        return false;
    }

    /**
     * Löscht ein nicht mehr benötigtes Script.
     * @access protected
     * @param string $Ident Der Ident des Script.
     */
    protected function UnregisterScript(string $Ident): void
    {
        $sid = @$this->GetIDForIdent($Ident);
        if ($sid === false) {
            return;
        }
        if (!IPS_ScriptExists($sid)) {
            return;
        } //bail out
        IPS_DeleteScript($sid, true);
    }
}
