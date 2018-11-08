<?php

declare(strict_types = 1);

/*
 * @addtogroup kodi
 * @{
 *
 * @package       Kodi
 * @file          module.php
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2018 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       2.0
 *
 */

/**
 * KodiConfigurator Klasse für die einfache Erstellungvon IPS-Instanzen in IPS.
 * Erweitert IPSModule.
 *
 * @package       Kodi
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2018 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       2.0
 * @example <b>Ohne</b>
 */
class KodiConfigurator extends IPSModule
{
    /**
     * Zuordnung der möglichen Kodi-Instanzen zu den GUIDs
     *
     * @access private
     *  @var array Key ist der Name, Value ist die GUID
     */
    public static $Name = array(
        "Addons"        => "{0731DD94-99E6-43D8-9BE3-2854B0C6EF24}",
        "Application"   => "{3AF936C4-9B31-48EC-84D8-A30F0BEF104C}",
        "Audio Library" => "{AA078FB4-30C1-4EF1-A2DE-5F957F58BDDC}",
        "Favourites"    => "{DA2C90A2-3863-4454-9B07-FBD083420E10}",
        "Files"         => "{54827867-BB3B-4ACC-A453-7A8D4DC78130}",
        "GUI"           => "{E15F2C11-0B28-4CFB-AEE6-463BD313A964}",
        "Input"         => "{9F3BE8BB-4610-49F4-A41A-40E14F641F43}",
        "TV/Radio"      => "{9D73D46E-7B80-4814-A7B2-31768DC6AB7E}",
        "System"        => "{03E18A60-02FD-45E8-8A2C-1F8E247C92D0}",
        "Video Library" => "{07943DF4-FAB9-454F-AA9E-702A5F9C9D57}"
    );

    /**
     * Zuordnung der möglichen Player-Instanzen zu den GUIDs und Medientyp.
     *
     * @access private
     *  @var array Key ist der Name, Index "GUID" ist die GUID und Index "Typ" ist der Medientyp.
     */
    public static $PlayerTypes = array(
        "Audio Player"   => array("GUID" => "{BA014AD9-9568-4F12-BE31-17D37BFED06D}", 'PlayerID' => 0),
        "Video Player"   => array("GUID" => "{BA014AD9-9568-4F12-BE31-17D37BFED06D}", 'PlayerID' => 1),
        "Picture Player" => array("GUID" => "{BA014AD9-9568-4F12-BE31-17D37BFED06D}", 'PlayerID' => 2),
    );

    /**
     * Zuordnung der möglichen Playlist-Instanzen zu den GUIDs und Medientyp.
     *
     * @access private
     *  @var array Key ist der Name, Index "GUID" ist die GUID und Index "Typ" ist der Medientyp.
     */
    public static $PlayeListTypes = array(
        "Audio Playlist"   => array("GUID" => "{7D73D0FF-0CC7-43D0-A196-0D6143E52756}", 'PlaylistID' => 0),
        "Video Playlist"   => array("GUID" => "{7D73D0FF-0CC7-43D0-A196-0D6143E52756}", 'PlaylistID' => 1),
        "Picture Playlist" => array("GUID" => "{7D73D0FF-0CC7-43D0-A196-0D6143E52756}", 'PlaylistID' => 2),
    );

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Create()
    {
        parent::Create();
        $this->ConnectParent("{D2F106B5-4473-4C19-A48F-812E8BAA316C}");
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function ApplyChanges()
    {
        parent::ApplyChanges();
    }

    ################## PRIVATE
    /**
     * Liefert den aktuell verbundenen Splitter.
     *
     * @access private
     * @return bool|int FALSE wenn kein Splitter vorhanden, sonst die ID des Splitter.
     */
    private function GetSplitter()
    {
        $SplitterID = IPS_GetInstance($this->InstanceID)['ConnectionID'];
        if ($SplitterID == 0) {
            trigger_error('Not connected to Splitter.' . PHP_EOL, E_USER_WARNING);
            return false;
        }
        return $SplitterID;
    }

    /**
     * Sucht anhand einer GUID eine mit dem Splitter verbundene Instanz.
     *
     * @access private
     * @param int $SplitterID ID des Splitter
     * @param string $ModuleID GUID der Instanz welche gesucht wird
     * @return bool|int FALSE wenn keine Instanz gefunden, sonst die ID der Instanz.
     */
    private function SearchInstance(int $SplitterID, string $ModuleID)
    {
        $InstanceIDs = IPS_GetInstanceListByModuleID($ModuleID);
        foreach ($InstanceIDs as $InstanceID) {
            if (IPS_GetInstance($InstanceID)['ConnectionID'] == $SplitterID) {
                return $InstanceID;
            }
        }
        return false;
    }

    /**
     * Sucht anhand einer GUID und eines Medientyp eine mit dem Splitter verbundene Instanz.
     *
     * @access private
     * @param int $SplitterID ID des Splitter
     * @param string $ModuleID GUID der Instanz welche gesucht wird
     * @param int $Typ Medientyp
     * @return bool|int FALSE wenn keine Instanz gefunden, sonst die ID der Instanz.
     */
    private function SearchPlayerInstance(int $SplitterID, string $ModuleID, int $Typ)
    {
        $InstanceIDs = IPS_GetInstanceListByModuleID($ModuleID);
        foreach ($InstanceIDs as $InstanceID) {
            if (IPS_GetInstance($InstanceID)['ConnectionID'] == $SplitterID) {
                if (IPS_GetProperty($InstanceID, 'PlayerID') == $Typ) {
                    return $InstanceID;
                }
            }
        }
        return false;
    }

    /**
     * Sucht anhand einer GUID und eines Medientyp eine mit dem Splitter verbundene Instanz.
     *
     * @access private
     * @param int $SplitterID ID des Splitter
     * @param string $ModuleID GUID der Instanz welche gesucht wird
     * @param int $Typ Medientyp
     * @return bool|int FALSE wenn keine Instanz gefunden, sonst die ID der Instanz.
     */
    private function SearchPlaylistInstance(int $SplitterID, string $ModuleID, int $Typ)
    {
        $InstanceIDs = IPS_GetInstanceListByModuleID($ModuleID);
        foreach ($InstanceIDs as $InstanceID) {
            if (IPS_GetInstance($InstanceID)['ConnectionID'] == $SplitterID) {
                if (IPS_GetProperty($InstanceID, 'PlaylistID') == $Typ) {
                    return $InstanceID;
                }
            }
        }
        return false;
    }

    ################## PUBLIC
    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function GetConfigurationForm()
    {
        $SplitterID = @$this->GetSplitter();

        if ($SplitterID === false) {
            return '{"actions":[{"type": "Label","caption": "Not connected to Splitter."}]}';
        }
        $Form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        if (IPS_GetInstance($SplitterID)['InstanceStatus'] != IS_ACTIVE) {
            $Form['actions'][] = [
                "type"  => "PopupAlert",
                "popup" => [
                    "items" => [[
                    "type"    => "Label",
                    "caption" => "Instance has no active parent."
                        ]]
                ]
            ];
            $this->SendDebug('FORM', json_encode($Form), 0);
            $this->SendDebug('FORM', json_last_error_msg(), 0);
            return json_encode($Form);
        }

        foreach (static::$PlayerTypes as $Name => $ModuleData) {
            $Value = [
                'type'   => $this->Translate($Name),
                'create' => [
                    'moduleID'      => $ModuleData['GUID'],
                    'configuration' => ['PlayerID' => $ModuleData['PlayerID']]
                ]
            ];
            $InstanzID = $this->SearchPlayerInstance($SplitterID, $ModuleData['GUID'], $ModuleData['PlayerID']);
            if ($InstanzID == false) {
                $Value['name'] = 'Kodi ' . $this->Translate($Name);
                $Value['location'] = '';
            } else {
                $Value['name'] = IPS_GetName($InstanzID);
                $Value['location'] = stristr(IPS_GetLocation($InstanzID), IPS_GetName($InstanzID), true);
                $Value['instanceID'] = $InstanzID;
            }
            $Values[] = $Value;
        }

        foreach (static::$PlayeListTypes as $Name => $ModuleData) {
            $Value = [
                'type'   => $this->Translate($Name),
                'create' => [
                    'moduleID'      => $ModuleData['GUID'],
                    'configuration' => ['PlaylistID' => $ModuleData['PlaylistID']]
                ]
            ];
            $InstanzID = $this->SearchPlaylistInstance($SplitterID, $ModuleData['GUID'], $ModuleData['PlaylistID']);
            if ($InstanzID == false) {
                $Value['name'] = 'Kodi ' . $this->Translate($Name);
                $Value['location'] = '';
            } else {
                $Value['name'] = IPS_GetName($InstanzID);
                $Value['location'] = stristr(IPS_GetLocation($InstanzID), IPS_GetName($InstanzID), true);
                $Value['instanceID'] = $InstanzID;
            }
            $Values[] = $Value;
        }

        foreach (static::$Name as $Name => $ModuleID) {
            $Value = [
                'type'   => $this->Translate($Name),
                'create' => [
                    'moduleID'      => $ModuleID,
                    'configuration' => new stdClass()
                ]
            ];
            $InstanzID = $this->SearchInstance($SplitterID, $ModuleID);
            if ($InstanzID == false) {
                $Value['name'] = 'Kodi ' . $this->Translate($Name);
                $Value['location'] = '';
            } else {
                $Value['name'] = IPS_GetName($InstanzID);
                $Value['location'] = stristr(IPS_GetLocation($InstanzID), IPS_GetName($InstanzID), true);
                $Value['instanceID'] = $InstanzID;
            }
            $Values[] = $Value;
        }

        $Form['actions'][0]['values'] = $Values;
        $this->SendDebug('FORM', json_encode($Form), 0);
        $this->SendDebug('FORM', json_last_error_msg(), 0);
        return json_encode($Form);
    }
}

/** @} */
