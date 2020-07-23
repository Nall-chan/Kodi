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
 * @version       2.10
 *
 */
require_once __DIR__ . '/../libs/KodiClass.php';  // diverse Klassen

/**
 * KodiDeviceGUI Klasse für den Namespace GUI der KODI-API.
 * Erweitert KodiBase.
 *
 * @package       Kodi
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2020 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       2.10
 * @example <b>Ohne</b>
 */
class KodiDeviceGUI extends KodiBase
{
    /**
     * RPC-Namespace
     *
     * @access private
     *  @var string
     * @value 'Application'
     */
    protected static $Namespace = 'GUI';

    /**
     * Alle Properties des RPC-Namespace
     *
     * @access private
     *  @var array
     */
    protected static $Properties = [
        'currentwindow',
        'currentcontrol',
        'skin',
        'fullscreen',
        'stereoscopicmode'
    ];

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Create()
    {
        parent::Create();
        $this->RegisterPropertyBoolean('showCurrentWindow', true);
        $this->RegisterPropertyBoolean('showCurrentControl', true);
        $this->RegisterPropertyBoolean('showSkin', true);
        $this->RegisterPropertyBoolean('showFullscreen', true);
        $this->RegisterPropertyBoolean('showScreensaver', true);
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function ApplyChanges()
    {
        if ($this->ReadPropertyBoolean('showCurrentWindow')) {
            $this->RegisterVariableString('currentwindow', $this->Translate('Current window'), '', 0);
            $this->RegisterVariableInteger('_currentwindowid', $this->Translate('Current window (id)'), '', 0);
            IPS_SetHidden($this->GetIDForIdent('_currentwindowid'), true);
        } else {
            $this->UnregisterVariable('currentwindow');
            $this->UnregisterVariable('_currentwindowid');
        }

        if ($this->ReadPropertyBoolean('showCurrentControl')) {
            $this->RegisterVariableString('currentcontrol', $this->Translate('Current control'), '', 1);
        } else {
            $this->UnregisterVariable('currentcontrol');
        }

        if ($this->ReadPropertyBoolean('showSkin')) {
            $this->RegisterVariableString('skin', $this->Translate('Current skin'), '', 2);
            $this->RegisterVariableString('_skinid', $this->Translate('Current skin (id)'), '', 2);
            IPS_SetHidden($this->GetIDForIdent('_skinid'), true);
        } else {
            $this->UnregisterVariable('skin');
            $this->UnregisterVariable('_skinid');
        }

        if ($this->ReadPropertyBoolean('showFullscreen')) {
            $this->RegisterVariableBoolean('fullscreen', $this->Translate('Full screen'), '~Switch', 3);
            $this->EnableAction('fullscreen');
        } else {
            $this->UnregisterVariable('fullscreen');
        }

        if ($this->ReadPropertyBoolean('showScreensaver')) {
            $this->RegisterVariableBoolean('screensaver', $this->Translate('Screensaver'), '~Switch', 4);
        } else {
            $this->UnregisterVariable('screensaver');
        }

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
            case 'fullscreen':
                return $this->SetFullscreen($Value);
            default:
                trigger_error('Invalid Ident.', E_USER_NOTICE);
                break;
        }
    }

    ################## PUBLIC
    /**
     * IPS-Instanz-Funktion 'KODIGUI_SetFullscreen'.
     * De-/Aktiviert den Vollbildmodus.
     *
     * @access public
     * @param bool $Value True für Vollbild aktiv, False bei inaktiv.
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function SetFullscreen(bool $Value)
    {
        if (!is_bool($Value)) {
            trigger_error('Value must be boolean', E_USER_NOTICE);
            return false;
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->SetFullscreen(['fullscreen' => $Value]);
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        $this->SetValueBoolean('fullscreen', $ret);
        if ($ret === $Value) {
            return true;
        }
        trigger_error($this->Translate('Error set full screen mode.'), E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIGUI_ShowNotification'.
     * Erzeugt eine Benachrichtigung
     *
     * @access public
     * @param string $Title
     * @param string $Message
     * @param string $Image
     * @param int $Timeout
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function ShowNotification(string $Title, string $Message, string $Image, int $Timeout)
    {
        if (!is_string($Title)) {
            trigger_error('Title must be string', E_USER_NOTICE);
            return false;
        }
        if (!is_string($Message)) {
            trigger_error('Message must be string', E_USER_NOTICE);
            return false;
        }
        if (!is_int($Timeout)) {
            trigger_error('Timeout must be integer', E_USER_NOTICE);
            return false;
        }

        $Data = ['title' => $Title, 'message' => $Message];

        if ($Image != '') {
            $Data['image'] = $Image;
        }
        if ($Timeout != 0) {
            $Data['displaytime'] = $Timeout;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->ShowNotification($Data);
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $ret === $Title;
    }

    /**
     * IPS-Instanz-Funktion 'KODIGUI_ActivateWindow'.
     * Aktiviert ein Fenster
     *
     * @access public
     * @param string $Window Das zu aktivierende Fenster
     * @return bool true bei Erfolg, sonst false.
     */
    public function ActivateWindow(string $Window)
    {
        if (!is_string($Window)) {
            trigger_error('Window must be string', E_USER_NOTICE);
            return false;
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->ActivateWindow(['window' => $Window]);
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $ret === $Window;
    }

    /**
     * IPS-Instanz-Funktion 'KODIGUI_RequestState'. Frage eine oder mehrere Properties ab.
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
     * Dekodiert die empfangenen Events und Anworten auf 'GetProperties'.
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
                    switch ($param) {
                        case 'currentcontrol':
                            $this->SetValueString('currentcontrol', $value->label);
                            break;
                        case 'currentwindow':
                            $this->SetValueString('currentwindow', $value->label);
                            $this->SetValueInteger('_currentwindowid', $value->id);
                            break;
                        case 'fullscreen':
                            $this->SetValueBoolean('fullscreen', $value);
                            break;
                        case 'skin':
                            $this->SetValueString('skin', $value->name);
                            $this->SetValueString('_skinid', $value->id);
                            break;
                    }
                }
                break;
            case 'OnScreensaverDeactivated':
                $this->SetValueBoolean('screensaver', false);
                break;
            case 'OnScreensaverActivated':
                $this->SetValueBoolean('screensaver', true);
                break;
        }
    }
}

/** @} */
