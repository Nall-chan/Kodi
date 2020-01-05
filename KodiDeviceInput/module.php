<?php

declare(strict_types=1);

/*
 * @addtogroup kodi
 * @{
 *
 * @package       Kodi
 * @file          module.php
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2016 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       2.0
 *
 */
require_once(__DIR__ . '/../libs/KodiClass.php');  // diverse Klassen

/**
 * KodiDeviceInput Klasse für den Namespace Input der KODI-API.
 * Erweitert KodiBase.
 *
 * @package       Kodi
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2016 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       1.0
 * @example <b>Ohne</b>
 * @todo Input.ShowPlayerProcessInfo ab v8
 */
class KodiDeviceInput extends KodiBase
{
    /**
     * RPC-Namespace
     *
     * @access private
     * @var string
     * @value 'Application'
     */
    protected static $Namespace = 'Input';

    /**
     * Alle Properties des RPC-Namespace
     *
     * @access private
     * @var array
     */
    protected static $Properties = [];

    /**
     * Alle Aktionen der RPC-Methode ExecuteAction
     *
     * @access private
     * @var array
     */
    protected static $ExecuteAction = [
        'left',
        'right',
        'up',
        'down',
        'pageup',
        'pagedown',
        'select',
        'highlight',
        'parentdir',
        'parentfolder',
        'back',
        'previousmenu',
        'info',
        'pause',
        'stop',
        'skipnext',
        'skipprevious',
        'fullscreen',
        'aspectratio',
        'stepforward',
        'stepback',
        'bigstepforward',
        'bigstepback',
        'chapterorbigstepforward',
        'chapterorbigstepback',
        'osd',
        'showsubtitles',
        'nextsubtitle',
        'cyclesubtitle',
        'codecinfo',
        'nextpicture',
        'previouspicture',
        'zoomout',
        'zoomin',
        'playlist',
        'queue',
        'zoomnormal',
        'zoomlevel1',
        'zoomlevel2',
        'zoomlevel3',
        'zoomlevel4',
        'zoomlevel5',
        'zoomlevel6',
        'zoomlevel7',
        'zoomlevel8',
        'zoomlevel9',
        'nextcalibration',
        'resetcalibration',
        'analogmove',
        'analogmovex',
        'analogmovey',
        'rotate',
        'rotateccw',
        'close',
        'subtitledelayminus',
        'subtitledelay',
        'subtitledelayplus',
        'audiodelayminus',
        'audiodelay',
        'audiodelayplus',
        'subtitleshiftup',
        'subtitleshiftdown',
        'subtitlealign',
        'audionextlanguage',
        'verticalshiftup',
        'verticalshiftdown',
        'nextresolution',
        'audiotoggledigital',
        'number0',
        'number1',
        'number2',
        'number3',
        'number4',
        'number5',
        'number6',
        'number7',
        'number8',
        'number9',
        'osdleft',
        'osdright',
        'osdup',
        'osddown',
        'osdselect',
        'osdvalueplus',
        'osdvalueminus',
        'smallstepback',
        'fastforward',
        'rewind',
        'play',
        'playpause',
        'switchplayer',
        'delete',
        'copy',
        'move',
        'mplayerosd',
        'hidesubmenu',
        'screenshot',
        'rename',
        'togglewatched',
        'scanitem',
        'reloadkeymaps',
        'volumeup',
        'volumedown',
        'mute',
        'backspace',
        'scrollup',
        'scrolldown',
        'analogfastforward',
        'analogrewind',
        'moveitemup',
        'moveitemdown',
        'contextmenu',
        'shift',
        'symbols',
        'cursorleft',
        'cursorright',
        'showtime',
        'analogseekforward',
        'analogseekback',
        'showpreset',
        'nextpreset',
        'previouspreset',
        'lockpreset',
        'randompreset',
        'increasevisrating',
        'decreasevisrating',
        'showvideomenu',
        'enter',
        'increaserating',
        'decreaserating',
        'togglefullscreen',
        'nextscene',
        'previousscene',
        'nextletter',
        'prevletter',
        'jumpsms2',
        'jumpsms3',
        'jumpsms4',
        'jumpsms5',
        'jumpsms6',
        'jumpsms7',
        'jumpsms8',
        'jumpsms9',
        'filter',
        'filterclear',
        'filtersms2',
        'filtersms3',
        'filtersms4',
        'filtersms5',
        'filtersms6',
        'filtersms7',
        'filtersms8',
        'filtersms9',
        'firstpage',
        'lastpage',
        'guiprofile',
        'red',
        'green',
        'yellow',
        'blue',
        'increasepar',
        'decreasepar',
        'volampup',
        'volampdown',
        'volumeamplification',
        'createbookmark',
        'createepisodebookmark',
        'settingsreset',
        'settingslevelchange',
        'stereomode',
        'nextstereomode',
        'previousstereomode',
        'togglestereomode',
        'stereomodetomono',
        'channelup',
        'channeldown',
        'previouschannelgroup',
        'nextchannelgroup',
        'playpvr',
        'playpvrtv',
        'playpvrradio',
        'record',
        'leftclick',
        'rightclick',
        'middleclick',
        'doubleclick',
        'longclick',
        'wheelup',
        'wheeldown',
        'mousedrag',
        'mousemove',
        'tap',
        'longpress',
        'pangesture',
        'zoomgesture',
        'rotategesture',
        'swipeleft',
        'swiperight',
        'swipeup',
        'swipedown',
        'error',
        'noop'
    ];

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Create()
    {
        parent::Create();
        $this->RegisterPropertyBoolean('showSVGRemote', true);
        $this->RegisterPropertyInteger('RemoteId', 1);
        $this->RegisterPropertyBoolean('showNavigationButtons', true);
        $this->RegisterPropertyBoolean('showControlButtons', true);
        $this->RegisterPropertyBoolean('showInputRequested', true);
        $this->RegisterPropertyBoolean('showTextInput', true);
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Destroy()
    {
        if (IPS_GetKernelRunlevel() <> KR_READY) {
            return parent::Destroy();
        }
        if (!IPS_InstanceExists($this->InstanceID)) {
            $this->UnregisterHook('/hook/KodiRemote' . $this->InstanceID);
        }

        parent::Destroy();
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function ApplyChanges()
    {
        $this->UnregisterScript('WebHookRemote');

        if ($this->ReadPropertyBoolean('showSVGRemote')) {
            if (IPS_GetKernelRunlevel() == KR_READY) {
                $this->RegisterHook('/hook/KodiRemote' . $this->InstanceID);
            }
            if (@$this->GetIDForIdent('Remote') == false) {
                $remoteID = $this->RegisterVariableString('Remote', 'Remote', '~HTMLBox', 1);
                /* @var $remote string */
                include 'generateRemote' . ($this->ReadPropertyInteger('RemoteId')) . '.php';
                SetValueString($remoteID, $remote);
            }
        } else {
            if (IPS_GetKernelRunlevel() == KR_READY) {
                $this->UnregisterHook('/hook/KodiRemote' . $this->InstanceID);
            }
            $this->UnregisterVariable('Remote');
        }

        if ($this->ReadPropertyBoolean('showNavigationButtons')) {
            $this->RegisterProfileIntegerEx('Navigation.Kodi', '', '', '', [
                [1, '<', '', -1],
                [2, '>', '', -1],
                [3, '^', '', -1],
                [4, 'v', '', -1],
                [5, 'OK', '', -1],
                [6, 'Zurück', '', -1],
                [7, 'Home', '', -1]
            ]);
            $this->RegisterVariableInteger('navremote', 'Navigation', 'Navigation.Kodi', 2);
            $this->EnableAction('navremote');
        } else {
            $this->UnregisterVariable('navremote');
        }

        if ($this->ReadPropertyBoolean('showControlButtons')) {
            $this->RegisterProfileIntegerEx('Control.Kodi', '', '', '', [
                [1, '<<', '', -1],
                [2, 'Menü', '', -1],
                [3, 'Play', '', -1],
                [4, 'Pause', '', -1],
                [5, 'Stop', '', -1],
                [6, '>>', '', -1]
            ]);
            $this->RegisterVariableInteger('ctrlremote', 'Steuerung', 'Control.Kodi', 3);
            $this->EnableAction('ctrlremote');
        } else {
            $this->UnregisterVariable('ctrlremote');
        }

        if ($this->ReadPropertyBoolean('showInputRequested')) {
            $this->RegisterVariableBoolean('inputrequested', 'Eingabe erwartet', '', 4);
            if (IPS_GetKernelRunlevel() == KR_INIT) {
                $this->SetValueBoolean('inputrequested', false);
            }
        } else {
            $this->UnregisterVariable('inputrequested');
        }

        if ($this->ReadPropertyBoolean('showTextInput')) {
            $this->RegisterVariableString('inputtext', 'Eingabe senden', '', 5);
            $this->EnableAction('inputtext');
        } else {
            $this->UnregisterVariable('inputrequested');
        }


        parent::ApplyChanges();
    }

    ################## PRIVATE
    /**
     * Verarbeitet Daten aus dem Webhook.
     *
     * @access protected
     * @global array $_GET
     */
    protected function ProcessHookdata()
    {
        if (isset($_GET['button'])) {
            if ($this->ExecuteAction($_GET['button']) === true) {
                echo 'OK';
            }
        } else {
            $this->SendDebug('illegal HOOK', $_GET, 0);
            echo 'Illegal hook';
        }
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
        switch ($Method) {
            case 'OnInputRequested':
                $this->SetValueBoolean('inputrequested', true);
                break;
            case 'OnInputFinished':
                $this->SetValueBoolean('inputrequested', true);
                break;
        }
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
            case 'navremote':
                switch ($Value) {
                    case 1:
                        $ret = $this->Left();
                        break;
                    case 2:
                        $ret = $this->Right();
                        break;
                    case 3:
                        $ret = $this->Up();
                        break;
                    case 4:
                        $ret = $this->Down();
                        break;
                    case 5:
                        $ret = $this->Select();
                        break;
                    case 6:
                        $ret = $this->Back();
                        break;
                    case 7:
                        $ret = $this->Home();
                        break;
                    default:
                        return trigger_error('Invalid Value.', E_USER_NOTICE);
                }
                break;
            case 'ctrlremote':
                switch ($Value) {
                    case 1:
                        $ret = $this->ExecuteAction('rewind');
                        break;
                    case 2:
                        $ret = $this->ExecuteAction('mplayerosd');
                        break;
                    case 3:
                        $ret = $this->ExecuteAction('play');
                        break;
                    case 4:
                        $ret = $this->ExecuteAction('pause');
                        break;
                    case 5:
                        $ret = $this->ExecuteAction('stop');
                        break;
                    case 6:
                        $ret = $this->ExecuteAction('fastforward');
                        break;
                    default:
                        return trigger_error('Invalid Value.', E_USER_NOTICE);
                }
                break;
            case 'inputtext':
                $ret = $this->SendText($Value, true);
                break;
            default:
                trigger_error('Invalid Ident.', E_USER_NOTICE);
                return;
        }
        if (!$ret) {
            trigger_error('Error on execute action.', E_USER_NOTICE);
        }
    }

    ################## PUBLIC
    /**
     * IPS-Instanz-Funktion 'KODIINPUT_Up'. Tastendruck 'Hoch' ausführen.
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function Up()
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Up();
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODIINPUT_Down'. Tastendruck 'Runter' ausführen.
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function Down()
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Down();
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODIINPUT_Left'. Tastendruck 'Links' ausführen.
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function Left()
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Left();
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODIINPUT_Right'. Tastendruck 'Rechts' ausführen.
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function Right()
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Right();
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODIINPUT_Back'. Tastendruck 'Zurück' ausführen.
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function Back()
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Back();
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODIINPUT_ContextMenu'. Tastendruck 'ContextMenu' ausführen.
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function ContextMenu()
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->ContextMenu();
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODIINPUT_Home'. Tastendruck 'Home' ausführen.
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function Home()
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Home();
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODIINPUT_Info'. Tastendruck 'Info' ausführen.
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function Info()
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Info();
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODIINPUT_Select'. Tastendruck 'Select' ausführen.
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function Select()
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Select();
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODIINPUT_ShowOSD'. Tastendruck 'ShowOSD' ausführen.
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function ShowOSD()
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->ShowOSD();
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODIINPUT_ShowCodec'. Tastendruck 'ShowCodec' ausführen.
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function ShowCodec()
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->ShowCodec();
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODIINPUT_ExecuteAction'. Als Parameter übergebenen Tastendruck ausführen.
     *
     * @access public
     * @param string $Action Auszuführende Aktion.
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function ExecuteAction(string $Action)
    {
        if (!is_string($Action)) {
            trigger_error('Action must be string', E_USER_NOTICE);
            return false;
        }
        if (!in_array($Action, self::$ExecuteAction)) {
            trigger_error('Unknown action.', E_USER_NOTICE);
            return false;
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->ExecuteAction(['action' => $Action]);
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODIINPUT_SendText'. Als Parameter übergebenen Text senden.
     *
     * @access public
     * @param string $Text Der zu sendene Text.
     * @param bool $Done True wenn die Eingabe beendet werden soll, sonst false.
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function SendText(string $Text, bool $Done)
    {
        if (!is_string($Text)) {
            trigger_error('Text must be string', E_USER_NOTICE);
            return false;
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->SendText(['text' => $Text, 'done' => $Done]);
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $ret === 'OK';
    }
}

/** @} */
