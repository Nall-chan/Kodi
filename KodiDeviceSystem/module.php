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
 * KodiDeviceSystem Klasse für den Namespace System der KODI-API.
 * Erweitert KodiBase.
 *
 * @package       Kodi
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2020 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
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
        $this->RegisterPropertyInteger('PowerScript', 1); // OLD
        $this->RegisterPropertyInteger(self::PropertyPowerOnType, 1);
        $this->RegisterPropertyInteger(self::PropertyPowerOff, 0);
        $this->RegisterPropertyString(self::PropertyMACAddress, '');
        $this->RegisterPropertyString(self::PropertyWOLAction, '');
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function ApplyChanges(): void
    {
        /** Update Config
         * @deprecated Wird zur 7.0 entfernt
         * @property PowerScript in eine Aktion überführen
         */
        if ($this->ReadPropertyInteger('PowerScript') > 1) {
            // dann Aktion erstellen
            $Action = [
                'actionID'  => '{7938A5A2-0981-5FE0-BE6C-8AA610D654EB}',
                'parameters'=> [
                    'ENVIRONMENT'=> 'Default',
                    'PARENT'     => $this->InstanceID,
                    'TARGET'     => $this->ReadPropertyInteger('PowerScript')
                ]
            ];
            IPS_SetProperty($this->InstanceID, self::PropertyWOLAction, json_encode($Action)); // Action setzen
            IPS_SetProperty($this->InstanceID, self::PropertyPowerOnType, 1); // PowerOnType auf Action (1) setzen
            IPS_SetProperty($this->InstanceID, 'PowerScript', 1); // PowerScript auf 1 setzen
            IPS_ApplyChanges($this->InstanceID); // speichern
            return; // verlassen
        }

        $this->RegisterProfileIntegerEx('Action.Kodi', '', '', '', [
            [0, $this->Translate('Execute'), '', -1]
        ]);
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
    public function RequestState(string $Ident): void
    {
        parent::RequestState($Ident);
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
     * @return string Die bereinigte Adresse.
     */
    private function GetMac(): string
    {
        $Address = $this->ReadPropertyString(self::PropertyMACAddress);
        $Address = str_replace('-', '', $Address);
        $Address = str_replace(':', '', $Address);
        if (strlen($Address) == 12) {
            return  strtoupper($Address);
        }
        return '';
    }

    private function WOLRequest(string $mac): bool
    {
        $this->SendDebug('SendWOL', $mac, 0);
        $ip = '255.255.255.255'; // Broadcast adresse
        $nic = fsockopen('udp://' . $ip, 15);
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

/** @} */
