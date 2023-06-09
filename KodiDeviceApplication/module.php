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
 *
 * @property string $Namespace RPC-Namespace
 * @property array $Properties Alle Properties des RPC-Namespace
 */
class KodiDeviceApplication extends KodiBase
{
    public const PropertyShowName = 'showName';
    public const PropertyShowVersion = 'showVersion';
    public const ProptertyShowExit = 'showExit';
    protected static $Namespace = 'Application';

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
    public function Create(): void
    {
        parent::Create();
        $this->RegisterPropertyBoolean(self::PropertyShowName, true);
        $this->RegisterPropertyBoolean(self::PropertyShowVersion, true);
        $this->RegisterPropertyBoolean(self::ProptertyShowExit, true);
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

        if ($this->ReadPropertyBoolean(self::PropertyShowName)) {
            $this->RegisterVariableString('name', 'Name', '', 0);
        } else {
            $this->UnregisterVariable('name');
        }

        if ($this->ReadPropertyBoolean(self::PropertyShowVersion)) {
            $this->RegisterVariableString('version', 'Version', '', 1);
        } else {
            $this->UnregisterVariable('version');
        }

        if ($this->ReadPropertyBoolean(self::ProptertyShowExit)) {
            $this->RegisterVariableInteger('quit', $this->Translate('Quit Kodi'), 'Action.Kodi', 2);
            $this->EnableAction('quit');
        } else {
            $this->UnregisterVariable('quit');
        }

        $this->RegisterVariableBoolean('mute', $this->Translate('Mute'), '~Mute', 3);
        $this->EnableAction('mute');

        $this->RegisterVariableInteger('volume', 'Volume', '~Volume', 4);
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
    public function RequestAction(string $Ident, mixed $Value, bool &$done = false): void
    {
        parent::RequestAction($Ident, $Value, $done);
        if ($done) {
            return;
        }
        switch ($Ident) {
            case 'mute':
                $this->SetMute((bool) $Value);
                return;
            case 'volume':
                $this->SetVolume((int) $Value);
                return;
            case 'quit':
                $this->Quit();
                return;
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
    public function SetMute(bool $Value): bool
    {
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
    public function SetVolume(int $Value): bool
    {
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
    public function Quit(): bool
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
    public function RequestState(string $Ident): void
    {
        parent::RequestState($Ident);
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
        foreach ($KodiPayload as $param => $value) {
            switch ($param) {
                case 'mute':
                case 'muted':
                    $this->SetValueBoolean('mute', (bool) $value);
                    break;
                case 'volume':
                    $this->SetValueInteger('volume', (int) $value);
                    break;
                case 'name':
                    $this->SetValueString('name', (string) $value);
                    break;
                case 'version':
                    $this->SetValueString('version', (string) $value->major . '.' . (string) $value->minor);
                    break;
            }
        }
    }
}

/** @} */
