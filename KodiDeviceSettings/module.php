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
 * KodiDeviceFiles Klasse für den Namespace Files der KODI-API.
 * Erweitert KodiBase.
 *
 * @package       Kodi
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2020 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       3.00
 * @example <b>Ohne</b>
 * @todo Suche über WF einbauen. String und Int-Var für Text suche in Album/Artist etc... Ergebnis als HTML-Tabelle.
 */
class KodiDeviceSettings extends KodiBase
{
    /**
     * RPC-Namespace
     *
     * @access private
     * @var string
     * @value 'Settings'
     */
    protected static $Namespace = 'Settings';

    /**
     * Alle Properties des RPC-Namespace
     *
     * @access private
     *  @var array
     */
    protected static $Properties = [];

    /**
     * Alle Properties eines Item
     *
     * @access private
     *  @var array
     */
    protected static $ItemListFull = [];

    ################## PUBLIC
    public function RequestAction($Ident, $Value)
    {
        if (parent::RequestAction($Ident, $Value)) {
            return;
        }
        if ($Ident == 'CreateVariable') {
            return $this->CreateSettingsVariable(json_decode($Value, true));
        }
        if ($Ident == 'ReloadForm') {
            return $this->ReloadForm();
        }
        if (@$this->GetIDForIdent($Ident) > 0) {
            return $this->SetSettingValue(str_replace('_', '.', $Ident), $Value);
        }

        trigger_error($this->Translate('Invalid ident.'), E_USER_NOTICE);
    }
    /**
     * IPS-Instanz-Funktion 'KODISETTINGS_GetSettingValue'. Liefert einen Wert einer Einstellung.
     *
     * @access public
     * @param string $Setting Der Name der zu lesenden Einstellung.
     * @return array|bool Wert der Einstellung oder NULL bei Fehler.
     */
    public function GetSettingValue(string $Setting)
    {
        if (!is_string($Setting)) {
            trigger_error('Setting must be string', E_USER_NOTICE);
            return false;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetSettingValue(['setting' => $Setting]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return null;
        }
        $Ident = str_replace('.', '_', $Setting);
        if (@$this->GetIDForIdent($Ident) > 0) {
            $this->SetValue($Ident, $ret->value);
        }
        return $ret->value;
    }
    public function SetSettingValueBoolean(string $Setting, bool $Value)
    {
        if (!is_string($Setting)) {
            trigger_error('Setting must be string.', E_USER_NOTICE);
            return false;
        }
        if (!is_bool($Value)) {
            trigger_error('Value must be bool.', E_USER_NOTICE);
            return false;
        }
        return $this->SetSettingValue($Setting, $Value);
    }
    public function SetSettingValueInteger(string $Setting, int $Value)
    {
        if (!is_string($Setting)) {
            trigger_error('Setting must be string.', E_USER_NOTICE);
            return false;
        }
        if (!is_int($Value)) {
            trigger_error('Value must be integer.', E_USER_NOTICE);
            return false;
        }
        return $this->SetSettingValue($Setting, $Value);
    }

    /**
     * IPS-Instanz-Funktion 'KODISETTINGS_SetSettingValueString'. Setzt einen Wert einer Einstellung.
     *
     * @access public
     * @param string $Setting Der Name der zu schreibenden Einstellung.
     * @param string $Value Der Wert der zu schreibenden Einstellung.
     * @return bool True bei Erfolg, false bei Fehler.
     */
    public function SetSettingValueString(string $Setting, string $Value)
    {
        if (!is_string($Setting)) {
            trigger_error('Setting must be string.', E_USER_NOTICE);
            return false;
        }
        if (!is_string($Value)) {
            trigger_error('Value must be string.', E_USER_NOTICE);
            return false;
        }
        return $this->SetSettingValue($Setting, $Value);
    }
    /**
     * IPS-Instanz-Funktion 'KODISETTINGS_GetSettings'. Liefert alle Einstellungen.
     *
     * @access public
     * @return array|bool Array mit allen Einstellungen oder false bei Fehler.
     */
    public function GetSettings()
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetSettings();
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        $Settings = $KodiData->ToArray($ret->settings);
        foreach ($Settings as $Setting) {
            $Ident = str_replace('.', '_', $Setting['id']);
            if (@$this->GetIDForIdent($Ident) > 0) {
                $this->SetValue($Ident, $Setting['value']);
            }
        }
        return $Settings;
    }
    public function ResetSettingValue(string $Setting)
    {
        if (!is_string($Setting)) {
            trigger_error('Setting must be string.', E_USER_NOTICE);
            return false;
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->ResetSettingValue(['setting' => $Setting]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return null;
        }
        return $ret;
    }
    public function GetConfigurationForm()
    {
        $Form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        $SettingsList = [];
        if ($this->HasActiveParent()) {
            $Settings = $this->GetControllableSettings();
            if (is_array($Settings)) {
                $SettingsList = $Settings;
                $this->SendDebug('Settings', $SettingsList, 0);
            }
        } else {
            $Form['actions'][2]['visible'] = true;
            $Form['actions'][2]['popup']['items'][0]['caption'] = 'Error';
            $Form['actions'][2]['popup']['items'][1]['caption'] = $this->Translate('Instance has no active parent.');
        }
        $Form['actions'][0]['values'] = $SettingsList;
        $this->SendDebug('FORM', json_encode($Form), 0);
        $this->SendDebug('FORM', json_last_error_msg(), 0);
        return json_encode($Form);
    }
    protected function SetSettingValue(string $Setting, $Value)
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->SetSettingValue(['setting' => $Setting, 'value'=>$Value]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret === true) {
            $Ident = str_replace('.', '_', $Setting);
            if (@$this->GetIDForIdent($Ident) > 0) {
                $this->SetValue($Ident, $Value);
            }
        } else {
            trigger_error($this->Translate('Error on set setting.'), E_USER_NOTICE);
        }
        return $ret;
    }
    /**
     * Keine Funktion.
     *
     * @access protected
     * @param string $Method RPC-Funktion ohne Namespace
     * @param object $KodiPayload Der zu dekodierende Datensatz als Objekt.
     */
    protected function Decode($Method, $KodiPayload)
    {
        return;
    }

    ################## PRIVATE
    private function GetControllableSettings()
    {
        $Settings = @$this->GetSettings();
        if ($Settings === false) {
            return false;
        }
        array_walk($Settings, [$this, 'AddSymconVariables']);

        return array_values($Settings);
    }
    private function AddSymconVariables(&$Setting, $Key)
    {
        $Creatable = false;
        $Data = [];
        $Data['Ident'] = str_replace('.', '_', $Setting['id']);
        $Data['Name'] = $Setting['label'];
        $Data['Profile']['Name'] = '';
        $Data['Profile']['MinValue'] = 0;
        $Data['Profile']['MaxValue'] = 0;
        $Data['Profile']['StepSize'] = 0;
        $Data['Profile']['Suffix'] = '';
        switch ($Setting['type']) {
            case 'boolean':
                $Creatable = true;
                $Data['VarType'] = VARIABLETYPE_BOOLEAN;
                $Data['Profile']['Name'] = '~Switch';
            break;
            case 'integer':
                if (array_key_exists('formatlabel', $Setting['control'])) {
                    $Suffix = ' ' . explode(' ', $Setting['control']['formatlabel'])[1];
                } else {
                    $Suffix = '';
                }
                switch ($Setting['control']['type']) {
                    case 'spinner':
                        $Data['Profile']['Name'] = 'Kodi.' . $this->InstanceID . '.' . $Setting['id'];
                        $Data['VarType'] = VARIABLETYPE_INTEGER;
                        if (array_key_exists('options', $Setting)) {
                            foreach ($Setting['options'] as $Option) {
                                $Data['Profile']['Associations'][] = [
                                    $Option['value'], $Option['label'], '', -1
                                ];
                            }
                        } else {
                            $Data['Profile']['MinValue'] = $Setting['minimum'];
                            $Data['Profile']['MaxValue'] = $Setting['maximum'];
                            $Data['Profile']['StepSize'] = $Setting['step'];
                            if (array_key_exists('minimumlabel', $Setting['control'])) {
                                $Data['Profile']['Associations'][] = [
                                    $Setting['minimum'], $Setting['control']['minimumlabel'], '', -1
                                ];
                                $Data['Profile']['Associations'][] = [
                                    $Setting['minimum'] + 1, '%d' . $Suffix, '', -1
                                ];
                            } else {
                                if ($Suffix != '') {
                                    $Data['Profile']['Suffix'] = $Suffix;
                                }
                            }
                        }
                        $Creatable = true;
                    break;
                    case 'edit':
                        $Data['VarType'] = VARIABLETYPE_INTEGER;
                        $Data['Profile']['Name'] = 'Kodi.' . $this->InstanceID . '.' . $Setting['id'];
                        $Data['Profile']['MinValue'] = $Setting['minimum'];
                        $Data['Profile']['MaxValue'] = $Setting['maximum'];
                        $Data['Profile']['StepSize'] = $Setting['step'];
                        $Creatable = true;
                    break;
                    case 'list':
                        $Data['VarType'] = VARIABLETYPE_INTEGER;
                        if (array_key_exists('options', $Setting)) {
                            $Data['VarType'] = VARIABLETYPE_INTEGER;
                            $Data['Profile']['Name'] = 'Kodi.' . $this->InstanceID . '.' . $Setting['id'];
                            foreach ($Setting['options'] as $Option) {
                                $Data['Profile']['Associations'][] = [
                                    $Option['value'], $Option['label'], '', -1
                                ];
                            }
                        }
                        $Creatable = true;
                    break;

                }
            break;
            case 'string':
                switch ($Setting['control']['type']) {
                    case 'edit':
                        $Data['VarType'] = VARIABLETYPE_STRING;
                        $Creatable = true;
                    break;
                    case 'list':
                    /* TODO
                    Erst mit String Assoziationen ab IPS 5.X
                     */
                    break;
                }

            break;
        }

        if ($Creatable) {
            $VariableId = @$this->GetIDForIdent($Data['Ident']);
            if ($VariableId > 0) {
                $Color = '#FFFFFF';
            } else {
                $VariableId = $this->Translate('Click to create variable');
                $Color = '#C0FFC0';
            }
        } else {
            $VariableId = '';
            $Color = '#DFDFDF';
        }
        $NewSetting['Data'] = $Data;
        $NewSetting['rowColor'] = $Color;
        $NewSetting['VariableId'] = $VariableId;
        $NewSetting['label'] = $Setting['label'];
        $NewSetting['id'] = $Setting['id'];
        $NewSetting['type'] = $Setting['type'] . ':' . $Setting['control']['type'];
        if (array_key_exists('help', $Setting)) {
            $NewSetting['help'] = $Setting['help'];
            $NewSetting['Help'] = $this->Translate('Click for help');
        } else {
            $NewSetting['help'] = '';
            $NewSetting['Help'] = $this->Translate('No help available');
        }
        $Setting = $NewSetting;
    }
    private function CreateSettingsVariable(array $VariableData)
    {
        $this->LogMessage(print_r($VariableData, true), KL_MESSAGE);
        if (($VariableData['Profile']['Name'] !== '') && ($VariableData['Profile']['Name'][0] !== '~')) {
            if (array_key_exists('Associations', $VariableData['Profile'])) {
                $this->RegisterProfileIntegerEx(
                    $VariableData['Profile']['Name'],
                '',
                '',
                $VariableData['Profile']['Suffix'],
                 $VariableData['Profile']['Associations']);
                if ($VariableData['Profile']['MaxValue'] > count($VariableData['Profile']['Associations'])) {
                }
                IPS_SetVariableProfileValues(
                     $VariableData['Profile']['Name'],
                     $VariableData['Profile']['MinValue'],
                     $VariableData['Profile']['MaxValue'],
                     $VariableData['Profile']['StepSize']
                     );
            } else {
                $this->RegisterProfile(
                $VariableData['VarType'],
                $VariableData['Profile']['Name'],
                '',
                '',
                $VariableData['Profile']['Suffix'],
                $VariableData['Profile']['MinValue'],
                $VariableData['Profile']['MaxValue'],
                $VariableData['Profile']['StepSize']
            );
            }
        }
        $this->MaintainVariable(
            $VariableData['Ident'],
            $VariableData['Name'],
            $VariableData['VarType'],
            $VariableData['Profile']['Name'],
            0,
            true
        );
        $this->EnableAction($VariableData['Ident']);
        $this->ReloadForm();
    }
}

/** @} */
