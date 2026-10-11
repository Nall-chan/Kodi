<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/KodiClass.php';  // diverse Klassen

/**
 * KodiDevicePlaylist Klasse für den Namespace Playlist der KODI-API.
 * Erweitert KodiBase.
 *
 * @project       KodiDevicePlaylist
 * @file          module.php
 * @package       Kodi
 * @author        Michael Tröger <nall4chan@gmail.com>
 * @copyright     2026 Michael Tröger
 * @license       Custom Non-Commercial Share-Alike License (see LICENSE)
 * @version       3.10
 * @example <b>Ohne</b>
 *
 * @property string $Namespace RPC-Namespace
 * @property array $Properties Alle Properties des RPC-Namespace
 * @property array $Playertype Zuordnung der von Kodi gemeldeten Medientypen zu den PlaylistIDs
 * @property array $ItemList Alle Properties eines Item
 * @property array $ItemListSmall Kleiner Teil der Properties eines Item
 *
 * @property int $LastAddonItem
 * @property array $Multi_Playlist
 * @property int $PlaylistId
 * @property int $PlayerId
 */
class KodiDevicePlaylist extends KodiBase
{
    public const PropertyPlaylistID = 'PlaylistID';
    public const PropertyShowPlaylist = 'showPlaylist';
    public const ActionVisibleFormElementsPlaylist = 'showPlaylist';
    public const Hook = 'KodiPlaylist';
    /** Alte Eigenschaft mit der ID des Konfigurations-Skripts (bis 3.02), nur noch in Migrate vorhanden. */
    public const LegacyPropertyConfigScript = 'Playlistconfig';
    public const FormPanelDesign = 'HTMLDesign';
    /**
     * PlaylistID für Audio
     *
     * @access private
     * @static int
     * @value 0
     */
    public const Audio = 0;

    /**
     * PlaylistID für Video
     *
     * @access private
     * @static int
     * @value 1
     */
    public const Video = 1;

    /**
     * PlaylistID für Bilder
     *
     * @access private
     * @static int
     * @value 2
     */
    public const Pictures = 2;

    /** Spalten der Playlist-Tabelle: Schlüssel, Überschrift, Breite (em), sichtbar. */
    private const TableColumns = [
        ['Play', '', 50, true],
        ['Position', 'Pos', 50, true],
        ['Type', 'Type', 100, true],
        ['Title', 'Title', 300, true],
        ['Showtitle', 'Series', 300, true],
        ['Season', 'S', 50, true],
        ['Episode', 'E', 50, true],
        ['Album', 'Album', 200, true],
        ['Track', 'Track', 50, true],
        ['Disc', 'Disc', 50, true],
        ['Genre', 'Genre', 200, true],
        ['Artist', 'Artist', 200, true],
        ['Year', 'Year', 50, true],
        ['Runtime', 'Duration', 100, true],
        ['Id', 'ID', 50, false],
        ['File', 'File', 300, false]
    ];

    protected static $Namespace = ['Playlist', 'Player'];
    protected static $Properties = [];
    protected static $Playertype = [
        'unknown'   => -1,
        'song'      => 0,
        'audio'     => 0,
        'radio'     => 0,
        'video'     => 1,
        'episode'   => 1,
        'movie'     => 1,
        'tv'        => 1,
        'picture'   => 2,
        'pictures'  => 2
    ];
    protected static $ItemList = [
        'title',
        'artist',
        'albumartist',
        'genre',
        'year',
        'rating',
        'album',
        'track',
        'duration',
        'comment',
        'lyrics',
        'musicbrainztrackid',
        'musicbrainzartistid',
        'musicbrainzalbumid',
        'musicbrainzalbumartistid',
        'playcount',
        'fanart',
        'director',
        'trailer',
        'tagline',
        'plot',
        'plotoutline',
        'originaltitle',
        'lastplayed',
        'writer',
        'studio',
        'mpaa',
        'cast',
        'country',
        'imdbnumber',
        'premiered',
        'productioncode',
        'runtime',
        'set',
        'showlink',
        'streamdetails',
        'top250',
        'votes',
        'firstaired',
        'season',
        'episode',
        'showtitle',
        'thumbnail',
        'file',
        'resume',
        'artistid',
        'albumid',
        'tvshowid',
        'setid',
        'watchedepisodes',
        'disc',
        'tag',
        'art',
        'genreid',
        'displayartist',
        'albumartistid',
        'description',
        'theme',
        'mood',
        'style',
        'albumlabel',
        'sorttitle',
        'episodeguide',
        'uniqueid',
        'dateadded',
        'channel',
        'channeltype',
        'hidden',
        'locked',
        'channelnumber',
        'starttime',
        'endtime'
    ];
    protected static $ItemListSmall = [
        'title',
        'artist',
        'albumartist',
        'genre',
        'year',
        'album',
        'track',
        'duration',
        'plot',
        'runtime',
        'season',
        'episode',
        'showtitle',
        'thumbnail',
        'file',
        'disc',
        'albumlabel',
    ];

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Create(): void
    {
        parent::Create();
        $this->PlaylistId = 0;
        $this->RegisterPropertyInteger(self::PropertyPlaylistID, 0);
        $this->RegisterPropertyBoolean(self::PropertyShowPlaylist, true);
        $this->RegisterTableProperties('', $this->GetDefaultTableConfig(self::TableColumns, true));
    }

    /**
     * Interne Funktion des SDK.
     * Überträgt das Design aus dem alten Konfigurations-Skript in die Eigenschaften.
     *
     * @access public
     */
    public function Migrate(string $JSONData): string
    {
        $Data = json_decode($JSONData);
        if ($this->MigrateTableScript($Data->configuration, self::LegacyPropertyConfigScript, '', $this->GetDefaultTableConfig(self::TableColumns, true))) {
            return json_encode($Data);
        }
        return '';
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Destroy(): void
    {
        if (!IPS_InstanceExists($this->InstanceID)) {
            $this->UnregisterProfile('Tracklist.' . $this->InstanceID . '.Kodi');
        }
        parent::Destroy();
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function ApplyChanges(): void
    {
        $this->UnregisterScript('WebHookPlaylist');
        $this->PlaylistId = $this->ReadPropertyInteger(self::PropertyPlaylistID);
        if ($this->ReadPropertyBoolean(self::PropertyShowPlaylist)) {
            $this->RegisterVariableString('Playlist', 'Playlist', '~HTMLBox', 2);
            $this->RegisterHook(self::Hook . $this->InstanceID);
        } else {
            $this->UnregisterVariable('Playlist');
            $this->UnregisterHook(self::Hook . $this->InstanceID);
        }
        $this->RegisterProfileInteger('Tracklist.' . $this->InstanceID . '.Kodi', '', '', '', 1, 1, 1);
        $this->RegisterVariableInteger('position', $this->Translate('Playlist position'), 'Tracklist.' . $this->InstanceID . '.Kodi', 1);
        $this->EnableAction('position');
        $this->RegisterVariableString('CurrentPlaylist', $this->Translate('Playlist'), '~Playlist', 3);
        $this->EnableAction('CurrentPlaylist');
        $this->Multi_Playlist = [];
        $this->LastAddonItem = 0;

        parent::ApplyChanges();
    }
    public function GetConfigurationForm(): string
    {
        $Form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        $this->FillTableFormPanel($Form['elements'], self::FormPanelDesign, '', $this->ReadPropertyBoolean(self::PropertyShowPlaylist));
        return json_encode($Form);
    }

    public function GoTo(int $Index): bool
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace[1]);
        $KodiData->Open(['item' => ['playlistid' => $this->PlaylistId, 'position' => $Index]], []);
        //$KodiData->GoTo(['playerid' => $this->PlaylistId, 'to' => $Index]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret === 'OK') {
            return true;
        }
        trigger_error($this->Translate('Error on jump to track.'), E_USER_NOTICE);
        return false;
    }
    ################## PUBLIC
    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_Get'.
     * Gibt alle Einträge der Playlist als Array zurück.
     *
     * @access public
     * @return array Das Array mit den Eigenschaften des Item, im Fehlerfall ein leeren Array.
     */
    public function Get(): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace[0], 'GetItems', ['playlistid' => $this->PlaylistId, 'properties' => self::$ItemListSmall]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->items);
        }
        return [];
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_GetPlaylists'.
     * Gibt alle Playlisten von Kodi mit ihrer ID und ihrem Typ zurück.
     *
     * @access public
     * @return array|bool Array mit den Playlisten (playlistid, type) oder false bei Fehler.
     */
    public function GetPlaylists(): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace[0], 'GetPlaylists');
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $KodiData->ToArray($ret);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_GetItem'.
     * Gibt ein Eintrag der Playlist zurück.
     *
     * @access public
     * @param int $Index Index des Items welche gelesen werden soll.
     * @return array Das Array mit den Eigenschaften des Item, im Fehlerfall ein leeren Array.
     */
    public function GetItem(int $Index): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace[0], 'GetItems', ['playlistid' => $this->PlaylistId, 'properties' => self::$ItemListSmall, 'limits' => ['start' => $Index, 'end' => $Index + 1]]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->items);
        }
        return [];
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_AddAlbum'.
     * Fügt der Playlist ein Album hinzu.
     *
     * @access public
     * @param int $AlbumId ID des Album.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */
    public function AddAlbum(int $AlbumId): bool
    {
        return $this->Add('albumid', $AlbumId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_AddArtist'.
     * Fügt der Playlist alle Item eines Artist hinzu.
     *
     * @access public
     * @param int $ArtistId ID des Artist.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */
    public function AddArtist(int $ArtistId): bool
    {
        return $this->Add('artistid', $ArtistId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_AddDirectory'.
     * Fügt der Playlist alle Item eines Verzeichnisses hinzu.
     *
     * @access public
     * @param string $Directory Pfad welcher hinzugefügt werden soll.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */
    public function AddDirectory(string $Directory): bool
    {
        return $this->Add('directory', $Directory);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_AddDirectoryRecursive'.
     * Fügt der Playlist alle Item eines Verzeichnisses, sowie dessen Unterverzeichnisse, hinzu.
     *
     * @access public
     * @param string $Directory Pfad welcher hinzugefügt werden soll.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */
    public function AddDirectoryRecursive(string $Directory): bool
    {
        return $this->Add('directory', $Directory, ['recursive' => true]);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_AddEpisode'.
     * Fügt der Playlist eine Episode hinzu.
     *
     * @access public
     * @param int $EpisodeId ID der Episode.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */
    public function AddEpisode(int $EpisodeId): bool
    {
        return $this->Add('episodeid', $EpisodeId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_AddFile'.
     * Fügt der Playlist eine Datei hinzu.
     *
     * @access public
     * @param string $File Pfad zu einer Datei.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */
    public function AddFile(string $File): bool
    {
        return $this->Add('file', $File);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_AddGenre'.
     * Fügt der Playlist eine komplettes Genre hinzu.
     *
     * @access public
     * @param int $GenreId ID des Genres.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */
    public function AddGenre(int $GenreId): bool
    {
        return $this->Add('genreid', $GenreId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_AddMovie'.
     * Fügt der Playlist ein Film hinzu.
     *
     * @access public
     * @param int $MovieId ID des Filmes.
     * @return bool True bei Erfolg. Sonst false.
     */
    public function AddMovie(int $MovieId): bool
    {
        return $this->Add('movieid', $MovieId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_AddMusicVideo'.
     * Fügt der Playlist ein Musicvideo hinzu.
     *
     * @access public
     * @param int $MusicvideoId ID des Musikvideos.
     * @return bool True bei Erfolg. Sonst false.
     */
    public function AddMusicVideo(int $MusicvideoId): bool
    {
        return $this->Add('musicvideoid', $MusicvideoId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_AddSong'.
     * Fügt der Playlist ein Song hinzu.
     *
     * @access public
     * @param int $SongId ID des Songs.
     * @return bool True bei Erfolg. Sonst false.
     */
    public function AddSong(int $SongId): bool
    {
        return $this->Add('songid', $SongId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_Clear'.
     * Leert die Playlist
     *
     * @access public
     * @return bool True bei Erfolg. Sonst false.
     */
    public function Clear(): bool
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace[0]);
        $KodiData->Clear(['playlistid' => $this->PlaylistId]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret === 'OK') {
            return true;
        }
        trigger_error($this->Translate('Error on clear playlist.'), E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_InsertAlbum'.
     * Fügt in der Playlist ein Album ein.
     * Alle anderen Einträge werden automatisch nach hinten verschoben.
     *
     * @access public
     * @param int $AlbumId ID des Album.
     * @param int $Position Startposition des Album in der Playlist.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */
    public function InsertAlbum(int $AlbumId, int $Position): bool
    {
        return $this->Insert($Position, 'albumid', $AlbumId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_InsertArtist'.
     * Fügt in der Playlist alle Items eines Artist ein.
     * Alle anderen Einträge werden automatisch nach hinten verschoben.
     *
     * @access public
     * @param int $ArtistId ID des Artist.
     * @param int $Position Startposition des Album in der Playlist.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */
    public function InsertArtist(int $ArtistId, int $Position): bool
    {
        return $this->Insert($Position, 'artistid', $ArtistId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_InsertDirectory'.
     * Fügt in der Playlist alle Item eines Verzeichnisses ein.
     * Alle anderen Einträge werden automatisch nach hinten verschoben.
     *
     * @access public
     * @param string $Directory Pfad welcher hinzugefügt werden soll.
     * @param int $Position Startposition des Album in der Playlist.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */
    public function InsertDirectory(string $Directory, int $Position): bool
    {
        return $this->Insert($Position, 'directory', $Directory);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_InsertDirectoryRecursive'.
     * Fügt in der Playlist alle Item eines Verzeichnisses, sowie dessen Unterverzeichnisse, ein.
     * Alle anderen Einträge werden automatisch nach hinten verschoben.
     *
     * @access public
     * @param string $Directory Pfad welcher hinzugefügt werden soll.
     * @param int $Position Startposition des Album in der Playlist.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */
    public function InsertDirectoryRecursive(string $Directory, int $Position): bool
    {
        return $this->Insert($Position, 'directory', $Directory, ['recursive' => true]);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_InsertEpisode'.
     * Fügt in der Playlist eine Episode ein.
     * Alle anderen Einträge werden automatisch nach hinten verschoben.
     *
     * @access public
     * @param int $EpisodeId ID der Episode.
     * @param int $Position Startposition des Album in der Playlist.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */
    public function InsertEpisode(int $EpisodeId, int $Position): bool
    {
        return $this->Insert($Position, 'episodeid', $EpisodeId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_InsertFile'.
     * Fügt in der Playlist eine Datei ein.
     * Alle anderen Einträge werden automatisch nach hinten verschoben.
     *
     * @access public
     * @param string $File Pfad zu einer Datei.
     * @param int $Position Startposition des Album in der Playlist.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */
    public function InsertFile(string $File, int $Position): bool
    {
        return $this->Insert($Position, 'file', $File);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_InsertGenre'.
     * Fügt in der Playlist eine komplettes Genre ein.
     * Alle anderen Einträge werden automatisch nach hinten verschoben.
     *
     * @access public
     * @param int $GenreId ID des Genres welches hinzugefügt werden soll.
     * @param int $Position Startposition des Album in der Playlist.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */
    public function InsertGenre(int $GenreId, int $Position): bool
    {
        return $this->Insert($Position, 'genreid', $GenreId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_InsertMovie'.
     * Fügt in der Playlist ein Filme ein.
     * Alle anderen Einträge werden automatisch nach hinten verschoben.
     *
     * @access public
     * @param int $MovieId ID des Filmes.
     * @param int $Position Startposition des Album in der Playlist.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */
    public function InsertMovie(int $MovieId, int $Position): bool
    {
        return $this->Insert($Position, 'movieid', $MovieId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_InsertMusicVideo'.
     * Fügt in der Playlist ein Musicvideo ein.
     * Alle anderen Einträge werden automatisch nach hinten verschoben.
     *
     * @access public
     * @param int $MusicvideoId ID des Musikvideos.
     * @param int $Position Startposition des Album in der Playlist.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */
    public function InsertMusicVideo(int $MusicvideoId, int $Position): bool
    {
        return $this->Insert($Position, 'musicvideoid', $MusicvideoId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_InsertSong'.
     * Fügt in der Playlist ein Lied ein.
     * Alle anderen Einträge werden automatisch nach hinten verschoben.
     *
     * @access public
     * @param int $SongId ID des Songs.
     * @param int $Position Startposition des Album in der Playlist.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */
    public function InsertSong(int $SongId, int $Position): bool
    {
        return $this->Insert($Position, 'songid', $SongId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_Remove'.
     * Entfernt einen Eintrag aus der Playlist.
     * Alle anderen Einträge werden automatisch nach vorne verschoben.
     *
     * @access public
     * @param int $Position Eintrag welcher entfernt wird.
     * @return bool True bei Erfolg. Sonst false.
     */
    public function Remove(int $Position): bool
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace[0], 'Remove', ['playlistid' => $this->PlaylistId, 'position' => $Position]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret === 'OK') {
            return true;
        }
        trigger_error($this->Translate('Error on remove item.'), E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYLIST_Swap'.
     * Tauscht zwei Einträge innerhalb der Playlist.
     *
     * @access public
     * @param int $Position1 | $Position2 Positionen der Einträge welche untereinander getauscht werden.
     * @return bool True bei Erfolg. Sonst false.
     */
    public function Swap(int $Position1, int $Position2): bool
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace[0], 'Swap', ['playlistid' => $this->PlaylistId, 'position1' => $Position1, 'position2' => $Position2]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret === 'OK') {
            return true;
        }
        trigger_error($this->Translate('Error on swap items.'), E_USER_NOTICE);
        return false;
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
            case self::ActionVisibleFormElementsPlaylist:
                $this->UpdateFormField(self::FormPanelDesign, 'visible', (bool) $Value);
                return;
            case 'position':
                $this->GoTo($Value - 1);
                return;
            case 'CurrentPlaylist':
                $this->GoTo(json_decode($Value, true)['current']);
                return;
            default:
                trigger_error($this->Translate('Invalid Ident.'), E_USER_NOTICE);
                return;
        }
    }

    ################## PRIVATE
    /**
     * Verarbeitet Daten aus dem Webhook.
     *
     * @access protected
     * @global array $_GET
     */
    protected function ProcessHookdata(): void
    {
        if ((!isset($_GET['Index'])) || (!isset($_GET['Secret']))) {
            echo $this->Translate('Bad Request');
            return;
        }
        $CalcSecret = base64_encode(sha1($this->WebHookSecret . '0' . $_GET['Index'], true));
        if ($CalcSecret != rawurldecode($_GET['Secret'])) {
            echo $this->Translate('Access denied');
            return;
        }
        if ($this->GoTo($_GET['Index'] - 1) === true) {
            echo 'OK';
        }
    }

    /**
     * Dekodiert die empfangenen Daten und führt die Statusvariablen nach.
     *
     * @access protected
     * @param string $Method RPC-Funktion ohne Namespace
     * @param object $KodiPayload Der zu dekodierende Datensatz als Objekt.
     */
    protected function Decode(string $Method, mixed $KodiPayload): void
    {
        //prüfen ob Player oder Playlist
        //Player nur bei neuer Position
        if (property_exists($KodiPayload, 'playlistid')) {
            if ($KodiPayload->playlistid != $this->PlaylistId) {
                return;
            }
        } elseif (property_exists($KodiPayload, 'player')) {
            if ($KodiPayload->player->playerid != $this->PlaylistId) {
                return;
            }
        } else {
            if (property_exists($KodiPayload, 'type')) {
                if (self::$Playertype[(string) $KodiPayload->type] != $this->PlayerId) {
                    return;
                }
            } else {
                if (property_exists($KodiPayload, 'item')) {
                    if (property_exists($KodiPayload->item, 'channeltype')) {
                        if (self::$Playertype[(string) $KodiPayload->item->channeltype] != $this->PlaylistId) {
                            return;
                        }
                    } else {
                        if (self::$Playertype[(string) $KodiPayload->item->type] != $this->PlaylistId) {
                            return;
                        }
                    }
                }
            }
        }
        //        elseif (property_exists($KodiPayload, 'item'))
        //        {
        //            if (self::$Playertype[(string) $KodiPayload->item->type] <> $this->PlaylistId)
        //                return false;
        //        }
        //        else
        //            return false;

        $this->SendDebug($Method, $KodiPayload, 0);

        if (property_exists($KodiPayload, 'property')) {
            $KodiPayload = $KodiPayload->property;
        }

        switch ($Method) {
            case 'GetProperties':
            case 'OnPropertyChanged':
                foreach ($KodiPayload as $param => $value) {
                    switch ($param) {
                        case 'position':
                            if ($this->SetValueInteger($param, (int) $value + 1)) {// and ( $KodiPayload->playlistid <> -1))
                                $this->RefreshPlaylist();
                            }
                            break;
                        case 'shuffled':
                            $this->Multi_Playlist = [];
                            $this->LastAddonItem = 0;
                            $KodiData = new Kodi_RPC_Data(self::$Namespace[0], 'GetItems', ['playlistid' => $this->PlaylistId, 'properties' => self::$ItemListSmall]);
                            $ret = $this->SendDirect($KodiData);
                            if (is_null($ret)) {
                                break;
                            }
                            if ($ret->limits->total > 0) {
                                $items = $KodiData->ToArray($ret->items);
                            } else {
                                break;
                            }
                            foreach ($items as $Index => $item) {
                                if ($item['title'] == '') {
                                    if ($item['label'] != '') {
                                        $item['title'] = $item['label'];
                                    } else {
                                        $parts = explode('/', $item['file']);
                                        $item['title'] = array_pop($parts);
                                    }
                                }
                                $this->AddItemToPlayList($Index, $item);
                            }
                            $this->LastAddonItem = $Index;
                            $KodiData = new Kodi_RPC_Data(self::$Namespace[1]);
                            $KodiData->GetProperties(['playerid' => $this->PlaylistId, 'properties' => ['playlistid', 'position']]);
                            $ret = $this->SendDirect($KodiData);
                            if (!is_null($ret)) {
                                $this->Decode('GetProperties', $ret);
                            }
                            $this->RefreshPlaylist(); //Liste neu bauen
                            break;
                    }
                }
                break;
            case 'OnStop':
                $this->SetValueInteger('position', 0);
                // Auch bei OnStop muss die Position neu erfragt und ein RefreshPlaylist erfolgen.
                // No break. Add additional comment above this line if intentional
            case 'OnPlay':
                $KodiData = new Kodi_RPC_Data(self::$Namespace[1]);
                $KodiData->GetProperties(['playerid' => $this->PlaylistId, 'properties' => ['playlistid', 'position']]);
                $ret = $this->SendDirect($KodiData);
                if (!is_null($ret)) {
                    $this->Decode('GetProperties', $ret);
                }

                if ($this->LastAddonItem > 0) {
                    break;
                }

                $KodiData = new Kodi_RPC_Data(self::$Namespace[0], 'GetItems', ['playlistid' => $this->PlaylistId, 'properties' => self::$ItemListSmall]);
                $ret = $this->SendDirect($KodiData);
                if (is_null($ret)) {
                    break;
                }
                if ($ret->limits->total > 0) {
                    $items = $KodiData->ToArray($ret->items);
                } else {
                    break;
                }
                foreach ($items as $Index => $item) {
                    if ($item['title'] == '') {
                        if ($item['label'] != '') {
                            $item['title'] = $item['label'];
                        } else {
                            $parts = explode('/', $item['file']);
                            $item['title'] = array_pop($parts);
                        }
                    }
                    $this->AddItemToPlayList($Index, $item);
                }
                $this->LastAddonItem = $Index;
                $this->RefreshPlaylist(); //Liste neu bauen
                break;
            case 'OnAdd':
                $LastAddonItem = $this->LastAddonItem;
                if (($LastAddonItem > 0) && ($LastAddonItem >= $KodiPayload->position)) {
                    break;
                }
                $KodiData = new Kodi_RPC_Data(self::$Namespace[0], 'GetItems', ['playlistid' => $this->PlaylistId, 'properties' => self::$ItemListSmall, 'limits' => ['start' => $LastAddonItem]]);
                $ret = $this->SendDirect($KodiData);
                if (is_null($ret)) {
                    break;
                }
                if ($ret->limits->total > 0) {
                    $items = $KodiData->ToArray($ret->items);
                } else {
                    break;
                }
                foreach ($items as $Index => $item) {
                    if ($item['title'] == '') {
                        if ($item['label'] != '') {
                            $item['title'] = $item['label'];
                        } else {
                            $parts = explode('/', $item['file']);
                            $item['title'] = array_pop($parts);
                        }
                    }
                    $this->SendDebug('AddItemToPlaylist', $item, 0);
                    $this->AddItemToPlayList($Index + $LastAddonItem, $item);
                }
                $this->LastAddonItem = $Index + $LastAddonItem;
                //                if ($ret->limits->total == $KodiPayload->position + 1)
                //                {
                $this->SendDebug('REFRESH_HTML', 'REFRESH_HTML', 0);
                $this->RefreshPlaylist(); //Liste neu bauen
                //                }
                break;
            case 'OnRemove':
                // function welche ein Item aus dem Buff entfernt
                $this->SendDebug('RemoveItemFromPlayList', $KodiPayload, 0);
                $Liste = $this->Multi_Playlist;
                unset($Liste[$KodiPayload->position]);
                $this->Multi_Playlist = $Liste;
                $this->RefreshPlaylist(); //Liste neu bauen
                break;
            case 'OnClear':
                $this->SendDebug('ClearPlayList', $KodiPayload, 0);
                $this->SetValueInteger('position', 0);
                $this->Multi_Playlist = [];
                $this->LastAddonItem = 0;
                $this->RefreshPlaylist(true);
                break;
            default:
                $this->SendDebug($Method, $KodiPayload, 0);
                break;
        }
    }

    /**
     * Fügt ein Item in die PlayList ein.
     *
     * @access private
     * @param int $Index Der Index des Items.
     * @param array $item Das einzufügende Item.
     */
    private function AddItemToPlayList(int $Index, array $item): void
    {
        $Liste = $this->Multi_Playlist;
        if (count($item) == 0) {
            unset($Liste[$Index]);
        } else {
            $Liste[$Index] = $item;
        }
        $this->Multi_Playlist = $Liste;
    }

    /**
     * Erzeugt aus der Playlist eine HTML-Tabelle für eine ~HTMLBox-Variable.
     *
     * @access private
     * @param bool $Empty Bei TRUE wird eine leere Tabelle erzeugt.
     */
    private function RefreshPlaylist($Empty = false): void
    {
        $Data = [];
        if (!$Empty) {
            $Data = $this->Multi_Playlist;
        }
        if ($Data === false) {
            return;
        }
        $this->SendDebug('RefreshPlaylist', $Data, 0);
        $Name = 'Tracklist.' . $this->InstanceID . '.Kodi';
        if (!IPS_VariableProfileExists($Name)) {
            IPS_CreateVariableProfile($Name, 1);
            IPS_SetVariableProfileValues($Name, (count($Data) == 0 ? 0 : 1), count($Data), 1);
        } else {
            if (IPS_GetVariableProfile($Name)['MaxValue'] != count($Data)) {
                IPS_SetVariableProfileValues($Name, (count($Data) == 0 ? 0 : 1), count($Data), 1);
            }
        }
        $CurrentIndex = $this->GetValue('position');
        if (count($Data) == 0) {
            $this->SetValue('CurrentPlaylist', '');
        } else {
            $playlistEntries = [];
            if ($this->PlaylistId == self::Audio) {
                foreach ($Data as $Line) {
                    if (array_key_exists('displayartist', $Line)) {
                        $artist = $Line['displayartist'];
                    } else {
                        if (array_key_exists('artist', $Line)) {
                            if (is_array($Line['artist'])) {
                                $artist = implode(', ', $Line['artist']);
                            } else {
                                $artist = $Line['artist'];
                            }
                        } else {
                            if (array_key_exists('albumartist', $Line)) {
                                if (is_array($Line['albumartist'])) {
                                    $artist = implode(', ', $Line['albumartist']);
                                } else {
                                    $artist = $Line['albumartist'];
                                }
                            } else {
                                $artist = '';
                            }
                        }
                    }
                    $playlistEntries[] = [
                        'artist'        => $artist,
                        'song'          => $Line['title'],
                        'duration'      => $Line['duration']
                    ];
                }
            } elseif ($this->PlaylistId == self::Video) {
                foreach ($Data as ['title' => $Title, 'showtitle'=>$Showtitle, 'runtime'=>$Runtime]) {
                    $playlistEntries[] = [
                        'artist'        => $Showtitle,
                        'song'          => $Title,
                        'duration'      => $Runtime
                    ];
                }
            }
            $this->SetValue('CurrentPlaylist', json_encode([
                'current' => $CurrentIndex - 1,
                'entries' => $playlistEntries
            ]));
        }

        if (!$this->ReadPropertyBoolean(self::PropertyShowPlaylist)) {
            return;
        }
        $Config = $this->GetTableConfig();

        $NewSecret = base64_encode(openssl_random_pseudo_bytes(12));
        $this->WebHookSecret = $NewSecret;

        $HTMLData = $this->GetTableHeader($Config);
        $pos = 0;
        $CurrentTrack = $this->GetValue('position');
        if (count($Data) > 0) {
            foreach ($Data as $Position => $line) {
                $Line = [];
                foreach ($line as $key => $value) {
                    if (is_string($key)) {
                        $Line[ucfirst($key)] = $value;
                    } else {
                        $Line[$key] = $value;
                    } //$key is not a string
                }
                $Line['Position'] = $Position + 1;
                $Line['Type'] = ucfirst($Line['Type']);

                if (array_key_exists('Runtime', $Line)) {
                    if ($Line['Runtime'] > 3600) {
                        $Line['Runtime'] = @date('H:i:s', $Line['Runtime'] - 3600);
                    } elseif ($Line['Runtime'] > 0) {
                        $Line['Runtime'] = @date('i:s', $Line['Runtime']);
                    } else {
                        $Line['Runtime'] = '---';
                    }
                } else {
                    $Line['Runtime'] = '---';
                }

                $Line['Play'] = ($Line['Position'] == $CurrentTrack ? '<div class="iconMediumSpinner ipsIconArrowRight" style="width: 100%; background-position: center center;"></div>' : '');
                $LineSecret = rawurlencode(base64_encode(sha1($NewSecret . '0' . $Line['Position'], true)));
                $Row = $Line['Position'] == $CurrentTrack ? \KodiBase\TableRow::Active : ($pos % 2 ? \KodiBase\TableRow::Odd : \KodiBase\TableRow::Even);
                $OnClick = 'onclick="xhrGet' . $this->InstanceID . '({ url: \'hook/KodiPlaylist' . $this->InstanceID . '?Index=' . $Line['Position'] . '&Secret=' . $LineSecret . '\' })"';
                $HTMLData .= $this->GetTableRow($Config, $Row, $Line, $OnClick, true);
                $pos++;
            }
        }
        $HTMLData .= $this->GetTableFooter();
        $this->SetValueString('Playlist', $HTMLData);
    }

    /**
     * Fügt der Playlist ein Item hinzu.
     *
     * @access private
     * @param string $ItemTyp Der Typ des Item.
     * @param mixed $ItemValue Der Wert des Item.
     * @param array $Ext Zusätzliche Felder des Item, z.B. ['recursive' => true] (optional).
     * @return bool True bei Erfolg. Sonst false.
     */
    private function Add(string $ItemTyp, $ItemValue, $Ext = []): bool
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace[0]);
        $KodiData->Add(['playlistid' => $this->PlaylistId, 'item' => array_merge([$ItemTyp => $ItemValue], $Ext)]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret === 'OK') {
            return true;
        }
        trigger_error($this->Translate('Error on add ') . $ItemTyp . '.', E_USER_NOTICE);
        return false;
    }

    /**
     * Fügt der Playlist ein Item, an einer bestimmten Position, hinzu.
     *
     * @access private
     * @param int $Position Position des Item in der Playlist.
     * @param string $ItemTyp Der Typ des Item.
     * @param mixed $ItemValue Der Wert des Item.
     * @param array $Ext Zusätzliche Felder des Item, z.B. ['recursive' => true] (optional).
     * @return bool True bei Erfolg. Sonst false.
     */
    private function Insert(int $Position, string $ItemTyp, mixed $ItemValue, $Ext = []): bool
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace[0], 'Insert', ['playlistid' => $this->PlaylistId, 'position' => $Position, 'item' => array_merge([$ItemTyp => $ItemValue], $Ext)]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret === 'OK') {
            return true;
        }
        trigger_error($this->Translate('Error on insert ') . $ItemTyp . '.', E_USER_NOTICE);
        return false;
    }
}
