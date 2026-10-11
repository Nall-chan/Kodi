<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/KodiClass.php';  // diverse Klassen

/**
 * KodiDeviceVideoLibrary Klasse für den Namespace VideoLibrary der KODI-API.
 * Erweitert KodiBase.
 *
 * @project       KodiDeviceVideoLibrary
 * @file          module.php
 * @package       Kodi
 * @author        Michael Tröger <nall4chan@gmail.com>
 * @copyright     2026 Michael Tröger
 * @license       Custom Non-Commercial Share-Alike License (see LICENSE)
 * @version       3.10
 * @example <b>Ohne</b>
 *
 * @todo Suche über WF einbauen. String und Int-Var für Text suche in Titel/Genre etc... Ergebnis als HTML-Tabelle.
 * @todo VideoLibrary.GetAvailableArt ab v10
 * @todo VideoLibrary.GetAvailableArtTypes ab v10
 *
 * @property string $Namespace RPC-Namespace
 * @property array $Properties Alle Properties des RPC-Namespace
 * @property array $EpisodeItemList Alle Properties von Episoden
 * @property array $EpisodeItemListSmall Ein Teil der Properties von Episoden
 * @property array $MovieItemList Alle Properties von Filmen
 * @property array $SetItemList Alle Properties von Filmsets
 * @property array $SeasonItemList Alle Properties von Seasons
 * @property array $MusicVideoItemList Alle Properties von Musikvideos
 * @property array $TvShowItemList Alle Properties von TV-Serien
 * @property array $GenreItemList Alle Properties von Genres
 *
 */
class KodiDeviceVideoLibrary extends KodiBase
{
    public const PropertyShowDoScan = 'showDoScan';
    public const PropertyShowDoClean = 'showDoClean';
    public const PropertyShowScan = 'showScan';
    public const PropertyShowClean = 'showClean';

    /** Felder, die Listen-Abfragen nicht anfordern (zu groß, z.B. alle Darsteller). Die Get*Details-Funktionen liefern sie weiterhin. */
    public const ListExcludedFields = ['cast', 'streamdetails', 'art'];

    protected static $Namespace = 'VideoLibrary';
    protected static $Properties = [];
    protected static $EpisodeItemList = [
        'title',
        'plot',
        'votes',
        'rating',
        'writer',
        'firstaired',
        'playcount',
        'runtime',
        'director',
        'productioncode',
        'season',
        'episode',
        'originaltitle',
        'showtitle',
        'cast',
        'streamdetails',
        'lastplayed',
        'fanart',
        'thumbnail',
        'file',
        'resume',
        'tvshowid',
        'dateadded',
        'uniqueid',
        'art'
    ];
    protected static $EpisodeItemListSmall = [
        'title',
        'playcount',
        'season',
        'episode',
        'originaltitle',
        'showtitle',
        'fanart',
        'thumbnail',
        'file',
        'tvshowid'
    ];
    protected static $MovieItemList = [
        'title',
        'genre',
        'year',
        'rating',
        'director',
        'trailer',
        'tagline',
        'plot',
        'plotoutline',
        'originaltitle',
        'lastplayed',
        'playcount',
        'writer',
        'studio',
        'mpaa',
        'cast',
        'country',
        'imdbnumber',
        'runtime',
        'set',
        'showlink',
        'streamdetails',
        'top250',
        'votes',
        'fanart',
        'thumbnail',
        'file',
        'sorttitle',
        'resume',
        'setid',
        'dateadded',
        'tag',
        'art'
    ];
    protected static $SetItemList = [
        'title',
        'playcount',
        'fanart',
        'thumbnail',
        'art'
    ];
    protected static $SeasonItemList = [
        'season',
        'showtitle',
        'playcount',
        'episode',
        'fanart',
        'thumbnail',
        'tvshowid',
        'watchedepisodes',
        'art',
        'userrating',
        'title'
    ];
    protected static $MusicVideoItemList = [
        'title',
        'playcount',
        'runtime',
        'director',
        'studio',
        'year',
        'plot',
        'album',
        'artist',
        'genre',
        'track',
        'streamdetails',
        'lastplayed',
        'fanart',
        'thumbnail',
        'file',
        'resume',
        'dateadded',
        'tag',
        'art'
    ];
    protected static $TvShowItemList = [
        'title',
        'genre',
        'year',
        'rating',
        'plot',
        'studio',
        'mpaa',
        'cast',
        'playcount',
        'episode',
        'imdbnumber',
        'premiered',
        'votes',
        'lastplayed',
        'fanart',
        'thumbnail',
        'file',
        'originaltitle',
        'sorttitle',
        'episodeguide',
        'season',
        'watchedepisodes',
        'dateadded',
        'tag',
        'art'
    ];
    protected static $GenreItemList = [
        'thumbnail',
        'title'
    ];

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Create(): void
    {
        parent::Create();
        $this->RegisterPropertyBoolean(self::PropertyShowDoScan, true);
        $this->RegisterPropertyBoolean(self::PropertyShowDoClean, true);
        $this->RegisterPropertyBoolean(self::PropertyShowScan, true);
        $this->RegisterPropertyBoolean(self::PropertyShowClean, true);
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

        if ($this->ReadPropertyBoolean(self::PropertyShowDoScan)) {
            $this->RegisterVariableInteger('doscan', $this->Translate('Search for new / changed content'), 'Action.Kodi', 1);
            $this->EnableAction('doscan');
        } else {
            $this->UnregisterVariable('doscan');
        }

        if ($this->ReadPropertyBoolean(self::PropertyShowScan)) {
            $this->RegisterVariableBoolean('scan', $this->Translate('Database search in progress'), '~Switch', 2);
        } else {
            $this->UnregisterVariable('scan');
        }

        if ($this->ReadPropertyBoolean(self::PropertyShowDoClean)) {
            $this->RegisterVariableInteger('doclean', $this->Translate('Clean up the database'), 'Action.Kodi', 3);
            $this->EnableAction('doclean');
        } else {
            $this->UnregisterVariable('doclean');
        }

        if ($this->ReadPropertyBoolean(self::PropertyShowClean)) {
            $this->RegisterVariableBoolean('clean', $this->Translate('Database cleanup in progress'), '~Switch', 4);
        } else {
            $this->UnregisterVariable('clean');
        }

        parent::ApplyChanges();
    }

    ################## PUBLIC
    /**
     * IPS-Instanz-Funktion 'KODIVIDEOLIB_Clean'. Startet das bereinigen der Datenbank
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function Clean(): bool
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Clean();
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret === 'OK') {
            return true;
        }
        trigger_error($this->Translate('Error start cleaning'), E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIVIDEOLIB_Export'. Exportiert die Video Datenbank.
     *
     * @access public
     * @param  string $Path Ziel-Verzeichnis für den Export in eine Datei. Leer = Export in einzelne Dateien bei den Medien.
     * @param bool $Overwrite Vorhandene Dateien überschreiben (nur bei leerem $Path).
     * @param bool $includeImages Bilder mit exportieren (nur bei leerem $Path).
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function Export(string $Path, bool $Overwrite, bool $includeImages): bool
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        // Kodi kennt nur zwei Varianten: Export in eine Datei unter $Path oder in einzelne Dateien bei den Medien (overwrite/images).
        if ($Path != '') {
            $KodiData->Export(['options' => ['path' => $Path]]);
        } else {
            $KodiData->Export(['options' => ['overwrite' => $Overwrite, 'images' => $includeImages]]);
        }
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODIVIDEOLIB_GetEpisodeDetails'. Liest die Eigenschaften eines Künstlers aus.
     *
     * @access public
     * @param  int $EpisodeId EpisodenID der zu lesenden Episode.
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetEpisodeDetails(int $EpisodeId): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetEpisodeDetails(['episodeid' => $EpisodeId, 'properties' => static::$EpisodeItemList]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $KodiData->ToArray($ret->episodedetails);
    }

    /**
     * IPS-Instanz-Funktion 'KODIVIDEOLIB_GetEpisodes'. Liest die Eigenschaften aller Songs aus.
     *
     * @access public
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetEpisodes(): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetEpisodes(['properties' => static::$EpisodeItemListSmall]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->episodes);
        }
        return [];
    }

    /**
     * IPS-Instanz-Funktion 'KODIVIDEOLIB_GetEpisodesEx'. Liest eine Seite der Episoden (für große Bibliotheken).
     *
     * @access public
     * @param int $Start Erster Eintrag (ab 0).
     * @param int $Count Anzahl der Einträge.
     * @return array|bool Array mit 'total' (Gesamtzahl) und 'items' (Einträge) oder false bei Fehler.
     */
    public function GetEpisodesEx(int $Start, int $Count): false|array
    {
        return $this->GetListPage('GetEpisodes', static::$EpisodeItemListSmall, 'episodes', $Start, $Count);
    }

    /**
     * IPS-Instanz-Funktion 'KODIVIDEOLIB_GetGenres'. Liest die Eigenschaften aller Genres aus.
     *
     * @access public
     * @param string $Type Der Typ der zu suchenden Genres.
     *   enum["movie"=Filme, "tvshow"=Serien, "musicvideo"=Musikvideos]
     *    * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetGenres(string $Type): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetGenres(['properties' => static::$GenreItemList, 'type' => $Type]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->genres);
        }
        return [];
    }

    /**
     * IPS-Instanz-Funktion 'KODIVIDEOLIB_GetInProgressTVShows'. Liest die Eigenschaften aller angefangenen TV-Serien aus.
     *
     * @access public
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetInProgressTVShows(): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetInProgressTVShows(['properties' => $this->ListFields(static::$TvShowItemList)]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->tvshows);
        }
        return [];
    }

    /**
     * IPS-Instanz-Funktion 'KODIVIDEOLIB_GetMovieDetails'. Liest die Eigenschaften eines Films aus.
     *
     * @access public
     * @param  int $MovieId MovieID des zu lesenden Films.
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetMovieDetails(int $MovieId): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetMovieDetails(['movieid' => $MovieId, 'properties' => static::$MovieItemList]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $KodiData->ToArray($ret->moviedetails);
    }

    /**
     * IPS-Instanz-Funktion 'KODIVIDEOLIB_GetMovies'. Liest die Eigenschaften aller Filme aus.
     *
     * @access public
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetMovies(): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetMovies(['properties' => $this->ListFields(static::$MovieItemList)]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->movies);
        }
        return [];
    }

    /**
     * IPS-Instanz-Funktion 'KODIVIDEOLIB_GetMoviesEx'. Liest eine Seite der Filme (für große Bibliotheken).
     *
     * @access public
     * @param int $Start Erster Eintrag (ab 0).
     * @param int $Count Anzahl der Einträge.
     * @return array|bool Array mit 'total' (Gesamtzahl) und 'items' (Einträge) oder false bei Fehler.
     */
    public function GetMoviesEx(int $Start, int $Count): false|array
    {
        return $this->GetListPage('GetMovies', $this->ListFields(static::$MovieItemList), 'movies', $Start, $Count);
    }

    /**
     * IPS-Instanz-Funktion 'KODIVIDEOLIB_GetMovieSetDetails'. Liest die Eigenschaften eines Film-Sets aus.
     *
     * @access public
     * @param  int $SetId SetId des zu lesenden Film-Sets.
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetMovieSetDetails(int $SetId): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetMovieSetDetails(['setid' => $SetId, 'properties' => static::$SetItemList]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $KodiData->ToArray($ret->setdetails);
    }

    /**
     * IPS-Instanz-Funktion 'KODIVIDEOLIB_GetMovieSets'. Liest die Eigenschaften aller Film-Sets.
     *
     * @access public
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetMovieSets(): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetMovieSets(['properties' => static::$SetItemList]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->sets);
        }
        return [];
    }

    /**
     * IPS-Instanz-Funktion 'KODIVIDEOLIB_GetMovieSetsEx'. Liest eine Seite der Film-Sets (für große Bibliotheken).
     *
     * @access public
     * @param int $Start Erster Eintrag (ab 0).
     * @param int $Count Anzahl der Einträge.
     * @return array|bool Array mit 'total' (Gesamtzahl) und 'items' (Einträge) oder false bei Fehler.
     */
    public function GetMovieSetsEx(int $Start, int $Count): false|array
    {
        return $this->GetListPage('GetMovieSets', static::$SetItemList, 'sets', $Start, $Count);
    }

    /**
     * IPS-Instanz-Funktion 'KODIVIDEOLIB_GetMusicVideoDetails'. Liest die Eigenschaften eines Musikvideos aus.
     *
     * @access public
     * @param  int $MusicVideoId MusicVideoId des zu lesenden Musikvideos.
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetMusicVideoDetails(int $MusicVideoId): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetMusicVideoDetails(['musicvideoid' => $MusicVideoId, 'properties' => static::$MusicVideoItemList]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $KodiData->ToArray($ret->musicvideodetails);
    }

    /**
     * IPS-Instanz-Funktion 'KODIVIDEOLIB_GetMusicVideos'. Liest die Eigenschaften aller Musikvideos.
     *
     * @access public
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetMusicVideos(): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetMusicVideos(['properties' => $this->ListFields(static::$MusicVideoItemList)]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->musicvideos);
        }
        return [];
    }

    /**
     * IPS-Instanz-Funktion 'KODIVIDEOLIB_GetMusicVideosEx'. Liest eine Seite der Musikvideos (für große Bibliotheken).
     *
     * @access public
     * @param int $Start Erster Eintrag (ab 0).
     * @param int $Count Anzahl der Einträge.
     * @return array|bool Array mit 'total' (Gesamtzahl) und 'items' (Einträge) oder false bei Fehler.
     */
    public function GetMusicVideosEx(int $Start, int $Count): false|array
    {
        return $this->GetListPage('GetMusicVideos', $this->ListFields(static::$MusicVideoItemList), 'musicvideos', $Start, $Count);
    }

    /**
     * IPS-Instanz-Funktion 'KODIVIDEOLIB_GetRecentlyAddedEpisodes'. Liest die Eigenschaften der zuletzt hinzugefügten Episoden aus.
     *
     * @access public
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetRecentlyAddedEpisodes(): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetRecentlyAddedEpisodes(['properties' => $this->ListFields(static::$EpisodeItemList)]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->episodes);
        }
        return [];
    }

    /**
     * IPS-Instanz-Funktion 'KODIVIDEOLIB_GetRecentlyAddedMovies'. Liest die Eigenschaften der zuletzt hinzugefügten Filme aus.
     *
     * @access public
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetRecentlyAddedMovies(): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetRecentlyAddedMovies(['properties' => $this->ListFields(static::$MovieItemList)]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->movies);
        }
        return [];
    }

    /**
     * IPS-Instanz-Funktion 'KODIVIDEOLIB_GetRecentlyAddedMusicVideos'. Liest die Eigenschaften der zuletzt hinzugefügten Filme aus.
     *
     * @access public
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetRecentlyAddedMusicVideos(): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetRecentlyAddedMusicVideos(['properties' => $this->ListFields(static::$MusicVideoItemList)]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->musicvideos);
        }
        return [];
    }

    /**
     * IPS-Instanz-Funktion 'KODIVIDEOLIB_GetSeasonDetails'. Liest die Eigenschaften einer Staffel aus.
     *
     * @access public
     * @param  int $SeasonId SeasonId der zu lesenden Staffel (aus GetSeasons, nicht die Staffelnummer).
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetSeasonDetails(int $SeasonId): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetSeasonDetails(['seasonid' => $SeasonId, 'properties' => static::$SeasonItemList]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $KodiData->ToArray($ret->seasondetails);
    }

    /**
     * IPS-Instanz-Funktion 'KODIVIDEOLIB_GetSeasons'. Liest die Eigenschaften aller Staffeln einer TV-Serie aus.
     *
     * @access public
     * @param  int $TvShowId TvShowId der zu lesenden Serie.
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetSeasons(int $TvShowId): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetSeasons(['tvshowid' => $TvShowId, 'properties' => static::$SeasonItemList]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $KodiData->ToArray($ret->seasons);
    }

    /**
     * IPS-Instanz-Funktion 'KODIVIDEOLIB_GetTags'. Liest alle Tags eines Medientyps aus.
     *
     * @access public
     * @param string $Type Der Typ der Medien.
     *   enum["movie"=Filme, "tvshow"=Serien, "musicvideo"=Musikvideos]
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetTags(string $Type): false|array
    {
        if (!in_array($Type, ['movie', 'tvshow', 'musicvideo'], true)) {
            trigger_error(sprintf($this->Translate('Invalid type: %s'), $Type), E_USER_NOTICE);
            return false;
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetTags(['type' => $Type, 'properties' => ['title']]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->tags);
        }
        return [];
    }

    /**
     * IPS-Instanz-Funktion 'KODIVIDEOLIB_GetTVShowDetails'. Liest die Eigenschaften eines TV-Serie aus.
     *
     * @access public
     * @param  int $TvShowId TvShowId der zu lesenden TV-Serie.
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetTVShowDetails(int $TvShowId): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetTVShowDetails(['tvshowid' => $TvShowId, 'properties' => static::$TvShowItemList]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $KodiData->ToArray($ret->tvshowdetails);
    }

    /**
     * IPS-Instanz-Funktion 'KODIVIDEOLIB_GetTVShows'. Liest die Eigenschaften aller TV-Serien.
     *
     * @access public
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetTVShows(): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetTVShows(['properties' => $this->ListFields(static::$TvShowItemList)]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->tvshows);
        }
        return [];
    }

    /**
     * IPS-Instanz-Funktion 'KODIVIDEOLIB_GetTVShowsEx'. Liest eine Seite der TV-Serien (für große Bibliotheken).
     *
     * @access public
     * @param int $Start Erster Eintrag (ab 0).
     * @param int $Count Anzahl der Einträge.
     * @return array|bool Array mit 'total' (Gesamtzahl) und 'items' (Einträge) oder false bei Fehler.
     */
    public function GetTVShowsEx(int $Start, int $Count): false|array
    {
        return $this->GetListPage('GetTVShows', $this->ListFields(static::$TvShowItemList), 'tvshows', $Start, $Count);
    }

    /**
     * IPS-Instanz-Funktion 'KODIVIDEOLIB_Scan'. Startet das Scannen der Quellen für neue Einträge in der Datenbank.
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function Scan(): bool
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Scan();
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret === 'OK') {
            return true;
        }
        trigger_error($this->Translate('Error start scanning'), E_USER_NOTICE);
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
            case 'doclean':
                $this->Clean();
                return;
            case 'doscan':
                $this->Scan();
                return;
            default:
                trigger_error($this->Translate('Invalid Ident.'), E_USER_NOTICE);
        }
    }
    protected function Decode(string $Method, mixed $KodiPayload): void
    {
        switch ($Method) {
            case 'OnScanStarted':
                $this->SetValueBoolean('scan', true);
                break;
            case 'OnScanFinished':
                $this->SetValueBoolean('scan', false);
                break;
            case 'OnCleanStarted':
                $this->SetValueBoolean('clean', true);
                break;
            case 'OnCleanFinished':
                $this->SetValueBoolean('clean', false);
                break;
        }
    }

    ################## PRIVATE
    /**
     * Liefert die Felder für Listen-Abfragen ohne die sehr großen Felder.
     * Hintergrund: Instanz-Funktionen dürfen höchstens 1 MB zurückgeben, cast allein macht über die Hälfte aus.
     *
     * @param array $Fields Alle Felder.
     * @return array Felder ohne ListExcludedFields.
     */
    private function ListFields(array $Fields): array
    {
        return array_values(array_diff($Fields, self::ListExcludedFields));
    }
}
