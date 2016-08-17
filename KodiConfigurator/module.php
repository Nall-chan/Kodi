<?

require_once(__DIR__ . "/../KodiClass.php");  // diverse Klassen

/*
 * @addtogroup kodi
 * @{
 *
 * @package       Kodi
 * @file          module.php
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2016 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       1.0
 *
 */

/**
 * KodiConfigurator Klasse für die einfache Erstellungvon IPS-Instanzen in IPS.
 * Erweitert IPSModule.
 *
 * @package       Kodi
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2016 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       1.0
 * @example <b>Ohne</b>
 */
class KodiConfigurator extends IPSModule
{

    /**
     * PlayerID für Audio
     * 
     * @access private
     * @static int
     * @value 0
     */
    const Audio = 0;

    /**
     * PlayerID für Video
     * 
     * @access private
     * @static int
     * @value 1
     */
    const Video = 1;

    /**
     * PlayerID für Bilder
     * 
     * @access private
     * @static int
     * @value 2
     */
    const Picture = 2;

    /**
     * Zuordnung der von Kodi gemeldeten Medientypen zu Klartextnamen
     * 
     * @access private
     *  @var array Key ist der Medientyp, Value ist der Name
     */
    static $PlayerName = array(
        self::Audio => 'Audio',
        self::Video => 'Video',
        self::Picture => 'Picture'
    );

    /**
     * Zuordnung der möglichen Kodi-Instanzen zu den GUIDs
     * 
     * @access private
     *  @var array Key ist der Name, Value ist die GUID
     */
    static $Types = array(
        "Addons" => "{0731DD94-99E6-43D8-9BE3-2854B0C6EF24}",
        "Application" => "{3AF936C4-9B31-48EC-84D8-A30F0BEF104C}",
        "Audio Library" => "{AA078FB4-30C1-4EF1-A2DE-5F957F58BDDC}",
        "Favourites" => "{DA2C90A2-3863-4454-9B07-FBD083420E10}",
        "Files" => "{54827867-BB3B-4ACC-A453-7A8D4DC78130}",
        "GUI" => "{E15F2C11-0B28-4CFB-AEE6-463BD313A964}",
        "Input" => "{9F3BE8BB-4610-49F4-A41A-40E14F641F43}",
        "PVR" => "{9D73D46E-7B80-4814-A7B2-31768DC6AB7E}",
        "System" => "{03E18A60-02FD-45E8-8A2C-1F8E247C92D0}",
        "Video Library" => "{07943DF4-FAB9-454F-AA9E-702A5F9C9D57}"
    );

    /**
     * Zuordnung der möglichen Player-Instanzen zu den GUIDs und Medientyp.
     * 
     * @access private
     *  @var array Key ist der Name, Index "GUID" ist die GUID und Index "Typ" ist der Medientyp.
     */
    static $PlayerTypes = array(
        "Audio Player" => array("GUID" => "{BA014AD9-9568-4F12-BE31-17D37BFED06D}", "Typ" => self::Audio),
        "Video Player" => array("GUID" => "{BA014AD9-9568-4F12-BE31-17D37BFED06D}", "Typ" => self::Video),
        "Picture Player" => array("GUID" => "{BA014AD9-9568-4F12-BE31-17D37BFED06D}", "Typ" => self::Picture),
    );

    /**
     * Zuordnung der möglichen Playlist-Instanzen zu den GUIDs und Medientyp.
     * 
     * @access private
     *  @var array Key ist der Name, Index "GUID" ist die GUID und Index "Typ" ist der Medientyp.
     */
    static $PlayeListTypes = array(
        "Audio Playlist" => array("GUID" => "{7D73D0FF-0CC7-43D0-A196-0D6143E52756}", "Typ" => self::Audio),
        "Video Playlist" => array("GUID" => "{7D73D0FF-0CC7-43D0-A196-0D6143E52756}", "Typ" => self::Video),
        "Picture Playlist" => array("GUID" => "{7D73D0FF-0CC7-43D0-A196-0D6143E52756}", "Typ" => self::Picture),
    );

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Create()
    {
        parent::Create();
        $this->RequireParent("{D2F106B5-4473-4C19-A48F-812E8BAA316C}");
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
        if ($SplitterID == 0)
        {
            trigger_error('Not connected to Splitter.' . PHP_EOL, E_USER_WARNING);
            return FALSE;
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
        foreach ($InstanceIDs as $InstanceID)
        {
            if (IPS_GetInstance($InstanceID)['ConnectionID'] == $SplitterID)
                return $InstanceID;
        }
        return FALSE;
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
        foreach ($InstanceIDs as $InstanceID)
        {
            if (IPS_GetInstance($InstanceID)['ConnectionID'] == $SplitterID)
                if (IPS_GetProperty($InstanceID, 'PlayerID') == $Typ)
                    return $InstanceID;
        }
        return FALSE;
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
        foreach ($InstanceIDs as $InstanceID)
        {
            if (IPS_GetInstance($InstanceID)['ConnectionID'] == $SplitterID)
                if (IPS_GetProperty($InstanceID, 'PlaylistID') == $Typ)
                    return $InstanceID;
        }
        return FALSE;
    }

################## PUBLIC

    /**
     * Erzeugt alle fehlenden Kodi-Instanzen eines Splitters.
     * 
     * @access public
     */
    public function CreateAllInstances()
    {
        $SplitterID = $this->GetSplitter();
        if ($SplitterID === FALSE)
            return;

        foreach (static::$PlayerTypes as $Name => $ModuleData)
        {
            if ($this->SearchPlayerInstance($SplitterID, $ModuleData['GUID'], $ModuleData['Typ']) == FALSE)
                $this->CreatePlayerInstance($ModuleData['GUID'], $ModuleData['Typ']);
        }

        foreach (static::$PlayeListTypes as $Name => $ModuleData)
        {
            if ($this->SearchPlaylistInstance($SplitterID, $ModuleData['GUID'], $ModuleData['Typ']) === FALSE)
                $this->CreatePlaylistInstance($ModuleData['GUID'], $ModuleData['Typ']);
        }

        foreach (static::$Types as $Name => $ModuleID)
        {
            if ($this->SearchInstance($SplitterID, $ModuleID) === FALSE)
                $this->CreateInstance($ModuleID);
        }
    }

    /**
     * Erzeugt eine Kodi-Instanzen anhand der übergebene GUID und verbindet sie mit den aktuellen Splitter des Konfigurators.
     * 
     * @access public
     * @param string $ModuleID GUID der zu erzeugenden Instanz.
     */
    public function CreateInstance(string $ModuleID)
    {
        $SplitterID = $this->GetSplitter();
        if ($SplitterID === FALSE)
            return;

        if ($this->SearchInstance($SplitterID, $ModuleID) === FALSE)
        {
            $DeviceID = IPS_CreateInstance($ModuleID);
            if (IPS_GetInstance($DeviceID)['ConnectionID'] <> $SplitterID)
            {
                IPS_DisconnectInstance($DeviceID);
                IPS_ConnectInstance($DeviceID, $SplitterID);
            }
            IPS_SetName($DeviceID, IPS_GetModule($ModuleID)["Aliases"][0]);
            return $DeviceID;
        }
        else
            trigger_error('Instance already exists.' . PHP_EOL, E_USER_WARNING);
    }

    /**
     * Erzeugt eine Kodi-Instanzen anhand der übergebene GUID und MedienTyp und verbindet sie mit den aktuellen Splitter des Konfigurators.
     * 
     * @access public
     * @param string $ModuleID GUID der zu erzeugenden Instanz.
     * @param int $Typ Meidentyp der zu erzeugenden Instanz.
     */
    public function CreatePlaylistInstance(string $ModuleID, int $Typ)
    {
        $SplitterID = $this->GetSplitter();
        if ($SplitterID === FALSE)
            return;

        if ($this->SearchPlaylistInstance($SplitterID, $ModuleID, $Typ) === FALSE)
        {
            $DeviceID = IPS_CreateInstance($ModuleID);
            if (IPS_GetInstance($DeviceID)['ConnectionID'] <> $SplitterID)
            {
                IPS_DisconnectInstance($DeviceID);
                IPS_ConnectInstance($DeviceID, $SplitterID);
            }
            IPS_SetName($DeviceID, 'Kodi ' . self::$PlayerName[$Typ] . ' Playlist');
            IPS_SetProperty($DeviceID, 'PlaylistID', $Typ);
            IPS_ApplyChanges($DeviceID);
            return $DeviceID;
        }
        else
            trigger_error('Instance already exists.' . PHP_EOL, E_USER_WARNING);
    }

    /**
     * Erzeugt eine Kodi-Instanzen anhand der übergebene GUID und MedienTyp und verbindet sie mit den aktuellen Splitter des Konfigurators.
     * 
     * @access public
     * @param string $ModuleID GUID der zu erzeugenden Instanz.
     * @param int $Typ Meidentyp der zu erzeugenden Instanz.
     */
    public function CreatePlayerInstance(string $ModuleID, int $Typ)
    {
        $SplitterID = $this->GetSplitter();
        if ($SplitterID === FALSE)
            return;

        if ($this->SearchPlayerInstance($SplitterID, $ModuleID, $Typ) === FALSE)
        {
            $DeviceID = IPS_CreateInstance($ModuleID);
            if (IPS_GetInstance($DeviceID)['ConnectionID'] <> $SplitterID)
            {
                IPS_DisconnectInstance($DeviceID);
                IPS_ConnectInstance($DeviceID, $SplitterID);
            }
            IPS_SetName($DeviceID, 'Kodi ' . self::$PlayerName[$Typ] . ' Player');
            IPS_SetProperty($DeviceID, 'PlayerID', $Typ);
            IPS_ApplyChanges($DeviceID);
            return $DeviceID;
        }
        else
            trigger_error('Instance already exists.' . PHP_EOL, E_USER_WARNING);
    }

    /**
     * Interne Funktion des SDK.
     * 
     * @access public
     */
    public function GetConfigurationForm()
    {
        $SplitterID = @$this->GetSplitter();

        if ($SplitterID === FALSE)
            return '{"actions":[{"type": "Label","label": "Not connected to Splitter."}]}';

        $Line = array();
        foreach (static::$PlayerTypes as $Name => $ModuleData)
        {
            if ($this->SearchPlayerInstance($SplitterID, $ModuleData['GUID'], $ModuleData['Typ']) == FALSE)
                $Line[] = '{"type": "Button","label": "' . $Name . '","onClick": "KODICONF_CreatePlayerInstance($id,\'' . $ModuleData['GUID'] . '\',\'' . $ModuleData['Typ'] . '\');"}';
        }

        foreach (static::$PlayeListTypes as $Name => $ModuleData)
        {
            if ($this->SearchPlaylistInstance($SplitterID, $ModuleData['GUID'], $ModuleData['Typ']) === FALSE)
                $Line[] = '{"type": "Button","label": "' . $Name . '","onClick": "KODICONF_CreatePlaylistInstance($id,\'' . $ModuleData['GUID'] . '\',\'' . $ModuleData['Typ'] . '\');"}';
        }

        foreach (static::$Types as $Name => $ModuleID)
        {
            if ($this->SearchInstance($SplitterID, $ModuleID) === FALSE)
                $Line[] = '{"type": "Button","label": "' . $Name . '","onClick": "KODICONF_CreateInstance($id,\'' . $ModuleID . '\');"}';
        }

        if (count($Line) == 0)
        {
            $Line[] = '{"type": "Label","label": "All instances are created."}';
        }
        else
        {
            array_unshift($Line, '{"type": "Button","label": "All / All missing","onClick": "KODICONF_CreateAllInstances($id);"}');
            array_unshift($Line, '{"type": "Label","label": "Push the buttons to create instances."}');
        }
        return '{"actions":[' . implode(',', $Line) . ']}';
    }

################## DUMMYS / WORKAROUNDS - protected

    /**
     * Prüft den Parent auf vorhandensein und Status.
     * 
     * @return bool True wenn Parent vorhanden und in Status 102, sonst false.
     */
    protected function HasActiveParent()
    {
        $instance = IPS_GetInstance($this->InstanceID);
        if ($instance['ConnectionID'] > 0)
        {
            $parent = IPS_GetInstance($instance['ConnectionID']);
            if ($parent['InstanceStatus'] == 102)
                return true;
        }
        return false;
    }

}

/** @} */
?>