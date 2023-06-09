<?php

declare(strict_types=1);
/** @addtogroup kodi
 * @{
 *
 * @package       Kodi
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2020 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       3.00
 * @example <b>Ohne</b>
 */

/**
 * Definiert eine KodiRPCException.
 *
 * @package       Kodi
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2020 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       3.00
 * @example <b>Ohne</b>
 */
class KodiRPCException extends Exception
{
    public function __construct($message, $code, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

/**
 * Enthält einen Kodi-RPC Datensatz.
 *
 * @package       Kodi
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2020 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       3.00
 * @example <b>Ohne</b>
 *
 * @method null ExecuteAddon
 * @method null GetAddons
 * @method array GetAddonDetails(array $Params)
 * @method null SetAddonEnabled
 *
 * @method null SetVolume(array $Params (int "volume" Neue Lautstärke)) Setzen der Lautstärke.
 * @method null SetMute(array $Params (bool "mute" Neuer Wert der Stummschaltung)) Setzen der Stummschaltung.
 * @method null Quit(null) Beendet Kodi.
 *
 * @method null Clean(null) Startet das bereinigen der Datenbank.
 * @method null Export(array $Params (array "options" (string "path" Ziel-Verzeichnis für den Export) (bool "overwrite" Vorhandene Daten überschreiben.) (bool "images" Bilder mit exportieren.)) Exportiert die Audio Datenbank.
 * @method null GetAlbumDetails(array $Params (string "albumid" AlbumID) (array "properties" Zu lesende Album-Eigenschaften) Liest die Eigenschaften eines Album aus.
 * @method null GetAlbums(null) Liest einen Teil der Eigenschaften aller Alben aus.
 * @method array GetArtistDetails (array $Params (string "artistid" ArtistID) (array "properties" Zu lesende Künstler-Eigenschaften) Liest die Eigenschaften eines Künstler aus.
 * @method null GetArtists(null) Liest einen Teil der Eigenschaften aller Künstler aus.
 * @method null GetGenres(null) Liest einen Teil der Eigenschaften aller Genres aus.
 * @method null GetRecentlyAddedAlbums(null) Liest die Eigenschaften der zuletzt hinzugefügten Alben aus.
 * @method null GetRecentlyAddedSongs(null) Liest die Eigenschaften der zuletzt hinzugefügten Songs aus.
 * @method null GetRecentlyPlayedAlbums(null) Liest die Eigenschaften der zuletzt abgespielten Alben aus.
 * @method null GetRecentlyPlayedSongs(null) Liest die Eigenschaften der zuletzt abgespielten Songs aus.
 * @method null GetSongDetails (array $Params (string "songid" SongID) (array "properties" Zu lesende Song-Eigenschaften) Liest die Eigenschaften eines Songs aus.
 * @method null GetSongs(null) Liest die Eigenschaften aller Songs aus.
 * @method null Scan(null) Startet das Scannen von PVR-Kanälen oder von Quellen für neue Einträge in der Datenbank.
 * @method null GetFavourites
 * @method null GetSources(array $Params (string "media"  enum["video", "music", "pictures", "files", "programs"])) Liest die Quellen.
 * @method null GetFileDetails(array $Params (string "file" Dateiname) (string "media"  enum["video", "music", "pictures", "files", "programs"]) (array "properties" Zu lesende Eigenschaften)) Liest die Quellen.
 * @method null GetDirectory(array $Params (string "directory" Verzeichnis welches gelesen werden soll.)) Liest ein Verzeichnis aus.
 * @method null SetFullscreen(array $Params (bool "fullscreen"))
 * @method null ShowNotification($Data) ???
 * @method null ActivateWindow(array $Params (int "window" ID des Fensters)) Aktiviert ein Fenster.
 * @method null Up(null) Tastendruck hoch.
 * @method null Down(null) Tastendruck runter.
 * @method null Left(null) Tastendruck links.
 * @method null Right(null) Tastendruck right.
 * @method null Back(null) Tastendruck zurück.
 * @method null ContextMenu(null) Tastendruck Context-Menü.
 * @method null Home(null) Tastendruck Home.
 * @method null Info(null) Tastendruck Info.
 * @method null Select(null) Tastendruck Select.
 * @method null ShowOSD(null) OSD Anzeigen.
 * @method null ShowCodec(null) Codec-Info anzeigen.
 * @method null ExecuteAction(array $Params (string "action" Die auszuführende Aktion)) Sendet eine Aktion.
 * @method null SendText(array $Params (string "text" Zu sender String) (bool "done" True zum beenden der Eingabe)) Sendet einen Eingabetext.
 *
 * @method null Record(array $Params (bool "record" Starten/Stoppen) (string "channel" Kanal für die Aufnahme)) Startet/Beendet eine laufende Aufnahme.
 *
 * @method null GetBroadcasts
 * @method null GetBroadcastDetails
 * @method null GetChannels
 * @method null GetChannelDetails
 * @method null GetChannelGroups
 * @method null GetChannelGroupDetails
 * @method null GetRecordings
 * @method null GetRecordingDetails
 * @method null GetTimers
 * @method null GetTimerDetails
 *
 * @method null GetActivePlayers
 * @method null GetItem
 * @method null GetPlayers
 * @method null GetProperties
 * @method null GoTo
 * @method null Move
 * @method null Open
 * @method null PlayPause
 * @method null Rotate
 * @method null Seek
 * @method null SetAudioStream
 * @method null SetPartymode
 * @method null SetRepeat
 * @method null SetShuffle
 * @method null SetSpeed
 * @method null SetSubtitle
 * @method null Stop
 * @method null Zoom
 *
 * @method null Add
 * @method null Clear
 * @method null GetItems
 * @method null GetPlaylists
 * @method null Insert
 * @method null Remove
 * @method null Swap
 *
 * @method null Shutdown(null) Führt einen Shutdown auf Betriebssystemebene aus.
 * @method null Hibernate(null) Führt einen Hibernate auf Betriebssystemebene aus.
 * @method null Suspend(null) Führt einen Suspend auf Betriebssystemebene aus.
 * @method null Reboot(null) Führt einen Reboot auf Betriebssystemebene aus.
 * @method null EjectOpticalDrive(null) Öffnet das Optische Laufwerk.
 * @method null GetEpisodeDetails (array $Params (string "episodeid" EpisodeID) (array "properties" Zu lesende Episoden-Eigenschaften) Liest die Eigenschaften eine Episode aus.
 * @method null GetEpisodes(null) Liest die Eigenschaften aller Episoden aus.
 * @method null GetRecentlyAddedEpisodes(null) Liest die Eigenschaften der zuletzt hinzugefügten Episoden aus.
 * @method null GetMovieDetails (array $Params (string "movieid" MovieID) (array "properties" Zu lesende Films-Eigenschaften) Liest die Eigenschaften eines Film aus.
 * @method null GetMovies(null) Liest die Eigenschaften aller Filme aus.
 * @method null GetRecentlyAddedMovies(null) Liest die Eigenschaften der zuletzt hinzugefügten Filme aus.
 * @method null GetMovieSetDetails (array $Params (string "setid" SetID) (array "properties" Zu lesende Movie-Set-Eigenschaften) Liest die Eigenschaften eines Movie-Set aus.
 * @method null GetMovieSets (null) Liest die Eigenschaften alle Movie-Sets aus.
 * @method null GetMusicVideoDetails (array $Params (string "musicvideoid" MusicVideoID) (array "properties" Zu lesende Musikvideo-Eigenschaften) Liest die Eigenschaften eines Musikvideos aus.
 * @method null GetRecentlyAddedMusicVideos(null) Liest die Eigenschaften der zuletzt hinzugefügten Musikvideos aus.
 * @method null GetSeasons (array $Params (string "tvshowid" TVShowID) (array "properties" Zu lesende Season Eigenschaften) Liest die Eigenschaften einer Season aus.
 * @method null GetTVShowDetails (array $Params (string "tvshowid" TVShowID) (array "properties" Zu lesende TV-Serien Eigenschaften) Liest die Eigenschaften einer TV-Serie.
 * @method null GetTVShows (null) Liest die Eigenschaften alle TV-Serien.
 */
class Kodi_RPC_Data extends stdClass
{
    public static $MethodTyp = 0;
    public static $EventTyp = 1;
    public static $ResultTyp = 2;

    /**
     * Typ der Daten
     * @access private
     * @var enum [ Kodi_RPC_Data::EventTyp, Kodi_RPC_Data::ParamTyp, Kodi_RPC_Data::ResultTyp]
     */
    private ?int $Typ;

    /**
     * RPC-Namespace
     * @access private
     * @var string
     */
    private ?string $Namespace;

    /**
     * Name der Methode
     * @access private
     * @var string
     */
    private ?string $Method;

    /**
     * Enthält Fehlermeldungen der Methode
     * @access private
     * @var object
     */
    private mixed $Error;

    /**
     * Parameter der Methode
     * @access private
     * @var object
     */
    private mixed $Params;

    /**
     * Antwort der Methode
     * @access private
     * @var object
     */
    private mixed $Result;

    /**
     * Id des RPC-Objektes
     * @access private
     * @var int
     */
    private ?int $Id;

    /**
     * Erstellt ein Kodi_RPC_Data Objekt.
     *
     * @access public
     * @param string $Namespace [optional] Der RPC Namespace
     * @param string $Method [optional] RPC-Methode
     * @param object $Params [optional] Parameter der Methode
     * @param int $Id [optional] Id des RPC-Objektes
     * @return Kodi_RPC_Data
     */
    public function __construct($Namespace = null, $Method = null, $Params = null, $Id = null)
    {
        $this->Typ = null;
        $this->Namespace = null;
        $this->Method = null;
        $this->Error = null;
        $this->Params = null;
        $this->Result = null;
        $this->Id = null;

        if (!is_null($Namespace)) {
            $this->Namespace = $Namespace;
        }
        if (is_null($Method)) {
            $this->Typ = self::$ResultTyp;
        } else {
            $this->Method = $Method;
            $this->Typ = self::$MethodTyp;
        }
        if (is_array($Params)) {
            $this->Params = (object) $Params;
        }
        if (is_object($Params)) {
            $this->Params = (object) $Params;
        }
        if (is_null($Id)) {
            $this->Id = (int) round(explode(' ', microtime())[0] * 10000);
        } else {
            if ($Id > 0) {
                $this->Id = $Id;
            } else {
                $this->Typ = self::$EventTyp;
            }
        }
    }

    /**
     *
     * @access public
     * @param string $name PropertyName
     * @return mixed Value of Name
     */
    public function __get(string $name): mixed
    {
        return $this->{$name};
    }

    /**
     * Führt eine RPC-Methode aus.
     *
     *
     * @access public
     * @param string $name Auszuführende RPC-Methode
     * @param object|array $arguments Parameter der RPC-Methode.
     */
    public function __call(string $name, array $arguments): void
    {
        $this->Method = $name;
        $this->Typ = self::$MethodTyp;
        if (count($arguments) == 0) {
            $this->Params = new stdClass();
        } else {
            if (is_array($arguments[0])) {
                $this->Params = (object) $arguments[0];
            }
            if (is_object($arguments[0])) {
                $this->Params = $arguments[0];
            }
        }
        $this->Id = (int) round(explode(' ', microtime())[0] * 10000);
    }

    /**
     * Gibt die RPC Antwort auf eine Anfrage zurück
     *
     *
     * @access public
     * @return array|object|mixed|KodiRPCException Enthält die Antwort des RPC-Server. Im Fehlerfall wird ein Objekt vom Typ KodiRPCException zurückgegeben.
     */
    public function GetResult(): mixed
    {
        if (!is_null($this->Error)) {
            return $this->GetErrorObject();
        }
        if (!is_null($this->Result)) {
            return $this->Result;
        }
        return [];
    }

    /**
     * Gibt die Daten eines RPC-Event zurück.
     *
     * @access public
     * @return object|mixed  Enthält die Daten eines RPC-Event des RPC-Server.
     */
    public function GetEvent(): mixed
    {
        if (property_exists($this->Params, 'data')) {
            return $this->Params->data;
        } else {
            return null;
        }
    }

    /**
     * Schreibt die Daten aus $Data in das Kodi_RPC_Data-Objekt.
     *
     * @access public
     * @param object $Data Muss ein Objekt sein, welche vom Kodi-Splitter erzeugt wurde.
     */
    public function CreateFromGenericObject(object $Data): void
    {
        if (property_exists($Data, 'Error')) {
            $this->Error = $Data->Error;
        }
        if (property_exists($Data, 'Result')) {
            $this->Result = $Data->Result;
        }
        if (property_exists($Data, 'Namespace')) {
            $this->Namespace = $Data->Namespace;
        }
        if (property_exists($Data, 'Method')) {
            $this->Method = $Data->Method;
            $this->Typ = self::$MethodTyp;
        } else {
            $this->Typ = self::$ResultTyp;
        }
        if (property_exists($Data, 'Params')) {
            $this->Params = $Data->Params;
        }

        if (property_exists($Data, 'Id')) {
            $this->Id = $Data->Id;
        } else {
            $this->Typ = self::$EventTyp;
        }

        if (property_exists($Data, 'Typ')) {
            $this->Typ = $Data->Typ;
        }
    }

    /**
     * Erzeugt einen, mit der GUID versehenen, JSON-kodierten String.
     *
     * @access public
     * @param string $GUID Die Interface-GUID welche mit in den JSON-String integriert werden soll.
     * @return string JSON-kodierter String für IPS-Dateninterface.
     */
    public function ToJSONString(string $GUID): string
    {
        $SendData = new stdClass();
        $SendData->DataID = $GUID;
        if (!is_null($this->Id)) {
            $SendData->Id = $this->Id;
        }
        if (!is_null($this->Namespace)) {
            $SendData->Namespace = $this->Namespace;
        }
        if (!is_null($this->Method)) {
            $SendData->Method = $this->Method;
        }
        if (!is_null($this->Params)) {
            $SendData->Params = $this->Params;
        }
        if (!is_null($this->Error)) {
            $SendData->Error = $this->Error;
        }
        if (!is_null($this->Result)) {
            $SendData->Result = $this->Result;
        }
        if (!is_null($this->Typ)) {
            $SendData->Typ = $this->Typ;
        }
        return json_encode($SendData);
    }

    /**
     * Schreibt die Daten aus $Data in das Kodi_RPC_Data-Objekt.
     *
     * @access public
     * @param string $Data Ein JSON-kodierter RPC-String vom RPC-Server.
     */
    public function CreateFromJSONString(string $Data): bool
    {
        $Json = json_decode($Data);
        if (is_null($Json)) {
            return false;
        }
        if (property_exists($Json, 'error')) {
            $this->Error = $Json->error;
        }
        if (property_exists($Json, 'method')) {
            $part = explode('.', $Json->method);
            $this->Namespace = array_shift($part);
            $this->Method = implode('.', $part);
        }
        if (property_exists($Json, 'params')) {
            $this->Params = $Json->params;
        }
        if (property_exists($Json, 'result')) {
            $this->Result = $Json->result;
            $this->Typ = self::$ResultTyp;
        }
        if (property_exists($Json, 'id')) {
            $this->Id = $Json->id;
        } else {
            $this->Id = null;
            $this->Typ = self::$EventTyp;
        }
        return true;
    }

    /**
     * Erzeugt einen, mit der GUID versehenen, JSON-kodierten String zum versand an den RPC-Server.
     *
     * @access public
     * @param string $GUID Die Interface-GUID welche mit in den JSON-String integriert werden soll.
     * @return string JSON-kodierter String für IPS-Dateninterface.
     */
    public function ToRPCJSONString(string $GUID): string
    {
        $RPC = new stdClass();
        $RPC->jsonrpc = '2.0';
        $RPC->method = $this->Namespace . '.' . $this->Method;
        if (!is_null($this->Params)) {
            $RPC->params = $this->Params;
        }
        $RPC->id = $this->Id;
        $SendData = new stdClass();
        $SendData->DataID = $GUID;
        $SendData->Buffer = bin2hex(json_encode($RPC));
        return json_encode($SendData);
    }

    /**
     * Erzeugt einen, JSON-kodierten String zum versand an den RPC-Server.
     *
     * @access public
     * @return string JSON-kodierter String.
     */
    public function ToRawRPCJSONString(): string
    {
        $RPC = new stdClass();
        $RPC->jsonrpc = '2.0';
        $RPC->method = $this->Namespace . '.' . $this->Method;
        if (!is_null($this->Params)) {
            $RPC->params = $this->Params;
        }
        $RPC->id = $this->Id;
        return json_encode($RPC);
    }

    /**
     * Erzeugt aus dem $Item ein Array.
     *
     * @access public
     * @param object $Item Das Objekt welches zu einem Array konvertiert wird.
     * @return array Das konvertierte Objekt als Array.
     */
    public function ToArray(mixed $Item): array
    {
        return json_decode(json_encode($Item), true);
    }

    /**
     * Gibt ein Objekt KodiRPCException mit den enthaltenen Fehlermeldung des RPC-Servers zurück.
     *
     * @access private
     * @return KodiRPCException  Enthält die Daten der Fehlermeldung des RPC-Server.
     */
    private function GetErrorObject(): KodiRPCException
    {
        if (property_exists($this->Error, 'data')) {
            if (property_exists($this->Error->data, 'stack')) {
                if (property_exists($this->Error->data->stack, 'message')) {
                    return new KodiRPCException((string) $this->Error->data->stack->message, (int) $this->Error->code);
                } else {
                    return new KodiRPCException((string) $this->Error->data->message . ':' . (string) $this->Error->data->stack->name, (int) $this->Error->code);
                }
            } else {
                return new KodiRPCException($this->Error->data->message, (int) $this->Error->code);
            }
        } else {
            return new KodiRPCException((string) $this->Error->message, (int) $this->Error->code);
        }
    }
}

/** @} */
