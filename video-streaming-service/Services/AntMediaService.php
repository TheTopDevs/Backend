<?php /** @noinspection ALL */

namespace App\Services;

use App\Models\UserStream;
use App\Services\AntMedia\Stream;
use App\Services\AntMedia\Token;
use GuzzleHttp\Client;


class AntMediaService
{
    /** Default Ant application name for live streaming "live" */
    const DEFAULT_APPLICATION_NAME = 'LiveApp';

    const DEFAULT_PAGINATION_SIZE = 20;
    const STREAM_STATE_FINISHED = 'finished';
    const STREAM_STATE_BROADCASTING = 'broadcasting';
    const STREAM_STATE_CREATED = 'created';


    /** @var array|\stdClass $apiAnswer - answer from API */
    private $apiAnswer = [];

    private $client;

    /** @var array $errors - array of errors of component */
    private $errors = [];

    /**
     * AntComponent constructor.
     */
    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => config('app.ant_base_uri'),
        ]);
    }


    /**
     * @return string
     */
    public function getDefaultApplicationName(): string
    {
        return self::DEFAULT_APPLICATION_NAME;
    }

    /**
     * Create new stream (for new Publisher means)
     * @param UserStream $streamParams
     * @return ?Stream
     */
    public function createStream(UserStream $streamParams = null): ?Stream
    {
        $data = [
            'name' => $streamParams->stream_name??'',
            'username' => $streamParams->getWowzaLogin()??'',
            'password' => $streamParams->getWowzaPass()??'',
        ];

        try {
            return Stream::create($data);
        } catch (\Exception $exception) {
            \Log::error('createStream ' . ($streamParams->stream_name??''). $exception->getMessage());
        }
        return null;
    }

    /**
     * @param string $streamName
     * @return Stream|bool
     */
    public function getStream($streamId):?Stream
    {
        try {
            return new Stream($streamId);
        } catch (\Exception $exception) {
            \Log::error('getStream '."$streamId \n" . $exception->getMessage());
        }
        return null;
    }

    public function updateStream($streamId, $data)
    {
        try {
            $request = $this->client->put($streamId, ['json' => $data]);

            if ($request->getStatusCode() === 200 && json_decode($request->getBody())->success === true) {
                return $this->getStream($streamId);
            }

            return (string)$request->getBody();

        } catch (\Exception $exception) {
            \Log::error('updateStream '. "$streamId \n" . $exception->getMessage());
        }
    }

    public function deleteStream( $streamId)
    {
        if (!$streamId) {
            return false;
        }
        try {
            $request = $this->client->request('DELETE', $streamId);

            if ($request->getStatusCode() === 200) {
                return json_decode($request->getBody());
            }

            return (string)$request->getBody();
        } catch (\Exception $exception) {
            \Log::error('deleteStream '."$streamId \n" . $exception->getMessage());
        }
    }


    public function stopStream($streamId)
    {
        if (!$streamId) {
            return false;
        }
        try {
            $request = $this->client->post($streamId . '/stop');

            if ($request->getStatusCode() === 200 && json_decode($request->getBody())->success === true) {
                return $this->getStream($streamId);
            }

            return (string)$request->getBody();
        } catch (\Exception $exception) {
            \Log::error('stopStream '."$streamId \n" . $exception->getMessage());
        }
    }

    public function streamState(string $streamId):string
    {
        return $this->getStream($streamId)->status;
    }

    public function createToken(string $streamId, string $type=Token::TOKEN_TYPE_PLAY, int $expireDate=null):?Token
    {
        try {
            return (new Stream($streamId))->createToken($type, $expireDate);
        }  catch (\Exception $exception) {
            \Log::error('createToken '."$streamId \n" . $exception->getMessage());
        }
        return null;
    }

    public function addConferenceToRoom(string $streamId):bool
    {
        if ($streamId) {
            return (new Stream($streamId))->addConferenceRoom();
        }
        return false;
    }

    public function deleteConferenceFromRoom(string $streamId):bool
    {
        if ($streamId) {
            return (new Stream($streamId))->deleteConferenceRoom();
        }
        return false;
    }

    /**
     * walking through all streams and delete finished streams
     * @return int
     */
    public function deleteUnusedStreams():int
    {
        $deleterdeStreamsCount = 0;
        $offset = 0;
        $limit = self::DEFAULT_PAGINATION_SIZE;
        try {
            $countRequest = $this->makeGetRequest('count');
            $total = $countRequest ? $countRequest->number : 0;

            if (!$total) {
                dd('no streams found on Antmedia');
            }
            $somethingWasDeleted = 1;
            while ($somethingWasDeleted) {
                $somethingWasDeleted = 0;
                while ($streamsList = $this->makeGetRequest("list/$offset/$limit")) {
                    foreach ($streamsList as $stream) {
                        dump($stream->streamId . ' ' . $stream->status);
                        if ($stream->status === 'finished') {
                            $somethingWasDeleted = 1;
                            $deleterdeStreamsCount++;
                            dump($this->deleteStream($stream->streamId));
                        }
                    }
                    $offset += $limit;
                }
            }
        } catch (\Throwable $e) {
            \Log::error("AntmediaService error:" . $e->getMessage());
        }
        return $deleterdeStreamsCount;
    }

    public function makeGetRequest($url)
    {
        $response = $this->client->get($url);
        if($response->getStatusCode() === 200)
            $data = json_decode($response->getBody()->getContents());
        if(!empty($data)){
            return $data;
        }
        return false;
    }

}
