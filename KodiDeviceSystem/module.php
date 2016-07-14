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
 * KodiDeviceSystem Klasse für den Namespace System der KODI-API.
 * Erweitert KodiBase.
 *
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
    static $Namespace = 'System';

    /**
     * Alle Properties des RPC-Namespace
     * 
     * @access private
     * @var array 
     */
    static $Properties = array(
        "canshutdown",
        "canhibernate",
        "cansuspend",
        "canreboot"
    );

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
        switch ($this->ReadPropertyInteger('PreSelectScript'))
        {
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
        if ($ID > 0)
        {
            IPS_SetHidden($ID, true);
            IPS_SetProperty($this->InstanceID, 'PowerScript', $ID);
            IPS_SetProperty($this->InstanceID, 'PreSelectScript', 0);
            IPS_Applychanges($this->InstanceID);
            return true;
        }
        $this->RegisterVariableBoolean("Power", "Power", "~Switch", 0);
        $this->EnableAction("Power");
        $this->RegisterVariableInteger("suspend", "Standby", "Action.Kodi", 1);
        $this->EnableAction("suspend");
        $this->RegisterVariableInteger("hibernate", "Ruhezustand", "Action.Kodi", 2);
        $this->EnableAction("hibernate");
        $this->RegisterVariableInteger("reboot", "Neustart", "Action.Kodi", 3);
        $this->EnableAction("reboot");
        $this->RegisterVariableInteger("shutdown", "Herunterfahren", "Action.Kodi", 4);
        $this->EnableAction("shutdown");
        $this->RegisterVariableInteger("ejectOpticalDrive", "Laufwerk öffnen", "Action.Kodi", 5);
        $this->EnableAction("ejectOpticalDrive");
        $this->RegisterVariableBoolean("LowBatteryEvent", "Batterie leer Event", "", 6);
        parent::ApplyChanges();
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
        if (strlen($Address) == 12)
            return '"'.strtoupper($Address) . '"';
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
        $Script = '<?
$mac = ' . $this->GetMac() . ' ;
$FBScript = 0;  /* Hier die ID von dem Script [FritzBox Project\Scripte\Aktions & Auslese-Script Host] eintragen */

if ($_IPS["SENDER"] <> "Kodi.System")
{
	echo "Dieses Script kann nicht direkt ausgeführt werden!";
	return;
}
   echo IPS_RunScriptWaitEx ($FBScript,array("SENDER"=>"RequestAction","IDENT"=>$mac,"VALUE"=>true));
?>';
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
        $Script = '<?
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
?>';
        return $Script;
    }

    /**
     * Dekodiert die empfangenen Events und Anworten auf 'GetProperties'.
     *
     * @access protected
     * @param string $Method RPC-Funktion ohne Namespace
     * @param object $KodiPayload Der zu dekodierende Datensatz als Objekt.
     */
    protected function Decode($Method, $KodiPayload)
    {
        
        switch ($Method)
        {
            case 'GetProperties':
                foreach ($KodiPayload as $param => $value)
                {
                    IPS_SetHidden($this->GetIDForIdent(substr($param, 3)), !$value);
                }
                break;
            case 'Power':
                $this->SetValueBoolean('Power', $KodiPayload);
                break;
            case 'OnLowBattery':
                IPS_SetValueBoolean($this->GetIDForIdent('LowBatteryEvent'), true);
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

################## ActionHandler

    /**
     * Actionhandler der Statusvariablen. Interne SDK-Funktion.
     * 
     * @access public
     * @param string $Ident Der Ident der Statusvariable.
     * @param boolean|float|integer|string $Value Der angeforderte neue Wert.
     */
    public function RequestAction($Ident, $Value)
    {
        switch ($Ident)
        {
            case "Power":
                if (!$this->Power($Value))
                    trigger_error('Error on send powerstate', E_USER_NOTICE);
                break;
            case "shutdown":
            case "reboot":
            case "hibernate":
            case "suspend":
            case "ejectOpticalDrive":
                if (!$this->{ucfirst($Ident)}())
                    trigger_error('Error on send ' . ucfirst($Ident), E_USER_NOTICE);
                break;
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
     * @param boolean $Value True für Einschalten, False für Ausschalten.
     * @return boolean true bei erfolgreicher Ausführung, sonst false.
     */
    public function Power(boolean $Value)
    {
        if (!is_bool($Value))
        {
            trigger_error('Value must be boolean', E_USER_NOTICE);
            return false;
        }

        if ($Value)
        {
            return $this->WakeUp();
        }
        else
        {
            switch ($this->ReadPropertyInteger('PowerOff'))
            {
                case 0:
                    $ret = $this->Shutdown();
                    break;
                case 1:
                    $ret = $this->Hibernate();
                    break;
                case 2:
                    $ret = $this->Suspend();
                    break;
                default:
                    $ret = false;
                    break;
            }
            if (!$ret)
            {
                trigger_error('Error on send power off', E_USER_NOTICE);
            }
            return $ret;
        }
    }

    /**
     * IPS-Instanz-Funktion 'KODISYS_WakeUp'. Schaltet 'Kodi' ein.
     *
     * @access public
     * @return boolean true bei erfolgreicher Ausführung, sonst false.
     */
    public function WakeUp()
    {
        $ID = $this->ReadPropertyInteger('PowerScript');
        if ($ID > 0)
        {
            if (IPS_RunScriptWaitEx($ID, array("SENDER" => "Kodi.System")) == "")
            {
                $this->SetValueBoolean('Power', true);
                return true;
            }
            trigger_error('Error on execute PowerOn-Script.', E_USER_NOTICE);
        }
        else
            trigger_error('Invalid PowerScript for power on.', E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODISYS_Shutdown'. Führt einen Shutdown auf Betriebssystemebene aus.
     *
     * @access public
     * @return boolean true bei erfolgreicher Ausführung, sonst false.
     */
    public function Shutdown()
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Shutdown();
        $ret = $this->Send($KodiData);
        if (is_null($ret))
            return false;
        if ($ret === 'OK')
        {
            $this->SetValueBoolean('Power', false);
            return true;
        }
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODISYS_Hibernate'. Führt einen Hibernate auf Betriebssystemebene aus.
     *
     * @access public
     * @return boolean true bei erfolgreicher Ausführung, sonst false.
     */
    public function Hibernate()
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Hibernate();
        $ret = $this->Send($KodiData);
        if (is_null($ret))
            return false;
        if ($ret === 'OK')
        {
            $this->SetValueBoolean('Power', false);
            return true;
        }
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODISYS_Suspend'. Führt einen Suspend auf Betriebssystemebene aus.
     *
     * @access public
     * @return boolean true bei erfolgreicher Ausführung, sonst false.
     */
    public function Suspend()
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Suspend();
        $ret = $this->Send($KodiData);
        if (is_null($ret))
            return false;
        if ($ret === 'OK')
        {
            $this->SetValueBoolean('Power', false);
            return true;
        }
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODISYS_Reboot'. Führt einen Reboot auf Betriebssystemebene aus.
     *
     * @access public
     * @return boolean true bei erfolgreicher Ausführung, sonst false.
     */
    public function Reboot()
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Reboot();
        $ret = $this->Send($KodiData);
        if (is_null($ret))
            return false;
        if ($ret === 'OK')
        {
            $this->SetValueBoolean('Power', false);
            return true;
        }
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODISYS_EjectOpticalDrive'. Öffnet das Optische Laufwerk.
     *
     * @access public
     * @return boolean true bei erfolgreicher Ausführung, sonst false.
     */
    public function EjectOpticalDrive()
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->EjectOpticalDrive();
        $ret = $this->Send($KodiData);
        if (is_null($ret))
            return false;
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODISYS_RequestState'. Frage eine oder mehrere Properties ab.
     *
     * @access public
     * @param string $Ident Enthält den Names des "properties" welches angefordert werden soll.
     * @return boolean true bei erfolgreicher Ausführung, sonst false.
     */
    public function RequestState(string $Ident)
    {
        return parent::RequestState($Ident);
    }


}

/** @} */
?>