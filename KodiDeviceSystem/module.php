<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/KodiClass.php';  // diverse Klassen

/**
 * KodiDeviceSystem Klasse für den Namespace System der KODI-API.
 * Erweitert KodiBase.
 *
 * @project       KodiDeviceSystem
 * @file          module.php
 * @package       Kodi
 * @author        Michael Tröger <nall4chan@gmail.com>
 * @copyright     2026 Michael Tröger
 * @license       Custom Non-Commercial Share-Alike License (see LICENSE)
 * @version       2.01
 * @example <b>Ohne</b>
 *
 * @property string $Namespace RPC-Namespace
 * @property array $Properties Alle Properties des RPC-Namespace
 */
class KodiDeviceSystem extends KodiBase
{
    public const PropertyPowerOnType = 'PowerOnType';
    public const PropertyPowerOff = 'PowerOff';
    public const PropertyMACAddress = 'MACAddress';
    public const PropertyWOLAction = 'WOLAction';
    /** Alte Eigenschaft (Einschalt-Skript), nur noch für Migrate(). */
    public const PropertyPowerScript = 'PowerScript';
    public const ActionVisibleFormElementsPowerOnType = 'PowerOnType';
    protected static $Namespace = 'System';
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
    public function Create(): void
    {
        parent::Create();
        $this->RegisterPropertyInteger(self::PropertyPowerOnType, 1);
        $this->RegisterPropertyInteger(self::PropertyPowerOff, 0);
        $this->RegisterPropertyString(self::PropertyMACAddress, '');
        $this->RegisterPropertyString(self::PropertyWOLAction, '');
    }

    /**
     * Interne Funktion des SDK.
     * Überträgt das alte Einschalt-Skript (Eigenschaft PowerScript) einmalig in die Aktion WOLAction und entfernt die Eigenschaft.
     *
     * @access public
     */
    public function Migrate(string $JSONData): string
    {
        $Data = json_decode($JSONData);
        if (!property_exists($Data->configuration, self::PropertyPowerScript)) {
            return '';
        }
        $ScriptID = (int) $Data->configuration->{self::PropertyPowerScript};
        unset($Data->configuration->{self::PropertyPowerScript});
        if ($ScriptID > 1) {
            // Aktion 'Skript ausführen' mit dem bisherigen Skript
            $Action = [
                'actionID'   => '{7938A5A2-0981-5FE0-BE6C-8AA610D654EB}',
                'parameters' => [
                    'ENVIRONMENT' => 'Default',
                    'PARENT'      => $this->InstanceID,
                    'TARGET'      => $ScriptID
                ]
            ];
            $Data->configuration->{self::PropertyWOLAction} = json_encode($Action);
            $Data->configuration->{self::PropertyPowerOnType} = 1;
            $this->LogMessage(sprintf($this->Translate('Power-on script %d migrated to the power-on action.'), $ScriptID), KL_NOTIFY);
        }
        return json_encode($Data);
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
        $this->RegisterVariableBoolean('Power', $this->Translate('Power'), '~Switch', 0);
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
    public function GetConfigurationForm(): string
    {
        $Form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        switch ($this->ReadPropertyInteger(self::PropertyPowerOnType)) {
            case 0:
                $Form['elements'][0]['items'][1]['visible'] = false;
                break;
            case 1:
                $Form['elements'][0]['items'][2]['visible'] = false;
                break;
        }
        $this->SendDebug('FORM', json_encode($Form), 0);
        $this->SendDebug('FORM', json_last_error_msg(), 0);
        return json_encode($Form);
    }

    ################## PUBLIC
    /**
     * IPS-Instanz-Funktion 'KODISYS_Power'. Schaltet Kodi ein oder aus. Einschalten erfolgt per hinterlegten PHP-Script in der Instanz. Der Modus für das Ausschalten ist ebenfalls in der Instanz zu konfigurieren.
     *
     * @access public
     * @param bool $Value True für Einschalten, False für Ausschalten.
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function Power(bool $Value): bool
    {
        if ($Value) {
            return $this->WakeUp();
        } else {
            switch ($this->ReadPropertyInteger(self::PropertyPowerOff)) {
                case 0:
                    return $this->Shutdown();
                case 1:
                    return $this->Hibernate();
                case 2:
                    return $this->Suspend();
            }
        }
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODISYS_WakeUp'. Schaltet 'Kodi' ein.
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function WakeUp(): bool
    {
        switch ($this->ReadPropertyInteger(self::PropertyPowerOnType)) {
            case 0: // build-in WOL
                return $this->WOLRequest($this->GetMac());
            case 1: // Selected action
                $action = json_decode($this->ReadPropertyString(self::PropertyWOLAction), true);
                $this->SendDebug('Run WOLAction', $action, 0);
                if ($action == null) {
                    return false;
                }
                $Result = IPS_RunActionWait($action['actionID'], $action['parameters']);
                if ($Result != '') {
                    trigger_error($Result, E_USER_NOTICE);
                    return false;
                }
                return true;
        }
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODISYS_Shutdown'. Führt einen Shutdown auf Betriebssystemebene aus.
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function Shutdown(): bool
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
    public function Hibernate(): bool
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
    public function Suspend(): bool
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
    public function Reboot(): bool
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
    public function EjectOpticalDrive(): bool
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
    public function RequestState(string $Ident): bool
    {
        return parent::RequestState($Ident);
    }
    ################## ActionHandler
    /**
     * Actionhandler der Statusvariablen. Interne SDK-Funktion.
     *
     * @access public
     * @param string $Ident Der Ident der Statusvariable.
     * @param bool|float|int|string $Value Der angeforderte neue Wert.
     */
    protected function ActionHandler(string $Ident, mixed $Value): void
    {
        switch ($Ident) {
            case self::ActionVisibleFormElementsPowerOnType:
                $this->UpdateFormField('MACAddress', 'visible', $Value == 0);
                $this->UpdateFormField('WOLActionPopup', 'visible', $Value == 1);
                break;
            case 'Power':
                $this->Power($Value);
                return;
            case 'shutdown':
            case 'reboot':
            case 'hibernate':
            case 'suspend':
            case 'ejectOpticalDrive':
                $this->{ucfirst($Ident)}();
                return;
            default:
                trigger_error($this->Translate('Invalid Ident.'), E_USER_NOTICE);
                break;
        }
    }

    /**
     * Wird ausgeführt wenn sich der Status vom Parent ändert.
     * Setzt das Ereignis 'Batterie schwach' bei einer neuen Verbindung zurück.
     *
     * @access protected
     */
    protected function IOChangeState(int $State): void
    {
        if ($State == IS_ACTIVE) {
            $this->SetValueBoolean('LowBatteryEvent', false);
        }
        parent::IOChangeState($State);
    }

    /**
     * Dekodiert die empfangenen Events und Antworten auf 'GetProperties'.
     *
     * @access protected
     * @param string $Method RPC-Funktion ohne Namespace
     * @param mixed|object $KodiPayload Der zu dekodierende Datensatz als Objekt.
     */
    protected function Decode(string $Method, mixed $KodiPayload): void
    {
        switch ($Method) {
            case 'GetProperties':
                // Aktionen nur anbieten, wenn Kodi sie auf diesem System ausführen kann
                foreach (['canshutdown' => 'shutdown', 'canhibernate' => 'hibernate', 'cansuspend' => 'suspend', 'canreboot' => 'reboot'] as $Property => $Ident) {
                    if (!property_exists($KodiPayload, $Property)) {
                        continue;
                    }
                    if ((bool) $KodiPayload->{$Property}) {
                        $this->EnableAction($Ident);
                    } else {
                        $this->DisableAction($Ident);
                    }
                }
                break;
            case 'Power':
                $this->SetValueBoolean('Power', $KodiPayload);
                break;
            case 'OnLowBattery':
                $this->SetValueBoolean('LowBatteryEvent', true);
                break;
            case 'OnQuit':
            case 'OnRestart':
            case 'OnSleep':
                $this->SetValueBoolean('Power', false);
                break;
            case 'OnWake':
                $this->SetValueBoolean('Power', true);
                $this->SetValueBoolean('LowBatteryEvent', false);
                break;
        }
    }

    ################## PRIVATE
    /**
     * Liest den String auf der Instanz-Eigenschaft MACAddress und konvertiert sie in ein bereinigtes Format.
     *
     * @access private
     * @return string Die bereinigte Adresse.
     */
    private function GetMac(): string
    {
        $Address = $this->ReadPropertyString(self::PropertyMACAddress);
        $Address = str_replace('-', '', $Address);
        $Address = str_replace(':', '', $Address);
        if (strlen($Address) == 12) {
            return strtoupper($Address);
        }
        return '';
    }

    private function WOLRequest(string $mac): bool
    {
        $this->SendDebug('SendWOL', $mac, 0);
        $ip = '255.255.255.255'; // Broadcast adresse
        $nic = fsockopen('udp://' . $ip, 9);
        if ($nic) {
            $packet = '';
            for ($i = 0; $i < 6; $i++) {
                $packet .= chr(0xFF);
            }
            for ($j = 0; $j < 16; $j++) {
                for ($k = 0; $k < 6; $k++) {
                    $str = substr($mac, $k * 2, 2);
                    $dec = hexdec($str);
                    $packet .= chr($dec);
                }
            }
            $this->SendDebug('SendWOL', $packet, 1);
            $ret = fwrite($nic, $packet);
            fclose($nic);
            if ($ret) {
                return true;
            }
        }
        return false;
    }
}

