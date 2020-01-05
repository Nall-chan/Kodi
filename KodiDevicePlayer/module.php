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
require_once __DIR__ . '/../libs/KodiClass.php';  // diverse Klassen

/**
 * KodiDevicePlayer Klasse für den Namespace Player der KODI-API.
 * Erweitert KodiBase.
 *
 * @package       Kodi
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2016 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       1.0
 * @example <b>Ohne</b>
 * @property bool $isActive
 * @property int $PlayerId
 * @todo Player.SetVideoStream ab v8
 */
class KodiDevicePlayer extends KodiBase
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
     * RPC-Namespace
     *
     * @access private
     *  @var string
     * @value 'Application'
     */
    protected static $Namespace = 'Player';

    /**
     * Alle Properties des RPC-Namespace
     *
     * @access private
     *  @var array
     */
    protected static $Properties = [
        'type',
        'partymode',
        'speed',
        'time',
        'percentage',
        'totaltime',
        'playlistid',
        'position',
        'repeat',
        'shuffled',
        'canseek',
        'canchangespeed',
        'canmove',
        'canzoom',
        'canrotate',
        'canshuffle',
        'canrepeat',
        'currentaudiostream',
        'audiostreams',
        'subtitleenabled',
        'currentsubtitle',
        'subtitles',
        'live'
    ];

    /**
     * Ein Teil der Properties des RPC-Namespace für Statusmeldungen
     *
     * @access private
     *  @var array
     */
    protected static $PartialProperties = [
        'type',
        'partymode',
        'speed',
        'time',
        'percentage',
        'repeat',
        'shuffled',
        'currentaudiostream',
        'subtitleenabled',
        'currentsubtitle'
    ];

    /**
     * Alle Properties eines Item
     *
     * @access private
     *  @var array
     */
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
        'endtime'];

    /**
     * Kleiner Teil der Properties eines Item
     *
     * @access private
     *  @var array
     */
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
     * Zuordnung der von Kodi gemeldeten Medientypen zu den PlayerIDs
     *
     * @access private
     *  @var array Key ist der Medientyp, Value die PlayerID
     */
    protected static $Playertype = [
        'song'     => 0,
        'audio'    => 0,
        'radio'    => 0,
        'video'    => 1,
        'episode'  => 1,
        'movie'    => 1,
        'tv'       => 1,
        'picture'  => 2,
        'pictures' => 2
    ];

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Create()
    {
        parent::Create();
        $this->RegisterPropertyInteger('PlayerID', static::Audio);
        $this->PlayerId = static::Audio;
        $this->RegisterPropertyInteger('CoverSize', 300);
        $this->RegisterPropertyString('CoverTyp', 'thumb');
        $this->RegisterTimer('PlayerStatus', 0, 'KODIPLAYER_RequestState($_IPS[\'TARGET\'],"PARTIAL");');
        $this->isActive = false;
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Destroy()
    {
        if (IPS_GetKernelRunlevel() != KR_READY) {
            return parent::Destroy();
        }
        if (!IPS_InstanceExists($this->InstanceID)) {
            $CoverID = @IPS_GetObjectIDByIdent('CoverIMG', $this->InstanceID);
            if ($CoverID > 0) {
                @IPS_DeleteMedia($CoverID, true);
            }
            //Profile löschen
            $this->UnregisterProfile('AudioStream.' . $this->InstanceID . '.Kodi');
            $this->UnregisterProfile('Subtitels.' . $this->InstanceID . '.Kodi');
            $this->UnregisterProfile('Speed.' . $this->InstanceID . '.Kodi');
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
        $this->PlayerId = $this->ReadPropertyInteger('PlayerID');
        $this->RegisterProfileIntegerEx('Repeat.Kodi', '', '', '', [
            [0, 'Aus', '', -1],
            [1, 'Titel', '', -1],
            [2, 'Playlist', '', -1]
        ]);

        $this->RegisterProfileInteger('Intensity.Kodi', 'Intensity', '', ' %', 0, 100, 1);
        $this->RegisterProfileIntegerEx('Status.Kodi', 'Information', '', '', [
            [0, 'Zurück', '', -1],
            [1, 'Stop', '', -1],
            [2, 'Play', '', -1],
            [3, 'Pause', '', -1],
            [4, 'Weiter', '', -1]
        ]);

        switch ($this->PlayerId) {
            case self::Audio:
                $this->UnregisterVariable('showtitle');
                $this->UnregisterVariable('season');
                $this->UnregisterVariable('episode');
                $this->UnregisterVariable('plot');
                $this->UnregisterVariable('audiostream');
                $this->UnregisterVariable('subtitle');
                $this->UnregisterProfile('AudioStream.' . $this->InstanceID . '.Kodi');
                $this->UnregisterProfile('Subtitels.' . $this->InstanceID . '.Kodi');

                $this->RegisterProfileIntegerEx('Speed.' . $this->InstanceID . '.Kodi', 'Intensity', '', '', [
                    [-32, '32 <<', '', -1],
                    [-16, '16 <<', '', -1],
                    [-8, '8 <<', '', -1],
                    [-4, '4 <<', '', -1],
                    [-2, '2 <<', '', -1],
                    [-1, '1 <<', '', -1],
                    [0, 'Pause', '', 0x0000FF],
                    [1, 'Play', '', 0x00FF00],
                    [2, '2 >>', '', -1],
                    [4, '4 >>', '', -1],
                    [8, '8 >>', '', -1],
                    [16, '16 >>', '', -1],
                    [32, '32 >>', '', -1]
                ]);

                $this->RegisterVariableString('album', 'Album', '', 15);
                $this->RegisterVariableInteger('track', 'Track', '', 16);
                $this->RegisterVariableInteger('disc', 'Disc', '', 17);
                $this->RegisterVariableString('artist', 'Artist', '', 20);
                $this->RegisterVariableString('lyrics', 'Lyrics', '', 30);
                $this->RegisterVariableBoolean('partymode', 'Partymodus', '~Switch', 13);
                $this->EnableAction('partymode');
                $this->RegisterVariableInteger('repeat', 'Wiederholen', 'Repeat.Kodi', 11);
                $this->EnableAction('repeat');
                $this->RegisterVariableInteger('year', 'Jahr', '', 19);
                $this->RegisterVariableString('genre', 'Genre', '', 21);
                $this->RegisterVariableString('duration', 'Dauer', '', 24);
                $this->RegisterVariableString('time', 'Spielzeit', '', 25);
                break;
            case self::Video:
                $this->UnregisterVariable('album');
                $this->UnregisterVariable('track');
                $this->UnregisterVariable('disc');
                $this->UnregisterVariable('artist');
                $this->UnregisterVariable('lyrics');

                $this->RegisterProfileIntegerEx('AudioStream.' . $this->InstanceID . '.Kodi', '', '', '', [
                    [0, '1', '', -1]
                ]);
                $this->RegisterProfileIntegerEx('Speed.' . $this->InstanceID . '.Kodi', 'Intensity', '', '', [
                    [-32, '32 <<', '', -1],
                    [-16, '16 <<', '', -1],
                    [-8, '8 <<', '', -1],
                    [-4, '4 <<', '', -1],
                    [-2, '2 <<', '', -1],
                    [-1, '1 <<', '', -1],
                    [0, 'Pause', '', 0x0000FF],
                    [1, 'Play', '', 0x00FF00],
                    [2, '2 >>', '', -1],
                    [4, '4 >>', '', -1],
                    [8, '8 >>', '', -1],
                    [16, '16 >>', '', -1],
                    [32, '32 >>', '', -1]
                ]);

                $this->RegisterVariableString('showtitle', 'Serie', '', 13);
                $this->RegisterVariableString('channel', 'Kanal', '', 13);
                $this->RegisterVariableInteger('season', 'Staffel', '', 15);
                $this->RegisterVariableInteger('episode', 'Episode', '', 16);

                $this->RegisterVariableString('plot', 'Handlung', '~TextBox', 19);
                $this->RegisterVariableInteger('audiostream', 'Audiospur', 'AudioStream.' . $this->InstanceID . '.Kodi', 30);

                $this->RegisterProfileIntegerEx('Subtitels.' . $this->InstanceID . '.Kodi', '', '', '', [
                    [-1, 'Aus', '', -1],
                    [0, 'Extern', '', -1]
                ]);
                $this->RegisterVariableInteger('subtitle', 'Aktiver Untertitel', 'Subtitels.' . $this->InstanceID . '.Kodi', 41);
                $this->RegisterVariableBoolean('partymode', 'Partymodus', '~Switch', 13);
                $this->EnableAction('partymode');
                $this->RegisterVariableInteger('repeat', 'Wiederholen', 'Repeat.Kodi', 11);
                $this->EnableAction('repeat');
                $this->RegisterVariableInteger('year', 'Jahr', '', 19);
                $this->RegisterVariableString('genre', 'Genre', '', 21);
                $this->RegisterVariableString('duration', 'Dauer', '', 24);
                $this->RegisterVariableString('time', 'Spielzeit', '', 25);
                break;

            case self::Picture:

                $this->UnregisterVariable('showtitle');
                $this->UnregisterVariable('season');
                $this->UnregisterVariable('episode');
                $this->UnregisterVariable('plot');
                $this->UnregisterVariable('audiostream');
                $this->UnregisterVariable('subtitle');
                $this->UnregisterProfile('AudioStream.' . $this->InstanceID . '.Kodi');
                $this->UnregisterProfile('Subtitels.' . $this->InstanceID . '.Kodi');

                $this->UnregisterVariable('album');
                $this->UnregisterVariable('track');
                $this->UnregisterVariable('disc');
                $this->UnregisterVariable('artist');
                $this->UnregisterVariable('lyrics');

                $this->UnregisterVariable('partymode');
                $this->UnregisterVariable('repeat');
                $this->UnregisterVariable('year');
                $this->UnregisterVariable('genre');
                $this->UnregisterVariable('duration');
                $this->UnregisterVariable('time');

                $this->RegisterProfileIntegerEx('Speed.' . $this->InstanceID . '.Kodi', 'Intensity', '', '', [
                    [-1, 'Rückwärts', '', -1],
                    [0, 'Standbild', '', 0x0000FF],
                    [1, 'Vorwärts', '', 0x00FF00],
                ]);

                break;
        }
        $this->RegisterVariableBoolean('shuffled', 'Zufall', '~Switch', 12);
        $this->EnableAction('shuffled');

        $this->RegisterVariableString('label', 'Titel', '', 14);
        $this->RegisterVariableInteger('Status', 'Status', 'Status.Kodi', 3);
        $this->EnableAction('Status');
        $this->RegisterVariableInteger('speed', 'Geschwindigkeit', 'Speed.' . $this->InstanceID . '.Kodi', 10);
        $this->RegisterVariableInteger('percentage', 'Position', 'Intensity.Kodi', 26);
        $this->EnableAction('percentage');

        $this->SetCover('');

        parent::ApplyChanges();

        $this->getActivePlayer();

        if ($this->isActive) {
            $this->GetItemInternal();
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
            case 'Status':
                switch ($Value) {
                    case 0: //Prev
                        return $this->GoToPrevious();
                    case 1: //Stop
                        return $this->Stop();
                    case 2: //Play
                        return $this->Play();
                    case 3: //Pause
                        return $this->Pause();
                    case 4: //Next
                        return $this->GoToNext();
                    default:
                        return trigger_error('Invalid Ident.', E_USER_NOTICE);
                }
                // FIXME: No break. Please add proper comment if intentional
            case 'shuffled':
                return $this->SetShuffle($Value);
            case 'repeat':
                return $this->SetRepeat($Value);
            case 'speed':
                return $this->SetSpeed($Value);
            case 'partymode':
                return $this->SetPartymode($Value);
            case 'percentage':
                return $this->SetPosition($Value);
            case 'subtitle':
                return $this->SetSubtitle($Value);
            case 'audiostream':
                return $this->SetAudioStream($Value);
            default:
                return trigger_error('Invalid Ident.', E_USER_NOTICE);
        }
    }

    ################## PUBLIC
    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_GetItemInternal'.
     * Holt sich die Daten des aktuellen wiedergegebenen Items, und bildet die Eigenschaften in IPS-Variablen ab.
     *
     * @access public
     */
    public function GetItemInternal()
    {
        if (!$this->isActive) {
            return false;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetItem(['playerid' => $this->PlayerId, 'properties' => self::$ItemList]);
        $raw = $this->SendDirect($KodiData);

        if (is_null($raw)) {
            return;
        }
        $ret = $raw->item;
        if (is_null($ret)) {
            return;
        }
        switch ($this->PlayerId) {
            case self::Audio:
                $this->SetValueString('label', $ret->label);
                if (property_exists($ret, 'displayartist')) {
                    $this->SetValueString('artist', $ret->displayartist);
                } else {
                    if (property_exists($ret, 'albumartist')) {
                        if (is_array($ret->artist)) {
                            $this->SetValueString('artist', implode(', ', $ret->albumartist));
                        } else {
                            $this->SetValueString('artist', $ret->albumartist);
                        }
                    } else {
                        if (property_exists($ret, 'artist')) {
                            if (is_array($ret->artist)) {
                                $this->SetValueString('artist', implode(', ', $ret->artist));
                            } else {
                                $this->SetValueString('artist', $ret->artist);
                            }
                        } else {
                            $this->SetValueString('artist', '');
                        }
                    }
                }

                if (property_exists($ret, 'genre')) {
                    if (is_array($ret->genre)) {
                        $this->SetValueString('genre', implode(', ', $ret->genre));
                    } else {
                        $this->SetValueString('genre', $ret->genre);
                    }
                } else {
                    $this->SetValueString('genre', '');
                }

                if (property_exists($ret, 'album')) {
                    $this->SetValueString('album', $ret->album);
                } else {
                    $this->SetValueString('album', '');
                }

                if (property_exists($ret, 'year')) {
                    $this->SetValueInteger('year', $ret->year);
                } else {
                    $this->SetValueInteger('year', 0);
                }

                if (property_exists($ret, 'track')) {
                    $this->SetValueInteger('track', $ret->track);
                } else {
                    $this->SetValueInteger('track', 0);
                }

                if (property_exists($ret, 'disc')) {
                    $this->SetValueInteger('disc', $ret->disc);
                } else {
                    $this->SetValueInteger('disc', 0);
                }

                if (property_exists($ret, 'duration')) {
                    $this->SetValueString('duration', $this->ConvertTime($ret->duration));
                } else {
                    $this->SetValueString('duration', '');
                }

                if (property_exists($ret, 'lyrics')) {
                    $this->SetValueString('lyrics', $ret->lyrics);
                } else {
                    $this->SetValueString('lyrics', '');
                }

                switch ($this->ReadPropertyString('CoverTyp')) {
                    case'artist':
                        if (property_exists($ret, 'art')) {
                            if (property_exists($ret->art, 'artist.fanart')) {
                                if ($ret->art->{'artist.fanart'} != '') {
                                    $this->SetCover($ret->art->{'artist.fanart'});
                                    break;
                                }
                            }
                        }
                        if (property_exists($ret, 'fanart')) {
                            if ($ret->fanart != '') {
                                $this->SetCover($ret->fanart);
                                break;
                            }
                        }
                        // FIXME: No break. Please add proper comment if intentional
                    default:
                        if (property_exists($ret, 'art')) {
                            if (property_exists($ret->art, 'thumb')) {
                                if ($ret->art->thumb != '') {
                                    $this->SetCover($ret->art->thumb);
                                    break;
                                }
                            }
                        }
                        if (property_exists($ret, 'thumbnail')) {
                            if ($ret->thumbnail != '') {
                                $this->SetCover($ret->thumbnail);
                                break;
                            }
                        }
                        $this->SetCover('');
                        break;
                }

                break;
            case self::Video:

                if (property_exists($ret, 'showtitle')) {
                    $this->SetValueString('showtitle', $ret->showtitle);
                } else {
                    $this->SetValueString('showtitle', '');
                }

                if (property_exists($ret, 'title')) {
                    $this->SetValueString('label', $ret->title);
                } else {
                    $this->SetValueString('label', $ret->label);
                }

                if (property_exists($ret, 'channel')) {
                    $this->SetValueString('channel', $ret->channel);
                } else {
                    $this->SetValueString('channel', '');
                }

                if (property_exists($ret, 'season')) {
                    $this->SetValueInteger('season', $ret->season);
                } else {
                    $this->SetValueInteger('season', -1);
                }

                if (property_exists($ret, 'episode')) {
                    $this->SetValueInteger('episode', $ret->episode);
                } else {
                    $this->SetValueInteger('episode', -1);
                }

                if (property_exists($ret, 'genre')) {
                    if (is_array($ret->genre)) {
                        $this->SetValueString('genre', implode(', ', $ret->genre));
                    } else {
                        $this->SetValueString('genre', $ret->genre);
                    }
                } else {
                    $this->SetValueString('genre', '');
                }

                if (property_exists($ret, 'runtime')) {
                    $this->SetValueString('duration', $this->ConvertTime($ret->runtime));
                } else {
                    $this->SetValueString('duration', '');
                }

                if (property_exists($ret, 'year')) {
                    $this->SetValueInteger('year', $ret->year);
                } else {
                    $this->SetValueInteger('year', 0);
                }

                if (property_exists($ret, 'plot')) {
                    $this->SetValueString('plot', $ret->plot);
                } else {
                    $this->SetValueString('plot', '');
                }

                switch ($this->ReadPropertyString('CoverTyp')) {
                    case'poster':
                        if (property_exists($ret, 'art')) {
                            if (property_exists($ret->art, 'tvshow.poster')) {
                                if ($ret->art->{'tvshow.poster'} != '') {
                                    $this->SetCover($ret->art->{'tvshow.poster'});
                                    break;
                                }
                            }
                            if (property_exists($ret->art, 'poster')) {
                                if ($ret->art->{'poster'} != '') {
                                    $this->SetCover($ret->art->{'poster'});
                                    break;
                                }
                            }
                        }
                        if (property_exists($ret, 'poster')) {
                            if ($ret->poster != '') {
                                $this->SetCover($ret->poster);
                                break;
                            }
                        }
                        if (property_exists($ret, 'art')) {
                            if (property_exists($ret->art, 'tvshow.banner')) {
                                if ($ret->art->{'tvshow.banner'} != '') {
                                    $this->SetCover($ret->art->{'tvshow.banner'});
                                    break;
                                }
                            }
                        }
                        if (property_exists($ret, 'banner')) {
                            if ($ret->banner != '') {
                                $this->SetCover($ret->banner);
                                break;
                            }
                        }
                        if (property_exists($ret, 'art')) {
                            if (property_exists($ret->art, 'thumb')) {
                                if ($ret->art->thumb != '') {
                                    $this->SetCover($ret->art->thumb);
                                    break;
                                }
                            }
                        }
                        if (property_exists($ret, 'thumbnail')) {
                            if ($ret->thumbnail != '') {
                                $this->SetCover($ret->thumbnail);
                                break;
                            }
                        }
                        $this->SetCover('');

                        break;
                    case'banner':
                        if (property_exists($ret, 'art')) {
                            if (property_exists($ret->art, 'tvshow.banner')) {
                                if ($ret->art->{'tvshow.banner'} != '') {
                                    $this->SetCover($ret->art->{'tvshow.banner'});
                                    break;
                                }
                            }
                        }
                        if (property_exists($ret, 'banner')) {
                            if ($ret->banner != '') {
                                $this->SetCover($ret->banner);
                                break;
                            }
                        }
                        if (property_exists($ret, 'art')) {
                            if (property_exists($ret->art, 'tvshow.poster')) {
                                if ($ret->art->{'tvshow.poster'} != '') {
                                    $this->SetCover($ret->art->{'tvshow.poster'});
                                    break;
                                }
                            }
                            if (property_exists($ret->art, 'poster')) {
                                if ($ret->art->{'poster'} != '') {
                                    $this->SetCover($ret->art->{'poster'});
                                    break;
                                }
                            }
                        }
                        if (property_exists($ret, 'poster')) {
                            if ($ret->poster != '') {
                                $this->SetCover($ret->poster);
                                break;
                            }
                        }
                        // FIXME: No break. Please add proper comment if intentional
                    default:
                        if (property_exists($ret, 'art')) {
                            if (property_exists($ret->art, 'thumb')) {
                                if ($ret->art->thumb != '') {
                                    $this->SetCover($ret->art->thumb);
                                    break;
                                }
                            }
                        }
                        if (property_exists($ret, 'thumbnail')) {
                            if ($ret->thumbnail != '') {
                                $this->SetCover($ret->thumbnail);
                                break;
                            }
                        }
                        $this->SetCover('');

                        break;
                }
                break;
            case self::Picture:
                if (property_exists($ret, 'label')) {
                    $label = (string) $ret->label;
                } elseif (property_exists($ret, 'file')) {
                    $parts = explode('/', (string) $ret->file);
                    $label = array_pop($parts);
                } else {
                    $label = '';
                }

                $this->SetValueString('label', $label);
                if (property_exists($ret, 'art')) {
                    if (property_exists($ret->art, 'thumb')) {
                        if ($ret->art->thumb != '') {
                            $this->SetCover($ret->art->thumb);
                        } else {
                            $this->SetCover('');
                        }
                    }
                } else {
                    if (property_exists($ret, 'thumbnail')) {
                        if ($ret->thumbnail != '') {
                            $this->SetCover($ret->thumbnail);
                        }
                    } else {
                        $this->SetCover('');
                    }
                }
                break;
        }
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_GetItem'.
     * Holt die Daten des aktuellen wiedergegebenen Items, und gibt Diese als Array zurück.
     *
     * @access public
     * @return array|bool Das Array mit den Eigenschaften des Item, im Fehlerfall false
     */
    public function GetItem()
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetItem(['playerid' => $this->PlayerId, 'properties' => self::$ItemList]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $KodiData->ToArray($ret->item);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_SetSubtitle'.
     * Deaktiviert oder aktiviert einen Untertitel
     *
     * @access public
     * @param int $Value Index des zu aktivierenden Untertitels, -1 für keinen.
     * @return bool true bei erfolgreicher Ausführung und dekodierung, sonst false.
     */
    public function SetSubtitle(int $Value)
    {
        if (!is_int($Value)) {
            trigger_error('Value must be integer', E_USER_NOTICE);
            return false;
        }

        if (!$this->isActive) {
            trigger_error('Player not active', E_USER_NOTICE);
            return false;
        }
        if ($Value == -1) {
            $Value = 'off';
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->SetSubtitle(['playerid' => $this->PlayerId, 'subtitle' => $Value]);
        $ret = $this->Send($KodiData);
        if ($ret === 'OK') {
            return true;
        }
        trigger_error('Error on set audiostream.', E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_SetAudioStream'.
     * Aktiviert einen bestimmten Audiostream.
     *
     * @access public
     * @param int $Value Index des zu aktivierenden Audiostream.
     * @return bool true bei erfolgreicher Ausführung und dekodierung, sonst false.
     */
    public function SetAudioStream(int $Value)
    {
        if (!is_int($Value)) {
            trigger_error('Value must be integer', E_USER_NOTICE);
            return false;
        }

        if (!$this->isActive) {
            trigger_error('Player not active', E_USER_NOTICE);
            return false;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->SetAudioStream(['playerid' => $this->PlayerId, 'stream' => $Value]);
        $ret = $this->Send($KodiData);
        if ($ret === 'OK') {
            return true;
        }
        trigger_error('Error on set audiostream.', E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_Play'.
     * Starte die Wiedergabe des aktuelle pausierten Items.
     *
     * @access public
     * @return bool True bei Erfolg, sonst false.
     */
    public function Play()
    {
        if (!$this->isActive) {
            if (!$this->LoadPlaylist()) {
                trigger_error('Error on send play.', E_USER_NOTICE);
                return false;
            }
            return;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->PlayPause(['playerid' => $this->PlayerId, 'play' => true]);
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->speed === 1) {
            $this->SetValueInteger('Status', 2);
            return true;
        }
        trigger_error('Error on send play.', E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_Pause'.
     * Pausiert die Wiedergabe des aktuellen Items.
     *
     * @access public
     * @return bool True bei Erfolg, sonst false.
     */
    public function Pause()
    {
        if (!$this->isActive) {
            trigger_error('Player not active', E_USER_NOTICE);
            return false;
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->PlayPause(['playerid' => $this->PlayerId, 'play' => false]);
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->speed === 0) {
            $this->SetValueInteger('Status', 3);
            return true;
        }
        trigger_error('Error on send pause.', E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_Stop'.
     * Stoppt die Wiedergabe des aktuellen Items.
     *
     * @access public
     * @return bool True bei Erfolg, sonst false.
     */
    public function Stop()
    {
        if (!$this->isActive) {
            trigger_error('Player not active', E_USER_NOTICE);
            return false;
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Stop(['playerid' => $this->PlayerId]);
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret === 'OK') {
            $this->SetValueInteger('Status', 1);
            return true;
        }
        trigger_error('Error on send stop.', E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_GoToNext'.
     * Springt zum nächsten Item in der Wiedergabeliste.
     *
     * @access public
     * @return bool True bei Erfolg, sonst false.
     */
    public function GoToNext()
    {
        return $this->GoToValue('next');
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_GoToPrevious'.
     * Springt zum vorherigen Item in der Wiedergabeliste.
     *
     * @access public
     * @return bool True bei Erfolg, sonst false.
     */
    public function GoToPrevious()
    {
        return $this->GoToValue('previous');
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_GoToTrack'.
     * Springt auf ein bestimmtes Item in der Wiedergabeliste.
     *
     * @access public
     * @param int $Value Index in der Wiedergabeliste.
     * @return bool True bei Erfolg, sonst false.
     */ public function GoToTrack(int $Value)
    {
        if (!is_int($Value)) {
            trigger_error('Value must be integer', E_USER_NOTICE);
            return false;
        }
        return $this->GoToValue($Value + 1);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_SetShuffle'.
     * Setzt den Zufallsmodus.
     *
     * @access public
     * @param bool $Value True für Zufallswiedergabe aktiv, false für deaktiv.
     * @return bool True bei Erfolg, sonst false.
     */
    public function SetShuffle(bool $Value)
    {
        if (!is_bool($Value)) {
            trigger_error('Value must be boolean', E_USER_NOTICE);
            return false;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->SetShuffle(['playerid' => $this->PlayerId, 'shuffle' => $Value]);
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret === 'OK') {
            $this->SetValueBoolean('shuffled', $Value);
            return true;
        }
        trigger_error('Error on set shuffle.', E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_SetRepeat'.
     * Setzten den Wiederholungsmodus.
     *
     * @access public
     * @param int $Value Modus der Wiederholung.
     *   enum[0=aus, 1=Titel, 2=Alle]
     * @return bool True bei Erfolg, sonst false.
     */
    public function SetRepeat(int $Value)
    {
        if (!is_int($Value)) {
            trigger_error('Value must be integer', E_USER_NOTICE);
            return false;
        }
        if (($Value < 0) || ($Value > 2)) {
            trigger_error('Value must be between 0 and 2', E_USER_NOTICE);
            return false;
        }

        $repeat = ['off', 'one', 'all'];
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->SetRepeat(['playerid' => $this->PlayerId, 'repeat' => $repeat[$Value]]);
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret === 'OK') {
            $this->SetValueInteger('repeat', $Value);
            return true;
        }
        trigger_error('Error on set repeat.', E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_SetPartymode'.
     * Setzt den Partymodus.
     *
     * @access public
     * @param bool $Value True für Partymodus aktiv, false für deaktiv.
     * @return bool True bei Erfolg, sonst false.
     */
    public function SetPartymode(bool $Value)
    {
        if (!is_bool($Value)) {
            trigger_error('Value must be boolean', E_USER_NOTICE);
            return false;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->SetPartymode(['playerid' => $this->PlayerId, 'partymode' => $Value]);
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret === 'OK') {
            $this->SetValueBoolean('partymode', $Value);
            return true;
        }
        trigger_error('Error on set partymode.', E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_SetSpeed'.
     * Setzten die Abspielgeschwindigkeit.
     *
     * @access public
     * @param int $Value Geschwindigkeit.
     *   enum[-32, -16, -8, -4, -2, 0, 1, 2, 4, 8, 16, 32]
     * @return bool True bei Erfolg, sonst false.
     */
    public function SetSpeed(int $Value)
    {
        if (!is_int($Value)) {
            trigger_error('Value must be integer', E_USER_NOTICE);
            return false;
        }
        if ($Value == 1) {
            return $this->Play();
        }

        if ($Value == 0) {
            return $this->Pause();
        }

        if (!$this->isActive) {
            trigger_error('Player not active', E_USER_NOTICE);
            return false;
        }

        if (!in_array($Value, [-32, -16, -8, -4, -2, -1, 0, 1, 2, 4, 8, 16, 32])) {
            trigger_error('Invalid Value for speed.', E_USER_NOTICE);
            return false;
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->SetSpeed(['playerid' => $this->PlayerId, 'speed' => $Value]);
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ((int) $ret->speed == $Value) {
            $this->SetValueInteger('speed', $Value);
            return true;
        }
        trigger_error('Error on set speed.', E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_SetPosition'.
     * Springt auf eine absolute Position innerhalb einer Wiedergabe.
     *
     * @access public
     * @param int $Value Position in Prozent
     * @return bool True bei Erfolg, sonst false.
     */
    public function SetPosition(int $Value)
    {
        if (!is_int($Value)) {
            trigger_error('Value must be integer', E_USER_NOTICE);
            return false;
        }
        if (($Value < 0) || ($Value > 100)) {
            trigger_error('Value must be between 0 and 100', E_USER_NOTICE);
            return false;
        }

        if (!$this->isActive) {
            trigger_error('Player not active', E_USER_NOTICE);
            return false;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Seek(['playerid' => $this->PlayerId, 'value' => ['percentage' => $Value]]);
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        // TODO Werte stimmen bei audio nicht =?!
        if ((int) $ret->percentage == $Value) {
            $this->SetValueInteger('percentage', $Value);
            return true;
        }
        trigger_error('Error on set Position.', E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_LoadAlbum'.
     * Lädt ein Album und startet die Wiedergabe.
     *
     * @access public
     * @param int $AlbumId ID des Album.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */
    public function LoadAlbum(int $AlbumId)
    {
        if (!is_int($AlbumId)) {
            trigger_error('AlbumId must be integer', E_USER_NOTICE);
            return false;
        }
        return $this->Load('albumid', $AlbumId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_LoadArtist'.
     * Lädt alle Itemes eines Artist und startet die Wiedergabe.
     *
     * @access public
     * @param int $ArtistId ID des Artist.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */
    public function LoadArtist(int $ArtistId)
    {
        if (!is_int($ArtistId)) {
            trigger_error('ArtistId must be integer', E_USER_NOTICE);
            return false;
        }
        return $this->Load('artistid', $ArtistId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_LoadChannel'.
     * Lädt einen PVR-Kanal und startet die Wiedergabe.
     *
     * @access public
     * @param int $ChannelId ID des Kanals.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */
    public function LoadChannel(int $ChannelId)
    {
        if (!is_int($ChannelId)) {
            trigger_error('ChannelId must be integer', E_USER_NOTICE);

            return false;
        }
        return $this->Load('channelid', $ChannelId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_LoadDirectory'.
     * Lädt alle Itemes eines Verzeichnisses und startet die Wiedergabe.
     *
     * @access public
     * @param string $Directory Pfad welcher hinzugefügt werden soll.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */
    public function LoadDirectory(string $Directory)
    {
        if (!is_string($Directory)) {
            trigger_error('Directory must be string', E_USER_NOTICE);
            return false;
        }
        return $this->Load('directory', $Directory);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_LoadDirectoryRecursive'.
     * Lädt alle Itemes eines Verzeichnisses, sowie dessen Unterverzeichnisse, und startet die Wiedergabe.
     *
     * @access public
     * @param string $Directory Pfad welcher hinzugefügt werden soll.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */
    public function LoadDirectoryRecursive(string $Directory)
    {
        if (!is_string($Directory)) {
            trigger_error('Directory must be string', E_USER_NOTICE);
            return false;
        }
        return $this->Load('Directory', $Directory, ['recursive' => true]);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_LoadEpisode'.
     * Lädt eine Episode und startet die Wiedergabe.
     *
     * @access public
     * @param int $EpisodeId ID der Episode.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */
    public function LoadEpisode(int $EpisodeId)
    {
        if (!is_int($EpisodeId)) {
            trigger_error('EpisodeId must be integer', E_USER_NOTICE);
            return false;
        }
        return $this->Load('episodeid', $EpisodeId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_LoadFile'.
     * Lädt eine Datei und startet die Wiedergabe.
     *
     * @access public
     * @param string $File Pfad zu einer Datei.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */ public function LoadFile(string $File)
    {
        if (!is_string($File)) {
            trigger_error('File must be string', E_USER_NOTICE);
            return false;
        }
        return $this->Load('file', $File);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_LoadGenre'.
     * Lädt eine komplettes Genre und startet die Wiedergabe.
     *
     * @access public
     * @param int $GenreId ID des Genres.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */
    public function LoadGenre(int $GenreId)
    {
        if (!is_int($GenreId)) {
            trigger_error('GenreId must be integer', E_USER_NOTICE);
            return false;
        }
        return $this->Load('genreid', $GenreId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_LoadMovie'.
     * Lädt ein Film und startet die Wiedergabe.
     *
     * @access public
     * @param int $MovieId ID des Filmes.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */
    public function LoadMovie(int $MovieId)
    {
        if (!is_int($MovieId)) {
            trigger_error('MovieId must be integer', E_USER_NOTICE);
            return false;
        }
        return $this->Load('movieid', $MovieId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_LoadMusicvideo'.
     * Lädt ein Musicvideo und startet die Wiedergabe.
     *
     * @access public
     * @param int $MusicvideoId ID des Musicvideos.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */
    public function LoadMusicvideo(int $MusicvideoId)
    {
        if (!is_int($MusicvideoId)) {
            trigger_error('MusicvideoId must be integer', E_USER_NOTICE);
            return false;
        }
        return $this->Load('musicvideoid', $MusicvideoId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_LoadPlaylist'.
     * Lädt die Playlist und startet die Wiedergabe.
     *
     * @access public
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */
    public function LoadPlaylist()
    {
        return $this->Load('playlistid', $this->PlayerId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_LoadRecording'.
     * Lädt eine Aufzeichnung und startet die Wiedergabe.
     *
     * @access public
     * @param int $RecordingId ID der Aufnahme.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */
    public function LoadRecording(int $RecordingId)
    {
        if (!is_int($RecordingId)) {
            trigger_error('RecordingId must be integer', E_USER_NOTICE);
            return false;
        }
        return $this->Load('recordingid', $RecordingId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_LoadSong'.
     * Lädt ein Songs und startet die Wiedergabe.
     *
     * @access public
     * @param int $SongId ID des Songs.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */
    public function LoadSong(int $SongId)
    {
        if (!is_int($SongId)) {
            trigger_error('SongId must be integer', E_USER_NOTICE);
            return false;
        }
        return $this->Load('songid', $SongId);
    }

    /**
     * Wird ausgeführt wenn sich der Status vom Parent ändert.
     * @access protected
     */
    protected function IOChangeState($State)
    {
        parent::IOChangeState($State);
        if ($State == IS_ACTIVE) {
            $this->isActive = true;
            IPS_RunScriptText('<? KODIPLAYER_RequestState(' . $this->InstanceID . ',"ALL");');
            IPS_RunScriptText('<? KODIPLAYER_GetItemInternal(' . $this->InstanceID . ');');
        } else {
            $this->isActive = false;
            $this->SetTimerInterval('PlayerStatus', 0);
            $this->SetValueInteger('Status', 1);
            $this->SetValueString('duration', '');
            $this->SetValueString('totaltime', '');
            $this->SetValueString('time', '');
            $this->SetValueInteger('percentage', 0);
            $this->SetValueInteger('speed', 0);
        }
    }

    /**
     * Werte der Eigenschaften anfragen.
     *
     * @access protected
     * @param array $Params Enthält den Index "properties", in welchen alle anzufragenden Eigenschaften als Array enthalten sind.
     * @return bool true bei erfolgreicher Ausführung und dekodierung, sonst false.
     */
    protected function RequestProperties(array $Params)
    {
        $Param = array_merge($Params, ['playerid' => $this->PlayerId]);
        if (!$this->isActive) {
            return false;
        }
        $KodiData = new Kodi_RPC_Data(static::$Namespace);
        $KodiData->GetProperties($Param);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            $KodiPayload = new stdClass();
            $KodiPayload->player = new stdClass();
            $KodiPayload->player->playerid = $this->PlayerId;
            $this->Decode('OnStop', $KodiPayload);
            return false;
        }
        $this->Decode('GetProperties', $ret);
        return true;
    }

    /**
     * Dekodiert die empfangenen Daten und führt die Statusvariablen nach.
     *
     * @access protected
     * @param string $Method RPC-Funktion ohne Namespace
     * @param object $KodiPayload Der zu dekodierende Datensatz als Objekt.
     */
    protected function Decode($Method, $KodiPayload)
    {
        if ($KodiPayload == null) {
            $this->SendDebug('NULL : ' . $Method, $KodiPayload, 0);
            return;
        }
        if (property_exists($KodiPayload, 'player')) {
            if ($KodiPayload->player->playerid != $this->PlayerId) {
                return false;
            }
        } else {
            if (property_exists($KodiPayload, 'type')) {
                if (self::$Playertype[(string) $KodiPayload->type] != $this->PlayerId) {
                    return false;
                }
            } else {
                if (property_exists($KodiPayload, 'item')) {
                    if (property_exists($KodiPayload->item, 'channeltype')) {
                        if (self::$Playertype[(string) $KodiPayload->item->channeltype] != $this->PlayerId) {
                            return false;
                        }
                    } else {
                        if (self::$Playertype[(string) $KodiPayload->item->type] != $this->PlayerId) {
                            return false;
                        }
                    }
                }
            }
        }
        $this->SendDebug($Method, $KodiPayload, 0);

        switch ($Method) {
            case 'GetProperties':
            case 'OnPropertyChanged':
                foreach ($KodiPayload as $param => $value) {
                    switch ($param) {
                        case 'subtitles':
                            if ($this->PlayerId != self::Video) {
                                break;
                            }
                            $this->CreateSubtitleProfil($value);
                            if (count($value) == 0) {
                                $this->DisableAction('subtitle');
                            } else {
                                $this->EnableAction('subtitle');
                            }
                            break;
                        case 'subtitleenabled':
                            if ($this->PlayerId != self::Video) {
                                break;
                            }
                            if ($value === false) {
                                $this->SetValueInteger('subtitle', -1);
                            }
                            break;
                        // Object
                        case 'currentsubtitle':
                            if ($this->PlayerId != self::Video) {
                                break;
                            }
                            /* if (is_object($value)) {
                              if (property_exists($value, 'index')) {
                              $this->SetValueInteger('subtitle', (int) $value->index);
                              } else {
                              $this->SetValueInteger('subtitle', -1);
                              }
                              } else {
                              $this->SetValueInteger('subtitle', -1);
                              } */
                            break;
                        case 'audiostreams':
                            if ($this->PlayerId != self::Video) {
                                break;
                            }
                            $this->CreateAudioProfil($value);
                            if (count($value) == 1) {
                                $this->DisableAction('audiostream');
                            } else {
                                $this->EnableAction('audiostream');
                            }
                            break;
                        case 'currentaudiostream':
                            if ($this->PlayerId != self::Video) {
                                break;
                            }
                            if (is_object($value)) {
                                if (property_exists($value, 'index')) {
                                    $this->SetValueInteger('audiostream', (int) $value->index);
                                } else {
                                    $this->SetValueInteger('audiostream', 0);
                                }
                            } else {
                                $this->SetValueInteger('audiostream', 0);
                            }
                            break;
                        //time
                        case 'totaltime':
                            $this->SetValueString('duration', $this->ConvertTime($value));
                            break;
                        case 'time':
                            $this->SetValueString($param, $this->ConvertTime($value));
                            break;
                        // Anzahl

                        case 'repeat': //off
                            if ($this->PlayerId != self::Picture) {
                                $this->SetValueInteger($param, array_search((string) $value, ['off', 'one', 'all']));
                            }
                            break;
                        //boolean
                        case 'shuffled':
                        case 'partymode':
                            $this->SetValueBoolean($param, (bool) $value);
                            break;
                        //integer
                        case 'speed':
                            if ((int) $value == 0) {
                                $this->SetValueInteger('Status', 3);
                            } else {
                                $this->SetValueInteger('Status', 2);
                            }
                            // FIXME: No break. Please add proper comment if intentional
                        case 'percentage':
                            $this->SetValueInteger($param, (int) $value);
                            break;

                        //Action en/disable
                        case 'canseek':
                            if ((bool) $value) {
                                $this->EnableAction('percentage');
                            } else {
                                $this->DisableAction('percentage');
                            }
                            break;
                        case 'canshuffle':
                            if ((bool) $value) {
                                $this->EnableAction('shuffled');
                            } else {
                                $this->DisableAction('shuffled');
                            }
                            break;
                        case 'canrepeat':
                            if ($this->PlayerId == self::Picture) {
                                break;
                            }
                            if ((bool) $value) {
                                $this->EnableAction('repeat');
                            } else {
                                $this->DisableAction('repeat');
                            }
                            break;

                        case 'canchangespeed':
                            if ((bool) $value) {
                                $this->EnableAction('speed');
                            } else {
                                $this->DisableAction('speed');
                            }
                            break;
                        //ignore
                        case 'canrotate':
                        case 'canzoom':
                        case 'canmove':
                        case 'playlistid':
                        case 'live':
                        case 'playlist':
                        case 'position':
                        case 'type':
                            break;
                        default:
                            $this->SendDebug('Todo:' . $param, $value, 0);

                            break;
                    }
                }
                break;
            case 'OnStop':
                $this->SetTimerInterval('PlayerStatus', 0);
                $this->SetValueInteger('Status', 1);
                $this->SetValueString('duration', '');
                $this->SetValueString('totaltime', '');
                $this->SetValueString('time', '');
                $this->SetValueInteger('percentage', 0);
                $this->SetValueInteger('speed', 0);

                $this->SetValueString('album', '');
                $this->SetValueInteger('track', 0);
                $this->SetValueInteger('disc', 0);
                $this->SetValueString('artist', '');
                $this->SetValueString('lyrics', '');
                $this->SetValueBoolean('partymode', false);
                $this->SetValueInteger('year', 0);
                $this->SetValueString('genre', '');

                $this->SetValueString('showtitle', '');
                $this->SetValueString('channel', '');
                $this->SetValueInteger('season', 0);
                $this->SetValueInteger('episode', 0);

                $this->SetValueString('plot', '');
                $this->SetValueInteger('audiostream', 0);
                $this->SetValueInteger('subtitle', 0);

                $this->SetValueInteger('repeat', 0);
                $this->SetValueBoolean('shuffled', false);

                $this->SetValueString('label', '');

                $this->setActivePlayer(false);
                $this->SetCover('');
                IPS_RunScriptText('<? @KODIPLAYER_RequestState(' . $this->InstanceID . ',"ALL");');
                IPS_RunScriptText('<? IPS_Sleep(500); @KODIPLAYER_GetItemInternal(' . $this->InstanceID . ');');
                break;
            case 'OnPlay':
            case 'OnResume':
                $this->setActivePlayer(true);
                $this->SetValueInteger('Status', 2);
                IPS_RunScriptText('<? @KODIPLAYER_RequestState(' . $this->InstanceID . ',"ALL");');
                IPS_RunScriptText('<? @KODIPLAYER_GetItemInternal(' . $this->InstanceID . ');');
                $this->SetTimerInterval('PlayerStatus', 2000);
                break;
            case 'OnPause':
                $this->SetTimerInterval('PlayerStatus', 0);
                $this->SetValueInteger('Status', 3);
                IPS_RunScriptText('<? @KODIPLAYER_RequestState(' . $this->InstanceID . ',"ALL");');
                break;
            case 'OnSeek':
                $this->SetValueString('time', $this->ConvertTime($KodiPayload->player->time));
                break;
            case 'OnSpeedChanged':
                IPS_RunScriptText('<? @KODIPLAYER_RequestState(' . $this->InstanceID . ',"speed");');
                break;
            default:
                $this->SendDebug($Method, $KodiPayload, 0);
                break;
        }
    }

    ################## PRIVATE
    /**
     * Fragt Kodi an ob der Playertyp der Instanz gerade aktiv ist.
     *
     * @return bool true wenn Player aktiv ist, sonset false
     */
    private function getActivePlayer()
    {
        $KodiData = new Kodi_RPC_Data(static::$Namespace);
        $KodiData->GetActivePlayers();
        $ret = @$this->SendDirect($KodiData);

        if (is_null($ret) || (count($ret) == 0)) {
            $this->isActive = false;
        } else {
            $this->isActive = ((int) $ret[0]->playerid == $this->PlayerId);
        }

        $this->SendDebug('getActivePlayer', $this->isActive, 0);

        return $this->isActive;
    }

    /**
     * Setzt die Eigenschaft isActive sowie die dazugehörige IPS-Variable.
     *
     * @access private
     * @param bool $isActive True wenn Player als aktive gesetzt werden soll, sonder false.
     */
    private function setActivePlayer(bool $isActive)
    {
        $this->isActive = $isActive;
        $this->SendDebug('setActive', $this->isActive, 0);
    }

    /**
     * Holt das über $flie übergebene Cover vom Kodi-Webinterface, skaliert und konvertiert dieses und speichert es in einem MedienObjekt ab.
     *
     * @access private
     * @param string $file
     */
    private function SetCover(string $file)
    {
        $CoverID = @IPS_GetObjectIDByIdent('CoverIMG', $this->InstanceID);
        if ($CoverID === false) {
            $CoverID = IPS_CreateMedia(1);
            IPS_SetParent($CoverID, $this->InstanceID);
            IPS_SetIdent($CoverID, 'CoverIMG');
            IPS_SetName($CoverID, 'Cover');
            IPS_SetPosition($CoverID, 27);
            IPS_SetMediaCached($CoverID, true);
            $filename = 'media' . DIRECTORY_SEPARATOR . 'Cover_' . $this->InstanceID . '.png';
            IPS_SetMediaFile($CoverID, $filename, false);
            $this->SendDebug('Create Media', $filename, 0);
        }

        if ($file == '') {
            $CoverRAW = false;
        } else {
            $Size = $this->ReadPropertyInteger('CoverSize');
            $CoverRAW = $this->GetThumbnail($file, 0, $Size);
        }

        if ($CoverRAW === false) {
            $CoverRAW = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'nocover.png');
        }

        IPS_SetMediaContent($CoverID, base64_encode($CoverRAW));
    }

    /**
     *
     * Springt auf ein bestimmtes Item in der Wiedergabeliste.
     *
     * @access private
     * @param int|string $Value Index oder String-Enum
     *   enum['previous', 'next']
     * @return bool True bei Erfolg, sonst false.
     */
    private function GoToValue($Value)
    {
        if (!$this->isActive) {
            trigger_error('Player not active', E_USER_NOTICE);
            return false;
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GoTo(['playerid' => $this->PlayerId, 'to' => $Value]);
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret === 'OK') {
            return true;
        }
        trigger_error('Error on send ' . $Value . '.', E_USER_NOTICE);
        return false;
    }

    /**
     * Lädt ein Item und startet die Wiedergabe.
     *
     * @access private
     * @param string $ItemTyp Der Typ des Item.
     * @param string $ItemValue Der Wert des Item.
     * @param array $Ext Array welches mit übergeben werden soll (optional).
     * @return bool True bei Erfolg. Sonst false.
     */
    private function Load(string $ItemTyp, $ItemValue, $Ext = [])
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Open(array_merge(['item' => [$ItemTyp => $ItemValue]], $Ext));
        $ret = $this->Send($KodiData);
        if ($ret === 'OK') {
            return true;
        }
        trigger_error('Error on load ' . $ItemTyp . '.', E_USER_NOTICE);
        return false;
    }

    /**
     * Erzeugt aus einem Objekt ein Array für ein IPS-Variablenprofil
     *
     * @return array Werteliste für das Variablenprofil.
     */
    private function CreateProfilArray($Data, $Assos = [])
    {
        foreach ($Data as $item) {
            if ($item->language == '') {
                if (property_exists($item, 'name')) {
                    $Assos[] = [$item->index, $item->name, '', -1];
                } else {
                    $Assos[] = [$item->index, 'Unbekannt', '', -1];
                }
            } else {
                $Assos[] = [$item->index, $item->language, '', -1];
            }
        }
        return $Assos;
    }

    /**
     * Erzeugt aus einem Objekt von Untertiteln ein IPS-Variablenprofil.
     */
    private function CreateSubtitleProfil($Subtitles)
    {
        $Assos[0] = [-1, 'Aus', '', -1];
        $Assos = $this->CreateProfilArray($Subtitles, $Assos);
        $this->RegisterProfileIntegerEx('Subtitels.' . $this->InstanceID . '.Kodi', '', '', '', $Assos);
    }

    /**
     * Erzeugt aus einem Objekt von Audiostreams ein IPS-Variablenprofil.
     */
    private function CreateAudioProfil($AudioStream)
    {
        $Assos = $this->CreateProfilArray($AudioStream);
        $this->RegisterProfileIntegerEx('AudioStream.' . $this->InstanceID . '.Kodi', '', '', '', $Assos);
    }
}

/** @} */
