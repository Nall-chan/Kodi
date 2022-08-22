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
 * KodiDeviceApplication Klasse für den Namespace Application der KODI-API.
 * Erweitert KodiBase.
 *
 * @package       Kodi
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2020 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       3.00
 * @example <b>Ohne</b>
 */
class KodiDeviceApplication extends KodiBase
{
    /**
     * RPC-Namespace
     *
     * @access private
     *  @var string
     * @value 'Application'
     */
    protected static $Namespace = 'Application';

    /**
     * Alle Properties des RPC-Namespace
     *
     * @access private
     *  @var array
     */
    protected static $Properties = [
        'volume',
        'muted',
        'name',
        'version'
    ];

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Create()
    {
        parent::Create();
        $this->RegisterPropertyBoolean('showName', true);
        $this->RegisterPropertyBoolean('showVersion', true);
        $this->RegisterPropertyBoolean('showExit', true);
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

        if ($this->ReadPropertyBoolean('showName')) {
            $this->RegisterVariableString('name', 'Name', '', 0);
        } else {
            $this->UnregisterVariable('name');
        }

        if ($this->ReadPropertyBoolean('showVersion')) {
            $this->RegisterVariableString('version', 'Version', '', 1);
        } else {
            $this->UnregisterVariable('version');
        }

        if ($this->ReadPropertyBoolean('showExit')) {
            $this->RegisterVariableInteger('quit', $this->Translate('Quit Kodi'), 'Action.Kodi', 2);
            $this->EnableAction('quit');
        } else {
            $this->UnregisterVariable('quit');
        }

        $this->RegisterVariableBoolean('mute', $this->Translate('Mute'), '~Switch', 3);
        $this->EnableAction('mute');

        $this->RegisterVariableInteger('volume', 'Volume', '~Intensity.100', 4);
        $this->EnableAction('volume');

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
            case 'mute':
                return $this->SetMute($Value);
            case 'volume':
                return $this->SetVolume($Value);
            case 'quit':
                return $this->Quit();
            default:
                trigger_error('Invalid Ident.', E_USER_NOTICE);
        }
    }

    ################## PUBLIC
    /**
     * IPS-Instanz-Funktion 'KODIAPP_SetMute'. De-/Aktiviert die Stummschaltung
     *
     * @access public
     * @param bool $Value True für Stummschaltung aktiv, False bei inaktiv.
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function SetMute(bool $Value)
    {
        if (!is_bool($Value)) {
            trigger_error('Value must be boolean', E_USER_NOTICE);
            return false;
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->SetMute(['mute' => $Value]);
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        $this->SetValueBoolean('mute', $ret);
        if ($ret === $Value) {
            return true;
        }
        trigger_error($this->Translate('Error on set mute.'), E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIAPP_SetVolume'. Setzen der Lautstärke
     *
     * @access public
     * @param int $Value Neue Lautstärke
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function SetVolume(int $Value)
    {
        if (!is_int($Value)) {
            trigger_error('Value must be integer', E_USER_NOTICE);
            return false;
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->SetVolume(['volume' => $Value]);
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        $this->SetValueInteger('volume', $ret);
        if ($ret === $Value) {
            return true;
        }
        trigger_error($this->Translate('Error on set volume.'), E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIAPP_Quit'. Beendet die Kodi-Anwendung
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function Quit()
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace); //, 'Quit');
        $KodiData->Quit(null);
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret === 'OK') {
            return true;
        }
        trigger_error($this->Translate('Error on quit Kodi.'), E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIAPP_RequestState'. Frage eine oder mehrere Properties ab.
     *
     * @access public
     * @param string $Ident Enthält den Names des "properties" welches angefordert werden soll.
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function RequestState(string $Ident)
    {
        return parent::RequestState($Ident);
    }

    ################## PRIVATE
    /**
     * Dekodiert die empfangenen Events und Antworten auf 'GetProperties'.
     *
     * @param string $Method RPC-Funktion ohne Namespace
     * @param object $KodiPayload Der zu dekodierende Datensatz als Objekt.
     */
    protected function Decode($Method, $KodiPayload)
    {
        foreach ($KodiPayload as $param => $value) {
            switch ($param) {
                case 'mute':
                case 'muted':
                    $this->SetValueBoolean('mute', $value);
                    break;
                case 'volume':
                    $this->SetValueInteger('volume', $value);
                    break;
                case 'name':
                    $this->SetValueString('name', $value);
                    break;
                case 'version':
                    $this->SetValueString('version', $value->major . '.' . $value->minor);
                    break;
            }
        }
    }
}

/** @} */
