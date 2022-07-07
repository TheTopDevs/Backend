<?php


namespace App\Services\AntMedia;


use App\Services\AntMediaService;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;

class Stream
{
    /**
     * description:
     * The basic broadcast class
     *
     * streamId    string
     * the id of the stream
     *
     * status    string
     * the status of the stream
     *
     * Enum:
     * [ finished, broadcasting, created ]
     * type    string
     * the type of the stream
     *
     * Enum:
     * [ liveStream, ipCamera, streamSource, VoD ]
     * name    string
     * the name of the stream
     *
     * description    string
     * the description of the stream
     *
     * publish    boolean
     * it is a video filter for the service, this value is controlled by the user, default value is true in the db
     *
     * date    integer($int64)
     * the date when record is created in milliseconds
     *
     * plannedStartDate    integer($int64)
     * the planned start date
     *
     * duration    integer($int64)
     * the duration of the stream in milliseconds
     *
     * endPointList    [...]
     * publicStream    boolean
     * the identifier of whether stream is public or not
     *
     * is360    boolean
     * the identifier of whether stream is 360 or not
     *
     * listenerHookURL    string
     * the url that will be notified when stream is published, ended and muxing finished
     *
     * category    string
     * the category of the stream
     *
     * ipAddr    string
     * the IP Address of the IP Camera or publisher
     *
     * username    string
     * the user name of the IP Camera
     *
     * password    string
     * the password of the IP Camera
     *
     * quality    string
     * the quality of the incoming stream during publishing
     *
     * speed    number($double)
     * the speed of the incoming stream, for better quality and performance it should be around 1.00
     *
     * streamUrl    string
     * the stream URL for fetching stream, especially should be defined for IP Cameras or Cloud streams
     *
     * originAdress    string
     * the origin address server broadcasting
     *
     * mp4Enabled    integer($int32)
     * MP4 muxing whether enabled or not for the stream, 1 means enabled, -1 means disabled, 0 means no settings for the stream
     *
     * expireDurationMS    integer($int32)
     * the expire time in milliseconds For instance if this value is 10000 then broadcast should be started in 10 seconds after it is created.If expire duration is 0, then stream will never expire
     *
     * rtmpURL    string
     * the RTMP URL where to publish live stream to
     *
     * zombi    boolean
     * is true, if a broadcast that is not added to data store through rest service or management console It is false by default
     *
     * pendingPacketSize    integer($int32)
     * the number of audio and video packets that is being pending to be encoded in the queue
     *
     * hlsViewerCount    integer($int32)
     * the number of HLS viewers of the stream
     *
     * webRTCViewerCount    integer($int32)
     * the number of WebRTC viewers of the stream
     *
     * rtmpViewerCount    integer($int32)
     * the number of RTMP viewers of the stream
     *
     * startTime    integer($int64)
     * the publishing start time of the stream
     *
     * receivedBytes    integer($int64)
     * the received bytes until now
     *
     * bitrate    integer($int64)
     * the received bytes / duration
     *
     * userAgent    string
     * User - Agent
     *
     * latitude    string
     * latitude of the broadcasting location
     *
     * longitude    string
     * longitude of the broadcasting location
     *
     * altitude    string
     * altitude of the broadcasting location
     */

    /** @var string $streamId */
    public $streamId;
    /** @var string $status  */
    public $status;
    /** @var string $name */
    public $name;
    /** @var string $username */
    public $username;
    /** @var string $password */
    public $password;
    /** @var int $bitrate */
    public $bitrate;
    /** @var int $hlsViewerCount */
    public $hlsViewerCount;
    /** @var int $rtmpViewerCount */
    public $rtmpViewerCount;
    /** @var int $webRTCViewerCount */
    public $webRTCViewerCount;
    /** @var float $speed */
    public $speed;
    /** @var string $quality */
    public $quality;
    /** @var int $startDate */
    public $startDate;
    /** @var int $endDate */
    public $endDate;

    private $client;

    /**
     * Stream constructor.
     * @param string $streamId
     */
    public function __construct(string $streamId='')
    {
        $this->client = new Client(['base_uri' => config('app.ant_base_uri')]);
        if($streamId !== '') {
            $this->streamId = $streamId;
            $this->getDataFromServer();
        }
    }

    /**
     * @param object $request
     * @return Stream
     */
    public static function createFromRequest(object $request): Stream
    {
        \Log::info('create ant media stream from request ' . json_encode($request));
        $object = new self($request->streamId);

        $object->parseRequest($request);
        \Log::info('ant media stream object from request ' . json_encode($object));

        return $object;
    }

    /**
     * @param array $streamParams
     * @return Stream
     */
    public static function create(array $streamParams): Stream
    {
        $object = new self('');
        $request = $object->client->post('create',
            ['json' =>
                [
                    'name' => $streamParams['name'],
                    'username' => $streamParams['username'],
                    'password' => $streamParams['password'],
                ]
            ]);

        if ($request->getStatusCode() === 200) {
            $object->parseRequest(json_decode($request->getBody()), true);
        }

        return $object;
    }

    /**
     * @param object $request
     * @param bool $withStreamId
     */
    private function parseRequest($request, $withStreamId = false): void
    {
        if ($withStreamId) {
            $this->streamId = $request->streamId;
        }
        $this->status = $request->status;
        $this->name = $request->name;
        $this->username = $request->username;
        $this->password = $request->password;
        $this->bitrate = $request->bitrate;
        $this->hlsViewerCount = $request->hlsViewerCount;
        $this->rtmpViewerCount = $request->rtmpViewerCount;
        $this->webRTCViewerCount = $request->webRTCViewerCount;
        $this->speed = $request->speed;
        $this->quality = $request->quality;
    }

    /**
     * @return bool
     */
    public function getDataFromServer(): bool
    {
        if (!isset($this->streamId)) {
            return false;
        }
        $request = $this->client->get($this->streamId);

        if ($request->getStatusCode() === 200) {
            $this->parseRequest(json_decode($request->getBody()));
            return true;
        }

        return false;
    }

    public function update($data): bool
    {
        if (!isset($this->streamId)) {
            return false;
        }

        $request = $this->client->put($this->streamId, ['json' => $data]);

        if ($request->getStatusCode() === 200 && json_decode($request->getBody())->success === true) {

            return $this->getDataFromServer();
        }

        return false;
    }


    /**
     * @return bool
     */
    public function delete(): bool
    {
        if (!isset($this->streamId)) {
            return false;
        }
        $request = $this->client->delete($this->streamId);

        return $request->getStatusCode() === 200;
    }

    public function stop(): bool
    {
        $request = $this->client->post($this->streamId . '/stop');

        if ($request->getStatusCode() === 200 && json_decode($request->getBody())->success === true) {
            return $this->getDataFromServer();
        }

        return false;
    }

    /**
     * @param string|null $type
     * @param int|null $expireDate
     * @return Token
     */
    public function createToken( string $type = null, int $expireDate = null): Token
    {
        if(!$this->streamId){
            return new Token();
        }

        $type = $type ?? Token::TOKEN_TYPE_PLAY;
        $expireDate = isset($expireDate) ? Carbon::parse($expireDate)->unix() : now()->addDay()->unix();
        $request = $this->client->get($this->streamId . '/token', ['query' => [
            'id' => $this->streamId,
            'type' => $type,
            'expireDate' => $expireDate,
        ]]);

        return Token::createFromResponse(json_decode($request->getBody()));
    }

    /**
     * @param int $offset
     * @param int $size
     * @return array
     */
    public function getTokens( int $offset = 0, int $size = AntMediaService::DEFAULT_PAGINATION_SIZE)
    {
        $request = $this->client->get("$this->streamId/tokens/list/$offset/$size");

        $result = json_decode($request->getBody());
        dump($result);

        return [];
    }

    public function deleteAllTokens()
    {
        $request = $this->client->delete($this->streamId . '/tokens');
        $result = json_decode($request->getBody());
        dump($result);
        return $result->message;
    }

    /**
     * @param Token $token
     * @return bool
     */
    public function tokenIsValid(Token $token):bool
    {
        $request = $this->client->post('/validate-token', ['json' => [
            'streamId' => $token->streamId,
            'tokenId' => $token->tokenId,
            'type' => $token->type,
            'expireDate' => $token->expireDate,
        ]]);

        if (!empty(json_decode($request->getBody())->message)) {
            return json_decode($request->getBody())->message;
        }
        return false;
    }

    public function addConferenceRoom(): bool
    {
        if(!$this->streamId) {
            return false;
        }

        $request = $this->client->post('conference-rooms', ['json' => [
            'roomId'    => $this->streamId,
            'startDate' => now()->timestamp,
            'endDate'   => now()->addDay()->timestamp,
        ]]);

        if ($request->getStatusCode() !== 200){
            \Log::error("addConferenceRoom({$this->streamId}) request finished with status code " . $request->getStatusCode());
            return false;
        }

         $result = json_decode($request->getBody()->getContents());

        if($this->streamId === $result->roomId) {
            $this->startDate = $result->startDate;
            $this->endDate = $result->endDate;
        }

        return true;
    }

    public function deleteConferenceRoom()
    {
        if(!$this->endDate || !$this->startDate || !$this->streamId){
            return false;
        }

        $request = $this->client->delete("conference-rooms/{$this->streamId}");

        if ($request->getStatusCode() !== 200){
            \Log::error("deleteConferenceRoom({$this->streamId}) request finished with status code " . $request->getStatusCode());
            return false;
        }

        $result = json_decode($request->getBody()->getContents());
        if( $result->success === 'true') {
            return true;
        }
        \Log::error("deleteConferenceRoom({$this->streamId}) request error " . $request->getBody()->getContents());
        return false;
    }


}
