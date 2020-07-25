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
 * @version       2.90
 *
 */
require_once __DIR__ . '/../libs/KodiClass.php';  // diverse Klassen

/**
 * KodiDeviceSystem Klasse für den Namespace System der KODI-API.
 * Erweitert KodiBase.
 *
 * @package       Kodi
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2020 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       2.01
 * @example <b>Ohne</b>
 */
class KodiDeviceSystem extends KodiBase
{
    /**
     * RPC-Namespace
     *
     * @access private
     * @var string
     * @value 'Application'
     */
    protected static $Namespace = 'System';

    /**
     * Alle Properties des RPC-Namespace
     *
     * @access private
     * @var array
     */
    protected static $Properties = [
        'canshutdown',
        'canhibernate',
        'cansuspend',
        'canreboot'
    ];

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Create()
    {
        parent::Create();
        $this->RegisterPropertyInteger('PowerScript', 0);
        $this->RegisterPropertyInteger('PowerOff', 0);
        $this->RegisterPropertyInteger('PreSelectScript', 0);
        $this->RegisterPropertyString('MACAddress', '');
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function ApplyChanges()
    {
        $this->RegisterProfileIntegerEx('Action.Kodi', '', '', '', [
            [0, $this->Translate('Execute'), '', -1]
        ]);

        switch ($this->ReadPropertyInteger('PreSelectScript')) {
            case 0:
                $ID = 0;
                break;
            case 1:
                $ID = $this->RegisterScript('WOLScript', 'Power ON', $this->CreateWOLScript(), -1);
                break;
            case 2:
                $ID = $this->RegisterScript('WOLScript', 'Power ON', $this->CreateFBPScript(), -1);
                break;
        }
        if ($ID > 0) {
            // ToDo. Anstelle von Property einen Action-Button benutzen.
            IPS_SetHidden($ID, true);
            IPS_SetProperty($this->InstanceID, 'PowerScript', $ID);
            IPS_SetProperty($this->InstanceID, 'PreSelectScript', 0);
            IPS_Applychanges($this->InstanceID);
            return true;
        }
        $ScriptID = $this->ReadPropertyInteger('PowerScript');
        if ($ScriptID > 0) {
            $this->RegisterReference($ScriptID);
        }
        $this->RegisterVariableBoolean('Power', 'Power', '~Switch', 0);
        $this->EnableAction('Power');
        $this->RegisterVariableInteger('suspend', 'Standby', 'Action.Kodi', 1);
        $this->EnableAction('suspend');
        $this->RegisterVariableInteger('hibernate', $this->Translate('Hibernate'), 'Action.Kodi', 2);
        $this->EnableAction('hibernate');
        $this->RegisterVariableInteger('reboot', $this->Translate('Reboot'), 'Action.Kodi', 3);
        $this->EnableAction('reboot');
        $this->RegisterVariableInteger('shutdown', $this->Translate('Shutdown'), 'Action.Kodi', 4);
        $this->EnableAction('shutdown');
        $this->RegisterVariableInteger('ejectOpticalDrive', $this->Translate('Eject optical drive'), 'Action.Kodi', 5);
        $this->EnableAction('ejectOpticalDrive');
        $this->RegisterVariableBoolean('LowBatteryEvent', $this->Translate('Low battery event'), '', 6);
        parent::ApplyChanges();
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
        if (parent::RequestAction($Ident, $Value)) {
            return true;
        }
        switch ($Ident) {
            case 'Power':
                return $this->Power($Value);
            case 'shutdown':
            case 'reboot':
            case 'hibernate':
            case 'suspend':
            case 'ejectOpticalDrive':
                return $this->{ucfirst($Ident)}();
            default:
                trigger_error('Invalid Ident.', E_USER_NOTICE);
                break;
        }
    }

    ################## PUBLIC
    /**
     * IPS-Instanz-Funktion 'KODISYS_Power'. Schaltet Kodi ein oder aus. Einschalten erfolgt per hinterlegten PHP-Script in der Instanz. Der Modus für das Ausschalten ist ebenfalls in der Instanz zu konfigurieren.
     *
     * @access public
     * @param bool $Value True für Einschalten, False für Ausschalten.
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function Power(bool $Value)
    {
        if (!is_bool($Value)) {
            trigger_error('Value must be boolean', E_USER_NOTICE);
            return false;
        }

        if ($Value) {
            return $this->WakeUp();
        } else {
            switch ($this->ReadPropertyInteger('PowerOff')) {
                case 0:
                    return $this->Shutdown();
                case 1:
                    return $this->Hibernate();
                case 2:
                    return $this->Suspend();
                default:
                    return false;
            }
        }
    }

    /**
     * IPS-Instanz-Funktion 'KODISYS_WakeUp'. Schaltet 'Kodi' ein.
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function WakeUp()
    {
        $ScriptID = $this->ReadPropertyInteger('PowerScript');
        if ($ScriptID > 0) {
            if (!IPS_ScriptExists($ScriptID)) {
                return false;
            }

            if (IPS_RunScriptWaitEx($ScriptID, ['SENDER' => 'Kodi.System']) === '') {
                $this->SetValueBoolean('Power', true);
                return true;
            }
            trigger_error($this->Translate('Error on execute PowerOn-Script.'), E_USER_NOTICE);
        } else {
            trigger_error($this->Translate('Invalid PowerScript for power on.'), E_USER_NOTICE);
        }
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODISYS_Shutdown'. Führt einen Shutdown auf Betriebssystemebene aus.
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function Shutdown()
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Shutdown();
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret === 'OK') {
            $this->SetValueBoolean('Power', false);
            return true;
        }
        trigger_error($this->Translate('Error on send shutdown'), E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODISYS_Hibernate'. Führt einen Hibernate auf Betriebssystemebene aus.
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function Hibernate()
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Hibernate();
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret === 'OK') {
            $this->SetValueBoolean('Power', false);
            return true;
        }
        trigger_error($this->Translate('Error on send hibernate'), E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODISYS_Suspend'. Führt einen Suspend auf Betriebssystemebene aus.
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function Suspend()
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Suspend();
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret === 'OK') {
            $this->SetValueBoolean('Power', false);
            return true;
        }
        trigger_error($this->Translate('Error on send suspend'), E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODISYS_Reboot'. Führt einen Reboot auf Betriebssystemebene aus.
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function Reboot()
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Reboot();
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret === 'OK') {
            $this->SetValueBoolean('Power', false);
            return true;
        }
        trigger_error($this->Translate('Error on send reboot'), E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODISYS_EjectOpticalDrive'. Öffnet das Optische Laufwerk.
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function EjectOpticalDrive()
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->EjectOpticalDrive();
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret === 'OK') {
            return true;
        }
        trigger_error($this->Translate('Error on eject optical drive'), E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODISYS_RequestState'. Frage eine oder mehrere Properties ab.
     *
     * @access public
     * @param string $Ident Enthält den Names des "properties" welches angefordert werden soll.
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function RequestState(string $Ident)
    {
        return parent::RequestState($Ident);
    }

    /**
     * Dekodiert die empfangenen Events und Antworten auf 'GetProperties'.
     *
     * @access protected
     * @param string $Method RPC-Funktion ohne Namespace
     * @param object $KodiPayload Der zu dekodierende Datensatz als Objekt.
     */
    protected function Decode($Method, $KodiPayload)
    {
        switch ($Method) {
            case 'GetProperties':
                foreach ($KodiPayload as $param => $value) {
                    // ToDo: Wofür war das?
                    IPS_SetHidden($this->GetIDForIdent(substr($param, 3)), !$value);
                }
                break;
            case 'Power':
                $this->SetValueBoolean('Power', $KodiPayload);
                break;
            case 'OnLowBattery':
                $this->SetValue('LowBatteryEvent', true);
                break;
            case 'OnQuit':
            case 'OnRestart':
            case 'OnSleep':
                $this->SetValueBoolean('Power', false);
                break;
            case 'OnWake':
                $this->SetValueBoolean('Power', true);
                break;
        }
    }

    ################## PRIVATE
    /**
     * Liest den String auf der Instanz-Eigenschaft MACAddress und konvertiert sie in ein bereinigtes Format.
     *
     * @access private
     * @result string Die bereinigte Adresse.
     */
    private function GetMac()
    {
        $Address = $this->ReadPropertyString('MACAddress');
        $Address = str_replace('-', '', $Address);
        $Address = str_replace(':', '', $Address);
        if (strlen($Address) == 12) {
            return '"' . strtoupper($Address) . '"';
        }
        return '"00AABB112233" /* Platzhalter für richtige Adresse */';
    }

    /**
     * Liefert einen PHP-Code als Vorlage für das Einschalten von Kodi per WOL der FritzBox. Unter Verwendung des FritzBox-Project.
     *
     * @access private
     * @result string PHP-Code
     */
    private function CreateFBPScript()
    {
        $Script = '<?php
$mac = ' . $this->GetMac() . ' ;
$FBScript = 0;  /* Hier die ID von dem Script [FritzBox Project\Scripte\Aktions & Auslese-Script Host] eintragen */

if ($_IPS["SENDER"] <> "Kodi.System")
{
	echo "Dieses Script kann nicht direkt ausgeführt werden!";
	return;
}
   echo IPS_RunScriptWaitEx ($FBScript,array("SENDER"=>"RequestAction","IDENT"=>$mac,"VALUE"=>true));
';
        return $Script;
    }

    /**
     * Liefert einen PHP-Code als Vorlage für das Einschalten von Kodi per WOL aus PHP herraus.
     *
     * @access private
     * @result string PHP-Code
     */
    private function CreateWOLScript()
    {
        $Script = '<?php
$mac = ' . $this->GetMac() . ' ;
if ($_IPS["SENDER"] <> "Kodi.System")
{
	echo "Dieses Script kann nicht direkt ausgeführt werden!";
	return;
}

$ip = "255.255.255.255"; // Broadcast adresse
return wake($ip,$mac);

function wake($ip, $mac)
{
  $nic = fsockopen("udp://" . $ip, 15);
  if($nic)
  {
    $packet = "";
    for($i = 0; $i < 6; $i++)
       $packet .= chr(0xFF);
    for($j = 0; $j < 16; $j++)
    {
      for($k = 0; $k < 6; $k++)
      {
        $str = substr($mac, $k * 2, 2);
        $dec = hexdec($str);
        $packet .= chr($dec);
      }
    }
    $ret = fwrite($nic, $packet);
    fclose($nic);
    if ($ret)
    {
      echo "";
      return true;
    }
  }
  echo "ERROR";
  return false;
}  
';
        return $Script;
    }
}

/** @} */
