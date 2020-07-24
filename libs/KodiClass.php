<?php

declare(strict_types=1);
/** @addtogroup kodi
 * @{
 *
 * @package       Kodi
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2020 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       2.10
 * @example <b>Ohne</b>
 */
eval('declare(strict_types=1);namespace KodiBase {?>' . file_get_contents(__DIR__ . '/../libs/helper/BufferHelper.php') . '}');
eval('declare(strict_types=1);namespace KodiBase {?>' . file_get_contents(__DIR__ . '/../libs/helper/ParentIOHelper.php') . '}');
eval('declare(strict_types=1);namespace KodiBase {?>' . file_get_contents(__DIR__ . '/../libs/helper/VariableProfileHelper.php') . '}');
eval('declare(strict_types=1);namespace KodiBase {?>' . file_get_contents(__DIR__ . '/../libs/helper/WebhookHelper.php') . '}');

require_once __DIR__ . '/DebugHelper.php';  // diverse Klassen
require_once __DIR__ . '/KodiRPCClass.php';  // diverse Klassen

/**
 * Basisklasse für alle Kodi IPS-Instanzklassen.
 * Erweitert IPSModule.
 *
 * @abstract
 * @package       Kodi
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2020 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       2.10
 * @example <b>Ohne</b>
 * @property int $ParentID
 * @property string $WebHookSecret
 */
abstract class KodiBase extends IPSModule
{
    use \KodiBase\VariableProfileHelper,
        \KodiBase\WebhookHelper,
        \KodiBase\DebugHelper,
        \KodiBase\BufferHelper,
        \KodiBase\InstanceStatus {
        \KodiBase\InstanceStatus::MessageSink as IOMessageSink;
        \KodiBase\InstanceStatus::RegisterParent as IORegisterParent;
        \KodiBase\InstanceStatus::RequestAction as IORequestAction;
    }
    /**
     * RPC-Namespace
     *
     * @access private
     * @var string
     */
    protected static $Namespace;

    /**
     * Nur einige Properties des RPC-Namespace
     *
     * @access private
     * @var array
     */
    protected static $PartialProperties;

    /**
     * Alle Properties des RPC-Namespace
     *
     * @access private
     * @var array
     */
    protected static $Properties;

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Create()
    {
        parent::Create();
        $this->ConnectParent('{D2F106B5-4473-4C19-A48F-812E8BAA316C}');
        $this->ParentID = 0;
        $this->WebHookSecret = '';
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function ApplyChanges()
    {
        $this->RegisterMessage(0, IPS_KERNELSTARTED);
        $this->RegisterMessage($this->InstanceID, FM_CONNECT);
        $this->RegisterMessage($this->InstanceID, FM_DISCONNECT);
        $this->ParentID = 0;

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
    public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    {
        $this->IOMessageSink($TimeStamp, $SenderID, $Message, $Data);

        switch ($Message) {
            case IPS_KERNELSTARTED:
                $this->KernelReady();
                break;
        }
    }

    public function RequestAction($Ident, $Value)
    {
        if ($this->IORequestAction($Ident, $Value)) {
            return true;
        }
        return false;
    }

    ################## PUBLIC
    /**
     * IPS-Instanz-Funktion '*_RequestState'. Frage eine oder mehrere Properties eines Namespace ab.
     *
     * @access public
     * @param string $Ident Enthält den Names des "properties" welches angefordert werden soll.
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function RequestState(string $Ident)
    {
        if ($Ident == 'ALL') {
            return $this->RequestProperties(['properties' => static::$Properties]);
        }
        if ($Ident == 'PARTIAL') {
            return $this->RequestProperties(['properties' => static::$PartialProperties]);
        }
        if (!in_array($Ident, static::$Properties)) {
            trigger_error('Property not found.');
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
    public function ReceiveData($JSONString)
    {
        $Data = json_decode($JSONString);
        $KodiData = new Kodi_RPC_Data();
        $KodiData->CreateFromGenericObject($Data);
        if ($KodiData->Typ != Kodi_RPC_Data::$EventTyp) {
            return false;
        }

        $Event = $KodiData->GetEvent();
        //$this->SendDebug('Event', $Event, 0);

        $this->Decode($KodiData->Method, $Event);
        return false;
    }

    /**
     * Wird ausgeführt wenn der Kernel hochgefahren wurde.
     */
    protected function KernelReady()
    {
        $this->ApplyChanges();
    }

    protected function RegisterParent()
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
    protected function IOChangeState($State)
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
    protected function RequestProperties(array $Params)
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
    abstract protected function Decode($Method, $KodiPayload);
    /**
     * Erzeugt ein lesbares Zeitformat.
     *
     * @access protected
     * @param object|int $Time Die zu formatierende Zeit als Kodi-Objekt oder als Sekunden.
     * @return string Gibt die formatierte Zeit zurück.
     */
    protected function ConvertTime($Time)
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
            if ($Time > 3600) {
                return date('H:i:s', $Time - (gettimeofday()['dsttime'] * 3600));
            } else {
                return date('i:s', $Time);
            }
        }
    }

    /**
     * Liefert den Header der HTML-Tabelle.
     *
     * @access private
     * @param array $Config Die Kofiguration der Tabelle
     * @return string HTML-String
     */
    protected function GetTableHeader($Config)
    {
        $html = '';
        // JS Rückkanal erzeugen
        $html .= '<script>
//window.xhrGet' . $this->InstanceID . '=
    function xhrGet' . $this->InstanceID . '(o)
{
    var HTTP = new XMLHttpRequest();
    HTTP.open(\'GET\',o.url,true);
    HTTP.send();
    HTTP.addEventListener(\'load\', function(event)
    {
        if (HTTP.status >= 200 && HTTP.status < 300)
        {
            if (HTTP.responseText != \'OK\')
                sendError' . $this->InstanceID . '(HTTP.responseText);
        } else {
            sendError' . $this->InstanceID . '(HTTP.statusText);
        }
    });
};

function sendError' . $this->InstanceID . '(data)
{
var notify = document.getElementsByClassName("ipsNotifications")[0];
var newDiv = document.createElement("div");
newDiv.innerHTML =\'<div style="height:auto; visibility: hidden; overflow: hidden; transition: height 500ms ease-in 0s" class="ipsNotification"><div class="spacer"></div><div class="message icon error" onclick="document.getElementsByClassName(\\\'ipsNotifications\\\')[0].removeChild(this.parentNode);"><div class="ipsIconClose"></div><div class="content"><div class="title">Fehler</div><div class="text">\' + data + \'</div></div></div></div>\';
if (notify.childElementCount == 0)
	var thisDiv = notify.appendChild(newDiv.firstChild);
else
	var thisDiv = notify.insertBefore(newDiv.firstChild,notify.childNodes[0]);
var newheight = window.getComputedStyle(thisDiv, null)["height"];
thisDiv.style.height = "0px";
thisDiv.style.visibility = "visible";
function sleep (time) {
  return new Promise((resolve) => setTimeout(resolve, time));
}
sleep(10).then(() => {
	thisDiv.style.height = newheight;
})
}
</script>';
        // Button Styles erzeugen
        if (isset($Config['Button'])) {
            $html .= '<style>' . PHP_EOL;
            foreach ($Config['Button'] as $Class => $Button) {
                $html .= '.' . $Class . ' {' . $Button . '}' . PHP_EOL;
            }
            $html .= '</style>' . PHP_EOL;
        }
        // Kopf der Tabelle erzeugen
        $html .= '<table style="' . $Config['Style']['T'] . '">' . PHP_EOL;
        $html .= '<colgroup>' . PHP_EOL;
        foreach ($Config['Spalten'] as $Index => $Value) {
            $html .= '<col width="' . $Config['Breite'][$Index] . '" />' . PHP_EOL;
        }
        $html .= '</colgroup>' . PHP_EOL;
        $html .= '<thead style="' . $Config['Style']['H'] . '">' . PHP_EOL;
        $html .= '<tr style="' . $Config['Style']['HR'] . '">';
        foreach ($Config['Spalten'] as $Index => $Value) {
            $html .= '<th style="' . $Config['Style']['HF' . $Index] . '">' . $Value . '</th>';
        }
        $html .= '</tr>' . PHP_EOL;
        $html .= '</thead>' . PHP_EOL;
        $html .= '<tbody style="' . $Config['Style']['B'] . '">' . PHP_EOL;
        return $html;
    }

    /**
     * Liefert den Footer der HTML-Tabelle.
     *
     * @access private
     * @return string HTML-String
     */
    protected function GetTableFooter()
    {
        $html = '</tbody>' . PHP_EOL;
        $html .= '</table>' . PHP_EOL;
        return $html;
    }

    /**
     * Holt das über $file übergebene Thumbnail vom Kodi-Webinterface, skaliert und konvertiert dieses.
     *
     * @access private
     * @param string $file Path zum Thumbnail im Kodi-Webserver
     */
    protected function GetThumbnail(string $file, $SizeWidth = 0, $SizeHeight = 0)
    {
        if ($this->ParentID == 0) {
            return false;
        }
        if ($file == '') {
            return false;
        }

        $ThumbRAW = @KODIRPC_GetImage($this->ParentID, $file);

        if ($ThumbRAW !== false) {
            $image = @imagecreatefromstring($ThumbRAW);
            if ($image !== false) {
                $width = imagesx($image);
                $height = imagesy($image);
                $factorw = 1;
                $factorh = 1;
                if ($SizeWidth > 0) {
                    if ($width > $SizeWidth) {
                        $factorw = $width / $SizeWidth;
                    }
                }
                if ($SizeHeight > 0) {
                    if ($height > $SizeHeight) {
                        $factorh = $height / $SizeHeight;
                    }
                }
                $factor = ($factorh < $factorw ? $factorw : $factorh);
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
     * @return Kodi_RPC_Data Objekt mit der Antwort. NULL im Fehlerfall.
     */
    protected function Send(Kodi_RPC_Data $KodiData)
    {
        try {
            $JSONData = $KodiData->ToJSONString('{0222A902-A6FA-4E94-94D3-D54AA4666321}');
            if (!$this->HasActiveParent()) {
                throw new Exception('Instance has no active parent.', E_USER_NOTICE);
            }
            $anwser = $this->SendDataToParent($JSONData);
            $this->SendDebug('Send', $JSONData, 0);
            if ($anwser === false) {
                $this->SendDebug('Receive', 'No valid answer', 0);
                return null;
            }
            $result = unserialize($anwser);
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
     * @return Kodi_RPC_Data Objekt mit der Antwort. NULL im Fehlerfall.
     */
    protected function SendDirect(Kodi_RPC_Data $KodiData)
    {
        try {
            if (!$this->HasActiveParent()) {
                throw new Exception('Instance has no active parent.', E_USER_NOTICE);
            }

            $SplitterInstance = IPS_GetInstance($this->ParentID);

            $Data = $KodiData->ToRawRPCJSONString();
            if (@IPS_GetProperty($SplitterInstance['ConnectionID'], 'Open') === false) {
                throw new Exception('Instance inactiv.', E_USER_NOTICE);
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
            curl_close($ch);

            if ($result === false) {
                throw new Exception('Kodi unreachable', E_USER_NOTICE);
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
            trigger_error('Error (' . $ex->getCode() . '): ' . $ex->getMessage() . ' in ' . get_called_class(), E_USER_NOTICE);
        } catch (Exception $ex) {
            $this->SendDebug('Receive Direct', $ex->getMessage(), 0);
            trigger_error($ex->getMessage(), $ex->getCode());
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
    protected function SetValueBoolean($Ident, $value)
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
     * Setzte eine IPS-Variable vom Typ integer auf den Wert von $value. Versteckt nicht benutzte Variablen anhand der Ident.
     *
     * @access protected
     * @param string $Ident Ident der Statusvariable.
     * @param int $value Neuer Wert der Statusvariable.
     * @return bool true wenn der neue Wert vom alten abweicht, sonst false.
     */
    protected function SetValueInteger($Ident, $value)
    {
        $id = @$this->GetIDForIdent($Ident);
        if ($id === false) {
            return false;
        }
        if (GetValueInteger($id) != $value) {
            if (!(($Ident[0] == '_') || ($Ident == 'speed') || ($Ident == 'repeat') || (IPS_GetVariable($id)['VariableAction'] != 0))) {
                if (($value <= 0) && (!IPS_GetObject($id)['ObjectIsHidden'])) {
                    //ToDo: Prüfen und IPS_SetHidden entsorgen.
                    IPS_SetHidden($id, true);
                }
                if (($value > 0) && (IPS_GetObject($id)['ObjectIsHidden'])) {
                    //ToDo: Prüfen und IPS_SetHidden entsorgen.
                    IPS_SetHidden($id, false);
                }
            }

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
    protected function SetValueString($Ident, $value)
    {
        $id = @$this->GetIDForIdent($Ident);
        if ($id === false) {
            return false;
        }
        if (GetValueString($id) != $value) {
            if ($Ident[0] != '_') {
                if ((($value == '') || ($value == 'unknown')) && (!IPS_GetObject($id)['ObjectIsHidden'])) {
                    //ToDo: Prüfen und IPS_SetHidden entsorgen.
                    IPS_SetHidden($id, true);
                }
                if ((($value != '') && ($value != 'unknown')) && (IPS_GetObject($id)['ObjectIsHidden'])) {
                    //ToDo: Prüfen und IPS_SetHidden entsorgen.
                    IPS_SetHidden($id, false);
                }
            }
            $this->SetValue($Ident, $value);
            return true;
        }
        return false;
    }

    /**
     * Löscht ein nicht mehr benötigtes Script.
     * @access protected
     * @param string $Ident Der Ident des Scriptes.
     */
    protected function UnregisterScript($Ident)
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

/** @} */
